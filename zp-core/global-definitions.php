<?php

if (!isset($_SERVER['HTTP_HOST']))
	die();
define('GITHUB', 'github.com/ZenPhoto20/ZenPhoto20');
define('ZP_LAST_MODIFIED', gmdate('D, d M Y H:i:s') . ' GMT');
require_once(dirname(__FILE__) . '/version.php'); // Include the version info.
if (!function_exists("gettext")) {
	require_once(dirname(__FILE__) . '/lib-gettext/gettext.inc');
}
if (!defined('SORT_FLAG_CASE'))
	define('SORT_FLAG_CASE', 0);
if (!defined('SORT_NATURAL'))
	define('SORT_NATURAL', 0);
if (!defined('SORT_LOCALE_STRING'))
	define('SORT_LOCALE_STRING', 0);

define('SCRIPTPATH', str_replace('\\', '/', dirname(dirname(__FILE__))));
define('ZENFOLDER', 'zp-core');
define('PLUGIN_FOLDER', 'zp-extensions');
define('COMMON_FOLDER', PLUGIN_FOLDER . '/common');
define('USER_PLUGIN_FOLDER', 'plugins');
define('ALBUMFOLDER', 'albums');
define('THEMEFOLDER', 'themes');
define('BACKUPFOLDER', 'backup');
define('UTILITIES_FOLDER', 'utilities');
define('DATA_FOLDER', 'zp-data');
define('CACHEFOLDER', 'cache');
define('UPLOAD_FOLDER', 'uploaded');
define("STATIC_CACHE_FOLDER", "cache_html");
define('CONFIGFILE', 'zenphoto.cfg.php');
define('MUTEX_FOLDER', '.mutex');

//bit masks for plugin priorities
define('CLASS_PLUGIN', 8192);
define('ADMIN_PLUGIN', 4096);
define('FEATURE_PLUGIN', 2048);
define('THEME_PLUGIN', 1024);
define('PLUGIN_PRIORITY', 1023);

//exif index defines
define('EXIF_SOURCE', 0);
define('EXIF_KEY', 1);
define('EXIF_DISPLAY_TEXT', 2);
define('EXIF_DISPLAY', 3);
define('EXIF_FIELD_SIZE', 4);
define('EXIF_FIELD_ENABLED', 5);
define('EXIF_FIELD_TYPE', 6);

define('SYMLINK', function_exists('symlink') && strpos(@ini_get("suhosin.executor.func.blacklist"), 'symlink') === false);
define('CASE_INSENSITIVE', file_exists(strtoupper(__FILE__)));

$_debug = explode('-', preg_replace('~-RC\d+~', '', ZENPHOTO_VERSION) . '-');
$_debug = $_debug[1];
define('TEST_RELEASE', !empty($_debug));

define('DEBUG_404', strpos($_debug, '404')); // set to true to log 404 error processing debug information.
define('DEBUG_EXIF', strpos($_debug, 'EXIF')); // set to true to log start/finish of exif processing.
define('EXPLAIN_SELECTS', strpos($_debug, 'EXPLAIN')); //	set to true to log the "EXPLAIN" of SQL SELECT queries
define('DEBUG_FILTERS', strpos($_debug, 'FILTERS')); // set to true to log filter application sequence.
define('DEBUG_IMAGE', strpos($_debug, 'IMAGE')); // set to true to log image processing debug information.
define('DEBUG_LOCALE', strpos($_debug, 'LOCALE')); // used for examining language selection problems
define('DEBUG_LOGIN', strpos($_debug, 'LOGIN')); // set to true to log admin saves and login attempts
define('DEBUG_PLUGINS', strpos($_debug, 'PLUGINS')); // set to true to log plugin load sequence.

unset($_debug);

define('DB_NOT_CONNECTED', serialize(array('mysql_host' => gettext('not connected'), 'mysql_database' => gettext('not connected'), 'mysql_prefix' => gettext('not connected'), 'mysql_user' => '', 'mysql_pass' => '')));
$_zp_DB_details = unserialize(DB_NOT_CONNECTED);

