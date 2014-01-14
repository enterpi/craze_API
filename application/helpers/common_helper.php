<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function sendNotification($fromUserId,$toUserId,$subject,$msgBody){
	if($this->config->item('sendNotification')){
		return true;
	}
}

function naturalSortByName($a, $b) {
        $myRetVal =  strcasecmp($a['username'], $b['username']);
        return $myRetVal;
}

function generateError($errorCode) {
	$CI =& get_instance();
	$errorMessage = $CI->lang->line($errorCode);
	$ret = array($errorCode, $errorMessage);
	return $ret;
}

function sendPixyMail($data, $type) 
{
		$CI =& get_instance();
		//print_r($data);exit;
		// Emails can be turned off for development purpose	
		
		$sendEmail = $CI->config->item('sendEmail');

		if (isset($sendEmail) && ($sendEmail === false))
		{
			return true;
		}
		
		$CI->load->library('email');
		$protocol = $CI->config->item('protocol');
		$emailconfig = array(
			'protocol' => $protocol,
			'mailtype' => 'html',
			'charset' => 'iso-8859-1',
			'newline' => "\r\n"
		);
		if ($protocol == "smtp") {
			$emailconfig['smtp_host'] = $CI->config->item('smtp_host');
			$emailconfig['smtp_port'] = $CI->config->item('smtp_port');
			$emailconfig['smtp_user'] = $CI->config->item('smtp_user');
			$emailconfig['smtp_pass'] = $CI->config->item('smtp_pass');		
		}
		$CI->email->initialize($emailconfig);
		
		//Set parameters
		if (array_key_exists('fromEmail', $data) and ($data['fromEmail'] != '')) 
			$fromEmail = $data['fromEmail'];
		else 
			$fromEmail = $CI->config->item('admin_email');

		if (array_key_exists('fromName', $data) and ($data['fromName'] != '')) 
			$fromName = $data['fromName'];
		else 
			$fromName = $CI->config->item('admin_name');

		if (array_key_exists('email', $data) and ($data['email'] != '')) 
			$toEmail = $data['email'];
		else 
		{
			// No to email. Hence return false;
			return false;  
		}
		
		if($CI->config->item('mdapi') == true)
		{
			$mandrilTemlate = true;
			ini_set("display_errors",1);
				
			$CI->load->config('MDAPIConfig');
			$parms['mdapi_key'] = $CI->config->item('mdapi_key');
			$parms['mdapi_url'] = $CI->config->item('mdapi_url');
			$parms['mdapi_suffix'] = $CI->config->item('mdapi_suffix');
			$CI->load->library('MDAPI',$parms);
				
			switch ($type)
			{
				case 'friendApprovalAutoApproval':
					$mdUsername = "[" . $data["username"] . "]";
					$mdFriendUsername = "[" . $data["friendUsername"] . "]";
					$mdploginUrl = '<A href="'.$CI->config->item('base_url').'/kazaana/plogin">here</a>';
					$mdTemplate = $data["template"];
					if ($mdTemplate == 'friendapprovalmanualapproval')
						$subject = "Your child ".$data["username"]." wants approval for a new friend on Kazaana.";
					else
						$subject = "Your child ".$data["username"]." just added a friend on Kazaana.";

						
					$messageArray =	array (
							"template_name" => $mdTemplate,
							"template_content" => array
							(
									"0" => array(
											"name" => "username",
											"content" => $mdUsername,
									),
									"1" => array(
											"name" => "friendUsername",
											"content" => $mdFriendUsername,
									),
									"2" => array(
											"name" => "ploginUrl",
											"content" => $mdploginUrl,
									)
							),
							"message" => array
							(
									//"html" => "<h1> Hello Message from Mandrill</h1>",
									//"text" => "Hello Message from Mandrill",
									"subject" => $subject,
									"from_email" => $fromEmail,
									"from_name" => $fromName,
									"to" => array
									(
											"0" => array
											(
													"email" => $toEmail,
													"name" => $data["username"],
											),
												
									),
										
							),
								
							"async" => "1",
					);					
					break;
				case 'registerKid':
					$mdUsername = "[" . $data["username"] . "]";
					$mdPassword = "[" . $data["password"] . "]";
					$mdActUrl = '<a href="' . $CI->config->item('base_url').'/kazaana?v='.$data['confirmationCode'].'&u='.$data["username"] . '" target="_blank"><img alt="Activate Now!" border="0" height="72" width="261" src="http://pixystage.com/assets/communication/newTemplate/activateButton.png"/></a>';
					$mdParentEmail = '<strong>Parent email address:&nbsp;</strong><a href="mailto:' . $data["email"] . '" style="color:#000; text-decoration:none;">' . $data["email"] . '</a>';
						
					$messageArray =	array (
							"template_name" => "Kazaana_kid_signup",
							"template_content" => array
							(
									"0" => array(
											"name" => "username",
											"content" => $mdUsername,
									),
									"1" => array(
											"name" => "regurl",
											"content" => $mdActUrl,
									),
									"2" => array(
											"name" => "email_parent",
											"content" => $mdParentEmail,
									),
									"3" => array(
											"name" => "username1",
											"content" => $data["username"],
									)
							),
							"message" => array
							(
									//"html" => "<h1> Hello Message from Mandrill</h1>",
									//"text" => "Hello Message from Mandrill",
									"subject" => "Your child ".$data["username"]." recently registered on Kazaana.",
									"from_email" => $fromEmail,
									"from_name" => $fromName,
									"to" => array
									(
											"0" => array
											(
													"email" => $toEmail,
													"name" => $data["username"],
											),
												
									),
										
							),
								
							"async" => "1",
					);					
					break;
				case 'forgotPassword':
					$subject = 'Kazaana Account Recovery.';
					if ($data['userType']==USERTYPE_MODERATOR) {
						$mdPassword = $data["password"];						
						$messageArray =	array (
							"template_name" => "Kazaana_parent_forgot_password",
							"template_content" => array
							(
									"0" => array(
											"name" => "password",
											"content" => $mdPassword
									)
							),
							"message" => array
							(
									"subject" => $subject,
									"from_email" => $fromEmail,
									"from_name" => $fromName,
									"to" => array(
											"0" => array(
													"email" => $toEmail,
													"name" => ''
											)
												
									)
										
							),
								
							"async" => "1"
						);
					}
					elseif ($data['userType']==USERTYPE_KID) {						
						$messageArray =	array (
							"template_name" => "Kazaana_kid_forgot_password",	
							"template_content" => array (),							
							"message" => array
							(
									//"html" => "<h1> Hello Message from Mandrill</h1>",
									//"text" => "Hello Message from Mandrill",
									"subject" => $subject,
									"from_email" => $fromEmail,
									"from_name" => $fromName,
									"to" => array
									(
											"0" => array
											(
													"email" => $toEmail,
													"name" => '',
											),
												
									),
										
							),
								
							"async" => "1",
					);
					}
					break;
				case 'forgotUsername':	
					$subject = "Kazaana Account Recovery.";	
					$mdUsernames = $data["usernames"];						
					$messageArray =	array (
							"template_name" => "Kazaana_forgotUsername",
							"template_content" => array
							(
									"0" => array(
											"name" => "usernames",
											"content" => $mdUsernames
									)
							),
							"message" => array
							(
									"subject" => $subject,
									"from_email" => $fromEmail,
									"from_name" => $fromName,
									"to" => array(
											"0" => array(
													"email" => $toEmail,
													"name" => ''
											)
												
									)
										
							),
								
							"async" => "1"
					);	
					
					break;
				case 'inviteExternalFriend':
					$subject = 'Kazaana Invitation';
					$mdFirstName = $data["firstName"];						
					$messageArray =	array (
							"template_name" => "Kazaana_invite_friend",
							"template_content" => array
							(
									"0" => array(
											"name" => "firstName",
											"content" => $mdFirstName
									),
									"1" => array(
											"name" => "toEmail",
											"content" => $toEmail.','
									)
							),
							"message" => array
							(
									"subject" => $subject,
									"from_email" => $fromEmail,
									"from_name" => $fromName,
									"to" => array(
											"0" => array(
													"email" => $toEmail,
													"name" => ''
											)
												
									)
										
							),
								
							"async" => "1"
					);	
					break;
				case 'sendReminder':			
				$subject = 'Friends pending approval';
					$mdUsername = $data["childName"];						
					$messageArray =	array (
							"template_name" => "Kazaana_parent_send_reminder",
							"template_content" => array
							(
									"0" => array(
											"name" => "username",
											"content" => $mdUsername
									)
							),
							"message" => array
							(
									"subject" => $subject,
									"from_email" => $fromEmail,
									"from_name" => $fromName,
									"to" => array(
											"0" => array(
													"email" => $toEmail,
													"name" => ''
											)
												
									)
										
							),
								
							"async" => "1"
					);	
					break;
				case 'registerParent01':
					$subject = 'Kazaana Account Confirmation.';
					$messageArray =	array (
							"template_name" => "Kazaana_parent_register",
							"template_content" => array(),
							"message" => array
							(
									"subject" => $subject,
									"from_email" => $fromEmail,
									"from_name" => $fromName,
									"to" => array(
											"0" => array(
													"email" => $toEmail,
													"name" => ''
											)
												
									)
										
							),
								
							"async" => "1"
					);	
					break;
				default:
					$mandrilTemlate = false;
					//return true;
					break;				
			}
			//print_r($messageArray); exit;
			if($mandrilTemlate) {
				$resp = $CI->mdapi->mandrillCall('/messages/send-template', $messageArray);
				//echo "response"; print_r($resp);exit;
				if($resp->status == "error")
				{
					//echo "error ";
debugDummy("send mail failed");
					return false;
				}
				else
				{
debugDummy("send mail sent");
					return true;
				}
				//print_r($resp);
				//return true;
			}
		}
	
		switch ($type)
		{
			case 'registerKid':
				$CI->lang->load('kid_registration');
				$parentExists = $data['parentExists'];
				/** PK-1476 activation URL opened for second child.***/
				//if($parentExists===true){
					//$data['regurl'] = "";
				//}else{
					//$data['regurl'] = $CI->config->item('flexPath').'#'.$data['confirmationCode'];
					$data['regurl'] = $CI->config->item('base_url').'/kazaana?v='.$data['confirmationCode'].'&u='.$data["username"];	
				//} 
				$data['email_username'] = $data["username"];
				$data['email_password'] = $data["password"];
				
				$data['email_dob'] = $data["dob"];
				$data['email_parent'] = $data["email"];
				
				$data['deleteUserAccountInDays'] = $CI->config->item('deleteUserAccountInDays');
				
				
				$subject = 'Your child '.$data["username"].' recently registered on Kazaana.';
				$content = $CI->load->view('registration/reKidEmailNewTemplate', $data, true);
				break;
			case 'registerParent':
				$data['dear_parent_content'] = 'At Kazaana.com, we are committed to the safety of our community.';
				$data['about_pixykids_content'] = '<p>Lorem ipsum dolor sit amet, cis. Cras malesuada placerat quam vitae tempus. Nulla rhoncus consequat lorem, vel rhoncus velit eleifend quis. Aenean pharetra neque tortor.</p><p>Lorem ipsum dolor sit amet, cis. Cras malesuada placerat quam vitae tempus. Cras malesuada placerat quam vitae tempus.</p><p>Lorem ipsum dolor sit amet, cis. Cras malesuada placerat quam vitae tempus.</p>';
				$data['email_username'] = '<b>Username: </b>'.$data["email"];
				$data['email_password'] = '<b>Password: </b>'.$data["password"];
				$data['more_about_pixykids_content'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In quam nulla, euismod at luctus at, mattis eu dui. Vestibulum porttitor sapien eleifend augue mollis sagittis. Cras malesuada placerat quam vitae tempus. Nulla rhoncus consequat lorem, vel rhoncus velit eleifend quis. Aenean pharetra neque tortor. <br/><br/>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In quam nulla, euismod at luctus at, mattis eu dui. Vestibulum porttitor sapien eleifend augue mollis sagittis. Cras malesuada placerat quam vitae tempus. Nulla rhoncus consequat lorem, vel rhoncus velit eleifend quis. Aenean pharetra neque tortor. Proin vulputate cursus euismod. Etiam tempus, nunc at sodales pretium, sapien nulla adipiscing odio, consequat sodales sem nibh id nunc. Nunc vitae nibh a eros lobortis molestie ut nec velit. <br/><br/>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras malesuada placerat quam vitae tempus.';
				$subject = 'Kazaana Account Confirmation.';
				$content = $CI->load->view('registration/regParentEmail', $data, true);
				break;
			case 'forgotPassword':
					
				if ($data['userType']==USERTYPE_MODERATOR) {
					$password = $data['password'];
					$data['content'] =<<<END_OF_EMAIL_CONTENT
					<p><span style="font:normal 16px Arial, Helvetica, sans-serif;">
					You indicated that you forgot your <strong>Kazaana</strong> password. <br>
					We have reset your password. Here is your new password. <br><br>
					<strong>Password:</strong> $password <br><br>
					Once you login, you can go to the parent dashboard and change your password. 
					</span></p>
					<p style="font:normal 14px Arial, Helvetica, sans-serif; line-height:20px; ">
					This is an automated email notification. If you have any other questions and concerns, 
					please direct them to 
					<span style="font:bold 14px Arial, Helvetica, sans-serif; color:#3B66A8;">
					<a href="mailto:support@kazaana.com">support@kazaana.com</a></span></p>
END_OF_EMAIL_CONTENT;
				} elseif ($data['userType']==USERTYPE_KID) {				
				
					$data['content'] = '';
					if (isset($data['username'])) {
						// Kid forgot password and has provided username
						$username = $data['username'];
						$data['content'] =<<<END_OF_EMAIL_CONTENT
						<p><span style="font:normal 16px Arial, Helvetica, sans-serif;">
						Your child (username: $username) has sent a request to retrieve their <strong>Kazaana</strong> password. </span><br><br>
						<span style="font:normal 16px Arial, Helvetica, sans-serif;">You can go to the parent's dashboard on Kazaana and view the passwords for all your kids. 
						</span></p>
						<p style="font:normal 14px Arial, Helvetica, sans-serif; line-height:20px; ">
						This is an automated email notification. If you have any other questions and concerns, 
						please direct them to 
						<span style="font:bold 14px Arial, Helvetica, sans-serif; color:#3B66A8;">
						<a href="mailto:support@kazaana.com">support@kazaana.com</a></span></p>
END_OF_EMAIL_CONTENT;
					} else {
						// Kid forgot password and has provided parent's email
						$data['content'] =<<<END_OF_EMAIL_CONTENT
						<p><span style="font:normal 16px Arial, Helvetica, sans-serif;">
						Your child has sent a request to retrieve their <strong>Kazaana</strong> password. </span><br><br>
						<span style="font:normal 16px Arial, Helvetica, sans-serif;">You can go to the parent's dashboard on Kazaana and view the passwords for all your kids. 
						</span></p>
						<p style="font:normal 14px Arial, Helvetica, sans-serif; line-height:20px; ">
						This is an automated email notification. If you have any other questions and concerns, 
						please direct them to 
						<span style="font:bold 14px Arial, Helvetica, sans-serif; color:#3B66A8;">
						<a href="mailto:support@kazaana.com">support@kazaana.com</a></span></p>
END_OF_EMAIL_CONTENT;
					
					}
					
				}					
				
				$subject = 'Kazaana Account Recovery';				
				$content = $CI->load->view('registration/forgotPassword', $data, true);
				break;
			case 'forgotUsername':
				$usernames = $data['usernames'];
				$data['content'] =<<<END_OF_EMAIL_CONTENT
				<p><span style="font:normal 16px Arial, Helvetica, sans-serif; line-height:25px;">
				Your child has sent a request to retrieve their Kazaana username. <br>
				Here are the usernames for all your children. <br>
				</span></p>
				<p style="font:bold 20px Arial, Helvetica, sans-serif; color:#464684; padding: 2px 0px;">Username: 
				<span style="font:normal 16px Arial, Helvetica, sans-serif; line-height:25px;">$usernames</span>
				</p>
				
				  <p style="font:normal 14px Arial, Helvetica, sans-serif; line-height:20px; ">
				  This is an automated email notification. If you have any other questions and 
				  concerns, please direct them to
				  <span style="font:bold 14px Arial, Helvetica, sans-serif; color:#3B66A8;"> 
				  <a href="mailto:support@kazaana.com">support@kazaana.com</a></span></p>
END_OF_EMAIL_CONTENT;
				$subject = 'Kazaana Account Recovery';				
				$content = $CI->load->view('registration/forgotUsername', $data, true);
				break;
			case 'sendReminder':
				$data['basePath'] = $CI->config->item('cron_base_url');
				$subject = 'Friends pending approval';
				$data['loginUrl'] = $CI->config->item('base_url');
				$content = $CI->load->view('registration/parentReminder', $data, true);
				//print_r($content);exit;
				break;
			case 'inviteExternalFriend':
				if($data['messageBody']!=''){
					$data['invitation_content'] = '<p>Hey it\'s <span style="font-weight:bold; color:#3399CC;">'.$data["firstName"].'</span>!';
					$data['invitation_content'] .= '<p>'.$data['messageBody'].'</p>';
					$data['invitation_content'] .= '<p>Check it out at <strong>Kazaana.com</strong> or by clicking below!</p>';
				}else{
					$data['invitation_content'] = '<p>Hey it\'s <span style="font-weight:bold; color:#3399CC;">'.$data["firstName"].'</span>! Join me at <strong>Kazaana</strong>, a fun place 
            		where we can create <br />
            		content, share things like photos and videos,
            		and play games. There are lots of fun <br />
            		apps and games, and we 
            		can style our 3D avatars and our personalized space! </p>
            		<p>Check it out at <strong>Kazaana.com</strong> or by clicking below!</p>';
				}
				$subject = 'Kazaana Invitation';
				$content = $CI->load->view('registration/inviteExternalFriend', $data, true);
				//print_r($content);exit;
				break;
			case 'friendApprovalsDigest':
				$subject = 'Kazaana Weekly Digest';
				$content = $CI->load->view('registration/weeklyDigestFriends.php', $data, true);				
				break;
			case 'reportAbuse':
				$username = $data['username'];
				$connection = $data['connection'];
				$message = $data['message'];
				$subject = 'Kazaana Report Abuse';
				$data['content'] =<<<END_OF_EMAIL_CONTENT
					<p><span style="font:normal 16px Arial, Helvetica, sans-serif;">
					$username has reported an abuse against $connection. Please see the message below, <br />
					<br /> $message </span></p>					
END_OF_EMAIL_CONTENT;
				$content = $CI->load->view('registration/reportabuse', $data, true);
				break;
			default: 
				break;
		}
		//print_r($content);exit;
		//Send email
		$CI->email->from($fromEmail, $fromName);
		$CI->email->to($toEmail, '');
		$CI->email->subject($subject);
		$CI->email->message($content);
		$ret = $CI->email->send();
		//$er = $CI->email->print_debugger();
		//print_r($er);
		return $ret;
	
}

function sendMailToPixyKids($data, $type)
{
	$CI =& get_instance();

	// Emails can be turned off for development purpose
	$sendEmail = $CI->config->item('sendEmail');

	if (isset($sendEmail) && ($sendEmail === false))
	{
		return true;
	}

	$CI->load->library('email');
	$protocol = $CI->config->item('protocol');
	$emailconfig = array(
			'protocol' => $protocol,
			'mailtype' => 'html',
			'charset' => 'iso-8859-1',
			'newline' => "\r\n"
	);
	
	if ($protocol == "smtp") {
		$emailconfig['smtp_host'] = $CI->config->item('smtp_host');
		$emailconfig['smtp_port'] = $CI->config->item('smtp_port');
		$emailconfig['smtp_user'] = $CI->config->item('smtp_user');
		$emailconfig['smtp_pass'] = $CI->config->item('smtp_pass');
	}
	
	$CI->email->initialize($emailconfig);
	
	
	switch ($type)
	{
		case 'support':
			$toEmail = $CI->config->item('supportEmail');
			break;
		case 'contact':
			$toEmail = $CI->config->item('contactEmail');
			break;
	}
	
	if (isset($data['message'])) {
		$message = nl2br($data['message']);
	}
	$CI->email->from($data['fromEmail'], $data['fromName']);
	$CI->email->to($toEmail, '');
	if (isset($data['selfcopy']) && ($data['selfcopy'] == '1')) {
		$CI->email->cc($data['fromEmail']);
	}
	$CI->email->subject($data['subject']);
	$CI->email->message($message);

	$ret = $CI->email->send();
	return $ret;
} 

function doEncryption($str)
{
	$CI =& get_instance();
	$key = $CI->config->item('encryption_key');
	$cryptText = base64_encode(mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $str, MCRYPT_MODE_ECB));
	return $cryptText;
}

