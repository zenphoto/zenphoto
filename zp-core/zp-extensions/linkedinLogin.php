<?php

/**
 *
 * The plugin provides login to ZenPhoto20 via a Linkedin OAuth protocol.
 *
 *
 * You must configure the plugin with your Linkedin Developer credentials. You will
 * need an <b><i>Client ID</i></b> as well as an <b><i>Client Secret</i></b>.
 * You can obtain these from
 * {@link https://www.linkedin.com/developer/apps/ Linkedin for developers}
 *
 * You will need to set an <i>Authorized Redirect URL</i> that
 * points to <var>%FULLWEBPATH%/%ZENFOLDER%/%PLUGIN_FOLDER%/linkedinLogin/linkedin.php</var>
 *
 * The e-mail address supplied by Linkedin OAuth will become the user's <i>user ID</i>
 * if present. If no e-mail address is supplied with the login, a user ID will be created
 * from the user's Linkedin ID. If this <i>user ID</i> does not exist as a ZenPhoto20 user,
 * a new user will be created. The user will be assigned to the group indicated by
 * the plugin's options. If <var>Notify</var> option is checked an e-mail will be sent to
 * the site administrator informing him of the new user.
 *
 * You can place a login button on your webpage by calling the function <var>flinkedinLogin::loginButton();</var>
 *
 * @author Stephen Billard (sbillard)
 * @Copyright 2017 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage users
 */
$plugin_is_filter = 900 | CLASS_PLUGIN;
$plugin_description = gettext("Handles logon via the user's <em>Linkedin</em> account.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_notice = sprintf(gettext('The PHP <var>curl</var> module is required for this plugin.'));
$plugin_disable = (extension_loaded('curl')) ? false : gettext('The PHP Curl is required.');

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/oAuthLogin.php');

$option_interface = 'linkedinLogin';

if ($plugin_disable) {
	enableExtension('linkedinLogin', 0);
} else {
	zp_register_filter('alt_login_handler', 'linkedinLogin::alt_login_handler');
	zp_register_filter('edit_admin_custom_data', 'linkedinLogin::edit_admin');
}
zp_session_start();

/**
 * Option class
 *
 */
class linkedinLogin extends oAuthLogin {

	/**
	 * Option instantiation
	 */
	function __construct() {
		global $_zp_authority;
		setOptionDefault('linkedinLogin_group', 'viewers');
		setOptionDefault('linkedinLogin_ClientID', '');
		setOptionDefault('linkedinLogin_ClientSecret', '');
	}

	/**
	 * Provides option list
	 */
	function getOptionsSupported() {
		global $_zp_authority;
		$admins = $_zp_authority->getAdministrators('groups');
		$ordered = array();
		foreach ($admins as $key => $admin) {
			if ($admin['name'] == 'group' && $admin['rights'] && !($admin['rights'] & ADMIN_RIGHTS)) {
				$ordered[$admin['user']] = $admin['user'];
			}
		}

		$options = array(gettext('Assign user to') => array('key' => 'linkedinLogin_group', 'type' => OPTION_TYPE_SELECTOR,
						'order' => 0,
						'selections' => $ordered,
						'desc' => gettext('The user group to which to map the user.')),
				gettext('App ID') => array('key' => 'linkedinLogin_ClientID', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext('This is your Linkedin Client ID.')),
				gettext('App Secret') => array('key' => 'linkedinLogin_ClientSecret', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext('This is your Linkedin Client Secret.'))
		);
		return $options;
	}

	/**
	 * Handles the custom option $option
	 * @param $option
	 * @param $currentValue
	 */
	function handleOption($option, $currentValue) {

	}

	/**
	 * Provides a list of alternate handlers for logon
	 * @param $handler_list
	 */
	static function alt_login_handler($handler_list) {
		return self::_alt_login_handler($handler_list, 'linkedinLogin', 'linkedin.php');
	}

	/**
	 * Common logon handler.
	 * Will log the user on if he exists. Otherwise it will create a user accoung and log
	 * on that account.
	 *
	 * Redirects into zenphoto on success presuming there is a redirect link.
	 *
	 * @param $user
	 * @param $email
	 * @param $name
	 * @param $redirect
	 */
	static function credentials($user, $email, $name, $redirect) {
		self::_credentials($user, $email, $name, $redirect, 'linkedinLogin');
	}

	/**
	 * Enter Admin user tab handler
	 * @param $html
	 * @param $userobj
	 * @param $i
	 * @param $background
	 * @param $current
	 * @param $local_alterrights
	 */
	static function edit_admin($html, $userobj, $i, $background, $current, $local_alterrights) {
		self::_edit_admin($html, $userobj, $i, $background, $current, $local_alterrights, 'linkedinLogin');
	}

	/**
	 * provides a login button for theme pages
	 */
	static function loginButton() {
		self::_loginButton('user_authentication.php', 'linkedinLogin');
	}

}
?>