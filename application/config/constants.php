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


/* Application Level Constants */
// Messages
define('DEFAULT_NUM_OF_MESSAGES',5);
define('MAX_NUM_OF_MESSAGES',50);
define('MESSAGE_EXCERPT_SIZE',100);

// Recommended Friends
define('DEFAULT_RECOMMENDED_FRIENDS', 30);
define('MAX_RECOMMENDED_FRIENDS',100);

// Wall 
define('DEFAULT_NUM_OF_POSTS', 10);
define('MAX_NUM_OF_POSTS',100);

// getUserProfile Constants
define('USER_PROFILE_MAX_FRIENDS', 30);
define('USER_PROFILE_MAX_ASSETS', 30);
define('USER_PROFILE_MAX_FANPAGES', 30);

// Search Connections 
define('DEFAULT_NUM_OF_SEARCH_CONNECTIONS',10);
define('MAX_NUM_OF_SEARCH_CONNECTIONS',50);

// Username
define('MAX_LENGTH_USERNAME',20);

/**
 * Look-up constants  
 */

define('CONTYPE_FRIEND',1);

define('MESSAGETYPE_NEWS',1);
define('MESSAGETYPE_REPLY',2);
define('MESSAGETYPE_FORWARD',3);
define('MESSAGETYPE_NOTIFICATION',4);

define('REFTYPE_POST',1);
define('REFTYPE_COMMENT',2);
define('REFTYPE_CHATASSET',3);
define('REFTYPE_ASSET',4);
define('REFTYPE_ASSETALBUM',5);
define('REFTYPE_MESSAGE',6);
define('REFTYPE_APPS',7);
define('REFTYPE_PROFILEPIC',8);
define('REFTYPE_MOBILEUPLOADS',9);
define('REFTYPE_MOBILEPROFILEPIC',10);

define('UPLOADTYPE_CHATTERBOX',1);
define('UPLOADTYPE_PROFILEPHOTO',2);

define('ALBUM_ISSHARED_PARTIAL', '0');
define('ALBUM_ISSHARED_ALL', '1');
define('ALBUM_ISSHARED_NONE', '2');

define('ALBUM_IS_DELETABLE_TRUE', '1');
define('ALBUM_IS_DELETABLE_FALSE', '0');

define('ALBUM_ADD_ALLOWED_TRUE', '0');
define('ALBUM_ADD_ALLOWED_FALSE', '1');

define('ALBUM_GROUP_UPLOAD_NO', '0');
define('ALBUM_GROUP_UPLOAD_YES', '1');

define('PUSH_NOTIFICATIONS_FALSE', '0');
define('PUSH_NOTIFICATIONS_TRUE', '1');

define('USERTYPE_ADMIN',1);
define('USERTYPE_MODERATOR',2);
define('USERTYPE_USER',3);
define('USERTYPE_KID',3);
define('USERTYPE_FAN',4);

define('CREATIONTYPE',1);

// Define Boy / Girl constants
define('GENDER_BOY', 'M');
define('GENDER_GIRL', 'F');

// Cache related constants
define('CACHEDUSERINFO','user_info_');
define('CACHEDUSERTOKEN','userToken_');

// Item Type Constants 
define('ITEMTYPE_BACKGROUND',1);
define('ITEMTYPE_VIRTUALGOOD',2);

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

define('COUCHBASE_CHAT_CONVERSATION_TYPE', 'chatConversation');
define('COUCHBASE_CHAT_MESSAGE_TYPE', 'chatMessage');
define('COUCHBASE_CONVERSATIONID_COUNT', 'conversationId');
define('COUCHBASE_CHATID_COUNT', 'messageId');
define('COUCHBASE_POSTID_COUNT', 'postId');
define('COUCHBASE_COMMENTID_COUNT', 'commentId');
define('COUCHBASE_WALL_POST_TYPE', 'wallPost');
define('COUCHBASE_WALL_COMMENT_TYPE', 'wallComment');
define('COUCHBASE_POST_DOC', 'post');
define('COUCHBASE_POST_COMMENT_DOC', 'comment');

// Chat constants
define('CHAT_MAX_LIMIT', 50);
define('CHAT_DEFAULT_LIMIT', 5);
define('CHAT_MAX_MESSAGE_ID', 999999999999);

// Use S3 flag 
define('UseS3',1);

/// New UI constants .... 
define('NEW_UI',1);
/* End of file constants.php */
/* Location: ./application/config/constants.php */
