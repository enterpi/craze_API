<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Model
{
	function User()
	{
		parent::__construct();
		
		$this->load->helper('email');
		$this->load->helper('string');
		$this->load->model('IdCounts');
		$this->load->model('Channel');
	}
	
	function getUser() {
		$resData = array();
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$requestUserId = $this->input->post('requestUserId');

		// exception handling
		if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('USR_006');
			return $resData;
		} else if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('USR_007');
			return $resData;
		} else if(empty($requestUserId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('USR_010');
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

                        $docKey = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $requestUserId;
debugDummy("NAM: getUser: docKey: " . $docKey);

                        // Get user profile document from db
                        $userDoc = $cb_obj->get($docKey);
 			if($userDoc) {
     				$userJson = json_decode($userDoc, true);
			} else {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('USR_009');
				return $resData;
			}

			// Remove password from json
			$userJson['password'] = "*********";


			// Retrieve User Metrics
                        $results = $cb_obj->view("post", "getActivityCountByUserProfile", array('key' => $docKey));
			$activityCount = 0;
			foreach($results['rows'] as $row) {
				$activityCount += $row['value'];
			}
			$userJson['activityCount'] = $activityCount;
			
			// Retrieve User Post Metrics
                        $results = $cb_obj->view("post", "getAllPostsByUserProfile", array('key' => $docKey,
											   'reduce' => true));
			$postCount = 0;
			foreach($results['rows'] as $row) {
				$postCount += $row['value'];
			}
			$userJson['postCount'] = $postCount;
			
			// Retrieve User Followers Metrics
                        $results = $cb_obj->view("channel", "getAllFollowersOfUserProfile", array('key' => $docKey,
											       'reduce' => true));
			$followerCount = 0;
			foreach($results['rows'] as $row) {
				$followerCount += $row['value'];
			}
			$userJson['followerCount'] = $followerCount;
			

			// Re-encode json document
                       	$userDoc = json_encode($userJson);
                       	if ($userDoc == false) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                               	return $resData;
                       	}

			// Return success and the session token
			$resData['_responseStatus'] = (integer)true;
			$resData['userJson'] = $userDoc;
			$resData['sessionToken'] = $sessionToken;

		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function getAllUsers() {
		$resData = array();
		$userDocs = array();
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$offset = $this->input->post('offset');
		$limit = $this->input->post('limit');

debugDummy("inside getAllUsers");
		// exception handling
		if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('USR_006');
			return $resData;
		} else if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('USR_007');
			return $resData;
		}

		// Authenticate userId matches sessionToken
		$sessionUserId = $userId; //NAM just for testing now need to get from memcache
		if ($userId != $sessionUserId) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_003');
			return $resData;
		}

		if (empty($offset))
                        $offset = USER_MAX_ID;
                if (empty($limit))
                        $limit = USER_DEFAULT_LIMIT;
                else if ($limit > USER_MAX_LIMIT)
                        $limit = USER_MAX_LIMIT;		

		// Authenticate userId matches sessionToken
		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// Retrieve user docs from DB
                        $myStartKey = (int)$offset;
                        $myEndKey = (int)0;
                        $skip = 0;
                        if ($offset != USER_MAX_ID)
                                $skip = 1;

