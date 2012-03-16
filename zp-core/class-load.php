<?php
/*******************************************************************************
* Load the base classes (Image, Album, Gallery, etc.)                          *
*******************************************************************************/

require_once(dirname(__FILE__).'/classes.php');
require_once(dirname(__FILE__).'/class-image.php');
require_once(dirname(__FILE__).'/class-album.php');
require_once(dirname(__FILE__).'/class-gallery.php');
require_once(dirname(__FILE__).'/class-search.php');
require_once(dirname(__FILE__).'/class-transientimage.php');
require_once(dirname(__FILE__).'/class-comment.php');

// load the class & filter plugins
if (OFFSET_PATH != 2) {	// setup does not need (and might have problems with) plugins
	$mask = CLASS_PLUGIN;
	if (OFFSET_PATH) {
		$mask = $mask | ADMIN_PLUGIN;
	}
	if (DEBUG_PLUGINS) {
		if (OFFSET_PATH) {
			debugLog('Loading the "class" and "admin" plugins.');
		} else {
			debugLog('Loading the "class" plugins.');
		}
	}
	foreach (getEnabledPlugins() as $extension => $priority) {
		if ($priority & $mask) {
			if (DEBUG_PLUGINS) {
				list($usec, $sec) = explode(" ", microtime());
				$start = (float)$usec + (float)$sec;
			}
			require_once(getPlugin($extension.'.php'));
			if (DEBUG_PLUGINS) {
				list($usec, $sec) = explode(" ", microtime());
				$end = (float)$usec + (float)$sec;
				debugLog(sprintf('    '.$extension.'('.($priority & PLUGIN_PRIORITY).')=>%.4fs',$end-$start));
			}
		}
	}
}
?>