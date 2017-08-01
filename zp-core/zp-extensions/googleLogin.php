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
 * points to <var>%FULLWEBPATH%/%ZENFOLDER%/%PLUGIN_FOLDER%/googleLogin/google.php</var>
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
$plugin_disable = zpFunctions::pluginDisable(array(array(version_compare(PHP_VERSION, '5.6.0', '<'), gettext('PHP version 5.4 or greater is required.')), array(!extension_loaded('curl'), gettext('The PHP Curl is required.'))));

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/oAuth/oAuthLogin.php');

$option_interface = 'googleLogin';

if ($plugin_disable) {
	enableExtension('googleLogin', 0);
} else {
	zp_register_filter('alt_login_handler', 'googleLogin::alt_login_handler');
	zp_register_filter('edit_admin_custom_data', 'googleLogin::edit_admin');
}

/**
 * Option class
 *
 */
class googleLogin extends oAuthLogin {

	/**
	 * Option instantiation
	 */
	function __construct() {
		parent::__construct();
		setOptionDefault('googleLogin_ClientID', '');
		setOptionDefault('googleLogin_ClientSecret', '');
		setOptionDefault('gmap_map_api_key', '');
	}

	/**
	 * Provides option list
	 */
	function getOptionsSupported() {
		$options = array(
				gettext('OAuth Client ID') => array('key' => 'googleLogin_ClientID', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 11,
						'desc' => gettext('This is your Google <em>OAuth Client ID</em>.')),
				gettext('OAuth Client Secret') => array('key' => 'googleLogin_ClientSecret', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 12,
						'desc' => gettext('This is your Google <em>OAuth Client Secret</em>.')),
				gettext('API key') => array('key' => 'gmap_map_api_key', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 13,
						'desc' => gettext('This is your Google <em>Developer API key</em>.'))
		);
		$options = array_merge($options, parent::getOptionsSupported());
		return $options;
	}

	/**
	 * Handles the custom option $option
	 * @param $option
	 * @param $currentValue
	 */
	function handleOption($option, $currentValue) {

	}

}

?>