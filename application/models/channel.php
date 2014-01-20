<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Channel extends CI_Model
{
	function Channel()
	{
		parent::__construct();
		
		$this->load->helper('string');
		$this->load->model('IdCounts');
	}
	
	function getChannel() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$channelId = $this->input->post('channelId');
		$channelDocId = $this->input->post('channelDocId');

		// exception handling
		if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_004');
			return $resData;
		} else if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_002');
			return $resData;
		}else if((empty($channelId)) && (empty($channelDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_008');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			if (!empty($channelDocId))
				$docKey = $channelDocId;
			else 
				$docKey = DOC_KEY_CHANNEL . COUCHBASE_KEY_DELIM . $channelId;

                        // Get channel document from db
                        $channelDoc = $cb_obj->get($docKey);
 			if($channelDoc) {
     				$channelJson = json_decode($channelDoc, true);
			} else {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('CHN_010');
				return $resData;
			}

			// Add channel metrics to channel Doc
                        $channelJson['channelPostCount'] = 100;
                        $channelJson['channelActivityCount'] = 200;
                        $channelJson['channelFollowerCount'] = 300;

			// Re-encode Json Doc
			$channelDoc = json_encode($channelJson);
                       	if ($channelDoc == false) {
				debugDummy("getChannel: failed to json encode channelDoc");
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                               	return $resData;
                       	}

			// Return success and the session token
			$resData['_responseStatus'] = (integer)true;
			$resData['channelJson'] = $channelDoc;
			$resData['sessionToken'] = $sessionToken;

		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function getAllChannels() {
		$resData = array();
		$channelDocs = array();
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$offset = $this->input->post('offset');
		$offsetDocId = $this->input->post('offsetDocId');
		$limit = $this->input->post('limit');

		// exception handling
		if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_004');
			return $resData;
		} else if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_002');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		if (empty($offset)) {
			if (!empty($offsetDocId)) {
				$expArray = explode(COUCHBASE_KEY_DELIM, $offsetDocId);
				$offset = $expArray[1];
			}
		}

		if (empty($offset))
                        $offset = CHANNEL_MAX_ID;
                if (empty($limit))
                        $limit = CHANNEL_DEFAULT_LIMIT;
                else if ($limit > CHANNEL_MAX_LIMIT)
                        $limit = CHANNEL_MAX_LIMIT;		

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// Retrieve channel docs from DB
                        $myStartKey = (int)$offset;
                        $myEndKey = (int)0;
                        $skip = 0;
                        if ($offset != CHANNEL_MAX_ID)
                                $skip = 1;

//debugDummy("getAllChannels: myStartKey: " . $myStartKey);
//debugDummy("getAllChannels: endKey: " . $myEndKey);
//debugDummy("getAllChannels: limit: " . $limit);
//debugDummy("getAllChannels: skip: " . $skip);
                        $results = $cb_obj->view("channel", "getAllChannels", array('startkey' => $myStartKey,
                                                                                    'endkey' => $myEndKey,
                                                                                    'descending' => 'true',
                                                                                    'skip' => $skip,
                                                                                    'limit' => $limit));

			// Set count of db rows for API return info
			$dbRows = count($results['rows']);

			// Iterate thru each row and pull the appropriate channel row
			foreach($results['rows'] as $row) {
                        	// Get user profile document from db
                                unset($doc);
//debugDummy("getAllChannels: getting channel doc: " .$row['id']);
	                        $doc = $cb_obj->get($row['id']);
 				if($doc) {
     					$channelJson = json_decode($doc, true);
				} else {
					debugDummy("getAllChannels: failed to get channel Doc: " . $row['id']);
					continue;
				}

				// Add channel metrics to channel Doc
				$channelJson['channelPostCount'] = 100;
				$channelJson['channelActivityCount'] = 200;
				$channelJson['channelFollowerCount'] = 300;

				array_push($channelDocs, $channelJson);
                        }

			// Re-encode json document
                       	$allChannelsJson = json_encode($channelDocs);
                       	if ($allChannelsJson == false) {
				debugDummy("getAllChannels: failed to json encode channelDocs");
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                               	return $resData;
                       	}

			// Return success and the session token
			$resData['_responseStatus'] = (integer)true;
			$resData['channelJson'] = $allChannelsJson;
			$resData['channelCount'] = $dbRows;
			$resData['sessionToken'] = $sessionToken;

		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function getChannelsByUserProfile() {
		$resData = array();
		$channelDocs = array();
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$requestUserId = $this->input->post('requestUserId');
		$requestUserDocId = $this->input->post('requestUserDocId');

		// exception handling
		if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_004');
			return $resData;
		} else if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_002');
			return $resData;
		} else if((empty($requestUserId)) && (empty($requestUserDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_011');
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

			// Get userProfile Doc Id
			if (!empty($requestUserDocId))
				$docKey = $requestUserDocId;
			else 
				$docKey = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $requestUserId;

			// Retrieve channel docs from DB
                        $results = $cb_obj->view("channel", "getChannelsByUserProfile", array('key' => $docKey,
											      'reduce' => false));

			// Set count of channels for API return info
			$dbRows = count($results['rows']);

			// Iterate thru each row and pull the appropriate channel row
			foreach($results['rows'] as $row) {
                        	// Get user profile document from db
                                unset($doc);
	                        $doc = $cb_obj->get($row['id']);
 				if($doc) {
     					$channelJson = json_decode($doc, true);
				} else {
					debugDummy("getChannelsByUserProfile: failed to get channel Doc: " . $row['id']);
					continue;
				}

				// Add channel metrics to channel Doc
				$channelJson['channelPostCount'] = 100;
				$channelJson['channelActivityCount'] = 200;
				$channelJson['channelFollowerCount'] = 300;

				array_push($channelDocs, $channelJson);
                        }

			// Re-encode json document
                       	$allChannelsJson = json_encode($channelDocs);
                       	if ($allChannelsJson == false) {
				debugDummy("getChannelsByUserProfile: failed to json encode channelDocs");
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                               	return $resData;
                       	}

			// Return success and the session token
			$resData['_responseStatus'] = (integer)true;
			$resData['channelJson'] = $allChannelsJson;
			$resData['requestUserDocId'] = $docKey;
			$resData['channelCount'] = $dbRows;
			$resData['sessionToken'] = $sessionToken;

		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function updateChannel() {
		$resData = array();
		$needsUpdate = false;
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$channelId = $this->input->post('channelId');
		$channelDocId = $this->input->post('channelDocId');
		$name = $this->input->post('name');
		$descriptionText = $this->input->post('descriptionText');
		$categoryId = $this->input->post('categoryId');
		$coverphotoUrls = $this->input->post('coverphotoUrls');
		$personalChannel = $this->input->post('personalChannel');
		$isPublic = $this->input->post('isPublic');

		// exception handling
		if((empty($userId)) || ($userId == 0)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_002');
			return $resData;
		}else if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_004');
			return $resData;
		}else if((empty($channelId)) && (empty($channelDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_008');
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

			if (!empty($channelDocId))
				$docKey = $channelDocId;
			else 
				$docKey = DOC_KEY_CHANNEL . COUCHBASE_KEY_DELIM . $channelId;
//debugDummy("NAM: updateChannel: docKey: " . $docKey);

                        // Get channel document from db
                        $myDoc = $cb_obj->get($docKey);
                        if($myDoc) {
                                $doc = json_decode($myDoc, true);
                        } else {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                return $resData;
                        }

			// Change category Id
			if (!empty($categoryId)) {
				// Ensure categoryId passed exists in db
                        	$catDoc = $cb_obj->get($categoryId);
				if ($cb_obj->getResultCode() != 0) {
					$resData['_responseStatus'] = (integer)false;
					list($resData['msgCode'], $resData['msg']) = generateError('CHN_007');
					return $resData;
				}
				$doc['category'] = $categoryId;
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

			// Set PersonalChannel flag
			if ((is_numeric($personalChannel)) && (strlen($personalChannel))) {
				if ($personalChannel == API_TRUE)
					$doc['personalChannel'] = true;
				else	 
					$doc['personalChannel'] = false;
				$needsUpdate = true;
			}

			// Change name setting
			if (!empty($name)) {
				$doc['name'] = $name;
				$needsUpdate = true;
			}

			// Change descriptionText setting
			if (!empty($descriptionText)) {
				$doc['descriptionText'] = $descriptionText;
				$needsUpdate = true;
			}

			// Change coverphotoUrls setting
			if (!empty($coverphotoUrls)) {
				$doc['coverphotoUrls'] = $coverphotoUrls;
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
                                $cb_obj->replace($docKey, $jsonDoc);
                                if ($cb_obj->getResultCode() != 0) {
//debugDummy("updateUser: replace result: " . $cb_obj->getResultCode());
                                        $resData['_responseStatus'] = (integer)false;
                                        list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                        return $resData;
                                }

			}

			// Return success 
			$resData['_responseStatus'] = (integer)true;
			$resData['channelDocId'] = $docKey;
			$resData['sessionToken'] = $sessionToken;

		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function createChannel() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$name = $this->input->post('name');
		$descriptionText = $this->input->post('descriptionText');
		$categoryId = $this->input->post('categoryId');
		$userId = $this->input->post('userId');
		$coverphotoUrls = $this->input->post('coverphotoUrls');
		$personalChannel = $this->input->post('personalChannel');
		$isPublic = $this->input->post('isPublic');

		// exception handling
		if(empty($name)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_001');
			return $resData;
		}else if((empty($userId)) || ($userId == 0)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_002');
			return $resData;
		}else if(empty($categoryId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_003');
			return $resData;
		}else if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_004');
			return $resData;
		}else if(!isset($personalChannel)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_005');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		// Set isPublic to true if not configured as private channel
		if ((!isset($isPublic)) || ($isPublic != API_FALSE)) {
			$isPublic = true;
		} else {
			$isPublic = false;
		}

		// Set PersonalChannel flag
		if ($personalChannel == API_TRUE)
			$personalChannel = true;
		else 
			$personalChannel = false;


		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// Ensure userId passed exists in db
                        $userProfile = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;
                        $profileDoc = $cb_obj->get($userProfile);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('CHN_006');
				return $resData;
			}

			// Ensure categoryId passed exists in db
                        $catDoc = $cb_obj->get($categoryId);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('CHN_007');
				return $resData;
			}

			// Get next channelId from db
                        $channelId = $this->IdCounts->internalGetNextChannelId();
//debugDummy("createChannel: channelId: " .$channelId);

			if ($channelId != 0) {
	                        // Generate json document for new channel
                        	$lastDate = date("Y-m-d H:i:s");
                        	$myDocArray = array('type' => DOC_TYPE_CHANNEL,
                                                        'channelId' => $channelId,
                                                        'userId' => $userId,
                                                        'userProfile' => $userProfile,
                                                        'category' => $categoryId,
                                                        'name' => $name,
                                                        'descriptionText' => $descriptionText,
                                                        'coverphotoUrls' => $coverphotoUrls,
                                                        'personalChannel' => $personalChannel,
                                                        'isPublic' => $isPublic,
                                                        'creationDate' => $lastDate,
                                                        'lastModifiedDate' => $lastDate
                                                        );
                        	$myDoc = json_encode($myDocArray);
                        	if ($myDoc == false) {
					$resData['_responseStatus'] = (integer)false;
					list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                	return $resData;
                        	}

                                // Generate docKey and add to DB
                                $docKey = DOC_KEY_CHANNEL . COUCHBASE_KEY_DELIM . $channelId;
                                $cb_obj->add($docKey, $myDoc);
				if ($cb_obj->getResultCode() != 0) {
//debugDummy("updateUser: replace result: " . $cb_obj->getResultCode());
					$resData['_responseStatus'] = (integer)false;
					list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                	return $resData;
				}

				// Return success and the channelId
				$resData['_responseStatus'] = (integer)true;
				$resData['sessionToken'] = $sessionToken;
				$resData['channelId'] = $channelId;
				$resData['channelDocId'] = $docKey;
				$resData['channelJson'] = $myDoc;
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

	function deleteChannel() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$channelId = $this->input->post('channelId');
		$channelDocId = $this->input->post('channelDocId');
		$userId = $this->input->post('userId');

		// exception handling
		if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_002');
			return $resData;
		}else if((empty($channelId)) && (empty($channelDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_008');
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

			if (!empty($channelDocId))
				$docKey = $channelDocId;
			else 
				$docKey = DOC_KEY_CHANNEL . COUCHBASE_KEY_DELIM . $channelId;

                        // Get channel document from db
                        $myDoc = $cb_obj->get($docKey);
                        if($myDoc) {
                                $doc = json_decode($myDoc, true);
                        } else {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('CHN_010');
                                return $resData;
                        }

			// Validate if the userId owns the channel
			$userProfileId = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;
			if (strcmp($doc['userProfile'], $userProfileId) != 0) {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('CHN_009');
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
                                $cb_obj->replace($docKey, $jsonDoc);
                                if ($cb_obj->getResultCode() != 0) {
                                        $resData['_responseStatus'] = (integer)false;
                                        list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                                        return $resData;
                                }

				$this->deleteChannelPosts($cb_obj, $docKey);
			}

			// Return success 
			$resData['_responseStatus'] = (integer)true;
			$resData['sessionToken'] = $sessionToken;
			$resData['channelDocId'] = $docKey;

		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function unfollowChannel() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$channelId = $this->input->post('channelId');
		$channelDocId = $this->input->post('channelDocId');

		// exception handling
		if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_004');
			return $resData;
		} else if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_002');
			return $resData;
		}else if((empty($channelId)) && (empty($channelDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_008');
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

/****NAM don't need to check if user exists
			// Ensure userId passed exists in db
                        $userProfile = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;
                        $profileDoc = $cb_obj->get($userProfile);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('CHN_006');
				return $resData;
			}
*****/

			if (!empty($channelDocId)) {
				$expArray = explode(COUCHBASE_KEY_DELIM, $channelDocId);
				$channelId = $expArray[1];
			}

                        // Generate docKey and delete from DB
                        $docKey = DOC_KEY_FOLLOW . COUCHBASE_KEY_DELIM . $channelId . COUCHBASE_KEY_DELIM . $userId;
                        $cb_obj->delete($docKey);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('CHN_013');
                                return $resData;
			}

			// Return success and the channelId
			$resData['_responseStatus'] = (integer)true;
			$resData['sessionToken'] = $sessionToken;
//			$resData['channelFollowDocId'] = $docKey;


		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function followChannel() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$channelId = $this->input->post('channelId');
		$channelDocId = $this->input->post('channelDocId');

		// exception handling
		if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_004');
			return $resData;
		} else if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_002');
			return $resData;
		}else if((empty($channelId)) && (empty($channelDocId))) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('CHN_008');
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

			// Ensure userId passed exists in db
                        $userProfile = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;
                        $profileDoc = $cb_obj->get($userProfile);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('CHN_006');
				return $resData;
			}

			if (empty($channelDocId)) {
                                $channelDocId = DOC_KEY_CHANNEL . COUCHBASE_KEY_DELIM . $channelId;
			} else {
				$expArray = explode(COUCHBASE_KEY_DELIM, $channelDocId);
				$channelId = $expArray[1];
			}

                        // Get channel document from db
                        $channelDoc = $cb_obj->get($channelDocId);
                        if($channelDoc) {
                                $channelJson = json_decode($channelDoc, true);
                        } else {
                                $resData['_responseStatus'] = (integer)false;
                                list($resData['msgCode'], $resData['msg']) = generateError('CHN_010');
                                return $resData;
                        }

	                // Generate json document for new follow channel
                       	$lastDate = date("Y-m-d H:i:s");
                       	$myDocArray = array('type' => DOC_TYPE_FOLLOW,
                                            'channelId' => $channelId,
                                            'channelDocId' => $channelDocId,
                                            'userId' => $userId,
                                            'userProfile' => $userProfile,
                                            'creationDate' => $lastDate,
                                            'lastModifiedDate' => $lastDate
                                            );
                       	$myDoc = json_encode($myDocArray);
                       	if ($myDoc == false) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                               	return $resData;
                       	}

                        // Generate docKey and add to DB
                        $docKey = DOC_KEY_FOLLOW . COUCHBASE_KEY_DELIM . $channelId . COUCHBASE_KEY_DELIM . $userId;
                        $cb_obj->add($docKey, $myDoc);
			if ($cb_obj->getResultCode() != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('CHN_012');
                                return $resData;
			}

			// Return success and the channelId
			$resData['_responseStatus'] = (integer)true;
			$resData['sessionToken'] = $sessionToken;
			$resData['channelFollowDocId'] = $docKey;


		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function deleteChannelPosts($cb_obj, $channelDocId) {
		// NAM function to get all posts made by a channel and set their delete flag to indicate deleted because channel was deleted
		// flag should be different to a regular post delete flag, so channel undelete would be able to undelete channel posts
	}
}
?>
