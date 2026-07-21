<?php
require_once(dirname(__FILE__) . '/definitions-global.php');
require_once(dirname(__FILE__) . '/functions/functions-common.php');
require_once(dirname(__FILE__) . '/classes/class-zpmutex.php');
require_once dirname(__FILE__) . '/functions/functions-basic.php';

$_zp_mutex = new zpMutex('cF');

/**
 * OFFSET_PATH definitions:
 * 		0		root scripts (e.g. the root index.php)
 * 		1		zp-core scripts
 * 		2		setup scripts
 * 		3		plugin scripts
 * 		4		scripts in the theme folders
 */
global $_zp_conf_vars;
$const_webpath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$const_serverpath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));

/**
 * see if we are executing out of any of the known script folders. If so we know how to adjust the paths
 * if not we presume the script is in the root of the installation. If it is not the script better have set
 * the SERVERPATH and WEBPATH defines to the correct values
 */
if (!preg_match('~(.*)/(' . ZENFOLDER . ')~', $const_webpath, $matches)) {
	preg_match('~(.*)/(' . USER_PLUGIN_FOLDER . '|' . THEMEFOLDER . ')~', $const_webpath, $matches);
}
if ($matches) {
	$const_webpath = $matches[1];
	$const_serverpath = substr($const_serverpath, 0, strrpos($const_serverpath, '/' . $matches[2]));
	if (!defined('OFFSET_PATH')) {
		switch ($matches[2]) {
			case ZENFOLDER:
				define('OFFSET_PATH', 1);
				break;
			case USER_PLUGIN_FOLDER:
				define('OFFSET_PATH', 3);
				break;
			case THEMEFOLDER:
				define('OFFSET_PATH', 4);
				break;
		}
	}
	unset($matches);
} else {
	if (!defined('OFFSET_PATH')) {
		define('OFFSET_PATH', 0);
	}
}
if ($const_webpath == '/' || $const_webpath == '.') {
	$const_webpath = '';
}

if (!defined('WEBPATH')) {
	define('WEBPATH', $const_webpath);
}
unset($const_webpath);

if (!defined('SERVERPATH')) {
	define('SERVERPATH', $const_serverpath);
}
unset($const_serverpath);

// Contexts (Bitwise and combinable)
define("ZP_INDEX", 1);
define("ZP_ALBUM", 2);
define("ZP_IMAGE", 4);
define("ZP_COMMENT", 8);
define("ZP_SEARCH", 16);
define("ZP_SEARCH_LINKED", 32);
define("ZP_ALBUM_LINKED", 64);
define('ZP_IMAGE_LINKED', 128);
define('ZP_ZENPAGE_NEWS_PAGE', 256);
define('ZP_ZENPAGE_NEWS_ARTICLE', 512);
define('ZP_ZENPAGE_NEWS_CATEGORY', 1024);
define('ZP_ZENPAGE_NEWS_DATE', 2048);
define('ZP_ZENPAGE_PAGE', 4096);
define('ZP_ZENPAGE_SINGLE', 8192);

switch (PHP_MAJOR_VERSION) {
	case 5:
		switch (PHP_MINOR_VERSION) {
			case 0:
			case 1:
			case 2:
				define('ENT_FLAGS', ENT_QUOTES);
				break;
			case 3:
				define('ENT_FLAGS', ENT_QUOTES | ENT_IGNORE);
				break;
			default: // 4 and beyond
				define('ENT_FLAGS', ENT_QUOTES | ENT_SUBSTITUTE);
				break;
		}
		break;
	default: // PHP 6?
		define('ENT_FLAGS', ENT_QUOTES | ENT_SUBSTITUTE);
		break;
}

// Including the config file more than once is OK, and avoids $conf missing.
if (OFFSET_PATH != 2 && !file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
	require_once SERVERPATH .'/'. ZENFOLDER . '/classes/class-reconfigure.php';
	require_once SERVERPATH .'/'. ZENFOLDER . '/deprecated/functions-reconfigure.php';
	reconfigure::reconfigureAction(1);
} else {
	require_once SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE;
}

require_once SERVERPATH .'/'. ZENFOLDER . '/definitions-debug.php';

