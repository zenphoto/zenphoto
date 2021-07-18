<?php

/* * *****************************************************************************
 * Load the base classes (Image, Album, Gallery, etc.)                          *
 * ***************************************************************************** */

require_once(dirname(__FILE__) . '/class-persistentobject.php');
require_once(dirname(__FILE__) . '/class-themeobject.php');
require_once(dirname(__FILE__) . '/class-mediaobject.php');
require_once(dirname(__FILE__) . '/class-gallery.php');
require_once(dirname(__FILE__) . '/class-albumbase.php');
require_once(dirname(__FILE__) . '/class-album.php');
require_once(dirname(__FILE__) . '/class-dynamicalbum.php');
require_once(dirname(__FILE__) . '/class-image.php');
require_once(dirname(__FILE__) . '/class-transientimage.php');
require_once(dirname(__FILE__) . '/class-searchengine.php');

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