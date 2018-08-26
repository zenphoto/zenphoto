<?php

/*
 * one time initialization code for basic execution
 */

require_once(dirname(__FILE__) . '/lib-encryption.php');
require_once(dirname(__FILE__) . '/lib-utf8.php');
$_zp_UTF8 = new utf8();

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

// Set error reporting.
error_reporting(E_ALL | E_STRICT);
if (DISPLAY_ERRORS) {
	@ini_set('display_errors', 1);
} else {
	@ini_set('display_errors', 0);
}

set_error_handler("zpErrorHandler");
set_exception_handler("zpExceptionHandler");
register_shutdown_function('zpShutDownFunction');
$_configMutex = new zpMutex('cF');
$_zp_mutex = new zpMutex();

$_zp_conf_options_associations = $_zp_options = array();
$_zp_conf_vars = array('db_software' => 'NULL', 'mysql_prefix' => '_', 'charset' => 'UTF-8', 'UTF-8' => 'utf8');
// Including the config file more than once is OK, and avoids $conf missing.
if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
	define('DATA_MOD', fileperms(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE) & 0777);
	@eval('?>' . file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE));
	if (!isset($_zp_conf_vars['UTF-8']) || $_zp_conf_vars['UTF-8'] === true) {
		$_zp_conf_vars['UTF-8'] = 'utf8';
	}
} else {
	define('DATA_MOD', 0777);
}
if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/security.log')) {
	define('LOG_MOD', fileperms(SERVERPATH . '/' . DATA_FOLDER . '/' . '/security.log') & 0777);
} else {
	define('LOG_MOD', DATA_MOD);
}
define('DATABASE_PREFIX', $_zp_conf_vars['mysql_prefix']);
define('LOCAL_CHARSET', $_zp_conf_vars['charset']);
if (!isset($_zp_conf_vars['special_pages'])) {
	//	get the default version form the distribution files
	$cfg = $_zp_conf_vars;
	@eval('?>' . file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/zenphoto_cfg.txt'));
	$cfg['special_pages'] = $_zp_conf_vars['special_pages'];
	$_zp_conf_vars = $cfg;
}

if (!defined('CHMOD_VALUE')) {
	define('CHMOD_VALUE', fileperms(dirname(__FILE__)) & 0666);
}
define('FOLDER_MOD', CHMOD_VALUE | 0311);
define('FILE_MOD', CHMOD_VALUE & 0666);

if (OFFSET_PATH != 2) {
	if (!file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
		_setup(11);
	} else if (!file_exists(dirname(__FILE__) . '/functions-db-' . $_zp_conf_vars['db_software'] . '.php')) {
		_setup(12);
	}
}

if (!defined('FILESYSTEM_CHARSET')) {
	if (isset($_zp_conf_vars['FILESYSTEM_CHARSET']) && $_zp_conf_vars['FILESYSTEM_CHARSET'] != 'unknown') {
		define('FILESYSTEM_CHARSET', $_zp_conf_vars['FILESYSTEM_CHARSET']);
	} else {
		define('FILESYSTEM_CHARSET', 'UTF-8');
	}
}

// If the server protocol is not set, set it to the default.
if (!isset($_zp_conf_vars['server_protocol'])) {
	$_zp_conf_vars['server_protocol'] = 'http';
}

foreach ($_zp_conf_vars as $name => $value) {
	if (!is_array($value)) {
		$_zp_conf_options_associations[strtolower($name)] = $name;
		$_zp_options[strtolower($name)] = $value;
	}
}

if (!defined('DATABASE_SOFTWARE') && (extension_loaded(strtolower($_zp_conf_vars['db_software'])) || $_zp_conf_vars['db_software'] == 'NULL')) {
	require_once(dirname(__FILE__) . '/functions-db-' . $_zp_conf_vars['db_software'] . '.php');
	$data = db_connect(array_intersect_key($_zp_conf_vars, array('db_software' => '', 'mysql_user' => '', 'mysql_pass' => '', 'mysql_host' => '', 'mysql_database' => '', 'mysql_prefix' => '', 'UTF-8' => '')), false);
	if ($data) {
		$software = db_software();
		define('MySQL_VERSION', $software['version']);
	}
} else {
	$data = false;
}
if (!defined('MySQL_VERSION')) {
	define('MySQL_VERSION', '0.0.0');
}

if (!$data && OFFSET_PATH != 2) {
	_setup(13);
}

primeOptions();
define('SITE_LOCALE', getOption('locale'));

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
if (function_exists('date_default_timezone_set')) {
	$level = error_reporting(0);
	$_zp_server_timezone = date_default_timezone_get();
	date_default_timezone_set($_zp_server_timezone);
	@ini_set('date.timezone', $_zp_server_timezone);
	error_reporting($level);
}

// Set the memory limit higher just in case -- suppress errors if user doesn't have control.
// 100663296 bytes = 96M
if (ini_get('memory_limit') && parse_size(ini_get('memory_limit')) < 100663296) {
	@ini_set('memory_limit', '96M');
}

// Set the internal encoding
@ini_set('default_charset', LOCAL_CHARSET);
if (function_exists('mb_internal_encoding')) {
	@mb_internal_encoding(LOCAL_CHARSET);
}

// load graphics libraries in priority order
// once a library has concented to load, all others will
// abdicate.
$_zp_graphics_optionhandlers = array();
$try = array('lib-GD.php', 'lib-NoGraphics.php');
if (getOption('use_imagick')) {
	array_unshift($try, 'lib-Imagick.php');
}
while (!function_exists('zp_graphicsLibInfo')) {
	require_once(dirname(__FILE__) . '/' . array_shift($try));
}
unset($try);
$_zp_cachefileSuffix = zp_graphicsLibInfo();


define('GRAPHICS_LIBRARY', $_zp_cachefileSuffix['Library']);
unset($_zp_cachefileSuffix['Library']);
unset($_zp_cachefileSuffix['Library_desc']);
$_zp_supported_images = $_zp_images_classes = array();
foreach ($_zp_cachefileSuffix as $key => $type) {
	if ($type) {
		$_zp_images_classes[$_zp_supported_images[] = strtolower($key)] = 'Image';
	}
}

//NOTE: SERVER_PROTOCOL is the option PROTOCOL is what should be used in links!!!!
define('SERVER_PROTOCOL', getOption('server_protocol'));
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
unset($c);

define('SAFE_MODE', preg_match('#(1|ON)#i', ini_get('safe_mode')));
define('FULLHOSTPATH', PROTOCOL . "://" . $_SERVER['HTTP_HOST']);
define('FULLWEBPATH', FULLHOSTPATH . WEBPATH);
define('SAFE_MODE_ALBUM_SEP', '__');
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

define('DATE_FORMAT', getOption('date_format'));

define('RW_SUFFIX', getOption('mod_rewrite_suffix'));
define('UNIQUE_IMAGE', getOption('unique_image_prefix') && MOD_REWRITE);
define('UTF8_IMAGE_URI', getOption('UTF8_image_URI'));
define('MEMBERS_ONLY_COMMENTS', getOption('comment_form_members_only'));

define('HASH_SEED', getOption('extra_auth_hash_text'));

define('IP_TIED_COOKIES', getOption('IP_tied_cookies'));

define('NO_WATERMARK', '!');

// Don't let anything get above this, to save the server from burning up...
define('MAX_SIZE', getOption('image_max_size'));

define('MENU_TRUNCATE_STRING', getOption('menu_truncate_string'));
define('MENU_TRUNCATE_INDICATOR', getOption('menu_truncate_indicator'));



/**
 * TODO: This code should eventually be replaced by a simple define of GOTHUB_ORG once
 * the organization has been changed.
 */
if (getOption('GitHubOwner') == 'netPhotoGraphics') {
	define('GITHUB_ORG', 'netPhotoGraphics');
} else {
	if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
		require_once(dirname(__FILE__) . '/github_locator.php');
	}
	if (!defined('GITHUB_ORG')) {
		define('GITHUB_ORG', 'ZenPhoto20');
	}
}
define('GITHUB', 'github.com/' . GITHUB_ORG . '/netPhotoGraphics');
