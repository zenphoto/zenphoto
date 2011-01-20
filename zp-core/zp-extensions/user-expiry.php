<?php
/**
 * Presents users in "chronological" order for
 *
 * @package plugins
 */

// force UTF-8 Ø

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Provides rudimentary user groups.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';
$plugin_URL = "http://www.zenphoto.org/documentation/plugins/_".PLUGIN_FOLDER."---user-expiry.php.html";

$option_interface = 'user_expiry';

zp_register_filter('admin_tabs', 'user_expiry_admin_tabs', 99);

/**
 * Option handler class
 *
 */
class user_expiry {
	/**
	 * class instantiation function
	 *
	 */
	function user_expiry() {
		setOptionDefault('user_expiry_interval', 365);
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return  array(	gettext('Days until expiration') => array('key' => 'user_expiry_interval', 'type' => OPTION_TYPE_TEXTBOX,
												'desc' => gettext('The number of days until a user is flagged as expired.'))
		);
	}

	function handleOption($option, $currentValue) {
	}
}

function user_expiry_admin_tabs($tabs, $current) {
	if ((zp_loggedin(ADMIN_RIGHTS))) {
		if (isset($tabs['users']['subtabs'])) {
			$subtabs = $tabs['users']['subtabs'];
		} else {
			$subtabs = array();
		}
		$subtabs[gettext('users')] = 'admin-users.php?page=users&amp;tab=users';
		$subtabs[gettext('expiry')] = PLUGIN_FOLDER.'/user-expiry/user-expiry-tab.php?page=users&amp;tab=expiry';
		$tabs['users'] = array(	'text'=>gettext("admin"),
														'link'=>WEBPATH."/".ZENFOLDER.'/admin-users.php?page=users&amp;tab=users',
														'subtabs'=>$subtabs,
														'default'=>'users');
	}
	return $tabs;
}

?>