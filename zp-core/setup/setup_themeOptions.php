<?php
/**
 * Used for settint theme default options
 *
 * @package setup
 *
 */
define('OFFSET_PATH',2);
require_once('setup-functions.php');
require_once(dirname(dirname(__FILE__)).'/functions.php');
$theme = sanitize(sanitize($_POST['theme']));
setupLog(sprintf(gettext('Set theme default options for %s started'),$theme),true);
require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/cacheImages.php');
require_once(SERVERPATH.'/'.THEMEFOLDER.'/'.$theme.'/themeoptions.php');
$optionHandler = new ThemeOptions(); /* prime the default theme options */
setupLog(sprintf(gettext('Set theme default options for %s completed'),$theme),true);
?>