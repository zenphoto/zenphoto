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
require_once(dirname(dirname(__FILE__)) . '/admin-functions.php');
$testRelease = defined('TEST_RELEASE') && TEST_RELEASE || strpos(getOption('markRelease_state'), '-DEBUG') !== false;
$debug = isset($_GET['debug']);

$theme = sanitize($_REQUEST['theme']);

$iMutex = new zpMutex('i', getOption('imageProcessorConcurrency'));
$iMutex->lock();

setupLog(sprintf(gettext('Theme:%s setup started'), $theme), $testRelease);

$requirePath = getPlugin('themeoptions.php', $theme);

if (!empty($requirePath)) {
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager.php');

	//loat theme related plugins incase they interact with the theme options
	foreach (getEnabledPlugins() as $extension => $plugin) {
		$loadtype = $plugin['priority'];
		if ($loadtype & FEATURE_PLUGIN) {
			require_once($plugin['path']);
		}
	}

	require_once(SERVERPATH . '/' . THEMEFOLDER . '/' . $theme . '/themeoptions.php');
	/* prime the default theme options */
	$_zp_gallery->setCurrentTheme($theme);
	$optionHandler = new ThemeOptions();
	setupLog(sprintf(gettext('Theme:%s option interface instantiated'), $theme), $testRelease);
}
/* then set any "standard" options that may not have been covered by the theme */
standardThemeOptions($theme, NULL);

$iMutex->unlock();

sendImage($_GET['class']);

list($usec, $sec) = explode(" ", microtime());
$last = (float) $usec + (float) $sec;
/* and record that we finished */
setupLog(sprintf(gettext('Theme:%s setup completed in %2$.4f seconds'), $theme, $last - $start), $testRelease);
exitZP();
?>