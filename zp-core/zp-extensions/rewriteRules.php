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
		$tabs['rewrite'] = array('text'		 => gettext("Rewrite Rules"),
						'link'		 => WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/rewriteRules/admin_tab.php?page=deprecated&amp;tab=' . gettext('rewrite'),
						'subtabs'	 => NULL);
		return $tabs;
	}

}

?>