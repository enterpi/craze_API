<?php
/*
 * kid_registration.php
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Update User</h1>
		<p><a href="<?php echo base_url()?>website/apis">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<form name="updateUser" action="<?php echo site_url('users/updateUser');?>" method="post">
		<p>Session Token: <input type="text" name="sessionToken" value="" id="sessionToken"/> </p>
		<p>User Id: <input type="text" name="userId" value="" id="userId"/> </p>

		<p>UserName: <input type="text" name="username" value="" id="username"/> </p>
		<p>profileUrl: <input type="text" name="profileUrl" value="" id="profileUrl"/> </p>
		<p>Email: <input type="text" name="email" value="" id="email"/> </p>
		
		<p>Password: <input type="text" name="password" value="" id="password"/> </p>
		<p>Confirm Password: <input type="text" name="confirmPassword" value="" id="confirmPassword"/> </p>
		<p>Old Password: <input type="text" name="oldPassword" value="" id="oldPassword"/> </p>
		

		<p><input name="submit" type="submit" value="Submit" /></p>
	</form>
<?php $this->load->view('apiviews/footer'); ?>	
