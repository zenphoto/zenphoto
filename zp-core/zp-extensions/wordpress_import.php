<?php

/**
 *
 * This imports Wordpress pages, posts, categories and comments to Zenpage
 *
 * NOTE: Requires MySQLi enabled as the database handler.
 *
 * @author Malte Müller (acrylian) made plugin compliant by Stephen Billard
 * @package plugins
 * @subpackage development
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Import Wordpress pages, posts, categories, and comments to Zenpage.");
$plugin_author = "Malte Müller (acrylian)";

zp_register_filter('admin_tabs', 'wordpress_import_admin_tabs');

function wordpress_import_admin_tabs($tabs) {
	if (zp_loggedin(ADMIN_RIGHTS)) {
		if (!isset($tabs['development'])) {
			$tabs['development'] = array('text' => gettext("development"),
					'link' => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/wordpress_import/admin_tab.php?tab=wordpress',
					'subtabs' => NULL);
		}
		$tabs['development']['subtabs'][gettext("wordpress importer")] = PLUGIN_FOLDER . '/wordpress_import/admin_tab.php?tab=wordpress';
	}
	return $tabs;
}

?>