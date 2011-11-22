<?php
/* Tries to authorize user based on Apache HTTP authenitcation credentials
 *
 * The PHP_AUTH_USER is mapped to a Zenphoto user
 * the PHP_AUTH_PW must be in cleartext and match the Zenphoto user's password
 * (If the User validation is set to "trusted" the PHP_AUTH_PW password will be ignored and need not be
 * cleartext.)
 *
 * Note that the HTTP logins are outside of Zenphoto so there is no security logging of
 * them. Nor can Zenphoto "log off" the user.
 *
 * Apache configuration:
 *
 * run the Apache htpasswd utility to create a password file containing your first user:
 *
 * 		<path to apache executables>htpasswd -cp <path to apache folder>passwords user1
 *
 * htpasswd will prompt you for the password. You can repeat the process for each additional user
 * or you can simply edit the "passwords" file with a text editor.
 *
 * Each user/password must match to a Zenphoto user/password or access to Zenphoto will be at a "guest"
 * level. If a user changes his password in Zenphoto someone must make the equivalent change in
 * the Apache password file for the Zenphoto user access to succeed. (However, see the User validation option.)
 *
 * create a file named "groups" in your apache folder
 * edit the "groups" file with a line similar to:
 * 		zenphoto: stephen george frank
 * this creates a group named zenphoto with the list of users as members
 *
 * Add the following lines to your Zenphoto root .htaccess file after the initial comments and
 * before the rewrite rules:
 *
 * 		AuthType Basic
 * 		AuthName "Zenphoto realm"
 * 		AuthUserFile c:/wamp/bin/apache/passwords
 * 		AuthGroupFile c:/wamp/bin/apache/groups
 * 		Require group zenphoto
 *
 * (replace "c:/wamp/bin/apache/" with the path to these files on your server.)
 *
 *
 * @package plugins
 */
$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext('Checks for Apache HTTP authenitcation authorized users');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.2';
$option_interface = 'http_auth';

zp_register_filter('authorization_cookie', 'http_auth_check');

class http_auth {
	/**
	 * class instantiation function
	 *
	 * @return http_auth
	 */
	function http_auth() {
		setOptionDefault('http_auth_trust', 0);
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(	gettext('User validation') => array('key' => 'http_auth_trust', 'type' => OPTION_TYPE_RADIO,
										'buttons' => array(gettext('verify')=>'0',gettext('trusted')=>'1'),
										'desc' => gettext('Set to <em>trusted</em> to presume the HTTP user is securely authorized. (This setting does not verify passwords against the Zenphoto user.)')));
	}

	function handleOption($option, $currentValue) {
	}

}

function http_auth_check($authorized) {
	global $_zp_authority, $_zp_current_admin_obj;
	if (!$authorized) {
		// not logged in via normal Zenphoto handling
		// PHP-CGI auth fixd
		if(isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$auth_params = explode(":" , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
			$_SERVER['PHP_AUTH_USER'] = $auth_params[0];
			unset($auth_params[0]);
			$_SERVER['PHP_AUTH_PW'] = implode('',$auth_params);
		}
		if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
			$auth_params = explode(":" , base64_decode(substr($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 6)));
			$_SERVER['PHP_AUTH_USER'] = $auth_params[0];
			unset($auth_params[0]);
			$_SERVER['PHP_AUTH_PW'] = implode('',$auth_params);
		}

		if (array_key_exists('PHP_AUTH_USER', $_SERVER) && array_key_exists('PHP_AUTH_PW', $_SERVER)) {
			$user = $_SERVER['PHP_AUTH_USER'];
			$pass = $_SERVER['PHP_AUTH_PW'];
			$searchfor = array('`user`=' => $user, '`pass`=' => $_zp_authority->passwordHash($user, $pass), '`valid`=' => 1);
			if (getOption('http_auth_trust')) {
				unset($searchfor['`pass`=']);
			}
			$userobj = $_zp_authority->getAnAdmin($searchfor);
			if ($userobj) {
				$credentials = array('http_auth','password');
				$userobj->setCredentials($credentials);
				$_zp_current_admin_obj = $userobj;
				$authorized = $_zp_current_admin_obj->getRights();
			}
		}
	}
	return $authorized;
}

?>