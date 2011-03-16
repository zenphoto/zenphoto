<?php
/**
 * flowplayer -- plugin support for the flowplayer 3.x.x flash video player.
 * NOTE: Flash players do not support external albums!
 *
 * Note on splash images: Flowplayer will try to use the first frame of a movie as a splash image or a videothumb if existing.
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 */


$plugin_description = gettext("Enable <strong>flowplayer 3</strong> to handle multimedia files.").'<p class="notebox">'.gettext("<strong>IMPORTANT</strong>: Only one multimedia player plugin can be enabled at the time and the class-video plugin must be enabled, too.").'</p>'.gettext("Please see <a href='http://flowplayer.org'>flowplayer.org</a> for more info about the player and its license.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$plugin_version = '1.4.1';
$plugin_URL = "http://www.zenphoto.org/documentation/plugins/_".PLUGIN_FOLDER."---flowplayer3.php.html";
$plugin_disable = (getOption('album_folder_class') === 'external')?gettext('Flash players do not support <em>External Albums</em>.'):false;

if ($plugin_disable) {
	setOption('zp_plugin_flowplayer3',0);
} else {
	global $_zp_flash_player;
	$option_interface = 'flowplayer3_options';
	$_zp_flash_player = new flowplayer3(); // claim to be the flash player.
	zp_register_filter('theme_head','flowplayer2JS');
}
function flowplayer2JS() {
	$curdir = getcwd();
	chdir(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/flowplayer3');
	$filelist = safe_glob('flowplayer-*.min.js');
	$player = array_shift($filelist);
	$filelist = safe_glob('flowplayer.playlist-*.min.js');
	$playlist = array_shift($filelist);
	chdir($curdir);
	?>
	<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/'.PLUGIN_FOLDER; ?>/flowplayer3/<?php echo $player; ?>"></script>
	<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/'.PLUGIN_FOLDER; ?>/flowplayer3/<?php echo $playlist; ?>"></script>
	<?php
}

if (!defined('FLOW_PLAYER_MP3_HEIGHT')) define ('FLOW_PLAYER_MP3_HEIGHT', 26);
/**
 * Plugin option handling class
 *
 */
class flowplayer3_options {

	function flowplayer3_options() {
		setOptionDefault('flow_player3_width', '320');
		setOptionDefault('flow_player3_height', '240');
		setOptionDefault('flow_player3_controlsbackgroundcolor', '#110e0e');
		setOptionDefault('flow_player3_controlsbackgroundcolorgradient', 'none');
		setOptionDefault('flow_player3_controlsbordercolor', '#000000');
		setOptionDefault('flow_player3_autoplay', '');
		setOptionDefault('flow_player3_backgroundcolor', '#000000');
		setOptionDefault('flow_player3_backgroundcolorgradient', 'none');
		setOptionDefault('flow_player3_controlsautohide', 'never');
		setOptionDefault('flow_player3_controlstimecolor', '#fcfcfc');
		setOptionDefault('flow_player3_controlsdurationcolor', '#ffffff');
		setOptionDefault('flow_player3_controlsprogresscolor', '#ffffff');
		setOptionDefault('flow_player3_controlsprogressgradient', 'low');
		setOptionDefault('flow_player3_controlsbuffercolor', '#275577');
		setOptionDefault('flow_player3_controlsbuffergradient', 'low');
		setOptionDefault('flow_player3_controlsslidercolor', '#ffffff');
		setOptionDefault('flow_player3_controlsslidergradient', 'low');
		setOptionDefault('flow_player3_controlsbuttoncolor', '#567890');
		setOptionDefault('flow_player3_controlsbuttonovercolor', '#999999');
		setOptionDefault('flow_player3_splashimagescale', 'fit');
		setOptionDefault('flow_player3_scaling', 'fit');
		setOptionDefault('flow_player3_mp3coverimage', '');
	}


	function getOptionsSupported() {
		return array(	gettext('flow player width') => array('key' => 'flow_player3_width', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Player width (ignored for <em>mp3</em> files.)")),
		gettext('flow player height') => array('key' => 'flow_player3_height', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Player height (ignored for <em>mp3</em> files.)")),
		gettext('Player background color gradient') => array('key' => 'flow_player3_backgroundcolorgradient', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('none')=>"none",gettext('low')=>"low", gettext('medium')=>"medium", gettext('high')=>"high"),
										'desc' => gettext("Gradient setting for player background color.")),
		gettext('Controls background color') => array('key' => 'flow_player3_controlsbackgroundcolor', 'type' => OPTION_TYPE_COLOR_PICKER,
										'desc' => gettext("Background color of the controls.")),
		gettext('Controls background color gradient') => array('key' => 'flow_player3_controlsbackgroundcolorgradient', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('none')=>"none",gettext('low')=>"low", gettext('medium')=>"medium", gettext('high')=>"high"),
										'desc' => gettext("Gradient setting for background color of the controls.")),
		gettext('Controls border color') => array('key' => 'flow_player3_controlsbordercolor', 'type' => OPTION_TYPE_COLOR_PICKER,
										'desc' => gettext("Color of the border of the player controls")),
		gettext('Autoplay') => array('key' => 'flow_player3_autoplay', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("Should the video start automatically. Yes if selected. (NOTE: Probably because of a flowplayer bug mp3s are always autoplayed.)")),
		gettext('Background color') => array('key' => 'flow_player3_backgroundcolor', 'type' => OPTION_TYPE_COLOR_PICKER,
										'desc' => gettext("Changes the color of the Flowplayer's background canvas. By default the canvas is all black. You can specify a value of -1 and the background will not be drawn (only the video will be visible).")),
		gettext('Controls autohide') => array('key' => 'flow_player3_controlsautohide', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('never')=>"never", gettext('always')=>"always", gettext('fullscreen')=>"fullscreen"),
										'desc' => gettext("Specifies whether the controlbar should be hidden when the user is not actively using the player.")),
		gettext('Controls time color') => array('key' => 'flow_player3_controlstimecolor', 'type' => OPTION_TYPE_COLOR_PICKER,
										'desc' => gettext("Value for the font color in the time field. This is the running time.")),
		gettext('Controls duration color') => array('key' => 'flow_player3_controlsdurationcolor', 'type' => OPTION_TYPE_COLOR_PICKER,
										'desc' => gettext("Value for the font color in the time field that specifies the total duration of the clip or total time.")),
		gettext('Controls progress bar color') => array('key' => 'flow_player3_progresscolor', 'type' => OPTION_TYPE_COLOR_PICKER,
										'desc' => gettext("Color of the progress bar. This is the bar in the timeline from zero time to the point where playback is at a given time.")),
		gettext('Controls progress bar gradient') => array('key' => 'flow_player3_progressgradient', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('none')=>"none",gettext('low')=>"low", gettext('medium')=>"medium", gettext('high')=>"high"),
										'desc' => gettext("Gradient setting for the progress bar.")),
		gettext('Controls buffer color') => array('key' => 'flow_player3_controlsbuffercolor', 'type' => OPTION_TYPE_COLOR_PICKER,
										'desc' => gettext("Color of the buffer. The buffer is the bar that indicates how much video data has been read into the player's memory.")),
		gettext('Controls buffer gradient') => array('key' => 'flow_player3_controlsbuffergradient', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('none')=>"none",gettext('low')=>"low", gettext('medium')=>"medium", gettext('high')=>"high"),
										'desc' => gettext("Gradient setting for the buffer.")),
		gettext('Controls slider color') => array('key' => 'flow_player3_controlsslidercolor', 'type' => OPTION_TYPE_COLOR_PICKER,
										'desc' => gettext("Background color for the timeline before the buffer bar fills it. The same background color is also used in the volume slider.")),
		gettext('Controls slider gradient') => array('key' => 'flow_player3_controlsslidergradient', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('none')=>"none",gettext('low')=>"low", gettext('medium')=>"medium", gettext('high')=>"high"),
										'desc' => gettext("Gradient setting for the sliders.")),
		gettext('Controls button color') => array('key' => 'flow_player3_controlsbuttoncolor', 'type' => OPTION_TYPE_COLOR_PICKER,
										'desc' => gettext("Color of the player buttons: stop, play, pause and full screen.")),
		gettext('Controls hover button color') => array('key' => 'flow_player3_controlsbuttonovercolor', 'type' => OPTION_TYPE_COLOR_PICKER,
										'desc' => gettext("Button color when the mouse is positioned over them.")),
		gettext('Splash image') => array('key' => 'flow_player3_splashimage', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("Enable if you want to show the videothumb as an splash image before the actual video starts (don't set the the player to autoPlay then). If you want to use this for mp3s as well set also the mp3 cover image option.").gettext("The image is always scaled to fit (unless it is smaller than the width and height) so best make sure it matches at least the aspect ration and size of your player width and height.")),
		gettext('Video scale') => array('key' => 'flow_player3_scaling', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('fit')=>"fit",gettext('half')=>"half", gettext('orig')=>"orig", gettext('scale')=>"scale"),
										'desc' => gettext("Setting which defines how video is scaled on the video screen. Available options are:<br /><em>fit</em>: Fit to window by preserving the aspect ratio encoded in the file's metadata.<br /><em>half</em>: Half-size (preserves aspect ratio)<br /><em>orig</em>: Use the dimensions encoded in the file. If the video is too big for the available space, the video is scaled using the 'fit' option.<br /><em>scale</em>: Scale the video to fill all available space. This is the default setting.")),
		gettext('MP3 cover image') => array('key' => 'flow_player3_mp3coverimage', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("If unchecked only the controlbar is shown for mp3s but if checked the video thumb is displayed as a cover image on the player screen.").gettext("The image is always scaled to fit (unless it is smaller than the width and height) so best make sure it matches at least the aspect ration and size of your player width and height."))

		);
	}
}

