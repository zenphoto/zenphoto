<?php

// force UTF-8 Ø

/* Plug-in for theme option handling
 * The Admin Options page tests for the presence of this file in a theme folder
 * If it is present it is linked to with a require_once call.
 * If it is not present, no theme options are displayed.
 *
*/

class ThemeOptions {

	function ThemeOptions() {
		setThemeOptionDefault('Allow_search', true);
		setThemeOptionDefault('thumb_transition', 1);
		setOptionDefault('colorbox_default_album', 1);
		setOptionDefault('colorbox_default_image', 1);
		setOptionDefault('colorbox_default_search', 1);
		setThemeOption('thumb_size',79, NULL, 'zpmobile');
		setThemeOptionDefault('thumb_crop_width', 79);
		setThemeOptionDefault('thumb_crop_height', 79);
		setThemeOptionDefault('thumb_crop', 1);
		setThemeOption('custom_index_page', 'gallery', NULL, 'zpmobile', false);
		setThemeOptionDefault('albums_per_page', 6);
		setThemeOptionDefault('albums_per_row', 1);
		setThemeOptionDefault('images_per_page', 24);
		setThemeOptionDefault('images_per_row', 6);
		if (class_exists('cacheManager')) {
			$me = basename(dirname(__FILE__));
			cacheManager::deleteThemeCacheSizes($me);
			cacheManager::addThemeCacheSize($me, NULL, 79, 79, 79, 79, NULL, NULL, true, getOption('Image_watermark'), NULL, NULL);
		}
	}

	function getOptionsSupported() {
		return array(
			gettext('Allow search') => array('key' => 'Allow_search', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to enable search form.')),
			gettext('Allow direct link from multimedia') => array('key' => 'zpmobile_mediadirectlink', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to enable a direct link to multimedia items on the single image page in case the player is not supported by the device but the actual format is.'))
		);
	}

  function getOptionsDisabled() {
  	return array('custom_index_page','image_size','thumb_size');
  }

	function handleOption($option, $currentValue) {

	}
}
?>