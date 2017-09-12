<?php

/*
 * Debugging aids for ZenPhoto20
 *
 * <b><i>Mark release</i> button:</b>
 *
 * This button is placed in the <i>Development</i> section of
 * admin utilities. It button inserts or removes the qualifiers from the version file
 * so that the install is switched between a <i>normal</i> install and a debugging one.
 * Options are provided that control which debugging options are enabled.
 *
 * <b>Debugging aids tabs:</b>
 *
 * Adds <i>Debug</i> tab with subtabs for:
 * <dl>
 * 	<dt><var>PHP info</var></dt><dd>displays the output from the PHP <var>php phpinfo()</var> function.</dd>
 * 	<dt><var>Locales</var></dt><dd>displays information about server supported <i>locales</i>.</dd>
 * 	<dt><var>Sessions</var></dt><dd>displays the content of the PHP <var>_SESSIONS()</var> variable.</dd>
 * 	<dt><var>HTTP Accept</var></dt><dd>displays language preferences of your browser.</dd>
 * 	<dt><var>Cookies</var></dt><dd>displays your browser <i>cookies</i>.</dd>
 * </dl>
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage development
 */

$plugin_is_filter = 10 | ADMIN_PLUGIN;
$plugin_description = gettext("Debugging aids.");
$plugin_author = "Stephen Billard (sbillard)";
$option_interface = 'debug';

zp_register_filter('admin_tabs', 'debug::tabs');
zp_register_filter('admin_utilities_buttons', 'debug::button');

if (OFFSET_PATH == 2) {
	if (strpos(getOption('markRelease_state'), '-DEBUG') !== false) {
		$version = debug::version(false);
		debug::updateVersion($version);
	}
} else if (isset($_REQUEST['markRelease'])) {
	XSRFdefender('markRelease');
	$version = debug::version($_REQUEST['markRelease'] == 'released');
	setOption('markRelease_state', $version);
	debug::updateVersion($version);
	header('location:' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
	exitZP();
}

class debug {

	function __construct() {
		if (OFFSET_PATH == 2) {
			$list = array('404' => '404');
			$options = getOptionsLike('debug_mark_');
			foreach ($options as $option => $value) {
				if ($value) {
					$object = strtoupper(str_replace('debug_mark_', '', $option));
					$list[$object] = $object;
				}
				purgeOption($option);
			}
			setOptionDefault('debug_marks', serialize($list));

			$version = debug::version(true);
			setOptionDefault('markRelease_state', $version);
		}
	}

	function getOptionsSupported() {
		$list = array(
				gettext('Log 404 error processing debug information.') => '404',
				gettext('Log start/finish of exif processing.') => 'EXIF',
				gettext('Log the <em>EXPLAIN</em> output from SQL SELECT queries.') => 'EXPLAIN_SELECTS',
				gettext('Log filter application sequence.') => 'FILTERS',
				gettext('Log image processing debug information.') => 'IMAGE',
				gettext('Log language selection processing.') => 'LOCALE,',
				gettext('Log admin saves and login attempts.') => 'LOGIN',
				gettext('Log plugin load sequence.') => 'PLUGINS'
		);
		$options = array(
				NULL => array('key' => 'debug_marks', 'type' => OPTION_TYPE_CHECKBOX_ARRAYLIST,
						'checkboxes' => $list,
						'order' => 1,
						'desc' => ''),
				1 => array('key' => '', 'type' => OPTION_TYPE_NOTE, 'desc' => gettext('Note: These options are enabled only when the release is marked in <em>debug</em> mode.'))
		);
		return $options;
	}

	function handleOptionSave($themename, $themealbum) {
		$version = self::version(false);
		if (TEST_RELEASE && ZENPHOTO_VERSION != $version) {
			self::updateVersion($version);
		}
	}

	static function updateVersion($version) {
		$v = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/version.php');
		$version = "define('ZENPHOTO_VERSION', '$version');\n";
		$v = preg_replace("~define\('ZENPHOTO_VERSION.*\n~", $version, $v);
		file_put_contents(SERVERPATH . '/' . ZENFOLDER . '/version.php', $v);
	}

	static function version($released) {
		$o = explode('-', ZENPHOTO_VERSION . '-');
		for ($i = count($o) - 1; $i > 0; $i--) {
			if (strpos($o[$i], 'RC') === false) {
				unset($o[$i]);
			}
		}
		$originalVersion = implode('-', $o);
		if ($released) {
			return $originalVersion;
		} else {
			$options = '';
			$list = getSerializedArray(getOption('debug_marks'));
			sort($list);
			$options = rtrim('-DEBUG_' . implode('_', $list), '_');
			return $originalVersion . $options;
		}
	}

	static function button($buttons) {
		$text = array('released' => gettext('released'), 'debug' => gettext('debug'));
		if (TEST_RELEASE) {
			$mark = '-DEBUG';
			$action = 'released';
		} else {
			$mark = '';
			$action = 'debug';
		}

		$buttons[] = array(
				'category' => gettext('Development'),
				'enable' => true,
				'button_text' => gettext('Mark release'),
				'formname' => 'markRelease_button',
				'action' => '?markRelease=' . $action,
				'icon' => $mark ? BULLSEYE_GREEN : BULLSEYE_RED,
				'title' => sprintf(gettext('Edits the version.php file making a “%s” install.'), $text[$action]),
				'alt' => '',
				'hidden' => '<input type="hidden" name="markRelease" value="' . $action . '" />',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'markRelease'
		);
		return $buttons;
	}

	static function tabs($tabs) {
		if (zp_loggedin(DEBUG_RIGHTS)) {
			if (!isset($tabs['development'])) {
				$tabs['development'] = array('text' => gettext("development"),
						'link' => WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/debug/admin_tab.php',
						'rights' => DEBUG_RIGHTS);
			}
			if (zp_loggedin(ADMIN_RIGHTS)) {
				$tabs['development']['default'] = 'phpinfo';
				$tabs['development']['subtabs'][gettext("phpinfo")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=phpinfo';
				$tabs['development']['subtabs'][gettext("Locales")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=locale';
				$tabs['development']['subtabs'][gettext("Session")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=session';
				$tabs['development']['subtabs'][gettext("SERVER")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=server';
				$tabs['development']['subtabs'][gettext("ENV")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=env';
			} else {
				$tabs['development']['default'] = 'cookie';
			}
			$tabs['development']['subtabs'][gettext("HTTP accept")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=http';
			$tabs['development']['subtabs'][gettext("Cookies")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=cookie';
		}
		return $tabs;
	}

}

?>