class flowplayer3 {

	/**
	 * Print the JS configuration of flowplayer
	 *
	 * @param string $moviepath the direct path of a movie (within the slideshow), if empty (within albums)
	 * the zenphoto function getUnprotectedImageURL() is used
	 *
	 * @param string $imagetitle the title of the movie
	 * 	 */
	function getPlayerConfig($moviepath='', $imagetitle, $count='', $width, $height) {
		global $_zp_current_image;
		$playerwidth = getOption('flow_player3_width');
		$playerheight = getOption('flow_player3_height');
		if(empty($moviepath)) {
			$moviepath = getUnprotectedImageURL();
			$ext = strtolower(strrchr(getUnprotectedImageURL(), "."));
		} else {
			$moviepath = $moviepath;
			$ext = strtolower(strrchr($moviepath, "."));
		}
		if(!empty($count)) {
			$count = "-".$count;
		}
		$imgextensions = array(".jpg",".jpeg",".gif",".png");
		$videoThumbImg = '';
		if(is_null($_zp_current_image)) {
			$albumfolder = $moviepath;
			$filename = $imagetitle;
			$videoThumb = '';
		} else {
			$album = $_zp_current_image->getAlbum();
			$albumfolder = $album->name;
			$filename = $_zp_current_image->filename;
			$splashimagerwidth = $playerwidth;
			$splashimageheight = $playerheight;
			getMaxSpaceContainer($splashimagerwidth, $splashimageheight, $_zp_current_image, true);
			$videoThumb = $_zp_current_image->getCustomImage(null, $splashimagerwidth, $splashimageheight, null, null, null, null, true);
			if(getOption('flow_player3_splashimage')) {
				$videoThumbImg = '<img src="'.pathurlencode($videoThumb).'" alt="" />';
			}
		}
		if(getOption("flow_player3_autoplay") == 1) {
			$autoplay = "true";
		} else {
			$autoplay = "false";
		}
		if($ext == ".mp3") {
			if(getOption('flow_player3_mp3coverimage')) {	
				if (is_null($height)) $height = $playerheight;
			} else {
				if (is_null($height)) $height = FLOW_PLAYER_MP3_HEIGHT;
				$videoThumbImg = '';
				$videoThumb = '';
			}
			$allowfullscreen = 'false';
		} else {
			if (is_null($height)) $height = $playerheight;
			$allowfullscreen = 'true';
		} 
		if (is_null($width)) $width = $this->getVideoWidth();
		if (is_null($width)) $width = $playerwidth;
			// inline css is kind of ugly but since we need to style dynamically there is no other way
		$curdir = getcwd();
		chdir(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/flowplayer3');
		$filelist = safe_glob('flowplayer-*.swf');
		$swf = array_shift($filelist);
		$filelist = safe_glob('flowplayer.audio-*.swf');
		$audio = array_shift($filelist);
		$filelist = safe_glob('flowplayer.controls-*.swf');
		$controls = array_shift($filelist);
		chdir($curdir);
		$playerconfig = '
		<span id="player'.$count.'" class="flowplayer" style="display:block; width: '.$width.'px; height: '.$height.'px">
		'.$videoThumbImg.'
		</span>
		<script type="text/javascript">
		// <!-- <![CDATA[
		flowplayer("player'.$count.'","'.WEBPATH . '/' . ZENFOLDER . '/'.PLUGIN_FOLDER . '/flowplayer3/'.$swf.'", {
		plugins: {
			audio: {
				url: "'.$audio.'"
			},
			controls: {
				url: "'.$controls.'",
				backgroundColor: "'.getOption('flow_player3_controlsbackgroundcolor').'",
				backgroundGradient: "'.getOption('flow_player3_controlsbackgroundcolorgradient').'",
				autoHide: "'.getOption('flow_player3_controlsautohide').'",
				timeColor:"'.getOption('flow_player3_controlstimecolor').'",
				durationColor: "'.getOption('flow_player3_controlsdurationcolor').'",
				progressColor: "'.getOption('flow_player3_controlsprogresscolor').'",
				progressGradient: "'.getOption('flow_player3_controlsprogressgradient').'",
				bufferColor: "'.getOption('flow_player3_controlsbuffercolor').'",
				bufferGradient:	 "'.getOption('flow_player3_controlsbuffergradient').'",
				sliderColor: "'.getOption('flow_player3_controlsslidercolor').'",
				sliderGradient: "'.getOption('flow_player3_controlsslidergradient').'",
				buttonColor: "'.getOption('flow_player3_controlsbuttoncolor').'",
				buttonOverColor: "'.getOption('flow_player3_controlsbuttonovercolor').'",
				fullscreen : '.$allowfullscreen.'
			}
		},
		canvas: {
			backgroundColor: "'.getOption('flow_player3_backgroundcolor').'",
			backgroundGradient: "'.getOption('flow_player3_backgroundcolorgradient').'"
		},';
			$playerconfigadd = 'clip:
			{
				url:"' . pathurlencode($moviepath) . '",
				autoPlay: '.$autoplay.',
				autoBuffering: '.$autoplay.',
				scaling: "'.getOption('flow_player3_scaling').'"';
			if($ext == ".mp3" && getOption('flow_player3_mp3coverimage')) {
				$playerconfigadd .= ',
				coverImage: {
					url:"'.urlencode($videoThumb).'",
					scaling: "'.getOption('flow_player3_scaling').'"
				}
				';
			}
			$playerconfigadd .= '
			}
		});
		// ]]> -->
		</script>';
		$playerconfig = $playerconfig.$playerconfigadd;
		return $playerconfig;
	}

	/**
	 * outputs the player configuration HTML
	 *
	 * @param string $moviepath the direct path of a movie (within the slideshow), if empty (within albums) the zenphoto function getUnprotectedImageURL() is used
	 * @param string $imagetitle the title of the movie to be passed to the player for display (within slideshow), if empty (within albums) the function getImageTitle() is used
	 * @param string $count unique text for when there are multiple player items on a page
	 */
	function printPlayerConfig($moviepath='',$imagetitle='',$count ='') {
		echo $this->getPlayerConfig($moviepath,$imagetitle,$count,NULL,NULL);
	}

	/**
	 * Returns the height of the player
	 * @param object $image the image for which the width is requested
	 *
	 * @return int
	 */
	function getVideoWidth($image=NULL) {
		return getOption('flow_player3_width');
	}

	/**
	 * Returns the width of the player
	 * @param object $image the image for which the height is requested
	 *
	 * @return int
	 */
	function getVideoHeigth($image=NULL) {
		if (!is_null($image) && strtolower(strrchr($image->filename, ".") == '.mp3') && !getOption('flow_player3_mp3coverimage')) {
			return FLOW_PLAYER_MP3_HEIGHT;
		}
		return getOption('flow_player3_height');
	}

}
?>