<?php
/**
 * Support for the MediaElement.js video and audio player by John Dyer (http://mediaelementjs.com). It will play natively via HTML5 in capable browsers.
 * 
 * Audio: <var>.mp3</var>, <var>.m4a</var>, <var>.fla</var> - Counterpart formats <var>.oga</var> and <var>.webma</var> supported (see note below!)<br>
 * Video: <var>.m4v</var>/<var>.mp4</var>, <var>.flv</var> - Counterpart formats <var>.ogv</var> and <var>.webmv</var> supported (see note below!)
 *
 * IMPORTANT NOTE ON OGG AND WEBM COUNTERPART FORMATS:
 *
 * The counterpart formats are not valid formats for Zenphoto itself as that would confuse the management.
 * Therefore these formats can be uploaded via FTP only.
 * The files needed to have the same file name (beware the character case!). In single player usage the player
 * will check via file system if a counterpart file exists if counterpart support is enabled.
 *
 * Since the flash fallback covers all essential formats ths is not much of an issue for visitors though.
 *
 * Subtitle and chapter support for videos (NOTE: NOT IMPLEMENTED YET!):
 * It supports .srt files. To differ what is what they must follow this naming convention:
 * subtitles file: <nameofyourvideo>_subtitles.srt
 * chapters file: <name of your video>_chapters.srt
 * 
 * Example: yourvideo.mp4 with yourvideo_subtitles.srt and yourvideo_chapters.srt
 *
 * Note: like the counterpart formats these MUST be uploaded via FTP!
 *
 * <b>NOTE:</b> This player does not support external albums! Also it does not have playlist capability.
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage media
 */

$plugin_description = gettext("Enable <strong>mediaelement.js</strong> to handle multimedia files.");
$plugin_notice = gettext("<strong>IMPORTANT</strong>: Only one multimedia player plugin can be enabled at the time and the class-video plugin must be enabled, too.").'<br /><br />'.gettext("Please see <a href='http://http://mediaelementjs.com'>mediaelementjs.com</a> for more info about the player and its license.");
$plugin_author = "Malte Müller (acrylian)";
$plugin_disable = (getOption('album_folder_class') === 'external')?gettext('This player does not support <em>External Albums</em>.'):false;

$option_interface = 'mediaelementjs_options';

if (isset($_zp_flash_player) || $plugin_disable) {
	setOption('zp_plugin_jplayer',0);
	if (isset($_zp_flash_player)) {
		trigger_error(sprintf(gettext('mediaelement.js not enabled, %s is already instantiated.'),get_class($_zp_flash_player)),E_USER_NOTICE);
	}
} else {
	$_zp_flash_player = new medialementjs_player(); // claim to be the flash player.
	zp_register_filter('theme_head','mediaelementjs_js');
}


function mediaelementjs_js() {
	/* 
	$skin = getOption('mediaelementjs_skin');
	if(file_exists($skin)) {
		$skin = str_replace(SERVERPATH,WEBPATH,$skin); //replace SERVERPATH as that does not work as a CSS link
	} else {
		$skin = WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/mediaelementjs_player/mediaelementplayer.css';
	} 
	*/
	$skin = WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/mediaelementjs_player/mediaelementplayer.css';
	?>
	<link href="<?php echo $skin; ?>" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="<?php echo WEBPATH .'/'.ZENFOLDER.'/'.PLUGIN_FOLDER; ?>/mediaelementjs_player/mediaelement-and-player.min.js"></script>
	<script>
		$(document).ready(function(){
			$('video,audio').mediaelementplayer();
		});
	</script>
	
	<?php
}

class mediaelementjs_options {

	function mediaelementjs_options() {
		setOptionDefault('mediaelementjs_playpause', 1);
		setOptionDefault('mediaelementjs_progress', 1);
		setOptionDefault('mediaelementjs_current', 1);
		setOptionDefault('mediaelementjs_duration', 1);
		setOptionDefault('mediaelementjs_tracks', 0);
		setOptionDefault('mediaelementjs_volume', 1);
		setOptionDefault('mediaelementjs_fullscreen', 1);
		setOptionDefault('mediaelementjs_videowidth', 470);
		setOptionDefault('mediaelementjs_videoheight', 270);
		setOptionDefault('mediaelementjs_audiowidth', 400);
		setOptionDefault('mediaelementjs_audioheight', 30);
		setOptionDefault('mediaelementjs_preload', 0);
		setOptionDefault('mediaelementjs_poster', 1);
	}

