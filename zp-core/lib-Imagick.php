<?php
/**
 * Library for image handling using the Imagick library of functions
 *
 * @internal Imagick::setResourceLimit() causes a PHP crash if called statically (fixed in Imagick 3.0.0RC1).
 *		Imagick::getResourceLimit(), Imagick::getVersion(), Imagick::queryFonts(), and Imagick::queryFormats()
 *		might also cause PHP to crash as well, though they should be able to be called statically.
 *
 * @package core
 */

// force UTF-8 Ø

/**
 * Requires Imagick 2.1.0+ (Imagick 2.0.0+ requires PHP5)
 * Imagick 2.3.0b1+ and ImageMagick 6.3.8+ suggested to avoid deprecated functions
 */
define('IMAGICK_LOADED', extension_loaded('imagick'));

$_imagick_version = phpversion('imagick'); // Imagick::IMAGICK_EXTVER
$_imagick_required_version = '2.1.0';
$_imagick_version_pass = version_compare($_imagick_version, $_imagick_required_version, '>=');

$_zp_imagick_present = IMAGICK_LOADED && $_imagick_version_pass;

$_zp_graphics_optionhandlers += array('lib_Imagick_Options' => new lib_Imagick_Options());

/**
 * Option class for lib-Imagick
 */
class lib_Imagick_Options {

	function __construct() {
		global $_zp_imagick_present;

		if ($_zp_imagick_present) {
			$this->defaultFilter = 'FILTER_LANCZOS';
			$this->defaultFontSize = 18;

			// setOptionDefault('use_imagick', $_zp_imagick_present);
			setOptionDefault('imagick_filter', $this->defaultFilter);
			setOptionDefault('magick_font_size', $this->defaultFontSize);

			if (!sanitize_numeric(getOption('magick_font_size'))) {
				setOption('magick_font_size', $this->defaultFontSize);
			}

			$mem_lim = getOption('magick_mem_lim');
			if (!is_numeric($mem_lim) || $mem_lim < 0 ) {
				setOption('magick_mem_lim', 0);
			}
		}
	}

