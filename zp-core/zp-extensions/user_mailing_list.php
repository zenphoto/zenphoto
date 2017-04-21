<?php

/**
 *
 * A tool to send e-mails to all registered users who have provided an e-mail address.
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 * @subpackage users
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Provides a utility function to send e-mails to all users who have provided an e-mail address.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";

$option_interface = 'user_mailing_list';

if (zp_loggedin(ADMIN_RIGHTS)) {
	zp_register_filter('admin_tabs', 'user_mailing_list::admin_tabs', -1300);
}

class user_mailing_list {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('user_mailing_list_pace', 10);
		}
	}

	function getOptionsSupported() {
		$options = array(gettext('Delay between sending.') => array('key' => 'user_mailing_list_pace', 'type' => OPTION_TYPE_NUMBER,
						'order' => 1,
						'desc' => gettext('The time in seconds to delay between sending mails.')));
		return $options;
	}

	function handleOption($option, $currentValue) {

	}

	static function admin_tabs($tabs) {
		$tabs['admin']['subtabs'][gettext('Mailing list')] = '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/user_mailing_list/user_mailing_listTab.php?tab=mailinglist';
		return $tabs;
	}

}

?>