<?php
/*
 * getmessagecount.php
 *
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Get Message Count</h1>
		<p><a href="<?php echo base_url()?>kazaana">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<p><b><i>Client side validations not considered in the API's.</i></b></p>
	<form name="getMessageCount" action="<?php echo site_url('messages/getmessagecount');?>" method="post">
		<p>Token Id: <input type="text" name="tokenId" value="" id="tokenId"/> </p>
		<p>Message Type: <select name="msgType"/>
			<option value="u">Unread</option>
		   	<option value="r">Read</option>
		   	<option value="a">All</option>
		   </select>
		</p>
		<p>Sent Mail Folder: 
			<select name="sentMail"/>
			<option value="0">No</option>
		   	<option value="1">Yes</option>
		   	</select>
		</p>		
		<p><input name="submit" type="submit" value="Submit" /></p>
	</form>
<?php $this->load->view('apiviews/footer'); ?>