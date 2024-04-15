<?php

/**
 *
 * This plugin directly handles `mp4`/`mp4` video and `mp3` audio natively in capable browsers
 * 
 * Other formats require a multimedia player to be enabled. The actual supported multimedia types may vary
 * according to the player enabled.
 *
 * @author Stephen Billard (sbillard), Malte Müller (acrylian)
 * @package zpcore\plugins\classvideo
 */
// force UTF-8 Ø

$plugin_is_filter = 990 | CLASS_PLUGIN;
$plugin_description = gettext('The Zenphoto <em>audio-video</em> handler.');
$plugin_notice = gettext('This plugin must always be enabled to use multimedia content. It supports mp4/m4v video and mp3 audio natively in capable browsers. For more support you should also enable a multimedia player. See the info of the player you use to see how it is configured.');
$plugin_author = "Stephen Billard (sbillard), Malte Müller (acrylian)";
$plugin_category = gettext('Media');
$plugin_deprecated = gettext('This plugin will be restructured and moved to core in later versions');

Gallery::addImageHandler('mp4', 'Video');
Gallery::addImageHandler('m4v', 'Video');
Gallery::addImageHandler('m4a', 'Video');
Gallery::addImageHandler('mp3', 'Video');

$option_interface = 'VideoObject_Options';

