<?php

// force UTF-8 Ø

global $_firstPageImages;
$np = getOption('images_per_page');
if ($_firstPageImages > 0)  {
	$_firstPageImages = $_firstPageImages - 1;
	$myimagepagestart = 1;
} else {
	$_firstPageImages = $np - 1;
	$myimagepagestart = 0;
}
$myimagepage = $myimagepagestart + getCurrentPage() - getTotalPages(true);
if ($myimagepage > 1 ) {
	$link_slides = 2;
} else {
	$link_slides = 1;
}
setOption('images_per_page', $np - $link_slides, false);
$_firstPageImages = NULL;
setOption('custom_index_page', 'gallery', false);
define('ALBUM_THUMB_WIDTH',210);
define('ALBUM_THUMB_HEIGHT',59);
?>