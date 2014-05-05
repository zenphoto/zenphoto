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
		if (!isset($tabs['development'])) {
			$tabs['development'] = array('text'		 => gettext("development"),
							'subtabs'	 => NULL);
		}
		$tabs['development']['subtabs'][gettext("rewrite")] = PLUGIN_FOLDER . '/rewriteRules/admin_tab.php?page=development&amp;tab=' . gettext('rewrite');
		$named = array_flip($tabs['development']['subtabs']);
		natcasesort($named);
		$tabs['development']['subtabs'] = $named = array_flip($named);
		$tabs['development']['link'] = array_shift($named);
		return $tabs;
	}

}

?>