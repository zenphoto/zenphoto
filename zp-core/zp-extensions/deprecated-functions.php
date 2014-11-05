<?php

/**
 * This plugin deals with functions have been removed from the mainstream as they have been
 * supplanted.
 * They are not maintained and they are not guaranteed to function correctly with the
 * current version.
 *
 * The actual set of functions resides in a <var>deprecated-functions.php</var> script within
 * the plugins folder. (General deprecated functions are in the %PLUGIN_FOLDER%/deprecated-functins folder)
 *
 * Convention is that the deprecated functions script will have a class defined for containing the following:
 *
 * <ul>
 * <li>general functions with parameters which have been deprecated: these are declared <var>public static</var></li>
 * <li>class methods that have been deprecated: these are declared <var>static</var></li>
 * <li>clas methods with parameters which have been deprecated: these are declared <var>final static</var></li>
 * </ul>
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
 *
 * @package plugins
 * @subpackage development
 */
$plugin_description = gettext("Provides replacements for deprecated functions.");
$plugin_notice = gettext("This plugin is <strong>NOT</strong> required for the distributed code.");
$option_interface = 'deprecated_functions';
$plugin_is_filter = 900 | CLASS_PLUGIN;

define('DEPRECATED_LOG', SERVERPATH . '/' . DATA_FOLDER . '/deprecated.log');

if (OFFSET_PATH == 2) {
	$deprecated = new deprecated_functions();
	$listed = $deprecated->listed_functions;
	if (empty($listed)) {
		enableExtension('deprecated-functions', 0);
	}
}

zp_register_filter('admin_utilities_buttons', 'deprecated_functions::button');
zp_register_filter('admin_tabs', 'deprecated_functions::tabs');

class deprecated_functions {

	var $listed_functions = array();
	var $unique_functions = array();

	function __construct() {
		global $_internalFunctions;
		foreach (getPluginFiles('*.php') as $extension => $plugin) {
			$deprecated = stripSuffix($plugin) . '/deprecated-functions.php';
			if (file_exists($deprecated)) {
				$plugin = basename(dirname($deprecated));
				$content = preg_replace('~#.*function~', '', file_get_contents($deprecated)); //	remove the comments!
				preg_match_all('~@deprecated\s+.*since\s+.*(\d+\.\d+\.\d+)~', $content, $versions);
				preg_match_all('/([public static|static]*)\s*function\s+(.*)\s?\(.*\)\s?\{/', $content, $functions);
				if ($plugin == 'deprecated-functions') {
					$plugin = 'core';
					$suffix = '';
				} else {
					$suffix = ' (' . $plugin . ')';
				}
				foreach ($functions[2] as $key => $function) {

					if ($functions[1][$key]) {
						$flag = '_method';
						$star = '*';
					} else {
						$star = $flag = '';
					}
					$name = $function . $star . $suffix;
					$option = 'deprecated_' . $plugin . '_' . $function . $flag;

					setOptionDefault($option, 1);
					$this->unique_functions[strtolower($function)] = $this->listed_functions[$name] = array(
									'plugin'	 => $plugin,
									'function' => $function,
									'class'		 => trim($functions[1][$key]),
									'since'		 => @$versions[1][$key],
									'option'	 => $option,
									'multiple' => array_key_exists($function, $this->unique_functions));
				}
			}
		}
	}

	function getOptionsSupported() {
		$options = $deorecated = $list = array();
		foreach ($this->listed_functions as $funct => $details) {
			$list[$funct] = $details['option'];
		}

		$options[gettext('Functions')] = array('key'				 => 'deprecated_Function_list', 'type'			 => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => $list,
						'order'			 => 1,
						'desc'			 => gettext('Send the <em>deprecated</em> notification message if the function name is checked. Un-checking these boxes will allow you to continue using your theme without warnings while you upgrade its implementation. Functions flagged with an asterisk are class methods. Ones flagged with two asterisks have deprecated parameters.'));

		return $options;
	}

