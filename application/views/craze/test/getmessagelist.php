<?php
/*
 * getmessagelist.php
 *
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Get Message List</h1>
		<p><a href="<?php echo base_url()?>kazaana">Home</a></p>
		<div class="clrFix"></div>
	</div>
	<p><b><i>Client side validations not considered in the API's.</i></b></p>
	<form name="getMessageList" action="<?php echo site_url('messages/getmessagelist');?>" method="post">
		<p>Token: <input type="text" name="tokenId" value="" id="token"/> </p>
		<p>Message Offset (optional): <input type="text" name="msgOffset" value="" id="msgOffset"/> </p>
		<p>Number of Messages (optional): <input type="text" name="msgCount" value="" id="msgCount"/> </p>
		<p>Sent Mail Folder: 
			<select name="sentMail"/>
			<option value="0">No</option>
		   	<option value="1">Yes</option>
		   	</select>
		</p>	
		<p><input name="submit" type="submit" value="Submit" /></p>
	</form>
<?php $this->load->view('apiviews/footer'); ?>