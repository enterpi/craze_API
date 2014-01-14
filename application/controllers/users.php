<?php
/*
 * user.php
 */

class Users extends My_Controller {

	function Users()
	{
		parent::__construct();
		$this->load->model('user');
		$this->lang->load('message');
		$this->load->helper('common');
		$this->load->helper('debug');
		$this->load->helper('string');

	}
	
	function index()
	{
		echo "<p>Inside User</p>";
	}
	
	function test($action='', $type='')
	{
		if($this->config->item('Environment') == 'PROD'){
			redirect('/', 'refresh');
		}
		switch($action)
		{
			case 'login':
				$this->load->view('user/test/login');
				break;				
			case 'logout':
				$this->load->view('user/test/logout');
				break;				
			case 'updateUser':
				$this->load->view('user/test/updateUser');
				break;				
			case 'createUser':
				$this->load->view('user/test/createUser');
				break;				
			case 'getUser':
				$this->load->view('user/test/getUser');
				break;				
			case 'getAllUsers':
				$this->load->view('user/test/getAllUsers');
				break;				
			default:
				echo '<info><result='.$action.'>'.$this->lang->line('PageNotFound').'</result></info>';
				break;		
		}
	}	
	
	function login() 
	{
		$data = $this->user->login();
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function logout() 
	{
		$data = $this->user->logout();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function updateUser() 
	{
		$data = $this->user->updateUser();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function createUser() 
	{
		$data = $this->user->createUser();
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function getUser() 
	{
		$data = $this->user->getUser();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function getAllUsers() 
	{
		$data = $this->user->getAllUsers();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

}

/* End of file  */
