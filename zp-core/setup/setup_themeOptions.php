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
$theme = sanitize(sanitize($_POST['theme']));
setupLog(sprintf(gettext('Theme:%s setup started'),$theme),true);
$requirePath = getPlugin('themeoptions.php', $theme);
if (!empty($requirePath)) {
	require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/cacheManager.php');
	require_once(SERVERPATH.'/'.THEMEFOLDER.'/'.$theme.'/themeoptions.php');
	/* prime the default theme options */
	$optionHandler = new ThemeOptions();
	setupLog(sprintf(gettext('Theme:%s option interface instantiated'),$theme),true);
}
/* then set any "standard" options that may not have been covered by the theme */
standardThemeOptions($theme, NULL);
/* and record that we finished */
setupLog(sprintf(gettext('Theme:%s setup completed'),$theme),true);
?>