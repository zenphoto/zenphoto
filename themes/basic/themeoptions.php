<?php

// force UTF-8 Ø

/* Plug-in for theme option handling
 * The Admin Options page tests for the presence of this file in a theme folder
 * If it is present it is linked to with a require_once call.
 * If it is not present, no theme options are displayed.
 *
 */

require_once(dirname(__FILE__) . '/functions.php');

class ThemeOptions {

	function __construct() {
		$me = basename(dirname(__FILE__));
		setThemeOptionDefault('Allow_search', true);
		setThemeOptionDefault('Theme_colors', 'light');
		setThemeOptionDefault('albums_per_page', 6);
		setThemeOptionDefault('albums_per_row', 2);
		setThemeOptionDefault('images_per_page', 20);
		setThemeOptionDefault('images_per_row', 5);
		setThemeOptionDefault('image_size', 595);
		setThemeOptionDefault('image_use_side', 'longest');
		setThemeOptionDefault('thumb_size', 100);
		setThemeOptionDefault('thumb_crop_width', 100);
		setThemeOptionDefault('thumb_crop_height', 100);
		setThemeOptionDefault('thumb_crop', 1);
		setThemeOptionDefault('thumb_transition', 1);
		setOptionDefault('colorbox_' . $me . '_album', 1);
		setOptionDefault('colorbox_' . $me . '_image', 1);
		setOptionDefault('colorbox_' . $me . '_search', 1);
		if (class_exists('cacheManager')) {
			if (getThemeOption('thumb_crop')) {
				$thumb_cw = getThemeOption('thumb_crop_width');
				$thumb_ch = getThemeOption('thumb_crop_height');
			} else {
				$thumb_cw = $thumb_ch = NULL;
			}
			if (getOption('Image_watermark')) {
				$thumb_wmk = getOption('Image_watermark');
			} else {
				$thumb_wmk = NULL;
			}
			if (getOption('fullimage_watermark')) {
				$img_wmk = getOption('fullimage_watermark');
			} else {
				$img_wmk = NULL;
			}
			if (getThemeOption('thumb_gray')) {
				$thumb_effect = 'gray';
			} else {
				$thumb_effect = NULL;
			}
			if (getThemeOption('image_gray')) {
				$img_effect = 'gray';
			} else {
				$img_effect = NULL;
			}
			cacheManager::deleteThemeCacheSizes($me);
			cacheManager::addThemeCacheSize($me, getThemeOption('thumb_size'), NULL, NULL, $thumb_cw, $thumb_ch, NULL, NULL, true, $thumb_wmk, $thumb_effect, NULL);
			cacheManager::addThemeCacheSize($me, getThemeOption('image_size'), NULL, NULL, NULL, NULL, NULL, NULL, false, $img_wmk, $img_effect, NULL);
		}
	}

	function getOptionsSupported() {
		return array(gettext('Allow search')	 => array('key' => 'Allow_search', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to enable search form.')),
						gettext('Theme colors')	 => array('key' => 'Theme_colors', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Select the colors of the theme'))
		);
	}

	function getOptionsDisabled() {
		return array('custom_index_page');
	}

	function handleOption($option, $currentValue) {
		global $themecolors;
		if ($option == 'Theme_colors') {
			echo '<select id="EF_themeselect_colors" name="' . $option . '"' . ">\n";
			generateListFromArray(array($currentValue), $themecolors, false, false);
			echo "</select>\n";
		}
	}

}

?>