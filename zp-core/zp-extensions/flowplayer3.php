<?php
/**
 *
 * Support for the flowplayer 3.x.x flash video player. Now incorporates the former separate flowplayer3_playlist plugin.
 * NOTE: Flash players do not support external albums!
 *
 * Note on splash images: Flowplayer will try to use the first frame of a movie as a splash image or a videothumb if existing.
 *
 * The playlist part of the plugin supports custom CSS styling. You may override the default by placing a CSS file your theme folder:
 * flowplayer3_playlist.css or alternativels within the user plugins folder /flowplayer3/flowplayer3_playlist.css
 * This will allow you to customize the appearance of the comments on your site.
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 */


$plugin_description = gettext("Enable <strong>flowplayer 3</strong> to handle multimedia files.");
$plugin_notice = gettext("<strong>IMPORTANT</strong>: Only one multimedia player plugin can be enabled at the time and the class-video plugin must be enabled, too.").gettext('The former separate flowplayer3_playlist plugin is now incorporated. You can use it to show the content of an multimedia album only as a playlist or as separate players on one page with Flowplayer 3').'<br /><br />'.gettext("Please see <a href='http://flowplayer.org'>flowplayer.org</a> for more info about the player and its license.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$plugin_disable = (getOption('album_folder_class') === 'external')?gettext('Flash players do not support <em>External Albums</em>.'):false;
$option_interface = 'flowplayer3_options';

if (isset($_zp_flash_player) || $plugin_disable) {
	setOption('zp_plugin_flowplayer3', 0);
	if (isset($_zp_flash_player)) {
		trigger_error(sprintf(gettext('Flowplayer3 not enabled, %s is already instantiated.'),get_class($_zp_flash_player)),E_USER_NOTICE);
	}
} else {
	$_zp_flash_player = new Flowplayer3(); // claim to be the flash player.
	zp_register_filter('theme_head','flowplayerJS');
	if(getOption('flow_player3_loadplaylist')) {
		zp_register_filter('theme_head','flowplayer_playlistJS');
	}
}


function flowplayerJS() {
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

function flowplayer_playlistJS() {
	$theme = getCurrentTheme();
	$css = SERVERPATH . '/' . THEMEFOLDER . '/' . internalToFilesystem($theme) . '/flowplayer3_playlist.css';
	if (file_exists($css)) {
		$css = WEBPATH . '/' . THEMEFOLDER . '/' . $theme . '/flowplayer3_playlist.css';
	} else {
		$css = WEBPATH . '/' . ZENFOLDER . '/'.PLUGIN_FOLDER . '/flowplayer3/flowplayer3_playlist.css';
	}
	?>
	<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/'.PLUGIN_FOLDER; ?>/flowplayer3/jquery.tools.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo pathurlencode($css); ?>" />
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
		setOptionDefault('flow_player3_sharing', '');
		//playlist specific options

		setOptionDefault('flow_player3_playlistwidth', '320');
		setOptionDefault('flow_player3_playlistheight', '240');
		setOptionDefault('flow_player3_playlistautoplay', '');
		setOptionDefault('flow_player3_playlistsautohide','');
		setOptionDefault('flow_player3_playlistsplashimage','firstentry');
		setOptionDefault('flow_player3_playlistnumbered','1');
		setOptionDefault('flow_player3_loadplaylist', '');
		setOptionDefault('flow_player3_playlistplaytime', 0);

		if (class_exists('cacheManager')) {
			cacheManager::deleteThemeCacheSizes('flow_player3');
			cacheManager::addThemeCacheSize('flow_player3', NULL, getOption('flow_player3_width'), getOption('flow_player3_height'), NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
		}
	}


	function getOptionsSupported() {
		return array(	gettext('flow player width') => array('key' => 'flow_player3_width', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Player width (ignored for <em>mp3/m4a/fla</em> files.)")),
		gettext('flow player height') => array('key' => 'flow_player3_height', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Player height (ignored for <em>mp3/m4a/fla</em> files.)")),
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
										'desc' => gettext("Should the video start automatically. Yes if selected. (NOTE: Probably because of a flowplayer bug mp3/m4a/fla are always autoplayed.)")),
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
										'desc' => gettext("Enable if you want to show the videothumb as an splash image before the actual video starts (don't set the player to autoPlay then). If you want to use this for mp3s as well set also the mp3 cover image option.").gettext("The image is always scaled to fit (unless it is smaller than the width and height) so best make sure it matches at least the aspect ratio and size of your player width and height.")),
		gettext('Video scale') => array('key' => 'flow_player3_scaling', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('fit')=>"fit",gettext('half')=>"half", gettext('orig')=>"orig", gettext('scale')=>"scale"),
										'desc' => gettext("Setting which defines how video is scaled on the video screen. Available options are:<br /><em>fit</em>: Fit to window by preserving the aspect ratio encoded in the file's metadata.<br /><em>half</em>: Half-size (preserves aspect ratio)<br /><em>orig</em>: Use the dimensions encoded in the file. If the video is too big for the available space, the video is scaled using the 'fit' option.<br /><em>scale</em>: Scale the video to fill all available space. This is the default setting.")),
		gettext('MP3 cover image') => array('key' => 'flow_player3_mp3coverimage', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("If unchecked only the controlbar is shown for mp3/m4v/fla but if checked the video thumb is displayed as a cover image on the player screen.").gettext("The image is always scaled to fit (unless it is smaller than the width and height) so best make sure it matches at least the aspect ratio and size of your player width and height.")),
		gettext('flow player width') => array('key' => 'flow_player3_playlistwidth', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Player width (Note this refers to the player window. The playlist display itself is styled via CSS.)")),
		gettext('flow player height') => array('key' => 'flow_player3_playlistheight', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Player height (Note this refers to the player window. The playlist display itself is styled via CSS.)")),
		gettext('Autoplay') => array('key' => 'flow_player3_playlistautoplay', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("Should the video start automatically. Yes if selected. (NOTE: Probably because of a flowplayer bug mp3/m4a/fla are always autoplayed.)")),
		gettext('Controls autohide') => array('key' => 'flow_player3_playlistautohide', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('never')=>"never", gettext('always')=>"always", gettext('full screen')=>"fullscreen"),
										'desc' => gettext("Specifies whether the controlbar should be hidden when the user is not actively using the player.")),
		gettext('Splash image - playlist') => array('key' => 'flow_player3_playlistsplashimage', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('none')=>"none", gettext('Video thumb of first playlist entry')=>"firstentry", gettext('Album thumbnail')=>"albumthumb"),
										'desc' => gettext("Check if you want to display a splash/cover image for the playlist. Since this playlist plugin only lists multimedia items the album thumbnail of course could also be special separate normal image and not one of the entries.").gettext("The image is always scaled to fit (unless it is smaller than the width and height) so best make sure it matches at least the aspect ratio and size of your player width and height.")),
		gettext('Numbered playlist') => array('key' => 'flow_player3_playlistnumbered', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("If the playlist should be shown with numbers. Then a standard html ordered list is used instead of just a div with links. (Your playlist css may require changes.)")),
		gettext('Load playlist scripts') => array('key' => 'flow_player3_loadplaylist', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("If the Flowplayer playlist scripts should be loaded. Note that you have to add the function flowplayerPlaylist() to your theme yourself. See the documentation of this function on how to do this.")),
		gettext('Playlist playtime') => array('key' => 'flow_player3_playlistplaytime', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("Enable to show the playtime for playlist entries.")),
		gettext('Sharing') => array('key' => 'flow_player3_sharing', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("Enables sharing and embed code links."))


		);
	}
}

class Flowplayer3 {

	/**
	 * Print the JS configuration of flowplayer
	 *
	 * @param string $moviepath the direct path of a movie (within the slideshow), if empty (within albums) the current image is used
	 *
	 * @param string $imagetitle the title of the movie
	 * 	 */
	function getPlayerConfig($moviepath='', $imagetitle='', $count='', $width=NULL, $height=NULL) {
		global $_zp_current_image;
		$playerwidth = getOption('flow_player3_width');
		$playerheight = getOption('flow_player3_height');
		if(empty($moviepath)) {
			$moviepath = $_zp_current_image->getFullImage();
		} else {
			$moviepath = $moviepath;
		}
		$ext = getSuffix($moviepath);
		if(!empty($count)) {
			$count = "-".$count;
		}
		$imgextensions = array("jpg","jpeg","gif","png");
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
		if($ext == "mp3" || $ext == "m4a" || $ext == "fla") {
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
		$sharingenabled = '';
		if(getOption('flow_player3_sharing')) {
			$filelist = safe_glob('flowplayer.sharing-*.swf');
			$sharing = array_shift($filelist);
			$sharingenabled = '
						sharing: {
							url: "'.$sharing.'"
						},
			';
		}
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
			'.$sharingenabled.'
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
			if(($ext == "mp3" || $ext == "m4a" || $ext == "fla") && getOption('flow_player3_mp3coverimage')) {
				$playerconfigadd .= ',
				coverImage: {
					url:"'.urlencode($videoThumb).'",
					scaling: "'.getOption('flow_player3_scaling').'"
				}
				';
			}
			$playerconfigadd .= '
			}
		}).ipad();
		// ]]> -->
		</script>';
		$playerconfig = $playerconfig.$playerconfigadd;
		return $playerconfig;
	}

	/**
	 * outputs the player configuration HTML
	 *
	 * @param string $moviepath the direct path of a movie (within the slideshow), if empty (within albums) the current image is used
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
		$ext = getSuffix($image->filename);
		if (!is_null($image) && ($ext == 'mp3' || $ext == 'm4a' || $ext == 'fla') && !getOption('flow_player3_mp3coverimage')) {
			return FLOW_PLAYER_MP3_HEIGHT;
		}
		return getOption('flow_player3_height');
	}

}

/**
 * Show the content of an media album with .flv/.mp4/.mp3 movie/audio files only as a playlist or as separate players with Flowplayer 3
 * Important: The Flowplayer 3 plugin needs to be activated to use this plugin. This plugin shares all settings with this plugin, too.
 *
 * You can either show a 'one player window' playlist or show all items as separate players paginated. See the examples below.
 * (set in the settings for thumbs per page) on one page (like on a audio or podcast blog).
 *
 * There are two usage modes:
 *
 * a) 'playlist'
 * The playlist is meant to replace the 'next_image()' loop on a theme's album.php.
 * It can be used with a special 'album theme' that can be assigned to media albums with with .flv/.mp4/.mp3s, although Flowplayer 3 also supports images
 * Replace the entire 'next_image()' loop on album.php with this:
 * <?php flowplayerPlaylist("playlist"); ?>
 *
 * This produces the following html:
 * <div class="wrapper">
 * <a class="up" title="Up"></a>
 * <div class="playlist">
 * <div class="clips">
 * <!-- single playlist entry as an "template" -->
 * <a href="${url}">${title}</a>
 * </div>
 * </div>
 * <a class="down" title="Down"></a>
 * </div>
 * </div>
 * This is styled by the css file 'playlist.css" that is located within the 'zp-core/plugins/flowplayer3_playlist/flowplayer3_playlist.css' by default.
 * Alternatively you can style it specifically for your theme. Just place a css file named "flowplayer3_playlist.css" in your theme's folder.
 *
 * b) 'players'
 * This displays each audio/movie file as a separate player on album.php.
 * If there is no videothumb image for an mp3 file existing only the player control bar is shown.
 * Modify the 'next_image()' loop on album.php like this:
 * <?php
 * while (next_image()):
 * flowplayerPlaylist("players");
 * endwhile;
 * ?>
 * Of course you can add further functions to b) like printImageTitle() etc., too.
 *
 * @param string $option The mode to use "players", "playlist" or "playlist-mp3". "playlist-mp3" is the same as "playlist" except that only the controlbar is shown (if you are too lazy for custom video thumbs and don't like the empty screen)
 * @param string $albumfolder For "playlist" mode only: To show a playlist of an specific album directly on another page (for example on index.php). Note: Currently it is not possible to have several playlists on one page
 */
