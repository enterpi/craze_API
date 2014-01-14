<?php
/*
 * getAllPosts.php
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Get All Posts</h1>
		<p><a href="<?php echo base_url()?>website/apis">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<form name="getAllPosts" action="<?php echo site_url('posts/getAllPosts');?>" method="post">
		<p>SessionToken: <input type="text" name="sessionToken" value="" id="sessionToken"/> </p>
		<p>User Id: <input type="text" name="userId" value="" id="userId"/> </p>

		<p>Offset: <input type="text" name="offset" value="" id="offset"/> </p>
		<p>Offset Doc Id: <input type="text" name="offsetDocId" value="" id="offsetDocId"/> </p>
		<p>Limit: <input type="text" name="limit" value="" id="limit"/> </p>
		<p>Order By: <input type="text" name="orderBy" value="" id="orderBy"/> </p>

		<p><input name="submit" type="submit" value="Submit" /></p>
	</form>
<?php $this->load->view('apiviews/footer'); ?>	
