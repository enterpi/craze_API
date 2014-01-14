<?php
/*
 * deleteComment.php
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Delete Comment</h1>
		<p><a href="<?php echo base_url()?>website/apis">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<form name="deleteComment" action="<?php echo site_url('posts/deleteComment');?>" method="post">
		<p>SessionToken: <input type="text" name="sessionToken" value="" id="sessionToken"/> </p>
		<p>User Id: <input type="text" name="userId" value="" id="userId"/> </p>
		<p>Comment Doc Id: <input type="text" name="commentDocId" value="" id="commentDocId"/> </p>

		<p><input name="submit" type="submit" value="Submit" /></p>
	</form>
<?php $this->load->view('apiviews/footer'); ?>	
