<?php
/**
 * Support for the Video.JS video player (videojs.com). It will play video natively via HTML5 in capable browsers
 * if the appropiate multimedia formats are provided. It will fall back to flash in older browsers.
 * The player size is responsive to the browser size.

 * Audio: This plugin does not play audio files.<br>
 * Video: <var>.m4v</var>/<var>.mp4</var> - Counterpart formats <var>.ogv</var> and <var>.webm</var> supported (see note below!)
 *
 * IMPORTANT NOTE ON OGG AND WEBM COUNTERPART FORMATS:
 *
 * The counterpart formats are not valid formats for Zenphoto itself as that would confuse the management.
 * Therefore these formats can be uploaded via ftp only.
 * The files needed to have the same file name except extension (beware the character case!).
 *
 * IMPORTANT NOTE ON HD and SD FORMATS:
 *
 * This player is capable of switching between HD and SD video files. To enable this feature the HD files should
 * be uploaded as described above. The SD files should be uploaded to a companion albums folder that has the same path and starts in the same folder
 * as the albums folder, but the root folder must be the same name as the normal albums folder with '.SD' appended to it. For example:
 *
 * HD video files go here: <var>/albums/videos/myvideo.mp4</var><br>
 * SD video files go here: <var>/albums.SD/videos/myvideo.mp4</var>
 *
 * (The counterpart videos must follow the same paths.)
 *
 * <b>NOTE:</b> This player does not support external albums!<br>
 * <b>NOTE:</b> This plugin does not support playlists!
 *
 * @author Jim Brown
 * @pluginCategory media
 * @package plugins/mediaelementjs_player
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Enable <strong>VideoJS</strong> to handle multimedia files.");
$plugin_notice = gettext("<strong>IMPORTANT</strong>: Only one multimedia extension plugin can be enabled at the time and the class-video plugin must be enabled, too.") . '<br /><br />' . gettext("Please see <a href='http://videojs.com'>VideoJS.com</a> for more info about the player and its license.");
$plugin_author = "Jim Brown";

$plugin_disable = zpFunctions::pluginDisable(array(array(!extensionEnabled('class-video'), gettext('This plugin requires the <em>class-video</em> plugin')), array(class_exists('Video') && Video::multimediaExtension() != 'VideoJS' && Video::multimediaExtension() != 'pseudoPlayer', sprintf(gettext('VideoJS not enabled, <a href="#%1$s"><code>%1$s</code></a> is already instantiated.'), class_exists('Video') ? Video::multimediaExtension() : false)), array(getOption('album_folder_class') === 'external', gettext('This player does not support <em>External Albums</em>.'))));

if ($plugin_disable) {
	enableExtension('VideoJS', 0);
} else {
	Gallery::addImageHandler('flv', 'Video');
	Gallery::addImageHandler('fla', 'Video');
	Gallery::addImageHandler('mp3', 'Video');
	Gallery::addImageHandler('mp4', 'Video');
	Gallery::addImageHandler('m4v', 'Video');
	Gallery::addImageHandler('m4a', 'Video');
}

$option_interface = 'VideoJS_options';

class VideoJS_options {

	public $name = 'VideoJS';

	function __construct() {
		setOptionDefault('VideoJS_autoplay', '');
		setOptionDefault('VideoJS_poster', 1);
		setOptionDefault('VideoJS_resolution', 'high');
	}

	function getOptionsSupported() {

		return array(gettext('Poster (Videothumb)') => array('key' => 'VideoJS_poster',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('If the videothumb should be shown (VideoJS calls it poster).')),
				gettext('Autoplay') => array('key' => 'VideoJS_autoplay',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Disabled automatically if several players on one page')),
				gettext('Default Resolution') => array('key' => 'VideoJS_resolution',
						'type' => OPTION_TYPE_SELECTOR,
						'selections' => array(
								gettext('High (HD)') => 'high',
								gettext('Low (SD)') => 'low'),
						'desc' => gettext("Default resolution where multiple resolutions are available"))
		);
	}

}

class VideoJS {

	public $width = '';
	public $height = '';

	function __construct() {
		$this->width = 1280;
		$this->height = 720;
	}

	static function headJS() {
		?>
		<link type="text/css" rel="stylesheet" href="<?php echo  WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/VideoJS/video-js.css"/>
		<link type="text/css" rel="stylesheet" href="<?php echo  WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/VideoJS/videojs-resolution-switcher.css"/>
		<script type="text/javascript" src="<?php echo  WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/VideoJS/ie8/videojs-ie8.min.js"></script>
		<script type="text/javascript" src="<?php echo  WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/VideoJS/video.js"></script>
		<script type="text/javascript" src="<?php echo  WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/VideoJS/videojs-resolution-switcher.js"></script>
		<?php
	}

	/**
	 * Get the JS configuration of VideoJS
	 *
	 * @param mixed $movie the image object
	 * @param string $movietitle the title of the movie
	 *
	 */
	function getPlayerConfig($movie, $movietitle = NULL) {
		global $_zp_current_album;

		$moviepath = $movie->getFullImageURL(FULLWEBPATH);

		$ext = getSuffix($moviepath);
		if (!in_array($ext, array('m4v', 'mp4', 'flv'))) {
			return '<span class="error">' . gettext('This multimedia format is not supported by VideoJS') . '</span>';
		}

		$autoplay = '';
		if (getOption('VideoJS_autoplay')) {
			$autoplay = ' autoplay';
		}

		$videoThumb = '';
		if (getOption('VideoJS_poster')) {
			$videoThumb = $movie->getCustomImage(null, $this->width, $this->height, $this->width, $this->height, null, null, true);
		}

		$videoRes = getOption('VideoJS_resolution');

		$metadata = getImageMetaData(NULL, false);
		$vidWidth = $metadata['VideoResolution_x'];
		$vidHeight = $metadata['VideoResolution_y'];

		$playerconfig = '
      <div id="player" >
        <video id="MyPlayer" class="video-js vjs-default-skin" controls' . $autoplay . ' preload="auto" width=' . $vidWidth . ' poster="' . $videoThumb . '">
          ' . $this->getCounterpartFile($moviepath, "mp4", "HD") . '
          ' . $this->getCounterpartFile($moviepath, "mp4", "SD") . '
          ' . $this->getCounterpartFile($moviepath, "ogv", "HD") . '
          ' . $this->getCounterpartFile($moviepath, "ogv", "SD") . '
          ' . $this->getCounterpartFile($moviepath, "webm", "HD") . '
          ' . $this->getCounterpartFile($moviepath, "webm", "SD") . '
        </video>
      </div>
      <script type="text/javascript">
        videojs("MyPlayer", {
          plugins: {
            videoJsResolutionSwitcher: {
              default: "' . $videoRes . '",
              dynamicLabel: true
            }
          }
        }, function(){
          var player = this;
          window.player = player
          player.on("play", function(){
            player.poster("")
          })
        })
      </script>';
		return $playerconfig;
	}

	/**
	 * outputs the player configuration HTML
	 *
	 * @param mixed $movie the image object if empty (within albums) the current image is used
	 * @param string $movietitle the title of the movie. if empty the Image Title is used
	 * @param string $count unique text for when there are multiple player items on a page
	 */
	function printPlayerConfig($movie = NULL, $movietitle = NULL) {
		global $_zp_current_image;
		if (empty($movie)) {
			$movie = $_zp_current_image;
		}
		echo $this->getPlayerConfig($movie, $movietitle);
	}

	/**
	 * Returns the width of the player
	 * @param object $image the image for which the width is requested
	 *
	 * @return int
	 */
	function getWidth($image = NULL) {
		return $this->width;
	}

	/**
	 * Returns the height of the player
	 * @param object $image the image for which the height is requested
	 *
	 * @return int
	 */
	function getHeight($image = NULL) {
		return $this->height;
	}

	function getCounterpartfile($moviepath, $ext, $definition) {
		$counterpartFile = '';
		$counterpart = str_replace("mp4", $ext, $moviepath);
		$albumPath = substr(ALBUM_FOLDER_WEBPATH, strlen(WEBPATH));
		$vidPath = getAlbumFolder() . str_replace(FULLWEBPATH . $albumPath, "", $counterpart);
		switch (strtoupper($definition)) {
			case "HD":
				if (file_exists($vidPath)) {
					$counterpartFile = '<source src="' . pathurlencode($counterpart) . '" label="HD" />';
				}
				break;
			case "SD":
				$vidPath = str_replace(rtrim(getAlbumFolder(), "/"), rtrim(getAlbumFolder(), "/") . ".SD", $vidPath);
				$counterpart = str_replace(rtrim(ALBUM_FOLDER_WEBPATH, "/"), rtrim(ALBUM_FOLDER_WEBPATH, "/") . ".SD", $counterpart);
				if (file_exists($vidPath)) {
					$counterpartFile = '<source src="' . pathurlencode($counterpart) . '" label="SD" />';
				}
				break;
		}
		return $counterpartFile;
	}

}

$_zp_multimedia_extension = new VideoJS(); // claim to be the flash player.
zp_register_filter('theme_head', 'VideoJS::headJS');