function doDecryption($str)
{
	$CI =& get_instance();
	$key = $CI->config->item('encryption_key');
	$decryptText = mcrypt_decrypt(MCRYPT_BLOWFISH, $key, base64_decode($str), MCRYPT_MODE_ECB);
	// mcrypt_decrypt pads the *RETURN STRING* with nulls ('\0') to fill out to n * blocksize. Hence we need to remove those nulls
	$decryptText = rtrim($decryptText, "\0");
	return $decryptText;
}

function encryptResponse($str)
{
	$CI =& get_instance();
	$encryptResponse = $CI->config->item('encryptResponse');
	$key = $CI->config->item('encryption_key');
	if (isset($encryptResponse) && ($encryptResponse === true))
	{
		$encryptedStr = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $str, MCRYPT_MODE_ECB);
		return ($encryptedStr);
	} else 
	{
		return ($str);
	}
}

function generateUsername($str)
{
	// use different mcrypt cipher here since we dont want username and confirmation code 
	// to be the same 
	$CI =& get_instance();
	$key = $CI->config->item('encryption_key');
	$cryptText = base64_encode(mcrypt_encrypt(MCRYPT_RC2, $key, $str, MCRYPT_MODE_ECB));
	return $cryptText;
}

/**
 * 
 * @param string $identifier
 * $identifier contain the key of the cached array (key, value) pair.
 * 
 */
