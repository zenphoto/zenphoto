<?php
// force UTF-8 Ø

if (!OFFSET_PATH) {
	define('TEXT_INPUT_SIZE', 50);

	setOption('personnal_thumb_width', '360', false);
	setOption('personnal_thumb_height', '180', false);

	setOption('comment_form_toggle', false, true);		// override this option of comment_form, to avoid JS conflits
	setOption('comment_form_pagination', false, true);	// override this option of comment_form, to avoid JS conflits
	setOption('tinymce4_comments', null, true);			// force this option to disable tinyMCE for comment form

	/* override this option called by user_login-out plugin, to avoid colorbox conflict */
	if (getOption('user_logout_login_form') == 2) {
		setOption('user_logout_login_form', 1);
	}

	// Check for mobile and tablets, set some options if so...
	require_once (SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/mobileTheme/Mobile_Detect.php');
	$detect = new Mobile_Detect;
	if ($detect->isTablet()) {
		$isTablet = true;
	} else {
		$isTablet = false;
	}
	if (($detect->isMobile()) && (!$detect->isTablet())) {
		$isMobile = true;
	} else {
	$isMobile = false;
	}
	if ($isMobile) { 
		// setOption('personnal_thumb_width', '360', false);
		// setOption('personnal_thumb_height', '180', false);
		// setOption('image_size', 400, false);
	}

	$_zp_page_check = 'my_checkPageValidity';
	$_zenpage_enabled = extensionEnabled('zenpage');
}

function my_checkPageValidity($request, $gallery_page, $page) {
	if (($gallery_page == 'gallery.php') || ($gallery_page == 'home.php')){
		$gallery_page = 'index.php';
	}
	return checkPageValidity($request, $gallery_page, $page);
}
?>