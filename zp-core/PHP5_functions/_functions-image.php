<?php
/**
 * cacheImage_protected
 * @package functions
 *
 */
/**
 * Provides an error protected cacheImage for PHP 5
 *
 */
function cacheImage_protected($newfilename, $imgfile, $args, $allow_watermark=false, $theme, $album) {
	try {
		cacheImage($newfilename, $imgfile, $args, $allow_watermark, $theme, $album);
		return true;
	} catch (Exception $e) {
		debugLog('cacheImage('.$newfilename.') exception: '.$e->getMessage());
		return false;
	}
}

?>