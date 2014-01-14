<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Category extends CI_Model
{
	function Category()
	{
		parent::__construct();
	}
	
	/*
         * function: getAllCategories
         */
        function getAllCategories(){
                $resData = array();
                try {
			$docIds = "";
			$docsArray = array();

                        $cb_obj = new Couchbase($this->config->item('cb_hostname'), $this->config->item('cb_username'), $this->config->item('cb_password'), $this->config->item('cb_craze_bucket'));
                        $results = $cb_obj->view("category", "getAllCategories", array());
                        if ($results != false) {
                            foreach($results['rows'] as $row) {
debugDummy("DocId: " . $row['id']);
				$docIds = $docIds . ":" . $row['id'];
                                $docKey = $row['id'];
                                $doc = $cb_obj->get($docKey);
                                if($doc) {
					$docsArray[] = $doc;
                                } else {
debugDummy("got doc id from cb");
                                        $resData['resStatus'] = (integer)false;
                                        list($resData['msgCode'], $resData['msg']) = generateError('CAT_002');
                                        return $resData;
                                }
                            }
			}
				
			$resData['categories'] = $docsArray;
			$resData['categoryDocIds'] = $docIds;
			$resData['_responseStatus'] = (integer)true;		
                } catch (CouchbaseException $e) {
			$resData['_responseStatus'] = (integer)false;		
			list($resData['_responseErrorCode'], $resData['_responseErrorMessage']) = generateError('CAT_001');
                        debugDummy("CouchBase Error: " . $e);
                }

                return $resData;
        }
}
