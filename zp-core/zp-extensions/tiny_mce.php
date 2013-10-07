<?php

/**
 * Back-end <i>WYSIWYG</i> editor
 *
 * You can place your own additional custom configuration files within
 * <var>%USER_PLUGIN_FOLDER%/tiny_mce/config</var>
 * (e.g. <i>filename</i>.js.php)
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 * @subpackage tools
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Tiny MCE<span id='tinymceversion'></span> text editor for backend <em>textareas</em>") . ' <script type="text/javascript">	if (tinymce) $("#tinymceversion").html(" v"+tinymce.majorVersion + "." + tinymce.minorVersion);	</script>';
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";

$option_interface = 'tinymceOptions';

setOptionDefault('zp_plugin_tiny_mce', $plugin_is_filter);

zp_register_filter('texteditor_config', 'tinymceConfigJS');

/**
 * Plugin option handling class
 *
 */
class tinymceOptions {

	function tinymceOptions() {
		setOptionDefault('tinymce_zenphoto', 'zenphoto-default.js.php');
		setOptionDefault('tinymce_zenpage', 'zenpage-default-full.js.php');
		setOptionDefault('tinymce_tinyzenpage_customimagesize', '400');
		setOptionDefault('tinymce_tinyzenpage_customthumb_size', '120');
		setOptionDefault('tinymce_tinyzenpage_customthumb_cropwidth', '120');
		setOptionDefault('tinymce_tinyzenpage_customthumb_cropheight', '120');
		setOptionDefault('tinymce_tinyzenpage_flowplayer_width', '320');
		setOptionDefault('tinymce_tinyzenpage_flowplayer_height', '240');
		setOptionDefault('tinymce_tinyzenpage_flowplayer_mp3_height', '26');
		if (class_exists('cacheManager')) {
			cacheManager::deleteThemeCacheSizes('tinyzenpage');
			cacheManager::addThemeCacheSize('tinyzenpage', NULL, getOption('tinymce_tinyzenpage_customimagesize'), getOption('tinymce_tinyzenpage_customthumb_size'), NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
			cacheManager::addThemeCacheSize('tinyzenpage', NULL, getOption('tinymce_tinyzenpage_flowplayer_width'), getOption('tinymce_tinyzenpage_flowplayer_height'), NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
			cacheManager::addThemeCacheSize('tinyzenpage', 85, NULL, NULL, 85, 85, NULL, NULL, true, NULL, NULL, NULL);
		}
	}

	function getOptionsSupported() {
		$configarray = getTinyMCEConfigFiles();
		$options = array(gettext('Text editor configuration - Zenphoto')	 => array('key'						 => 'tinymce_zenphoto', 'type'					 => OPTION_TYPE_SELECTOR,
										'order'					 => 0,
										'selections'		 => $configarray,
										'null_selection' => gettext('Disabled'),
										'desc'					 => gettext('Applies to <em>admin</em> editable text other than for Zenpage pages and news articles.')),
						gettext('Text editor configuration - Zenpage')	 => array('key'						 => 'tinymce_zenpage', 'type'					 => OPTION_TYPE_SELECTOR,
										'order'					 => 0,
										'selections'		 => $configarray,
										'null_selection' => gettext('Disabled'),
										'desc'					 => gettext('Applies to editing on the Zenpage <em>pages</em> and <em>news</em> tabs.')),
						gettext('Custom image size')										 => array('key'		 => 'tinymce_tinyzenpage_customimagesize', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext("Predefined size (px) for custom size images included using tinyZenpage.")),
						gettext('Custom thumb crop - size')							 => array('key'		 => 'tinymce_tinyzenpage_customthumb_size', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext("Predefined size (px) for custom cropped thumb images included using tinyZenpage.")),
						gettext('Custom thumb crop - width')						 => array('key'		 => 'tinymce_tinyzenpage_customthumb_cropwidth', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext("Predefined crop width (%) for custom cropped thumb  images included using tinyZenpage.")),
						gettext('Custom thumb crop - height')						 => array('key'		 => 'tinymce_tinyzenpage_customthumb_cropheight', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext("Predefined crop height (%) for custom cropped thumb images included using tinyZenpage.")),
						gettext('Flowplayer width')											 => array('key'		 => 'tinymce_tinyzenpage_flowplayer_width', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 3,
										'desc'	 => gettext("Video player width if included using tinyZenpage")),
						gettext('Flowplayer height')										 => array('key'		 => 'tinymce_tinyzenpage_flowplayer_height', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 4,
										'desc'	 => gettext("Video player height for videos included using tinyZenpage")),
						gettext('Mp3 control bar height')								 => array('key'		 => 'tinymce_tinyzenpage_flowplayer_mp3_height', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 5,
										'desc'	 => gettext("Video player height for mp3s included using tinyZenpage"))
		);
		return $options;
	}

	function handleOption($option, $currentValue) {

	}

}

function tinymceConfigJS($editorconfig, $mode) {
	if (empty($editorconfig)) { // only if we get here first!
		$locale = 'en';
		$loc = str_replace('_', '-', strtolower(getOption("locale")));
		if ($loc) {
			if (file_exists(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tiny_mce/langs/' . $loc . '.js')) {
				$locale = $loc;
			} else {
				$loc = substr($loc, 0, 2);
				if (file_exists(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tiny_mce/langs/' . $loc . '.js')) {
					$locale = $loc;
				}
			}
		}
		$editorconfig = getOption('tinymce_' . $mode);
		if (!empty($editorconfig)) {
			$editorconfig = getPlugin('/tiny_mce/config/' . $editorconfig);
			if (!empty($editorconfig)) {
				require_once($editorconfig);
			}
		}
	}
	return $editorconfig;
}

function getTinyMCEConfigFiles() {
	$files = getPluginFiles('*.js.php', 'tiny_mce/config/');
	$array = array();
	foreach ($files as $file) {
		$filename = strrchr($file, '/');
		$filename = substr($filename, 1);
		$array[ucfirst(substr($filename, 0, strpos($filename, '.js.php')))] = $filename;
	}
	return $array;
}

?>