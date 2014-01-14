<?php 
if ($resStatus == 1) {
	$messageresult = $result[0];
	echo '<messagecount>' . $messageresult->messagecount . '</messagecount>';
} 
?>