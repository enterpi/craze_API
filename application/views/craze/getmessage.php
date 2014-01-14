<?php 
if ($resStatus == 1) {
	$message = $result;
	echo "<message>\n";
	echo ' <messageId>' . $message->id . "</messageId>\n";
	echo ' <subject><![CDATA[' . $message->subject . "]]></subject>\n";
	echo ' <messageContent><![CDATA[' . $message->message . "]]></messageContent>\n";
	echo ' <messageDate>' . $message->messageDate . "</messageDate>\n";
	if (isset($message->fromUserInfo))
	{
		echo '<fromUser>';
		echo '<id>' . $message->fromUserInfo->id . '</id>';
		echo '<name>' . xml_convert($message->fromUserInfo->name) . '</name>';
		echo '<avatar>' . $message->fromUserInfo->avatar . '</avatar>'; 
		echo "</fromUser>\n";		 
	}
	if (isset($message->toUserInfo) && (count($message->toUserInfo) > 0)) {
		echo "  <toUsers>\n";
		foreach ($message->toUserInfo as $toUser) 
		{
			echo '<toUser>';
			echo '<id>' . $toUser->id . '</id>';
			echo '<name>' . xml_convert($toUser->name) . '</name>';
			echo '<avatar>' . $toUser->avatar . '</avatar>'; 
			echo "</toUser>\n";
		}
		echo "  </toUsers>\n";
	}
	if (!empty($message->attachments)) {
		echo "\n <attachments>";			
		foreach ($message->attachments as $attachment) {
        	echo "\n";
        	echo '  <attachment>';
        	echo '<attachmentId>' . $attachment->attachmentId . '</attachmentId>';
        	echo '<attachmentThumb>' . $attachment->attachmentThumb . '</attachmentThumb>';
        	echo '<attachmentAsset>' . $attachment->attachmentAsset . '</attachmentAsset>';
        	echo '<attachmentType>' . $attachment->assetType . '</attachmentType>';
        	echo "\n  </attachment>";
		}
        echo "\n </attachments>";
    }
	echo '</message>';

} 
?>