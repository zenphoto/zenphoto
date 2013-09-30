<?php

if (!isset($_SERVER['HTTP_HOST']))
	die();
define('ZP_LAST_MODIFIED', gmdate('D, d M Y H:i:s') . ' GMT');
require_once(dirname(__FILE__) . '/version.php'); // Include the version info.
if (!function_exists("gettext")) {
	require_once(dirname(__FILE__) . '/lib-gettext/gettext.inc');
}

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

define('TEST_RELEASE', strpos(ZENPHOTO_VERSION, '-') !== false);

define('DEBUG_LOGIN', false); // set to true to log admin saves and login attempts
define('DEBUG_ERROR', TEST_RELEASE); // set to true to supplies the calling sequence with zp_error messages
define('DEBUG_IMAGE', false); // set to true to log image processing debug information.
define('DEBUG_IMAGE_ERR', TEST_RELEASE); // set to true to flag image processing errors.
define('DEBUG_404', TEST_RELEASE); // set to true to log 404 error processing debug information.
define('DEBUG_EXIF', false); // set to true to log start/finish of exif processing. Useful to find problematic images.
define('DEBUG_PLUGINS', false); // set to true to log plugin load sequence.
define('DEBUG_FILTERS', false); // set to true to log filter application sequence.
define('EXPLAIN_SELECTS', false); //	set to true to log the "EXPLAIN" of SELECT queries in the debug log

define('DB_NOT_CONNECTED', serialize(array('mysql_host'		 => gettext('not connected'), 'mysql_database' => gettext('not connected'), 'mysql_prefix'	 => gettext('not connected'), 'mysql_user'		 => '', 'mysql_pass'		 => '')));
$_zp_DB_details = unserialize(DB_NOT_CONNECTED);
?>