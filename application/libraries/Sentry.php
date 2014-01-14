<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 *
 	Author: Pawan Rote
	Email Id: pawan_rote@neovasolutions.com
 */
class Sentry
{
	function Sentry()
	{
		$CI =& get_instance();
		$CI->load->model('user');
	}

	function checkAuthentication()
	{
		$CI = &get_instance();
		$userInfoRet = $CI->user->isLoggedIn();
		
		if (is_array($userInfoRet) && isset($userInfoRet['resStatus']) && ($userInfoRet['resStatus'] == '1') && isset($userInfoRet['result'])) 
		{
			// user is logged in
			$userInfo = $userInfoRet['result'];
			return $userInfo;
			
		} else 
		{
			return false;
		}
		
	}
}

/* End of file sentry.php */
/* Location: ./application/libraries/sentry.php */
