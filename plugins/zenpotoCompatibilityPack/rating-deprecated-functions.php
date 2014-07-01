<?php

/*
 * Rating deprecated functions
 */

/**
 * @deprecated
 * @since 1.2.7
 */
function printImageRating($object = NULL) {
	deprecated_functions::notify(gettext('Use printRating().'));
	global $_zp_current_image;
	if (is_null($object))
		$object = $_zp_current_image;
	printRating(3, $object);
}

/**
 * @deprecated
 * @since 1.2.7
 */
function printAlbumRating($object = NULL) {
	deprecated_functions::notify(gettext('Use printRating().'));
	global $_zp_current_album;
	if (is_null($object))
		$object = $_zp_current_album;
	printRating(3, $object);
}

?>