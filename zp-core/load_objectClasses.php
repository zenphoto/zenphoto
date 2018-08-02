<?php

/**
 *
 * Load the base classes (Image, Album, Gallery, etc.)
 * and any enabled "class" plugins
 *
 * @author Stephen Billard (sbillard)
 *
 * @package core
 */
$_zp_plugin_differed_actions = array(); //	final initialization for class plugins (mostly for language translation issues)

require_once(dirname(__FILE__) . '/classes.php');
require_once(dirname(__FILE__) . '/class-gallery.php');
require_once(dirname(__FILE__) . '/class-album.php');
require_once(dirname(__FILE__) . '/class-image.php');
require_once(dirname(__FILE__) . '/class-search.php');

$_zp_loaded_plugins = array();
// load the class & filter plugins
if (abs(OFFSET_PATH) != 2) { // setup does not need (and might have problems with) plugins
	if (DEBUG_PLUGINS) {
		debugLog('Loading the "class" plugins.');
	}
	$enabled = getEnabledPlugins();
	foreach ($enabled as $extension => $plugin) {
		$priority = $plugin['priority'];
		if ($priority & CLASS_PLUGIN) {
			$start = microtime();
			require_once($plugin['path']);
			if (DEBUG_PLUGINS) {
				zpFunctions::pluginDebug($extension, $priority, $start);
			}
			$_zp_loaded_plugins[$extension] = $extension;
		}
	}
}
//	check for logged in users and set up the locale
require_once(dirname(__FILE__) . '/auth_zp.php');
define('ZENPHOTO_LOCALE', setMainDomain());
//	process any differred language strings
$_zp_active_languages = $_zp_all_languages = NULL; //	clear out so that they will get translated properly
foreach ($_zp_plugin_differed_actions as $callback) {
	call_user_func($callback);
}
?>