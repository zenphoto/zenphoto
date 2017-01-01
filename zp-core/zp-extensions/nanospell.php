<?php

/**
 * This plugin adds spelling checking for tinyMCE edit windows.
 *
 * To use this feature you must download and install the <i>nanospell spellchecker for tinymce</i>
 * software from {@link http://tinymcespellcheck.com/ <b>nanospell</b>}
 *
 * Unzip the <i>nanospell</i> files into your <var>%USER_PLUGINS%</var> folder. If
 * you are operating in a language other than English you should also download the
 * appropriate dictionary(s) from the {@link http://tinymcespellcheck.com/dictionaries <b>nanospell</b> dictionaries resource}.
 *
 * Please note that <b>nanospell</b> requires a license agreement. This plugin in no
 * way exempts you from any license fees that may be required.
 *
 * Unzip the files into the <i>nanospell/dictionaries</i> subfolder.
 *
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 8 | CLASS_PLUGIN;
$plugin_description = gettext("Spellchecker for tinyMCE.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/nanospell/plugin.js')) ? false : 'You must download and install the nanospell spellchecker for tinymce—see the plugin documentation for details.';

zp_register_filter('tinymce_config', 'nanospell_spellchecker');

function nanospell_spellchecker($discard) {
	global $MCEspecial, $MCEtoolbars;
	if (!empty($MCEspecial)) {
		$MCEspecial .= ",\n";
	}
	$MCEspecial .= "\t\t" . 'external_plugins: { "nanospell": "' . WEBPATH . '/' . USER_PLUGIN_FOLDER . '/nanospell/plugin.js" },' . "\n" .
					"\t\t" . 'nanospell_server:"php"';

	if (!empty($MCEtoolbars)) {
		$bar = array_pop($MCEtoolbars);
		array_push($MCEtoolbars, $bar . ' | nanospell');
	}
	return $discard;
}

?>