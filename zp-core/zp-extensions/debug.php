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

if (isset($_REQUEST['markRelease'])) {
	XSRFdefender('markRelease');
	$version = debug::version($_REQUEST['markRelease'] == 'released');
	debug::updateVersion($version);
	header('location:' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
	exitZP();
}

class debug {

	function __construct() {
		setOptionDefault('debug_mark_404', true);
	}

	function getOptionsSupported() {
		$options = array(gettext('Debuging options') => array(
										'key'				 => 'galleryArticles_items', 'type'			 => OPTION_TYPE_CHECKBOX_ARRAY,
										'order'			 => 1,
										'checkboxes' => array(
														'404'							 => 'debug_mark_404',
														'ERROR'						 => 'debug_mark_ERROR',
														'EXIF'						 => 'debug_mark_EXIF',
														'EXPLAIN_SELECTS'	 => 'debug_mark_EXPLAIN_SELECTS',
														'FILTERS'					 => 'debug_mark_FILTERS',
														'IMAGE'						 => 'debug_mark_IMAGE',
														'LOCALE'					 => 'debug_mark_LOCALE',
														'LOGIN'						 => 'debug_mark_LOGIN',
														'PLUGINS'					 => 'debug_mark_PLUGINS'
										),
										'desc'			 => gettext('Select the debug options to enable.')
						)
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
			$list = getOptionsLike('debug_mark_');
			ksort($list);
			foreach ($list as $option => $value) {
				if ($value) {
					$options .= strtoupper(substr($option, 10));
				}
			}
			return "$originalVersion-DEBUG$options";
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
						'category'		 => gettext('Development'),
						'enable'			 => true,
						'button_text'	 => gettext('Mark release'),
						'formname'		 => 'markRelease_button',
						'action'			 => '?markRelease=' . $action,
						'icon'				 => $mark ? 'images/comments-on.png' : 'images/comments-off.png',
						'title'				 => sprintf(gettext('Edits the version.php file making a “%s” install.'), $text[$action]),
						'alt'					 => '',
						'hidden'			 => '<input type="hidden" name="markRelease" value="' . $action . '" />',
						'rights'			 => ADMIN_RIGHTS,
						'XSRFTag'			 => 'markRelease'
		);
		return $buttons;
	}

	static function tabs($tabs) {
		if (zp_loggedin(DEBUG_RIGHTS)) {
			if (!isset($tabs['development'])) {
				$tabs['debug'] = array('text'	 => gettext("development"),
								'link'	 => WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/debug/admin_tab.php',
								'rights' => DEBUG_RIGHTS);
			}
			if (zp_loggedin(ADMIN_RIGHTS)) {
				$tabs['development']['default'] = 'phpinfo';
				$tabs['development']['subtabs'][gettext("phpinfo")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=debug&tab=phpinfo';
				$tabs['development']['subtabs'][gettext("Locales")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=debug&tab=locale';
				$tabs['development']['subtabs'][gettext("Session")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=debug&tab=session';
			} else {
				$tabs['development']['default'] = 'cookie';
			}
			$tabs['development']['subtabs'][gettext("HTTP accept")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=debug&tab=http';
			$tabs['development']['subtabs'][gettext("Cookies")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=debug&tab=cookie';
		}
		return $tabs;
	}

}

?>