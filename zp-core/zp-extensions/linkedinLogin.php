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
$plugin_disable = zpFunctions::pluginDisable(array(array(version_compare(PHP_VERSION, '5.6.0', '<'), gettext('PHP version 5.4 or greater is required.')), array(!extension_loaded('curl'), gettext('The PHP Curl is required.'))));

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/oAuth/oAuthLogin.php');

$option_interface = 'linkedinLogin';

if ($plugin_disable) {
	enableExtension('linkedinLogin', 0);
} else {
	zp_register_filter('alt_login_handler', 'linkedinLogin::alt_login_handler');
	zp_register_filter('edit_admin_custom_data', 'linkedinLogin::edit_admin');
}

/**
 * Option class
 *
 */
class linkedinLogin extends oAuthLogin {

	/**
	 * Option instantiation
	 */
	function __construct() {
		parent::__construct();
		setOptionDefault('linkedinLogin_ClientID', '');
		setOptionDefault('linkedinLogin_ClientSecret', '');
	}

	/**
	 * Provides option list
	 */
	function getOptionsSupported() {
		$options = array(
				gettext('App ID') => array('key' => 'linkedinLogin_ClientID', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 11,
						'desc' => gettext('This is your Linkedin <em>Client ID</em>.')),
				gettext('App Secret') => array('key' => 'linkedinLogin_ClientSecret', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 12,
						'desc' => gettext('This is your Linkedin <em>Client Secret</em>.'))
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