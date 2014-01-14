<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class My_Controller extends CI_Controller
{
	var $userInfo;
	
	function My_Controller()
	{
		
		//Parent Constructor
		parent::__construct();
		
		//Redirect to secure in case of instances under load balancer and https
	        $httpheaders = $this->input->request_headers();
       		if (array_key_exists('X-forwarded-proto', $httpheaders) &&  $this->input->get_request_header('X-forwarded-proto') == 'http') {
        		redirect($this->config->item('base_url') . $_SERVER['REQUEST_URI'], 'location', 301);
		}
		
		//Load Memcached driver and check its support
		if($this->config->item('useMemcached'))
		{
			//$cacheInfo = array();
			$this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
			if($this->cache->is_supported('memcached'))
			{				
				$cacheInfo = $this->cache->cache_info();
				//if ((!$this->cache->is_supported('memcached')) && (!is_array($cacheInfo)))
				if(!is_array($cacheInfo))
					$this->config->set_item('useMemcached', false);
	        }
			else
				$this->config->set_item('useMemcached', false);			       	
		}
		
		/* check if token expired or don't*/
		if($this->input->post('tokenId') != ''){
			$tokenID = $this->input->post('tokenId');
			$isTokenExpired = $this->user->isTokenExpired($tokenID);
			if($isTokenExpired){
				$data['resStatus'] = (integer)false;
				list($data['msgCode'], $data['msg']) = generateError('USR_031');
				$data['xmlResponse'] = "user/login";
				$responseXML = $this->load->view('xmlbase',$data, TRUE);
				echo encryptResponse($responseXML);			
				exit;
			}
		
		}
		
                       
		//Check for restricted access
		$restrictedAccess = $this->config->item('restrictedAccess');
		$flag = true;
		if ($restrictedAccess)
		{
			$flag = $this->checkRestriction();			
		}
		
		if(!$flag)
		{
			//$response = $this->load->view('error_404', array(), true);
			//echo $response;
			$this->lang->load('message');
			$this->load->helper('common');			
			$data['resStatus'] = (integer)false;
			$data['tokenId']='';			
			list($data['msgCode'], $data['msg']) = generateError('USR_018');
			$data['xmlResponse'] = "user/login";
			$responseXML = $this->load->view('xmlbase',$data, TRUE);
			echo encryptResponse($responseXML);			
			exit;
		}
		else
		{
			// mark start time for benchmarking response time
			$this->benchmark->mark('request_start');
			
			$this->load->model('audit');
			$this->load->library('sentry');
			$this->lang->load('message');
			
			// a list of unlocked (ie: not password protected) controllers.  We assume
			// controllers are locked if they aren't explicitly on this list
			$unlocked = array('website', 'user','crons');
			
			$authenticateRequests = $this->config->item('authenticateRequests');
			if (isset($authenticateRequests) && ($authenticateRequests === true)) 
			{
				$request = $this->uri->uri_string();
				if ((! in_array(strtolower(get_class($this)), $unlocked)) && (strstr($request,"/test/") === false))
				{
					$userInfo = $this->sentry->checkAuthentication();
					if (($userInfo !== false) && (is_object($userInfo)))
					{
						// user is logged in. user has access to everything
	
						// store user as an object property since we need user info in destructor
						$this->userInfo = $userInfo;
					} else 
					{
						// user is not logged in. return error. 
						$data['tokenId'] = $this->input->post('tokenId');
						$data['resStatus'] = 0;
						list($data['msgCode'], $data['msg']) = generateError('USR_013');
						$responseXML = $this->load->view('xmlbase',$data, TRUE);
						echo $responseXML;
						exit;
					}
				}
			}
		}
	}

	function checkRestriction()
	{
		//return false;
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
		    header('WWW-Authenticate: Basic realm="website"');
		    header('HTTP/1.0 401 Unauthorized');
			return false;
		} else {
			if ($_SERVER['PHP_AUTH_USER'] == $this->config->item('rusername') && $_SERVER['PHP_AUTH_PW'] == $this->config->item('rpassword'))
			    return true;
			else
				return false;
		}
	}

	function audit($data=array())
	{
		// check audit config parameter to decide whether to audit
		$audit = $this->config->item('audit');
		if (isset($audit) && ($audit === true)) {
			// mark end time for benchmarking response time
			$this->benchmark->mark('request_end');
	    	$request = $this->uri->uri_string();
	    	$elapsedTime = $this->benchmark->elapsed_time('request_start','request_end')*1000;
			$tokenId = $this->input->post('tokenId');
			if (is_object($this->userInfo) && (isset($this->userInfo->id))) 
			{
				$userId = $this->userInfo->id;
			} else {
				$userId = 0;
			}
			$this->audit->recordRequestTime($request, $elapsedTime, $tokenId, $userId);
		}
	}

}
?>
