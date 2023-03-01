<?php

/**
 * Base class that kicks in if neither GD or Imagick are available for image handling
 * Provides basic check method to see what library is available
 * 
 * @since 1.6 - reworked as class
 * 
 * @package zpcore\classes\graphics
 */
class graphicsBase {
	
	public $info = array();
	public $gd_loaded = false;
	public $fontlist = array();
	public $generalinfo = array();
	public $gd_present = false;
	public $imagick_present = false;
	public $imagick_version = '';
	public $imagick_version_pass = false;
	public $imagemagick_version = '';
	public $imagemagick_version_pass = false;
	
	function __construct() {
		$this->info['Library'] = 'None';
		$this->info['Library_desc'] = '<p class="error">' . gettext('There is no PHP Graphics support.') . '</p>';
		if (DEBUG_IMAGE) {
			debugLog($this->info['Library_desc']);
		}
		$this->checkGraphicSupport();
	}
	
	/**
	 * Check if a required graphics libary is available on the system
	 * Mzst be  called by every graphics class related constructor to ensure that general properties are set properly
	 * 
	 * @global boolean $_zp_gd_present
	 * @global type $_zp_imagick_present
	 * @global type $_zp_imagick_version
	 * @global type $_zp_imagick_version_pass
	 */
  function checkGraphicSupport() {
		if (extension_loaded('gd')) {
			$this->gd_present = true;
			$info = gd_info();
			$this->generalinfo['GD'] = sprintf(gettext('PHP GD library <em>%s</em>'), $info['GD Version']);
		}
		$this->imagick_version = phpversion('imagick');
		$this->imagick_version_pass = version_compare($this->imagick_version, IMAGICK_REQUIRED_VERSION, '>=');
		$this->imagick_present = extension_loaded('imagick') && $this->imagick_version_pass;
		if ($this->imagick_present) {
			@$this->imagemagick_version = Imagick::getVersion();
			preg_match('/\d+(\.\d+)*/', $this->imagemagick_version['versionString'], $matches);
			$this->imagemagick_version['versionNumber'] = $matches[0];
			$this->imagemagick_version_pass = version_compare($this->imagemagick_version['versionNumber'], IMAGEMAGICK_REQUIRED_VERSION, '>=');
			$this->imagick_present &= $this->imagick_version_pass;
			unset($matches);
			$this->generalinfo['Imagick'] = sprintf(gettext('PHP Imagick library <em>%s</em>') . '<br /><em>%s</em>', $this->imagick_version, $this->imagemagick_version['versionString']);
		}
	}

	function imageGet($imgfile) {
		return false;
	}

	function imageOutput($im, $type, $filename = NULL, $qual = 75) {
		return false;
	}

	function createImage($w, $h) {
		return false;
	}

	function imageFill($image, $x, $y, $color) {
		return false;
	}

	function imageColorTransparent($image, $color) {
		return false;
	}

	function copyCanvas($imgCanvas, $img, $dest_x, $dest_y, $src_x, $src_y, $w, $h) {
		return false;
	}

	function resampleImage($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
		return false;
	}

	function imageUnsharpMask($img, $amount, $radius, $threshold) {
		return false;
	}

	function imageResizeAlpha($src, $w, $h) {
		return false;
	}
	
	function imageResizeTransparent($src, $w, $h) {
		return false;
	}

	function imageCanRotate() {
		return false;
	}

	function rotateImage($im, $rotate) {
		return false;
	}
	
	function flipImage($im, $direction) {
		return false;
	}
	
	/**
	 * Rotates and/or flips an image based on by using rotateImage() and flipImage() methods
	 * 
	 * @since 1.6.1
	 * 
	 * @param object $im GDImage or imagick image object
	 * @param array $rotate Two dimensional array with rotate and flip indexes as returned be getImageRotation()
	 * @return object|false
	 */
	function flipRotateImage($im, $rotate) {
		if ($rotate['flip']) {
			$im = $this->flipImage($im, $rotate['flip']);
		}
		if ($rotate['rotate']) {
			$im = $this->rotateImage($im, $rotate['rotate']);
		}
		return $im;
	}
	
	/**
	 * Returns  the counter clockwise rotation degree the GD library requires
	 * 
	 * @since 1.6.1
	 * 
	 * Adapted from anonymous comment on https://www.php.net/manual/en/imagick.rotateimage
	 * @param int $degree Rotation degree clockwise
	 * @return int
	 */
	static function getCounterClockwiseRotation($degree) {
		if ($degree == 0 || $degree == 180) {
			return $degree;
		}
		if ($degree < 0 || $degree > 360) {
			$degree = 90;
		}
		return intval(360 - $degree);
	}

	function imageDims($filename) {
		$imageinfo = NULL;
		$rslt = getimagesize($filename, $imageinfo);
		if (is_array($rslt)) {
			return array('width' => $rslt[0], 'height' => $rslt[1]);
		} else {
			return false;
		}
	}

	function imageIPTC($filename) {
		$imageinfo = NULL;
		$rslt = getimagesize($filename, $imageinfo);
		if (is_array($rslt) && isset($imageinfo['APP13'])) {
			return $imageinfo['APP13'];
		} else {
			return false;
		}
	}

	function imageWidth($im) {
		return false;
	}

	function imageHeight($im) {
		return false;
	}

	function imageMerge($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {
		return false;
	}

	function imageGray($image) {
		
	}

	function imageKill($im) {
		return false;
	}

	function colorAllocate($image, $red, $green, $blue) {
		return false;
	}

	function writeString($image, $font, $x, $y, $string, $color) {
		
	}

	function drawRectangle($image, $x1, $y1, $x2, $y2, $color) {
		return false;
	}

	function graphicsLibInfo() {
		return $this->info;
	}

	function getFonts() {
		return array();
	}

	function imageLoadFont($font = NULL, $size = 18) {
		return false;
	}

	function imageFontWidth($font) {
		return false;
	}

	function imageFontHeight($font) {
		return false;
	}

	function imageBlurGD($imgCanvas, $imgCanvas2, $radius, $w, $h) {
		
	}

	function imageFromString($string) {
		return false;
	}

}