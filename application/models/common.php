<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common extends CI_Model
{
	function Common()
	{
		parent::__construct();
	}
	
	
	/**
	 * function: uploadRefAttachment 
	 * Attach assets to contents based on inputs. 
	 * 
	 */
	function uploadRefAttachment($refId,$refTypeId,$assetId,$attachmentSource='album'){
		if($attachmentSource!='thirdparty'){
			$tableName = ($attachmentSource=='default')?("assetdefault"):("asset");
			$this->db->select('*');
			$this->db->where('id', $assetId);
			$query = $this->db->get($tableName);
			$result = $query->row();
		
			$assetPath = ($result->assetType==ASSETTYPE_IMAGE)?($this->config->item('assetPathPhoto')):($this->config->item('assetPathVideo'));		
			$attachmentLocation = "";
			if($refTypeId!=''){
				$attachmentPath = $this->config->item('attachmentPath');		
				$attachmentLocation = $attachmentPath."/".$refTypeId."_".$refId;
				$attachment_location = $this->config->item('attachment_path')."/".$refTypeId."_".$refId;
				//$this->deleteRefAttachment($refId,$refTypeId);	
				if(!is_dir($this->config->item('assetPath').$attachmentLocation)){
					@mkdir($this->config->item('assetPath').$attachmentLocation, 0755, true);
				}
				if(is_dir($this->config->item('assetPath').$attachmentLocation)){
					$fileName = $result->path;
					$thumbName = $result->path;
					
					$source_path = $assetPath."/".$result->userId."/".$attachmentSource."/".$result->album."/".$fileName;
					$thumb_source_path = $assetPath."/".$result->userId."/".$attachmentSource."/".$result->album."/". "thumbnail_" . $fileName;
					$original_source_path = $assetPath."/".$result->userId."/".$attachmentSource."/".$result->album."/". "original_" . $fileName;
					if(copy($source_path, $this->config->item('assetPath').$attachmentLocation ."/". $fileName)){
						if ($result->assetType==ASSETTYPE_IMAGE) {
							// copy thumbnail
							copy($thumb_source_path, $this->config->item('assetPath').$attachmentLocation ."/". "thumbnail_" . $fileName);
							// copy original file
							copy($original_source_path, $this->config->item('assetPath').$attachmentLocation ."/". "original_" . $fileName);
						} elseif($result->assetType==ASSETTYPE_VIDEO){
							$fileNameArr = explode(".",$fileName);
							$thumbName = $fileNameArr[0].".jpg";
							$source_path = $assetPath."/".$result->userId."/".$attachmentSource."/".$result->album."/".$thumbName;
							copy($source_path, $this->config->item('assetPath').$attachmentLocation ."/". $thumbName);
						}
						$data = array(
							'refId' => $refId,
							'refType' => $refTypeId,						
							'path' => $attachment_location,
							'thumb' => $thumbName,
							'asset' => $fileName,
							'assetType' => $result->assetType
						);			
						//Execute Query
						$this->db->insert('attachments', $data);
					}
				}			
			}
			return $this->config->item('assetPath').$attachmentLocation;
		}else{
			$data = array(
				'refId' => $refId,
				'refType' => $refTypeId,						
				'path' => '',
				'thumb' => $assetId,
				'asset' => $assetId,
				'assetType' => ASSETTYPE_VIDEO
			);			
			//Execute Query
			$this->db->insert('attachments', $data);
			return $attachmentLocation;
		}
	}
	
	
	function copyFromMyStuffToChatterbox($userId, $assetId) {
		
		$s3 = new AmazonS3();
		$ret = false;

		
		// first get details of the source asset 
		$this->db->where("id", $assetId);
		$this->db->limit(1);
		$userassetQuery = $this->db->get("userasset");
		
		if (($userassetQuery->num_rows > 0) && ($userId) && ($assetId)) {
			$sourceFileName = $userassetQuery->row()->asset;
			$sourceElements = explode("_", $sourceFileName);
			$sourceElementsCount = count($sourceElements);
			if ($sourceElementsCount > 0) {
				// get the last element which is the md5 of the filename
				$fileName = $sourceElements[$sourceElementsCount-1];
			} else {
				$fileName = $sourceFileName;
			}
			$targetFileName = $userId . "_" . time() . "_" . $fileName;

                        if ($userassetQuery->row()->assetType == ASSETTYPE_IMAGE) {
	
		            $bucket = $this->config->item('userImagesBucket');

			    // copy all 3 variants of file on s3
			
    			    // copy regular file 
			    $sourceFileArray = array('bucket' => $bucket, 'filename' => $sourceFileName);
			    $targetFileArray = array('bucket' => $bucket, 'filename' => $targetFileName);
			    $responseReg = $s3->copy_object($sourceFileArray, $targetFileArray, array('acl' => AmazonS3::ACL_PUBLIC));

			    // copy original file 
			    $sourceFileArray = array('bucket' => $bucket, 'filename' => "original_" . $sourceFileName);
			    $targetFileArray = array('bucket' => $bucket, 'filename' => "original_" . $targetFileName);
			    $responseOrg = $s3->copy_object($sourceFileArray, $targetFileArray, array('acl' => AmazonS3::ACL_PUBLIC));

			    // copy thumbnail file
			    $sourceFileArray = array('bucket' => $bucket, 'filename' => "thumbnail_" . $sourceFileName);
			    $targetFileArray = array('bucket' => $bucket, 'filename' => "thumbnail_" . $targetFileName);
			    $responseThb = $s3->copy_object($sourceFileArray, $targetFileArray, array('acl' => AmazonS3::ACL_PUBLIC));
                        } else if ($userassetQuery->row()->assetType == ASSETTYPE_VIDEO) {
		            $bucket = $this->config->item('userVideosBucket');

    			    // copy regular video file 
			    $sourceFileArray = array('bucket' => $bucket, 'filename' => $sourceFileName);
			    $targetFileArray = array('bucket' => $bucket, 'filename' => $targetFileName);
			    $responseReg = $s3->copy_object($sourceFileArray, $targetFileArray, array('acl' => AmazonS3::ACL_PUBLIC));

			    // copy video placeholder file
			    $sourceFileArray = array('bucket' => $bucket, 'filename' => getVideoPlaceholderImage($sourceFileName));
			    $targetFileArray = array('bucket' => $bucket, 'filename' => getVideoPlaceholderImage($targetFileName));
			    $responseThb = $s3->copy_object($sourceFileArray, $targetFileArray, array('acl' => AmazonS3::ACL_PUBLIC));

			    // copy mp4 version if available
			    if ($userassetQuery->row()->mp4Available == "1") {
			        $sourceFileArray = array('bucket' => $bucket, 'filename' => getMP4VideoFilename($sourceFileName));
			        $targetFileArray = array('bucket' => $bucket, 'filename' => getMP4VideoFilename($targetFileName));
			        $responseMP4 = $s3->copy_object($sourceFileArray, $targetFileArray, array('acl' => AmazonS3::ACL_PUBLIC));
			    }
                        }
		
			if ($responseReg->isOK() && $responseThb->isOK()) {
				// now insert new userasset into database
			
				$insertData = array(
					'userId' =>  $userassetQuery->row()->userId,
					'asset' => $targetFileName,
					'assetType' => $userassetQuery->row()->assetType,
					'title' => $userassetQuery->row()->title, 
					'desc' => $userassetQuery->row()->desc,
					'refId' => 0,
					'refType' => REFTYPE_POST,
					'isAvailable' => 1,
					'isLowRes' => $userassetQuery->row()->isLowRes, 
					'assetDate' => $userassetQuery->row()->assetDate,
					'isFlagged' => $userassetQuery->row()->isFlagged,
					'flaggedBy' => $userassetQuery->row()->flaggedBy, 
					'flaggedDate' => $userassetQuery->row()->flaggedDate,
					'mp4Available' => $userassetQuery->row()->mp4Available,
				);  
				$this->db->insert('userasset',$insertData);
				$userassetIdInserted = $this->db->insert_id();
				$ret = $userassetIdInserted;
			}
		} 
		
		return $ret;
		
	}

	function sendPushNotification($userId, $message, $sound = false, $badge = false)
	{
//debugDummy("NAM: inside sendPushNotification");
//debugDummy("NAM: userId: ".$userId);
//debugDummy("NAM: message: ".$message);

       		 if (!$sound)
       		         $sound = 'default';
       		 if (!$badge)
       		         $badge = 1;
       		 $badge = sprintf('%d', $badge);

		 // get pushQueue to use 
		 $memcacheArr = $this->config->item('PushServerMemcache');
                 $memcacheServer = $memcacheArr['hostname'];
                 $memcachePort = $memcacheArr['port'];
                 debugDummy("NAM: Push memcache: hostname: " . $memcacheServer);
                 debugDummy("NAM: Push memcache: port: " . $memcachePort);

       		 $memcache_obj = memcache_connect($memcacheServer, $memcachePort);
       		 $queueId = memcache_get($memcache_obj, $this->config->item('pushQueue'));
		 if ((!isset($queueId)) || ($queueId == ''))
			$queueId = $this->config->item('pushQueue1');
//debugDummy("NAM: queue being used: ".$queueId);

		 // get queue and process contents
       		 $serialzedArray = memcache_get($memcache_obj, $queueId);
       		 $unserializedArray = array();

       		 if($serialzedArray) {
       		         $unserializedArray = unserialize($serialzedArray);
		 }

       		         $this->db->select('u.pushNotifications,ud.deviceId');
       		         $this->db->distinct();
			 $this->db->join('user as u', 'u.id = ud.userId');
       		         $this->db->where('userId', $userId);
       		         $query = $this->db->get('userdevices as ud');
	                if($query->num_rows() > 0)
                	{
                       		 foreach($query->result() as $row)
                       		 {
//debugDummy("NAM: pushing notification: deviceId: ".$row->deviceId." userId: ".$userId);
//debugDummy("NAM: pushNotifications flag: ".$row->pushNotifications);
					if ($row->pushNotifications == PUSH_NOTIFICATIONS_TRUE) {
	                       		         array_push($unserializedArray, array("token"=>$row->deviceId, "message"=>$message, "sound"=>$sound, "badge"=>$badge));
						debugDummy("NAM: push notifications added to arry");
					} else {
						debugDummy("NAM: push notifications disabled, skipping send for user.");
					}
                       		 }
               	 	} else {
//debugDummy("NAM: no devices for user to send push notification");
               	 	}

	        	$serializedArray = serialize($unserializedArray);
	       	 	memcache_set($memcache_obj, $queueId,  $serializedArray, 0, 84600);
	}

}
?>
