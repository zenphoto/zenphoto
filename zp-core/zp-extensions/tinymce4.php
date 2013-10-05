<?php
/**
 * Back-end <i>WYSIWYG</i> editor TinyMCE 4.x. 
 *
 * You can place your own additional custom configuration files within
 * <var>%USER_PLUGIN_FOLDER%/tiny_mce/config</var>
 * There is a naming convention since there is a difference between Zenphoto (gallery) and Zenpag (news/pages) editor configurations
 * - zenphoto-<yourcustomname>.js.php
 * - zenpagae-<yourcustomname>.js.php
 * 
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage tools
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Text editor TinyMCE 4.x");
$plugin_author = "Malte Müller (acrylian)";
$option_interface = 'tinymce4Options';

zp_register_filter('texteditor_config','tinymce4ConfigJS');

/**
 * Plugin option handling class
 *
 */
class tinymce4Options {

	function tinymce4Options() {
		setOptionDefault('tinymce4_zenphoto', 'zenphoto-slim.js.php');
		setOptionDefault('tinymce4_zenpage', 'zenpage-slim.js.php');
	}

	function getOptionsSupported() {
		$configs_zenpage = getTinyMCE4ConfigFiles('zenpage');
		$configs_zenphoto = getTinyMCE4ConfigFiles('zenphoto');
		$options = array(gettext('Text editor configuration - Zenphoto') => array('key' => 'tinymce4_zenphoto', 'type' => OPTION_TYPE_SELECTOR,
										'order'=>0,
										'selections' => $configs_zenphoto,
										'null_selection' => gettext('Disabled'),
										'desc' => gettext('Applies to <em>admin</em> editable text other than for Zenpage pages and news articles.')),
										gettext('Text editor configuration - Zenpage') => array('key' => 'tinymce4_zenpage', 'type' => OPTION_TYPE_SELECTOR,
										'order'=>0,
										'selections' => $configs_zenpage,
										'null_selection' => gettext('Disabled'),
										'desc' => gettext('Applies to editing on the Zenpage <em>pages</em> and <em>news</em> tabs.')),
										gettext('Custom image size')	=> array('key'		 => 'tinymce_tinyzenpage_customimagesize', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext("Predefined size (px) for custom size images included using tinyZenpage.")),
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


function tinymce4ConfigJS($editorconfig,$mode) {
	if (empty($editorconfig)) {	// only if we get here first!
		$locale = substr(getOption("locale"),0,2);
		if (empty($locale)) {
			$locale = 'en';
		}
		$editorconfig = getOption('tinymce4_'.$mode);
		if (!empty($editorconfig)) {
			$editorconfig = getPlugin('/tinymce4/config/'.$editorconfig);
			if (!empty($editorconfig)) {
				require_once($editorconfig);
			}
		}
	}
	return $editorconfig;
}


function getTinyMCE4ConfigFiles($mode) {
	// get only those that work!
	switch($mode) {
		case 'zenphoto':
			$files = getPluginFiles('zenphoto-*.js.php','tinymce4/config/');
			break;
		case 'zenpage':
			$files = getPluginFiles('zenpage-*.js.php','tinymce4/config/');
			break;
	}
	$array = array();
	foreach($files as $file) {
		$filename = strrchr($file,'/');
		$filename = substr($filename, 1);
		$array[ucfirst(substr($filename,0,strpos($filename, '.js.php')))] = $filename;
	}
	return $array;	
}

?>