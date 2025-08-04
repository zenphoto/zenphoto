<?php

$_zp_gallery->setAlbumPublish((int) isset($_POST['album_default']));
$_zp_gallery->setImagePublish((int) isset($_POST['image_default']));

setOption('AlbumThumbSelect', sanitize_numeric($_POST['thumbselector']));
$_zp_gallery->setThumbSelectImages((int) isset($_POST['thumb_select_images']));
$_zp_gallery->setSecondLevelThumbs((int) isset($_POST['multilevel_thumb_select_images']));
$_zp_gallery->setTitle(process_language_string_save('gallery_title', 2));
$_zp_gallery->setDesc(process_language_string_save('Gallery_description', EDITOR_SANITIZE_LEVEL));

$_zp_gallery->setCopyrightNotice(process_language_string_save('copyright_site_notice', EDITOR_SANITIZE_LEVEL));
$_zp_gallery->setCopyrightURL(sanitize($_POST['copyright_site_url'], 3));
$_zp_gallery->set('copyright_site_url_custom', sanitize($_POST['copyright_site_url_custom'], 3));
setOption('display_copyright_notice', (int) isset($_POST['display_copyright_notice']));
$_zp_gallery->set('copyright_site_rightsholder', sanitize($_POST['copyright_site_rightsholder'], 3));
$_zp_gallery->set('copyright_site_rightsholder_custom', sanitize($_POST['copyright_site_rightsholder_custom'], 3));

$_zp_gallery->setParentSiteTitle(process_language_string_save('website_title', 2));
$web = sanitize($_POST['website_url'], 3);
$_zp_gallery->setParentSiteURL($web);
$_zp_gallery->setAlbumUseImagedate((int) isset($_POST['album_use_new_image_date']));
$st = strtolower(sanitize($_POST['gallery_sorttype'], 3));
if ($st == 'custom')
	$st = strtolower(sanitize($_POST['customalbumsort'], 3));
$_zp_gallery->setSortType($st);
if (($st == 'manual') || ($st == 'random')) {
	$_zp_gallery->setSortDirection(false);
} else {
	$_zp_gallery->setSortDirection(isset($_POST['gallery_sortdirection']));
}
foreach ($_POST as $item => $value) {
	if (strpos($item, 'gallery-page_') === 0) {
		$item = sanitize(substr(postIndexDecode($item), 13));
		$_zp_gallery->setUnprotectedPage($item, (int) isset($_POST['gallery_page_unprotected_' . $item]));
	}
}
$_zp_gallery->setSecurity(sanitize($_POST['gallery_security'], 3));
$notify = processCredentials($_zp_gallery);
if (zp_loggedin(CODEBLOCK_RIGHTS)) {
	$_zp_gallery->setCodeblock(processCodeblockSave(0));
}
$_zp_gallery->save();
$returntab = "&tab=gallery";
