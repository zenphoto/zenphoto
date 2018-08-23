<?php

/*
 * Debugging aids
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
 * Copyright 2014 by Stephen L Billard for use in {@link https://%GITHUB% netPhotoGraphics and derivatives}
 *
 * @package plugins/debug
 * @pluginCategory development
 */

$plugin_is_filter = 10 | ADMIN_PLUGIN;
$plugin_description = gettext("Debugging aids.");

$option_interface = 'debug';

zp_register_filter('admin_tabs', 'debug::tabs', 100);
zp_register_filter('admin_utilities_buttons', 'debug::button');

if (isset($_REQUEST['markRelease'])) {
	XSRFdefender('markRelease');
	$version = debug::version($_REQUEST['markRelease'] == 'released');
	setOption('markRelease_state', $version);
	debug::updateVersion($version);
	header('location:' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
	exitZP();
} else {
	if (!TEST_RELEASE && strpos(getOption('markRelease_state'), '-DEBUG') !== false) {
		$version = debug::version(false);
		debug::updateVersion($version);
	}
}

class debug {

	function __construct() {
		if (OFFSET_PATH == 2) {
			$list = array('404' => '404', 'DISPLAY_ERRORS' => 'DISPLAY_ERRORS');
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
			setOptionDefault('jQuery_Migrate_theme', 0);
			setOptionDefault('jQuery_Migrate_admin', 0);
			setOptionDefault('jQuery_v1', 0);
			setOptionDefault('markRelease_state', $version);
		}
	}

	function getOptionsSupported() {
		$list = array(
				gettext('Display PHP errors') => 'DISPLAY_ERRORS',
				gettext('Log 404 error processing debug information') => '404',
				gettext('Log start/finish of exif processing') => 'EXIF',
				gettext('Log the <em>EXPLAIN</em> output from SQL SELECT queries') => 'EXPLAIN_SELECTS',
				gettext('Log filter application sequence') => 'FILTERS',
				gettext('Log image processing debug information') => 'IMAGE',
				gettext('Log language selection processing') => 'LOCALE',
				gettext('Log admin saves and login attempts') => 'LOGIN',
				gettext('Log plugin load sequence') => 'PLUGINS',
				gettext('Log Feed issues') => 'FEED',
				gettext('Log Managaed Objects changes') => 'OBJECTS'
		);
		$options = array(
				NULL => array('key' => 'debug_marks', 'type' => OPTION_TYPE_CHECKBOX_ARRAYLIST,
						'checkboxes' => $list,
						'order' => 1,
						'desc' => ''),
				1 => array('key' => '', 'type' => OPTION_TYPE_NOTE, 'desc' => gettext('Note: These options are enabled only when the release is marked in <em>debug</em> mode.')),
				gettext('jQuery migration (admin)') => array('key' => 'jQuery_Migrate_admin', 'type' => OPTION_TYPE_RADIO,
						'buttons' => array(// The definition of the radio buttons to choose from and their values.
								gettext('Disabled') => 0,
								gettext('Production') => 1,
								gettext('Debug') => 2
						),
						'order' => 2,
						'desc' => gettext('Adds the <a href="https://jquery.com/upgrade-guide/3.0/">jQuery 3.3 migration</a> tool to the administrative pages.')),
				gettext('jQuery migration (theme)') => array('key' => 'jQuery_Migrate_theme', 'type' => OPTION_TYPE_RADIO,
						'buttons' => array(// The definition of the radio buttons to choose from and their values.
								gettext('Disabled') => 0,
								gettext('Production') => 1,
								gettext('Debug') => 2,
								gettext('No migration') => 3
						),
						'order' => 3,
						'desc' => gettext('Adds the <a href="https://jquery.com/upgrade-guide/">jQuery migration</a> tool to theme pages. (If <em>No migration</em> is selected jQuery v1.12 and jQuery migration v1.4.1 will be loaded instead of jQuery v3.'))
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
		if ($released) {
			return $o[0];
		} else {
			$options = '';
			$list = getSerializedArray(getOption('debug_marks'));
			sort($list);
			$options = rtrim('-DEBUG_' . implode('_', $list), '_');
			return $o[0] . $options;
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
						'default' => (zp_loggedin(ADMIN_RIGHTS)) ? 'phpinfo' : 'http',
						'rights' => DEBUG_RIGHTS);
			}
			if (zp_loggedin(ADMIN_RIGHTS)) {
				$tabs['development']['subtabs'][gettext("phpinfo")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=phpinfo';
				$tabs['development']['subtabs'][gettext("Locales")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=locale';
				$tabs['development']['subtabs'][gettext("Session")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=session';
				$tabs['development']['subtabs'][gettext("SERVER")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=server';
				$tabs['development']['subtabs'][gettext("ENV")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=env';
			}
			$tabs['development']['subtabs'][gettext("HTTP accept")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=http';
			$tabs['development']['subtabs'][gettext("Cookies")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=develpment&tab=cookie';
		}
		return $tabs;
	}

}

?>