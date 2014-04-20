<?php

/*
 * List the site rewrite rules
 *
 * This plugin creates an admin tab that lists the rewrite rules as "active". That is the rules will
 * have had all definitions replaced with the definition value so that the rule\
 * is shown in the state in which it is applied.
 *
 * @package plugins
 * @subpackage development
 */

$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Site rewrite rules tab.");
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('admin_tabs', 'rewriteRules::tabs');

class rewriteRules {

	static function tabs($tabs) {
		$mylink = PLUGIN_FOLDER . '/rewriteRules/admin_tab.php?page=development&amp;tab=' . gettext('rewrite');
		if (!isset($tabs['development'])) {
			$tabs['development'] = array('text'		 => gettext("Development"),
							'link'		 => WEBPATH . "/" . ZENFOLDER . '/' . $mylink,
							'subtabs'	 => NULL);
		}
		$tabs['development']['subtabs'][gettext("rewrite")] = $mylink;
		return $tabs;
	}

}

?>