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
		setThemeOptionDefault('albums_per_page', 12);
		setThemeOptionDefault('images_per_row', 4);
		setThemeOptionDefault('images_per_page', 16);
		setThemeOptionDefault('thumb_size', 350); // 220
		setThemeOptionDefault('thumb_crop', 1);
		setThemeOptionDefault('thumb_crop_width', 350); // 220
		setThemeOptionDefault('thumb_crop_height', 350); // 220
		setThemeOptionDefault('image_size', 800);
		setThemeOptionDefault('image_use_side', 'longest');
		setThemeOptionDefault('gallery_index', TRUE);

		setThemeOptionDefault('zpB_homepage', true);
		setThemeOptionDefault('zpB_homepage_album_filename', '');
		setThemeOptionDefault('zpB_homepage_random_pictures', 5);
		setThemeOptionDefault('zpB_allow_search', true);
		setThemeOptionDefault('zpB_show_archive', true);
		setThemeOptionDefault('zpB_show_tags', true);
		setThemeOptionDefault('zpB_social_links', true);
		setThemeOptionDefault('zpB_show_exif', true);
		setThemeOptionDefault('zpB_use_isotope', false);

		if (class_exists('colorbox')) {
			colorbox::registerScripts(array());
		}

		if (class_exists('cacheManager')) {
			$me = basename(dirname(__FILE__));
			cacheManager::deleteThemeCacheSizes($me);
			cacheManager::addThemeCacheSize($me, getThemeOption('thumb_size'), NULL, NULL, getThemeOption('thumb_crop_width'), getThemeOption('thumb_crop_height'), NULL, NULL, true);
			cacheManager::addThemeCacheSize($me, getThemeOption('image_size'), NULL, NULL, NULL, NULL, NULL, NULL, false);
		}
	}

	function getOptionsDisabled() {
		return array('thumb_size', 'image_size', 'custom_index_page');
	}

	function getOptionsSupported() {

		$albums = $album_list = array();
		genAlbumList($album_list, NULL, ALL_ALBUMS_RIGHTS);
		foreach ($album_list as $fullfolder => $albumtitle) {
			$albums[$fullfolder] = $fullfolder;
		}

		return array(
				gettext('Homepage') => array('order' => 0, 'key' => 'zpB_homepage', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Display a home page, with a slider of random pictures, the gallery description and the latest news.')),
				gettext('Homepage slider') => array(
						'order' => 2,
						'key' => 'zpB_homepage_album_filename',
						'type' => OPTION_TYPE_SELECTOR,
						'null_selection' => '* ' . gettext('Gallery') . ' *',
						'selections' => $albums,
						'multilingual' => 0,
						'desc' =>
						gettext('Select the Album to use for the homepage slider (Dynamic albums may used).') . '<br />' .
						gettext('If Gallery is selected, the whole gallery will be used for the slider.')),
				gettext('Random pictures for homepage slider') => array('order' => 4, 'key' => 'zpB_homepage_random_pictures', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => 0, 'desc' => gettext('Number of random pictures to use for the homepage slider.')),
				gettext('Use isotope for all albums pages') => array(
						'order' => 6,
						'key' => 'zpB_use_isotope',
						'type' => OPTION_TYPE_CHECKBOX,
						'multilingual' => 0,
						'desc' =>
						gettext('Use <a href="https://isotope.metafizzy.co/" target="_blank">isotope jQuery plugin</a> for all albums pages rather than standard albums page. This album layout allows to filter pictures based on their tags.') . '<br />' .
						gettext('This album layout does not manage sub-albums (in that case, only pictures of the album are shown and you cant not access on sub-albums!).') . '<br />' .
						gettext('Rather than use isotope layout for all albums, you may also allow "multiple_layouts" plugin and then choice "album_isotope" as layout for specific albums of your gallery.')),
				gettext('Social Links') => array('order' => 8, 'key' => 'zpB_social_links', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to show some social links.')),
				gettext('Allow search') => array('order' => 10, 'key' => 'zpB_allow_search', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to enable search form.')),
				gettext('Archive View') => array('order' => 12, 'key' => 'zpB_show_archive', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Display a link to the Archive list.')),
				gettext('Tags') => array('order' => 14, 'key' => 'zpB_show_tags', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to show a tag cloud in Archive list, with all the tags of the gallery.')),
				gettext('Exif') => array('order' => 16, 'key' => 'zpB_show_exif', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Show the EXIF Data on Image page. Remember you have to check EXIFs data you want to show on options>image>information EXIF.'))
		);
	}

	function handleOption($option, $currentValue) {

	}

}

?>