<?php
/*
 * kid_registration.php
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Create User</h1>
		<p><a href="<?php echo base_url()?>website/apis">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<form name="createUser" action="<?php echo site_url('users/createUser');?>" method="post">
		<p>UserName: <input type="text" name="username" value="" id="username"/> </p>
		<p>profileUrl: <input type="text" name="profileUrl" value="" id="profileUrl"/> </p>
		<p>Email: <input type="text" name="email" value="" id="email"/> </p>
		
		<p>Password: <input type="text" name="password" value="" id="password"/> </p>
		<p>Confirm Password: <input type="text" name="confirmPassword" value="" id="confirmPassword"/> </p>
		

		<p><input name="submit" type="submit" value="Submit" /></p>
	</form>
<?php $this->load->view('apiviews/footer'); ?>	
