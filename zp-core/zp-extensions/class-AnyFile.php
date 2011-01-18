<?php
/**
 * Plugin handler for arbitray files.
 * Only thumbnails are displayed.
 *
 * Default thumbnail images may be created in the "plugins/class-AnyFile" folder. The naming convention is
 * "<suffix>Default.png". If no such file is found, the class object default thumbnail will be used.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 *
 */

$plugin_is_filter = 9|CLASS_PLUGIN;
$plugin_description = gettext('Provides a means for handling arbitrary file types. (No rendering provided!)');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';

foreach (get_AnyFile_suffixes() as $suffix) {
	addPluginType($suffix, 'AnyFile');
}
$option_interface = 'AnyFile_Options';

/**
 * Option class for textobjects objects
 *
 */
class AnyFile_Options {

	/**
	 * Standard option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		$listi = array();
		foreach (get_AnyFile_suffixes() as $suffix) {
			$listi[$suffix] = 'AnyFile_file_list_'.$suffix;
		}
		if ($suffix = getOption('AnyFile_file_new')) {
			setOption('AnyFile_file_new', '');
			$listi[$suffix] = 'AnyFile_file_list_'.$suffix;
			setOption('AnyFile_file_list_'.$suffix, 1);
		}
		return array(gettext('Watermark default images') => array ('key' => 'AnyFile_watermark_default_images', 'type' => OPTION_TYPE_CHECKBOX,
																	'desc' => gettext('Check to place watermark image on default thumbnail images.')),
									gettext('Handled files') => array ('key'=> 'AnyFile_file_list', 'type'=>OPTION_TYPE_CHECKBOX_UL,
																	'checkboxes' => $listi,
																	'desc' => gettext('File suffixes to be handled.')),
									gettext('Add file suffix') => array('key'=> 'AnyFile_file_new', 'type'=>OPTION_TYPE_TEXTBOX,
																	'desc' => gettext('Add a file suffix to be handled by the plugin'))
								);
	}
	function handleOption($option, $currentValue) {
	}
}

function get_AnyFile_suffixes() {
	$mysetoptions = array();
	$alloptionlist = getOptionList();
	foreach ($alloptionlist as $key=>$option) {
		if (($option == 1) && strpos($key, 'AnyFile_file_list_') === 0) {
			if ($option) {
				$mysetoptions[] = str_replace('AnyFile_file_list_', '', $key);
			} else {
				purgeOption($key);
			}
		}
	}
	return $mysetoptions;
}

require_once(dirname(__FILE__).'/class-TextObject.php');

class AnyFile extends TextObject {

	/**
	 * creates a GoogleDocs (image standin)
	 *
	 * @param object $album the owner album
	 * @param string $filename the filename of the text file
	 * @return TextObject
	 */
	function AnyFile($album, $filename) {
		global $_zp_supported_images;

		// $album is an Album object; it should already be created.
		if (!is_object($album)) return NULL;
		if (!$this->classSetup($album, $filename)) { // spoof attempt
			$this->exists = false;
			return;
		}
		$this->sidecars = $_zp_supported_images;
		$this->objectsThumb = checkObjectsThumb($album->localpath, $filename);
		// Check if the file exists.
		if (!file_exists($this->localpath) || is_dir($this->localpath)) {
			$this->exists = false;
			return;
		}
		$this->updateDimensions();
		if (parent::PersistentObject('images', array('filename'=>$filename, 'albumid'=>$this->album->id), 'filename', false, false)) {
			$title = $this->getDefaultTitle();
			$this->set('title', $title);
			$this->set('mtime', $ts = filectime($this->localpath));
			$newdate = strftime('%Y-%m-%d %T', $ts);
			$this->updateMetaData();
			$this->save();
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
	function getThumbImageFile($path=NULL) {
		if (is_null($path)) {
			$path = SERVERPATH;
		}
		if ($this->objectsThumb != NULL) {
			$imgfile = getAlbumFolder().$this->album->name.'/'.$this->objectsThumb;
		} else {
			$img = '/'.getSuffix($this->filename).'Default.png';
			$imgfile = $path . '/' . THEMEFOLDER . '/' . internalToFilesystem($this->album->gallery->getCurrentTheme()) . '/images/'.$img;
			if (!file_exists($imgfile)) {
				$imgfile = $path . "/" . USER_PLUGIN_FOLDER .'/'. substr(basename(__FILE__), 0, -4). '/'.$img;
				if (!file_exists($imgfile)) {
					$imgfile = $path . "/" . ZENFOLDER . '/'.PLUGIN_FOLDER .'/'. substr(basename(__FILE__), 0, -4). '/anyFileDefault.png';
				}
			}
		}
	return $imgfile;
	}

	/**
	 * returns a link to the thumbnail for the text file.
	 *
	 * @param string $type 'image' or 'album'
	 * @return string
	 */
	function getThumb($type='image') {
		list($custom, $sw, $sh, $cw, $ch, $cx, $cy) = $this->getThumbCropping($type);
		$wmt = getOption('AnyFile_watermark');
		if ($this->objectsThumb == NULL) {
			$cx = $cy = NULL;
			$filename = makeSpecialImageName($this->getThumbImageFile());
			if (!getOption('AnyFile_watermark_default_images')) {
				$wmt = '!';
			}
		} else {
			$filename = $this->objectsThumb;
		}
		$args = getImageParameters(array(getOption('thumb_size'), $sw, $sh, $cw, $ch, $cx, $cy, NULL, true, true, true, $wmt, NULL, NULL), $this->album->name);		$cachefilename = getImageCacheFilename($alb = $this->album->name, $this->filename, $args);
		if (file_exists(SERVERCACHE . $cachefilename)	&& filemtime(SERVERCACHE . $cachefilename) > $this->filemtime) {
			return WEBPATH . '/'.CACHEFOLDER . pathurlencode(imgSrcURI($cachefilename));
		} else {
			return getImageProcessorURI($args, $this->album->name, $filename);
		}
	}

	/**
	 * Returns the content of the text file
	 *
	 * @param int $w optional width
	 * @param int $h optional height
	 * @return string
	 */
	function getBody($w=NULL, $h=NULL) {
		$this->updateDimensions();
		if (is_null($w)) $w = $this->getWidth();
		if (is_null($h)) $h = $this->getHeight();
		switch(getSuffix($this->filename)) {
			case 'pps':
			case 'pdf':
				return '<iframe src="http://docs.google.com/gview?url='.$this->getFullImage(FULLWEBPATH).'&amp;embedded=true" style="width:'.$w.'px;height:'.$h.'px;" frameborder="0"></iframe>';
			default: // just in case we extend and are lazy...
				return '<img src="'.$this->getThumb().'">';
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
	function getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin=false, $effects=NULL) {
		if ($thumbStandin) {
			$wmt = getOption('GoogleDocs_watermark');
		} else {
			$wmt = NULL;
		}
		$args = getImageParameters(array($size, $width, $height, $cropw, $croph, $cropx, $cropy, NULL, $thumbStandin, NULL, $thumbStandin, $wmt, NULL, $effects), $this->album->name);
		if ($thumbStandin) {
			if ($this->objectsThumb == NULL) {
				$filename = makeSpecialImageName($this->getThumbImageFile());
				if (!getOption('AnyFile_watermark_default_images')) {
					$args[11] = '!';
				}
				return getImageProcessorURI($args, $this->album->name, $filename);
			} else {
				$filename = $this->objectsThumb;
				$cachefilename = getImageCacheFilename($alb = $this->album->name, $filename, $args);
				if (file_exists(SERVERCACHE . $cachefilename) && filemtime(SERVERCACHE . $cachefilename) > $this->filemtime) {
					return WEBPATH . '/'.CACHEFOLDER . pathurlencode(imgSrcURI($cachefilename));
				} else {
					return getImageProcessorURI($args, $this->album->name, $filename);
				}
			}
		} else {
			return $this->getBody($width, $height);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see zp-core/_Image::getSizedImage()
	 */
	function getSizedImage($size) {
		$width = $this->getWidth();
		$height = $this->getHeight;
		if ($width > $height) {	//portrait
			$height = $height * $size/$width;
		} else {
			$width = $width * $size/$height;
		}
		return $this->getBody($width, $height);
	}

	/**
	 * (non-PHPdoc)
	 * @see zp-core/_Image::updateDimensions()
	 */
	function updateDimensions() {
		$this->set('width', getOption('image_size'));
		$this->set('height', floor((getOption('image_size') * 24) / 36));
	}

}

?>