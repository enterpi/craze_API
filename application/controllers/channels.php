<?php
/*
 * channel.php
 */

class Channels extends My_Controller {

	function Channels()
	{
		parent::__construct();
		$this->load->model('channel');
		$this->lang->load('message');
		$this->load->helper('common');
		$this->load->helper('debug');
		$this->load->helper('string');

	}
	
	function index()
	{
		echo "<p>Inside Channels</p>";
	}
	
	function test($action='', $type='')
	{
		if($this->config->item('Environment') == 'PROD'){
			redirect('/', 'refresh');
		}
		switch($action)
		{
			case 'createChannel':
				$this->load->view('channel/test/createChannel');
				break;				
			case 'updateChannel':
				$this->load->view('channel/test/updateChannel');
				break;				
			case 'deleteChannel':
				$this->load->view('channel/test/deleteChannel');
				break;				
			case 'followChannel':
				$this->load->view('channel/test/followChannel');
				break;				
			case 'unfollowChannel':
				$this->load->view('channel/test/unfollowChannel');
				break;				
			case 'getChannel':
				$this->load->view('channel/test/getChannel');
				break;				
			case 'getAllChannels':
				$this->load->view('channel/test/getAllChannels');
				break;				
			case 'getChannelsByUserProfile':
				$this->load->view('channel/test/getChannelsByUserProfile');
				break;				
			default:
				echo '<info><result='.$action.'>'.$this->lang->line('PageNotFound').'</result></info>';
				break;		
		}
	}	
	
	function followChannel() 
	{
		$data = $this->channel->followChannel();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function unfollowChannel() 
	{
		$data = $this->channel->unfollowChannel();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function deleteChannel() 
	{
		$data = $this->channel->deleteChannel();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function updateChannel() 
	{
		$data = $this->channel->updateChannel();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function createChannel() 
	{
		$data = $this->channel->createChannel();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function getChannel() 
	{
		$data = $this->channel->getChannel();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function getAllChannels() 
	{
		$data = $this->channel->getAllChannels();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function getChannelsByUserProfile() 
	{
		$data = $this->channel->getChannelsByUserProfile();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}


}

/* End of file  */
