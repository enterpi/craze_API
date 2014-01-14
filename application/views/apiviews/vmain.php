<?php
/*
 * Created on Oct 24, 2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>
<?php $this->load->view('apiviews/header'); ?>
	<div class="headwrap">
		<h1>Craze Test APIs</h1>
	</div>
	
	<!-- All Feature Views -->
	<hr />
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td><?php $this->load->view('apiviews/vcore'); ?></td>
			<td><?php $this->load->view('apiviews/vuser'); ?></td>
			<td><?php $this->load->view('apiviews/vchannel'); ?></td>
		</tr>
		<tr>
			<td><?php $this->load->view('apiviews/vpost'); ?></td>
		</tr>
	</table>
	<hr />
<?php $this->load->view('apiviews/footer'); ?>	