function deleteCachedData($identifier)
{
	$CI =& get_instance();
	if($CI->config->item('useMemcached')){
		$CI->cache->memcached->delete($identifier);
	}       	
}

function getCachedData($identifier)
{
	$CI =& get_instance();
	$data = array();
	if($CI->config->item('useMemcached')){
		$data = $CI->cache->get($identifier);
	}   
	return $data;    	
}

function saveCachedData($identifier, $pvalue, $tokenExpire)
{
	$CI =& get_instance();
	if($CI->config->item('useMemcached')){
		$CI->cache->save($identifier, $pvalue, $tokenExpire);
	}       	
}

function getS3FileName($filename, $userId) 
{
	$uniquefilename = '';
	$extension = '';
		
	$filenameElements = explode(".", $filename);
	if (count($filenameElements) >= 2) {
		$namePart = $filenameElements[0];
		$extensionPart = $filenameElements[count($filenameElements)-1];
	} else {
		$namePart = $filename;
	}
	$uniquefilename = $userId . "_" . time() . "_" . md5($namePart .  mt_rand(100,10000)) ;
	if ($extensionPart) {
		$uniquefilename .= "." . $extensionPart;
	}
	
	return $uniquefilename;
}

function getS3OriginalFileName($filename) 
{
	$originalFilename = '';
	if ($filename) {
		$originalFilename = "original_" . $filename;
	}
	
	return $originalFilename;
}

