<?php

/**
 * SEO file/folder name clenser
 *
 * This plugin will scan your images and albums for file/folder names that are not <i>SEO friendly</i>.
 * It will rename those that found needing improvement replacing offending characters with friendly equivalents.
 *
 * Note: Clicking the button causes this process to execute. There is no <i>undo</i>.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage seo
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Provides a utility SEO file/folder name clenser.");
$plugin_author = "Stephen Billard (sbillard)";

if (zp_loggedin(ADMIN_RIGHTS)) {
	zp_register_filter('admin_tabs', 'seo_cleanup_admin_tabs', -1900);
}

function seo_cleanup_admin_tabs($tabs) {
	$tabs['admin']['subtabs'][gettext('SEO cleaner')] = '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/seo_cleanup/admin_tab.php?tab=seocleaner&XSRFToken=' . getXSRFToken('seo_cleanup');
	return $tabs;
}

?>