function flowplayerPlaylist($option="playlist",$albumfolder="") {
	global $_zp_current_image,$_zp_current_album,$_zp_flash_player;
		$curdir = getcwd();
		chdir(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/flowplayer3');
		$filelist = safe_glob('flowplayer-*.swf');
		$swf = array_shift($filelist);
		$filelist = safe_glob('flowplayer.audio-*.swf');
		$audio = array_shift($filelist);
		$filelist = safe_glob('flowplayer.controls-*.swf');
		$controls = array_shift($filelist);
		chdir($curdir);
		$playlistwidth = getOption('flow_player3_playlistwidth');
		$playlistheight = getOption('flow_player3_playlistheight');
	switch($option) {
		case 'playlist':
		case 'playlist-mp3':
				$splashimage = getOption('flow_player3_playlistsplashimage');
				if($option == 'playlist-mp3') {
					$playlistheight = FLOW_PLAYER_MP3_HEIGHT;
					$splashimage = 'none';
				}
				if(empty($albumfolder)) {
					$albumname = $_zp_current_album->name;
				} else {
					$albumname = $albumfolder;
				}
				$album = new Album(NULL, $albumname);
				if(getOption("flow_player3_playlistautoplay") == 1) {
					$autoplay = 'true';
				} else {
					$autoplay = 'false';
				}
				$playlist = $album->getImages();

				// slash image fetching
				$videoobj = new Video($album,$playlist[0]);
				$albumfolder = $album->name;
				$splashimagerwidth = $playlistwidth;
				$splashimageheight = $playlistheight;
				$videoThumbImg = '';
				if ($splashimage != 'none') {
					switch($splashimage) {
						case 'albumthumb':
							$albumthumbobj = $album->getAlbumThumbImage();
				  		getMaxSpaceContainer($splashimagerwidth, $splashimageheight, $albumthumbobj, true);
				  		$albumthumb = $albumthumbobj->getCustomImage(null, $splashimagerwidth, $splashimageheight, null, null, null, null, true);
				  		$videoThumbImg = '<img src="'.pathurlencode($albumthumb).'" alt="" />';
							break;
						case 'firstentry':
							getMaxSpaceContainer($splashimagerwidth, $splashimageheight, $videoobj, true);
				  		$videoThumb = $videoobj->getCustomImage(null, $splashimagerwidth, $splashimageheight, null, null, null, null, true);
							$videoThumbImg = '<img src="'.pathurlencode($videoThumb).'" alt="" />';
							break;
					}
				}
			if($album->getNumImages() != 0) {
				if(getOption('flow_player3_playlistnumbered')) {
					$liststyle = 'ol';
				} else {
					$liststyle = 'div';
				}
			echo '<div class="flowplayer3_playlistwrapper">
			<a id="player'.$album->get('id').'" class="flowplayer3_playlist" style="display:block; width: '.$playlistwidth.'px; height: '.$playlistheight.'px;">
			'.$videoThumbImg.'
			</a>
			<script type="text/javascript">
			// <!-- <![CDATA[
			$(function() {

			$("div.playlist").scrollable({
				items:"'.$liststyle.'.clips'.$album->get('id').'",
				vertical:true,
				next:"a.down",
				prev:"a.up",
				mousewheel: true
			});
			flowplayer("player'.$album->get('id').'","'.WEBPATH . '/' . ZENFOLDER . '/'.PLUGIN_FOLDER . '/flowplayer3/'.$swf.'", {
			plugins: {
				audio: {
					url: "'.$audio.'"
				},
				controls: {
					url: "'.$controls.'",
					backgroundColor: "'.getOption('flow_player3_controlsbackgroundcolor').'",
					autoHide: "'.getOption('flow_player3_playlistautohide').'",
					timeColor:"'.getOption('flow_player3_controlstimecolor').'",
					durationColor: "'.getOption('flow_player3_controlsdurationcolor').'",
					progressColor: "'.getOption('flow_player3_controlsprogresscolor').'",
					progressGradient: "'.getOption('flow_player3_controlsprogressgradient').'",
					bufferColor: "'.getOption('flow_player3_controlsbuffercolor').'",
					bufferGradient:	 "'.getOption('fflow_player3_controlsbuffergradient').'",
					sliderColor: "'.getOption('flow_player3_controlsslidercolor').'",
					sliderGradient: "'.getOption('flow_player3_controlsslidergradient').'",
					buttonColor: "'.getOption('flow_player3_controlsbuttoncolor').'",
					buttonOverColor: "'.getOption('flow_player3_controlsbuttonovercolor').'",
					scaling: "'.getOption('flow_player3_scaling').'",
					playlist: true
				}
			},
			canvas: {
				backgroundColor: "'.getOption('flow_player3_backgroundcolor').'",
				backgroundGradient: "'.getOption('flow_player3_backgroundcolorgradient').'"
			},';
			$list = '';
			foreach($playlist as $item) {
				$image = newImage($album, $item);
				$playtime = '';
				if(getOption('flow_player3_playlistplaytime') ) {
					$playtime = $image->get('VideoPlaytime');
					if(!empty($playtime)) {
						$playtime = $image->get('VideoPlaytime');
					}
				}
				$coverimagerwidth = getOption('flow_player3_playlistwidth');
				$coverimageheight = getOption('flow_player3_playlistheight');
				getMaxSpaceContainer($coverimagerwidth, $coverimageheight, $image, true);
				$cover = $image->getCustomImage(null, $coverimagerwidth, $coverimageheight, null, null, null, null, true);
				$ext = getSuffix($item);
				if ($ext == "flv" || $ext == "mp3" || $ext == "mp4" || $ext == "fla" || $ext == "m4v" || $ext == "m4a") {
				$list .= '{
					url:"'.ALBUM_FOLDER_WEBPATH.$album->name.'/'.$item.'",
					autoPlay: '.$autoplay.',
					title: "'.$image->getTitle().' <small>('.$playtime.')</small>",
					autoBuffering: '.$autoplay.',
					coverImage: {
						url: "'.urlencode($cover).'",
						scaling: "fit"
					}
				},';
				} // if ext end
			} // foreach end
			echo 'playlist: ['.substr($list,0,-1).']
			});
			flowplayer("player'.$album->get('id').'").playlist("'.$liststyle.'.clips'.$album->get('id').':first", {loop:true});
			});
			// ]]> -->
			</script>';
		?>
		<div class="wrapper">
					<a class="up" title="Up"></a>

			<div class="playlist playlist<?php echo $album->get('id'); ?>">
				<<?php echo $liststyle; ?> class="clips clips<?php echo $album->get('id'); ?>">
					<!-- single playlist entry as an "template" -->
					<?php if($liststyle == 'ol') { ?> <li> <?php } ?>
					<a href="${url}">${title}</a>
					<?php if($liststyle == 'ol') { ?> </li> <?php } ?>
				</<?php echo $liststyle; ?>>
			</div>
		<a class="down" title="Down"></a>
</div>
</div><!-- flowplayer3_playlist wrapper end -->
<?php } // check if there are images end
			break;
			case 'players':
				$_zp_flash_player->printPlayerConfig('','',imageNumber());
				break;
		} // switch end
}
?>