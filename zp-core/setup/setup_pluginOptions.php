<?php

/**
 * Used for setting theme/plugin default options
 *
 * @author Stephen Billard (sbillard)
 *
 * @package setup
 *
 */
list($usec, $sec) = explode(" ", microtime());
$start = (float) $usec + (float) $sec;

define('OFFSET_PATH', 2);
require_once('setup-functions.php');
register_shutdown_function('shutDownFunction');
require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager.php');
$debug = TEST_RELEASE || isset($_GET['debug']);

$iMutex = new zpMutex('i', getOption('imageProcessorConcurrency'));
$iMutex->lock();

$extension = sanitize($_REQUEST['plugin']);
setupLog(sprintf(gettext('Plugin:%s setup started'), $extension));
$option_interface = NULL;
$plugin_is_filter = 5 | THEME_PLUGIN;

require_once($path = getPlugin($extension . '.php'));

if (extensionEnabled($extension)) {
	//	update the enabled priority
	$priority = $plugin_is_filter & PLUGIN_PRIORITY;
	if ($plugin_is_filter & CLASS_PLUGIN) {
		$priority .= ' | CLASS_PLUGIN';
	}
	if ($plugin_is_filter & ADMIN_PLUGIN) {
		$priority .= ' | ADMIN_PLUGIN';
	}
	if ($plugin_is_filter & FEATURE_PLUGIN) {
		$priority .= ' | FEATURE_PLUGIN';
	}
	if ($plugin_is_filter & THEME_PLUGIN) {
		$priority .= ' | THEME_PLUGIN';
	}
	setupLog(sprintf(gettext('Plugin:%s enabled (%2$s)'), $extension, $priority));
	enableExtension($extension, $plugin_is_filter);
}
if (strpos($path, SERVERPATH . '/' . USER_PLUGIN_FOLDER) === 0) {
	$pluginStream = file_get_contents($path);
	if ($str = isolate('@category', $pluginStream)) {
		preg_match('|@category\s+(.*)\s|', $str, $matches);
		$deprecate = !isset($matches[1]) || $matches[1] != 'package';
	} else {
		$deprecate = true;
	}
} else {
	$deprecate = false;
}
if ($option_interface) {
	//	prime the default options
	setupLog(sprintf(gettext('Plugin:%1$s option interface instantiated (%2$s)'), $extension, $option_interface));
	$option_interface = new $option_interface;
}

$iMutex->unlock();

sendImage($deprecate);

list($usec, $sec) = explode(" ", microtime());
$last = (float) $usec + (float) $sec;
setupLog(sprintf(gettext('Plugin:%1$s setup completed in %2$.4f seconds'), $extension, $last - $start));
?>