define('GETID3_INCLUDEPATH', SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/class-video/getid3/');
require_once(dirname(__FILE__) . '/class-video/getid3/getid3.php');

/**
 * Option class for video objects
 *
 */
class VideoObject_Options {

	function __construct() {
		purgeOption('class-video_mov_w');
		purgeOption('class-video_mov_h');
		purgeOption('class-video_3gp_w');
		purgeOption('class-video_3gp_h');
		purgeOption('class-video_videoalt');
		setOptionDefault('video_videoposter', 1);
		setOptionDefault('video_audioposter', 1);
		setOptionDefault('video_videoposter_width', 1280);
		setOptionDefault('video_videoposter_height', 720);
		setOptionDefault('video_audioposter_width', 1280);
		setOptionDefault('video_audioposter_height', 720);
		setOptionDefault('video_videoposter_maxspace', true);
		setOptionDefault('video_audioposter_maxspace', true);
		setOptionDefault('video_videoposter_css', true);
		setOptionDefault('video_audioposter_css', true);
	}

	/**
	 * Standard option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(
				gettext('Video Poster') => array(
						'key' => 'video_videoposter', 
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('If a sidecar image should be used as the poster of the video. .')),
				gettext('Video player width') => array(
						'key' => 'video_videoposter_width', 
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('Max width of the video player (px). Image will be sized automatially in responsive layouts. Might require theme CSS changes to work correctly.')),
				gettext('Video player height') => array(
						'key' => 'video_videoposter_height', 
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('Height of the video player (px). Image will be sized automatially in responsive layouts. Might require theme CSS changes to work correctly.')),
				gettext('Video poster - maxspace') => array(
						'key' => 'video_videoposter_maxspace', 
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('If enabled (default) the image is resized to fit within width and height without being cropped.')),
				gettext('Audio poster') => array(
						'key' => 'video_audioposter', 
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('If a sidecar image should be shown with audio files. You need to set the width/height. .')),
				gettext('Audio poster width') => array(
						'key' => 'video_audioposter_width', 
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('Max width of the audio poster (px). Image will be sized automatially in responsive layouts. Might require theme CSS changes to work correctly.')),
				gettext('Audio poster height') => array(
						'key' => 'video_audioposter_height', 
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('Height of the audio poster (px). Image will be sized automatially in responsive layouts. Might require theme CSS changes to work correctly.')),
				gettext('Audio poster - maxspace') => array(
						'key' => 'video_audioposter_maxspace', 
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('If enabled (default) the image is resized to fit within width and height without being cropped.')),
				gettext('Default CSS') => array(
						'key' => 'video_audioposter_css', 
						'type' => OPTION_TYPE_CHECKBOX_ARRAY,
						'checkboxes' => array(
								gettext('Video player CSS') => 'video_videoposter_css',
								gettext('Audio player CSS') => 'video_audioposter_css',),
						'desc' => gettext('Loads default inline CSS if the theme used has no proper support.')),
				gettext('Watermark default images') => array(
						'key' => 'video_watermark_default_images',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Check to place watermark image on default thumbnail images.')),
		);
	}

}

class Video extends Image {
	
	public $video = false;
	public $videoalt = array();

	/**
	 * Constructor for class-video
	 *
	 * @param object &$album the owning album
	 * @param sting $filename the filename of the image
	 * @return Image
	 */
	function __construct($album, $filename, $quiet = false) {
		global $_zp_supported_images;
		$msg = false;
		if (!is_object($album) || !$album->exists) {
			$msg = gettext('Invalid video instantiation: Album does not exist');
		} else if (!$this->classSetup($album, $filename) || !file_exists($this->localpath) || is_dir($this->localpath)) {
			$msg = gettext('Invalid video instantiation: file does not exist.');
		}
		if ($msg) {
			$this->exists = false;
			if (!$quiet) {
				trigger_error($msg, E_USER_ERROR);
			}
			return;
		}

		$alts = explode(',', strval(getOption('class-video_videoalt'))); //extensionEnabled() must have been a mistake…
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
	 * Update this object's values for width and height.
	 *
	 */
	function updateDimensions() {
		global $_zp_multimedia_extension;
		$ext = getSuffix($this->filename);
		$h = $_zp_multimedia_extension->getHeight($this);
		$w = $_zp_multimedia_extension->getWidth($this);
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
				default: // just in case we extend and are lazy...
					$img = '/multimediaDefault.png';
					break;
			}
			$imgfile = $path . '/' . THEMEFOLDER . '/' . internalToFilesystem($_zp_gallery->getCurrentTheme()) . '/images' . $img;
			if (!file_exists($imgfile)) { // first check if the theme has adefault image
				$imgfile = $path . '/' . THEMEFOLDER . '/' . internalToFilesystem($_zp_gallery->getCurrentTheme()) . '/images/multimediaDefault.png';
				if (!file_exists($imgfile)) { // if theme has a generic default image use it otherwise use the Zenphoto image
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
	function getThumb($type = 'image') {
		$ts = getOption('thumb_size');
		if (getOption('thumb_crop')) {
			$crop = true;
			$sw = getOption('thumb_crop_width');
			$sh = getOption('thumb_crop_height');
			list($custom, $cw, $ch, $cx, $cy) = $this->getThumbCropping($ts, $sw, $sh);
		} else {
			$crop = false;
			$sw = $sh = $cw = $ch = $cx = $cy = null;
		}
		$wmt = getOption('Video_watermark');
		if (empty($wmt)) {
			$wmt = getWatermarkParam($this, WATERMARK_THUMB);
		}
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
		$args = getImageParameters(array($ts, $sw, $sh, $cw, $ch, $cx, $cy, null, true, $crop, true, $wmt, NULL, NULL), $this->album->name);
		return getImageURI($args, $this->album->name, $filename, $mtime);
	}
	
	/**
	 * Returns an array with widht and height the sidecar thumb image
	 * 
	 * @since 1.5.8
	 * 
	 * @return array
	 */
	function getThumbDimensions() {
		global $_zp_graphics;
		if (!is_null($this->thumbdimensions)) {
			return $this->thumbdimensions;
		}
		$imgfile = $this->getThumbImageFile();
		$image = $_zp_graphics->imageGet($imgfile);
		$width = $_zp_graphics->imageWidth($image);
		$height = $_zp_graphics->imageHeight($image);
		return $this->thumbdimensions = array(
				'width' => $width,
				'height' => $height
		);
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
	function getCustomImage($size = null, $width = null, $height = null, $cropw = null, $croph = null, $cropx = null, $cropy = null, $thumbStandin = false, $effects = NULL) {
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
	 * @param string $path the "path" to the image. Defaults to the simple WEBPATH
	 */
	function getFullImageURL($path = WEBPATH) {
		// Search for a high quality version of the video
		if ($vid = parent::getFullImageURL($path)) {
			$folder = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($this->album->getName());
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
		$ext = getSuffix($this->getFullImage());
		return $_zp_multimedia_extension->getPlayerConfig($this, NULL, NULL, $w, $h);
	}

	/**
	 *
	 * "video" metadata support function
	 */
	private function getMetaDataID3() {
		$suffix = getSuffix($this->localpath);
		if (in_array($suffix, array('m4a', 'm4v', 'mp3', 'mp4'))) {
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
		//see if there are any "enabled" VIDEO fields
		$process = array();
		foreach ($_zp_exifvars as $field => $exifvar) {
			if ($exifvar[5] && $exifvar[0] == 'VIDEO') {
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

class pseudoPlayer {

	public $name = '';
	private $width = 1280;
	private $height = 720;


	function getWidth($dummy) {
		return $this->width;
	}

	function getHeight($dummy) {
		return $this->height;
	}
	
	/**
	 * Gets the poster width
	 * 
	 * @param object $obj
	 * @return int
	 */
	function getPosterWidth($obj) {
		$suffix = getSuffix($obj->getFullImage(FULLWEBPATH));
		switch ($suffix) {
			case 'mp4':
			case 'm4v':
				$width = getOption('video_videoposter_width');
				break;
			case 'm4a':
			case 'mp3':
				$width = getOption('video_audioposter_width');
				break;
		}
		if (empty($width)) {
			$width = $this->getWidth();
		}
		return $width;
	}
	
	/**
	 * Gets the poster height
	 * 
	 * @sicne 1.6.1
	 * 
	 * @param object $obj
	 * @return int
	 */
	function getPosterHeight($obj) {
		$suffix = getSuffix($obj->getFullImage(FULLWEBPATH));
		switch ($suffix) {
			case 'mp4':
			case 'm4v':
				$height = getOption('video_videoposter_height');
				break;
			case 'm4a':
			case 'mp3':
				$height = getOption('video_audioposter_height');
				break;
		}
		if (empty($height)) {
			$height = $this->getHeight();
		}
		return $height;
	}
	
	/**
	 * Gets an array with the poster data
	 * 
	 * @since 1.6.1
	 * 
	 * @param object $obj
	 * @param int $width Default null
	 * @param int $height Default null
	 * @return array
	 */
	function getPoster($obj, $width = null, $height = null) {
		$poster = array(
				'width' => $width,
				'height' => $height,
				'cropwidth' => null,
				'cropheight' => null,
				'url' => ''
		);
		if (getOption('video_videoposter') || getOption('video_audioposter')) {
			$suffix = getSuffix($obj->getFullImage(FULLWEBPATH));
			if (empty($poster['width'])) {
				$poster['width'] = $this->getPosterWidth($obj);
			}
			if (empty($poster['height'])) {
				$poster['height'] = $this->getPosterHeight($obj);
			}
			$poster['cropwidth'] = $poster['width'];
			$poster['cropheight'] = $poster['height'];
			switch ($suffix) {
				default:
				case 'mp4':
				case 'm4v':
					if (getOption('video_videoposter')) {
						if (getOption('video_videoposter_maxspace')) {
							getMaxSpaceContainer($poster['width'], $poster['height'], $obj, true);
							$poster['cropwidth'] = null;
							$poster['cropheight'] = null;
						}
						$poster['url'] = $obj->getCustomImage($poster['width'], $poster['width'], $poster['height'], $poster['cropwidth'], $poster['cropheight'], null, null, true);
					}
					break;
				case 'm4a':
				case 'mp3':
					if (getOption('video_audioposter')) {
						if (getOption('video_audioposter_maxspace')) {
							getMaxSpaceContainer($poster['width'], $poster['height'], $obj, true);
							$poster['cropwidth'] = null;
							$poster['cropheight'] = null;
						}
						$poster['url'] = $obj->getCustomImage($poster['width'], $poster['width'], $poster['height'], $poster['cropwidth'], $poster['cropheight'], null, null, true);
					}
					break;
			}
		}
		return $poster;
	}

	function getPlayerConfig($obj, $movietitle = NULL, $count = NULL) {
		$movie = $obj->getFullImage(FULLWEBPATH);
		$suffix = getSuffix($movie);
		$poster = '';
		$content = '';
		$posterdata = $this->getPoster($obj);
		$container_width = $this->getPosterWidth($obj);
		$container_height = $this->getPosterHeight($obj);
		switch ($suffix) {
			case 'mp4':
			case 'm4v':
				if (!empty($posterdata['url'])) {
					$poster = ' poster="' . html_encode($posterdata['url']) . '"';
				}
				$style = '';
				if (getOption('video_videoposter_css')) {
					$aspectratio = Image::calculateAspectRatio($container_width, $container_height, '/');
					$style = ' style="max-width: 100%; height: auto; aspect-ratio: ' . $aspectratio . ';"';
				}
				$content = '<video class="video_videoplayer"' . $poster . ' src="' . html_encode($movie) . '" controls width="' . $container_width . '" height="' . $container_height . '"' . $style . '>';
				$content .= gettext('Your browser sadly does not support this video format.');
				$content .= '</video>';
				break;
			case 'm4a':
			case 'mp3':
				if (!empty($posterdata['url'])) {
					if (!getOption('video_audioposter_maxspace')) { // here use the real image sizes!
						$container_width = $posterdata['width'];
						$container_height = $posterdata['height'];
					}
					$style = $style_player = '';
					if (getOption('video_audioposter_css')) {
						$aspectratio = Image::calculateAspectRatio($container_width, $container_height, '/');
						$style = ' style="max-width: 100%; height: auto; object-fit: contain !important; aspect-ratio: ' . $aspectratio . ';"';
						$style_player = ' style="width: 100%; max-width: ' . $container_width . 'px;"';
					}
					$content = '<img class="video_audioposter" src="' . html_encode($posterdata['url']) . '" width="' . $container_width . '" height="' . $container_height . '"' . $style . ' alt="">' . "\n";
				}
				$content .= '<audio class="video_audioplayer" src="' . html_encode($movie) . '" controls' . $style_player . '>';
				$content .= gettext('Your browser sadly does not support this audio format.');
				$content .= '</audio>';
				break;
		}
		if (empty($content)) {
			return '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images_errors/err-noflashplayer.png" alt="' . gettext('No multimedia extension installed for this format.') . '" />';
		}
		return $content;
	}

}

$_zp_multimedia_extension = new pseudoPlayer();
