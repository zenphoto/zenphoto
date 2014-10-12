<?php

/*
 * Generate tab for debuging aids
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
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
		if (zp_loggedin(DEBUG_RIGHTS)) {
			if (!isset($tabs['debug'])) {
				$tabs['debug'] = array('text'	 => gettext("debug"),
								'link'	 => WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/debug/admin_tab.php',
								'rights' => DEBUG_RIGHTS);
			}
			if (zp_loggedin(ADMIN_RIGHTS)) {
				$tabs['debug']['default'] = 'phpinfo';
				$tabs['debug']['subtabs'][gettext("phpinfo")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=debug&tab=phpinfo';
				$tabs['debug']['subtabs'][gettext("Locales")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=debug&tab=locale';
				$tabs['debug']['subtabs'][gettext("Session")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=debug&tab=session';
			} else {
				$tabs['debug']['default'] = 'cookie';
			}
			$tabs['debug']['subtabs'][gettext("HTTP accept")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=debug&tab=http';
			$tabs['debug']['subtabs'][gettext("Cookies")] = PLUGIN_FOLDER . '/debug/admin_tab.php?page=debug&tab=cookie';
		}
		return $tabs;
	}

}

?>