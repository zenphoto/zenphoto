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

function zp_getCookie($name) {
	if (isset($_SESSION[$name])) { return $_SESSION[$name]; }
	if (isset($_COOKIE[$name])) { return $_COOKIE[$name]; }
	return false;
}

function zp_setCookie($name, $value, $time=0, $path='/') {
	setcookie($name, $value, $time, $path);
	if ($time < 0) {
		unset($_SESSION[$name]);
		unset($_COOKIE[$name]);
	} else {
		$_SESSION[$name] = $value;
		$_COOKIE[$name] = $value;
	}
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

function printAdminFooter() {
	echo "<div id=\"footer\">";
	echo "\n  <a href=\"http://www.zenphoto.org\" title=\"".gettext('A simpler web album')."\">zen<strong>photo</strong></a>";
	echo " | <a href=\"http://www.zenphoto.org/support/\" title=\"".gettext('Forum').'">'.gettext('Forum')."</a> | <a href=\"http://www.zenphoto.org/trac/\" title=\"Trac\">Trac</a> | <a href=\"changelog.html\" title=\"".gettext('View Change log')."\">".gettext('Change log')."</a>\n</div>";
}

function debugLog($message, $reset=false) {
	setupLog($message, true);
}

/**
 * Creates the body of a select list
 *
 * @param array $currentValue list of items to be flagged as checked
 * @param array $list the elements of the select list
 * @param bool $descending set true for a reverse order sort
 */
function generateListFromArray($currentValue, $list, $descending, $localize) {
	if ($localize) {
		$list = array_flip($list);
		if ($descending) {
			arsort($list);
		} else {
			natcasesort($list);
		}
		$list = array_flip($list);
	} else {
		if ($descending) {
			rsort($list);
		} else {
			natcasesort($list);
		}
	}
	foreach($list as $key=>$item) {
		echo '<option value="' . $item . '"';
		if (in_array($item, $currentValue)) {
			echo ' selected="selected"';
		}
		if ($localize) $display = $key; else $display = $item;
		echo '>' . $display . "</option>"."\n";
	}
}

function zp_loggedin() {
	return ADMIN_RIGHTS;
}

function zp_clearCookie($name) {
}

function getRequestURI() {
	return NULL;
}

?>