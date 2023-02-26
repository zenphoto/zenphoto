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

/**
 * Enables test release mode, supports the markRelease plugin
 */
define('TEST_RELEASE', (isset($_zp_conf_vars['test_release']) && $_zp_conf_vars['test_release']) || preg_match('~-[^RC]~', ZENPHOTO_VERSION));

/**
 * set to true to log admin saves and login attempts
 */
define('DEBUG_LOGIN', (isset($_zp_conf_vars['debug_login']) && $_zp_conf_vars['debug_login'])); 

/** 
 * set to true to supplies the calling sequence with zp_error messages
 */
define('DEBUG_ERROR', (isset($_zp_conf_vars['debug_error']) && $_zp_conf_vars['debug_error']) || TEST_RELEASE);
/**
 * set to true to log image processing debug information.
 */
define('DEBUG_IMAGE', isset($_zp_conf_vars['debug_image']) && $_zp_conf_vars['debug_image']); 

/**
 * set to true to flag image processing errors.
 */
define('DEBUG_IMAGE_ERR', (isset($_zp_conf_vars['debug_image_err']) && $_zp_conf_vars['debug_image_err']) || TEST_RELEASE); 

/**
 * set to true to log 404 error processing debug information.
 */
define('DEBUG_404', (isset($_zp_conf_vars['debug_404']) && $_zp_conf_vars['debug_404']) || TEST_RELEASE); 

/**
 * set to true to log start/finish of exif processing. Useful to find problematic images.
 */
define('DEBUG_EXIF', isset($_zp_conf_vars['debug_exif']) && $_zp_conf_vars['debug_exif']); 

/**
 * set to true to log plugin load sequence.
 */
define('DEBUG_PLUGINS', isset($_zp_conf_vars['debug_plugins']) && $_zp_conf_vars['debug_plugins']); 

/**
 * set to true to log filter application sequence.
 */
define('DEBUG_FILTERS', isset($_zp_conf_vars['debug_filters']) && $_zp_conf_vars['debug_filters']); 

/**
 * 	set to true to log the "EXPLAIN" of SELECT queries in the debug log
 */
define('EXPLAIN_SELECTS', isset($_zp_conf_vars['explain_selects']) && $_zp_conf_vars['explain_selects']); 

/**
 * used for examining language selection problems
 */
define('DEBUG_LOCALE', isset($_zp_conf_vars['debug_locale']) && $_zp_conf_vars['debug_locale']); 