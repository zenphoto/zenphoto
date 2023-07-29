<?php
/**
 * Deprecated wrapper function library for image handling functions using either GD or Imagick classes
 *
 * Use the global object `$_zp_graphics` and its methods of the same name instead of these functions.
 *
 * @package zpcore\functions\deprecated
 *
 * @deprecated 2.0 Use the global object varible $_zp_graphics and its class methods instead
 * @since 1.6
 */

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.
 * @since 1.6
 */
function zp_imageGet($imgfile) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageGet($imgfile);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_imageOutput($im, $type, $filename = NULL, $qual = 75) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageOutput($im, $type, $filename, $qual);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_createImage($w, $h) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->createImage($w, $h);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_imageFill($image, $x, $y, $color) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageFill($image, $x, $y, $color);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.
 * @since 1.6
 */
function zp_imageColorTransparent($image, $color) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageColorTransparent($image, $color);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_copyCanvas($imgCanvas, $img, $dest_x, $dest_y, $src_x, $src_y, $w, $h) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->copyCanvas($imgCanvas, $img, $dest_x, $dest_y, $src_x, $src_y, $w, $h);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_resampleImage($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->resampleImage($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_imageUnsharpMask($img, $amount, $radius, $threshold) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageUnsharpMask($img, $amount, $radius, $threshold);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_imageResizeAlpha(&$src, $w, $h) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageResizeAlpha($src, $w, $h);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_imageCanRotate() {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageCanRotate();
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_rotateImage($im, $rotate) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->rotateImage($im, $rotate);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.
 * @since 1.6
 */
function zp_imageDims($filename) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageDims($filename);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_imageIPTC($filename) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageIPTC($filename);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_imageWidth($im) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageWidth($im);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead 
 * @since 1.6.
 */
function zp_imageHeight($im) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageHeight($im);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_imageMerge($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageMerge($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_imageGray($image) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageGray($image);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.
 * @since 1.6
 */
function zp_imageKill($im) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageKill($im);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_colorAllocate($image, $red, $green, $blue) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->colorAllocate($image, $red, $green, $blue);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_writeString($image, $font, $x, $y, $string, $color) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->writeString($image, $font, $x, $y, $string, $color);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_drawRectangle($image, $x1, $y1, $x2, $y2, $color) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->drawRectangle($image, $x1, $y1, $x2, $y2, $color);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_graphicsLibInfo() {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->graphicsLibInfo();
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_getFonts() {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->getFonts();
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_imageLoadFont($font = NULL, $size = 18) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageLoadFont($font, $size);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.
 * @since 1.6
 */
function zp_imageFontWidth($font) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageFontWidth($font);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_imageFontHeight($font) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageFontHeight($font);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function imageBlurGD($imgCanvas, $imgCanvas2, $radius, $w, $h) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageBlurGD($imgCanvas, $imgCanvas2, $radius, $w, $h);
}

/**
 * @deprecated 2.0 Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead. 
 * @since 1.6
 */
function zp_imageFromString($string) {
	global $_zp_graphics;
	deprecationNotice(gettext('Use the global object $_zp_graphics and the class method with the same name (but without the "zp_" prefix) instead.'));
	return $_zp_graphics->imageFromString($string);
}