	/**
	 * Standard option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		global $_zp_imagick_present;

		$imagickOptions = array();

		if ($_zp_imagick_present) {
			$imagickOptions += array(
				gettext('Enable Imagick') => array(
					'key' => 'use_imagick',
					'type' => OPTION_TYPE_CHECKBOX,
					'order' => 0,
					'desc' => gettext('Your PHP has support for Imagick. Check this option if you wish to use the Imagick graphics library.')
				),
				gettext('Imagick filter') => array(
					'key' => 'imagick_filter',
					'type' => OPTION_TYPE_SELECTOR,
					'selections' => getMagickConstants('Imagick', 'FILTER_'),
					'order' => 2,
					'desc' => '<p>' . sprintf(gettext('The type of filter used when resampling an image. The default is <strong>%s</strong>.'), $this->defaultFilter) . '</p>'
				)
			);

			if (!isset($_zp_graphics_optionhandlers['lib_Gmagick_Options'])) {
				$imagickOptions += array(
					gettext('Magick memory limit') => array(
						'key' => 'magick_mem_lim',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => '<p>' . gettext('Amount of memory allocated to Gmagick/Imagick in megabytes. Set to <strong>0</strong> for unlimited memory.') . '</p><p class="notebox">' . gettext('<strong>Note:</strong> Image processing will be faster with a higher memory limit. However, if your server experiences problems with image processing, try setting this lower.') . '</p>'
					),
					gettext('CAPTCHA font size') => array(
						'key' => 'magick_font_size',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 3,
						'desc' => sprintf(gettext('The font size (in pixels) for CAPTCHAs. Default is <strong>%s</strong>.'), $this->defaultFontSize)
					)
				);
			}
		}

		return $imagickOptions;
	}

	function canLoadMsg() {
		global $_imagick_version_pass, $_imagick_required_version;

		if (IMAGICK_LOADED) {
			if (!$_imagick_version_pass) {
				return sprintf(gettext('The <strong><em>Imagick</em></strong> library version must be <strong>%s</strong> or later.'), $_imagick_required_version);
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
	$_imagick_temp = new Imagick();

	$_magick_mem_lim = getOption('magick_mem_lim');
	if ($_magick_mem_lim > 0) {
		$_imagick_temp->setResourceLimit(Imagick::RESOURCETYPE_MEMORY, $_magick_mem_lim);
	}

	$_imagemagick_version = $_imagick_temp->getVersion();

	$_lib_Imagick_info = array();
	$_lib_Imagick_info['Library'] = 'Imagick';
	$_lib_Imagick_info['Library_desc'] = sprintf(gettext('PHP Imagick library <em>%s</em>') . '<br /><em>%s</em>', $_imagick_version, $_imagemagick_version['versionString']);

	$_use_imagick_deprecated = version_compare($_imagick_version, '2.3.0b1', '<') && version_compare($_imagemagick_version['versionString'], '6.3.8', '<');

	$_imagick_format_blacklist = array(
		// video formats
		'AVI', 'M2V', 'M4V', 'MOV', 'MP4', 'MPEG', 'MPG', 'WMV',
		// text formats
		'HTM', 'HTML', 'MAN', 'PDF', 'SHTML', 'TEXT', 'TXT', 'UBRL',
		// font formats
		'DFONT', 'OTF', 'PFA', 'PFB', 'TTC', 'TTF',
		// GhostScript formats; 'MAN' and 'PDF' also require this
		'EPI', 'EPS', 'EPS2', 'EPS3', 'EPSF', 'EPSI', 'EPT', 'EPT2', 'EPT3', 'PS', 'PS2', 'PS3',
		// other formats with lib dependencies, so possibly no decode delegate
		'CGM', 'EMF', 'FIG', 'FPX', 'GPLT', 'HPGL', 'JBIG', 'RAD', 'WMF', 'WMZ',
		// just to be sure...
		'ZIP'
	);

	$_imagick_formats = array_diff($_imagick_temp->queryFormats(), $_imagick_format_blacklist);
	$_lib_Imagick_info += array_combine(array_map('strtoupper', $_imagick_formats), array_map('strtolower', $_imagick_formats));

	define('IMAGICK_RETAIN_PROFILES',version_compare($_imagemagick_version['versionNumber'], '6.3.6', '>='));


	$_imagick_temp->destroy();
	unset($_magick_mem_lim);
	unset($_imagick_format_blacklist);
	unset($_imagick_formats);

	if (DEBUG_IMAGE) {
		debugLog('Loading ' . $_lib_Imagick_info['Library']);
	}

	/**
	 * Takes an image filename and returns an Imagick image object
	 *
	 * @param string $imgfile the full path and filename of the image to load
	 * @throws ImagickException
	 * @return Imagick
	 */
	function zp_imageGet($imgfile) {
		global $_lib_Imagick_info;

		$ext = getSuffix($imgfile);
		if (in_array($ext, $_lib_Imagick_info)) {
			$image = new Imagick($imgfile);

			if (IMAGE_WATERMARK | FULLIMAGE_WATERMARK | THUMB_WATERMARK) {
				try {
					$image = $image->coalesceImages();
				} catch (ImagickException $e) {
					if (DEBUG_IMAGE) {
						debugLog('Caught ImagickException in zp_copyCanvas(): ' . $e->getMessage());
					}
				}
			}

			return $image;
		}

		return false;
	}

