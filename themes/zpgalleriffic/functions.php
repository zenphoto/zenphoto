<?php

$_zp_page_check = 'my_checkPageValidity';

function my_checkPageValidity($request, $gallery_page, $page) {
	switch ($gallery_page) {
		case 'index.php':
			if (extensionEnabled('zenpage')) {
				if (checkForPage(getOption("zpgal_homepage"))) {
					return $page == 1; // only one page if enabled.
				}
			}
			break;
		case 'news.php':
		case 'album.php':
		case 'search.php':
			break;
		default:
			if ($page != 1) {
				return false;
			}
	}
	return checkPageValidity($request, $gallery_page, $page);
}
