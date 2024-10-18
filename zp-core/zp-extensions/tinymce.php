<?php

/**
 * Back-end <i>WYSIWYG</i> editor TinyMCE 7+.
 *
 * You can place your own additional custom configuration files within
 * <var>%USER_PLUGIN_FOLDER%/tiny_mce/config</var>
 * There is a naming convention since there is a difference between Zenphoto (gallery) and Zenpage (news/pages) editor configurations.
 * <var>zenphoto-<yourcustomname>.js.php</var>
 * <var>zenpage-<yourcustomname>.js.php</var>
 *
 * @author Malte Müller (acrylian)
 * @package zpcore\plugins\tinymce
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Text editor TinyMCE 7+");
$plugin_author = "Malte Müller (acrylian), Fred Sondaar (fretzl), Antonio Ranesi (bic-ed)";
$plugin_category = gettext('Admin');
$plugin_disable = (extensionEnabled('tinymce4')) ? sprintf(gettext('Only one TinyMCE editor plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'), 'tinymce4') : '';
$option_interface = 'tinymceOptions';

if (!defined('EDITOR_SANITIZE_LEVEL'))
	define('EDITOR_SANITIZE_LEVEL', 4);
zp_register_filter('texteditor_config', 'tinymceOptions::tinymceConfigJS');

/**
 * Plugin option handling class
 *
 */
class tinymceOptions {

	function __construct() {
		setOptionDefault('tinymce_zenphoto', 'zenphoto-basic.js.php');
		setOptionDefault('tinymce_zenpage', 'zenpage-basic.js.php');
		setOptionDefault('tinymce_entitiyencoding', 'raw');
		setOptionDefault('tinymce_textfield-height', 400);
		setOptionDefault('tinymce_browser-spellcheck', false);
		setOptionDefault('tinymce_browser-menu', false);
	}

	function getOptionsSupported() {
		$configs_zenpage = self::gettinymceConfigFiles('zenpage');
		$configs_zenphoto = self::gettinymceConfigFiles('zenphoto');
		$options = array(
				gettext('Text editor configuration - Zenphoto') => array(
						'key' => 'tinymce_zenphoto',
						'type' => OPTION_TYPE_SELECTOR,
						'selections' => $configs_zenphoto,
						'null_selection' => gettext('Disabled'),
						'desc' => gettext('Applies to <em>admin</em> editable text other than for Zenpage pages and news articles.')),
				gettext('Text editor configuration - Zenpage') => array(
						'key' => 'tinymce_zenpage',
						'type' => OPTION_TYPE_SELECTOR,
						'selections' => $configs_zenpage,
						'null_selection' => gettext('Disabled'),
						'desc' => gettext('Applies to editing on the Zenpage <em>pages</em> and <em>news</em> tabs.')),
				gettext('Text editor height') => array(
						'key' => 'tinymce_textfield-height',
						'type' => OPTION_TYPE_CLEARTEXT,
						'desc' => gettext('Predefined height (px) for Zenphoto and Zenpage textfields. Default is 400px.')),
				gettext('Custom image size') => array(
						'key' => 'tinymce_tinyzenpage_customimagesize',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("Predefined size (px) for custom size images included using tinyZenpage.")),
				gettext('Custom image size') => array(
						'key' => 'tinymce_tinyzenpage_customimagesize',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("Predefined size (px) for custom size images included using tinyZenpage.")),
				gettext('Custom thumb crop - size') => array(
						'key' => 'tinymce_tinyzenpage_customthumb_size',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("Predefined size (px) for custom cropped thumb images included using tinyZenpage.")),
				gettext('Custom thumb crop - width') => array(
						'key' => 'tinymce_tinyzenpage_customthumb_cropwidth',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("Predefined crop width (%) for custom cropped thumb  images included using tinyZenpage.")),
				gettext('Custom thumb crop - height') => array(
						'key' => 'tinymce_tinyzenpage_customthumb_cropheight',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("Predefined crop height (%) for custom cropped thumb images included using tinyZenpage.")),
				gettext('Entity encoding') => array(
						'key' => 'tinymce_entityencoding',
						'type' => OPTION_TYPE_SELECTOR,
						'selections' => array(
								gettext('Raw') => 'raw',
								gettext('Numeric') => 'numeric',
								gettext('Named') => 'named',
								gettext('Named and numeric') => 'named+numeric'
						),
						'null_selection' => 'raw',
						'desc' => gettext('If encountering issues with special chars and other character entities change this. Note that this applies on re-saving content only. More info on the <a href="https://www.tiny.cloud/docs/tinymce/latest/content-filtering/">TinyMCE docs</a>.')),
				gettext('Entities') => array(
						'key' => 'tinymce_entities',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('You can adjust how entities are processed. More info on the <a href="https://www.tiny.cloud/docs/tinymce/latest/content-filtering/#entities">TinyMCE docs</a>.')),
				gettext('Browser spellcheck') => array(
						'key' => 'tinymce_browser-spellcheck',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Check to use your browser spellchecker.<br><strong>Note:</strong> Spellcheck feature should be available in the browser and enabled (location of settings to do so vary from browser to browser).<br><strong>Warning:</strong> Depending on browser, this can be a privacy concern. Do your research on the implementation of spellchek in your browser of choice.')),
				gettext('Browser context menu') => array(
						'key' => 'tinymce_browser-menu',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Check to disable TinyMCE right-click menu, browser right-click menu will be used instead.<br><strong>Note:</strong> If TinyMCE right-click menu is used - browser right-click menu can still be accessed by pressing <em>Ctrl+Right click</em>.')),
		);
		return $options;
	}

	function handleOption($option, $currentValue) {
		
	}

	static function tinymceConfigJS($mode) {
		global $_editorconfig;
		if (empty($_editorconfig)) { // only if we get here first!
			/*
			 *  I am not really sure we need to load these files as tinymce is supposed to do this automatically?
			 * 
			 */
			$locale = 'en';
			$loc = str_replace('_', '-', getOption("locale"));
			if ($loc) {
				if (file_exists(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/langs/' . $loc . '.js')) {
					$locale = $loc;
				} else {
					$loc = substr($loc, 0, 2);
					if (file_exists(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/langs/' . $loc . '.js')) {
						$locale = $loc;
					}
				}
			}
			$_editorconfig = getOption('tinymce_' . $mode);
			if (!empty($_editorconfig)) {
				$_editorconfig = getPlugin('/tinymce/config/' . $_editorconfig, true);
				if (!empty($_editorconfig)) {
					require_once($_editorconfig);
				}
			}
		}
		return $mode;
	}

	static function gettinymceConfigFiles($mode) {
		// get only those that work!
		$files = getPluginFiles($mode . '-*.js.php', 'tinymce/config/');
		$array = array();
		foreach ($files as $file) {
			$filename = strrchr($file, '/');
			$filename = substr($filename, 1);
			$option = preg_replace('/^' . $mode . '-/', '', $filename);
			$option = ucfirst(preg_replace('/.js.php$/', '', $option));
			$array[$option] = $filename;
		}
		return $array;
	}
}
