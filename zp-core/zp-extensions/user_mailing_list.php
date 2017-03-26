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

zp_register_filter('admin_utilities_buttons', 'user_mailing_list::button');

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

	static function button($buttons) {
		global $_zp_authority, $_zp_current_admin_obj;
		$button = array(
				'category' => gettext('Admin'),
				'enable' => false,
				'button_text' => gettext('User mailing list'),
				'formname' => 'user_mailing_list.php',
				'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/user_mailing_list/user_mailing_listTab.php',
				'icon' => 'images/icon_mail.png',
				'title' => gettext('There are no other registered users who have provided an e-mail address.'),
				'alt' => '',
				'hidden' => '',
				'rights' => ADMIN_RIGHTS
		);
		$currentadminuser = $_zp_current_admin_obj->getUser();
		$admins = $_zp_authority->getAdministrators();
		foreach ($admins as $admin) {
			if (!empty($admin['email']) && $currentadminuser != $admin['user']) {
				$button['enable'] = true;
				$button['title'] = gettext('A tool to send e-mails to all registered users who have provided an e-mail address.');
				break;
			}
		}
		$buttons[] = $button;
		return $buttons;
	}

}
?>