	static function tabs($tabs) {
		if (zp_loggedin(ADMIN_RIGHTS)) {
			if (!isset($tabs['development'])) {
				$tabs['development'] = array('text'		 => gettext("development"),
								'subtabs'	 => NULL);
			}
			$tabs['development']['subtabs'][gettext("deprecated")] = PLUGIN_FOLDER . '/deprecated-functions/admin_tab.php?page=development&tab=' . gettext('deprecated');
			$named = array_flip($tabs['development']['subtabs']);
			natcasesort($named);
			$tabs['development']['subtabs'] = $named = array_flip($named);
			$link = array_shift($named);
			if (strpos($link, '/') !== 0) { // zp_core relative
				$tabs['development']['link'] = WEBPATH . '/' . ZENFOLDER . '/' . $link;
			} else {
				$tabs['development']['link'] = WEBPATH . $link;
			}
		}
		return $tabs;
	}

	/**
	 * log writer
	 * @param type $msg
	 */
	static function log($msg) {
		global $debug, $_zp_mutex, $chmod;
		if (is_object($_zp_mutex))
			$_zp_mutex->lock();
		$f = fopen(DEPRECATED_LOG, 'a');
		if ($f) {
			fwrite($f, strip_tags($msg) . "\n");
			fclose($f);
			clearstatcache();
		}
		if (is_object($_zp_mutex))
			$_zp_mutex->unlock();
	}

	/*
	 * used to provided deprecated function notification.
	 */

	static function

	notify($use) {
		$traces = @debug_backtrace();
		$fcn = $traces[1]['function'];
		if (empty($fcn))
			$fcn = gettext('function');
		if (!empty($use))
			$use = ' ' . $use;
//get the container folder
		if (isset($traces[0]['file']) && isset($traces[0]['line'])) {
			$script = basename(dirname($traces[0]['file']));
		} else {
			$script = 'unknown';
		}
		if ($script == 'deprecated-functions') {
			$plugin = 'core';
		} else {
			$plugin = $script;
		}
		if (isset($traces[1]['file']) && isset($traces[1]['line'])) {

			$path = explode('/', str_replace(SERVERPATH . '/', '', trim(str_replace('\\', '/', $traces[1]['file']), '/')));
			switch (array_shift($path)) {
				case THEMEFOLDER:
					$script = sprintf(gettext('theme %1$s:%2$s'), array_shift($path), array_pop($path));
					break;
				case USER_PLUGIN_FOLDER:
					$script = sprintf(gettext('user plugin %1$s:%2$s'), array_shift($path), array_pop($path));
					break;
				case PLUGIN_FOLDER:
					$script = sprintf(gettext('standard plugin %1$s:%2$s'), array_shift($path), array_pop($path));
					break;
				default:
					$script = sprintf(gettext('core:%s'), array_pop($path));
					break;
			}
			$line = $traces[1]['line'];
		} else {
			$script = $line = gettext('unknown');
		}
		$output = sprintf(gettext('%1$s (called from %2$s line %3$s) is deprecated.'), $fcn, $script, $line) . "\n" . $use . "\n";

		if (file_exists(DEPRECATED_LOG)) {
			$content = file_get_contents(DEPRECATED_LOG);
			$log = !preg_match('~' . preg_quote($output) . '~', $content);
		} else {
			$log = true;
		}
		if ($log) {
			if (@$traces[1]['class']) {
				$flag = '_method';
			} else {
				$flag = '';
			}

			$prefix = '  ';
			$line = '';
			$caller = '';
			foreach ($traces as $b) {
				$caller = (isset($b['class']) ? $b['class'] : '') . (isset($b['type']) ? $b['type'] : '') . $b['function'];
				if (!empty($line)) { // skip first output to match up functions with line where they are used.
					$prefix .= '  ';
					$output .= 'from ' . $caller . ' (' . $line . ")\n" . $prefix;
				} else {
					$output .= '  ' . $caller . " called ";
				}
				$date = false;
				if (isset($b['file']) && isset($b['line'])) {
					$line = basename($b['file']) . ' [' . $b['line'] . "]";
				} else {
					$line = 'unknown';
				}
			}
			if (!empty($line)) {
				$output .= 'from ' . $line;
			}
			self::log($output);
		}
	}

	static function button($buttons) {
		$buttons[] = array(
						'category'		 => gettext('Development'),
						'enable'			 => true,
						'button_text'	 => gettext('Check deprecated use'),
						'formname'		 => 'deprecated_functions_check.php',
						'action'			 => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/deprecated-functions/check_for_deprecated.php',
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
	unset($deprecated);
}
?>
