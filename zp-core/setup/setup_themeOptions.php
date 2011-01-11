<?php
/**
 * Used for settint theme default options
 *
 * @package setup
 *
 */
define('OFFSET_PATH',3);
require_once('setup-functions.php');
require_once(dirname(dirname(__FILE__)).'/functions.php');
$theme = sanitize(sanitize($_POST['theme']));
require_once(SERVERPATH.'/'.THEMEFOLDER.'/'.$theme.'/themeoptions.php');
$optionHandler = new ThemeOptions(); /* prime the default theme options */
setupLog(sprintf(gettext('Set default options for %s'),$theme));
?>