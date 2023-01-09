<?php

/**
 * Used for setting theme/plugin default options
 *
 * @package zpcore\setup
 *
 */
define('OFFSET_PATH', 2);
require_once('class-setup.php');
require_once(dirname(dirname(__FILE__)) . '/admin-functions.php');

$iMutex = new zpMutex('setupoptions', 25);
$iMutex->lock();

$theme = sanitize($_REQUEST['theme']);
$returnmode = isset($_REQUEST['returnmode']);
setup::Log(sprintf(gettext('Theme:%s setup started'), $theme), true);
$requirePath = getPlugin('themeoptions.php', $theme);
if (!empty($requirePath)) {
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager.php');
	require_once(SERVERPATH . '/' . THEMEFOLDER . '/' . $theme . '/themeoptions.php');
	/* prime the default theme options */
	$optionHandler = new ThemeOptions();
	setup::Log(sprintf(gettext('Theme:%s option interface instantiated'), $theme), true);
}
/* then set any "standard" options that may not have been covered by the theme */
standardThemeOptions($theme, NULL);
/* and record that we finished */
setup::Log(sprintf(gettext('Theme:%s setup completed'), $theme), true);

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