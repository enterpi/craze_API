<?php
/*
 * posts.php
 */

class Posts extends My_Controller {

	function Posts()
	{
		parent::__construct();
		$this->load->model('post');
		$this->lang->load('message');
		$this->load->helper('common');
		$this->load->helper('debug');
		$this->load->helper('string');

	}
	
	function index()
	{
		echo "<p>Inside Post</p>";
	}
	
	function test($action='', $type='')
	{
		if($this->config->item('Environment') == 'PROD'){
			redirect('/', 'refresh');
		}
		switch($action)
		{
			case 'getAllPosts':
				$this->load->view('post/test/getAllPosts');
				break;				
			case 'getPost':
				$this->load->view('post/test/getPost');
				break;				
			case 'createPost':
				$this->load->view('post/test/createPost');
				break;				
			case 'updatePost':
				$this->load->view('post/test/updatePost');
				break;				
			case 'deletePost':
				$this->load->view('post/test/deletePost');
				break;				
			case 'undeletePost':
				$this->load->view('post/test/undeletePost');
				break;				
			case 'likePost':
				$this->load->view('post/test/likePost');
				break;				
			case 'unlikePost':
				$this->load->view('post/test/unlikePost');
				break;				
			case 'addComment':
				$this->load->view('post/test/addComment');
				break;				
			case 'deleteComment':
				$this->load->view('post/test/deleteComment');
				break;				
			case 'sharePost':
				$this->load->view('post/test/sharePost');
				break;				
			case 'unsharePost':
				$this->load->view('post/test/unsharePost');
				break;				
			default:
				echo '<info><result='.$action.'>'.$this->lang->line('PageNotFound').'</result></info>';
				break;		
		}
	}	
	
	function getPost() 
	{
		$data = $this->post->getPost();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function deletePost() 
	{
		$data = $this->post->deletePost();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function undeletePost() 
	{
		$data = $this->post->undeletePost();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function updatePost() 
	{
		$data = $this->post->updatePost();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function createPost() 
	{
		$data = $this->post->createPost();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function likePost() 
	{
		$data = $this->post->likePost();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function unlikePost() 
	{
		$data = $this->post->unlikePost();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function addComment() 
	{
		$data = $this->post->addComment();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function deleteComment() 
	{
		$data = $this->post->deleteComment();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function sharePost() 
	{
		$data = $this->post->sharePost();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function unsharePost() 
	{
		$data = $this->post->unsharePost();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}

	function getAllPosts() 
	{
		$data = $this->post->getAllPosts();
		$data['sessionToken'] = $this->input->post('sessionToken');
		$responseJson = json_encode($data);
		echo encryptResponse($responseJson);
		$this->audit();
	}


}

/* End of file  */
