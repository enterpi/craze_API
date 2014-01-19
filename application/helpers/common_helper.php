<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function generateError($errorCode) {
	$CI =& get_instance();
	$errorMessage = $CI->lang->line($errorCode);
	$ret = array($errorCode, $errorMessage);
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

?>
