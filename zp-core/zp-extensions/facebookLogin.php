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
 * points to <var>%FULLWEBPATH%/%ZENFOLDER%/%PLUGIN_FOLDER%/facebookLogin/facebook.php</var>
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
$plugin_disable = zpFunctions::pluginDisable(array(array(version_compare(PHP_VERSION, '5.6.0', '<'), gettext('PHP version 5.4 or greater is required.')), array(!extension_loaded('curl'), gettext('The PHP Curl is required.'))));

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/oAuth/oAuthLogin.php');

$option_interface = 'facebookLogin';

if ($plugin_disable) {
	enableExtension('facebookLogin', 0);
} else {
	zp_register_filter('alt_login_handler', 'facebookLogin::alt_login_handler');
	zp_register_filter('edit_admin_custom_data', 'facebookLogin::edit_admin');
}

/**
 * Option class
 *
 */
class facebookLogin extends oAuthLogin {

	/**
	 * Option instantiation
	 */
	function __construct() {
		parent::__construct();
		setOptionDefault('facebookLogin_APPID', '');
		setOptionDefault('facebookLogin_APPSecret', '');
	}

	/**
	 * Provides option list
	 */
	function getOptionsSupported() {
		$options = array(
				gettext('App ID') => array('key' => 'facebookLogin_APPID', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 11,
						'desc' => gettext('This is your Facebook <em>App ID</em>.')),
				gettext('App Secret') => array('key' => 'facebookLogin_APPSecret', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 12,
						'desc' => gettext('This is your Facebook <em>App Secret</em>.'))
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