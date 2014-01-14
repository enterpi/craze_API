<?php
/*
 * updatePost.php
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Update Post</h1>
		<p><a href="<?php echo base_url()?>website/apis">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<form name="updatePost" action="<?php echo site_url('posts/updatePost');?>" method="post">
		<p>SessionToken: <input type="text" name="sessionToken" value="" id="sessionToken"/> </p>
		<p>User Id: <input type="text" name="userId" value="" id="userId"/> </p>
		<p>Post Id: <input type="text" name="postId" value="" id="postId"/> </p>
		<p>Post Doc Id: <input type="text" name="postDocId" value="" id="postDocId"/> </p>
		<p>Channel Id: <input type="text" name="channelId" value="" id="channelId"/> </p>
		<p>Channel Doc Id: <input type="text" name="channelDocId" value="" id="channelDocId"/> </p>
		<p>Post Title: <input type="text" name="postTitle" value="" id="postTitle"/> </p>
		<p>Post Text: <input type="text" name="postText" value="" id="postText"/> </p>
		<p>Original Asset URL: <input type="text" name="originalAsset" value="" id="originalAsset"/> </p>
		<p>Thumbnail Asset URL: <input type="text" name="thumbnailAsset" value="" id="thumbnailAsset"/> </p>
		<p>Asset Type: <input type="text" name="assetType" value="" id="assetType"/> </p>
		<p>hasAttachment (true, false): <input type="text" name="hasAttachment" value="" id="hasAttachment"/> </p>
		<p>Parent Post Id: <input type="text" name="parentPostId" value="" id="parentPostId"/> </p>
		<p>Post Sequence Id: <input type="text" name="postSeqId" value="" id="postSeqId"/> </p>
		<p>isSharedPost (true, falsoe): <input type="text" name="isSharedPost" value="" id="isSharedPost"/> </p> 
		<p>Shared From Post Doc: <input type="text" name="sharedFromPost" value="" id="sharedFromPost"/> </p>
		<p>isPublic Channel (1 true, 0 false): <input type="text" name="isPublic" value="" id="isPublic"/> </p>
		<p>Link Array: <input type="text" name="linkArray" value="" id="linkArray"/> </p>
		

		<p><input name="submit" type="submit" value="Submit" /></p>
	</form>
<?php $this->load->view('apiviews/footer'); ?>	
