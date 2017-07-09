<?php

/**
 *
 * The plugin provides login to ZenPhoto20 via Google OAuth2 protocol.
 *
 *
 * You must configure the plugin with your Google Developer credentials. You will
 * need an <b><i>API key</i></b> as well as an OAuth2 <b><i>Client ID</i></b> and <b><i>Client Secret</i></b>.
 * (The <b><i>API key</i></b> is shared with the <var>googleMap</var> plugin, so you may already have one.)
 * You can obtain these from the
 * {@link https://console.developers.google.com/apis/dashboard Google Developers Console}
 *
 * Your <i>OAuth2 client ID</i> will need an <i>Authorized redirect URI</i> that
 * points to <var>%FULLWEBPATH%/%ZENFOLDER%/%PLUGIN_FOLDER%/googleLogin/user_authentication.php</var>
 *
 * The gmail address supplied by Google OAuth2 will become the user's <i>user ID</i>
 * if present. If no e-mail address is supplied with the login, a user ID will be created
 * from the user's Google ID. If this <i>user ID</i> does not exist as a ZenPhoto20 user,
 * a new user will be created. The user will be assigned to the group indicated by
 * the plugin's options. If <var>Notify</var> option is checked an e-mail will be sent to
 * the site administrator informing him of the new user.
 *
 * You can place a login button on your webpage by calling the function <var>googleLogin::loginButton();</var>
 *
 * @author Stephen Billard (sbillard)
 * @Copyright 2017 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage users
 */
$plugin_is_filter = 900 | CLASS_PLUGIN;
$plugin_description = gettext("Handles logon via the user's <em>Google</em> account.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_notice = sprintf(gettext('The PHP <var>curl</var> module is required for this plugin.'));
$plugin_disable = (extension_loaded('curl')) ? false : gettext('The PHP Curl is required.');

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/oAuthLogin.php');

$option_interface = 'googleLogin';

if ($plugin_disable) {
	enableExtension('googleLogin', 0);
} else {
	zp_register_filter('alt_login_handler', 'googleLogin::alt_login_handler');
	zp_register_filter('edit_admin_custom_data', 'googleLogin::edit_admin');
}
zp_session_start();

/**
 * Option class
 *
 */
class googleLogin extends oAuthLogin {

	protected $link = 'user_authentication.php';
	protected $authority = 'Google';

	/**
	 * Option instantiation
	 */
	function __construct() {
		global $_zp_authority;
		setOptionDefault('googleLogin_group', 'viewers');
		setOptionDefault('googleLogin_ClientID', '');
		setOptionDefault('googleLogin_ClientSecret', '');
		setOptionDefault('gmap_map_api_key', '');
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

		$options = array(gettext('Assign user to') => array('key' => 'googleLogin_group', 'type' => OPTION_TYPE_SELECTOR,
						'order' => 0,
						'selections' => $ordered,
						'desc' => gettext('The user group to which to map the user.')),
				gettext('OAuth Client ID') => array('key' => 'googleLogin_ClientID', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext('This is your Google OAuth Client ID.')),
				gettext('OAuth Client Secret') => array('key' => 'googleLogin_ClientSecret', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext('This is your Google OAuth Client Secret.')),
				gettext('API key') => array('key' => 'gmap_map_api_key', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 3,
						'desc' => gettext('This is your Google Developer API key.'))
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
		return self::_alt_login_handler($handler_list, 'googleLogin', 'user_authentication.php');
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
		self::_credentials($user, $email, $name, $redirect, 'googleLogin');
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
		self::_edit_admin($html, $userobj, $i, $background, $current, $local_alterrights, 'googleLogin');
	}

	/**
	 * provides a login button for theme pages
	 */
	static function loginButton() {
		self::_loginButton('user_authentication.php', 'googleLogin');
	}

}
?>