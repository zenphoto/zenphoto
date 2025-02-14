<?php
/**
 * Global defintions of constants
 * 
 * @package zpcore
 */
if (!isset($_SERVER['HTTP_HOST'])) {
	die();
}
/**
 * Defines the date of the current session for use as a last modified date within the page header()
 */
define('ZP_LAST_MODIFIED', gmdate('D, d M Y H:i:s') . ' GMT');
require_once(dirname(__FILE__) . '/version.php'); // Include the version info.
if (!function_exists("gettext")) {
	require_once(dirname(__FILE__) . '/libs/functions-gettext.php');
}
if (!defined('SORT_FLAG_CASE')) {
	/**
	 * Defines sort flag case
	 */
	define('SORT_FLAG_CASE', 0);
}
if (!defined('SORT_NATURAL')) {
	/**
	 * Defines if sorting should be natural order
	 */
	define('SORT_NATURAL', 0);
}

/**
 * Defines the name of the Zenphoto core folder (zp-core)
 */
define('ZENFOLDER', 'zp-core');
/**
 * Defines the name of the core extensions folder
 */
define('PLUGIN_FOLDER', 'zp-extensions');

/**
 * Defines the name of the shared "common" folder within/for core extensions
 */
define('COMMON_FOLDER', PLUGIN_FOLDER . '/common');

/**
 * Defines the user 3rd party plugin folder outside the Zenphoto core folder
 */
define('USER_PLUGIN_FOLDER', 'plugins');

/**
 * Defines the name of the albums folder
 */
define('ALBUMFOLDER', 'albums');

/**
 * Defines the name of the themes folder
 */
define('THEMEFOLDER', 'themes');

/**
 * Defines the name of the backup folder
 */
define('BACKUPFOLDER', 'backup');

/**
 * Defines the name of the core utlitlites folder 
 */
define('UTILITIES_FOLDER', 'utilities');

/**
 * Defines the name of the zp-data folder outside the core folder
 */
define('DATA_FOLDER', 'zp-data');

/**
 * Defines the name of the image cache folder
 */
define('CACHEFOLDER', 'cache');

/**
 * Defines name of the non albums upload folder
 */
define('UPLOAD_FOLDER', 'uploaded');

/**
 * Defines the name of the html cache folder
 */
define("STATIC_CACHE_FOLDER", "cache_html");

/**
 * Defines the name of the config file
 */
define('CONFIGFILE', 'zenphoto.cfg.php');

/**
 * Defines the mutex folder
 */
define('MUTEX_FOLDER', '.mutex');

//bit masks for plugin priorities

/**
 * Defines the bitmask for load order priority of class plugins
 */
define('CLASS_PLUGIN', 8192);

/**
 * Defines the bitmask for load order priority of admin plugins
 */
define('ADMIN_PLUGIN', 4096);

/**
 * Defines the bitmask for load order priority of feature plugins
 */
define('FEATURE_PLUGIN', 2048);

/**
 * Defines the bitmask for load order priority of theme plugins
 */
define('THEME_PLUGIN', 1024);

/**
 * Defines the bitmask for load order priority of standard plugins
 */
define('PLUGIN_PRIORITY', 1023);

/**
 * Defines if symlinks are supported on the server
 */
define('SYMLINK', function_exists('symlink') && strpos(@ini_get("suhosin.executor.func.blacklist"), 'symlink') === false);

/**
 * Defines if the server file system is case insensitive
 */
define('CASE_INSENSITIVE', file_exists(dirname(__FILE__) . '/VERSION.PHP'));