	function getOptionsSupported() {
		//$skins = getMediaelementjsSkins();
 		return array(
 			gettext('Control bar') => array(
				'key' => 'mediaelementjs_controlbar',
				'type' => OPTION_TYPE_CHECKBOX_UL,
				'order' => 0,
				'checkboxes' => array( // The definition of the checkboxes
					gettext('Play/Pause')=>'mediaelementjs_playpause',
					gettext('Progress')=>'mediaelementjs_progress',
					gettext('Current')=>'mediaelementjs_current',
					gettext('Duration')=>'mediaelementjs_duration',
					gettext('Tracks (Video only)')=>'medialementjs_tracks',
					gettext('Volume')=>'mediaelementjs_volume',
					gettext('Fullscreen')=>'mediaelementjs_fullscreen'
				),
				'desc' => gettext('Enable what should be shown in the player control bar.')),
			gettext('Video width') => array(
				'key' => 'mediaelementjs_videowidth', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>5,
				'desc' => gettext('Pixel value or percent for responsive layouts')),
			gettext('Video height') => array(
				'key' => 'mediaelementjs_videoheight', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>5,
				'desc' => gettext('Pixel value or percent for responsive layouts')),
			gettext('Audio width') => array(
				'key' => 'mediaelementjs_audiowidth', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>5,
				'desc' => gettext('Pixel value or percent for responsive layouts')),
			gettext('Audio height') => array(
				'key' => 'mediaelementjs_audioheight', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>5,
				'desc' => gettext('Pixel value or percent for responsive layouts')),
			gettext('Preload') => array(
				'key' => 'mediaelementjs_preload', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>5,
				'desc' => gettext('If the files should be preloaded.')),
			gettext('Poster') => array(
				'key' => 'mediaelementjs_poster', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>5,
				'desc' => gettext('If a poster of the videothumb should be shown. This is cropped to fit the player size (videos only).'))
		);
	}
}
/** NOT USED YET
 * Gets the skin names and css files
 *
 */
function getMediaelementjsSkins() {
	$all_skins = array();
	$default_skins_dir = WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/mediaelementjs_player/';
	$user_skins_dir = SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/';
	$filestoignore = array( '.', '..','.DS_Store','Thumbs.db','.htaccess','.svn');
	$skins = array_diff(scandir($default_skins_dir),array_merge($filestoignore));
	$default_skins = getMediaelementjsSkinCSS($skins,$default_skins_dir);
	//echo "<pre>";print_r($default_skins);echo "</pre>";
	$skins2 = @array_diff(scandir($user_skins_dir),array_merge($filestoignore));
	if(is_array($skins2)) {
		$user_skins = getMediaelementjsSkinCSS($skins2,$user_skins_dir);
		//echo "<pre>";print_r($user_skins);echo "</pre>";
		$default_skins = array_merge($default_skins,$user_skins);
	}
	return $default_skins;
}
/** NOT USED YET
 * Gets the css files for a skin. Helper function for getMediaelementjsSkins().  
 *
 */
function getMediaelementjsSkinCSS($skins,$dir) {
	$skin_css = array();
	foreach($skins as $skin) {
		$css = safe_glob($dir.'/'.$skin.'/*.css');
		if($css) {
			$skin_css = array_merge($skin_css,array($skin => $css[0]));	// a skin should only have one css file so we just use the first found
		}
	}
	return $skin_css;
}


class medialementjs_player {
	public $width = '';
	public $height = '';
	public $mode = '';
	
	function __construct() {
		
	}

	/**
	 * Get the JS configuration of jplayer
	 *
	 * @param string $moviepath the direct path of a movie
	 * @param string $imagefilename the filename of the movie
	 * @param string $count number (preferredly the id) of the item to append to the css for multiple players on one page
	 * @param string $width Not supported as jPlayer is dependend on its CSS based skin to change sizes. Can only be set via plugin options.
	 * @param string $height Not supported as jPlayer is dependend on its CSS based skin to change sizes. Can only be set via plugin options.
	 *
	 */
	function getPlayerConfig($moviepath, $imagefilename, $count='', $width='', $height='') {
		global $_zp_current_album, $_zp_current_image;
		$ext = getSuffix($moviepath);
		if(!in_array($ext,array('m4a','m4v','mp3','mp4','flv', 'fla'))) {
			echo '<p>'.gettext('This multimedia format is not supported by mediaelement.js.').'</p>';
			return NULL;
		}
		switch($ext) {
			case 'm4a':
			case 'mp3':
			case 'fla':
				$this->mode = 'audio';
			  break;
			case 'mp4':
			case 'm4v':
			case 'flv':
				$this->mode = 'video';
			  break;
		}
		if(!empty($width)) {
			$this->width = $this->getVideoWidth();
		} else {
			$this->width = $width;
		}
		if(!empty($height)) {
			$this->height = $this->getVideoHeight();
		} else {
			$this->height = $height;
		}
		if(empty($count)) {
			$multiplayer = false;
			$count = '1';
		}	else {
			$multiplayer = true; // since we need extra JS if multiple players on one page
			$count = $count;
		}
		$playerconfig  = '';
		if(getOption('mediaelementjs_preload')) {
			$preload = ' preload="preload"';
		} else {
			$preload = ' preload="none"';
		}
		$counterparts = $this->getCounterpartFiles($moviepath,$ext);
		switch($this->mode) {
			case 'audio':
				$playerconfig  = '
					<audio id="mediaelementjsplayer'.$count.'" width="'.$this->width.'" height="'.$this->height.'" controls="controls"'.$preload.'>
    				<source type="audio/mp3" src="'.pathurlencode($moviepath).'" />';
    			if(count($counterparts) != 0) {
    				foreach($counterparts as $counterpart) {
    					$playerconfig .= $counterpart;
    				}
    			} 
    	  	$playerconfig  .= '		
    				<object width="'.$this->width.'" height="'.$this->height.'" type="application/x-shockwave-flash" data="'.SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/mediaelementjs_player/flashmediaelement.swf">
        			<param name="movie" value="'.SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/mediaelementjs_player/flashmediaelement.swf" />
        			<param name="flashvars" value="controls=true&file='.pathurlencode($moviepath).'" />
        			<p>'.gettext('Sorry, no playback capabilities.').'</p>
    				</object>
					</audio>
				'; 
				break;
			case 'video':
				$poster = '';
				if(getOption('mediaelementjs_poster')) {
					if(is_null($_zp_current_image)) {
						$poster = '';
					} else {
						$poster = ' poster="'.$_zp_current_image->getCustomImage(null, $this->width, $this->height, $this->width, $this->height, null, null, true).'"';
					}
				} 
				$playerconfig  = '
					<video id="mediaelementjsplayer'.$count.'" width="'.$this->width.'" height="'.$this->height.'" controls="controls"'.$preload.$poster.'>
    				<source type="video/mp4" src="'.pathurlencode($moviepath).'" />';
    		if(count($counterparts) != 0) {
    				foreach($counterparts as $counterpart) {
    					$playerconfig .= $counterpart;
    				}
    			}		
				$playerconfig  .= '		
    				<!-- <track kind="subtitles" src="subtitles.srt" srclang="en" /> -->
    				<!-- <track kind="chapters" src="chapters.srt" srclang="en" /> -->
    				<object width="'.$this->width.'" height="'.$this->height.'" type="application/x-shockwave-flash" data="'.SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/mediaelementjs_player/flashmediaelement.swf">
        			<param name="movie" value="'.SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/mediaelementjs_player/flashmediaelement.swf" />
        			<param name="flashvars" value="controls=true&file='.pathurlencode($moviepath).'" />
        			<p>'.gettext('Sorry, no playback capabilities.').'</p>
    				</object>
					</video>
				'; 
				break;
		} 
		return $playerconfig; 
	}