// Set error reporting.
@ini_set('display_errors', '0'); // try to disable in case set
if (isset($_zp_conf_vars['display_errors']) && $_zp_conf_vars['display_errors']) {
	error_reporting(E_ALL);
	@ini_set('display_errors', '1');
} 
set_error_handler("zpErrorHandler");
set_exception_handler("zpErrorHandler");


// If the server protocol is not set, set it to the default.
if (!isset($_zp_conf_vars['server_protocol'])) {
	$_zp_conf_vars['server_protocol'] = 'http';
}

//NOTE: SERVER_PROTOCOL is the option, PROTOCOL is what should be used in links
if (isset($_zp_conf_vars['server_protocol'])) {
	if ($_zp_conf_vars['server_protocol'] == 'https' || $_zp_conf_vars['server_protocol'] == 'https_admin') {
		define('SERVER_PROTOCOL', 'https');
	} else {
		define('SERVER_PROTOCOL', 'http');
	}
} else {
	define('SERVER_PROTOCOL', 'http');
}
switch (SERVER_PROTOCOL) {
	case 'https':
		define('PROTOCOL', 'https');
		break;
	default:
		if (secureServer()) {
			define('PROTOCOL', 'https');
		} else {
			define('PROTOCOL', 'http');
		}
		break;
}

// Silently setup default rewrite tokens if missing completely or partly from current config file
if (!isset($_zp_conf_vars['special_pages'])) {
	$_zp_conf_vars['special_pages'] = getDefaultRewriteTokens(null);
} else {
	addMissingDefaultRewriteTokens();
}

$mysql_prefix = '';
if (isset($_zp_conf_vars['mysql_prefix'])) {
	$mysql_prefix = $_zp_conf_vars['mysql_prefix'];
}
define('DATABASE_PREFIX', $mysql_prefix);


$_zp_mutex = new zpMutex();

if (OFFSET_PATH != 2 && empty($_zp_conf_vars['mysql_database'])) {
	require_once SERVERPATH .'/'. ZENFOLDER . '/classes/class-reconfigure.php';
	require_once SERVERPATH .'/'. ZENFOLDER . '/deprecated/functions-reconfigure.php';
	reconfigure::reconfigureAction(2);
}

require_once(SERVERPATH . '/' . ZENFOLDER . '/libs/class-utf8.php');
if (!function_exists('mb_internal_encoding')) {
	require_once(SERVERPATH . '/' . ZENFOLDER . '/libs/functions-utf8.php');
}
global $_zp_utf8;
$_zp_utf8 = new utf8();

if (!defined('CHMOD_VALUE')) {
	define('CHMOD_VALUE', fileperms(dirname(__FILE__)) & 0666);
}
define('FOLDER_MOD', CHMOD_VALUE | 0311);
define('FILE_MOD', CHMOD_VALUE & 0666);
define('DATA_MOD', fileperms(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE) & 0777);
if(file_exists(SERVERPATH . '/' . DATA_FOLDER . '/setup.log')) {
	define('LOGS_MOD', fileperms(SERVERPATH . '/' . DATA_FOLDER . '/setup.log') & 0600);
} else {
	define('LOGS_MOD', DATA_MOD);
}
if (!defined('DATABASE_SOFTWARE') && extension_loaded(strtolower(@$_zp_conf_vars['db_software']))) {
	require_once SERVERPATH . '/' . ZENFOLDER . '/deprecated/functions-db.php'; // legacy functions wrapper
	require_once SERVERPATH . '/' . ZENFOLDER . '/classes/class-dbbase.php'; // empty base db class
	require_once SERVERPATH . '/' . ZENFOLDER . '/classes/class-db' . strtolower($_zp_conf_vars['db_software']) . '.php'; // actual db handler
	define('DATABASE_SOFTWARE', $_zp_conf_vars['db_software']);
	define('DATABASE_MIN_VERSION', '5.5.3');
	define('DATABASE_DESIRED_VERSION', '5.7.0');
	define('DATABASE_MARIADB_MIN_VERSION', '5.5.0'); // more or less MySQL 5.5
	define('DATABASE_MARIADB_DESIRED_VERSION', '10.1.0'); // more or less MySQL 5.7
	$_zp_dbclass = 'db' . $_zp_conf_vars['db_software'];
	$dbconfig_defaults = array(
			'db_software' => $_zp_conf_vars['db_software'],
			'mysql_user' => null,
			'mysql_pass' => null,
			'mysql_host' => 'localhost',
			'mysql_database' => null,
			'mysql_port' => 3306,
			'mysql_prefix' => 'zp_',
			'mysql_socket' => '',
			'UTF-8' => true);
	foreach ($dbconfig_defaults as $key => $value) {
		if (!isset($_zp_conf_vars[$key]) || ($key != 'mysql_prefix' && isset($_zp_conf_vars[$key]) && empty($_zp_conf_vars[$key]))) {
			$_zp_conf_vars[$key] = $value;
		}
	}
	$_zp_db = new $_zp_dbclass($_zp_conf_vars, false);
	$data = $_zp_db->connection;
} else {
	$data = false;
}
if (!$data && OFFSET_PATH != 2) {
	require_once SERVERPATH .'/'. ZENFOLDER . '/classes/class-reconfigure.php';
	require_once SERVERPATH .'/'. ZENFOLDER . '/deprecated/functions-reconfigure.php';
	reconfigure::reconfigureAction(3);
}

