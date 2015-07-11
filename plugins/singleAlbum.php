<?php

/**
 * This plugin will intercept the load process and force references to the index page to
 * the the single album of the installation
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage example
 * @category package
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext('Forces a defined album as the index page.');
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('load_request', 'forceAlbum');

function forceAlbum($success) {
	// we presume that the site only serves the one album.
	$gallery = new Gallery();
	$albums = $gallery->getAlbums();
	$_GET['album'] = array_shift($albums);
	return $success;
}

?>