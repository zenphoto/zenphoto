<?php

/**
 * Root-level include that handles all user requests.
 * @package core
 */

// force UTF-8 Ø


require_once(dirname(__FILE__).'/functions-controller.php');


// Initialize the global objects and object arrays:
$_zp_current_album = NULL;
$_zp_current_album_restore = NULL;
$_zp_albums = NULL;
$_zp_current_image = NULL;
$_zp_current_image_restore = NULL;
$_zp_images = NULL;
$_zp_current_comment = NULL;
$_zp_comments = NULL;
$_zp_current_context = 0;
$_zp_current_search = NULL;
$_zp_current_zenpage_news = NULL;
$_zp_current_zenpage_page = NULL;
$_zp_current_category = NULL;
$_zp_post_date = NULL;
$_zp_pre_authorization = array();

/*** Request Handler **********************
 ******************************************/
// This is the main top-level action handler for user requests. It parses a
// request, validates the input, loads the appropriate objects, and sets
// the context. All that is done in functions-controller.php.

zp_load_gallery();	//	load the gallery and set the context to be on the front-end
$zp_request = zp_load_request();

// handle any passwords that might have been posted
if (!zp_loggedin()) {
	zp_handle_password();
}

// Handle any comments that might be posted.
if (getOption('zp_plugin_comment_form') &&
		( (commentsAllowed('comment_form_albums') && in_context(ZP_ALBUM) && !in_context(ZP_IMAGE) && $_zp_current_album->getCommentsAllowed()) ||
			(commentsAllowed('comment_form_images') && in_context(ZP_IMAGE) && $_zp_current_image->getCommentsAllowed()) ||
			(commentsAllowed('comment_form_articles') && in_context(ZP_ZENPAGE_NEWS_ARTICLE) && $_zp_current_zenpage_news->getCommentsAllowed()) ||
			(commentsAllowed('comment_form_pages') && in_context(ZP_ZENPAGE_PAGE) && $_zp_current_zenpage_page->getCommentsAllowed()) )
		){
	$_zp_comment_error = zp_handle_comment();
}

/*** Server-side AJAX Handling ***********
 ******************************************/
if (zp_loggedin() && getOption('edit_in_place')) {
	if ( !empty($_POST["eip_context"] ) &&  !empty($_POST["eip_field"] ) ) {
		editInPlace_handle_request($_POST["eip_context"], $_POST["eip_field"], $_POST["new_value"], $_POST["orig_value"]);
	}
}


/*** Consistent URL redirection ***********
 ******************************************/
// Check to see if we use mod_rewrite, but got a query-string request for a page.
// If so, redirect with a 301 to the correct URL. This must come AFTER the Ajax init above,
// and is mostly helpful for SEO, but also for users. Consistent URLs are a Good Thing.

fix_path_redirect();

?>
