<?php

/* Totally hides unpublished images from not signed in viewers.
 *
 * @author Stephen Billard (sbillard)
 * 
 * @package plugins
 * @subpackage example
 * @category package
 */
$plugin_is_filter = 5 | THEME_PLUGIN;
$plugin_description = gettext('Prevents guest viewers from viewing unpublished images albums.');
$plugin_author = "Stephen Billard (sbillard)";

if (!OFFSET_PATH) {
	zp_register_filter('album_instantiate', 'no_show_hideAlbum');
	zp_register_filter('image_instantiate', 'no_show_hideImage');
}

function no_show_hideImage($imageObj) {
	$album = $imageObj->getAlbum();
	$check = checkAlbumPassword($album);
	if ($check == 'zp_public_access') {
		$imageObj->exists = $imageObj->getShow();
	}
	return $imageObj;
}

function no_show_hideAlbum($albumObj) {
	$check = checkAlbumPassword($albumObj);
	if ($check == 'zp_public_access') {
		$albumObj->exists = $albumObj->getShow();
	}
	return $albumObj;
}

?>