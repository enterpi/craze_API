<?php
/*
 * craze.php
 *
 */

class Craze extends My_Controller {

	function Craze()
	{
		parent::__construct();
		$this->load->model('idcounts');
		$this->load->model('category');
		$this->lang->load('message');
		$this->load->helper('common');
	}
	
	function index()
	{
		echo "<p>Inside Messages</p>";
	}
	
	function test($action='', $type='')
	{
		if($this->config->item('Environment') == 'PROD'){
			redirect('/', 'refresh');
		}
		switch($action)
		{
			case 'initIdCounts':
			    $this->load->view('craze/test/initIdCounts');
			    break;	
			case 'getNextUserId':
			    $this->load->view('craze/test/getNextUserId');
			    break;
			case 'getNextChannelId':
			    $this->load->view('craze/test/getNextChannelId');
			    break;			    
			case 'getNextPostId':
			    $this->load->view('craze/test/getNextPostId');
			    break;
			default:
				echo '<info><result='.$action.'>'.$this->lang->line('PageNotFound').'</result></info>';
				break;			
		}
	}

	function initIdCounts() 
	{
		$data = $this->idcounts->initIdCounts();
//		$data['tokenId'] = $this->input->post('tokenId');
//		$data['xmlResponse'] = "craze/getmessagelist";		
		$responseXML = $this->load->view('xmlbase',$data, TRUE);
		echo encryptResponse($responseXML);
		$this->audit();
	}

	function getNextUserId() 
	{
		$data = $this->idcounts->getNextUserId();
//		$data['tokenId'] = $this->input->post('tokenId');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function getNextChannelId() 
	{
		$data = $this->idcounts->getNextChannelId();
//		$data['tokenId'] = $this->input->post('tokenId');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}	

	function getNextPostId() 
	{
		$data = $this->idcounts->getNextPostId();
//		$data['tokenId'] = $this->input->post('tokenId');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function getAllCategories() 
	{
		$data = $this->category->getAllCategories();
//		$data['tokenId'] = $this->input->post('tokenId');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

}
?>
