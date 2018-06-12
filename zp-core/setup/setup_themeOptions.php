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
require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');

$logFull = isset($_GET['logFull']);

$theme = sanitize($_REQUEST['theme']);
setupLog(sprintf(gettext('Theme:%s setup started'), $theme), $logFull);

$requirePath = getPlugin('themeoptions.php', $theme);
if (!empty($requirePath)) {
	//	load some theme support plugins that have option interedependencies
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager.php');
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/menu_manager.php');
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/colorbox_js.php');

	require_once(SERVERPATH . '/' . THEMEFOLDER . '/' . $theme . '/themeoptions.php');
	/* prime the default theme options */
	$optionHandler = new ThemeOptions();
	setupLog(sprintf(gettext('Theme:%s option interface instantiated'), $theme), $logFull);
}
/* then set any "standard" options that may not have been covered by the theme */
standardThemeOptions($theme, NULL);

list($usec, $sec) = explode(" ", microtime());
$last = (float) $usec + (float) $sec;
/* and record that we finished */
setupLog(sprintf(gettext('Theme:%s setup completed in %2$.4f seconds'), $theme, $last - $start), $logFull);

sendImage($_GET['class'], 'theme_' . $theme);
exitZP();
?>