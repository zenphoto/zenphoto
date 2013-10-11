<?php

/**
 * Core class for handling "non-image" files
 *
 * Text type files can be displayed in place of an image in themes
 *
 * supports files of the following types:
 * 	.txt
 * 	.htm
 * 	.html
 * 		The contents of these files are "dumpped" into a SPAN sized to a 24x36 ratioed box based on your
 * 		theme	"image size" option. This has a class of "textobject" so it can be styled.
 *
 * What this plugin really is for is to serve as a model of how a plugin can be made to handle file types
 * that zenphoto does not handle natively.
 *
 * Some key points to note:
 * 1. The naming convention for these plugins is class-«handler class».php.
 * 2. The statement setting the plugin_is_filter variable must be near the front of the file. This is important
 * as it is the indicator to the Zenphoto plugin loader to load the script at the same point that other
 * object modules are loaded.
 * 3. These objects are extension to the zenphoto "Image" class. This means they have all the properties of
 * an image plus whatever you add. Of course you will need to override some of the image class functions to
 * implement the functionality of your new class.
 * 4. There is one VERY IMPORTANT method that you must provide which is not part of the "Image" base class. That
 * getBody() method. This method is called by template-functions.php in place of where it would normally put a URL
 * to the image to show. This method must do everything needed to cause your image object to be viewable by the
 * browser.
 *
 * So, briefly, the first four lines of code below are the standard plugin interface to Admin. There is one small
 * wrinkle you might notice--the code for 'plugin_description' includes a test which sets the variable $disable.
 * As you might expect, there were some changes needed to zenphoto in order to get this concept to work.  $disable
 * is set to true if the revision of zenphoto that is attempting to load this plugin is lower than the one where the
 * implementation first appeared. The interface variable 'plugin_disable' is set to this value telling Admin not to
 * allow enabling of the plugin if the release level is too low.
 *
 * The line that follows insures that the plugin will not load when it should be disabled--just in case.
 *
 * Then there is a call on addPlginType(«file extension», «Object Name»); This function registers the plugin as the
 * handler for files with the specified extension. If the plugin can handle more than one file extension, make a call
 * to the registration function for each extension that it handles.
 *
 * The rest is the object class for handling these files.
 *
 * The code of the object instantiation function is mostly required. Plugin "images" follow the lead of videos in that
 * if there is a real image file with the same name save the suffix, it will be considered the thumb image of the object.
 * This image is fetched by the call on checkObjectsThumb(). There is also code in the getThumb() method to deal with
 * this property.
 *
 * Since text files have no natural height and width, we set them based on the image size option. This happens after the call
 * PersistentObject(). The rest of the code there sets up the default title.
 *
 * getThumb() is responsible for generating the thumbnail image for the object. As above, if there is a similar named real
 * image, it will be used. Otherwise [for this object implementation] we will use a thumbnail image provided with the plugin.
 * The particular form of the file name used when there is no thumb stand-in image allows zenphoto to choose an image in the
 * plugin folder.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 *
 */
class TextObject extends Image {

	protected $watermark = NULL;
	protected $watermarkDefault = NULL;

	/**
	 * creates a textobject (image standin)
	 *
	 * @param object $album the owner album
	 * @param string $filename the filename of the text file
	 * @return TextObject
	 */
	function __construct($album, $filename, $quiet = false) {

		$this->watermark = getOption('TextObject_watermark');
		$this->watermarkDefault = getOption('textobject_watermark_default_images');

		$this->common_instantiate($album, $filename, $quiet);
	}

	/**
	 * Handles class common instantiation
	 * @param $album
	 * @param $filename
	 */
	function common_instantiate($album, $filename, $quiet = false) {
		global $_zp_supported_images;
		$msg = false;
		if (!is_object($album) || !$album->exists) {
			$msg = gettext('Invalid Textobject instantiation: Album does not exist');
		} else if (!$this->classSetup($album, $filename) || !file_exists($this->localpath) || is_dir($this->localpath)) {
			$msg = gettext('Invalid Textobject instantiation: file does not exist');
		}
		if ($msg) {
			$this->exists = false;
			if (!$quiet) {
				trigger_error($msg, E_USER_ERROR);
			}
			return;
		}
		$this->sidecars = $_zp_supported_images;
		$this->objectsThumb = checkObjectsThumb($this->localpath);
		$this->updateDimensions();
		$new = parent::PersistentObject('images', array('filename' => $filename, 'albumid'	 => $this->album->getID()), 'filename');
		if ($new || $this->filemtime != $this->get('mtime')) {
			if ($new)
				$this->setTitle($this->displayname); $title = $this->displayname;
			$this->updateMetaData();
			$this->set('mtime', $this->filemtime);
			$this->save();
			if ($new)
				zp_apply_filter('new_image', $this);
		}
	}

	/**
	 * Returns the image file name for the thumbnail image.
	 *
	 * @param string $path override path
	 *
	 * @return s
	 */
	function getThumbImageFile($path = NULL) {
		global $_zp_gallery;
		if (is_null($path)) {
			$path = SERVERPATH;
		}
		if (is_null($this->objectsThumb)) {
			switch (getSuffix($this->filename)) {
				default: // just in case we extend and are lazy...
					$img = '/textDefault.png';
					break;
			}
			$imgfile = $path . '/' . THEMEFOLDER . '/' . internalToFilesystem($_zp_gallery->getCurrentTheme()) . '/images/' . $img;
			if (!file_exists($imgfile)) {
				$imgfile = $path . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/class-textobject/' . $img;
			}
		} else {
			$imgfile = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($this->imagefolder) . '/' . $this->objectsThumb;
		}
		return $imgfile;
	}

