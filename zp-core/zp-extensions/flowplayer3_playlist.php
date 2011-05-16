<?php
/**
 * Show the content of an media album with .flv/.mp4/.mp3 movie/audio files only as a playlist or as separate players with Flowplayer 3
 * IMPORTANT: The Flowplayer 3 plugin needs to be activated to use this plugin.
 *
 * Note that this does not work with pure image albums and is not meant to!
 *
 * See usage details below
 *
 * NOTE: Flash players do not support external albums!
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 */


$plugin_description =  gettext("Use to show the content of an media album with .flv/.mp4/.mp3 movie/audio files only as a playlist or as separate players on one page with Flowplayer 3.");
$plugin_author = "Malte Müller (acrylian)";
$plugin_version = '1.4.1';
$plugin_disable = ((!getOption('zp_plugin_flowplayer3'))?gettext('This plugin requires the Flowplayer 3 plugin to be activated. '):false) . ((getOption('album_folder_class') === 'external')?gettext('Flash players do not support <em>External Albums</em>.'):false);

if ($plugin_disable) {
	setOption('zp_plugin_flowplayer3_playlist',0);
} else {
	$option_interface = 'flowplayer3_playlist';
	// register the scripts needed - only playlist additions, all others incl. the playlist plugin are loaded by the flowplayer3 plugin!
	if (in_context(ZP_ALBUM) && !OFFSET_PATH) {
		zp_register_filter('theme_head','flowplayer3_playlistJS');
	}
}
function flowplayer3_playlistJS() {
	$theme = getCurrentTheme();
	$css = SERVERPATH . '/' . THEMEFOLDER . '/' . internalToFilesystem($theme) . '/flowplayer3_playlist.css';
	if (file_exists($css)) {
		$css = WEBPATH . '/' . THEMEFOLDER . '/' . $theme . '/flowplayer3_playlist.css';
	} else {
		$css = WEBPATH . '/' . ZENFOLDER . '/'.PLUGIN_FOLDER . '/flowplayer3_playlist/flowplayer3_playlist.css';
	}
	?>
	<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/'.PLUGIN_FOLDER; ?>/flowplayer3_playlist/jquery.tools.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo pathurlencode($css); ?>" />
	<?php
}

/**
 * Plugin option handling class
 *
 */
class flowplayer3_playlist {
	function flowplayer3_playlist() {
		setOptionDefault('flow_player3_playlistwidth', '320');
		setOptionDefault('flow_player3_playlistheight', '240');
		setOptionDefault('flow_player3_playlistautoplay', '');
		setOptionDefault('flow_player3_playlistsautohide','');
		setOptionDefault('flow_player3_playlistsplashimage','firstentry');
		setOptionDefault('flow_player3_playlistnumbered','1');
	}

	function getOptionsSupported() {
		return array(	gettext('flow player width') => array('key' => 'flow_player3_playlistwidth', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Player width (Note this refers to the player window. The playlist display itself is styled via CSS.)")),
		gettext('flow player height') => array('key' => 'flow_player3_playlistheight', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Player height (Note this refers to the player window. The playlist display itself is styled via CSS.)")),
		gettext('Autoplay') => array('key' => 'flow_player3_playlistautoplay', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("Should the video start automatically. Yes if selected. (NOTE: Probably because of a flowplayer bug mp3s are always autoplayed.)")),
		gettext('Controls autohide') => array('key' => 'flow_player3_playlistautohide', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('never')=>"never", gettext('always')=>"always", gettext('full screen')=>"fullscreen"),
										'desc' => gettext("Specifies whether the controlbar should be hidden when the user is not actively using the player.")),
		gettext('Splash image') => array('key' => 'flow_player3_playlistsplashimage', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('none')=>"none", gettext('Video thumb of first playlist entry')=>"firstentry", gettext('Album thumbnail')=>"albumthumb"),
										'desc' => gettext("Check if you want to display a splash/cover image for the playlist. Since this playlist plugin only lists multimedia items the album thumbnail of course could also be special separate normal image and not one of the entries.").gettext("The image is always scaled to fit (unless it is smaller than the width and height) so best make sure it matches at least the aspect ratio and size of your player width and height.")),
		gettext('Numbered playlist') => array('key' => 'flow_player3_playlistnumbered', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("If the playlist should be shown with numbers. Then a standard html ordered list is used instead of just a div with links. (Your playlist css may require changes.)")),

	);
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
				$album = new Album(new Gallery(), $albumname);
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
				$coverimagerwidth = getOption('flow_player3_playlistwidth');
				$coverimageheight = getOption('flow_player3_playlistheight');
				getMaxSpaceContainer($coverimagerwidth, $coverimageheight, $image, true);
				$cover = $image->getCustomImage(null, $coverimagerwidth, $coverimageheight, null, null, null, null, true);
				$ext = strtolower(strrchr($item, "."));
				if (($ext == ".flv") || ($ext == ".mp3") || ($ext == ".mp4")) {
				$list .= '{
					url:"'.ALBUM_FOLDER_WEBPATH.$album->name.'/'.$item.'",
					autoPlay: '.$autoplay.',
					title: "'.$image->getTitle().' <small>('.$ext.')</small>",
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