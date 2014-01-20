<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Post extends CI_Model
{
	function Post()
	{
		parent::__construct();
		
		$this->load->helper('string');
		$this->load->model('IdCounts');
	}
	
	function getAllPosts() {
		$resData = array();
		$postDocs = array();
		$lastOffset = "";
		$lastOffsetDocId = "";
		$lastOffsetScore = "";

		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$offset = $this->input->post('offset');
		$offsetDocId = $this->input->post('offsetDocId');
		$limit = $this->input->post('limit');
		$orderBy = $this->input->post('orderBy');

		// exception handling
		if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_005');
			return $resData;
		} else if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_006');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		if (empty($offsetDocId))
	                $offsetDocId = POST_MAX_ID;
                if (empty($limit))
                        $limit = POST_DEFAULT_LIMIT;
                else if ($limit > POST_MAX_LIMIT)
                        $limit = POST_MAX_LIMIT;		

		// Validate orderBy parm and default to score if not valid
		if ((strcmp($orderBy, POST_ORDERBY_DATE) != 0) && (strcmp($orderBy, POST_ORDERBY_SCORE) != 0) && (strcmp($orderBy, POST_ORDERBY_GEO) != 0))
			$orderBy = POST_ORDERBY_SCORE;

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// Retrieve channel docs from DB
                        $skip = 0;

			if (strcmp($orderBy, POST_ORDERBY_DATE) == 0) {
				if (empty($offset))
		                        $offset = POST_MAX_DATE;
				else 
                                	$skip = 1;
                        	$myEndKey = array("", "");
                        	$myStartKey = array($offset, $offsetDocId);


//debugDummy("getAllPosts: offset: " . $offset);
//debugDummy("getAllPosts: offsetDocId: " . $offsetDocId);
//debugDummy("getAllPosts: endKey: " . $myEndKey);
//debugDummy("getAllPosts: limit: " . $limit);
//debugDummy("getAllPosts: skip: " . $skip);
                        	$results = $cb_obj->view("post", "getAllPostsByLastModified", array('startkey' => $myStartKey,
                                                                                                    'endkey' => $myEndKey,
                                                                                                    'descending' => 'true',
                                                                                                    'skip' => $skip,
                                                                                                    'limit' => $limit));
			} else {
				if (empty($offset))
		                        $offset = POST_MAX_SCORE;
				else 
                                	$skip = 2; // NAM: have no fucking idea why this needs to be 2, I should only have to skip 1 but some reason it only works if 2
                        	$myEndKey = array(0, "");
                        	$myStartKey = array($offset, $offsetDocId);

                        	$results = $cb_obj->view("post", "getAllPostsByScore", array('startkey' => $myStartKey,
                                                                                             'endkey' => $myEndKey,
                                                                                             'skip' => $skip,
                                                                                             'descending' => 'true',
                                                                                             'limit' => $limit));
			}

			// Set count of db rows for API return info
			$dbRows = count($results['rows']);

			// Iterate thru each row and pull the appropriate channel row
			foreach($results['rows'] as $row) {
                        	// Get post document from db
                                unset($doc);
	                        $doc = $cb_obj->get($row['id']);
 				if($doc) {
     					$postJson = json_decode($doc, true);
					// Now get all child posts
                        		$childResults = $cb_obj->view("post", "getChildPostsByPost", array('key' => $row['id']));
					foreach($childResults['rows'] as $childRow) {
                                		unset($childDoc);
			                        $childDoc = $cb_obj->get($childRow['id']);
 						if($childDoc) {
     							$childPostJson = json_decode($childDoc, true);
							array_push($postDocs, $childPostJson);
						} else {
							debugDummy("getAllPosts: failed to get child post Doc: " . $childRow['id']);
							continue;
						}
					}
				} else {
					debugDummy("getAllPosts: failed to get post Doc: " . $row['id']);
					continue;
				}

				array_push($postDocs, $postJson);
				$lastOffset = $postJson['lastModifiedDate'];
				$lastOffsetScore = $postJson['postScore'];
				$lastOffsetDocId = $row['id'];
                        }

			// Re-encode json document
                       	$allPostsJson = json_encode($postDocs);
                       	if ($allPostsJson == false) {
				debugDummy("getAllPosts: failed to json encode postDocs");
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                               	return $resData;
                       	}

			// Return success and the session token
			$resData['_responseStatus'] = (integer)true;
			$resData['postJson'] = $allPostsJson;
			$resData['postCount'] = $dbRows;
			if (strcmp($orderBy, POST_ORDERBY_DATE) == 0) {
				$resData['lastOffset'] = $lastOffset;
			} else {
				$resData['lastOffset'] = $lastOffsetScore;
			}
			$resData['lastOffsetDocId'] = $lastOffsetDocId;
			$resData['sessionToken'] = $sessionToken;

		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function getPost() {
		$resData = array();

		$sessionToken = $this->input->post('sessionToken');
                $userId = $this->input->post('userId');
                $postId = $this->input->post('postId');
                $postDocId = $this->input->post('postDocId');

		// exception handling
		if((empty($userId)) || ($userId == 0)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_002');
			return $resData;
		}else if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_004');
			return $resData;
		}else if((empty($postId)) && (empty($postDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_014');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// Generate postDocId if not passed over
			if (empty($postDocId))
				$postDocId = DOC_KEY_POST . COUCHBASE_KEY_DELIM . $postId;

                        // Get post document from db
                        $postDoc = $cb_obj->get($postDocId);

                        if($postDoc) {
                                $postJson = json_decode($postDoc, true);
                        } else {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('PST_010');
                                return $resData;
                        }

			// validate decode of json worked
			if ($postJson == NULL) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_004');
                               	return $resData;
			}

			// Return success 
			$resData['_responseStatus'] = (integer)true;
			$resData['postJson'] = $postDoc;
			$resData['postDocId'] = $postDocId;
			$resData['sessionToken'] = $sessionToken;

		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function updatePost() {
		$resData = array();
		$needsUpdate = false;

		$sessionToken = $this->input->post('sessionToken');
                $userId = $this->input->post('userId');
                $postId = $this->input->post('postId');
                $postDocId = $this->input->post('postDocId');
                $channelId = $this->input->post('channelId');
                $channelDocId = $this->input->post('channelDocId');
                $postTitle = $this->input->post('postTitle');
                $postText = $this->input->post('postText');
                $originalAsset = $this->input->post('originalAsset');
                $thumbnailAsset = $this->input->post('thumbnailAsset');
                $assetType = $this->input->post('assetType'); // Integer value
                $hasAttachment = $this->input->post('hasAttachment'); // Boolean
                $parentPostId = $this->input->post('parentPostId'); // Post Doc Id for parent post
                $postSeqId = $this->input->post('postSeqId'); // Sequence Id for multi step post
                $isSharedPost = $this->input->post('isSharedPost'); // Boolean
                $sharedFromPost = $this->input->post('sharedFromPost'); // original post doc Id
                $likeCount = $this->input->post('likeCount'); // integer value
                $commentCount = $this->input->post('commentCount'); // integer value
                $shareCount = $this->input->post('shareCount'); // integer value
                $isPublic = $this->input->post('isPublic'); // Boolean
                $linkArray = $this->input->post('linkArray'); // Array of multi links

		// exception handling
		if((empty($userId)) || ($userId == 0)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_002');
			return $resData;
		}else if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_004');
			return $resData;
		}else if((empty($postId)) && (empty($postDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_014');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// Verify post Id exists
			if (empty($postDocId))
				$postDocId = DOC_KEY_POST . COUCHBASE_KEY_DELIM . $postId;

                        // Get post document from db
                        $myDoc = $cb_obj->get($postDocId);
                        if($myDoc) {
                                $doc = json_decode($myDoc, true);
                        } else {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('PST_010');
                                return $resData;
                        }

			// validate decode of json worked
			if ($doc == NULL) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_004');
                               	return $resData;
			}

			// Change channel Id
			if ((!empty($channelId)) || (!empty($channelDocId))) {
				// Ensure channelId passed exists in db
                        	if (!empty($channelDocId)) {
					$expArray = explode(COUCHBASE_KEY_DELIM, $channelDocId);
					$channelId = $expArray[1];
	                        } else {
       	                        	$channelDocId = DOC_KEY_CHANNEL . COUCHBASE_KEY_DELIM . $channelId;
				}

                        	$channelDoc = $cb_obj->get($channelDocId);
				if ($cb_obj->getResultCode() != 0) {
					$resData['_responseStatus'] = (integer)false;
					list($resData['msgCode'], $resData['msg']) = generateError('PST_007');
					return $resData;
				}
				$doc['channelId'] = $channelId;
				$doc['channelDocId'] = $channelDocId;
				$needsUpdate = true;
			}

			// Change assetType setting
			if ((is_numeric($assetType)) && (strlen($assetType))) {
				if(($assetType != ASSETTYPE_IMAGE) && ($assetType != ASSETTYPE_VIDEO) && ($assetType != ASSETTYPE_YOUTUBE)) {
					$resData['_responseStatus'] = (integer)false;
					list($resData['msgCode'], $resData['msg']) = generateError('PST_012');
					return $resData;
				}
				$doc['assetType'] = $assetType;
				$needsUpdate = true;
			}

			// Change parentPostId setting
			if ((is_numeric($parentPostId)) && (strlen($parentPostId))) {
				$doc['parentPostId'] = $parentPostId;
				$needsUpdate = true;
			}

			// Change posSeqId setting
			if ((is_numeric($posSeqId)) && (strlen($posSeqId))) {
				$doc['posSeqId'] = $posSeqId;
				$needsUpdate = true;
			}

			// Change isPublic setting
			if ((is_numeric($isPublic)) && (strlen($isPublic))) {
				if ($isPublic != API_FALSE) 
					$doc['isPublic'] = true;
				else 
					$doc['isPublic'] = false;
				$needsUpdate = true;
			}

			// Set hasAttachment flag
			if ((is_numeric($hasAttachment)) && (strlen($hasAttachment))) {
				if ($hasAttachment == API_TRUE)
					$doc['hasAttachment'] = true;
				else	 
					$doc['hasAttachment'] = false;
				$needsUpdate = true;
			}

			// Set isSharedPost flag
			if ((is_numeric($isSharedPost)) && (strlen($isSharedPost))) {
				if ($isSharedPost == API_TRUE)
					$doc['isSharedPost'] = true;
				else	 
					$doc['isSharedPost'] = false;
				$needsUpdate = true;
			}

			// If shared from post then validate original post exists 
			if (!empty($sharedFromPost)) {
                        	$sharedDoc = $cb_obj->get($sharedFromPost);
                        	if ($cb_obj->getResultCode() != 0) {
                                	$resData['_responseStatus'] = (integer)false;
	                                list($resData['msgCode'], $resData['msg']) = generateError('PST_013');
                                	return $resData;
				}
				$doc['sharedFromPost'] = $sharedFromPost;
				$needsUpdate = true;
                        }


			// Change postTitle setting
			if (!empty($postTitle)) {
				$doc['postTitle'] = $postTitle;
				$needsUpdate = true;
			}

			// Change postText setting
			if (!empty($postText)) {
				$doc['postText'] = $postText;
				$needsUpdate = true;
			}

			// Change originalAsset setting
			if (!empty($originalAsset)) {
				$doc['originalAsset'] = $originalAsset;
				$needsUpdate = true;
			}

			// Change thumbnailAsset setting
			if (!empty($thumbnailAsset)) {
				$doc['thumbnailAsset'] = $thumbnailAsset;
				$needsUpdate = true;
			}

			// Change linkArray setting
			if (!empty($linkArray)) {
				$doc['linkArray'] = $linkArray;
				$needsUpdate = true;
			}

			if ($needsUpdate) {
	                        // Generate json document for new channel
                        	$lastDate = date("Y-m-d H:i:s");
				$doc['lastModifiedDate'] = $lastDate;

				$jsonDoc = json_encode($doc);
                                if ($jsonDoc == false) {
                                        $resData['_responseStatus'] = (integer)false;
                                        list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                        return $resData;
                                }

                                // Update Document in db
                                $cb_obj->replace($postDocId, $jsonDoc);
                                if ($cb_obj->getResultCode() != 0) {
//debugDummy("updateUser: replace result: " . $cb_obj->getResultCode());
                                        $resData['_responseStatus'] = (integer)false;
                                        list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                        return $resData;
                                }

			}

			// Return success 
			$resData['_responseStatus'] = (integer)true;
			$resData['postDocId'] = $postDocId;
			$resData['sessionToken'] = $sessionToken;

		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function createMultiStepPost() {
		$resData = array();
		$childPostIds = array();
		$data = json_decode(file_get_contents('php://input'), true);
		$jsondata = json_decode($data, true);
                if ($jsondata == NULL) {
			debugDummy("createMulti: error json decode");
		}

		// Process Parent Post Step
		$sessionToken = $jsondata['sessionToken'];
		$userId = $jsondata['userId'];
		$channelId = $jsondata['channelId'];
		$channelDocId = $jsondata['channelDocId'];
		$postTitle = $jsondata['postTitle'];
		$postText = $jsondata['postText'];
		$originalAsset = $jsondata['originalAsset'];
		$thumbnailAsset = $jsondata['thumbnailAsset'];
		$geolocationLatitude = $jsondata['geolocationLatitude'];
		$geolocationLongitude = $jsondata['geolocationLongitude'];
		$assetType = $jsondata['assetType'];
		$hasAttachment = $jsondata['hasAttachment'];
		$parentPostId = 0;
		$postSeqId = 1;
		$isSharedPost = $jsondata['isSharedPost'];
		$sharedFromPost = $jsondata['sharedFromPost'];
		$isPublic = $jsondata['isPublic'];
		$linkArray = $jsondata['linkArray'];
		$likeCount = 0;
		$commentCount = 0;
		$shareCount = 0;

//debugDummy("sessionToken: " . $sessionToken);
//debugDummy("userId: " . $userId);

		// Create array for post info and call createPost for parent step
		$myArray = array('sessionToken' => $sessionToken,
				 'userId' => $userId,
				 'channelId' => $channelId,
				 'channelDocId' => $channelDocId,
				 'postTitle' => $postTitle,
				 'postText' => $postText,
				 'originalAsset' => $originalAsset,
				 'thumbnailAsset' => $thumbnailAsset,
				 'geolocationLatitude' => $geolocationLatitude,
				 'geolocationLongitude' => $geolocationLongitude,
				 'assetType' => $assetType,
				 'hasAttachment' => $hasAttachment,
				 'parentPostId' => $parentPostId,
				 'postSeqId' => $postSeqId,
				 'isSharedPost' => $isSharedPost,
				 'sharedFromPost' => $sharedFromPost,
				 'isPublic' => $isPublic,
				 'linkArray' => $linkArray,
				 'likeCount' => $likeCount,
				 'commentCount' => $commentCount,
				 'shareCount' => $shareCount);

		$myRet = $this->createPost($myArray);
		if ($myRet['_responseStatus'] == (integer)false ) {
debugDummy("createMulti: failed to create parent post doc");
			return $myRet;
		}

		// Successfully created Parent Post Step, extract postId for child steps
		$postId = $myRet['postId'];
		$postDocId = $myRet['postDocId'];
//debugDummy("createMulti: parentPostId: " . $postId);
//debugDummy("createMulti: parentPostDocId: " . $postDocId);

		// Iterate through childPosts and create sub post for each step
		foreach($jsondata['childPosts'] as $childPost) {
			unset($myArray);
			unset($myRet);
			// Setup new data array for post creation
			$myArray = array('sessionToken' => $sessionToken,
					 'userId' => $userId,
					 'channelId' => $channelId,
					 'channelDocId' => $channelDocId,
					 'postTitle' => '',
					 'postText' => $childPost['postText'],
					 'originalAsset' => $childPost['originalAsset'],
					 'thumbnailAsset' => $childPost['thumbnailAsset'],
					 'geolocationLatitude' => $geolocationLatitude,
					 'geolocationLongitude' => $geolocationLongitude,
					 'assetType' => $childPost['assetType'],
					 'hasAttachment' => $childPost['hasAttachment'],
					 'parentPostId' => $postDocId,
					 'postSeqId' => $childPost['postSeqId'],
					 'isSharedPost' => $isSharedPost,
					 'sharedFromPost' => $sharedFromPost,
					 'isPublic' => $isPublic,
					 'linkArray' => $childPost['linkArray'],
					 'likeCount' => $likeCount,
					 'commentCount' => $commentCount,
					 'shareCount' => $shareCount);

			$myRet = $this->createPost($myArray);
			if ($myRet['_responseStatus'] == (integer)false ) {
debugDummy("createMulti: failed to create parent post doc");
				$myRet['postId'] = $postId;
				$myRet['postDocId'] = $postDocId;
				$myRet['childPostIds'] = $childPostIds;
				return $myRet;
			}
			
			array_push($childPostIds, $myRet['postId']);
		}

		// Return success and the postId
		$resData['_responseStatus'] = (integer)true;
		$resData['sessionToken'] = $sessionToken;
		$resData['postId'] = $postId;
		$resData['postDocId'] = $postDocId;
		$resData['childPostIds'] = $childPostIds;

		return $resData;
	}

	function createPost($jsondata = "") {
		$resData = array();
		if (empty($jsondata)) {
			$sessionToken = $this->input->post('sessionToken');
			$userId = $this->input->post('userId');
			$channelId = $this->input->post('channelId');
			$channelDocId = $this->input->post('channelDocId');
			$postTitle = $this->input->post('postTitle');
			$postText = $this->input->post('postText');
			$originalAsset = $this->input->post('originalAsset');
			$thumbnailAsset = $this->input->post('thumbnailAsset');
			$geolocationLatitude = $this->input->post('geolocationLatitude');
			$geolocationLongitude = $this->input->post('geolocationLongitude');
			$assetType = $this->input->post('assetType'); // Integer value
			$hasAttachment = $this->input->post('hasAttachment'); // Boolean
			$parentPostId = $this->input->post('parentPostId'); // Post Doc Id for parent post
			$postSeqId = $this->input->post('postSeqId'); // Sequence Id for multi step post
			$isSharedPost = $this->input->post('isSharedPost'); // Boolean
			$sharedFromPost = $this->input->post('sharedFromPost'); // original post doc Id
			$likeCount = $this->input->post('likeCount'); // integer value
			$commentCount = $this->input->post('commentCount'); // integer value
			$shareCount = $this->input->post('shareCount'); // integer value
			$isPublic = $this->input->post('isPublic'); // Boolean
			$linkArray = $this->input->post('linkArray'); // Array of multi links
		} else {
			$sessionToken = $jsondata['sessionToken'];
			$userId = $jsondata['userId'];
			$channelId = $jsondata['channelId'];
			$channelDocId = $jsondata['channelDocId'];
			$postTitle = $jsondata['postTitle'];
			$postText = $jsondata['postText'];
			$originalAsset = $jsondata['originalAsset'];
			$thumbnailAsset = $jsondata['thumbnailAsset'];
			$geolocationLatitude = $jsondata['geolocationLatitude'];
			$geolocationLongitude = $jsondata['geolocationLongitude'];
			$assetType = $jsondata['assetType'];
			$hasAttachment = $jsondata['hasAttachment'];
			$parentPostId = $jsondata['parentPostId'];
			$postSeqId = $jsondata['postSeqId'];
			$isSharedPost = $jsondata['isSharedPost'];
			$sharedFromPost = $jsondata['sharedFromPost'];
			$isPublic = $jsondata['isPublic'];
			$linkArray = $jsondata['linkArray'];
			$likeCount = $jsondata['likeCount'];
			$commentCount = $jsondata['commentCount'];
			$shareCount = $jsondata['shareCount'];
		}

		// exception handling
		if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_002');
			return $resData;
		}else if((empty($channelId)) && (empty($channelDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_008');
			return $resData;
		}else if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_004');
			return $resData;
		}else if((empty($assetType)) && (strlen($assetType))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_001');
			return $resData;
		}else if(($assetType != ASSETTYPE_IMAGE) && ($assetType != ASSETTYPE_VIDEO) && ($assetType != ASSETTYPE_YOUTUBE)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_012');
			return $resData;
		}else if((empty($hasAttachment)) && (strlen($hasAttachment))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_005');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		if (empty($geolocationLatitude)) 
			$geolocationLatitude = DEFAULT_LATITUDE;
		if (empty($geolocationLongitude)) 
			$geolocationLongitude = DEFAULT_LONGITUDE;

		// Set isSharedPost to true if not passed over as true
		if ((!isset($isSharedPost)) || ($isSharedPost != API_TRUE)) {
			$isSharedPost = false;
		} else {
			$isSharedPost = true;
		}

		// Set isPublic to true if not configured as private channel
		if ((!isset($isPublic)) || ($isPublic != API_FALSE)) {
			$isPublic = true;
		} else {
			$isPublic = false;
		}

		// Set hasAttachment to true if passed over
		if ((!isset($hasAttachement)) || ($hasAttachement != API_FALSE)) {
			$hasAttachement = true;
			// Validate asset Urls if hasAttachment
			if ((empty($originalAsset)) || (empty($thumbnailAsset))) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('PST_011');
				return $resData;
			}
		} else {
			$hasAttachement = false;
		}
 


		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// Ensure userId passed exists in db
                        $userProfile = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;
                        $profileDoc = $cb_obj->get($userProfile);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('PST_006');
				return $resData;
			}

			// Verify channel Id exists
                        if (!empty($channelDocId)) {
				$expArray = explode(COUCHBASE_KEY_DELIM, $channelDocId);
				$channelId = $expArray[1];
                        } else {
                                $channelDocId = DOC_KEY_CHANNEL . COUCHBASE_KEY_DELIM . $channelId;
			}

                        $myDoc = $cb_obj->get($channelDocId);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('PST_007');
				return $resData;
			}

			// Validate that user is the owner of the channel
                        if($myDoc) {
                                $doc = json_decode($myDoc, true);
                        } else {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('PST_007');
                                return $resData;
                        }

			// validate decode of json worked
			if ($doc == NULL) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_004');
                               	return $resData;
			}

			// Validate if the userId owns the channel
			$userProfileId = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;
			if (strcmp($doc['userProfile'], $userProfileId) != 0) {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('PST_023');
                                return $resData;
			}

			// If shared from post then validate original post exists 
			if (!empty($sharedFromPost)) {
                        	$postDoc = $cb_obj->get($sharedFromPost);
                        	if ($cb_obj->getResultCode() != 0) {
                                	$resData['_responseStatus'] = (integer)false;
	                                list($resData['msgCode'], $resData['msg']) = generateError('PST_013');
                                	return $resData;
				}
                        }

			// Get next postId from db
                        $postId = $this->IdCounts->internalGetNextPostId();
//debugDummy("createPost: postId: " .$postId);

			if ($postId != 0) {
	                        // Generate json document for new channel
                        	$lastDate = date("Y-m-d H:i:s");
                        	$myDocArray = array('type' => DOC_TYPE_POST,
                                                        'userId' => $userId,
                                                        'userProfile' => $userProfile,
                                                        'channelId' => $channelId,
                                                        'channelDocId' => $channelDocId,
                                                        'assetType' => $assetType,
                                                        'hasAttachment' => $hasAttachment,
                                                        'postSeqId' => $postSeqId,
                                                        'parentPostId' => $parentPostId,
                                                        'geolocationLatitude' => $geolocationLatitude,
                                                        'geolocationLongitude' => $geolocationLongitude,
                                                        'originalAsset' => $originalAsset,
                                                        'thumbnailAsset' => $thumbnailAsset,
                                                        'postTitle' => $postTitle,
                                                        'postText' => $postText,
                                                        'isSharedPost' => $isSharedPost,
                                                        'sharedFromPost' => $sharedFromPost,
                                                        'isPublic' => $isPublic,
                                                        'linkArray' => $linkArray,
                                                        'likeCount' => 0,
                                                        'commentCount' => 0,
                                                        'postScore' => 0,
                                                        'shareCount' => 0,
                                                        'creationDate' => $lastDate,
                                                        'lastModifiedDate' => $lastDate
                                                        );
                        	$myDoc = json_encode($myDocArray);
                        	if ($myDoc == false) {
					$resData['_responseStatus'] = (integer)false;
					list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                	return $resData;
                        	}

                                // Generate postDocId and add to DB
                                $postDocId = DOC_KEY_POST . COUCHBASE_KEY_DELIM . $postId;
                                $cb_obj->add($postDocId, $myDoc);
				if ($cb_obj->getResultCode() != 0) {
					$resData['_responseStatus'] = (integer)false;
					list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                	return $resData;
				}

				// Return success and the postId
				$resData['_responseStatus'] = (integer)true;
				$resData['sessionToken'] = $sessionToken;
				$resData['postId'] = $postId;
				$resData['postDocId'] = $postDocId;
			} else {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
				return $resData;
			}


		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function deletePost() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$postId = $this->input->post('postId');
		$postDocId = $this->input->post('postDocId');
		$userId = $this->input->post('userId');

		// exception handling
		if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_002');
			return $resData;
		}else if((empty($postId)) && (empty($postDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_014');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			if (empty($postDocId))
				$postDocId = DOC_KEY_POST . COUCHBASE_KEY_DELIM . $postId;

                        // Get post document from db
                        $myDoc = $cb_obj->get($postDocId);
                        if($myDoc) {
                                $doc = json_decode($myDoc, true);
                        } else {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('PST_010');
                                return $resData;
                        }

			// validate decode of json worked
			if ($doc == NULL) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_004');
                               	return $resData;
			}

			// Validate if the userId owns the post
			$userProfileId = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;
			if (strcmp($doc['userProfile'], $userProfileId) != 0) {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('PST_009');
                                return $resData;
			}

			if ($doc['deleted'] != true) {
	                        // Generate json document for new channel
                        	$lastDate = date("Y-m-d H:i:s");
				$doc['lastModifiedDate'] = $lastDate;
				$doc['deleteDate'] = $lastDate;
				$doc['deleted'] = true;

				$jsonDoc = json_encode($doc);
                                if ($jsonDoc == false) {
                                        $resData['_responseStatus'] = (integer)false;
                                        list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                        return $resData;
                                }

                                // Update Document in db
                                $cb_obj->replace($postDocId, $jsonDoc);
                                if ($cb_obj->getResultCode() != 0) {
                                        $resData['_responseStatus'] = (integer)false;
                                        list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                        return $resData;
                                }
			}

			// Return success 
			$resData['_responseStatus'] = (integer)true;
			$resData['sessionToken'] = $sessionToken;
			$resData['postDocId'] = $postDocId;

		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function undeletePost() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$postId = $this->input->post('postId');
		$postDocId = $this->input->post('postDocId');
		$userId = $this->input->post('userId');

		// exception handling
		if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_002');
			return $resData;
		}else if((empty($postId)) && (empty($postDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_014');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			if (empty($postDocId))
				$postDocId = DOC_KEY_POST . COUCHBASE_KEY_DELIM . $postId;

                        // Get post document from db
                        $myDoc = $cb_obj->get($postDocId);
                        if($myDoc) {
                                $doc = json_decode($myDoc, true);
                        } else {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('PST_010');
                                return $resData;
                        }

			// validate decode of json worked
			if ($doc == NULL) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_004');
                               	return $resData;
			}

			// Validate if the userId owns the post
			$userProfileId = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;
			if (strcmp($doc['userProfile'], $userProfileId) != 0) {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('PST_009');
                                return $resData;
			}

			if ($doc['deleted'] == true) {
	                        // Generate json document for new channel
                        	$lastDate = date("Y-m-d H:i:s");
				$doc['lastModifiedDate'] = $lastDate;
				$doc['undeleteDate'] = $lastDate;
				unset($doc['deleteDate']);
				unset($doc['deleted']);

				$jsonDoc = json_encode($doc);
                                if ($jsonDoc == false) {
                                        $resData['_responseStatus'] = (integer)false;
                                        list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                        return $resData;
                                }

                                // Update Document in db
                                $cb_obj->replace($postDocId, $jsonDoc);
                                if ($cb_obj->getResultCode() != 0) {
                                        $resData['_responseStatus'] = (integer)false;
                                        list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                        return $resData;
                                }
			}

			// Return success 
			$resData['_responseStatus'] = (integer)true;
			$resData['sessionToken'] = $sessionToken;
			$resData['postDocId'] = $postDocId;

		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function likePost() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$postId = $this->input->post('postId');
		$postDocId = $this->input->post('postDocId');

		// exception handling
		if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_002');
			return $resData;
		}else if((empty($postId)) && (empty($postDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_014');
			return $resData;
		}else if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_004');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// Ensure post exists
			if (!empty($postDocId)) {
				$expArray = explode(COUCHBASE_KEY_DELIM, $postDocId);
				$postId = $expArray[1];
                        } else {
                                $postDocId = DOC_KEY_POST . COUCHBASE_KEY_DELIM . $postId;
                        }

                	$postDocRaw = $cb_obj->get($postDocId);
			// validate json decode worked ok
			if ($postDocRaw == NULL) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_004');
                               	return $resData;
			}
                        $postDoc = json_decode($postDocRaw, true);
                	if ($postDoc == NULL) {
                        	$resData['_responseStatus'] = (integer)false;
                         	list($resData['msgCode'], $resData['msg']) = generateError('PST_010');
                        	return $resData;
			}

			// get channel ids from post doc and 
			$channelId = $postDoc['channelId'];
                        $channelDocId = $postDoc['channelDocId'];

			// Set originalUserProfile to the userProfile of the post
			$originalUserProfile = $postDoc['userProfile'];

			// Generate like post doc id base don postDocId and userId
			$likeDocId = $postDocId . COUCHBASE_SECOND_DELIM . DOC_KEY_LIKE . COUCHBASE_KEY_DELIM . $userId;

			// Generate userProfile for doc
			$userProfile = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;

	                // Generate json document for new channel
                       	$lastDate = date("Y-m-d H:i:s");
                       	$myDocArray = array('type' => DOC_TYPE_LIKE,
                                                'postId' => $postId,
                                                'postDocId' => $postDocId,
                                                'userId' => $userId,
                                                'userProfile' => $userProfile,
                                                'channelId' => $channelId,
                                                'channelDocId' => $channelDocId,
                                                'originalUserProfile' => $originalUserProfile,
                                                'likeDocId' => $likeDocId,
                                                'creationDate' => $lastDate,
                                                'lastModifiedDate' => $lastDate
                                                );
                       	$myDoc = json_encode($myDocArray);
                       	if ($myDoc == false) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                               	return $resData;
                       	}

			// Add document to db
                        $cb_obj->add($likeDocId, $myDoc);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                               	return $resData;
			}

			// imcrease count score for post document
			$this->updatePostScore($cb_obj, $postDocId, POST_LIKE_TYPE, POST_ACTION_ADD);

			// Return success and the postId
			$resData['_responseStatus'] = (integer)true;
			$resData['sessionToken'] = $sessionToken;
			$resData['postLikeId'] = $likeDocId;


		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function unlikePost() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$postId = $this->input->post('postId');
		$postDocId = $this->input->post('postDocId');

		// exception handling
		if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_002');
			return $resData;
		}else if((empty($postId)) && (empty($postDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_014');
			return $resData;
		}else if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_004');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// generate postDocId
			if (empty($postDocId)) 
                                $postDocId = DOC_KEY_POST . COUCHBASE_KEY_DELIM . $postId;

			// Generate like post doc id base don postDocId and userId
			$likeDocId = $postDocId . COUCHBASE_SECOND_DELIM . DOC_KEY_LIKE . COUCHBASE_KEY_DELIM . $userId;

			// Get likeDoc from db
                        $likeDoc = $cb_obj->get($likeDocId);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('PST_016');
                               	return $resData;
			}
                        $likeJson = json_decode($likeDoc, true);
			// validate json decode worked ok
			if ($likeJson == NULL) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_004');
                               	return $resData;
			}
			
			// Validate if the userId owns the likePost
                        $userProfileId = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;
                        if (strcmp($likeJson['userProfile'], $userProfileId) != 0) {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('PST_009');
                                return $resData;
                        }

			// Remove like doc from db - ie unlike post
                        $cb_obj->delete($likeDocId);
                        if ($cb_obj->getResultCode() != 0) {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                return $resData;
			}

			// Reduce count score for post document
			$this->updatePostScore($cb_obj, $postDocId, POST_LIKE_TYPE, POST_ACTION_REMOVE);

			// Return success 
			$resData['_responseStatus'] = (integer)true;
			$resData['sessionToken'] = $sessionToken;
			$resData['postLikeId'] = $likeDocId;


		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function addComment() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$postId = $this->input->post('postId');
		$postDocId = $this->input->post('postDocId');
		$commentText = $this->input->post('commentText');

		// exception handling
		if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_002');
			return $resData;
		}else if((empty($postId)) && (empty($postDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_014');
			return $resData;
		}else if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_004');
			return $resData;
		}else if(empty($commentText)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_017');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// Ensure post exists
			if (!empty($postDocId)) {
				$expArray = explode(COUCHBASE_KEY_DELIM, $postDocId);
				$postId = $expArray[1];
                        } else {
                                $postDocId = DOC_KEY_POST . COUCHBASE_KEY_DELIM . $postId;
                        }

                	$postDoc = $cb_obj->get($postDocId);
                	if ($cb_obj->getResultCode() != 0) {
                        	$resData['_responseStatus'] = (integer)false;
                         	list($resData['msgCode'], $resData['msg']) = generateError('PST_010');
                        	return $resData;
			}
                        $postJson = json_decode($postDoc, true);
			// validate json decode worked ok
			if ($postJson == NULL) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_004');
                               	return $resData;
			}

			// get channel ids from post doc and 
			$channelId = $postJson['channelId'];
                        $channelDocId = $postJson['channelDocId'];

			// Set originalUserProfile to the userProfile of the post
			$originalUserProfile = $postJson['userProfile'];

			// Generate comment post doc id base don postDocId and userId
                       	$timestamp = time();
			$commentDocId = $postDocId . COUCHBASE_SECOND_DELIM . DOC_KEY_COMMENT . COUCHBASE_KEY_DELIM . $userId . COUCHBASE_SECOND_DELIM . $timestamp;

			// Generate userProfile for doc
			$userProfile = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;

	                // Generate json document for new channel
                       	$lastDate = date("Y-m-d H:i:s");
                       	$myDocArray = array('type' => DOC_TYPE_COMMENT,
                                                'postId' => $postId,
                                                'postDocId' => $postDocId,
                                                'userId' => $userId,
                                                'userProfile' => $userProfile,
                                                'channelId' => $channelId,
                                                'channelDocId' => $channelDocId,
                                                'originalUserProfile' => $originalUserProfile,
                                                'commentDocId' => $commentDocId,
                                                'commentText' => $commentText,
                                                'creationDate' => $lastDate,
                                                'lastModifiedDate' => $lastDate
                                                );
                       	$myDoc = json_encode($myDocArray);
                       	if ($myDoc == false) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                               	return $resData;
                       	}

                        $cb_obj->add($commentDocId, $myDoc);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                               	return $resData;
			}

			// imcrease count score for post document
			$this->updatePostScore($cb_obj, $postDocId, POST_COMMENT_TYPE, POST_ACTION_ADD);

			// Return success and the postId
			$resData['_responseStatus'] = (integer)true;
			$resData['sessionToken'] = $sessionToken;
			$resData['postCommentId'] = $commentDocId;


		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function deleteComment() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$commentDocId = $this->input->post('commentDocId');

		// exception handling
		if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_002');
			return $resData;
		}else if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_004');
			return $resData;
		}else if(empty($commentDocId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_018');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// Get commentDoc from db
                        $commentDoc = $cb_obj->get($commentDocId);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('PST_019');
                               	return $resData;
			}
			// validate decode worked fine
                        $commentJson = json_decode($commentDoc, true);
			// validate json decode worked ok
			if ($commentJson == NULL) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_004');
                               	return $resData;
			}
			
			// generate postDocId from the document name, ie first part of the doc key
			$expArray = explode(COUCHBASE_SECOND_DELIM, $commentDocId);
			$postDocId = $expArray[0];

			// Validate if the userId owns the comment
                        $userProfileId = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;
                        if (strcmp($commentJson['userProfile'], $userProfileId) != 0) {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('PST_009');
                                return $resData;
                        }

			// Remove like doc from db - ie unlike post
                        $cb_obj->delete($commentDocId);
                        if ($cb_obj->getResultCode() != 0) {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                return $resData;
			}

			// Reduce count score for post document
			$this->updatePostScore($cb_obj, $postDocId, POST_COMMENT_TYPE, POST_ACTION_REMOVE);

			// Return success 
			$resData['_responseStatus'] = (integer)true;
			$resData['sessionToken'] = $sessionToken;
			$resData['commentDocId'] = $commentDocId;


		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function sharePost() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$postId = $this->input->post('postId');
		$postDocId = $this->input->post('postDocId');
		$channelId = $this->input->post('channelId');
		$channelDocId = $this->input->post('channelDocId');

		// exception handling
		if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_002');
			return $resData;
		}else if((empty($postId)) && (empty($postDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_014');
			return $resData;
		}else if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_004');
			return $resData;
		}else if((empty($channelId)) && (empty($channelDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_008');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// Ensure post exists
			if (!empty($postDocId)) {
				$expArray = explode(COUCHBASE_KEY_DELIM, $postDocId);
				$postId = $expArray[1];
                        } else {
                                $postDocId = DOC_KEY_POST . COUCHBASE_KEY_DELIM . $postId;
                        }

                	$postDoc = $cb_obj->get($postDocId);
                	if ($cb_obj->getResultCode() != 0) {
                        	$resData['_responseStatus'] = (integer)false;
                         	list($resData['msgCode'], $resData['msg']) = generateError('PST_010');
                        	return $resData;
			}
                        $postJson = json_decode($postDoc, true);
			// validate json decode worked ok
			if ($postJson == NULL) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_004');
                               	return $resData;
			}

			// Ensure channelId passed exists in db
                        if (!empty($channelDocId)) {
				$expArray = explode(COUCHBASE_KEY_DELIM, $channelDocId);
				$channelId = $expArray[1];
                        } else {
                                $channelDocId = DOC_KEY_CHANNEL . COUCHBASE_KEY_DELIM . $channelId;
                        }

                        $channelDoc = $cb_obj->get($channelDocId);
                        if ($cb_obj->getResultCode() != 0) {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('PST_007');
                                return $resData;
                        }

                        $channelJson = json_decode($channelDoc, true);
			// Validate the user is the owner of the channel sharing to
			if (($channelJson['userId'] == NULL) || ($channelJson['userId'] != $userId)) {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
                                return $resData;
			}

			// get channel ids from post doc and 
			$originalChannelId = $postJson['channelId'];
                        $originalChannelDocId = $postJson['channelDocId'];

			// Set originalUserProfile to the userProfile of the post
			$originalUserProfile = $postJson['userProfile'];

			// Generate comment post doc id base don postDocId and userId - means only 1 share doc is permitted per channel
			$shareDocId = $postDocId . COUCHBASE_SECOND_DELIM . DOC_KEY_SHARE . COUCHBASE_KEY_DELIM . $channelId;
			$shareDoc = $cb_obj->get($shareDocId);
                        if($shareDoc) {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('PST_020');
                                return $resData;
                        }

			// Generate userProfile for doc
			$userProfile = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;

	                // Generate json document for new channel
                       	$lastDate = date("Y-m-d H:i:s");
                       	$myDocArray = array('type' => DOC_TYPE_SHARE,
                                                'postId' => $postId,
                                                'postDocId' => $postDocId,
                                                'userId' => $userId,
                                                'userProfile' => $userProfile,
                                                'channelId' => $channelId,
                                                'channelDocId' => $channelDocId,
                                                'originalChannelId' => $originalChannelId, // referenced from original post channelId
                                                'originalChannelDocId' => $originalChannelDocId, // referenced from original post channelDocId
                                                'originalUserProfile' => $originalUserProfile, // referenced from original post doc userProfile
                                                'shareDocId' => $shareDocId,
                                                'creationDate' => $lastDate,
                                                'lastModifiedDate' => $lastDate
                                                );
                       	$myDoc = json_encode($myDocArray);
                       	if ($myDoc == false) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                               	return $resData;
                       	}

                        $cb_obj->add($shareDocId, $myDoc);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                               	return $resData;
			}

			// imcrease count score for post document
			$this->updatePostScore($cb_obj, $postDocId, POST_SHARE_TYPE, POST_ACTION_ADD);

			// Return success and the postId
			$resData['_responseStatus'] = (integer)true;
			$resData['sessionToken'] = $sessionToken;
			$resData['shareDocId'] = $shareDocId;


		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function unsharePost() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$postId = $this->input->post('postId');
		$postDocId = $this->input->post('postDocId');
		$channelId = $this->input->post('channelId');
		$channelDocId = $this->input->post('channelDocId');

		// exception handling
		if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_002');
			return $resData;
		}else if((empty($postId)) && (empty($postDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_014');
			return $resData;
		}else if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_004');
			return $resData;
		}else if((empty($channelId)) && (empty($channelDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('PST_008');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// generate postDocId
			if (empty($postDocId)) 
                                $postDocId = DOC_KEY_POST . COUCHBASE_KEY_DELIM . $postId;

			// Ensure channelId passed exists in db
                        if (!empty($channelDocId)) {
				$expArray = explode(COUCHBASE_KEY_DELIM, $channelDocId);
				$channelId = $expArray[1];
                        } else {
                                $channelDocId = DOC_KEY_CHANNEL . COUCHBASE_KEY_DELIM . $channelId;
                        }

			// Generate shareDocId to delete
			$shareDocId = $postDocId . COUCHBASE_SECOND_DELIM . DOC_KEY_SHARE . COUCHBASE_KEY_DELIM . $channelId;

			// Get shareDoc from db
                        $shareDoc = $cb_obj->get($shareDocId);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('PST_022');
                               	return $resData;
			}
                        $shareJson = json_decode($shareDoc, true);
			// validate json decode worked ok
			if ($shareJson == NULL) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_004');
                               	return $resData;
			}
			
			// Validate if the userId owns the share Doc
                        $userProfileId = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;
                        if (strcmp($shareJson['userProfile'], $userProfileId) != 0) {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('PST_009');
                                return $resData;
                        }

			// Remove share doc from db
                        $cb_obj->delete($shareDocId);
                        if ($cb_obj->getResultCode() != 0) {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                return $resData;
			}

			// Reduce count score for post document
			$this->updatePostScore($cb_obj, $postDocId, POST_SHARE_TYPE, POST_ACTION_REMOVE);

			// Return success 
			$resData['_responseStatus'] = (integer)true;
			$resData['sessionToken'] = $sessionToken;
			$resData['shareDocId'] = $shareDocId;


		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function updatePostScore($cb_obj, $postDocId, $type, $action) 	{
		// Get postDoc from db
                $postDoc = $cb_obj->get($postDocId);
                if ($cb_obj->getResultCode() != 0) {
                        return $false;
                }	
                $doc = json_decode($postDoc, true);
		// validate json decode worked ok
		if ($doc == NULL) {
                       	return $false;
		}

		// Set incr/decr amounts based on action flag
		$incrAmount = 1; 
		if ($action == POST_ACTION_REMOVE) 
			$incrAmount = -1;

		switch($type) {
			case POST_LIKE_TYPE:
				$doc['likeCount'] += (1 * $incrAmount);
				$doc['postScore'] += (POST_SCORE_LIKE * $incrAmount);
				break;
			case POST_COMMENT_TYPE:
				$doc['commentCount'] += (1 * $incrAmount);
				$doc['postScore'] += (POST_SCORE_COMMENT * $incrAmount);
				break;
			case POST_SHARE_TYPE:
				$doc['shareCount'] += (1 * $incrAmount);
				$doc['postScore'] += (POST_SCORE_SHARE * $incrAmount);
				break;
		}
		
		$jsonDoc = json_encode($doc);
                if ($jsonDoc == false) 
                	return false;
                
                $cb_obj->replace($postDocId, $jsonDoc);
                if ($cb_obj->getResultCode() != 0) {
			return false;
		}
		
		return true;
	}
}
?>