if($data && $_zp_db->isEmptyTable('administrators')) {
	require_once SERVERPATH .'/'. ZENFOLDER . '/classes/class-reconfigure.php';
	require_once SERVERPATH .'/'. ZENFOLDER . '/deprecated/functions-reconfigure.php';
	reconfigure::reconfigureAction(4);
}

if (!defined('FILESYSTEM_CHARSET')) {
	if (isset($_zp_conf_vars['FILESYSTEM_CHARSET']) && $_zp_conf_vars['FILESYSTEM_CHARSET'] != 'unknown') {
		define('FILESYSTEM_CHARSET', $_zp_conf_vars['FILESYSTEM_CHARSET']);
	} else {
		$data = getOption('filesystem_charset');
		if(!$data) {
			$data = 'UTF-8';
		}
		define('FILESYSTEM_CHARSET', $data);
	}
}

$data = getOption('charset');
if (!$data) {
	$data = 'UTF-8';
}
define('LOCAL_CHARSET', $data);

$data = getOption('gallery_data');
if ($data) {
	$data = getSerializedArray($data);
} else {
	$data = array();
}
define('GALLERY_SESSION', @$data['album_session']);
define('GALLERY_SECURITY', @$data['gallery_security']);
unset($data);

// insure a correct timezone
$level = error_reporting(0);
$_zp_server_timezone = date_default_timezone_get();
date_default_timezone_set($_zp_server_timezone);
@ini_set('date.timezone', $_zp_server_timezone);
error_reporting($level);

// Set the memory limit higher just in case -- suppress errors if user doesn't have control.
// 100663296 bytes = 96M
if (ini_get('memory_limit') && parse_size(ini_get('memory_limit')) < 100663296) {
	@ini_set('memory_limit', '96M');
}

// Set the internal encoding
if (function_exists('mb_internal_encoding')) {
	@mb_internal_encoding(LOCAL_CHARSET);
}

// load graphics libraries in priority order
define('IMAGICK_REQUIRED_VERSION', '3.0.0');
define('IMAGEMAGICK_REQUIRED_VERSION', '6.3.8');
require_once SERVERPATH . '/' . ZENFOLDER . '/deprecated/functions-graphics.php'; // legacy functions
require_once SERVERPATH . '/' . ZENFOLDER . '/admin-options/class-graphicsoptions.php'; // option class
require_once SERVERPATH . '/' . ZENFOLDER . '/classes/class-graphicsbase.php'; // base class
$_zp_graphics = new graphicsBase();
$_zp_graphics_optionhandlers[] = new graphicsOptions(); // register option handler
if ((getOption('use_imagick') || getOption('graphicslib_selected') == 'imagick') && $_zp_graphics->imagick_present) { // support legacy option
	require_once SERVERPATH . '/' . ZENFOLDER . '/classes/class-graphicsimagick.php';
	$_zp_graphics = new graphicsImagick();
} else if ($_zp_graphics->gd_present) {
	require_once SERVERPATH . '/' . ZENFOLDER . '/classes/class-graphicsgd.php';
	$_zp_graphics = new graphicsGD();
} 
$_zp_cachefile_suffix = $_zp_graphics->info;
define('GRAPHICS_LIBRARY', $_zp_cachefile_suffix['Library']);
unset($_zp_cachefile_suffix['Library']);
unset($_zp_cachefile_suffix['Library_desc']);
$_zp_supported_images = array();
foreach ($_zp_cachefile_suffix as $key => $type) {
	if ($type) {
		$_zp_supported_images[] = strtolower($key);
	}
}

