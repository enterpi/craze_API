<?php
/*
 * updateChannel.php
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Update Channel</h1>
		<p><a href="<?php echo base_url()?>website/apis">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<form name="updateChannel" action="<?php echo site_url('channels/updateChannel');?>" method="post">
		<p>SessionToken: <input type="text" name="sessionToken" value="" id="sessionToken"/> </p>
		<p>User Id: <input type="text" name="userId" value="" id="userId"/> </p>
		<p>Channel Id: <input type="text" name="channelId" value="" id="channelId"/> </p>
		<p>Channel Doc Id: <input type="text" name="channelDocId" value="" id="channelDocId"/> </p>
		<p>Name: <input type="text" name="name" value="" id="name"/> </p>
		<p>Description: <input type="text" name="descriptionText" value="" id="descriptionText"/> </p>
		<p>Category Doc Id: <input type="text" name="categoryId" value="" id="categoryId"/> </p>
		<p>coverphotoUrls: <input type="text" name="coverphotoUrls" value="" id="coverphotoUrls"/> </p>
		<p>Personal Channel (1 true, 0 false): <input type="text" name="personalChannel" value="" id="personalChannel"/> </p>
		<p>isPublic Channel (1 true, 0 false): <input type="text" name="isPublic" value="" id="isPublic"/> </p>
		

		<p><input name="submit" type="submit" value="Submit" /></p>
	</form>
<?php $this->load->view('apiviews/footer'); ?>	
