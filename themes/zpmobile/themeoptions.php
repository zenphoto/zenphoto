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
		setOptionDefault('zp_plugin_colorbox', 1);
		setOptionDefault('colorbox_default_album', 1);
		setOptionDefault('colorbox_default_image', 1);
		setOptionDefault('colorbox_default_search', 1);
		if (class_exists('cacheManager')) {
			cacheManager::deleteThemeCacheSizes('zpMobile');
			cacheManager::addThemeCacheSize('zpMobile', NULL, 79, 79, 79, 79, NULL, NULL, true, getOption('Image_watermark'), NULL, NULL);
		}
	}

	function getOptionsSupported() {
		return array(	gettext('Allow search') => array('key' => 'Allow_search', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to enable search form.')),
								);
	}

  function getOptionsDisabled() {
  	return array('custom_index_page','image_size','thumb_size');
  }

	function handleOption($option, $currentValue) {
		
	}
}
?>
