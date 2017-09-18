<?php

/**
 *
 * This plugin directly handles the <code>3gp</code> and <code>mov</code> <i>video</i>
 * class images if <i>Apple Quicktime</i> is installed on the visitors system.
 * Other formats require a multimedia player to be enabled. The actual supported multimedia types may vary
 * according to the player enabled.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package classes
 * @subpackage media
 */
// force UTF-8 Ø

$plugin_is_filter = defaultExtension(990 | CLASS_PLUGIN);
$plugin_description = gettext('The <em>audio-video</em> handler.');
$plugin_notice = gettext('This plugin handles <code>3gp</code> and <code>mov</code> multi-media files. <strong>Note:</strong> you should also enable a multimedia player plugin to handle other media files.');
$plugin_author = "Stephen Billard (sbillard)";

if (extensionEnabled('class-video')) {
	Gallery::addImageHandler('3gp', 'Video');
	Gallery::addImageHandler('mov', 'Video');
}
$option_interface = 'VideoObject_Options';

define('GETID3_INCLUDEPATH', SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/class-video/getid3/');
require_once(dirname(__FILE__) . '/class-video/getid3/getid3.php');

/**
 * Option class for video objects
 *
 */
class VideoObject_Options {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('class-video_mov_w', 520);
			setOptionDefault('class-video_mov_h', 390);
			setOptionDefault('class-video_3gp_w', 520);
			setOptionDefault('class-video_3gp_h', 390);
			setOptionDefault('class-video_videoalt', 'ogg, avi, wmv');
		}
	}

	/**
	 * Standard option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(gettext('Watermark default images') => array('key' => 'video_watermark_default_images', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 0,
						'desc' => gettext('Check to place watermark image on default thumbnail images.')),
				gettext('Quicktime video width') => array('key' => 'class-video_mov_w', 'type' => OPTION_TYPE_NUMBER,
						'order' => 2,
						'desc' => ''),
				gettext('Quicktime video height') => array('key' => 'class-video_mov_h', 'type' => OPTION_TYPE_NUMBER,
						'order' => 2,
						'desc' => ''),
				gettext('3gp video width') => array('key' => 'class-video_3gp_w', 'type' => OPTION_TYPE_NUMBER,
						'order' => 2,
						'desc' => ''),
				gettext('3gp video height') => array('key' => 'class-video_3gp_h', 'type' => OPTION_TYPE_NUMBER,
						'order' => 2,
						'desc' => ''),
				gettext('High quality alternate') => array('key' => 'class-video_videoalt', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext('<code>getFullImageURL()</code> returns a URL to a file with one of these high quality video alternate suffixes if present.'))
		);
	}

}

class Video extends Image {

	var $videoalt = array();

	/**
	 * Constructor for class-video
	 *
	 * @param object &$album the owning album
	 * @param sting $filename the filename of the image
	 * @return Image
	 */
	function __construct($album, $filename, $quiet = false) {
		global $_zp_supported_images;

		$msg = $this->invalid($album, $filename);
		if ($msg) {
			$this->exists = false;
			if (!$quiet) {
				debugLogBacktrace($msg);
			}
			return;
		}
		$alts = explode(',', extensionEnabled('class-video_videoalt'));
		foreach ($alts as $alt) {
			$this->videoalt[] = trim(strtolower($alt));
		}
		$this->sidecars = $_zp_supported_images;
		$this->video = true;
		$this->objectsThumb = checkObjectsThumb($this->localpath);

		// This is where the magic happens...
		$album_name = $album->name;
		$this->updateDimensions();

		$new = $this->instantiate('images', array('filename' => $filename, 'albumid' => $this->album->getID()), 'filename', true, empty($album_name));
		if ($new || $this->filemtime != $this->get('mtime')) {
			if ($new)
				$this->setTitle($this->displayname);
			$this->updateMetaData();
			$this->set('mtime', $this->filemtime);
			$this->save();
			if ($new)
				zp_apply_filter('new_image', $this);
		}
	}

	/**
	 * returns the database fields used by the object
	 * @return array
	 *
	 * @author Stephen Billard
	 * @Copyright 2015 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
	 */
	static function getMetadataFields() {
		return array(
				// Database Field     	 => array(0:'source', 1:'Metadata Key', 2;'ZP Display Text', 3:Display?	4:size,	5:enabled, 6:type, 7:linked)
				'VideoFormat' => array('VIDEO', 'fileformat', gettext('Video File Format'), false, 32, true, 'string', false),
				'VideoSize' => array('VIDEO', 'filesize', gettext('Video File Size'), false, 32, true, 'number', false),
				'VideoArtist' => array('VIDEO', 'artist', gettext('Video Artist'), false, 256, true, 'string', false),
				'VideoTitle' => array('VIDEO', 'title', gettext('Video Title'), false, 256, true, 'string', false),
				'VideoBitrate' => array('VIDEO', 'bitrate', gettext('Bitrate'), false, 32, true, 'number', false),
				'VideoBitrate_mode' => array('VIDEO', 'bitrate_mode', gettext('Bitrate_Mode'), false, 32, true, 'string', false),
				'VideoBits_per_sample' => array('VIDEO', 'bits_per_sample', gettext('Bits per sample'), false, 32, true, 'number', false),
				'VideoCodec' => array('VIDEO', 'codec', gettext('Codec'), false, 32, true, 'string', false),
				'VideoCompression_ratio' => array('VIDEO', 'compression_ratio', gettext('Compression Ratio'), false, 32, true, 'number', false),
				'VideoDataformat' => array('VIDEO', 'dataformat', gettext('Video Dataformat'), false, 32, true, 'string', false),
				'VideoEncoder' => array('VIDEO', 'encoder', gettext('File Encoder'), false, 10, true, 'string', false),
				'VideoSamplerate' => array('VIDEO', 'Samplerate', gettext('Sample rate'), false, 32, true, 'number', false),
				'VideoChannelmode' => array('VIDEO', 'channelmode', gettext('Channel mode'), false, 32, true, 'string', false),
				'VideoFormat' => array('VIDEO', 'format', gettext('Format'), false, 10, true, 'string', false),
				'VideoChannels' => array('VIDEO', 'channels', gettext('Channels'), false, 10, true, 'number', false),
				'VideoFramerate' => array('VIDEO', 'framerate', gettext('Frame rate'), false, 32, true, 'number', false),
				'VideoResolution_x' => array('VIDEO', 'resolution_x', gettext('X Resolution'), false, 32, true, 'number', false),
				'VideoResolution_y' => array('VIDEO', 'resolution_y', gettext('Y Resolution'), false, 32, true, 'number', false),
				'VideoAspect_ratio' => array('VIDEO', 'pixel_aspect_ratio', gettext('Aspect ratio'), false, 32, true, 'number', false),
				'VideoPlaytime' => array('VIDEO', 'playtime_string', gettext('Play Time'), false, 10, true, 'string', false)
		);
	}

	/**
	 * Update this object's values for width and height.
	 *
	 */
	function updateDimensions() {
		global $_zp_multimedia_extension;
		$ext = getSuffix($this->filename);
		switch ($ext) {
			case '3gp':
				$h = extensionEnabled('class-video_3gp_h');
				$w = extensionEnabled('class-video_3gp_w');
				break;
			case 'mov':
				$h = extensionEnabled('class-video_mov_h');
				$w = extensionEnabled('class-video_mov_w');
				break;
			default:
				$h = $_zp_multimedia_extension->getHeight($this);
				$w = $_zp_multimedia_extension->getWidth($this);
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
	function getThumbImageFile($path = NULL) {
		global $_zp_gallery;
		if (is_null($path))
			$path = SERVERPATH;
		if (is_null($this->objectsThumb)) {
			$suffix = getSuffix($this->filename);
			switch ($suffix) {
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
			$imgfile = $path . '/' . THEMEFOLDER . '/' . internalToFilesystem($_zp_gallery->getCurrentTheme()) . '/images' . $img;
			if (!file_exists($imgfile)) { // first check if the theme has adefault image
				$imgfile = $path . '/' . THEMEFOLDER . '/' . internalToFilesystem($_zp_gallery->getCurrentTheme()) . '/images/multimediaDefault.png';
				if (!file_exists($imgfile)) { // if theme has a generic default image use it otherwise use the standard image
					$imgfile = $path . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/' . substr(basename(__FILE__), 0, -4) . $img;
				}
			}
		} else {
			$imgfile = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($this->imagefolder) . '/' . $this->objectsThumb;
		}
		return $imgfile;
	}

	/**
	 * Get a default-sized thumbnail of this image.
	 *
	 * @return string
	 */
	function getThumb($type = 'image', $wmt = NULL) {
		$ts = getOption('thumb_size');
		$sw = getOption('thumb_crop_width');
		$sh = getOption('thumb_crop_height');
		list($custom, $cw, $ch, $cx, $cy) = $this->getThumbCropping($ts, $sw, $sh);
		if (empty($wmt))
			$wmt = getOption('Video_watermark');
		if (empty($wmt))
			$wmt = getWatermarkParam($this, WATERMARK_THUMB);

		if ($this->objectsThumb == NULL) {
			$mtime = $cx = $cy = NULL;
			$filename = makeSpecialImageName($this->getThumbImageFile());
			if (!getOption('video_watermark_default_images')) {
				$wmt = '!';
			}
		} else {
			$filename = filesystemToInternal($this->objectsThumb);
			$mtime = filemtime(ALBUM_FOLDER_SERVERPATH . '/' . internalToFilesystem($this->imagefolder) . '/' . $this->objectsThumb);
		}
		$args = getImageParameters(array($ts, $sw, $sh, $cw, $ch, $cx, $cy, NULL, true, true, true, $wmt, NULL, NULL), $this->album->name);
		return getImageURI($args, $this->album->name, $filename, $mtime);
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
			$wmt = getOption('Video_watermark');
			if (empty($wmt)) {
				$wmt = getWatermarkParam($this, WATERMARK_THUMB);
			}
		} else {
			$wmt = NULL;
		}
		if ($thumbStandin & 1) {
			$args = array($size, $width, $height, $cropw, $croph, $cropx, $cropy, NULL, $thumbStandin, NULL, $thumbStandin, NULL, NULL, NULL);
			if ($this->objectsThumb == NULL) {
				$filename = makeSpecialImageName($this->getThumbImageFile());
				if (!getOption('video_watermark_default_images')) {
					$args[11] = '!';
				}
				$mtime = NULL;
			} else {
				$filename = filesystemToInternal($this->objectsThumb);
				$mtime = filemtime(ALBUM_FOLDER_SERVERPATH . '/' . internalToFilesystem($this->imagefolder) . '/' . $this->objectsThumb);
			}
			return getImageURI($args, $this->album->name, $filename, $this->filemtime);
		} else {
			$args = getImageParameters(array($size, $width, $height, $cropw, $croph, $cropx, $cropy, NULL, $thumbStandin, NULL, $thumbStandin, $wmt, NULL, $effects), $this->album->name);
			$filename = $this->filename;
			return getImageURI($args, $this->album->name, $filename, $this->filemtime);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see zp-core/Image::getSizedImage()
	 */
	function getSizedImage($size) {
		$width = $this->getWidth();
		$height = $this->getHeight();
		if ($width > $height) { //portrait
			$height = $height * $size / $width;
		} else {
			$width = $width * $size / $height;
		}
		return $this->getContent($width, $height);
	}

	/**
	 * returns URL to the original image or to a high quality alternate
	 * e.g. ogg, avi, wmv files that can be handled by the client browser
	 *
	 * @param unknown_type $path
	 */
	function getFullImageURL($path = WEBPATH) {
		// Search for a high quality version of the video
		if ($vid = parent::getFullImageURL($path)) {
			$folder = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($this->album->getFileName());
			$video = stripSuffix($this->filename);
			$curdir = getcwd();
			chdir($folder);
			$candidates = safe_glob($video . '.*');
			chdir($curdir);
			foreach ($candidates as $target) {
				$ext = getSuffix($target);
				if (in_array($ext, $this->videoalt)) {
					$vid = stripSuffix($vid) . '.' . substr(strrchr($target, "."), 1);
				}
			}
		}
		return zp_apply_filter('getLink', $vid, 'full-image.php', NULL);
		return $vid;
	}

	/**
	 * returns the content of the vido
	 *
	 * @param $w
	 * @param $h
	 * @return string
	 */
	function getContent($w = NULL, $h = NULL) {
		global $_zp_multimedia_extension;
		if (is_null($w))
			$w = $this->getWidth();
		if (is_null($h))
			$h = $this->getHeight();
		$ext = getSuffix($this->getFullImageURL());
		switch ($ext) {
			default:
				return $_zp_multimedia_extension->getPlayerConfig($this, NULL, NULL, $w, $h);
				break;
			case '3gp':
			case 'mov':
				return '</a>
					<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="' . $w . '" height="' . $h . '" codebase="http://www.apple.com/qtactivex/qtplugin.cab">
					<param name="src" value="' . pathurlencode($this->getFullImageURL()) . '"/>
					<param name="autoplay" value="false" />
					<param name="type" value="video/quicktime" />
					<param name="controller" value="true" />
					<embed src="' . pathurlencode($this->getFullImageURL()) . '" width="' . $w . '" height="' . $h . '" scale="aspect" autoplay="false" controller"true" type="video/quicktime"
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
		$suffix = getSuffix($this->localpath);
		if (in_array($suffix, array('m4a', 'm4v', 'mp3', 'mp4', 'flv', 'fla', 'mov', '3gp'))) {
			$getID3 = new getID3;
			@set_time_limit(30);
			$ThisFileInfo = $getID3->analyze($this->localpath);
			getid3_lib::CopyTagsToComments($ThisFileInfo);
			// output desired information in whatever format you want
			if (is_array($ThisFileInfo)) {
				return $ThisFileInfo;
			}
		}
		return NULL; // don't try to cover other files even if getid3 reads images as well
	}

	/**
	 * Processes multi-media file metadata
	 * (non-PHPdoc)
	 * @see zp-core/Image::updateMetaData()
	 */
	function updateMetaData() {
		global $_zp_exifvars;
		parent::updateMetaData();
		if (!SAFE_MODE) {
			//see if there are any "enabled" VIDEO fields
			$process = array();
			foreach ($_zp_exifvars as $field => $exifvar) {
				if ($exifvar[EXIF_FIELD_ENABLED] && $exifvar[EXIF_SOURCE] == 'VIDEO') {
					$process[$field] = $exifvar;
				}
			}
			if (!empty($process)) {
				$ThisFileInfo = $this->getMetaDataID3();
				if (is_array($ThisFileInfo)) {
					foreach ($ThisFileInfo as $key => $info) {
						if (is_array($info)) {
							switch ($key) {
								case 'comments':
									foreach ($info as $key1 => $data) {
										$ThisFileInfo[$key1] = array_shift($data);
									}
									break;
								case 'audio':
								case 'video':
									foreach ($info as $key1 => $data) {
										$ThisFileInfo[$key1] = $data;
									}
									break;
								case 'error':
									$msg = sprintf(gettext('getid3 exceptions for %1$s::%2$s'), $this->album->name, $this->filename);
									foreach ($info as $data) {
										$msg .= "\n" . $data;
									}
									debugLog($msg);
									break;
								default:
									//discard, not used
									break;
							}
							unset($ThisFileInfo[$key]);
						}
					}
					foreach ($process as $field => $exifvar) {
						if (isset($ThisFileInfo[$exifvar[1]])) {
							$data = $ThisFileInfo[$exifvar[1]];
							if (!empty($data)) {
								$this->set($field, $data);
								$this->set('hasMetadata', 1);
							}
						}
					}
					$title = $this->get('VideoTitle');
					if (!empty($title)) {
						$this->setTitle($title);
					}
				}
			}
		}
	}

	/**
	 * returns the class of the active multi-media handler
	 * @global pseudoPlayer $_zp_multimedia_extension
	 * @return string
	 *
	 * @author Stephen Billard
	 * @Copyright 2015 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
	 */
	static function multimediaExtension() {
		global $_zp_multimedia_extension;
		return get_class($_zp_multimedia_extension);
	}

}

class pseudoPlayer {

	private $width = 480;
	private $height = 360;

	function getWidth($dummy) {
		return $this->width;
	}

	function getHeight($dummy) {
		return $this->height;
	}

	function getPlayerConfig($moviepath, $imagefilename) {
		return '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/err-noflashplayer.png" alt="' . gettext('No multimeida extension installed.') . '" />';
	}

}

function class_video_enable($enabled) {
	if ($enabled) {
		//establish defaults for display and disable
		$display = $disable = array();
		$exifvars = Video::getMetadataFields();
		foreach ($exifvars as $key => $item) {
			if ($exifvars[$key][EXIF_DISPLAY]) {
				$display[$key] = $key;
			}
			if (!$exifvars[$key][EXIF_FIELD_ENABLED]) {
				$disable[$key] = $key;
			}
		}
		setOption('metadata_disabled', serialize($disable));
		setOption('metadata_displayed', serialize($display));
	}
	requestSetup('Video Metadata');
}

$_zp_multimedia_extension = new pseudoPlayer();
?>
