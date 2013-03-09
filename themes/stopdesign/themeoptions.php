<?php

// force UTF-8 Ø

class ThemeOptions {

	function ThemeOptions() {
		/* put any setup code needed here */
		setThemeOptionDefault('Allow_search', true);
		setThemeOptionDefault('Mini_slide_selector', 'Recent images');
		setThemeOption('albums_per_page', 9, NULL, 'stopdesign');
		setThemeOption('albums_per_row', 3, NULL, 'stopdesign');
		setThemeOption('images_per_page', 24, NULL, 'stopdesign');
		setThemeOption('images_per_row', 6, NULL, 'stopdesign');
		setThemeOption('image_size', 480, NULL, 'stopdesign');
		setThemeOption('image_use_side', 'longest', NULL, 'stopdesign');
		setThemeOption('thumb_size',89, NULL, 'stopdesign');
		setThemeOptionDefault('thumb_crop_width', 89);
		setThemeOptionDefault('thumb_crop_height', 89);
		setThemeOptionDefault('thumb_crop', 1);
		setThemeOptionDefault('thumb_transition', 1);
		setOptionDefault('colorbox_stopdesign_album', 1);
		setOptionDefault('colorbox_stopdesign_image', 1);
		setOptionDefault('colorbox_stopdesign_search', 1);
		if (class_exists('cacheManager')) {
			$me = basename(dirname(__FILE__));
			cacheManager::deleteThemeCacheSizes($me);
			cacheManager::addThemeCacheSize($me, 480, NULL, NULL, NULL, NULL, NULL, NULL, false, getOption('fullimage_watermark'), NULL, NULL);
			cacheManager::addThemeCacheSize($me, NULL, NULL, 89, 67, 89, NULL, NULL, true, getOption('Image_watermark'), NULL, NULL);
			cacheManager::addThemeCacheSize($me, NULL, 89, NULL, 89, 67, NULL, NULL, true, getOption('Image_watermark'), NULL, NULL);
			cacheManager::addThemeCacheSize($me, NULL, 210, 59, 310, 59, NULL, NULL, true, getOption('Image_watermark'), NULL, NULL);
		}
	}

	function getOptionsSupported() {
		return array(	gettext('Allow search') => array('key' => 'Allow_search', 'type' => OPTION_TYPE_CHECKBOX,
													'desc' => gettext('Check to enable search form.')),
									gettext('Mini slide selector') => array('key' => 'Mini_slide_selector', 'type' => OPTION_TYPE_SELECTOR,
													'selections' => array(gettext('Recent images') => 'Recent images', gettext('Random images') => 'Random images'),
													'desc' => gettext('Select what you want for the six special slides.'))
									);
	}

	function getOptionsDisabled() {
		return array('thumb_size','thumb_crop','albums_per_row','albums_per_page','images_per_row','images_per_page','image_size','custom_index_page');
	}

	function handleOption($option, $currentValue) {
	}

}
?>