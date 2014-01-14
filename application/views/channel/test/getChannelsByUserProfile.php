<?php
/*
 * getChannelsByUserProfile.php
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Get Channels By User Profile</h1>
		<p><a href="<?php echo base_url()?>website/apis">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<form name="getChannelsByUserProfile" action="<?php echo site_url('channels/getChannelsByUserProfile');?>" method="post">
		<p>Session Token: <input type="text" name="sessionToken" value="" id="sessionToken"/> </p>
		<p>User Id: <input type="text" name="userId" value="" id="userId"/> </p>

		<p>Request User Id: <input type="text" name="requestUserId" value="" id="requestUserId"/> </p>
		<p>Request User Doc Id: <input type="text" name="requestUserDocId" value="" id="requestUserDocId"/> </p>

		<p><input name="submit" type="submit" value="Submit" /></p>
	</form>
<?php $this->load->view('apiviews/footer'); ?>	