	/**
	 * Outputs an image resource as a given type
	 *
	 * @internal Imagick::INTERLACE_[GIF|JPEG|PNG] require Imagick compiled against ImageMagick 6.3.4+
	 *
	 * @param Imagick $im
	 * @param string $type
	 * @param string $filename
	 * @param int $qual
	 * @throws ImagickException
	 * @return bool
	 */
	function zp_imageOutput($im, $type, $filename = NULL, $qual = 75) {
		global $_imagemagick_version, $_imagick_newer_interlace;

		if (!isset($_imagick_newer_interlace)) {
			$_imagick_newer_interlace = version_compare($_imagemagick_version['versionNumber'], '6.3.4', '>=');
		}

		$interlace = getOption('image_interlace');
		$qual = max(min($qual, 100), 0);

		$im->setImageFormat($type);

		switch ($type) {
			case 'gif':
				$im->setCompression(Imagick::COMPRESSION_LZW);
				$im->setCompressionQuality($qual);

				if ($interlace) {
					if ($_imagick_newer_interlace) {
						$im->setInterlaceScheme(Imagick::INTERLACE_GIF);
					} else {
						$im->setInterlaceScheme(Imagick::INTERLACE_LINE);
					}
				}

				break;

			case 'jpeg':
			case 'jpg':
				$im->setCompression(Imagick::COMPRESSION_JPEG);
				$im->setCompressionQuality($qual);

				if ($interlace) {
					if ($_imagick_newer_interlace) {
						$im->setInterlaceScheme(Imagick::INTERLACE_JPEG);
					} else {
						$im->setInterlaceScheme(Imagick::INTERLACE_LINE);
					}
				}

				break;

			case 'png':
				$im->setCompression(Imagick::COMPRESSION_ZIP);
				$im->setCompressionQuality($qual);

				if ($interlace) {
					if ($_imagick_newer_interlace) {
						$im->setInterlaceScheme(Imagick::INTERLACE_PNG);
					} else {
						$im->setInterlaceScheme(Imagick::INTERLACE_LINE);
					}
				}

				break;
		}

		try {
			$im->optimizeImageLayers();
		} catch (ImagickException $e) {
			if (DEBUG_IMAGE) {
				debugLog('Caught ImagickException in zp_imageOutput(): ' . $e->getMessage());
			}
		}

		if ($filename == NULL) {
			header('Content-Type: image/' . $type);
			return print $im->getImagesBlob();
		}

		return $im->writeImages($filename, true);
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
	 * @internal Imagick::floodFillPaintImage() requires Imagick 2.3.0b1+ compiled against ImageMagick 6.3.8+
	 *
	 * @param Imagick $image
	 * @param int $x
	 * @param int $y
	 * @param color $color
	 * @return bool
	 */
	function zp_imageFill($image, $x, $y, $color) {
		global $_use_imagick_deprecated;

		if ($_use_imagick_deprecated) {
			return $image->paintFloodfillImage($color, 1, $color, $x, $y);
		}

		$target = $image->getImagePixelColor($x, $y);
		return $image->floodFillPaintImage($color, 1, $target, $x, $y, false);
	}

	/**
	 * Sets the transparency color
	 *
	 * @internal Imagick::transparentPaintImage() requires Imagick 2.3.0b1+ compiled against ImageMagick 6.3.8+
	 *
	 * @param Imagick $image
	 * @param color $color
	 * @return bool
	 */
	function zp_imageColorTransparent($image, $color)  {
		global $_use_imagick_deprecated;

		if ($_use_imagick_deprecated) {
			return $image->paintTransparentImage($color, 0.0, 1);
		}

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

		for ($i = 0; $result && $i <= $imgCanvas->getNumberImages(); $i++) {
			$result = $imgCanvas->compositeImage($img, Imagick::COMPOSITE_OVER, $dest_x, $dest_y);
			$imgCanvas->previousImage();
		}

		return $result;
	}

	/**
	 * Resamples an image to a new copy
	 *
	 * @internal Imagick::getImageProfiles() requires Imagick compiled against ImageMagick 6.3.6+
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
		global $_imagemagick_version;

		if (IMAGICK_RETAIN_PROFILES) {
			foreach($src_image->getImageProfiles() as $name => $profile) {
				$dst_image->profileImage($name, $profile);
			}
		}

		$src_image->cropImage($src_w, $src_h, $src_x, $src_y);
		$src_image->resizeImage($dst_w, $dst_h, constant('Imagick::' . getOption('imagick_filter')), 1);
		return $dst_image->compositeImage($src_image, Imagick::COMPOSITE_OVER, $dst_x, $dst_y);
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
	 * Returns true if Imagick library is configued with image rotation support
	 *
	 * @return bool
	 */
	function zp_imageCanRotate() {
		global $_imagick_can_rotate;

		if (!isset($_imagick_can_rotate)) {
			$imagickReflection = new ReflectionClass('Imagick');
			$_imagick_can_rotate = $imagickReflection->hasMethod('rotateImage');
		}

		return $_imagick_can_rotate;
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

		if ($ping->pingImage($filename)) {
			return array('width' => $ping->getImageWidth(), 'height' => $ping->getImageHeight());
		}

		return false;
	}

	/**
	 * Returns the IPTC data of an image
	 *
	 * @param string $filename
	 * @throws ImagickException
	 * @return string
	 */
	function zp_imageIPTC($filename) {
		$ping = new Imagick();

		if ($ping->pingImage($filename)) {
			try {
				return $ping->getImageProfile('exif');
			} catch (ImagickException $e) {
				if (DEBUG_IMAGE) {
					debugLog('Caught ImagickException in zp_imageIPTC(): ' . $e->getMessage());
				}
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
	 * @internal Imagick::setImageOpacity() requires Imagick compiled against ImageMagick 6.3.1+
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
		global $_imagemagick_version, $_imagick_merge_grayscale;

		if (!isset($_imagick_merge_grayscale)) {
			$_imagick_merge_grayscale = version_compare($_imagemagick_version['versionNumber'], '6.3.1', '<');
		}

		$src_im->cropImage($w, $h, $src_x, $src_y);

		if ($_imagick_merge_grayscale) {
			zp_imageGray($src_im, $dst_im);
		} else {
			$src_im->setImageOpacity($pct / 100);
		}

		return $dst_im->compositeImage($src_im, Imagick::COMPOSITE_OVER, $dest_x, $dest_y);
	}

	/**
	 * Creates a grayscale image
	 *
	 * @internal Imagick::getImageProperty() requires Imagick compiled against ImageMagick 6.3.2+
	 * @internal Imagick::setImageProperty() requires Imagick compiled against ImageMagick 6.3.2+
	 *
	 * @param Imagick $image The image to grayscale
	 * @param Imagick $correct_image The image to profile correct
	 * @return Imagick
	 */
	function zp_imageGray($image, $correct_image = NULL) {
		global $_imagemagick_version, $_imagick_correct_colorspace;

		if (!isset($_imagick_correct_colorspace)) {
			$_imagick_correct_colorspace = version_compare($_imagemagick_version['versionNumber'], '6.3.2', '>=');
		}

		$image->setType(Imagick::IMGTYPE_GRAYSCALE);

		if (is_null($correct_image)) {
			$correct_image = $image;
		}

		// assumes that exif:ColorSpace is not set to an undefined colorspace
		if ($_imagick_correct_colorspace && $correct_image->getImageProperty('exif:ColorSpace')) {
			$correct_image->setImageProperty('exif:ColorSpace', Imagick::IMGTYPE_GRAYSCALE);
		}

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
			$temp = new Imagick();
			$_imagick_fontlist = $temp->queryFonts();
			$temp->destroy();

			$_imagick_fontlist = array('system' => '') + array_combine($_imagick_fontlist, $_imagick_fontlist);

			$basefile = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/imagick_fonts/';

			if (is_dir($basefile)) {
				chdir($basefile);
				$filelist = safe_glob('*.ttf');

				foreach($filelist as $file) {
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
	function zp_imageLoadFont($font = NULL) {
		$draw = new ImagickDraw();

		if (!empty($font)) {
			try {
				$draw->setFont($font);
			} catch(ImagickDrawException $e) {
				if (DEBUG_IMAGE) {
					debugLog('Caught ImagickDrawException in zp_imageLoadFont(): ' . $e->getMessage());
				}
			}
		}

		$draw->setFontSize(getOption('magick_font_size'));
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
	 *
	 * creates an image from an image stream
	 * @param $string
	 */
	function zp_imageFromString($string) {
		$im = new Imagick();
		$im->readImageBlob($string);
		return $im;
	}

}

?>
