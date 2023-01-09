<?php

/**
 * Used for setting theme/plugin default options
 *
 * @package zpcore\setup
 *
 */
define('OFFSET_PATH', 2);
require_once('class-setup.php');
require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager.php');

$iMutex = new zpMutex('setupoptions', 25);
$iMutex->lock();

$extension = sanitize($_REQUEST['plugin']);
$returnmode = isset($_REQUEST['returnmode']);
setup::Log(sprintf(gettext('Plugin:%s setup started'), $extension), true);
$option_interface = NULL;
$plugin_is_filter = 5 | THEME_PLUGIN;
require_once(getPlugin($extension . '.php'));

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
	setup::Log(sprintf(gettext('Plugin:%s enabled (%2$s)'), $extension, $priority), true);
	enableExtension($extension, $plugin_is_filter);
}

if ($option_interface) {
	//	prime the default options
	setup::Log(sprintf(gettext('Plugin:%1$s option interface instantiated (%2$s)'), $extension, $option_interface), true);
	$option_interface = new $option_interface;
}

setup::Log(sprintf(gettext('Plugin:%s setup completed'), $extension), true);

$iMutex->unlock();

if($returnmode) {
	echo FULLWEBPATH . '/' . ZENFOLDER . '/images/pass.png';
} else {
	$fp = fopen(SERVERPATH . '/' . ZENFOLDER . '/images/pass.png', 'rb');
	// send the right headers
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header("Content-Type: image/png");
	header("Content-Length: " . filesize(SERVERPATH . '/' . ZENFOLDER . '/images/pass.png'));
	// dump the picture and stop the script
	fpassthru($fp);
	fclose($fp);
}
?>