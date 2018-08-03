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
$startPO = (float) $usec + (float) $sec;

define('OFFSET_PATH', 2);
define('SETUP_PLUGIN', TRUE);
require_once('setup-functions.php');
register_shutdown_function('shutDownFunction');
require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager.php');
$fullLog = isset($_GET['fullLog']);

$extension = sanitize($_REQUEST['plugin']);
setupLog(sprintf(gettext('Plugin:%s setup started'), $extension), $fullLog);
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
	setupLog(sprintf(gettext('Plugin:%s enabled (%2$s)'), $extension, $priority), $fullLog);
	enableExtension($extension, $plugin_is_filter);
}

if ($option_interface) {
	//	prime the default options
	setupLog(sprintf(gettext('Plugin:%1$s option interface instantiated (%2$s)'), $extension, $option_interface), $fullLog);
	$option_interface = new $option_interface;
}

list($usec, $sec) = explode(" ", microtime());
$last = (float) $usec + (float) $sec;
/* and record that we finished */
setupLog(sprintf(gettext('Plugin:%1$s setup completed in %2$.4f seconds'), $extension, $last - $startPO), $fullLog);

sendImage($_GET['class'], 'plugin_' . $extension);
exitZP();
?>