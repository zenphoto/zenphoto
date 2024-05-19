<?php

/**
 * This plugin deals with functions that have either been altered* or removed** from mainstream Zenphoto completely.
 * Altered functions have deprecated parameters.
 * Removed functions are not maintained and they are not guaranteed to function correctly with the
 * current version of Zenphoto.
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
 * Use it to be proactive in replacing or changing these items.
 * @deprecated 2.0 
 * @author Stephen Billard (sbillard)
 * @package zpcore\plugins\deprecatedfunctions
 */
$plugin_description = gettext("Provides deprecated Zenphoto functions.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_notice = gettext("This plugin is <strong>NOT</strong> required for the Zenphoto distributed functions.");
$plugin_category = gettext('Development');
$plugin_deprecated = true;
$plugin_is_filter = 900 | CLASS_PLUGIN;

if (OFFSET_PATH == 2)
	enableExtension('deprecated-functions', $plugin_is_filter); //	Yes, I know some people will be annoyed that this keeps coming back,
//	but each release may deprecated new functions which would then just give
//	(perhaps unseen) errors. Better the user should disable this once he knows
//	his site is working.
zp_register_filter('admin_utilities_buttons', 'deprecated_functions::button');
zp_register_filter('admin_tabs', 'deprecated_functions::tabs');

/**
 * @deprecated 2.0 
 */
class deprecated_functions {

	public $listed_functions = array();
	public $unique_functions = array();

	/**
	 * @deprecated 2.0  
	 */
	function __construct() {
		foreach (getPluginFiles('*.php') as $extension => $plugin) {
			$deprecated = stripSuffix($plugin) . '/deprecated-functions.php';
			if (file_exists($deprecated)) {
				$plugin = basename(dirname($deprecated));
				$content = file_get_contents($deprecated);
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
					$this->unique_functions[strtolower($function)] = $this->listed_functions[$name] = array(
							'plugin' => $plugin,
							'function' => $function,
							'class' => trim($functions[1][$key]),
							'since' => @$versions[1][$key],
							'option' => '',
							'multiple' => array_key_exists($function, $this->unique_functions));
				}
			}
		}
	}

	/**
	 * @deprecated 2.0 
	 * @param type $tabs
	 * @return type
	 */
	static function tabs($tabs) {
		if (zp_loggedin(ADMIN_RIGHTS)) {
			if (!isset($tabs['development'])) {
				$tabs['development'] = array(
						'text' => gettext("development"),
						'subtabs' => NULL);
			}
			$tabs['development']['subtabs'][gettext("deprecated")] = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/deprecated-functions/admin_tab.php?page=deprecated&tab=' . gettext('deprecated');
			$named = array_flip($tabs['development']['subtabs']);
			sortArray($named);
			$tabs['development']['subtabs'] = $named = array_flip($named);
			$tabs['development']['link'] = array_shift($named);
		}
		return $tabs;
	}

	/**
	 * used to provided deprecated function notification.
	 * @deprecated 2.0
	 */
	static function notify($use) {
		deprecationNotice(gettext('Use deprecationNotice() instead'));
		deprecationNotice($use);
	}

	/**
	 * @deprecated 2.0  
	 * @param type $buttons
	 * @return string
	 */
	static function button($buttons) {
		$buttons[] = array(
				'category' => gettext('Development'),
				'enable' => true,
				'button_text' => gettext('Check deprecated use'),
				'formname' => 'deprecated_functions_check',
				'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/deprecated-functions/check_for_deprecated.php',
				'icon' => 'images/magnify.png',
				'title' => gettext("Searches PHP scripts for use of deprecated functions."),
				'alt' => gettext('Check for update'),
				'hidden' => '',
				'rights' => ADMIN_RIGHTS
		);
		return $buttons;
	}

	/**
	 * @deprecated 2.0 
	 * @global type $_zp_plugin_scripts
	 */
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