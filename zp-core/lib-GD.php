<?php

/**
 * library for image handling using the GD library of functions
 * @package core
 */
// force UTF-8 Ø

$_zp_graphics_optionhandlers[] = new lib_GD_Options(); // register option handler
/**
 * Option class for lib-GD
 *
 */

class lib_GD_Options {

	function __construct() {
		setOptionDefault('GD_FreeType_Path', SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/gd_fonts');
	}

	/**
	 * Standard option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		if (defined('GD_FREETYPE') && GD_FREETYPE) {
			return array(gettext('GD TypeFace path') => array('key'	 => 'GD_FreeType_Path', 'type' => OPTION_TYPE_TEXTBOX,
											'desc' => gettext('Supply the full path to your TrueType fonts.')));
		} else {
			return array();
		}
	}

	function canLoadMsg() {
		if (extension_loaded('gd')) {
			return '';
		} else {
			return gettext('The <strong><em>GD</em></strong> extension is not available.');
		}
	}

}

if (!function_exists('zp_graphicsLibInfo')) {

	/**
	 * Zenphoto image manipulation functions using the PHP GD library
	 *
	 */
	if (extension_loaded('gd')) { // only define the functions if we have the proper versions
		$_lib_GD_info = array();
		$info = gd_info();
		$_lib_GD_info['Library'] = 'GD';
		$_lib_GD_info['Library_desc'] = sprintf(gettext('PHP GD library <em>%s</em>'), $info['GD Version']);
		$_lib_GD_info['FreeType'] = $info['FreeType Support'];
		define('GD_FREETYPE', (bool) $_lib_GD_info['FreeType']);
		unset($_lib_GD_info['FreeType']);
		define('GD_FREETYPE_SAMPLE', 'The quick brown fox jumps over the lazy dog');
		define('GD_FREETYPE_SAMPLE_CHARS', strlen('GD_FREETYPE_SAMPLE'));
		$_gd_freetype_fonts = array(0);

		$imgtypes = imagetypes();
		$_lib_GD_info['GIF'] = ($imgtypes & IMG_GIF) ? 'gif' : false;
		$_lib_GD_info['JPG'] = ($imgtypes & IMG_JPG) ? 'jpg' : false;
		$_lib_GD_info['JPEG'] = ($imgtypes & IMG_JPG) ? 'jpg' : false;
		$_lib_GD_info['PNG'] = ($imgtypes & IMG_PNG) ? 'png' : false;
		$_lib_GD_info['BMP'] = ($imgtypes & IMG_WBMP) ? 'jpg' : false;
		unset($imgtypes);
		unset($info);

		if (DEBUG_IMAGE)
			debugLog("Loading " . $_lib_GD_info['Library']);

		/**
		 * Takes an image filename and returns a GD Image using the correct function
		 * for the image's format (imagecreatefrom*). Supports JPEG, GIF, and PNG.
		 * @param string $imagefile the full path and filename of the image to load.
		 * @return image the loaded GD image object.
		 *
		 */
		function zp_imageGet($imgfile) {
			$ext = getSuffix($imgfile);
			switch ($ext) {
				case 'png':
					return imagecreatefrompng($imgfile);
				case 'wbmp':
					return imagecreatefromwbmp($imgfile);
				case 'jpeg':
				case 'jpg':
					return imagecreatefromjpeg($imgfile);
				case 'gif':
					return imagecreatefromgif($imgfile);
			}
			return false;
		}

		/**
		 * outputs an image resource as a given type
		 *
		 * @param resource $im
		 * @param string $type
		 * @param string $filename
		 * @param int $qual
		 */
		function zp_imageOutput($im, $type, $filename = NULL, $qual = 75) {
			$qual = max(min($qual, 100), 0);
			if (getOption('image_interlace')) {
				imageinterlace($im, true);
			}
			switch ($type) {
				case 'png':
					$qual = max(0, 9 - round($qual / 10));
					return imagepng($im, $filename, $qual);
				case 'wbmp':
					return imagewbmp($im, $filename);
				case 'jpeg':
				case 'jpg':
					return imagejpeg($im, $filename, $qual);
				case 'gif':
					return imagegif($im, $filename);
			}
			return false;
		}

		/**
		 * Creates a true color image
		 *
		 * @param int $w the width of the image
		 * @param int $h the height of the image
		 * @return image
		 */
		function zp_createImage($w, $h) {
			return imagecreatetruecolor($w, $h);
		}

		/**
		 * Fills an image area
		 *
		 * @param image $image
		 * @param int $x
		 * @param int $y
		 * @param color $color
		 * @return bool
		 */
		function zp_imageFill($image, $x, $y, $color) {
			return imagefill($image, $x, $y, $color);
		}

		/**
		 * Sets the transparency color
		 *
		 * @param image $image
		 * @param color $color
		 * @return bool
		 */
		function zp_imageColorTransparent($image, $color) {
			return imagecolortransparent($image, $color);
		}

		/**
		 * copies an image canvas
		 *
		 * @param image $imgCanvas source canvas
		 * @param image $img destination canvas
		 * @param int $dest_x destination x
		 * @param int $dest_y destination y
		 * @param int $src_x source x
		 * @param int $src_y source y
		 * @param int $w width
		 * @param int $h height
		 */
		function zp_copyCanvas($imgCanvas, $img, $dest_x, $dest_y, $src_x, $src_y, $w, $h) {
			return imageCopy($imgCanvas, $img, $dest_x, $dest_y, $src_x, $src_y, $w, $h);
		}

		/**
		 * resamples an image to a new copy
		 *
		 * @param resource $dst_image
		 * @param resource $src_image
		 * @param int $dst_x
		 * @param int $dst_y
		 * @param int $src_x
		 * @param int $src_y
		 * @param int $dst_w
		 * @param int $dst_h
		 * @param int $src_w
		 * @param int $src_h
		 * @return bool
		 */
		function zp_resampleImage($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
			return imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
		}

		/**
		 * Sharpens an image using an Unsharp Mask filter.
		 *
		 * Original description from the author:
		 *
		 * WARNING ! Due to a known bug in PHP 4.3.2 this script is not working well in this
		 * version. The sharpened images get too dark. The bug is fixed in version 4.3.3.
		 *
		 * From version 2 (July 17 2006) the script uses the imageconvolution function in
		 * PHP version >= 5.1, which improves the performance considerably.
		 *
		 * Unsharp masking is a traditional darkroom technique that has proven very
		 * suitable for digital imaging. The principle of unsharp masking is to create a
		 * blurred copy of the image and compare it to the underlying original. The
		 * difference in colour values between the two images is greatest for the pixels
		 * near sharp edges. When this difference is subtracted from the original image,
		 * the edges will be accentuated.
		 *
		 * The Amount parameter simply says how much of the effect you want. 100 is
		 * 'normal'. Radius is the radius of the blurring circle of the mask. 'Threshold'
		 * is the least difference in colour values that is allowed between the original
		 * and the mask. In practice this means that low-contrast areas of the picture are
		 * left unrendered whereas edges are treated normally. This is good for pictures of
		 * e.g. skin or blue skies.
		 *
		 * Any suggenstions for improvement of the algorithm, expecially regarding the
		 * speed and the roundoff errors in the Gaussian blur process, are welcome.
		 *
		 * Permission to license this code under the GPL was granted by the author on 2/12/2007.
		 *
		 * @param image $img the GD format image to sharpen. This is not a URL string, but
		 *   should be the result of a GD image function.
		 * @param int $amount the strength of the sharpening effect. Nominal values are between 0 and 100.
		 * @param int $radius the pixel radius of the sharpening mask. A smaller radius sharpens smaller
		 *   details, and a larger radius sharpens larger details.
		 * @param int $threshold the color difference threshold required for sharpening. A low threshold
		 *   sharpens all edges including faint ones, while a higher threshold only sharpens more distinct edges.
		 * @return image the input image with the specified sharpening applied.
		 */
		function zp_imageUnsharpMask($img, $amount, $radius, $threshold) {
			/*
			  Unsharp Mask for PHP - version 2.0
			  Unsharp mask algorithm by Torstein Hønsi 2003-06.
			  Please leave this notice.
			 */

			// $img is an image that is already created within php using
			// imgcreatetruecolor. No url! $img must be a truecolor image.
			// Attempt to calibrate the parameters to Photoshop:
			if ($amount > 500)
				$amount = 500;
			$amount = $amount * 0.016;
			if ($radius > 50)
				$radius = 50;
			$radius = $radius * 2;
			if ($threshold > 255)
				$threshold = 255;

			$radius = abs(round($radius)); // Only integers make sense.
			if ($radius == 0)
				return $img;
			$w = imagesx($img);
			$h = imagesy($img);
			$imgCanvas = imagecreatetruecolor($w, $h);
			$imgCanvas2 = imagecreatetruecolor($w, $h);
			imagecopy($imgCanvas, $img, 0, 0, 0, 0, $w, $h);
			imagecopy($imgCanvas2, $img, 0, 0, 0, 0, $w, $h);

			imageBlurGD($imgCanvas, $imgCanvas2, $radius, $w, $h);

			// Calculate the difference between the blurred pixels and the original
			// and set the pixels
			for ($x = 0; $x < $w; $x++) { // each row
				for ($y = 0; $y < $h; $y++) { // each pixel
					$rgbOrig = ImageColorAt($imgCanvas2, $x, $y);
					$rOrig = (($rgbOrig >> 16) & 0xFF);
					$gOrig = (($rgbOrig >> 8) & 0xFF);
					$bOrig = ($rgbOrig & 0xFF);

					$rgbBlur = ImageColorAt($imgCanvas, $x, $y);

					$rBlur = (($rgbBlur >> 16) & 0xFF);
					$gBlur = (($rgbBlur >> 8) & 0xFF);
					$bBlur = ($rgbBlur & 0xFF);

					// When the masked pixels differ less from the original
					// than the threshold specifies, they are set to their original value.
					$rNew = (abs($rOrig - $rBlur) >= $threshold) ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig)) : $rOrig;
					$gNew = (abs($gOrig - $gBlur) >= $threshold) ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig)) : $gOrig;
					$bNew = (abs($bOrig - $bBlur) >= $threshold) ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig)) : $bOrig;

					if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
						$pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
						ImageSetPixel($img, $x, $y, $pixCol);
					}
				}
			}
			return $img;
		}

		/**
		 * Resize a PNG file with transparency to given dimensions
		 * and still retain the alpha channel information
		 * Author:  Alex Le - http://www.alexle.net
		 *
		 *
		 * @param image $src
		 * @param int $w
		 * @param int $h
		 * @return image
		 */
		function zp_imageResizeAlpha(&$src, $w, $h) {
			/* create a new image with the new width and height */
			if ($temp = @imagecreatetruecolor($w, $h)) {

				/* making the new image transparent */
				$background = imagecolorallocate($temp, 0, 0, 0);
				imagecolortransparent($temp, $background); // make the new temp image all transparent
				imagealphablending($temp, false); // turn off the alpha blending to keep the alpha channel

				/* Resize the PNG file */
				/* use imagecopyresized to gain some performance but loose some quality */
				imagecopyresampled($temp, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
				/* use imagecopyresampled if you concern more about the quality */
				//imagecopyresampled($temp, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
			}
			return $temp;
		}

		/**
		 * Returns true if GD library is configued with image rotation suppord
		 *
		 * @return bool
		 */
		function zp_imageCanRotate() {
			return function_exists('imagerotate');
		}

		/**
		 * Rotates an image resource according to its Orientation
		 * NB: requires the imagarotate function to be configured
		 *
		 * @param resource $im
		 * @param int $rotate
		 * @return resource
		 */
		function zp_rotateImage($im, $rotate) {
			$newim_rot = imagerotate($im, $rotate, 0);
			imagedestroy($im);
			return $newim_rot;
		}

		/**
		 * Returns the image height and width
		 *
		 * @param string $filename
		 * @return array
		 */
		function zp_imageDims($filename) {
			$imageinfo = NULL;
			$rslt = getimagesize($filename, $imageinfo);
			if (is_array($rslt)) {
				return array('width'	 => $rslt[0], 'height' => $rslt[1]);
			} else {
				return false;
			}
		}

		/**
		 * Returns the IPTC data of an image
		 *
		 * @param string $filename
		 * @return string
		 */
		function zp_imageIPTC($filename) {
			$imageinfo = NULL;
			$rslt = getimagesize($filename, $imageinfo);
			if (is_array($rslt) && isset($imageinfo['APP13'])) {
				return $imageinfo['APP13'];
			} else {
				return false;
			}
		}

		/**
		 * Returns the width of an image resource
		 *
		 * @param resource $im
		 * @return int
		 */
		function zp_imageWidth($im) {
			return imagesx($im);
		}

		/**
		 * Returns the height of an image resource
		 *
		 * @param resource $im
		 * @return int
		 */
		function zp_imageHeight($im) {
			return imagesy($im);
		}

		/**
		 * Does a copy merge of two image resources
		 *
		 * @param resource $dst_im
		 * @param resource $src_im
		 * @param int $dst_x
		 * @param int $dst_y
		 * @param int $src_x
		 * @param int $src_y
		 * @param int $src_w
		 * @param int $src_h
		 * @param int $pct
		 * @return bool
		 */
		function zp_imageMerge($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {
			return imagecopymerge($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);
		}

		/**
		 * Creates a grayscale image
		 *
		 * @param resource $image
		 * @return resource
		 */
		function zp_imageGray($image) {
			$img_height = imagesy($image);
			$img_width = imagesx($image);
			for ($y = 0; $y < $img_height; $y++) {
				for ($x = 0; $x < $img_width; $x++) {
					$gray = (ImageColorAt($image, $x, $y) >> 8) & 0xFF;
					imagesetpixel($image, $x, $y, ImageColorAllocate($image, $gray, $gray, $gray));
				}
			}
		}

		/**
		 * destroys an image resource
		 *
		 * @param resource $im
		 * @return bool
		 */
		function zp_imageKill($im) {
			return imagedestroy($im);
		}

		/**
		 * Returns an RGB color identifier
		 *
		 * @param resource $image
		 * @param int $red
		 * @param int $green
		 * @param int $blue
		 * @return int
		 */
		function zp_colorAllocate($image, $red, $green, $blue) {
			return imagecolorallocate($image, $red, $green, $blue);
		}

		/**
		 * Rencers a string into the image
		 *
		 * @param resource $image
		 * @param int $font
		 * @param int $x
		 * @param int $y
		 * @param string $string
		 * @param int $color
		 * @return bool
		 */
		function zp_writeString($image, $font, $x, $y, $string, $color) {
			global $_gd_freetype_fonts;
			if ($font > 0) {
				return imagestring($image, $font, $x, $y, $string, $color);
			} else {
				$font = abs($font);
				$fontfile = $_gd_freetype_fonts[$font]['path'];
				$size = $_gd_freetype_fonts[abs($font)]['size'];
				$bbox = imagettfbbox($_gd_freetype_fonts[$font]['size'], 0, $_gd_freetype_fonts[$font]['path'], GD_FREETYPE_SAMPLE);
				$w = (int) (($bbox[2] - $bbox[0]) / GD_FREETYPE_SAMPLE_CHARS);
				$h = $bbox[1] - $bbox[7];
				$rslt = imagettftext($image, $size, 0, $x + $w, $y + $h, $color, $fontfile, $string);
				return is_array($rslt);
			}
		}

		/**
		 * Creates a rectangle
		 *
		 * @param resource $image
		 * @param int $x1
		 * @param int $y1
		 * @param int $x2
		 * @param int $y2
		 * @param int $color
		 * @return bool
		 */
		function zp_drawRectangle($image, $x1, $y1, $x2, $y2, $color) {
			return imagerectangle($image, $x1, $y1, $x2, $y2, $color);
		}

		/**
		 * Returns array of graphics library info
		 *
		 * @return array
		 */
		function zp_graphicsLibInfo() {
			global $_lib_GD_info;
			return $_lib_GD_info;
		}

		/**
		 * Returns a list of available fonts
		 *
		 * @return array
		 */
		function zp_getFonts() {
			global $_gd_fontlist;
			if (!is_array($_gd_fontlist)) {
				$_gd_fontlist = array('system' => '');
				$curdir = getcwd();
				$basefile = SERVERPATH . '/' . USER_PLUGIN_FOLDER . 'gd_fonts/';
				if (is_dir($basefile)) {
					chdir($basefile);
					$filelist = safe_glob('*.gdf');
					foreach ($filelist as $file) {
						$key = filesystemToInternal(str_replace('.gdf', '', $file));
						$_gd_fontlist[$key] = $basefile . '/' . $file;
					}
				}
				chdir($basefile = SERVERPATH . '/' . ZENFOLDER . '/gd_fonts');
				$filelist = safe_glob('*.gdf');
				foreach ($filelist as $file) {
					$key = filesystemToInternal(preg_replace('/\.gdf/i', '', $file));
					$_gd_fontlist[$key] = $basefile . '/' . $file;
				}
				if (GD_FREETYPE) {
					$basefile = rtrim(getOption('GD_FreeType_Path') . '/');
					if (is_dir($basefile)) {
						chdir($basefile);
						$filelist = safe_glob('*.ttf');
						foreach ($filelist as $file) {
							$key = filesystemToInternal($file);
							$_gd_fontlist[$key] = $basefile . '/' . $file;
						}
					}
				}
				chdir($curdir);
			}
			return $_gd_fontlist;
		}

		/**
		 * Loads a font and returns its font id
		 *
		 * @param string $font
		 * @return int
		 */
		function zp_imageLoadFont($font = NULL, $size = 18) {
			global $_gd_freetype_fonts;
			if (!empty($font)) {
				if (file_exists($font)) {
					switch (getSuffix($font)) {
						case 'gdf':
							return imageloadfont($font);
						case 'ttf':
							$index = -count($_gd_freetype_fonts);
							array_push($_gd_freetype_fonts, array('path' => $font, 'size' => $size));
							return $index;
					}
				}
			}
			return 5; // default to the largest inbuilt font
		}

		/**
		 * Returns the font width in pixels
		 *
		 * @param int $font
		 * @return int
		 */
		function zp_imageFontWidth($font) {
			global $_gd_freetype_fonts;
			if ($font > 0) {
				return imagefontwidth($font);
			} else {
				$font = abs($font);
				$bbox = imagettfbbox($_gd_freetype_fonts[$font]['size'], 0, $_gd_freetype_fonts[$font]['path'], GD_FREETYPE_SAMPLE);
				$w = (int) (($bbox[2] - $bbox[0]) / GD_FREETYPE_SAMPLE_CHARS);
				return $w;
			}
		}

		/**
		 * Returns the font height in pixels
		 *
		 * @param int $font
		 * @return int
		 */
		function zp_imageFontHeight($font) {
			global $_gd_freetype_fonts;
			if ($font > 0) {
				return imagefontheight($font);
			} else {
				$font = abs($font);
				$bbox = imagettfbbox($_gd_freetype_fonts[$font]['size'], 0, $_gd_freetype_fonts[$font]['path'], GD_FREETYPE_SAMPLE);
				$h = $bbox[1] - $bbox[7];
				return $h;
			}
		}

		/**
		 * provides image blur support for lib-GD:zp_imageUnsharpMask
		 *
		 * @param image $imgCanvas
		 * @param int $radius
		 * @param int $w
		 * @param int $h
		 */
		function imageBlurGD($imgCanvas, $imgCanvas2, $radius, $w, $h) {
			// Gaussian blur matrix:
			//    1    2    1
			//    2    4    2
			//    1    2    1
			//////////////////////////////////////////////////
			for ($i = 0; $i < $radius; $i++) {
				if (function_exists('imageconvolution')) { // PHP >= 5.1
					$matrix = array(
									array(1, 2, 1),
									array(2, 4, 2),
									array(1, 2, 1)
					);
					imageconvolution($imgCanvas, $matrix, 16, 0);
				}
			}
		}

		/**
		 *
		 * creates an image from an image stream
		 * @param $string
		 */
		function zp_imageFromString($string) {
			return imagecreatefromstring($string);
		}

	}
}
?>