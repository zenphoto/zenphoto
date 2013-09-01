<?php

/**
 *  Tries to authorize user based on Apache HTTP authentication credentials
 *
 * The <var>PHP_AUTH_USER</var> is mapped to a Zenphoto user
 * the <var>PHP_AUTH_PW</var> must be in cleartext and match the Zenphoto user's password
 * (If the User validation is set to <i>trusted</i> the <var>PHP_AUTH_PW</var> password will be ignored and
 * need not be cleartext.)
 *
 * Note that the HTTP logins are outside of Zenphoto so there is no security logging of
 * them. Nor can Zenphoto "log off" the user. The normal logout links will not show for
 * users logged in via this plugin.
 *
 * Apache configuration:
 * 	<ul>
 * <li>Run the Apache <var>htpasswd</var> utility to create a password file containing your first user:
 * 		<i>path to apache executables</i> <var>htpasswd -cp</var> <i>path to apache folder</i> <var>passwords user1</var><br><br>
 * <var>htpasswd</var> will prompt you for the password. You can repeat the process for each additional user
 * or you can simply edit the <i>passwords</i> file with a text editor.<br><br>
 * Each <i>user/password</i> must match to a Zenphoto <i>user/password</i> or access to Zenphoto will be at a <i>guest</i>
 * level. If a user changes his password in Zenphoto someone must make the equivalent change in
 * the Apache password file for the Zenphoto user access to succeed. (However, see the <i>User validation</i>
 * option.)</li>
 *
 * <li>Create a file named "groups" in your apache folder</li>
 * <li>Edit the "groups" file with a line similar to:
 * 		<var>zenphoto: stephen george frank</var>.
 * This creates a group named zenphoto with the list of users as members</li>
 *
 * <li>Add the following lines to your Zenphoto root .htaccess file after the initial comments and
 * before the rewrite rules:
 * 	<ul>
 * 		<li>AuthType Basic</li>
 * 		<li>AuthName "Zenphoto realm"</li>
 * 		<li>AuthUserFile c:/wamp/bin/apache/passwords</li>
 * 		<li>AuthGroupFile c:/wamp/bin/apache/groups</li>
 * 		<li>Require group zenphoto</li>
 * 	</ul>
 * 	</li>
 * 	</ul>
 *
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage users
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext('Checks for Apache HTTP authentication of authorized users.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'http_auth';

zp_register_filter('authorization_cookie', 'http_auth::check');

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
		return array(gettext('User validation') => array('key'			 => 'http_auth_trust', 'type'		 => OPTION_TYPE_RADIO,
										'buttons'	 => array(gettext('verify')	 => '0', gettext('trusted') => '1'),
										'desc'		 => gettext('Set to <em>trusted</em> to presume the HTTP user is securely authorized. (This setting does not verify passwords against the Zenphoto user.)')));
	}

	function handleOption($option, $currentValue) {

	}

	static function check($authorized) {
		global $_zp_current_admin_obj;
		if (!$authorized) {
			// not logged in via normal Zenphoto handling
			// PHP-CGI auth fixd
			if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
				$auth_params = explode(":", base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
				$_SERVER['PHP_AUTH_USER'] = $auth_params[0];
				unset($auth_params[0]);
				$_SERVER['PHP_AUTH_PW'] = implode('', $auth_params);
			}
			if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
				$auth_params = explode(":", base64_decode(substr($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 6)));
				$_SERVER['PHP_AUTH_USER'] = $auth_params[0];
				unset($auth_params[0]);
				$_SERVER['PHP_AUTH_PW'] = implode('', $auth_params);
			}

			if (array_key_exists('PHP_AUTH_USER', $_SERVER) && array_key_exists('PHP_AUTH_PW', $_SERVER)) {
				$user = $_SERVER['PHP_AUTH_USER'];
				$pass = $_SERVER['PHP_AUTH_PW'];
				if (getOption('http_auth_trust')) {
					$userobj = Zenphoto_Authority::getAnAdmin(array('`user`='	 => $user, '`valid`=' => 1));
				} else {
					$userobj = Zenphoto_Authority::checkLogon($user, $pass);
				}
				if ($userobj) {
					$_zp_current_admin_obj = $userobj;
					$_zp_current_admin_obj->logout_link = false;
					$authorized = $_zp_current_admin_obj->getRights();
				}
			}
		}
		return $authorized;
	}

}

?>