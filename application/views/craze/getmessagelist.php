<?php if ($resStatus == 1) {
	echo '<totalMessages>'. $totalMessages . "</totalMessages>\n";
	echo "<messages>\n";
	foreach ($result as $message) {
		echo " <message>\n";
		echo '  <messageId>' . $message->messageId . "</messageId>\n";
		echo '  <subject><![CDATA[' . $message->subject . "]]></subject>\n";
		echo '  <messageExcerpt><![CDATA[' . $message->messageExcerpt . "]]></messageExcerpt>\n";
		echo '  <restrictedView>' . $message->restrictedView . "</restrictedView>\n";
		echo '  <messageDate>' . $message->messageDate . "</messageDate>\n";
		echo '  <isRead>' . $message->isRead . "</isRead>\n";
		if (isset($message->fromUserInfo))
		{
			echo '  <fromUser>';
			echo '<id>' . $message->fromUserInfo->id . '</id>';
			echo '<name>' . xml_convert($message->fromUserInfo->name) . '</name>';
			echo '<avatar>' . $message->fromUserInfo->avatar . '</avatar>';
			echo "</fromUser>\n";		 
		}
		if (isset($message->toUserInfo) && (count($message->toUserInfo) > 0)) {
			echo "  <toUsers>\n";
			foreach ($message->toUserInfo as $toUser) 
			{
				echo '   <toUser>';
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
		echo " </message>\n";
	} 
	echo "</messages>\n";
} ?>