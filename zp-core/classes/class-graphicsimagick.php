<?php
// force UTF-8 Ø
/**
 * Class for image handling using the Imagick library
 *
 * Requires Imagick 3.0.0+ and ImageMagick 6.3.8+
 * 
 * @since 1.6 - reworked as class
 * 
 * @package zpcore\classes\graphics
 */
class graphicsImagick extends graphicsBase {

	function __construct() {
		$this->checkGraphicSupport();
		//$_zp_imagick_version = phpversion('imagick');
		//$_zp_imagemagick_version = Imagick::getVersion();
		$this->info['Library'] = 'Imagick';
		$this->info['Library_desc'] = sprintf(gettext('PHP Imagick library <em>%s</em>') . '<br /><em>%s</em>', $this->imagick_version, $this->imagemagick_version['versionString']);
		$imagick_format_whitelist = array(
				'BMP' => 'jpg',
				'BMP2' => 'jpg',
				'BMP3' => 'jpg',
				'GIF' => 'gif',
				'GIF87' => 'gif',
				'JPG' => 'jpg',
				'JPEG' => 'jpg',
				'PNG' => 'png',
				'PNG8' => 'png',
				'PNG24' => 'png',
				'PNG32' => 'png',
				'TIFF' => 'jpg',
				'TIFF64' => 'jpg',
				'WEBP' => 'webp',
				'AVIF' => 'avif'
		);

		$imagick = new Imagick();
		$imagick_formats = $imagick->queryFormats();
		foreach ($imagick_formats as $format) {
			if (array_key_exists($format, $imagick_format_whitelist)) {
				$this->info[$format] = $imagick_format_whitelist[$format];
			}
		}
		unset($imagick_format_whitelist);
		unset($imagick_formats);
		unset($imagick);
		unset($format);

		if (DEBUG_IMAGE) {
			debugLog('Loading ' . $this->info['Library']);
		}
	}

