<?php

/**
 * Library for image handling using the Imagick library of functions
 *
 * Requires Imagick 3.0.0+ and ImageMagick 6.3.8+
 *
 * @package core
 */
// force UTF-8 Ø

define('IMAGICK_REQUIRED_VERSION', '3.0.0');
define('IMAGEMAGICK_REQUIRED_VERSION', '6.3.8');

$_imagick_version = phpversion('imagick');
$_imagick_version_pass = version_compare($_imagick_version, IMAGICK_REQUIRED_VERSION, '>=');

$_imagemagick_version = '';
$_imagemagick_version_pass = false;

$_zp_imagick_present = extension_loaded('imagick') && $_imagick_version_pass;

if ($_zp_imagick_present) {
	@$_imagemagick_version = Imagick::getVersion();
	preg_match('/\d+(\.\d+)*/', $_imagemagick_version['versionString'], $matches);

	$_imagemagick_version['versionNumber'] = $matches[0];
	$_imagemagick_version_pass = version_compare($_imagemagick_version['versionNumber'], IMAGEMAGICK_REQUIRED_VERSION, '>=');

	$_zp_imagick_present &= $_imagick_version_pass;
	unset($matches);
}

$_zp_graphics_optionhandlers += array('lib_Imagick_Options' => new lib_Imagick_Options());

/**
 * Option class for lib-Imagick
 */
class lib_Imagick_Options {

	function __construct() {

	}

