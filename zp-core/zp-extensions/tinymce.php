<?php

/**
 * Back-end <i>WYSIWYG</i> editor TinyMCE 4.x.
 *
 * You can place your own additional custom configuration files within
 * <var>%USER_PLUGIN_FOLDER%/tiny_mce/config</var> or <var>%THEMEFOLDER%/theme_name/tiny_mce/config</var> folder.
 * The naming convention for these files is use prefix the file name with the intended
 * use, e.g.
 * <ul>
 * 	<li>zenphoto-<name>.php</li>
 * 	<li>zenpage-<name>.php</li>
 * 	<li>comment-<name>.php</li>
 * </ul>
 *
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = defaultExtension(5 | ADMIN_PLUGIN);
$plugin_description = gettext("TinyMCE WYSIWYG editor");
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

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('tinymce_zenphoto', 'zenphoto-ribbon.php');
			setOptionDefault('tinymce_zenpage', 'zenpage-ribbon.php');
			setOptionDefault('tiny_mce_entity_encoding', 'raw');
		}
	}

	function getOptionsSupported() {
		global $_zp_RTL_css;
		if ($_zp_RTL_css) {
			setOption('tiny_mce_rtl_override', 1, false);
		}
		$configs_zenpage = gettinymceConfigFiles('zenpage');
		$configs_zenphoto = gettinymceConfigFiles('zenphoto');
		$options = array(
				gettext('Text editor configuration - zenphoto') => array('key' => 'tinymce_zenphoto', 'type' => OPTION_TYPE_SELECTOR,
						'order' => 0,
						'selections' => $configs_zenphoto,
						'null_selection' => gettext('Disabled'),
						'desc' => gettext('Applies to <em>admin</em> editable text other than for Zenpage pages and news articles.')),
				gettext('Text editor configuration - zenpage') => array('key' => 'tinymce_zenpage', 'type' => OPTION_TYPE_SELECTOR,
						'order' => 1,
						'selections' => $configs_zenpage,
						'null_selection' => gettext('Disabled'),
						'desc' => gettext('Applies to editing on the Zenpage <em>pages</em> and <em>news</em> tabs.')),
				gettext('Entity encoding') => array('key' => 'tiny_mce_entity_encoding', 'type' => OPTION_TYPE_SELECTOR,
						'order' => 2,
						'selections' => array(gettext('named') => 'named', gettext('numeric') => 'numeric', gettext('raw') => 'raw'),
						'desc' => gettext('Select the TinyMCE <em>entity_encoding</em> strategy.')),
				gettext('Text editor text direction') => array('key' => 'tiny_mce_rtl_override', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 3,
						'desc' => gettext('This option should be checked if your language writing direction is right-to-left')));
		return $options;
	}

	function handleOption($option, $currentValue) {

	}

}

function tinymceConfigJS($mode) {
	global $_editorconfig, $MCEskin, $MCEdirection, $MCEcss, $MCEspecial, $MCEimage_advtab, $MCEtoolbars, $MCElocale;
	$MCEskin = $MCEdirection = $MCEcss = $MCEspecial = $MCEimage_advtab = $MCEtoolbars = NULL;

	if (empty($_editorconfig)) { // only if we get here first!
		$MCElocale = 'en';
		$loc = str_replace('_', '-', getOption('locale'));
		if ($loc) {
			if (file_exists(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/langs/' . $loc . '.js')) {
				$MCElocale = $loc;
			} else {
				$loc = substr($loc, 0, 2);
				if (file_exists(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/langs/' . $loc . '.js')) {
					$MCElocale = $loc;
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