<?php

/* Plug-in for theme option handling
 * The Admin Options page tests for the presence of this file in a theme folder
 * If it is present it is linked to with a require_once call.
 * If it is not present, no theme options are displayed.
 */

require_once(SERVERPATH . '/' . ZENFOLDER . '/admin-functions.php');

class ThemeOptions {

	function __construct() {
		setThemeOptionDefault('albums_per_row', 3);
		setThemeOptionDefault('albums_per_page', 9);
		setThemeOptionDefault('images_per_row', 4);
		setThemeOptionDefault('images_per_page', 16);
		setThemeOptionDefault('thumb_size', 220);
		setThemeOptionDefault('thumb_crop', 1);
		setThemeOptionDefault('thumb_crop_width', 220);
		setThemeOptionDefault('thumb_crop_height', 220);
		setThemeOptionDefault('image_size', 800, NULL);
		setThemeOptionDefault('image_use_side', 'longest');

		setThemeOptionDefault('zpB_homepage', true);
		setThemeOptionDefault('allow_search', true);
		setThemeOptionDefault('zpB_show_archive', true);
		setThemeOptionDefault('zpB_show_tags', true);
		setThemeOptionDefault('zpB_social_links', true);
		setThemeOptionDefault('zpB_show_exif', true);

		// configure some zenphoto plugin options
		if (class_exists('colorbox')) {
			colorbox::registerScripts(array('album', 'favorites', 'image', 'search'));
		}
		if (class_exists('slideshow')) {
			slideshow::registerScripts(array('album', 'favorites', 'image', 'search'));
		}
		if (class_exists('cycle')) {
			cycle::registerScripts(array('album', 'favorites', 'image', 'search'));
		}

		if (class_exists('cacheManager')) {
			$me = basename(dirname(__FILE__));
			cacheManager::deleteThemeCacheSizes($me);
			cacheManager::addThemeCacheSize($me, getThemeOption('thumb_size'), NULL, NULL, getThemeOption('thumb_crop_width'), getThemeOption('thumb_crop_height'), NULL, NULL, true);
			cacheManager::addThemeCacheSize($me, getThemeOption('image_size'), NULL, NULL, NULL, NULL, NULL, NULL, false);
		}
	}

	function getOptionsDisabled() {
		return array('thumb_size', 'image_size');
	}

	function getOptionsSupported() {
		return array(
				gettext('Homepage') => array('order' => 1, 'key' => 'zpB_homepage', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext("Display a home page, with a slider of 5 random picts, the gallery description and the latest news.")),
				gettext('Social Links') => array('order' => 2, 'key' => 'zpB_social_links', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext("Check to show some social links.")),
				gettext('Allow search') => array('order' => 5, 'key' => 'allow_search', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext("Check to enable search form.")),
				gettext('Archive View') => array('order' => 6, 'key' => 'zpB_show_archive', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext("Display a link to the Archive list.")),
				gettext('Tags') => array('order' => 7, 'key' => 'zpB_show_tags', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext("Check to show a tag cloud in Archive list, with all the tags of the gallery.")),
				gettext('Exif') => array('order' => 7, 'key' => 'zpB_show_exif', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Show the EXIF Data on Image page. Remember you have to check EXIFs data you want to show on admin>image>information EXIF.'))
		);
	}

	function handleOption($option, $currentValue) {

	}

}

?>