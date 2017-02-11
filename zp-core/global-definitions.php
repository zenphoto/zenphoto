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
?>