debugDummy("getAllUsers: myStartKey: " . $myStartKey);
debugDummy("getAllUsers: endKey: " . $myEndKey);
debugDummy("getAllUsers: limit: " . $limit);
debugDummy("getAllUsers: skip: " . $skip);
                        $results = $cb_obj->view("user", "getAllUsers", array('startkey' => $myStartKey,
                                                                              'endkey' => $myEndKey,
                                                                              'descending' => 'true',
                                                                              'skip' => $skip,
                                                                              'limit' => $limit));

			// Set count of db rows for API return info
                        $dbRows = count($results['rows']);

			// Iterate thru each row and pull the appropriate user row
			foreach($results['rows'] as $row) {
                        	// Get user profile document from db
                                unset($userDoc);
debugDummy("getAllUsers: getting user doc: " .$row['id']);
	                        $userDoc = $cb_obj->get($row['id']);
 				if($userDoc) {
     					$userJson = json_decode($userDoc, true);
				} else {
					debugDummy("getAllUsers: failed to get user Doc: " . $row['id']);
					continue;
				}

				// Remove password from json
				$userJson['password'] = "*********";
	
				// Set docKey to use to retrieve metrics for that user
                        	$docKey = $row['id'];

				// Retrieve User Metrics
                        	$metricsResult = $cb_obj->view("post", "getActivityCountByUserProfile", array('key' => $docKey));
                        	$activityCount = 0;
                        	foreach($metricsResult['rows'] as $metricRow) {
                                	$activityCount += $metricRow['value'];
                        	}
                        	$userJson['activityCount'] = $activityCount;

                        	// Retrieve User Post Metrics
                        	$metricsResult = $cb_obj->view("post", "getAllPostsByUserProfile", array('key' => $docKey,
                                                                                           'reduce' => true));
                        	$postCount = 0;
                        	foreach($metricsResult['rows'] as $metricRow) {
                                	$postCount += $metricRow['value'];
                        	}
                        	$userJson['postCount'] = $postCount;

                        	// Retrieve User Followers Metrics
                        	$metricsResult = $cb_obj->view("channel", "getAllFollowersOfUserProfile", array('key' => $docKey,
                                                                                               'reduce' => true));
                        	$followerCount = 0;
                        	foreach($metricsResult['rows'] as $metricRow) {
                                	$followerCount += $metricRow['value'];
                        	}
                        	$userJson['followerCount'] = $followerCount;
	
/*****
				// Re-encode json document
       	                	$userDoc = json_encode($userJson);
       	                	if ($userDoc == false) {
					debugDummy("getAllUsers: failed to json decode user Doc: " . $row['id']);
					continue;
       	                	}
*****/
			
				array_push($userDocs, $userJson);
                        }

			// Re-encode json document
                       	$allUsersJson = json_encode($userDocs);
                       	if ($allUsersJson == false) {
				debugDummy("getAllUsers: failed to json encode userDocs");
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
                               	return $resData;
                       	}

			// Return success and the session token
			$resData['_responseStatus'] = (integer)true;
			$resData['userJson'] = $allUsersJson;
			$resData['userCount'] = $dbRows;
			$resData['sessionToken'] = $sessionToken;

		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function login() {
		$resData = array();
		$username = $this->input->post('username');
		$password = $this->input->post('password');

		// exception handling
		if(empty($username) || empty($password)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('USR_001');
			return $resData;
		}


		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// Get user profile doc id, should only be 1 for a username
			$results = $cb_obj->view("user", "findUserByUsername", array('key' => $username));
			$docKey = "";
			if ($results != false) {
    				foreach($results['rows'] as $row) {
					$docKey = $row['id'];
//debugDummy("login: userId found: ". $row['id']);
					break;
				}
			}
			if ($docKey == "") {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('USR_005');
				return $resData;
			}

//debugDummy("NAM: login: docKey: " . $docKey);

                        // Get user profile document from db
                        $myDoc = $cb_obj->get($docKey);
 			if($myDoc) {
     				$doc = json_decode($myDoc, true);
			} else {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
				return $resData;
			}

			// Verify passwords match
			$docPassword = $doc['password'];
			$password = doEncryption($password);
//debugDummy("NAM: login: docPassword: " . $docPassword);
//debugDummy("NAM: login: password: " . $password);
			if (strcmp($password, $docPassword) != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('USR_005');
				return $resData;
			}

			// Generate session token and store in memcache
			$sessionToken = "mysessiontoken";

			// Return success and the session token
			$resData['_responseStatus'] = (integer)true;
			$resData['userJson'] = $myDoc;
			$resData['sessionToken'] = $sessionToken;

		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	function logout() {
		$resData = array();
		$username = $this->input->post('username');
		$sessionToken = $this->input->post('sessionToken');

		// exception handling
		if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('USR_006');
			return $resData;
		}

		// Remove session token from memcache
		// NAM still to do

		// Return success and the session token
		$resData['_responseStatus'] = (integer)true;
		$resData['sessionToken'] = $sessionToken;
	}

	function createUser() {
		$resData = array();
//		$userData = $this->getUserDetails('token', $this->input->post('tokenId'));
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		$confirmPassword = $this->input->post('confirmPassword');
		$email = $this->input->post('email');
		$profileUrl = $this->input->post('profileUrl');

		// exception handling
		if(empty($username) || empty($password)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('USR_001');
			return $resData;
		}else if(strcmp($password, $confirmPassword) != 0) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('USR_002');
			return $resData;
		} else if(empty($email)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('USR_003');
			return $resData;
		}


		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));

			// Check if username already exists
			$results = $cb_obj->view("user", "findUserByUsername", array('key' => $username));
