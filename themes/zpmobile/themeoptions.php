<?php

// force UTF-8 Ø

/* Plug-in for theme option handling
 * The Admin Options page tests for the presence of this file in a theme folder
 * If it is present it is linked to with a require_once call.
 * If it is not present, no theme options are displayed.
 *
 */
class ThemeOptions {

	function __construct() {
		$me = basename(dirname(__FILE__));
		setThemeOptionDefault('Allow_search', true);
		setThemeOptionDefault('thumb_transition', 1);
		setOptionDefault('colorbox_default_album', 1);
		setOptionDefault('colorbox_default_image', 1);
		setOptionDefault('colorbox_default_search', 1);
		setThemeOption('thumb_size', 79, NULL, 'zpmobile');
		setThemeOptionDefault('thumb_crop_width', 79);
		setThemeOptionDefault('thumb_crop_height', 79);
		setThemeOptionDefault('thumb_crop', 1);
		setThemeOption('custom_index_page', 'gallery', NULL, 'zpmobile', false);
		setThemeOptionDefault('albums_per_page', 6);
		setThemeOptionDefault('albums_per_row', 1);
		setThemeOptionDefault('images_per_page', 24);
		setThemeOptionDefault('images_per_row', 6);
		if (class_exists('cacheManager')) {
			cacheManager::deleteCacheSizes('zpMobile');
			cacheManager::addDefaultSizedImageSize('zpMobile');
			$img_wmk = getOption('fullimage_watermark') ? getOption('fullimage_watermark') : null;
			$thumb_wmk = getOption('Image_watermark') ? getOption('Image_watermark') : null;
			$img_effect = getOption('image_gray') ? 'gray' : null;
			$thumb_effect = getThemeOption('thumb_gray') ? 'gray' : null;
			cacheManager::addCacheSize('zpMobile', NULL, 640, 640, NULL, NULL, NULL, NULL, NULL, $img_wmk, $img_effect, true);
			cacheManager::addCacheSize('zpMobile', NULL, 230, 230, 230, 230, NULL, NULL, true, $img_wmk, $thumb_effect, false);
			cacheManager::addCacheSize('zpMobile', NULL, 79, 79, 79, 79, NULL, NULL, true, $thumb_wmk, $thumb_effect, false);
		}
	}

	function getOptionsSupported() {
		return array(
				gettext('Allow search') => array(
						'key' => 'Allow_search',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Check to enable search form.')),
				gettext('Allow direct link from multimedia') => array(
						'key' => 'zpmobile_mediadirectlink',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Check to enable a direct link to multimedia items on the single image page in case the player is not supported by the device but the actual format is.'))
		);
	}

	function getOptionsDisabled() {
		return array('custom_index_page', 'image_size', 'thumb_size');
	}

	function handleOption($option, $currentValue) {
		
	}

}

?>