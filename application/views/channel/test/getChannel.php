<?php
/*
 * getChannel.php
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Get Channel</h1>
		<p><a href="<?php echo base_url()?>website/apis">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<form name="getChannel" action="<?php echo site_url('channels/getChannel');?>" method="post">
		<p>Session Token: <input type="text" name="sessionToken" value="" id="sessionToken"/> </p>
		<p>User Id: <input type="text" name="userId" value="" id="userId"/> </p>

		<p>Channel Id: <input type="text" name="channelId" value="" id="channelId"/> </p>
		<p>Channel Doc Id: <input type="text" name="channelDocId" value="" id="channelDocId"/> </p>

		<p><input name="submit" type="submit" value="Submit" /></p>
	</form>
<?php $this->load->view('apiviews/footer'); ?>	
