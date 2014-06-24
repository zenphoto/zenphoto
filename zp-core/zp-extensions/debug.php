<?php

/*
 * Generat tab for debuging aids
 *
 * @package plugins
 * @subpackage development
 */

$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Debugging aids tab.");
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('admin_tabs', 'debug::tabs');

class debug {

	static function tabs($tabs) {
		if (!isset($tabs['debug'])) {
			$tabs['debug'] = array('text'		 => gettext("debug"),
							'link'		 => WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/debug/admin_tab.php',
							'default'	 => 'phpinfo');
		}
		$tabs['debug']['subtabs'][gettext("phpinfo")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=debug&tab=phpinfo';
		$tabs['debug']['subtabs'][gettext("HTTP accept")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=debug&tab=http';
		$tabs['debug']['subtabs'][gettext("Locales")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=debug&tab=locale';
		return $tabs;
	}

}
?>