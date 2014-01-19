<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Audit extends CI_Model
{
	function Audit()
	{
		parent::__construct();
	}
	
	function recordRequestTime($request, $elapsedTime, $tokenId, $userId) 
	{
		$auditData = array(
			'request' => $request,
			'requestTime' => $elapsedTime, 
			'token' => $tokenId, 
			'userId' => $userId,
		);
//		$this->db->insert('audit',$auditData);
	}
}
?>