function getS3ThumbnailFileName($filename) 
{
	$thumbnailFilename = '';
	if ($filename) {
		$thumbnailFilename = "thumbnail_" . $filename;
	}
	
	return $thumbnailFilename;
}

function getS3UserAssetUrl($filename, $assetType)
{
        $CI =& get_instance();
        $s3 = new AmazonS3();
        $s3url = '';
        $bucket = '';
        if ($assetType == ASSETTYPE_IMAGE) {
                $bucket = $CI->config->item('userImagesBucket');
        } elseif ($assetType == ASSETTYPE_VIDEO) {
                $bucket = $CI->config->item('userVideosBucket');
        }
        if (($bucket) && ($filename)) {
                $s3url = $s3->get_object_url($bucket, $filename);
        }
        return ($s3url);
}

function getS3OriginalUserAssetUrl($filename, $assetType)
{
	$CI =& get_instance();
	$s3 = new AmazonS3();
	$s3url = ''; 
	$bucket = '';
	if ($assetType == ASSETTYPE_IMAGE) {
		$bucket = $CI->config->item('originalUserImagesBucket');
	} elseif ($assetType == ASSETTYPE_VIDEO) {
		$bucket = $CI->config->item('originalUserImagesBucket');
	} 
	if (($bucket) && ($filename)) {
		$s3url = $s3->get_object_url($bucket, $filename);
	}
	return ($s3url);
}

