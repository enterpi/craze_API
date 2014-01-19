<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

define('TOKENEXPIRE', 1200);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

// Define GlobalFlag for TRUE & FALSE via API calls
define('API_TRUE',1);
define('API_FALSE',0);

// Define GlobalFlag for TRUE & FALSE for CouchBase docs
define('COUCHBASE_TRUE', 1);
define('COUCHBASE_FALSE', 0);

// Define Asset Types accepted in post
define('ASSETTYPE_IMAGE',1);
define('ASSETTYPE_VIDEO',2);
define('ASSETTYPE_YOUTUBE',3);
define('ASSETTYPE_UNKNOWN',65534);

// Post constants
define('POST_MAX_LIMIT', 50);
define('POST_DEFAULT_LIMIT', 5);
define('POST_MAX_ID', "post::9999999999999999");
define('POST_MAX_DATE', "9999-99-99 99:99:99");
define('POST_MAX_SCORE', 999999999999999999);
define('POST_ORDERBY_DATE', "date");
define('POST_ORDERBY_SCORE', "score");
define('POST_ORDERBY_GEO', "geo");

// User constants
define('USER_MAX_LIMIT', 50);
define('USER_DEFAULT_LIMIT', 5);
define('USER_MAX_ID', 9999999999999999);

// Channel constants
define('CHANNEL_MAX_LIMIT', 50);
define('CHANNEL_DEFAULT_LIMIT', 5);
define('CHANNEL_MAX_ID', 9999999999999999);
define('CHANNEL_PERSONAL_CHANNEL_ID', "Cat::PersonalChannels");

// NAM: end of my constants


// Score values for like and comment on a post
define('POST_SCORE_LIKE', 5);
define('POST_SCORE_COMMENT', 10);
define('POST_SCORE_SHARE', 25);

// Types of score updates
define('POST_LIKE_TYPE', 1);
define('POST_COMMENT_TYPE', 2);
define('POST_SHARE_TYPE', 3);

// define whether add or remove comments, likes, shares
define('POST_ACTION_ADD', 1);
define('POST_ACTION_REMOVE', 2);


// Couchbase Key Delimiter
define('COUCHBASE_KEY_DELIM', '::');
define('COUCHBASE_SECOND_DELIM', '#');
define('DOC_TYPE_USER_PROFILE', 'userProfile');
define('DOC_KEY_USER_PROFILE', 'user');
define('DOC_TYPE_CATEGORY', 'category');
define('DOC_KEY_CATEGORY', 'cat');
define('DOC_TYPE_CHANNEL', 'channel');
define('DOC_KEY_CHANNEL', 'chn');
define('DOC_TYPE_LIKE', 'likepost');
define('DOC_KEY_LIKE', 'lik');
define('DOC_TYPE_COMMENT', 'commentpost');
define('DOC_KEY_COMMENT', 'cmt');
define('DOC_TYPE_SHARE', 'sharepost');
define('DOC_KEY_SHARE', 'shr');
define('DOC_TYPE_POST', 'post');
define('DOC_KEY_POST', 'post');
define('DOC_TYPE_FOLLOW', 'follow');
define('DOC_KEY_FOLLOW', 'flr');

/* End of file constants.php */
/* Location: ./application/config/constants.php */
