<?php
/**
 * Used for setting theme/plugin default options
 *
 * @package setup
 *
 */
define('OFFSET_PATH',2);
require_once('setup-functions.php');
require_once(dirname(dirname(__FILE__)).'/admin-globals.php');
$extension = sanitize(sanitize($_REQUEST['plugin']));
setupLog(sprintf(gettext('Plugin:%s setup started'),$extension),true);
$option_interface = NULL;
$plugin_is_filter = 5|THEME_PLUGIN;
require_once(getPlugin($extension.'.php'));
if ($option_interface) {
	//	prime the default options
	setupLog(sprintf(gettext('Plugin:%s option interface instantiated'),$extension),true);
	$option_interface = new $option_interface;
}
if (getOption('zp_plugin_'.$extension)) {
	setOption('zp_plugin_'.$extension, $plugin_is_filter);
}
setupLog(sprintf(gettext('Plugin:%s setup completed'),$extension),true);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s').' GMT');
header('Content-Type: image/png');
header('Location: ' . FULLWEBPATH.'/'.ZENFOLDER.'/images/pass.png', true, 301);
?>