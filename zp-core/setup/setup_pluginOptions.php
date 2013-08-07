<?php

/**
 * Used for setting theme/plugin default options
 *
 * @package setup
 *
 */
define('OFFSET_PATH', 2);
require_once('setup-functions.php');
require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');

$iMutex = new Mutex('i', getOption('imageProcessorConcurrency'));
$iMutex->lock();

$extension = sanitize(sanitize($_REQUEST['plugin']));
setupLog(sprintf(gettext('Plugin:%s setup started'), $extension), true);
$option_interface = NULL;
$plugin_is_filter = 5 | THEME_PLUGIN;
require_once(getPlugin($extension . '.php'));
if ($option_interface) {
	//	prime the default options
	setupLog(sprintf(gettext('Plugin:%s option interface instantiated'), $extension), true);
	$option_interface = new $option_interface;
}
if (extensionEnabled($extension)) {
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
	setupLog(sprintf(gettext('Plugin:%s enabled (%2$s)'), $extension, $priority), true);
	enableExtension($extension, $plugin_is_filter);
}
setupLog(sprintf(gettext('Plugin:%s setup completed'), $extension), true);

$iMutex->unlock();

header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Content-Type: image/png');
header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/images/pass.png', true, 301);
?>