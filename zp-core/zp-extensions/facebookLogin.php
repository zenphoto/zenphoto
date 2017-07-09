<?php

/**
 *
 * The plugin provides login to ZenPhoto20 via a Facebook OAuth protocol.
 *
 *
 * You must configure the plugin with your Facebook Developer credentials. You will
 * need an <b><i>App ID</i></b> as well as an <b><i>App Secret</i></b>.
 * You can obtain these from
 * {@link https://developers.facebook.com/apps/ Facebook for developers}
 *
 * Your <i>APP Client OAuth Settings</i> will need a <i>Valid OAuth redirect URI</i> that
 * points to <var>%FULLWEBPATH%/%ZENFOLDER%/%PLUGIN_FOLDER%/facebookLogin/fbconfig.php</var>
 *
 * The e-mail address supplied by Facebook OAuth will become the user's <i>user ID</i>
 * if present. If no e-mail address is supplied with the login, a user ID will be created
 * from the user's Facebook ID. If this <i>user ID</i> does not exist as a ZenPhoto20 user,
 * a new user will be created. The user will be assigned to the group indicated by
 * the plugin's options. If <var>Notify</var> option is checked an e-mail will be sent to
 * the site administrator informing him of the new user.
 *
 * You can place a login button on your webpage by calling the function <var>facebookLogin::loginButton();</var>
 *
 * @author Stephen Billard (sbillard)
 * @Copyright 2017 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage users
 */
$plugin_is_filter = 900 | CLASS_PLUGIN;
$plugin_description = gettext("Handles logon via the user's <em>Facebook</em> account.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_notice = sprintf(gettext('The PHP <var>curl</var> module is required for this plugin.'));
$plugin_disable = (extension_loaded('curl')) ? false : gettext('The PHP Curl is required.');

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/oAuthLogin.php');

$option_interface = 'facebookLogin';

if ($plugin_disable) {
	enableExtension('facebookLogin', 0);
} else {
	zp_register_filter('alt_login_handler', 'facebookLogin::alt_login_handler');
	zp_register_filter('edit_admin_custom_data', 'facebookLogin::edit_admin');
}
zp_session_start();

/**
 * Option class
 *
 */
class facebookLogin extends oAuthLogin {

	/**
	 * Option instantiation
	 */
	function __construct() {
		global $_zp_authority;
		setOptionDefault('facebookLogin_group', 'viewers');
		setOptionDefault('facebookLogin_APPID', '');
		setOptionDefault('facebookLogin_APPSecret', '');
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

		$options = array(gettext('Assign user to') => array('key' => 'facebookLogin_group', 'type' => OPTION_TYPE_SELECTOR,
						'order' => 0,
						'selections' => $ordered,
						'desc' => gettext('The user group to which to map the user.')),
				gettext('App ID') => array('key' => 'facebookLogin_APPID', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext('This is your Facebook App ID.')),
				gettext('App Secret') => array('key' => 'facebookLogin_APPSecret', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext('This is your Facebook App Secret.'))
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
		return self::_alt_login_handler($handler_list, 'facebookLogin', 'fbconfig.php');
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
		self::_credentials($user, $email, $name, $redirect, 'facebookLogin');
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
		self::_edit_admin($html, $userobj, $i, $background, $current, $local_alterrights, 'facebookLogin');
	}

	/**
	 * provides a login button for theme pages
	 */
	static function loginButton() {
		self::_loginButton('user_authentication.php', 'facebookLogin');
	}

}
?>