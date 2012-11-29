<?php
/*******************************************************************************
* Load the base classes (Image, Album, Gallery, etc.)                          *
*******************************************************************************/

require_once(dirname(__FILE__).'/classes.php');
require_once(dirname(__FILE__).'/class-image.php');
require_once(dirname(__FILE__).'/class-album.php');
require_once(dirname(__FILE__).'/class-gallery.php');
require_once(dirname(__FILE__).'/class-search.php');
require_once(dirname(__FILE__).'/class-comment.php');

// load the class & filter plugins
if (OFFSET_PATH != 2) {	// setup does not need (and might have problems with) plugins
	$masks[] = CLASS_PLUGIN;
	if (OFFSET_PATH) {
		$masks[] = ADMIN_PLUGIN;
	}
	if (DEBUG_PLUGINS) {
		if (OFFSET_PATH) {
			debugLog('Loading the "class" and "admin" plugins.');
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
					$start = (float)$usec + (float)$sec;
				}
				require_once($plugin['path']);
				if (DEBUG_PLUGINS) {
					list($usec, $sec) = explode(" ", microtime());
					$end = (float)$usec + (float)$sec;
					$class = array();
					if ($priority & CLASS_PLUGIN) {
						$class[] = 'CLASS';
					} else if ($priority & ADMIN_PLUGIN) {
						$class[] = 'ADMIN';
					}
					debugLog(sprintf('    '.$extension.'(%s:%u)=>%.4fs',implode('|',$class),$priority & PLUGIN_PRIORITY,$end-$start));
				}
			}
		}
		require_once(dirname(__FILE__).'/auth_zp.php');	// loaded after CLASS_PLUGIN and before ADMIN_PLUGIN
	}
} else {
	require_once(dirname(__FILE__).'/auth_zp.php');	// setup needs this!
}
?>