define('NO_WATERMARK', '!');
// Don't let anything get above this, to save the server from burning up...
define('MAX_SIZE', getOption('image_max_size'));

require_once(SERVERPATH . '/' . ZENFOLDER . '/libs/functions-encryption.php');

if (!defined('COOKIE_PERSISTENCE')) {
	$persistence = getOption('cookie_persistence');
	if (!$persistence)
		$persistence = 5184000;
	define('COOKIE_PERSISTENCE', $persistence);
	unset($persistence);
}
if ($c = getOption('zenphoto_cookie_path')) {
	define('COOKIE_PATH', $c);
} else {
	define('COOKIE_PATH', WEBPATH);
}

define('SERVER_HTTP_HOST', PROTOCOL . "://" . $_SERVER['HTTP_HOST']);
define('SAFE_MODE', false);
define('FULLWEBPATH', SERVER_HTTP_HOST . WEBPATH);
define('SAFE_MODE_ALBUM_SEP', '');
define('SERVERCACHE', SERVERPATH . '/' . CACHEFOLDER);
define('MOD_REWRITE', getOption('mod_rewrite'));

define('DEBUG_LOG_SIZE', getOption('debug_log_size'));

define('ALBUM_FOLDER_WEBPATH', getAlbumFolder(WEBPATH));
define('ALBUM_FOLDER_SERVERPATH', getAlbumFolder(SERVERPATH));
define('ALBUM_FOLDER_EMPTY', getAlbumFolder(''));

define('IMAGE_WATERMARK', getOption('fullimage_watermark'));
define('FULLIMAGE_WATERMARK', getOption('fullsizeimage_watermark'));
define('THUMB_WATERMARK', getOption('Image_watermark'));
define('OPEN_IMAGE_CACHE', !getOption('protected_image_cache'));
define('IMAGE_CACHE_SUFFIX', getOption('image_cache_suffix'));

$time_display_disabled = getOption('time_display_disabled');
define('DATE_FORMAT', trim(strval(getOption('date_format'))));
define('TIME_FORMAT', trim(strval(getOption('time_format'))));
define('DATETIME_FORMAT', DATE_FORMAT . ' ' . TIME_FORMAT);
if (getOption('date_format_localized') && in_array(DATE_FORMAT, array('locale_preferreddate_time', 'locale_preferreddate_notime'))) {
	deprecationNotice(gettext("The date format options 'locale_preferreddate_time' and 'locale_preferreddate_notime' are deprecated and will be removed in Zenphoto 1.7. Please set individual date and time formats."));
	define('DATETIME_DISPLAYFORMAT', DATE_FORMAT);
} else {
	if ($time_display_disabled) {
		define('DATETIME_DISPLAYFORMAT', DATE_FORMAT);
	} else {
		define('DATETIME_DISPLAYFORMAT', DATETIME_FORMAT);
	}
}

define('IM_SUFFIX', getOption('mod_rewrite_image_suffix'));
define('UTF8_IMAGE_URI', getOption('UTF8_image_URI'));
define('MEMBERS_ONLY_COMMENTS', getOption('comment_form_members_only'));

define('HASH_SEED', getOption('extra_auth_hash_text'));
define("CACHE_HASH_LENGTH", strlen(sha1(strval(HASH_SEED)))); //Zenphoto 1.5.1 moved from cacheManager/functions.php 
define('IP_TIED_COOKIES', getOption('IP_tied_cookies'));

define('MENU_TRUNCATE_STRING', getOption('menu_truncate_string'));
define('MENU_TRUNCATE_INDICATOR', getOption('menu_truncate_indicator'));