	/**
	 * returns a link to the thumbnail for the text file.
	 *
	 * @param string $type 'image' or 'album'
	 * @return string
	 */
	function getThumb($type = 'image') {
		$ts = getOption('thumb_size');
		$sw = getOption('thumb_crop_width');
		$sh = getOption('thumb_crop_height');
		list($custom, $cw, $ch, $cx, $cy) = $this->getThumbCropping($ts, $sw, $sh);
		$wmt = $this->watermark;
		if (empty($wmt)) {
			$wmt = getWatermarkParam($this, WATERMARK_THUMB);
		}
		if (is_null($this->objectsThumb)) {
			$mtime = $cx = $cy = NULL;
			$filename = makeSpecialImageName($this->getThumbImageFile());
			if (!$this->watermarkDefault) {
				$wmt = '!';
			}
		} else {
			$filename = filesystemToInternal($this->objectsThumb);
			$mtime = filemtime(ALBUM_FOLDER_SERVERPATH . '/' . internalToFilesystem($this->imagefolder) . '/' . $this->objectsThumb);
		}
		$args = getImageParameters(array($ts, $sw, $sh, $cw, $ch, $cx, $cy, NULL, true, true, true, $wmt, NULL, NULL), $this->album->name);
		$cachefilename = getImageCacheFilename($alb = $this->album->name, $this->filename, $args);
		return getImageURI($args, $alb, $filename, $mtime);
	}

	/**
	 * Returns the content of the text file
	 *
	 * @param int $w optional width
	 * @param int $h optional height
	 * @return string
	 */
	function getBody($w = NULL, $h = NULL) {
		$this->updateDimensions();
		if (is_null($w))
			$w = $this->getWidth();
		if (is_null($h))
			$h = $this->getHeight();
		switch (getSuffix($this->filename)) {
			case 'txt':
			case 'htm':
			case 'html':
				return '<span style="display:block;width:' . $w . 'px;height:' . $h . 'px;" class="textobject">' . @file_get_contents($this->localpath) . '</span>';
			default: // just in case we extend and are lazy...
				return '<img src="' . html_encode(pathurlencode($this->getThumb())) . '">';
		}
	}

	/**
	 *  Get a custom sized version of this image based on the parameters.
	 *
	 * @param string $alt Alt text for the url
	 * @param int $size size
	 * @param int $width width
	 * @param int $height height
	 * @param int $cropw crop width
	 * @param int $croph crop height
	 * @param int $cropx crop x axis
	 * @param int $cropy crop y axis
	 * @param string $class Optional style class
	 * @param string $id Optional style id
	 * @param bool $thumbStandin set to true to treat as thumbnail
	 * @param bool $effects ignored
	 * @return string
	 */
	function getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin = false, $effects = NULL) {
		if ($thumbStandin) {
			$wmt = $this->watermark;
			if (empty($wmt)) {
				$wmt = getWatermarkParam($this, WATERMARK_THUMB);
			}
		} else {
			$wmt = NULL;
		}
		if ($thumbStandin & 1) {
			$args = getImageParameters(array($size, $width, $height, $cropw, $croph, $cropx, $cropy, NULL, $thumbStandin, NULL, $thumbStandin, $wmt, NULL, $effects), $this->album->name);
			if ($this->objectsThumb == NULL) {
				$filename = makeSpecialImageName($this->getThumbImageFile());
				if (!$this->watermarkDefault) {
					$args[11] = '!';
				}
				$mtime = NULL;
			} else {
				$filename = filesystemToInternal($this->objectsThumb);
				$mtime = filemtime(ALBUM_FOLDER_SERVERPATH . '/' . internalToFilesystem($this->imagefolder) . '/' . $this->objectsThumb);
			}
			return getImageURI($args, $this->album->name, $filename, $mtime);
		} else {
			return $this->getBody($width, $height);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see zp-core/Image::getSizedImage()
	 */
	function getSizedImage($size) {
		switch (getOption('image_use_side')) {
			case 'width':
			case 'longest':
				$w = $size;
				$h = floor(($size * 24) / 36);
				break;
			case 'height':
			case 'shortest':
				$h = $size;
				$w = floor(($size * 36) / 24);
				break;
		}

		return $this->getBody($w, $h);
	}

	/**
	 * (non-PHPdoc)
	 * @see zp-core/Image::updateDimensions()
	 */
	function updateDimensions() {
		$size = getOption('image_size');
		switch (getOption('image_use_side')) {
			case 'width':
			case 'longest':
				$this->set('width', getOption('image_size'));
				$this->set('height', floor((getOption('image_size') * 24) / 36));
				break;
			case 'height':
			case 'shortest':
				$this->set('height', getOption('image_size'));
				$this->set('width', floor((getOption('image_size') * 36) / 24));
				break;
		}
	}

}

?>