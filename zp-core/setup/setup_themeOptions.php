<?php

/**
 * Used for setting theme/plugin default options
 *
 * @author Stephen Billard (sbillard)
 *
 * @package setup
 *
 */
define('OFFSET_PATH', 2);
require_once('setup-functions.php');
require_once(dirname(dirname(__FILE__)) . '/admin-functions.php');

$iMutex = new Mutex('i', getOption('imageProcessorConcurrency'));
$iMutex->lock();

$theme = sanitize($_REQUEST['theme']);
setupLog(sprintf(gettext('Theme:%s setup started'), $theme), true);
if (!protectedTheme($theme)) {
	setupLog(sprintf(gettext('Theme:%s triggered the deprecated functions plugin'), $theme), true);
	enableExtension('deprecated-functions', 900 | CLASS_PLUGIN);
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/deprecated-functions.php');
	$deprecate = true;
}

$requirePath = getPlugin('themeoptions.php', $theme);

if (!empty($requirePath)) {
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager.php');
	require_once(SERVERPATH . '/' . THEMEFOLDER . '/' . $theme . '/themeoptions.php');
	/* prime the default theme options */
	$_zp_gallery->setCurrentTheme($theme);
	$optionHandler = new ThemeOptions();
	setupLog(sprintf(gettext('Theme:%s option interface instantiated'), $theme), true);
}
/* then set any "standard" options that may not have been covered by the theme */
standardThemeOptions($theme, NULL);
/* and record that we finished */
setupLog(sprintf(gettext('Theme:%s setup completed'), $theme), true);

$iMutex->unlock();

if (isset($deprecate) && $deprecate) {
	$img = 'pass_2.png';
} else {
	$img = 'pass.png';
}
$fp = fopen(SERVERPATH . '/' . ZENFOLDER . '/images/' . $img, 'rb');
// send the right headers
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header("Content-Type: image/png");
header("Content-Length: " . filesize(SERVERPATH . '/' . ZENFOLDER . '/images/' . $img));
// dump the picture and stop the script
fpassthru($fp);
fclose($fp);
?>