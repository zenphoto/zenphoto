<?php

/**
 * This plugin deals with functions have been removed from mainstream Zenphoto as they have been
 * supplanted.
 * They are not maintained and they are not guaranteed to function correctly with the
 * current version of Zenphoto.
 *
 * The actual set of functions resides in a <var>deprecated-functions.php</var> script within
 * the plugins folder. (General deprecated functions are in the %PLUGIN_FOLDER%/deprecated-functins folder)
 *
 * The default settings cause an <var>E_USER_NOTICE</var> error to be generated when the function is used.
 * The text of the error message will tell you how to replace calls on the deprecated function. The error
 * message can be disabled to allow your scripts to continue to run. Visit the <i>deprecated-functions</i>
 * plugin options. Find the function and uncheck the box by the function.
 *
 * A utility button is provided that allows you to search themes and plugins for uses of functions which have been deprecated.
 * Use it to be proactive in replacing these discontinued items.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage development
 */
$plugin_description = gettext("Provides deprecated Zenphoto functions.");
$plugin_notice = gettext("This plugin is <strong>NOT</strong> required for the Zenphoto distributed functions.");
$option_interface = 'deprecated_functions';
$plugin_is_filter = 9 | CLASS_PLUGIN;

if (OFFSET_PATH == 2)
	enableExtension('deprecated-functions', $plugin_is_filter); //	Yes, I know some people will be annoyed that this keeps coming back,
//	but each release may deprecated new functions which would then just give
//	(perhaps unseen) errors. Better the user should disable this once he knows
//	his site is working.

zp_register_filter('admin_utilities_buttons', 'deprecated_functions::button');

class deprecated_functions {

	var $listed_functions = array();

	function deprecated_functions() {
		global $_internalFunctions;
		foreach (getPluginFiles('*.php') as $extension => $plugin) {
			$deprecated = stripSuffix($plugin) . '/deprecated-functions.php';
			if (file_exists($deprecated)) {
				$plugin = basename(dirname($deprecated));
				if ($plugin == 'deprecated-functions')
					$plugin = 'core';
				$content = file_get_contents($deprecated);

				preg_match_all('~@deprecated\s.*since\s.*(\d+\.\d+\.\d+)~', $content, $versions);
				preg_match_all('/([public static|static]*)\s*function\s+(.*)\s?\(.*\)\s?\{/', $content, $functions);
				foreach ($functions[2] as $key => $function) {
					setOptionDefault('deprecated_' . $plugin . '_' . $functions[1][$key] . '_' . $function, 1);
					$this->listed_functions[$function] = array('plugin' => $plugin, 'class' => $functions[1][$key], 'since' => @$versions[1][$key]);
				}
			}
		}
	}

	function getOptionsSupported() {
		$options = $deorecated = $list = array();
		foreach ($this->listed_functions as $funct => $details) {
			switch (trim($details['class'])) {
				case 'static':
					$class = '*';
					break;
				case 'public static':
					$class = '**';
					break;
				default:
					$class = '';
					break;
			}
			if ($since = $details['since'])
				$since = ' (' . $since . ')';

			$list[$funct . $class . $since] = 'deprecated_' . $details['plugin'] . '_' . $details['class'] . '_' . $funct;
		}
		$options[gettext('Functions')] = array('key'				 => 'deprecated_Function_list', 'type'			 => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => $list,
						'order'			 => 1,
						'desc'			 => gettext('Send the <em>deprecated</em> notification message if the function name is checked. Un-checking these boxes will allow you to continue using your theme without warnings while you upgrade its implementation. Functions flagged with an asterisk are class methods. Ones flagged with two asterisks have deprecated parameters.'));

		return $options;
	}

	/*
	 * used to provided deprecated function notification.
	 */

	static function notify($use) {
		$traces = @debug_backtrace();
		$fcn = $traces[1]['function'];

		if (empty($fcn) || getOption('deprecated_' . $fcn)) {
			if (empty($fcn))
				$fcn = gettext('function');
			if (!empty($use))
				$use = ' ' . $use;
			if (isset($traces[1]['file']) && isset($traces[1]['line'])) {
				$script = basename($traces[1]['file']);
				$line = $traces[1]['line'];
			} else {
				$script = $line = gettext('unknown');
			}
			trigger_error(sprintf(gettext('%1$s (called from %2$s line %3$s) is deprecated'), $fcn, $script, $line) . $use . ' ' . sprintf(gettext('You can disable this error message by going to the <em>deprecated-functions</em> plugin options and un-checking <strong>%s</strong> in the list of functions.' . '<br />'), $fcn), E_USER_WARNING);
		}
	}

	static function button($buttons) {
		$buttons[] = array(
						'category'		 => gettext('Development'),
						'enable'			 => true,
						'button_text'	 => gettext('Check deprecated use'),
						'formname'		 => 'deprecated_functions.php',
						'action'			 => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/deprecated-functions/check_for_deprecated.php',
						'icon'				 => 'images/magnify.png',
						'title'				 => gettext("Searches PHP scripts for use of deprecated functions."),
						'alt'					 => gettext('Check for update'),
						'hidden'			 => '',
						'rights'			 => ADMIN_RIGHTS
		);
		return $buttons;
	}

	static function addPluginScript() {
		global $_zp_plugin_scripts;
		if (is_array($_zp_plugin_scripts)) {
			foreach ($_zp_plugin_scripts as $script) {
				echo $script . "\n";
			}
		}
	}

}

//Load the deprecated function scripts
require_once(stripSuffix(__FILE__) . '/deprecated-functions.php');
foreach (getPluginFiles('*.php') as $extension => $plugin) {
	$deprecated = stripSuffix($plugin) . '/deprecated-functions.php';
	if (file_exists($deprecated)) {
		require_once($deprecated);
	}
}
?>