	/**
	 * outputs the player configuration HTML
	 *
	 * @param string $moviepath the direct path of a movie (within the slideshow), if empty (within albums) the current image is used
	 * @param string $imagefilename the filename of the movie. if empty (within albums) the function getImageTitle() is used
	 * @param string $count unique text for when there are multiple player items on a page
	 */
	function printPlayerConfig($moviepath='',$imagefilename='',$count ='') {
		echo $this->getPlayerConfig($moviepath,$imagefilename,$count);
	}


	/** 
	 * Gets the counterpart formats (webm,ogg) for html5 browser compatibilty
	 * NOTE: THese formats need to be uploaded via FTP as they are not valid file types for Zenphoto to avoid confusion
	 *
	 * @param string $moviepath full link to the multimedia file to get counterpart formats to.
	 * @param string $ext the file format extention to search the counterpart for (as we already have fetched that)
	 */
	function getCounterpartFiles($moviepath,$ext) {
		$counterparts = array();
		switch($this->mode) {
			case 'audio':
				$suffixes = array('oga','webma');
				break;
			case 'video':
				$suffixes = array('ogv','webmv');
				break;
		}
		foreach($suffixes as $suffix) {
			$counterpart = str_replace($ext,$suffix,$moviepath,$count);
			if(file_exists(str_replace(FULLWEBPATH,SERVERPATH,$counterpart))) {
				switch($suffix) {
					case 'oga':
						$type = 'audio/ogg';
						break;
					case 'webma':
						$type = 'audio/webm';
						break;
					case 'ogv':
						$type = 'video/ogg';
						break;
					case 'webmv':
						$type = 'video/webm';
						break;
				}
				$source = '<source type="'.$type.'" src="'.pathurlencode($counterpart).'" />';
				array_push($counterparts,$source);
			}
		}
		return $counterparts;
	}
	
	/**
	 * Returns the height of the player
	 * @param object $image the image for which the width is requested (not used)
	 *
	 * @return mixed
	 */
	function getVideoWidth($image=NULL) {
		switch($this->mode) {
			case 'audio':
				$width = getOption('mediaelementjs_audiowidth');
				if(empty($width)) {
					return '100%';
				} else {
					return $width;
				}
				break;
			case 'video': 
				$width = getOption('mediaelementjs_videowidth');
				if(empty($width)) {
					return '100%';
				} else {
					return $width;
				}
				break;
		}
	}

	/**
	 * Returns the width of the player
	 * @param object $image the image for which the height is requested (not used!)
	 *
	 * @return mixed
	 */
	function getVideoHeight($image=NULL) {
		switch($this->mode) {
			case 'audio':
				$height = getOption('mediaelementjs_audioheight');
				if(empty($height)) {
					return '30';
				} else {
					return $height;
				}
				break;
			case 'video':
				$height = getOption('mediaelementjs_videoheight');
				if(empty($height)) {
					return 'auto';
				} else {
					return $height;
				}
				break;
		}
	}
	
} // mediaelementjs class