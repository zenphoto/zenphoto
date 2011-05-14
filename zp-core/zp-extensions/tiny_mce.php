<?php
/**
 * Back-end WYSIWYG editor
 * You can place your own additional custom configuration files within /zp-core/zp-extensions/tiny_mce/config (e.g. <filename>.js.php)
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Tiny MCE<span id='tinymceversion'></span> text editor for backend textareas").'<script type="text/javascript">if (tinymce) $("#tinymceversion").html(" v"+tinymce.majorVersion + "." + tinymce.minorVersion);</script>';

$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$plugin_version = '1.4.1';
$option_interface = 'tinymceOptions';

zp_register_filter('texteditor_config','tinymceConfigJS');

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
	}

	function getOptionsSupported() {
		$configarray = getTinyMCEConfigFiles();
		$options = array(gettext('Text editor configuration - Zenphoto') => array('key' => 'tinymce_zenphoto', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => $configarray,
										'desc' => gettext('Applies to <em>admin</em> editable text other than for Zenpage pages and news articles.')),
										gettext('Text editor configuration - Zenpage') => array('key' => 'tinymce_zenpage', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => $configarray,
										'desc' => gettext('Applies to editing on the Zenpage <em>pages</em> and <em>news</em> tabs.')),
										gettext('tinyZenpage - custom image size') => array('key' => 'tinymce_tinyzenpage_customimagesize', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Predefined size (px) for custom size images included using tinyZenpage.")),
										gettext('tinyZenpage - custom thumb crop - size') => array('key' => 'tinymce_tinyzenpage_customthumb_size', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Predefined size (px) for custom cropped thumb images included using tinyZenpage.")),
										gettext('tinyZenpage - custom thumb crop - width') => array('key' => 'tinymce_tinyzenpage_customthumb_cropwidth', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Predefined crop width (%) for custom cropped thumb  images included using tinyZenpage.")),
										gettext('tinyZenpage - custom thumb crop - height') => array('key' => 'tinymce_tinyzenpage_customthumb_cropheight', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Predefined crop height (%) for custom cropped thumb  images included using tinyZenpage.")),
										gettext('tinyZenpage - Flowplayer width') => array('key' => 'tinymce_tinyzenpage_flowplayer_width', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Video player width if included using tinyZenpage")),
										gettext('tinyZenpage - Flowplayer height') => array('key' => 'tinymce_tinyzenpage_flowplayer_height', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Video player height if included using tinyZenpage (for mp3s only the control bar is used)"))
									);
		return $options;
	}

	function handleOption($option, $currentValue) {
	}
}


function tinymceConfigJS($editorconfig,$mode) {
	if (empty($editorconfig)) {	// only if we get here first!
		$locale = getLocaleForTinyMCEandAFM();
		switch($mode) {
			case 'zenphoto':
				$editorconfig = getOption('tinymce_zenphoto');
				break;
			case 'zenpage';
			$editorconfig = getOption('tinymce_zenpage');
			break;
		}
		if (!empty($editorconfig)) {
			$editorconfig = getPlugin('/tiny_mce/config/'.$editorconfig);
			require_once($editorconfig);
		}
	}
	return $editorconfig;
}

function getTinyMCEConfigFiles() {
		$array = array();
		$files = getPluginFiles('*.js.php','tiny_mce/config/');
		$default = array(gettext('TinyMCE disabled') => '');
		$array = array_merge($array,$default);
		foreach($files as $file) {
			$filename = strrchr($file,'/');
			$filename = substr($filename, 1);
			$filearray = array($filename => $filename);
			//print_r($filearray);
			$array = array_merge($array,$filearray);
		}
		return $array;
	}

	//$array = getTinyMCEConfigFiles();
	//echo "<pre>"; print_r($array); echo "</pre>";
?>