function getVideoPlaceholderImage($filename)
{
	$placeholderImg = "";
	$filenameArr = explode(".",$filename);
	$placeholderImg = $filenameArr[0].".jpg";
	return $placeholderImg;
}

function getProcessingAssetUrl($assetType)
{
	$CI =& get_instance();
	$returl = "";
	if ($assetType == ASSETTYPE_VIDEO) {
		$returl = $CI->config->item('asset_path_video'). "placeholdervideo.jpg";
	}
	return ($returl);
}

function getMP4VideoFilename($filename)
{
	$mp4Filename = "";
	$filenameArr = explode(".",$filename);
	$mp4Filename = $filenameArr[0].".mp4";
	return $mp4Filename;
}

/*
 * generatePassword
 * To avoid generating passwords containing offensive words, vowels are excluded
 * from the list of possible characters. To avoid confusing users, pairs of
 * characters which look similar (letter O and number 0, letter S and number 5,
 * lower-case letter L and number 1) have also been left out.
 */
function generatePassword ($length = 8)
  {

    // start with a blank password
    $password = "";

    // define possible characters - any character in this string can be
    // picked for use in the password, so if you want to put vowels back in
    // or add special characters such as exclamation marks, this is where
    // you should do it
    $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";

    // we refer to the length of $possible a few times, so let's grab it now
    $maxlength = strlen($possible);
  
    // check for length overflow and truncate if necessary
    if ($length > $maxlength) {
      $length = $maxlength;
    }
	
    // set up a counter for how many characters are in the password so far
    $i = 0; 
    
    // add random characters to $password until $length is reached
    while ($i < $length) { 

      // pick a random character from the possible ones
      $char = substr($possible, mt_rand(0, $maxlength-1), 1);
        
      // have we already used this character in $password?
      if (!strstr($password, $char)) { 
        // no, so it's OK to add it onto the end of whatever we've already got...
        $password .= $char;
        // ... and increase the counter by one
        $i++;
      }

    }

    // done!
    return $password;

  }

function getKeyValuePairsStrFromMysqlRow($row, $insideSep="", $outsideSep="")
{
	$ret = ''; $sep = '';
	foreach ($row as $key=>$val) {
		$ret .= $sep . $key . $insideSep . $val;
		$sep = $outsideSep;
	}
	return $ret;
}

?>
