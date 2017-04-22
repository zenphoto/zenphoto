<?php

/**
 * This plugin deals with functions that have either been altered or removed completely.
 *
 * The actual set of functions resides in a <var>deprecated-functions.php</var> script within
 * the plugins folder. (General deprecated functions are in the <var>%PLUGIN_FOLDER%/deprecated-functins</var> folder)
 *
 * Convention is that the deprecated functions script will have a class defined indicataing the following:
 *
 * <dl>
 * 	<dt><var>public static</var></dt><dd>general functions with parameters which have been deprecated.</dd>
 * 	<dt><var>static</var></dt><dd>class methods that have been deprecated.</dd>
 * 	<dt><var>final static</var></dt><dd>class methods with parameters which have been deprecated.</dd>
 * </dl>
 *
 * 	A log entry in the <var>deprecated</var> log is created if a deprecated function is invoked.
 *
 * A utility button is provided that allows you to search themes and plugins for uses of functions which have been deprecated.
 * Use it to be proactive in replacing or changing these items.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage development
 */
$plugin_description = gettext("Provides replacements for deprecated functions.");
$plugin_notice = gettext("This plugin is <strong>NOT</strong> required for the distributed code.");
$plugin_is_filter = 900 | CLASS_PLUGIN;

define('DEPRECATED_LOG', SERVERPATH . '/' . DATA_FOLDER . '/deprecated.log');

zp_register_filter('admin_tabs', 'deprecated_functions::admin_tabs', -308);
zp_register_filter('admin_tabs', 'deprecated_functions::tabs');

class deprecated_functions {

	var $listed_functions = array();
	var $unique_functions = array();

	function __construct() {

		if (OFFSET_PATH == 2) {
			//clean up the mess from previous implementation
			$sql = 'SELECT * FROM ' . prefix('options') . ' WHERE `name` LIKE "deprecated_%"';
			$result = query_full_array($sql);
			foreach ($result as $option) {
				if ($option['name'] != 'deprecated_functions_signature') {
					purgeOption($option['name']);
				}
			}
		}
		foreach (getPluginFiles('*.php') as $extension => $plugin) {
			$deprecated = stripSuffix($plugin) . '/deprecated-functions.php';
			if (file_exists($deprecated)) {
				$plugin = basename(dirname($deprecated));
				$content = file_get_contents($deprecated);
				$content = preg_replace('~#(.*)\n~', '', $content);
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

					$this->unique_functions[strtolower($function)] = $this->listed_functions[$name] = array(
							'plugin' => $plugin,
							'function' => $function,
							'class' => trim($functions[1][$key]),
							'since' => @$versions[1][$key],
							'option' => $option,
							'multiple' => array_key_exists($function, $this->unique_functions));
				}
			}
		}
	}

	static function tabs($tabs) {
		if (zp_loggedin(ADMIN_RIGHTS)) {
			if (!isset($tabs['development'])) {
				$tabs['development'] = array('text' => gettext("development"),
						'link' => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/deprecated-functions/admin_tab.php?page=development&tab=deprecated',
						'subtabs' => NULL);
			}
			$tabs['development']['subtabs'][gettext("deprecated")] = PLUGIN_FOLDER . '/deprecated-functions/admin_tab.php?page=development&tab=deprecated';
			$tabs['development']['subtabs'][gettext('Check deprecated')] = '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/deprecated-functions/check_for_deprecated.php?tab=checkdeprecated';
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

	static function notify($use) {
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

			$path = explode('/', replaceScriptPath($traces[1]['file']));
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

	static function admin_tabs($tabs) {
		return $tabs;
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
