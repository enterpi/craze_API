<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common extends CI_Model
{
	function Common()
	{
		parent::__construct();
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
