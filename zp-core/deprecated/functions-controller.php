<?php

/**
 * Common functions used in the controller for getting/setting current classes,
 * redirecting URLs, and working with the context.
 *
 * @package core
 * @subpackage functions\functions-controller
 */
// force UTF-8 Ø

/**
 * Creates a "REWRITE" url given the query parameters that represent the link
 * 
 * @deprecated 2.0 Use controller::rewriteURL() instead
 *
 * @param type $query
 * @return string
 */
function zpRewriteURL($query) {
	deprecationNotice(gettext('Use controller::rewriteURL() instead'));
	return controller::rewriteURL($query);
}

/**
 * Checks to see if the current URL is a query string url when mod_rewrite is active.
 * If so it will redirects to the rewritten URL with a 301 Moved Permanently.
 * @deprecated 2.0 Use controller::fixPathRedirect() instead
 */
function fix_path_redirect() {
	deprecationNotice(gettext('Use controller::fixPathRedirect() instead'));
	controller::fixPathRedirect();
}

/**
 * @deprecated 2.0 Use controller::loadPage() instead
 */
function zp_load_page($pagenum = NULL) {
	deprecationNotice(gettext('Use controller::loadPage() instead'));
	controller:loadPage($pagenum);
}

/**
 * initializes the gallery.
 * @deprecated 2.0 Use controller::loadGallery() instead
 */
function zp_load_gallery() {
	deprecationNotice(gettext('Use controller::loadGallery() instead'));
	controller::loadGallery();
}

/**
 * Loads the search object.
 * @deprecated 2.0 Use controller::loadSearch() instead
 */
function zp_load_search() {
	deprecationNotice(gettext('Use controller::controller::loadSearch() instead'));
	return controller::loadSearch();
}

/**
 * zp_load_album - loads the album given by the folder name $folder into the
 * global context, and sets the context appropriately.
 * 
 * @deprecated 2.0 Use controller::controller::loadAlbum() instead
 * 
 * @param $folder the folder name of the album to load. Ex: 'testalbum', 'test/subalbum', etc.
 * @param $force_cache whether to force the use of the global object cache.
 * @return the loaded album object on success, or (===false) on failure.
 */
function zp_load_album($folder, $force_nocache = false) {
	deprecationNotice(gettext('Use controller::controller::loadAlbum() instead'));
	return controller::loadAlbum($folder, $force_nocache);
}

/**
 * zp_load_image - loads the image given by the $folder and $filename into the
 * global context, and sets the context appropriately.
 * 
 * @deprecated 2.0 Use controller::controller::loadImage() instead
 * 
 * @param $folder is the folder name of the album this image is in. Ex: 'testalbum'
 * @param $filename is the filename of the image to load.
 * @return the loaded album object on success, or (===false) on failure.
 */
function zp_load_image($folder, $filename) {
	deprecationNotice(gettext('Use controller::controller::loadImage() instead'));
	return controller::loadImage($folder, $filename);
}

/**
 * Loads a zenpage pages page
 * Sets up $_zp_current_zenpage_page and returns it as the function result.
 * 
 * @deprecated 2.0 Use controller::controller::loadZenpagePages() instead
 * 
 * @param $titlelink the titlelink of a zenpage page to setup a page object directly. Used for custom
 * page scripts based on a zenpage page.
 *
 * @return object
 */
function load_zenpage_pages($titlelink) {
	deprecationNotice(gettext('Use controller::controller::loadZenpagePages() instead'));
	return controller::loadZenpagePages($titlelink);
}

/**
 * Loads a zenpage news article
 * Sets up $_zp_current_zenpage_news and returns it as the function result.
 * 
 * @deprecated 2.0 Use controller::controller::loadZenpageNews() instead
 *
 * @param array $request an array with one member: the key is "date", "category", or "title" and specifies
 * what you want loaded. The value is the date or title of the article wanted
 *
 * @return object
 */
function load_zenpage_news($request) {
	deprecationNotice(gettext('Use controller::controller::loadZenpageNews() instead'));
	return controller::loadZenpageNews($request);
}

/**
 * Figures out what is being accessed and calls the appropriate load function
 *
 * @deprecated 2.0 Use controller::controller::loadRequest() instead
 * @return bool
 */
function zp_load_request() {
	deprecationNotice(gettext('Use controller::controller::loadRequest() instead'));
	return controller::loadRequest();
}

/**
 *
 * sets up for loading the index page
 * 
 * @deprecated 2.0 Use controller::controller::prepareIndexPage() instead
 * 
 * @return string
 */
function prepareIndexPage() {
	deprecationNotice(gettext('Use controller::controller::prepareIndexPage() instead'));
	return controller::prepareIndexPage();
}

/**
 *
 * @deprecated 2.0 Use controller::controller::prepareAlbumpage()
 * sets up for loading an album page
 */
function prepareAlbumPage() {
	deprecationNotice(gettext('Use controller::controller::prepareAlbumpage()'));
	return controller::prepareAlbumpage();
}

/**
 *
 * sets up for loading an image page
 * 
 * @deprecated 2.0 Use controller::controller::prepareImagePage()
 * @return string
 */
function prepareImagePage() {
	deprecationNotice(gettext('Use controller::controller::prepareImagePage()'));
	return controller::prepareImagePage();
}

/**
 *
 * sets up for loading p=page pages
 * 
 * @deprecated 2.0 Use controller::controller::prepareCustomPage()
 * @return string
 */
function prepareCustomPage() {
	deprecationNotice(gettext('Use controller::controller::prepareCustomPage()'));
	return controller::prepareCustomPage();
}

/**
 * Handles redirections via filter hook "redirection_handler".
 * It is meant to perform redirections of pages that have been removed or renamed.
 * 
 * @deprecated 2.0 Use controller::redirectionHandler()
 * 
 * @since 1.5.2
 */
function redirectionHandler() {
	deprecationNotice(gettext('Use controller::redirectionHandler()'));
	controller::redirectionHandler();
}

/**
 * Code replaced by controller::checkLicenceAccepted() to be used after loading the class
 */
//force license page if not acknowledged
/* if (!getOption('license_accepted')) {
	if (isset($_GET['z']) && $_GET['z'] != 'setup') {
// License needs agreement
		$_GET['p'] = 'license';
		$_GET['z'] = '';
	}
} */