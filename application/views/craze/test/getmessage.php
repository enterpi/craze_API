<?php
/*
 * getmessage.php
 *
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Get Message</h1>
		<p><a href="<?php echo base_url()?>kazaana">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<p><b><i>Client side validations not considered in the API's.</i></b></p>
	<form name="getMessage" action="<?php echo site_url('messages/getmessage');?>" method="post">
		<p>Token: <input type="text" name="tokenId" value="" id="tokenId"/> </p>
		<p>MessageId: <input type="text" name="messageId" value="" id="messageId"/> </p>
		<p>Set Read Flag As: 
			<select name="readFlag"/>
			<option value="">No Change</option>
			<option value="0">0</option>
		   	<option value="1">1</option>
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