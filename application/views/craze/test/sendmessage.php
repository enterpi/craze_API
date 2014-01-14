<?php
/*
 * sendmessage.php
 *
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Send Message</h1>
		<p><a href="<?php echo base_url()?>kazaana">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<p><b><i>Client side validations not considered in the API's.</i></b></p>
	<form name="sendMessage" action="<?php echo site_url('messages/sendmessage');?>" method="post">
		<p>Token Id: <input type="text" name="tokenId" value="" id="tokenId"/> </p>
		<p>To User id: <input type="text" name="toUsers" value="" id="toUsers"/>  (e.g. 1,3,6) </p>
		<p>Subject: <input type="text" name="subject" value="" id="subject"/> </p>
		<p>Message: <textarea name="message" id="message"/></textarea></p>
		<p>User Local time: <input type="text" value="" name="userLocalTime" id="userLocalTime"/>(e.g. 2012-05-24 10:12:15)</p>
		<p>MessageId (optional) (in case of Reply): <input type="text" name="parentMessageId" value="" id="messageId"/> </p>
		<p>Asset Id: <input type="text" value="" name="assetId" id="assetId"/> (Optional : e.g. 1,2 )</p>
		<p>Asset Source:
			<select name='attachmentSource'>
				<option value="album">album</option>
				<option value="thirdparty">thirdparty</option>
				<option value="default">default</option>
			</select> (If any asset attached)
		</p>		
		<p><input name="submit" type="submit" value="Submit" /></p>
	</form>
<?php $this->load->view('apiviews/footer'); ?>