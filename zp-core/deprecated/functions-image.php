<?php
/**
 * image processing functions
 * @package core
 * @subpackage functions\functions-image
 *
 */
// force UTF-8 Ø
// functions-image.php - HEADERS NOT SENT YET!

/**
 * 
 * @deprecated 2.0 Use imageProcessor::imageError()
 * 
 * @global string $newfilename
 * @global string $album
 * @global string $image
 * @param string $status_text
 * @param string $errormessage the error message to print if $_GET['debug'] is set.
 * @param string $errorimg the filename of the error image to display for production. Defaults to 'err-imagegeneral.png'. Images should be located in /zp-core/images_errors .
 * @param string $image
 * @param string $album
 * @param string $newfilename
 */
function imageError($status_text, $errormessage, $errorimg = 'err-imagegeneral.png', $image = '', $album = '', $newfilename = '') {
	deprecationNotice(gettext('Use imageProcessor::imageError()'));
	imageProcessor::imageError($status_text, $errormessage, $errorimg, $image, $album, $newfilename);
}

/**
 * Prints debug information from the arguments to i.php.
 * 
 * @deprecated 2.0 Use imageProcessor::imageDebug()
 *
 * @param string $album alubm name
 * @param string $image image name
 * @param array $args size/crop arguments
 * @param string $imgfile the filename of the image
 */
function imageDebug($album, $image, $args, $imgfile) {
	deprecationNotice(gettext('Use imageProcessor::imageDebug()'));
	imageProcessor::imageDebug($album, $image, $args, $imgfile);
}

/**
 * Calculates proprotional width and height
 * Used internally by cacheImage
 *
 * Returns array containing the new width and height
 * 
 * @deprecated 2.0 Use imageProcessor::propSizes()
 *
 * @param int $size
 * @param int $width
 * @param int $height
 * @param int $w
 * @param int $h
 * @param int $thumb
 * @param int $image_use_side
 * @param int $dim
 * @return array
 */
function propSizes($size, $width, $height, $w, $h, $thumb, $image_use_side, $dim) {
	deprecationNotice(gettext('Use imageProcessor::propSizes()'));
	return imageProcessor::propSizes($size, $width, $height, $w, $h, $thumb, $image_use_side, $dim);
}

/**
 * iptc_make_tag() function by Thies C. Arntzen
 * 
 * @deprecated 2.0 Use imageProcessor::iptcMakeTag()
 * 
 * @param $rec
 * @param $data
 * @param $value
 */
function iptc_make_tag($rec, $data, $value) {
	deprecationNotice(gettext('Use imageProcessor::iptcMakeTag()'));
	return imageProcessor::iptcMakeTag($rec, $data, $value);
}

/**
 * Creates the cache folder version of the image, including watermarking
 * 
 * @deprecated 2.0 Use imageProcessor::cacheImage(
 *
 * @param string $newfilename the name of the file when it is in the cache
 * @param string $imgfile the image name
 * @param array $args the cropping arguments
 * @param bool $allow_watermark set to true if image may be watermarked
 * @param string $theme the current theme
 * @param string $album the album containing the image
 */
function cacheImage($newfilename, $imgfile, $args, $allow_watermark = false, $theme = '', $album = '') {
	deprecationNotice(gettext('Use imageProcessor::cacheImage()'));
	return imageProcessor::cacheImage($newfilename, $imgfile, $args, $allow_watermark, $theme, $album);
}

/**
 * Determines the rotation of the image by looking at EXIF information.
 * 
 * Returns an array with two indexes "rotate" (= degree to rotate) and "flip" ("horizontal" or "vertical") 
 * or false if nothing applies
 *
 * @since 1.6.1 Return values changed from string|false to array|false
 * 
 * @deprecated 2.0 Use imageProcessor::getImageRotation(
 * 
 * @param string $imgfile the image name
 * @return array|false
 */
function getImageRotation($imgfile) {
	deprecationNotice(gettext('Use imageProcessor::getImageRotation()'));
	return imageProcessor::getImageRotation($imgfile);
}

/**
 * Adds a watermark to a resized image. If no watermark is set it just returns the image
 * 
 * @since 1.5.3 - consolidated from cacheImage() and full-image.php
 * 
 * @deprecated 2.0 Use imageProcessor::addWatermark()
 * 
 * @param resource|object $newim GD image resource or Imagick object
 * @param string $watermark_image The path to the watermark to use
 * @param string $imgfile Path to the image being processed (optionally for debugging only)
 * @return resource|object
 */
function addWatermark($newim, $watermark_image, $imgfile = null) {
	deprecationNotice(gettext('Use imageProcessor::addWatermark()'));
	return imageProcessor::addWatermark($newim, $watermark_image, $imgfile);
}

/**
 * Checks if an processed image is a GD library image
 * 
 * @since 1.6
 * 
 * @deprecated 2.0 Use imageProcessor::isGDImage()
 * 
 * @param mixed $image And Image resource (PHP < 8) or GDImage object (PHP 8+)
 * @return boolean
 */
function isGDImage($image) {
	deprecationNotice(gettext('Use imageProcessor::isGDImage()'));
	return imageProcessor::isGDImage($image);
}