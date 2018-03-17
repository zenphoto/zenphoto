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

$themelist = array();
$albums = $_zp_gallery->getAlbums(0);
foreach ($albums as $alb) {
	$album = newAlbum($alb);
	if ($album->isMyItem(THEMES_RIGHTS)) {
		$albumtheme = $album->getAlbumTheme();
		if ($theme == $albumtheme) {
			$themelist[] = $album;
		}
	}
}

$iMutex = new zpMutex('i', getOption('imageProcessorConcurrency'));
$iMutex->lock();

setupLog(sprintf(gettext('Theme:%s setup started'), $theme), $testRelease);

$requirePath = getPlugin('themeoptions.php', $theme);

if (!empty($requirePath)) {
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager.php');
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/colorbox_js.php');
	require_once(SERVERPATH . '/' . THEMEFOLDER . '/' . $theme . '/themeoptions.php');
	/* prime the default theme options */
	$_zp_gallery->setCurrentTheme($theme);
	$optionHandler = new ThemeOptions();
	foreach ($themelist as $_set_theme_album) {
		$optionHandler->__construct();
		standardThemeOptions($theme, $_set_theme_album);
	}
	setupLog(sprintf(gettext('Theme:%s option interface instantiated'), $theme), $testRelease);
}
/* then set any "standard" options that may not have been covered by the theme */
standardThemeOptions($theme, NULL);

$iMutex->unlock();

sendImage(!protectedTheme($theme));

list($usec, $sec) = explode(" ", microtime());
$last = (float) $usec + (float) $sec;
/* and record that we finished */
setupLog(sprintf(gettext('Theme:%s setup completed in %2$.4f seconds'), $theme, $last - $start), $testRelease);
exitZP();
?>