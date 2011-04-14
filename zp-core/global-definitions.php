<?php
$_zp_last_modified = gmdate('D, d M Y H:i:s').' GMT';
require_once(dirname(__FILE__).'/version.php'); // Include the version info.
define('ZENFOLDER', 'zp-core');
define('PLUGIN_FOLDER', 'zp-extensions');
define('USER_PLUGIN_FOLDER', 'plugins');
define('ALBUMFOLDER', 'albums');
define('THEMEFOLDER', 'themes');
define('BACKUPFOLDER', 'backup');
define('UTILITIES_FOLDER', 'utilities');
define('DATA_FOLDER','zp-data');
define('CACHEFOLDER', 'cache');
define('UPLOAD_FOLDER','uploaded');
define("STATIC_CACHE_FOLDER","cache_html");

//bit masks for plugin priorities
define('CLASS_PLUGIN',4096);
define('ADMIN_PLUGIN',2048);
define('THEME_PLUGIN',128);
define('PLUGIN_PRIORITY',127);

define('DEBUG_LOGIN', false); // set to true to log admin saves and login attempts
define('DEBUG_ERROR', !defined('RELEASE')); // set to true to supplies the calling sequence with zp_error messages
define('DEBUG_IMAGE', false); // set to true to log image processing debug information.
define('DEBUG_404', !defined('RELEASE')); // set to true to log 404 error processing debug information.
define('DEBUG_EXIF', false); // set to true to log start/finish of exif processing. Useful to find problematic images.
define('DEBUG_PLUGINS', false); // set to true to log plugin load sequence.
define('DEBUG_FILTERS', false); // set to true to log filter application sequence.

?>