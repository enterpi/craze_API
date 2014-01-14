<?php
$encryptResponse = $this->config->item('encryptResponse');
if (isset($encryptResponse) && ($encryptResponse === true))
{
	header("Content-Type: text/html");
} else 
{
	header("Content-Type: application/xml");	
}

 
echo '<?xml version="1.0"?>' . "\n";
echo '<?xml-stylesheet type="text/xsl" href="'.base_url().'/css/response.xsl"?>' . "\n";
?>
<response>
	<home><?php echo base_url()."website" ?></home>
	<token><?php echo $tokenId?></token>
<?php if(isset($resStatus)){?>
	<resStatus><?php echo $resStatus?></resStatus>
<?php } if(isset($msgCode)) {?>
	<resMessage>
		<msgCode><?php echo $msgCode?></msgCode>
		<msg><?php echo $msg?></msg>
	</resMessage>
<?php }?>
