<?php
/*
 * Created on Oct 24, 2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class website extends My_Controller {
	public $_dataArr = array();
	function website()
	{
		parent::__construct();
		$this->load->helper('common');
		$this->load->helper('debug');
	}
	
	function index()
	{
		$this->load->view('static2/index');
	}
	
	function healthCheck()
        {
                $this->load->view('static2/healthCheck');
        }

	function apis()
	{
		if($this->config->item('Environment') != 'PROD'){
			$this->load->view('apiviews/vmain');
		}else{
			redirect('/', 'refresh');
		}
	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */
