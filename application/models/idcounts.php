<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class IdCounts extends CI_Model
{
	function IdCounts()
	{
		parent::__construct();
	}
	
	/*
	 * function: initIdCounts
	 */
	function initIdCounts(){
		$resData = array();
/*******
		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_counts_bucket'));
			$cb_obj->set("user::nextId", 2000);
			$cb_obj->set("channel::nextId", 2000);
			$cb_obj->set("post::nextId", 2000);
		} catch (CouchbaseException $e) {
			$resData['resStatus'] = (integer)false;		
			list($resData['msgCode'], $resData['msg']) = generateError('ERR_001');
			debugDummy("CouchBase Error: " . $e);
		}
		
********/
		return $resData;
	}

	/*
	 * function: getNextUserId
	 */
	function getNextUserId(){
		$resData = array();
		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_counts_bucket'));
			$my_id = $cb_obj->increment("user::nextId");
			$resData['userId'] = $my_id;
			$resData['_responseStatus'] = (integer)true;		
		} catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;		
			list($resData['_responseErrorCode'], $resData['_responseErrorMessage']) = generateError('ERR_001');
			debugDummy("CouchBase Error: " . $e);
		}
		
		return $resData;
	}

	/*
         * function: getNextChannelId
         */
        function getNextChannelId(){
                $resData = array();
                try {
                        $cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_counts_bucket'));
                        $my_id = $cb_obj->increment("channel::nextId");
                        $resData['channelId'] = $my_id;
			$resData['_responseStatus'] = (integer)true;		
                } catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;		
			list($resData['_responseErrorCode'], $resData['_responseErrorMessage']) = generateError('ERR_001');
                        debugDummy("CouchBase Error: " . $e);
                }

                return $resData;
        }

	/*
         * function: getNextPostId
         */
        function getNextPostId(){
                $resData = array();
                try {
                        $cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_counts_bucket'));
                        $my_id = $cb_obj->increment("post::nextId");
                        $resData['postId'] = $my_id;
			$resData['_responseStatus'] = (integer)true;		
                } catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;		
			list($resData['_responseErrorCode'], $resData['_responseErrorMessage']) = generateError('ERR_001');
                        debugDummy("CouchBase Error: " . $e);
                }

                return $resData;
        }
	
	/*
	 * function: internalGetNextUserId
	 */
	function internalGetNextUserId(){
		try {
			$cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_counts_bucket'));
			$my_id = $cb_obj->increment("user::nextId");
		} catch (CouchbaseException $e) {
			debugDummy("CouchBase Error: " . $e);
			return 0;
		}
		
		return $my_id;
	}

        function internalGetNextChannelId(){
                try {
                        $cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_counts_bucket'));
                        $my_id = $cb_obj->increment("channel::nextId");
                } catch (CouchbaseException $e) {
                        debugDummy("CouchBase Error: " . $e);
			return 0;
                }

                return $my_id;
        }
	
	function internalGetNextPostId(){
                try {
                        $cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_counts_bucket'));
                        $my_id = $cb_obj->increment("post::nextId");
                } catch (CouchbaseException $e) {
                        debugDummy("CouchBase Error: " . $e);
			return 0;
                }

                return $my_id;
        }

}
