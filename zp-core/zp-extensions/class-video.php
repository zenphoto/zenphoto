<?php
/**
 * This plugin handles "video" class images
 * @package classes
 */

// force UTF-8 Ø

$plugin_is_filter = 9|CLASS_PLUGIN;
$plugin_description = gettext('Audio (MP3,M4A,FLA) and video (MP4/M4V,FLV pluas Quicktime,3GP if Quicktime is installed on the visitor system) handling for Zenphoto. This plugin must always be enabled to use multimedia content. Note that you also need to enable a multimedia player. See the info there how each format is used.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.2';

addPluginType('flv', 'Video');
addPluginType('fla', 'Video');
addPluginType('3gp', 'Video');
addPluginType('mov', 'Video');
addPluginType('mp3', 'Video');
addPluginType('mp4', 'Video');
addPluginType('m4v', 'Video');
addPluginType('m4a', 'Video');
$option_interface = 'VideoObject_Options';

define('GETID3_INCLUDEPATH', SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/class-video/getid3/');
require_once(dirname(__FILE__).'/class-video/getid3/getid3.php');

/**
 * Option class for video objects
 *
 */
class VideoObject_Options {


	function VideoObject_Options() {
		setOptionDefault('zp_plugin_class-video_mov_w',520);
		setOptionDefault('zp_plugin_class-video_mov_h',390);
		setOptionDefault('zp_plugin_class-video_3gp_w',520);
		setOptionDefault('zp_plugin_class-video_3gp_h',390);
	}
	/**
	 * Standard option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(gettext('Watermark default images') => array ('key' => 'video_watermark_default_images', 'type' => OPTION_TYPE_CHECKBOX,
																	'desc' => gettext('Check to place watermark image on default thumbnail images.')),
		gettext('Quicktime video width') => array ('key' => 'zp_plugin_class-video_mov_w', 'type' => OPTION_TYPE_TEXTBOX,
																	'desc' => ''),
		gettext('Quicktime video height') => array ('key' => 'zp_plugin_class-video_mov_h', 'type' => OPTION_TYPE_TEXTBOX,
																	'desc' => ''),
		gettext('3gp video width') => array ('key' => 'zp_plugin_class-video_3gp_w', 'type' => OPTION_TYPE_TEXTBOX,
																	'desc' => ''),
		gettext('3gp video height') => array ('key' => 'zp_plugin_class-video_3gp_h', 'type' => OPTION_TYPE_TEXTBOX,
																	'desc' => '')
		);
	}

}

class Video extends _Image {

	/**
	 * Constructor for class-video
	 *
	 * @param object &$album the owning album
	 * @param sting $filename the filename of the image
	 * @return Image
	 */
	function __construct(&$album, $filename) {
		global $_zp_supported_images;
		// $album is an Album object; it should already be created.
		if (!is_object($album)) return NULL;
		if (!$this->classSetup($album, $filename)) { // spoof attempt
			$this->exists = false;
			return;
		}
		$this->sidecars = $_zp_supported_images;
		$this->video = true;
		$this->objectsThumb = checkObjectsThumb($album->localpath, $filename);
		// Check if the file exists.
		if (!file_exists($this->localpath) || is_dir($this->localpath)) {
			$this->exists = false;
			return;
		}


		// This is where the magic happens...
		$album_name = $album->name;
		$this->updateDimensions();
		if (parent::PersistentObject('images', array('filename'=>$filename, 'albumid'=>$this->album->id), 'filename', true, empty($album_name))) {
			$this->set('mtime', $ts = filemtime($this->localpath));
			$this->updateMetaData();
			$this->save();
			zp_apply_filter('new_image', $this);
		}
	}

	/**
	 * Update this object's values for width and height.
	 *
	 */
	function updateDimensions() {
		global $_zp_flash_player;
		$ext = strtolower(strrchr($this->filename, "."));
		if (is_null($_zp_flash_player) || $ext == '.3gp' || $ext == '.mov') {
			switch ($ext) {
				case '.3gp':
					$h = getOption('zp_plugin_class-video_3gp_h');
					$w = getOption('zp_plugin_class-video_3gp_w');
					break;
				case '.mov':
					$h = getOption('zp_plugin_class-video_mov_h');
					$w = getOption('zp_plugin_class-video_mov_w');
					break;
				default:
					$h = 320;
					$w = 480;
			}
		} else {
			$h = $_zp_flash_player->getVideoHeigth($this);
			$w = $_zp_flash_player->getVideoWidth($this);
		}
		$this->set('width', $w);
		$this->set('height', $h);
	}

	/**
	 * Returns the image file name for the thumbnail image.
	 *
	 * @param string $path override path
	 *
	 * @return string
	 */
	function getThumbImageFile($path=NULL) {
		if (is_null($path)) $path = SERVERPATH;
		if (is_null($this->objectsThumb)) {
			$suffix = getSuffix($this->filename);
			switch($suffix) {
				case "mp3":
					$img = '/mp3Default.png';
					break;
				case "mp4": // generic suffix for mp4 stuff - considered video
					$img = '/mp4Default.png';
					break;
				case "m4v": // specific suffix for mp4 video
					$img = '/m4vDefault.png';
					break;
				case "m4a": // specific suffix for mp4/AAC audio
					$img = '/m4aDefault.png';
					break;
				case "flv": // suffix for flash video container
					$img = '/flvDefault.png';
					break;
				case "fla": // suffix for flash audio container
					$img = '/flaDefault.png';
					break;
				case "mov":
					$img = '/movDefault.png';
					break;
				case "3gp":
					$img = '/3gpDefault.png';
					break;
				default: // just in case we extend and are lazy...
					$img = '/multimediaDefault.png';
					break;
			}
			$imgfile = $path . '/' . THEMEFOLDER . '/' . internalToFilesystem($this->album->gallery->getCurrentTheme()) . '/images'.$img;
			if (!file_exists($imgfile)) {  // first check if the theme has adefault image
				$imgfile = $path . '/' . THEMEFOLDER . '/' . internalToFilesystem($this->album->gallery->getCurrentTheme()) . '/images/multimediaDefault.png';
				if (!file_exists($imgfile)) { // if theme has a generic default image use it otherwise use the Zenphoto image
					$imgfile = $path . "/" . ZENFOLDER . '/'.PLUGIN_FOLDER.'/' . substr(basename(__FILE__), 0, -4).$img;
				}
			}
		} else {
			$imgfile = ALBUM_FOLDER_SERVERPATH.$this->album->name.'/'.$this->objectsThumb;
		}
		return $imgfile;
	}

	/**
	 * Get a default-sized thumbnail of this image.
	 *
	 * @return string
	 */
	function getThumb($type='image') {
		list($custom, $sw, $sh, $cw, $ch, $cx, $cy) = $this->getThumbCropping($type);
		$wmt = getOption('Video_watermark');
		if (empty($wmt)) {
			$wmt = getWatermarkParam($this, WATERMARK_THUMB);
		}
		if ($this->objectsThumb == NULL) {
			$cx = $cy = NULL;
			$filename = makeSpecialImageName($this->getThumbImageFile());
			if (!getOption('video_watermark_default_images')) {
				$wmt = '!';
			}
		} else {
			$filename = $this->objectsThumb;
		}
		$args = getImageParameters(array(getOption('thumb_size'), $sw, $sh, $cw, $ch, $cx, $cy, NULL, true, true, true, $wmt, NULL, NULL), $this->album->name);
		$cachefilename = getImageCacheFilename($alb = $this->album->name, $this->filename, $args);
		if (file_exists(SERVERCACHE . $cachefilename)	&& filemtime(SERVERCACHE . $cachefilename) > $this->filemtime) {
			return WEBPATH . '/'.CACHEFOLDER . pathurlencode(imgSrcURI($cachefilename));
		} else {
			return getImageProcessorURI($args, $this->album->name, $filename);
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
			$wmt = getOption('Video_watermark');
			if (empty($wmt)) {
				$wmt = getWatermarkParam($this, WATERMARK_THUMB);
			}
		} else {
			$wmt = NULL;
		}
		$args = getImageParameters(array($size, $width, $height, $cropw, $croph, $cropx, $cropy, NULL, $thumbStandin, NULL, $thumbStandin, $wmt, NULL, $effects), $this->album->name);
		if ($thumbStandin & 1) {
			if ($this->objectsThumb == NULL) {
				$filename = makeSpecialImageName($this->getThumbImageFile());
				if (!getOption('video_watermark_default_images')) {
					$args[11] = '!';
				}
				return getImageProcessorURI($args, $this->album->name, $filename);
			} else {
				$filename = $this->objectsThumb;
				$cachefilename = getImageCacheFilename($alb = $this->album->name, $filename,
														getImageParameters(array($size, $width, $height, $cropw, $croph, $cropx, $cropy, NULL, $thumbStandin, NULL, $thumbStandin, NULL, NULL, NULL)), $this->album->name);
				if (file_exists(SERVERCACHE . $cachefilename) && filemtime(SERVERCACHE . $cachefilename) > $this->filemtime) {
					return WEBPATH . '/'.CACHEFOLDER . pathurlencode(imgSrcURI($cachefilename));
				} else {
					return getImageProcessorURI($args, $this->album->name, $filename);
				}
			}
		} else {
			$filename = $this->filename;
			$cachefilename = getImageCacheFilename($this->album->name, $filename,	$args);
			if (file_exists(SERVERCACHE . $cachefilename) && filemtime(SERVERCACHE . $cachefilename) > $this->filemtime) {
				return WEBPATH . '/'.CACHEFOLDER . pathurlencode(imgSrcURI($cachefilename));
			} else {
				return getImageProcessorURI($args, $this->album->name, $filename);
			}
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
	 * returns the content of the vido
	 *
	 * @param $w
	 * @param $h
	 * @return string
	 */
	function getBody($w=NULL, $h=NULL) {
		global $_zp_flash_player;
		if (is_null($w)) $w = $this->getWidth();
		if (is_null($h)) $h = $this->getHeight();
		$ext = strtolower(strrchr($this->getFullImage(), "."));
		switch ($ext) {
			case '.flv':
			case '.fla':
			case '.mp3':
			case '.mp4':
			case '.m4v':
			case '.m4a':
				if (is_null($_zp_flash_player)) {
					return  '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/err-noflashplayer.png" alt="'.gettext('No flash player installed.').'" />';
				} else {
					return $_zp_flash_player->getPlayerConfig('',$this->getTitle(), '', $w, $h);
				}
				break;
			case '.3gp':
			case '.mov':
				return '</a>
					<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="'.$w.'" height="'.$h.'" codebase="http://www.apple.com/qtactivex/qtplugin.cab">
					<param name="src" value="' . pathurlencode($this->getFullImage()) . '"/>
					<param name="autoplay" value="false" />
					<param name="type" value="video/quicktime" />
					<param name="controller" value="true" />
					<embed src="' . pathurlencode($this->getFullImage()) . '" width="'.$w.'" height="'.$h.'" scale="aspect" autoplay="false" controller"true" type="video/quicktime"
						pluginspage="http://www.apple.com/quicktime/download/" cache="true"></embed>
					</object><a>';
				break;
		}
	}

	/**
	 *
	 * "video" metadata support function
	 */
	private function getMetaDataID3() {
		$allowedmedia = array('m4a','m4v','mp3','mp4','flv','fla','mov','3gp');
		$albobj = $this->getAlbum();
		$FullFileName = SERVERPATH.'/'.ALBUMFOLDER.'/'.$albobj->name.'/'.$this->filename; //this full path is required
		$suffix = getSuffix($FullFileName);
		if(in_array($suffix,$allowedmedia)) {
			$getID3 = new getID3;
			set_time_limit(30);
			$ThisFileInfo = $getID3->analyze($FullFileName);
			getid3_lib::CopyTagsToComments($ThisFileInfo);
			// output desired information in whatever format you want
			if(is_array($ThisFileInfo)) {
				return $ThisFileInfo;
			} else {
				return NULL; // don't try to cover other files even if getid3 reads images as well
			}
		}
	}


	/**
	 * Processes multi-media file metadata
	 * (non-PHPdoc)
	 * @see zp-core/_Image::updateMetaData()
	 */
	function updateMetaData() {
		global $_zp_exifvars;
		$ThisFileInfo = $this->getMetaDataID3();
		if(is_array($ThisFileInfo)) {
			foreach ($ThisFileInfo as $key=>$info) {
				if (is_array($info)) {
					switch ($key) {
						case 'comments':
							foreach ($info as $key1=>$data) {
								$ThisFileInfo[$key1] = array_shift($data);
							}
							break;
						case 'audio':
						case 'video':
							foreach ($info as $key1=>$data) {
								$ThisFileInfo[$key1] = $data;
							}
							break;
						default:
							//discard, not used
							break;
					}
					unset($ThisFileInfo[$key]);
				}
			}
			foreach ($_zp_exifvars as $field=>$exifvar) {
				if (strpos($exifvar[0], 'VIDEO') !== false) {
					if ($exifvar[0] == 'VIDEO') {
						if (isset($ThisFileInfo[$exifvar[1]])) {
							$data = $ThisFileInfo[$exifvar[1]];
							if (!empty($data)) {
								$this->set($field, $data);
							}
						}
					}
				}
			}
			$title = $this->get('VideoTitle');
			$imagetitle = $this->getTitle(); 
			if(!empty($title) && empty($imagetitle)) {
				$this->set('title',$title);
			}
		}
		parent::updateMetaData();
	}
}
?>