//debugDummy("createUser: results: " .count($results['rows']));
			if (count($results['rows']) > 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('USR_004');
				return $resData;
			}

			// Get next userId from db
                        $userId = $this->IdCounts->internalGetNextUserId();
//debugDummy("createUser: userId: " .$userId);

			if ($userId != 0) {
                        // Generate json document for new user
                        	$lastDate = date("Y-m-d H:i:s");
                        	$myDocArray = array('type' => DOC_TYPE_USER_PROFILE,
                                                        'userId' => $userId,
                                                        'username' => $username,
                                                        'password' => doEncryption($password),
                                                        'email' => $email,
                                                        'profileUrl' => $profileUrl,
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
                                $docKey = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;
                                $cb_obj->add($docKey, $myDoc);

				// Generate session token - need to implement
				$sessionToken = "sessionTokenPlaceHolder";

				// Now create Personal Channel for new user
				$_POST['sessionToken'] = "abc"; // NAM: need to generate session token before
				$_POST['descriptionText'] = $username . "'s personal channel";
				$_POST['name'] = $username;
				$_POST['categoryId'] = CHANNEL_PERSONAL_CHANNEL_ID;
				$_POST['userId'] = $userId;
				$_POST['coverphotoUrls'] = "";
				$_POST['personalChannel'] = API_TRUE;
				$_POST['isPublic'] = API_TRUE;
				$myRes = $this->Channel->createChannel();
				if ($myRes['_responseStatus'] != (integer)true) {
					// Create person channel failed
					debugDummy("createUser: failed to create personal channel for user: " . $docKey);
					debugDummy("createUser: failed to create personal channel for msgCode: " . $myRes['msgCode']);
					debugDummy("createUser: failed to create personal channel for msg: " . $myRes['msg']);
					$resData['_responseStatus'] = (integer)false;
					list($resData['msgCode'], $resData['msg']) = generateError('USR_011');
                                	return $resData;
				}


				// Return success and the userId
				$resData['_responseStatus'] = (integer)true;
				$resData['sessionToken'] = $sessionToken;
				$resData['userId'] = $userId;
				$resData['userJson'] = $myDoc;
				$resData['channelId'] = $myRes['channelId'];
				$resData['channelDocId'] = $myRes['channelDocId'];
				$resData['channelJson'] = $myRes['channelJson'];
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

	function updateUser() {
		$resData = array();
//		$userData = $this->getUserDetails('token', $this->input->post('tokenId'));
		$sessionToken = $this->input->post('sessionToken');
		$userId = $this->input->post('userId');
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		$confirmPassword = $this->input->post('confirmPassword');
		$oldPassword = $this->input->post('oldPassword');
		$email = $this->input->post('email');
		$profileUrl = $this->input->post('profileUrl');

		// exception handling
		if(empty($sessionToken)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('USR_006');
			return $resData;
		} else if(empty($userId)) {
			$resData['_responseStatus'] = (integer)false;
			list($resData['msgCode'], $resData['msg']) = generateError('USR_007');
			return $resData;
		} else if(!empty($password)) {
			if(strcmp($password, $confirmPassword) != 0) {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('USR_002');
				return $resData;
			}
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

                        $docKey = DOC_KEY_USER_PROFILE . COUCHBASE_KEY_DELIM . $userId;
//debugDummy("NAM: updateUser: docKey: " . $docKey);

                        // Get user profile document from db
                        $myDoc = $cb_obj->get($docKey);
 			if($myDoc) {
     				$doc = json_decode($myDoc, true);
			} else {
				$resData['_responseStatus'] = (integer)false;
				list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
				return $resData;
			}

			// Update username if changed
			if (!empty($username)) {
				if (strcmp($username, $doc['username']) != 0) {
					// Check if username already exists
					$results = $cb_obj->view("user", "findUserByUsername", array('key' => $username));
//debugDummy("updateUser: results: " .count($results['rows']));
					if (count($results['rows']) > 0) {
						$resData['_responseStatus'] = (integer)false;
						list($resData['msgCode'], $resData['msg']) = generateError('USR_004');
						return $resData;
					}
					$doc['username'] = $username;
					//debugDummy("updateUser: updating username to: " . $username);
				}
			}

			// Update password if changed
			if (!empty($password) && !empty($confirmPassword)) {
				// Already checked password == confirmPassword
				// Verify oldPassword == document password
				$oldPassword = doEncryption($oldPassword);
				$docPassword = $doc['password'];
//debugDummy("NAM: updateUser: docPassword: " . $docPassword);
//debugDummy("NAM:updateUserlogin: password: " . $password);
				if (strcmp($oldPassword, $docPassword) != 0) {
					$resData['_responseStatus'] = (integer)false;
					list($resData['msgCode'], $resData['msg']) = generateError('USR_008');
					return $resData;
				}
				// Set password to new password
				$doc['password'] = doEncryption($password);
				//debugDummy("updateUser: updating password to: " . $password);
			}

			// Update Email
			if (!empty($email)) {
				$doc['email'] = $email;
				//debugDummy("updateUser: updating email to: " . $email);
			}


			// Update profileUrl
			if (!empty($profileUrl)) {
				$doc['profileUrl'] = $profileUrl;
				//debugDummy("updateUser: updating profileUrl to: " . $profileUrl);
			}
				


			if ($userId != 0) {
                        	// Generate json document for new user
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

				// Return success and the userId
				$resData['_responseStatus'] = (integer)true;
				$resData['sessionToken'] = $sessionToken;
				$resData['userId'] = $userId;
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
		

	function getConversation() {
		$resData = array();
		$userData = $this->getUserDetails('token', $this->input->post('tokenId'));
		$userId = $this->input->post('userId');
		$friendId = $this->input->post('friendId');
		$conversationId = $this->input->post('conversationId');
		$offset = intval($this->input->post('offset'));
		$limit = $this->input->post('limit');

		if (empty($offset))
			$offset = CHAT_MAX_MESSAGE_ID;
		if (empty($limit))
			$limit = CHAT_DEFAULT_LIMIT;
		else if ($limit > CHAT_MAX_LIMIT) 
			$limit = CHAT_MAX_LIMIT;

		// exception handling
		if(empty($userData) || empty($userId)) {
			$resData['resStatus'] = (integer)false;		
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			return $resData;
		}else if($userId != $userData->id){
			$resData['resStatus'] = (integer)false;		
			list($resData['msgCode'], $resData['msg']) = generateError('CHT_001');
			return $resData;
		}

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_chat_bucket'));


			// If conversationId passed in use it otherwise find conversation from user/friend ids
			$docKey = false;
			if (empty($conversationId)) {
				$myKey = array($userId,$friendId);
				$results = $cb_obj->view("conversations", "find_by_users", array('key' => $myKey));
				if ($results != false) {
    					foreach($results['rows'] as $row) {
						$docKey = $row['id'];
//debugDummy("sendChat: conversation found: ". $row['id']);
						break;
					}
				}
			} else {
				// use conversationId passed over, validate the conversation exists and userId is part of the conversation
				$docKey = $conversationId;
//debugDummy("find conversation by id: ".$docKey);
       	 			$doc = $cb_obj->get($docKey);
       	 			if($doc) {
            				$doc = json_decode($doc, true);
					if (($userId != $doc['userA']) && ($userId != $doc['userB'])) {
						$resData['resStatus'] = (integer)false;		
						list($resData['msgCode'], $resData['msg']) = generateError('CHT_005');
						return $resData;
					}
				} else {
					$resData['resStatus'] = (integer)false;		
					list($resData['msgCode'], $resData['msg']) = generateError('CHT_004');
					return $resData;
				}
			}

			// OK got document conversation so now find the appropriate message records for that conversation
			if ($docKey != false) {
				$myStartKey = array($docKey, $offset);
				$myEndKey = array($docKey, 0);
				$skip = 0;
				if ($offset != CHAT_MAX_MESSAGE_ID)
					$skip = 1;

				$results = $cb_obj->view("conversations", "get_messages", array('startkey' => $myStartKey,
												'endkey' => $myEndKey,
												'descending' => 'true',
												'skip' => $skip,
												'limit' => $limit));
/****
$myVar = urldecode( $cb_obj->viewGenQuery("conversations", "get_messages", array('startkey' => $myStartKey,
                                                                                                'endkey' => $myEndKey,
                                                                                                'descending' => 'true',
												'skip' => $skip,
                                                                                                'limit' => $limit)));
*****/
//debugDummy("NAM: ".$myVar);

    				foreach($results['rows'] as $row) {
	       	 			// Load the full document by the ID
					unset($doc);
       		 			$doc = $cb_obj->get($row['id']);
       		 			if($doc) {
       		     				// Decode the JSON string into a PHP array
       		     				$doc = json_decode($doc, true);
						$resData['messages'][] = array(
       		         				'userId' => $doc['userA'],
       		         				'friendId' => $doc['userB'],
       		         				'conversationId' => $doc['conversation'],
       		         				'messageId' => $doc['messageId'],
       		         				'date' => $doc['lastDate'],
       		         				'docId' => $row['id'],
							'qbUser' => $doc['qbUserA'],
							'qbFriend' => $doc['qbUserB'],
							'message' => $doc['message']
       		     				);
       		 			}
				}
				$resData['resStatus'] = (integer)true;		
			} else {
				// Should not fail here, if it does something is badly wrong so throw an error
				$resData['resStatus'] = (integer)false;		
				list($resData['msgCode'], $resData['msg']) = generateError('CHT_003');
				return $resData;
			}

		} catch (CouchbaseException $e) {
			$resData['resStatus'] = (integer)false;		
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_001');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}


	function sendChat() {
		$resData = array();
		$userData = $this->getUserDetails('token', $this->input->post('tokenId'));
		$userId = $this->input->post('userId');
		$friendId = $this->input->post('friendId');
		$qbuserId = $this->input->post('qbUser');
		$qbfriendId = $this->input->post('qbFriend');
		$message = $this->input->post('message');

		// exception handling
		if(empty($userData) || empty($userId) || empty($friendId) || empty($message)){
			$resData['resStatus'] = (integer)false;		
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			return $resData;
		}else if($userId != $userData->id){
			$resData['resStatus'] = (integer)false;		
			list($resData['msgCode'], $resData['msg']) = generateError('CHT_001');
			return $resData;
		}

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_chat_bucket'));


			$myKey = array($userId,$friendId);
			$results = $cb_obj->view("conversations", "find_by_users", array('key' => $myKey));
    			
			$docKey = false;
			if ($results != false) {
    				foreach($results['rows'] as $row) {
       	 				$doc = $cb_obj->get($row['id']);
       	 				if($doc) {
       	     					// Decode the JSON string into a PHP array
            					$doc = json_decode($doc, true);
						$docKey = $row['id'];
//debugDummy("sendChat: conversation found: ". $row['id']);
						// Existing conversation exists so use the conversation Id to link the new message
						// update lastDate of conversation
						$doc['lastDate'] = date("Y-m-d H:i:s");
						$myDoc = json_encode($doc);
						if ($myDoc == false) {
							$resData['resStatus'] = (integer)false;		
							list($resData['msgCode'], $resData['msg']) = generateError('CHT_002');
							return $resData;
						}
						$cb_obj->replace($docKey, $myDoc);
					
						break;
					}
				}
				if ($docKey === false) {
					// Existing conversation between users does not exist so create a new conversation
//debugDummy("sendChat: conversation not found");
					$conversationId_key = COUCHBASE_CHAT_DOC . COUCHBASE_KEY_DELIM . COUCHBASE_CONVERSATIONID_COUNT;
					$conversationId = $cb_obj -> increment($conversationId_key);
//debugDummy("sendChat: conversationId: " .$conversationId);
					
					// Generate json document for new conversation
					$lastDate = date("Y-m-d H:i:s");
					$myDocArray = array('type' => COUCHBASE_CHAT_CONVERSATION_TYPE,
							'userA' => $userId,
							'userB' => $friendId,
							'qbUserA' => $qbuserId,
							'qbUserB' => $qbfriendId,
							'conversationId' => $conversationId,
							'lastDate' => $lastDate
							);
					$myDoc = json_encode($myDocArray);
					if ($myDoc == false) {
						$resData['resStatus'] = (integer)false;		
						list($resData['msgCode'], $resData['msg']) = generateError('CHT_002');
						return $resData;
					}
					
					// Generate docKey and add to DB
					$docKey = COUCHBASE_CHAT_DOC . COUCHBASE_KEY_DELIM . COUCHBASE_CHAT_DOC . "#" . $conversationId;
					$cb_obj->add($docKey, $myDoc);

				}
						
			}

			if ($docKey != false) {
				// Now generate the chat document and store in DB
				$chatId_key = COUCHBASE_CHAT_DOC . COUCHBASE_KEY_DELIM . COUCHBASE_CHATID_COUNT;
				$chatId = $cb_obj -> increment($chatId_key);
//debugDummy("sendChat: conversationId: " .$chatId);

				// Generate json document for new conversation
				$lastDate = date("Y-m-d H:i:s");
				unset($myDocArray);
				unset($myDoc);
				$myDocArray = array('type' => COUCHBASE_CHAT_MESSAGE_TYPE,
						'userA' => $userId,
						'userB' => $friendId,
						'qbUserA' => $qbuserId,
						'qbUserB' => $qbfriendId,
						'messageId' => $chatId,
						'message' => $message,
						'conversation' => $docKey,
						'lastDate' => $lastDate
						);
				$myDoc = json_encode($myDocArray);
				if ($myDoc == false) {
					$resData['resStatus'] = (integer)false;		
					list($resData['msgCode'], $resData['msg']) = generateError('CHT_002');
					return $resData;
				}
				
				// Generate docKey and add to DB
				$docKey = COUCHBASE_CHAT_DOC . COUCHBASE_KEY_DELIM . COUCHBASE_CHAT_MESSAGE_DOC . "#" . $chatId;
				$cb_obj->add($docKey, $myDoc);

				$resData['resStatus'] = (integer)true;		

			} else {
				// Should not fail here, if it does something is badly wrong so throw an error
				$resData['resStatus'] = (integer)false;		
				list($resData['msgCode'], $resData['msg']) = generateError('CHT_003');
				return $resData;
			}

		} catch (CouchbaseException $e) {
			$resData['resStatus'] = (integer)false;		
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_001');
			debugDummy("CouchBase Error: " + $e);
		}
		
		return $resData;
	}


	function getAllConversations() {
		$resData = array();
		$resData['conversations'] = array();

		$userData = $this->getUserDetails('token', $this->input->post('tokenId'));
		$userId = $this->input->post('userId');

		// exception handling
		if(empty($userData) || empty($userId)){
			$resData['resStatus'] = (integer)false;		
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_002');
			return $resData;
		}else if($userId != $userData->id){
			$resData['resStatus'] = (integer)false;		
			list($resData['msgCode'], $resData['msg']) = generateError('CHT_001');
			return $resData;
		}

		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_chat_bucket'));

			$results = $cb_obj->view("conversations", "find_userA", array('key' => $userId));
			$userB = $cb_obj->view("conversations", "find_userB", array('key' => $userId));
			foreach($userB['rows'] as $row) {
				$results['rows'][] = $row;
			}
    			
    			// Build the response string for each active conversation
    			foreach($results['rows'] as $row) {
       	 			// Load the full document by the ID
       	 			$doc = $cb_obj->get($row['id']);
       	 			if($doc) {
       	     				// Decode the JSON string into a PHP array
            				$doc = json_decode($doc, true);
					if ($userId == $doc['userA']) {
						$friendId = $doc['userB'];
						$qbUserId = $doc['qbUserA'];	
						$qbFriendId = $doc['qbUserB'];	
					} else {
						$friendId = $doc['userA'];
						$qbUserId = $doc['qbUserB'];	
						$qbFriendId = $doc['qbUserA'];	
					}

					// get userid of user
					$friendUserData = $this->getUserDetails('id', $friendId);
					if(empty($friendUserData)){
						debugDummy("getAllConversations: failed to retrieve user data for userId: " . $friendId);
					} else {
						$resData['conversations'][] = array(
       	         					'userId' => $userId,
       	         					'friendId' => $friendId,
							'friendUsername' => $friendUserData->username,
							'friendName' => $friendUserData->name,
							'friendAvatar' => $friendUserData->avatar,
							'friendProfilePhoto' => $friendUserData->profilePhoto,
                					'conversationId' => $doc['conversationId'],
                					'lastDate' => $doc['lastDate'],
                					'docId' => $row['id'],
							'qbUser' => $qbUserId,
							'qbFriend' => $qbFriendId
            					);
					}
        			}
			}
			$resData['resStatus'] = (integer)true;		
		} catch (CouchbaseException $e) {
			$resData['resStatus'] = (integer)false;		
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_001');
			debugDummy("CouchBase Error: " + $e);
		}
		
		return $resData;
	}

}
?>
