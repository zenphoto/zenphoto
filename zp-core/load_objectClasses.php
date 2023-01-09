<?php
/**
 * Load the base classes (Image, Album, Gallery, etc.)
 * 
 * @package zpcore
 */
$_zp_extra_filetypes = array(); // contains file extensions and the handler class for alternate images
$_zp_object_cache = array();
define('OBJECT_CACHE_DEPTH', 150); //	how many objects to hold for each object class
define('WATERMARK_IMAGE', 1);
define('WATERMARK_THUMB', 2);
define('WATERMARK_FULL', 4);
define('EXACT_TAG_MATCH', getOption('exact_tag_match'));
define('SEARCH_DURATION', 3000);
define('SEARCH_CACHE_DURATION', getOption('search_cache_duration'));

require_once(dirname(__FILE__) . '/classes/class-persistentobject.php');
require_once(dirname(__FILE__) . '/classes/class-themeobject.php');
require_once(dirname(__FILE__) . '/classes/class-mediaobject.php');
require_once(dirname(__FILE__) . '/classes/class-gallery.php');

$_zp_gallery = new Gallery();
define('IMAGE_SORT_DIRECTION', getOption('image_sortdirection'));
define('IMAGE_SORT_TYPE', getOption('image_sorttype'));
Gallery::addAlbumHandler('alb', 'dynamicAlbum');

require_once(dirname(__FILE__) . '/classes/class-albumbase.php');
require_once(dirname(__FILE__) . '/classes/class-album.php');
require_once(dirname(__FILE__) . '/classes/class-dynamicalbum.php');
require_once(dirname(__FILE__) . '/classes/class-image.php');
require_once(dirname(__FILE__) . '/classes/class-transientimage.php');
require_once(dirname(__FILE__) . '/classes/class-searchengine.php');
require_once(dirname(__FILE__) . '/classes/class-maintenancemode.php');

$_zp_loaded_plugins = array();
// load the class & filter plugins
if (OFFSET_PATH != 2) { // setup does not need (and might have problems with) plugins
	$masks[] = CLASS_PLUGIN;
	if (OFFSET_PATH) {
		$masks[] = ADMIN_PLUGIN | FEATURE_PLUGIN;
	}
	if (DEBUG_PLUGINS) {
		if (OFFSET_PATH) {
			debugLog('Loading the "class" "feature" and "admin" plugins.');
		} else {
			debugLog('Loading the "class" plugins.');
		}
	}
	foreach ($masks as $mask) {
		foreach (getEnabledPlugins() as $extension => $plugin) {
			$priority = $plugin['priority'];
			if ($priority & $mask) {
				if (DEBUG_PLUGINS) {
					list($usec, $sec) = explode(" ", microtime());
					$start = (float) $usec + (float) $sec;
				}
				require_once($plugin['path']);
				$_zp_loaded_plugins[$extension] = $extension;
				if (DEBUG_PLUGINS) {
					pluginDebug($extension, $priority, $start);
				}
			}
		}
		require_once(dirname(__FILE__) . '/auth.php'); // loaded after CLASS_PLUGIN and before ADMIN_PLUGIN
	}
} else {
	require_once(dirname(__FILE__) . '/auth.php'); // setup needs this!
}

if (GALLERY_SESSION || zp_loggedin()) {
	zp_session_start();
} 