	/**
	 * Standard option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		global $_zp_imagick_present, $_zp_graphics_optionhandlers;

		$disabled = $this->canLoadMsg();

		$imagickOptions = array(
						gettext('Enable Imagick') => array(
										'key'			 => 'use_imagick',
										'type'		 => OPTION_TYPE_CHECKBOX,
										'order'		 => 0,
										'disabled' => $disabled,
										'desc'		 => ($disabled) ? '<p class="notebox">' . $disabled . '</p>' : gettext('Your PHP has support for Imagick. Check this option if you wish to use the Imagick graphics library.')
						)
		);

		return $imagickOptions;
	}

	function canLoadMsg() {
		global $_imagick_version_pass, $_imagemagick_version_pass;

		if (extension_loaded('imagick')) {
			if (!$_imagick_version_pass) {
				return sprintf(gettext('The <strong><em>Imagick</em></strong> library version must be <strong>%s</strong> or later.'), IMAGICK_REQUIRED_VERSION);
			}

			if (!$_imagemagick_version_pass) {
				return sprintf(gettext('The <strong><em>ImageMagick</em></strong> binary version must be <strong>%s</strong> or later.'), IMAGEMAGICK_REQUIRED_VERSION);
			}
		} else {
			return gettext('The <strong><em>Imagick</em></strong> extension is not available.');
		}

		return '';
	}

}

/**
 * Zenphoto image manipulation functions using the Imagick library
 */
if ($_zp_imagick_present && (getOption('use_imagick') || !extension_loaded('gd'))) {
	$_lib_Imagick_info = array();
	$_lib_Imagick_info['Library'] = 'Imagick';
	$_lib_Imagick_info['Library_desc'] = sprintf(gettext('PHP Imagick library <em>%s</em>') . '<br /><em>%s</em>', $_imagick_version, $_imagemagick_version['versionString']);

	$_imagick_format_whitelist = array(
					'BMP'		 => 'jpg', 'BMP2'	 => 'jpg', 'BMP3'	 => 'jpg',
					'GIF'		 => 'gif', 'GIF87'	 => 'gif',
					'JPG'		 => 'jpg', 'JPEG'	 => 'jpg',
					'PNG'		 => 'png', 'PNG8'	 => 'png', 'PNG24'	 => 'png', 'PNG32'	 => 'png',
					'TIFF'	 => 'jpg', 'TIFF64' => 'jpg'
	);

	$_imagick = new Imagick();
	$_imagick_formats = $_imagick->queryFormats();
	foreach ($_imagick_formats as $format) {
		if (array_key_exists($format, $_imagick_format_whitelist)) {
			$_lib_Imagick_info[$format] = $_imagick_format_whitelist[$format];
		}
	}
	unset($_imagick_format_whitelist);
	unset($_imagick_formats);
	unset($_imagick);
	unset($format);

	if (DEBUG_IMAGE) {
		debugLog('Loading ' . $_lib_Imagick_info['Library']);
	}

	/**
	 * Takes an image filename and returns an Imagick image object
	 *
	 * @param string $imgfile the full path and filename of the image to load
	 * @return Imagick
	 */
	function zp_imageGet($imgfile) {
		global $_lib_Imagick_info;

		if (in_array(getSuffix($imgfile), $_lib_Imagick_info)) {
			$image = new Imagick(filesystemToInternal($imgfile));

			return $image;
		}

		return false;
	}

	/**
	 * Outputs an image resource as a given type
	 *
	 * @param Imagick $im
	 * @param string $type
	 * @param string $filename
	 * @param int $qual
	 * @return bool
	 */
	function zp_imageOutput($im, $type, $filename = NULL, $qual = 75) {
		$interlace = getOption('image_interlace');
		$qual = max(min($qual, 100), 0);

		$im->setImageFormat($type);

		switch ($type) {
			case 'gif':
				$im->setCompression(Imagick::COMPRESSION_LZW);
				$im->setCompressionQuality($qual);

				if ($interlace) {
					$im->setInterlaceScheme(Imagick::INTERLACE_GIF);
				}

				break;

			case 'jpeg':
			case 'jpg':
				$im->setCompression(Imagick::COMPRESSION_JPEG);
				$im->setCompressionQuality($qual);

				if ($interlace) {
					$im->setInterlaceScheme(Imagick::INTERLACE_JPEG);
				}

				break;

			case 'png':
				$im->setCompression(Imagick::COMPRESSION_ZIP);
				$im->setCompressionQuality($qual);

				if ($interlace) {
					$im->setInterlaceScheme(Imagick::INTERLACE_PNG);
				}

				break;
		}

		$im->optimizeImageLayers();

		if ($filename == NULL) {
			header('Content-Type: image/' . $type);

			return print $im->getImagesBlob();
		}

		return $im->writeImages(filesystemToInternal($filename), true);
	}

	/**
	 * Creates a true color image
	 *
	 * @param int $w the width of the image
	 * @param int $h the height of the image
	 * @return Imagick
	 */
	function zp_createImage($w, $h) {
		$im = new Imagick();
		$im->newImage($w, $h, 'none');
		$im->setImageType(Imagick::IMGTYPE_TRUECOLORMATTE);

		return $im;
	}

	/**
	 * Fills an image area
	 *
	 * @param Imagick $image
	 * @param int $x
	 * @param int $y
	 * @param color $color
	 * @return bool
	 */
	function zp_imageFill($image, $x, $y, $color) {
		$target = $image->getImagePixelColor($x, $y);

		return $image->floodFillPaintImage($color, 1, $target, $x, $y, false);
	}

	/**
	 * Sets the transparency color
	 *
	 * @param Imagick $image
	 * @param color $color
	 * @return bool
	 */
	function zp_imageColorTransparent($image, $color) {
		return $image->transparentPaintImage($color, 0.0, 1, false);
	}

	/**
	 * Copies an image canvas
	 *
	 * @param Imagick $imgCanvas destination canvas
	 * @param Imagick $img source canvas
	 * @param int $dest_x destination x
	 * @param int $dest_y destination y
	 * @param int $src_x source x
	 * @param int $src_y source y
	 * @param int $w width
	 * @param int $h height
	 * @return bool
	 */
	function zp_copyCanvas($imgCanvas, $img, $dest_x, $dest_y, $src_x, $src_y, $w, $h) {
		$img->cropImage($w, $h, $src_x, $src_y);

		$result = true;

		$imgCanvas = $imgCanvas->coalesceImages();

		foreach ($imgCanvas as $frame) {
			$result &= $imgCanvas->compositeImage($img, Imagick::COMPOSITE_OVER, $dest_x, $dest_y);
		}

		return $result;
	}

	/**
	 * Resamples an image to a new copy
	 *
	 * @param Imagick $dst_image
	 * @param Imagick $src_image
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
		foreach ($src_image->getImageProfiles() as $name => $profile) {
			$dst_image->profileImage($name, $profile);
		}

		$result = true;

		$src_image = $src_image->coalesceImages();

		foreach ($src_image as $frame) {
			$frame->cropImage($src_w, $src_h, $src_x, $src_y);
			$frame->setImagePage(0, 0, 0, 0);
		}

		$src_image = $src_image->coalesceImages();

		foreach ($src_image as $frame) {
			$frame->resizeImage($dst_w, $dst_h, Imagick::FILTER_LANCZOS, 1);

			$dst_image->setImageDelay($frame->getImageDelay());
			$result &= $dst_image->compositeImage($frame, Imagick::COMPOSITE_OVER, $dst_x, $dst_y);

			if ($dst_image->getNumberImages() < $src_image->getNumberImages()) {
				$result &= $dst_image->addImage(zp_createImage($dst_image->getImageWidth(), $dst_image->getImageHeight()));
			}

			if (!$result) {
				break;
			}
		}

		return $result;
	}

	/**
	 * Sharpens an image using an Unsharp Mask filter.
	 *
	 * @param Imagick $img the image to sharpen
	 * @param int $amount the strength of the sharpening effect
	 * @param int $radius the pixel radius of the sharpening mask
	 * @param int $threshold the color difference threshold required for sharpening
	 * @return Imagick
	 */
	function zp_imageUnsharpMask($img, $amount, $radius, $threshold) {
		$img->unsharpMaskImage($radius, 0.1, $amount, $threshold);

		return $img;
	}

	/**
	 * Resize a file with transparency to given dimensions and still retain the alpha channel information
	 *
	 * @param Imagick $src
	 * @param int $w
	 * @param int $h
	 * @return Imagick
	 */
	function zp_imageResizeAlpha($src, $w, $h) {
		$src->scaleImage($w, $h);

		return $src;
	}

	/**
	 * Returns true if Imagick library is configured with image rotation support
	 *
	 * @return bool
	 */
	function zp_imageCanRotate() {
		return true;
	}

	/**
	 * Rotates an image resource according to its Orientation setting
	 *
	 * @param Imagick $im
	 * @param int $rotate
	 * @return Imagick
	 */
	function zp_rotateImage($im, $rotate) {
		$im->rotateImage('none', 360 - $rotate); // GD rotates CCW, Imagick rotates CW

		return $im;
	}

	/**
	 * Returns the image height and width
	 *
	 * @param string $filename
	 * @param array $imageinfo
	 * @return array
	 */
	function zp_imageDims($filename) {
		$ping = new Imagick();

		if ($ping->pingImage(filesystemToInternal($filename))) {
			return array('width'	 => $ping->getImageWidth(), 'height' => $ping->getImageHeight());
		}

		return false;
	}

	/**
	 * Returns the IPTC data of an image
	 *
	 * @param string $filename
	 * @return string
	 */
	function zp_imageIPTC($filename) {
		$ping = new Imagick();

		if ($ping->pingImage(filesystemToInternal($filename))) {
			try {
				return $ping->getImageProfile('exif');
			} catch (ImagickException $e) {
				// EXIF profile does not exist
			}
		}

		return false;
	}

	/**
	 * Returns the width of an image resource
	 *
	 * @param Imagick $im
	 * @return int
	 */
	function zp_imageWidth($im) {
		return $im->getImageWidth();
	}

	/**
	 * Returns the height of an image resource
	 *
	 * @param Imagick $im
	 * @return int
	 */
	function zp_imageHeight($im) {
		return $im->getImageHeight();
	}

	/**
	 * Does a copy merge of two image resources
	 *
	 * @param Imagick $dst_im
	 * @param Imagick $src_im
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
		$src_im->cropImage($src_w, $src_h, $src_x, $src_y);
		$src_im->setImageOpacity($pct / 100);

		return $dst_im->compositeImage($src_im, Imagick::COMPOSITE_OVER, $dst_x, $dst_y);
	}

	/**
	 * Creates a grayscale image
	 *
	 * @param Imagick $image The image to grayscale
	 * @return Imagick
	 */
	function zp_imageGray($image) {
		$image->setType(Imagick::IMGTYPE_GRAYSCALE);
		$image->setImageColorspace(Imagick::COLORSPACE_GRAY);
		$image->setImageProperty('exif:ColorSpace', Imagick::IMGTYPE_GRAYSCALE);

		return $image;
	}

	/**
	 * Destroys an image resource
	 *
	 * @param Imagick $im
	 * @return bool
	 */
	function zp_imageKill($im) {
		return $im->destroy();
	}

	/**
	 * Returns an RGB color identifier
	 *
	 * @param Imagick $image
	 * @param int $red
	 * @param int $green
	 * @param int $blue
	 * @return ImagickPixel
	 */
	function zp_colorAllocate($image, $red, $green, $blue) {
		return new ImagickPixel("rgb($red, $green, $blue)");
	}

	/**
	 * Renders a string into the image
	 *
	 * @param Imagick $image
	 * @param ImagickDraw $font
	 * @param int $x
	 * @param int $y
	 * @param string $string
	 * @param ImagickPixel $color
	 * @return bool
	 */
	function zp_writeString($image, $font, $x, $y, $string, $color) {
		$font->setStrokeColor($color);

		return $image->annotateImage($font, $x, $y + $image->getImageHeight() / 2, 0, $string);
	}

	/**
	 * Creates a rectangle
	 *
	 * @param Imagick $image
	 * @param int $x1
	 * @param int $y1
	 * @param int $x2
	 * @param int $y2
	 * @param ImagickPixel $color
	 * @return bool
	 */
	function zp_drawRectangle($image, $x1, $y1, $x2, $y2, $color) {
		return $image->borderImage($color, 1, 1);
	}

	/**
	 * Returns array of graphics library info
	 *
	 * @return array
	 */
	function zp_graphicsLibInfo() {
		global $_lib_Imagick_info;

		return $_lib_Imagick_info;
	}

	/**
	 * Returns a list of available fonts
	 *
	 * @return array
	 */
	function zp_getFonts() {
		global $_imagick_fontlist;

		if (!is_array($_imagick_fontlist)) {
			@$_imagick_fontlist = Imagick::queryFonts();
			$_imagick_fontlist = array('system' => '') + array_combine($_imagick_fontlist, $_imagick_fontlist);

			$basefile = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/imagick_fonts/';

			if (is_dir($basefile)) {
				chdir($basefile);
				$filelist = safe_glob('*.ttf');

				foreach ($filelist as $file) {
					$key = filesystemToInternal(str_replace('.ttf', '', $file));
					$_imagick_fontlist[$key] = getcwd() . '/' . $file;
				}
			}

			chdir(dirname(__FILE__));
		}

		return $_imagick_fontlist;
	}

	/**
	 * Loads a font and returns an object with the font loaded
	 *
	 * @param string $font
	 * @return ImagickDraw
	 */
	function zp_imageLoadFont($font = NULL, $size = 18) {
		$draw = new ImagickDraw();

		if (!empty($font)) {
			$draw->setFont($font);
		}

		$draw->setFontSize($size);

		return $draw;
	}

	/**
	 * Returns the font width in pixels
	 *
	 * @param ImagickDraw $font
	 * @return int
	 */
	function zp_imageFontWidth($font) {
		$temp = new Imagick();
		$metrics = $temp->queryFontMetrics($font, "The quick brown fox jumps over the lazy dog");
		$temp->destroy();

		return $metrics['characterWidth'];
	}

	/**
	 * Returns the font height in pixels
	 *
	 * @param ImagickDraw $font
	 * @return int
	 */
	function zp_imageFontHeight($font) {
		$temp = new Imagick();
		$metrics = $temp->queryFontMetrics($font, "The quick brown fox jumps over the lazy dog");
		$temp->destroy();

		return $metrics['characterHeight'];
	}

	/**
	 * Creates an image from an image stream
	 *
	 * @param $string
	 * @return Imagick
	 */
	function zp_imageFromString($string) {
		$im = new Imagick();
		$im->readImageBlob($string);

		return $im;
	}

}
?>
