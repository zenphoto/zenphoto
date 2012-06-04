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
require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/cacheManager.php');
require_once(SERVERPATH.'/'.THEMEFOLDER.'/'.$theme.'/themeoptions.php');
/* prime the default theme options */
$optionHandler = new ThemeOptions();
/* then set any "standard" options that may not have been covered by the theme */
setThemeOption('albums_per_page', 6, NULL, $theme, true);
setThemeOption('albums_per_row', 3, NULL, $theme, true);
setThemeOption('images_per_page', 20, NULL, $theme, true);
setThemeOption('images_per_row', 5, NULL, $theme, true);
setThemeOption('image_size', 595, NULL, $theme, true);
setThemeOption('image_use_side', 'longest', NULL, $theme, true);
setThemeOption('thumb_size', 100, NULL, $theme, true);
setThemeOption('thumb_crop_width', 100, NULL, $theme, true);
setThemeOption('thumb_crop_height', 100, NULL, $theme, true);
setThemeOption('thumb_crop', 1, NULL, $theme, true);
setThemeOption('thumb_transition', 1, NULL, $theme, true);
/* and record that we finished */
setupLog(sprintf(gettext('Set theme default options for %s completed'),$theme),true);
?>