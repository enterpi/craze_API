<?php
/*
 * undeletePost.php
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Undelete Post</h1>
		<p><a href="<?php echo base_url()?>website/apis">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<form name="undeletePost" action="<?php echo site_url('posts/undeletePost');?>" method="post">
		<p>SessionToken: <input type="text" name="sessionToken" value="" id="sessionToken"/> </p>
		<p>User Id: <input type="text" name="userId" value="" id="userId"/> </p>
		<p>Post Id: <input type="text" name="postId" value="" id="postId"/> </p>
		<p>Post Doc Id: <input type="text" name="postDocId" value="" id="postDocId"/> </p>

		<p><input name="submit" type="submit" value="Submit" /></p>
	</form>
<?php $this->load->view('apiviews/footer'); ?>	
