<?php

/**
 * Filters out images/albums from the filesystem lists
 * This plugin is intended as an example of the use of the album_filter and image_filter filters.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage example
 * @category package
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Filter out files from albums and image searches that we do not want shown.");
$option_interface = 'filter_file_searches_options';

$mysetoptions = array();
$alloptionlist = getOptionList();
$enablealbum = false;
$enableimage = false;

foreach ($alloptionlist as $key => $option) {
	if (($option == 1) && strpos($key, 'filter_file_searches_') === 0) {
		$mysetoptions[] = $key;
		if (strpos($key, 'filter_file_searches_albums_') === 0) {
			$enablealbum = true;
		}
		if (strpos($key, 'filter_file_searches_images_') === 0) {
			$enableimage = true;
		}
	}
}
if ($enablealbum)
	zp_register_filter('album_filter', 'filterAlbums');
if ($enableimage) {
	zp_register_filter('image_filter', 'filterImages');
	zp_register_filter('upload_filetypes', 'filterImageUploads');
}

/**
 * Plugin option handling class
 *
 */
class filter_file_searches_options {

	function __construct() {

	}

	function getOptionsSupported() {
		global $_zp_gallery, $_zp_images_classes, $mysetoptions;

		$dir = opendir($albumdir = $_zp_gallery->getAlbumDir());
		$albums = array();

		while ($dirname = readdir($dir)) {
			if ((is_dir($albumdir . $dirname) && (substr($dirname, 0, 1) != '.')) || hasDynamicAlbumSuffix($dirname)) {
				$albums[] = filesystemToInternal($dirname);
			}
		}
		closedir($dir);


		$albums = array_unique($albums);
		natcasesort($albums);
		$lista = array();
		foreach ($albums as $album) {
			$lista[$album] = 'filter_file_searches_albums_' . $album;
		}
		$list = array_keys($_zp_images_classes);
		natcasesort($list);
		$listi = array();
		foreach ($list as $suffix) {
			$listi[$suffix] = 'filter_file_searches_images_' . $suffix;
		}
		return array(gettext('Albums') => array('key' => 'filter_file_searches_albums', 'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => $lista,
						'desc' => gettext("Check album names to be ignored.")),
				gettext('Images') => array('key' => 'filter_file_searches_images', 'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => $listi,
						'desc' => gettext('Check image suffixes to be ignored.'))
		);
	}

	function handleOption($option, $currentValue) {

	}

	function loadAlbumNames($albumdir) {
		if (!is_dir($albumdir) || !is_readable($albumdir)) {
			return array();
		}

		$dir = opendir($albumdir);
		$albums = array();

		while ($dirname = readdir($dir)) {
			$dirname = filesystemToInternal($dirname);
			if ((is_dir($albumdir . $dirname) && $dirname{0 } != '.')) {
				$albums = array_merge(array($dirname), $this->loadAlbumNames($albumdir . $dirname . '/'));
			}
		}
		closedir($dir);
		return $albums;
	}

}

/**
 * Removes unwanted albums from the list found on Disk
 *
 * @param array $album_array list of albums found
 * @return array
 */
function filterAlbums($album_array) {
	$new_list = array();
	foreach ($album_array as $album) {
		if (!getOption('filter_file_searches_albums_' . $album)) {
			$new_list[] = $album;
		}
	}
	return $new_list;
}

/**
 * Removes unwanted images from the list returned from the filesystem
 *
 * @param array $image_array the list of images found
 * @return array
 */
function filterImages($image_array) {
	$new_list = array();
	foreach ($image_array as $image) {
		if (!getOption('filter_file_searches_images_' . getSuffix($image))) {
			$new_list[] = $image;
		}
	}
	return $new_list;
}

function filterImageUploads($types) {
	$options = getOptionList();
	foreach ($options as $option => $value) {
		if (strpos($option, 'filter_file_searches_images_') !== false) {
			if ($value) {
				$key = array_search(substr($option, 28), $types);
				if ($key !== false) {
					unset($types[$key]);
				}
			}
		}
	}
	return $types;
}

?>