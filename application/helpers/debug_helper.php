<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//Debug Helper Functions to debug the development process

/**
 * Write the data into a text file
 * @param $filesname - Default file name is given, if you want you can pass specific file name.
 * @param $data - data to write into the file.
 */
function debugDummy($debugData,$filesname='debug.log')
{
	$path = "/tmp/";
	$file = fopen($path.$filesname, 'a+');
	WriteData($file,$debugData);
	fclose($file);	
}

function manageLog($debugData,$filesname='default.log')
{
	//$debugData = (array)$debugData;
	$path = "debuglog/";
	$file = fopen($path.$filesname, 'a');
	WriteData($file,"##----------".date("Y-m-d H:i:s")."----------");
	WriteData($file,$debugData);
	WriteData($file,"##******************************************\n");
	fclose($file);	
}

function WriteData($file,$debugData){
	if(is_array($debugData)){
		foreach($debugData as $key => $value){
			if(is_object($value)){
				$value = (array)$value;
				WriteData($file,$value);
			}else if(is_array($value)){
				WriteData($file,$value);
			}else{
				fwrite($file,$key." --> ".$value."\n");	
			}			
		}
	}else if(is_object($debugData)){
		$debugData = (array)$debugData;
		WriteData($file,$debugData);
	}else{
		fwrite($file, $debugData."\n");	
	}	
}
function ReadData($filename){
	//$filename = "/usr/local/something.txt";
	//$path = "debuglog/";
	$handle = fopen($filename, "r");
	$contents = fread($handle, filesize($filename));
	fclose($handle);
	return $contents;
	
}

function object_2_array($result)
{
    $array = array();
    foreach ($result as $key=>$value)
    {
        if (is_object($value))
        {
            $array[$key]=object_2_array($value);
        }
        elseif (is_array($value))
        {
            $array[$key]=object_2_array($value);
        }else{
            $array[$key]=$value;
        }
    }
    return $array;
}

function toArray($data) {
    if (is_object($data)) 
    	$data = get_object_vars($data);
    return is_array($data) ? array_map(__FUNCTION__, $data) : $data;
}

/* function : logMsg 
 * This function is useful for debugging purposes. define('DEBUG_MESSAGES',1) in your code 
 * and start seeing debug messages appear in the error log
 */
function logMsg($data) {
	if ((defined('DEBUG_MESSAGES')) && (DEBUG_MESSAGES == "1")) {
		$sep = ''; $logmsg = '';
		foreach ($data as $key => $val) {
			$logmsg .= $sep . "$key:" . print_r($val,true);
			$sep = ', ';
		};
		error_log("DEBUG: $logmsg");
	}
}
?>
