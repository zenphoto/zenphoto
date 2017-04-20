<?php

/*
 * List the site rewrite rules
 *
 * This plugin creates an development tab that lists the rewrite rules as "active". That is the rules will
 * have had all definitions replaced with the definition value so that the rule\
 * is shown in the state in which it is applied.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage development
 */

$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Site rewrite rules subtab.");
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('admin_tabs', 'rewriteRules::tabs');

class rewriteRules {

	static function tabs($tabs) {
		if (zp_loggedin(ADMIN_RIGHTS)) {
			if (!isset($tabs['development'])) {
				$tabs['development'] = array('text' => gettext("development"),
						'link' => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/rewriteRules/admin_tab.php?page=development&tab=rewrite',
						'subtabs' => NULL);
			}
			$tabs['development']['subtabs'][gettext("rewrite")] = PLUGIN_FOLDER . '/rewriteRules/admin_tab.php?page=development&tab=rewrite';
		}
		return $tabs;
	}

}

?>