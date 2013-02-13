<?php
/**
 * These are the functions that setup needs before the database can be accessed (so it can't include
 * functions.php because that will cause a database connect error.)
 * @package setup
 */

// force UTF-8 Ø


require_once(dirname(dirname(__FILE__)).'/global-definitions.php');
require_once(dirname(dirname(__FILE__)).'/functions-common.php');

require_once(dirname(dirname(__FILE__)).'/lib-kses.php');

$const_webpath = str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']));
$serverpath = str_replace('\\','/',dirname($_SERVER['SCRIPT_FILENAME']));
preg_match('~(.*)/('.ZENFOLDER.')~',$const_webpath, $matches);
if (empty($matches)) {
	$const_webpath = '';
} else {
	$const_webpath = $matches[1];
	$serverpath = substr($serverpath,0,strrpos($serverpath,'/'.ZENFOLDER));
}

if (!defined('WEBPATH')) { define('WEBPATH', $const_webpath); }
if (!defined('SERVERPATH')) { define('SERVERPATH', $serverpath); }
define('LOCAL_CHARSET','UTF-8');
define('FILESYSTEM_CHARSET', 'ISO-8859-1');
define('ADMIN_RIGHTS',1);
define('PROTOCOL', 'http');

error_reporting(E_ALL | E_STRICT);
set_error_handler("zpErrorHandler");
set_exception_handler("zpErrorHandler");

// insure a correct timezone
if (function_exists('date_default_timezone_set')) {
	$level = error_reporting(0);
	$_zp_server_timezone = date_default_timezone_get();
	date_default_timezone_set($_zp_server_timezone);
	@ini_set('date.timezone', $_zp_server_timezone);
	error_reporting($level);
}

$_options = array();
function getOption($key) {
	global $_options;
	if (isset($_options[$key])) return $_options[$key];
	return NULL;
}

function setOption($key, $value, $persistent=true) {
	global $_options;
	$_options[$key] = $value;
}

function setOptionDefault($key, $value) {
	global $_options;
	$_options[$key] = $value;
}

function debugLog($message, $reset=false) {
	setupLog($message, true);
}

function getRequestURI() {
	return NULL;
}

?>