<?php 
	//Response Header
	$this->load->view('xmlheader');
	
	//Actual Response
	if(isset($xmlResponse)) 
		$this->load->view($xmlResponse); 
	
	//Response Footer
	$this->load->view('xmlfooter');
?>