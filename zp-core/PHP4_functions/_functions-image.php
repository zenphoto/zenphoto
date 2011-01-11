<?php
/**
 * cacheImage_protected
 * @package functions
 *
 */
/**
 * Provides an [not] error protected cacheImage for PHP 4
 *
  */
function cacheImage_protected($newfilename, $imgfile, $args, $allow_watermark=false, $theme, $album) {
	cacheImage($newfilename, $imgfile, $args, $allow_watermark, $theme, $album);
	return true;
}

?>