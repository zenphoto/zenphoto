<?php

/**
 * Back-end <i>WYSIWYG</i> editor TinyMCE 4.x.
 *
 * You can place your own additional custom configuration files within
 * <var>%USER_PLUGIN_FOLDER%/tiny_mce/config</var> or <var>%THEMEFOLDER%/tiny_mce/config</var>
 * There is a naming convention for editor configurations
 * - zenphoto-<name>.php
 * - zenpage-<name>.php
 * - comment-<name>.php
 *
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = defaultExtension(5 | ADMIN_PLUGIN);
$plugin_description = gettext("Text editor TinyMCE 4.x");
$plugin_author = "Malte Müller (acrylian)";
$option_interface = 'tinymceOptions';

if (!defined('EDITOR_SANITIZE_LEVEL'))
	define('EDITOR_SANITIZE_LEVEL', 4);
zp_register_filter('texteditor_config', 'tinymceConfigJS');

/**
 * Plugin option handling class
 *
 */
class tinymceOptions {

	function tinymceOptions() {
		if (OFFSET_PATH == 2) {
			$old = getOption('tinymce_zenphoto');
			if (strpos($old, '.js.php') !== false)
				setOption('tinymce_zenphoto', str_replace('.js.php', '.php', $old));
			$old = getOption('tinymce_zenpage');
			if (strpos($old, '.js.php') !== false)
				setOption('tinymce_zenpage', str_replace('.js.php', '.php', $old));
		}
		setOptionDefault('tinymce_zenphoto', 'zenphoto-ribbon.php');
		setOptionDefault('tinymce_zenpage', 'zenpage-ribbon.php');
	}

	function getOptionsSupported() {
		$configs_zenpage = gettinymceConfigFiles('zenpage');
		$configs_zenphoto = gettinymceConfigFiles('zenphoto');
		$options = array(
						gettext('Text editor configuration - zenphoto')	 => array('key'						 => 'tinymce_zenphoto', 'type'					 => OPTION_TYPE_SELECTOR,
										'order'					 => 0,
										'selections'		 => $configs_zenphoto,
										'null_selection' => gettext('Disabled'),
										'desc'					 => gettext('Applies to <em>admin</em> editable text other than for Zenpage pages and news articles.')),
						gettext('tinyZenpage plugin')										 => array('key'		 => 'tinymce_tinyzenpage', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 1.5,
										'desc'	 => gettext('Enable legacy tinyZenpage feature.')),
						gettext('Text editor configuration - zenpage')	 => array('key'						 => 'tinymce_zenpage', 'type'					 => OPTION_TYPE_SELECTOR,
										'order'					 => 1,
										'selections'		 => $configs_zenpage,
										'null_selection' => gettext('Disabled'),
										'desc'					 => gettext('Applies to editing on the Zenpage <em>pages</em> and <em>news</em> tabs.'))
		);
		if (getOption('tinymce_tinyzenpage')) {
			$tinyzenpageoptions = array(
							gettext('Custom image size')					 => array('key'		 => 'tinymce_tinyzenpage_customimagesize', 'type'	 => OPTION_TYPE_NUMBER,
											'order'	 => 2,
											'desc'	 => gettext("Predefined size (px) for custom size images included using tinyZenpage.")),
							gettext('Custom image size')					 => array('key'		 => 'tinymce_tinyzenpage_customimagesize', 'type'	 => OPTION_TYPE_NUMBER,
											'order'	 => 2,
											'desc'	 => gettext("Predefined size (px) for custom size images included using tinyZenpage.")),
							gettext('Custom thumb crop - size')		 => array('key'		 => 'tinymce_tinyzenpage_customthumb_size', 'type'	 => OPTION_TYPE_NUMBER,
											'order'	 => 2,
											'desc'	 => gettext("Predefined size (px) for custom cropped thumb images included using tinyZenpage.")),
							gettext('Custom thumb crop - width')	 => array('key'		 => 'tinymce_tinyzenpage_customthumb_cropwidth', 'type'	 => OPTION_TYPE_NUMBER,
											'order'	 => 2,
											'desc'	 => gettext("Predefined crop width (%) for custom cropped thumb  images included using tinyZenpage.")),
							gettext('Custom thumb crop - height')	 => array('key'		 => 'tinymce_tinyzenpage_customthumb_cropheight', 'type'	 => OPTION_TYPE_NUMBER,
											'order'	 => 2,
											'desc'	 => gettext("Predefined crop height (%) for custom cropped thumb images included using tinyZenpage."))
			);
			$options = array_merge($options, $tinyzenpageoptions);
		}
		return $options;
	}

	function handleOption($option, $currentValue) {

	}

}

function tinymceConfigJS($mode) {
	global $_editorconfig;
	if (empty($_editorconfig)) { // only if we get here first!
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
			$_editorconfig = getPlugin('tinymce/config/' . $_editorconfig, true);
			if (!empty($_editorconfig)) {
				require_once($_editorconfig);
			}
		}
	}
	return $mode;
}

function gettinymceConfigFiles($mode) {
	// get only those that work!
	$files = getPluginFiles($mode . '-*.php', 'tinymce/config/');
	$array = array();
	foreach ($files as $file) {
		$filename = strrchr($file, '/');
		$filename = substr($filename, 1);
		$option = preg_replace('/^' . $mode . '-/', '', $filename);
		$option = ucfirst(preg_replace('/.php$/', '', $option));
		$array[$option] = $filename;
	}
	return $array;
}

?>