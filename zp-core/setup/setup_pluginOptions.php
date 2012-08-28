<?php
/**
 * Used for setting theme/plugin default options
 *
 * @package setup
 *
 */
define('OFFSET_PATH',2);
require_once('setup-functions.php');
require_once(dirname(dirname(__FILE__)).'/admin-functions.php');
$extension = sanitize(sanitize($_POST['plugin']));
setupLog(sprintf(gettext('Plugin:%s setup started'),$extension),true);
$option_interface = NULL;
require_once(getPlugin($extension.'.php'));
if ($option_interface) {
	//	prime the default options
	setupLog(sprintf(gettext('Plugin:%s option interface instantiated'),$extension),true);
	$option_interface = new $option_interface;
}
setupLog(sprintf(gettext('Plugin:%s setup completed'),$extension),true);
?>