//icons
define('ARROW_DOWN_GREEN', '<span style="color: green;font-size: large;line-height: 80%">&dArr;</span>');
define('ARROW_LEFT_BLUE', '<span style="color: blue;font-size:large;line-height: 60%;">&lArr;</span>');
define('ARROW_RIGHT_BLUE', '<span style="color: blue;font-size:large;line-height: 60%">&rArr;</span>');
define('ARROW_UP_GRAY', '<span style="color: lightgray;font-size: large;">&uArr;</span>');
define('ARROW_UP_GREEN', '<span style="color: green;font-size: large;">&uArr;</span>');
define('BALLOT_BOX_WITH_X_RED', '<span style="color: red;font-size: large;line-height: 80%;">&#9746;</span>');
define('BULLSEYE_BLUE', '<span style="color: blue;font-size: large;line-height: 80%;">&#9678;</span>');
define('BULLSEYE_DARKORANGE', '<span style="color: darkorange;font-size: large;line-height: 80%;">&#9678;</span>');
define('BULLSEYE_GREEN', '<span style="color: green;font-size: large;line-height: 80%;">&#9678;</span>');
define('BULLSEYE_LIGHTGRAY', '<span style="color: lightgray;font-size: large;line-height: 80%;">&#9678;</span>');
define('BULLSEYE_RED', '<span style="color: red;font-size: large;line-height: 80%;">&#9678;</span>');
define('BURST_BLUE', '<span style="color: blue;font-size: large;line-height: 80%;">&#10040;</span>');
define('CIRCLED_BLUE_STAR', '<span style="color: blue;font-size: large;line-height: 60%">&#10026;</span>');
define('CLIPBOARD', '&#128203;');
define('CLOCKFACE', '&#128336;');
define('CLOCKWISE_OPEN_CIRCLE_ARROW_GREEN', '<span style="font-size:large;color:green;line-height: 60%;">&#8635;</span>');
define('CROSS_MARK_RED', '<span style="color: red;">&#10060;</span>');
define('DRAG_HANDLE', '<span style="color:lightsteelblue;font-size: x-large;">&#10021;</span>');
define('DRAG_HANDLE_ALERT', '<span style="color:red;font-size: x-large;">&#10021;</span>');
define('ENVELOPE', '&#128231;');
define('EXCLAMATION_RED', '<span style="color: red;padding-left: 5px;padding-right: 5px;">&#10071;</span>');
define('GEAR_WITHOUT_HUB', '&#9965;');
define('GREEN_CROSS_ON_SHIELD', '<span style="color: green;font-size: large;line-height: 60%;">&#9960;</span>');
define('HEAVY_BLUE_CURVED_UPWARDS_AND_RIGHTWARDS_ARROW', '<span style="color:blue;font-size:large;line-height: 60%;">&#10150;</span>');
define('HEAVY_GREEN_CHECKMARK', '<span style="color: green;">&#9989;</span>');
define('INFORMATION_BLUE', '<span style="color: blue;font-size: large;line-height: 90%;">&#8505;</span>');
define('KEY', '&#128273;');
define('MENU_ICON', '&#9776;');
define('NO_ENTRY', '<span style="color: red;">&#9940;</span>');
define('NORTH_WEST_CORNER_ARROW', '<span style="color: green;">&#8689;</span>');
define('PENCIL_BLUE', '<span style="color:blue;font-size: large;">&#9998;</span>');
define('SOUTH_EAST_CORNER_ARROW', '<span style="color: green;">&#8690;</span>');
define('SQUARED_KEY_GREEN', '<span style="color: green;font-size: large;">&#9919</span>;');
define('WARNING_SIGN_ORANGE', '<span style="color: darkorange;font-size: large;line-height: 80%">&#9888;</span>');
//Firefox has a huge wastebasket image
if (preg_match('~firefox~i', $_SERVER['HTTP_USER_AGENT'])) {
	define('WASTEBASKET', '<span style="color: brown;">&#128465;</span>');
} else {
	define('WASTEBASKET', '<span style="color: brown;font-size: large;line-height: 80%">&#128465;</span>');
}
?>