	/**
	 * Takes an image filename and returns an Imagick image object
	 *
	 * @param string $imgfile the full path and filename of the image to load
	 * @return Imagick
	 */
	function imageGet($imgfile) {
		if (array_key_exists(strtoupper(getSuffix($imgfile)), $this->info)) {
			$image = new Imagick();
			$maxHeight = getOption('magick_max_height');
			$maxWidth = getOption('magick_max_width');
			if ($maxHeight > graphicsOptions::$ignore_size && $maxWidth > graphicsOptions::$ignore_size) {
				$image->setOption('jpeg:size', $maxWidth . 'x' . $maxHeight);
			}
			$image->readImage(filesystemToInternal($imgfile));

			//Generic CMYK to RGB conversion
			if ($image->getImageColorspace() == Imagick::COLORSPACE_CMYK) {
				$image->transformimagecolorspace(Imagick::COLORSPACE_SRGB);
			}
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
	function imageOutput($im, $type, $filename = NULL, $qual = 75) {
		$interlace = getOption('image_interlace');
		$qual = max(min($qual, 100), 0);
		$im->setImageFormat($type);
		switch ($type) {
			case 'gif':
				$im->setImageCompression(Imagick::COMPRESSION_LZW);
				$im->setImageCompressionQuality($qual);
				if ($interlace) {
					$im->setInterlaceScheme(Imagick::INTERLACE_GIF);
				}
				break;
			case 'jpeg':
			case 'jpg':
				$im->setImageCompression(Imagick::COMPRESSION_JPEG);
				$im->setImageCompressionQuality($qual);
				if ($interlace) {
					$im->setInterlaceScheme(Imagick::INTERLACE_JPEG);
				}
				break;
			case 'png':
			case 'webp': // apparently there are no interlace and compression constants for webp/avif so we just use the png setting
			case 'avif': 
				$im->setImageCompression(Imagick::COMPRESSION_ZIP);
				$im->setImageCompressionQuality($qual);
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
	 * @param bool $truecolor True to create a true color image, false for usage with palette images like gifs
	 * @return Imagick
	 */
	function createImage($w, $h, $truecolor = true) {
		$im = new Imagick();
		$im->newImage($w, $h, 'none');
		if ($truecolor) {
			$im->setImageType(Imagick::IMGTYPE_TRUECOLORMATTE);
		} else {
			$imagetype = $im->getImageType();
			$im->setImageType($imagetype);
		}
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
	function imageFill($image, $x, $y, $color) {
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
	function imageColorTransparent($image, $color) {
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
	function copyCanvas($imgCanvas, $img, $dest_x, $dest_y, $src_x, $src_y, $w, $h) {
		$img->cropImage($w, $h, $src_x, $src_y);
		$result = true;
		foreach ($imgCanvas as $frame) {
			$result &= $frame->compositeImage($img, Imagick::COMPOSITE_OVER, $dest_x, $dest_y);
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
	function resampleImage($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
		foreach ($src_image->getImageProfiles() as $name => $profile) {
			$dst_image->profileImage($name, $profile);
		}
		$result = true;
		$src_image = $src_image->coalesceImages();
		foreach ($src_image as $frame) {
			$frame->cropImage($src_w, $src_h, $src_x, $src_y);
			$frame->setImagePage(0, 0, 0, 0);
			$frame->resizeImage($dst_w, $dst_h, Imagick::FILTER_LANCZOS, 1);

			$dst_image->setImageDelay($frame->getImageDelay());
			$result &= $dst_image->compositeImage($frame, Imagick::COMPOSITE_OVER, $dst_x, $dst_y);

			if ($dst_image->getNumberImages() < $src_image->getNumberImages()) {
				$result &= $dst_image->addImage($this->createImage($dst_image->getImageWidth(), $dst_image->getImageHeight()));
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
	function imageUnsharpMask($img, $amount, $radius, $threshold) {
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
	function imageResizeAlpha($src, $w, $h) {
		$src->scaleImage($w, $h);
		return $src;
	}
	
	/**
	 * Uses imageResizeAlpha() internally as Imagick does not make a difference
	 * 
	 * @param type $src
	 * @param type $w
	 * @param type $h
	 * @return type
	 */
	function imageResizeTransparent($src, $w, $h) { 
		return $this->imageResizeAlpha($src, $w, $h);
	}

	/**
	 * Returns true if Imagick library is configured with image rotation support
	 *
	 * @return bool
	 */
	function imageCanRotate() {
		return true;
	}

	/**
	 * Rotates an image resource according to its Orientation setting
	 *
	 * @param Imagick $im Imagick object
	 * @param int $rotate Rotation degree clock wise
	 * @return Imagick
	 */
	function rotateImage($im, $rotate) {
		$im->rotateImage('none', $rotate);
		return $im;
	}
	
	/**
	 * Flips (mirrors) an image
	 * 
	 * @since 1.6
	 * 
	 * @param image $im 
	 * @param string $mode "horizontal" (default) or "vertical" 
	 * @return object
	 */
	function flipImage($im, $mode = 'horizontal') {
		switch ($mode) {
			default:
			case 'horizontal':
				$im->flopImage();
				break;
			case 'vertical';
				$im->flipImage();
				break;
		}
		return $im;
	}

	/**
	 * Returns the width of an image resource
	 *
	 * @param Imagick $im
	 * @return int
	 */
	function imageWidth($im) {
		return $im->getImageWidth();
	}

	/**
	 * Returns the height of an image resource
	 *
	 * @param Imagick $im
	 * @return int
	 */
	function imageHeight($im) {
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
	function imageMerge($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {
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
	function imageGray($image) {
		$image->setType(Imagick::IMGTYPE_GRAYSCALE);
		$image->transformImageColorspace(Imagick::COLORSPACE_GRAY);
		$image->setImageProperty('exif:ColorSpace', Imagick::IMGTYPE_GRAYSCALE);
		return $image;
	}

	/**
	 * Destroys an image resource
	 *
	 * @param Imagick $im
	 * @return bool
	 */
	function imageKill($im) {
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
	function colorAllocate($image, $red, $green, $blue) {
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
	function writeString($image, $font, $x, $y, $string, $color) {
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
	function drawRectangle($image, $x1, $y1, $x2, $y2, $color) {
		return $image->borderImage($color, 1, 1);
	}

	/**
	 * Returns a list of available fonts
	 *
	 * @return array
	 */
	function getFonts() {
		if (!is_array($this->fontlist)) {
			@$this->fontlist = Imagick::queryFonts();
			$this->fontlist = array('system' => '') + array_combine($this->fontlist, $this->fontlist);
			$basefile = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/imagick_fonts/';
			if (is_dir($basefile)) {
				chdir($basefile);
				$filelist = safe_glob('*.ttf');
				foreach ($filelist as $file) {
					$key = filesystemToInternal(str_replace('.ttf', '', $file));
					$this->fontlist[$key] = getcwd() . '/' . $file;
				}
			}
			chdir(dirname(__FILE__));
		}
		return $this->fontlist;
	}

	/**
	 * Loads a font and returns an object with the font loaded
	 *
	 * @param string $font
	 * @return ImagickDraw
	 */
function imageLoadFont($font = NULL, $size = 18) {
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
	function imageFontWidth($font) {
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
	function imageFontHeight($font) {
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
	function imageFromString($string) {
		$im = new Imagick();
		$maxHeight = getOption('magick_max_height');
		$maxWidth = getOption('magick_max_width');
		if ($maxHeight > graphicsOptions::$ignore_size && $maxWidth > graphicsOptions::$ignore_size) {
			$im->setOption('jpeg:size', $maxWidth . 'x' . $maxHeight);
		}
		$im->readImageBlob($string);
		return $im;
	}

}