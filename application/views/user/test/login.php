<?php
/*
 * kid_registration.php
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>User Login</h1>
		<p><a href="<?php echo base_url()?>website/apis">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<form name="login" action="<?php echo site_url('users/login');?>" method="post">
		<p>UserName: <input type="text" name="username" value="" id="username"/> </p>
		
		<p>Password: <input type="text" name="password" value="" id="password"/> </p>
		

		<p><input name="submit" type="submit" value="Submit" /></p>
	</form>
<?php $this->load->view('apiviews/footer'); ?>	
