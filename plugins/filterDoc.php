<?php

/* Generates doc file for filters
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage development
 * @category package
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('Generates and displays a Doc file for filters.');
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('admin_utilities_buttons', 'filterDoc_button');
if (file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/filterDoc/filter list_index.html')) {
	zp_register_filter('admin_tabs', 'filterDoc_tabs');
}

function filterDoc_tabs($tabs) {
	if (zp_loggedin(ADMIN_RIGHTS)) {
		if (!isset($tabs['development'])) {
			$tabs['development'] = array('text' => gettext("development"),
					'link' => WEBPATH . '/' . USER_PLUGIN_FOLDER . '/filterDoc/admin_tab.php?page=development&tab=filters',
					'subtabs' => NULL);
		}
		$tabs['development']['subtabs'][gettext("filters")] = '/' . USER_PLUGIN_FOLDER . '/filterDoc/admin_tab.php?page=development&tab=filters';
	}
	return $tabs;
}

function filterDoc_button($buttons) {
	if (isset($_REQUEST['filterDoc'])) {
		XSRFdefender('filterDoc');
		include (SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/filterDoc/process.php');
		processFilters();
	}
	$buttons[] = array(
			'category' => gettext('Development'),
			'enable' => true,
			'button_text' => gettext('Filter Doc Gen'),
			'formname' => 'filterDoc_button',
			'action' => '?filterDoc=gen',
			'icon' => PLUS_ICON,
			'title' => gettext('Generate filter document'),
			'alt' => '',
			'hidden' => '<input type="hidden" name="filterDoc" value="gen" />',
			'rights' => ADMIN_RIGHTS,
			'XSRFTag' => 'filterDoc'
	);
	return $buttons;
}

?>