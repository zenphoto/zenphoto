<?php

/**
 *
 * The plugin provides infrastructure for OAuth2 protocol login plugins.
 *
 * The plugin name should be <i>authority</i>Login. There must be a folder of that
 * name containing the script for handling the login. The script name is <i>authority</i>.php.
 * There also must be a PNG image for the login button named <var>login_button.png</var>
 *
 * @author Stephen Billard (sbillard)
 * @Copyright 2017 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage users
 */
class oAuthLogin {

	/**
	 * "comon" option initialization
	 */
	function __construct() {
		$class = get_called_class();
		setOptionDefault($class . '_group', 'viewers');
	}

	/**
	 * common option handling
	 *
	 * @global type $_zp_authority
	 * @return array
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

		$options = array(
				gettext('Assign user to') => array('key' => 'twitterLogin_group', 'type' => OPTION_TYPE_SELECTOR,
						'order' => 0,
						'selections' => $ordered,
						'desc' => gettext('The user group to which to map the user.'))
		);
		return $options;
	}

	/**
	 * Provides a list of alternate handlers for logon
	 * @param $handler_list
	 */
	static function alt_login_handler($handler_list) {
		$class = get_called_class();
		$oAuthAuthority = ucfirst(str_replace('Login', '', $class));
		$link = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/' . $class . '/' . strtolower($oAuthAuthority) . '.php';
		$handler_list[$oAuthAuthority] = array('script' => $link, 'params' => array('request=login'));
		return $handler_list;
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
		global $_zp_authority;
		$class = get_called_class();
		$oAuthAuthority = ucfirst(str_replace('Login', '', $class));
		if (is_valid_email_zp($email)) { // prefer email as user id
			$user = $email;
		} else {
			$user = $oAuthAuthority . '_User_' . $user;
		}

		$userobj = $_zp_authority->getAnAdmin(array('`user`=' => $user, '`valid`=' => 1));
		$more = false;
		if ($userobj) { //	update if changed
			$save = false;
			if (!empty($email) && $email != $userobj->getEmail()) {
				$save = true;
				$userobj->setEmail($email);
			}
			if (!empty($name) && $name != $userobj->getName()) {
				$save = true;
				$userobj->setName($name);
			}
			$credentials = array('auth' => $oAuthAuthority . 'OAuth', 'user' => 'user', 'email' => 'email');
			if ($name)
				$credentials['name'] = 'name';
			if ($credentials != $userobj->getCredentials()) {
				$save = true;
				$userobj->setCredentials($credentials);
			}
			if ($save) {
				$userobj->save();
			}
		} else { //	User does not exist, create him
			$groupname = getOption($class . '_group');
			$groupobj = $_zp_authority->getAnAdmin(array('`user`=' => $groupname, '`valid`=' => 0));
			if ($groupobj) {
				$group = NULL;
				if ($groupobj->getName() != 'template') {
					$group = $groupname;
				}
				$userobj = Zenphoto_Authority::newAdministrator('');
				$userobj->transient = false;
				$userobj->setUser($user);
				$credentials = array('auth' => $oAuthAuthority . 'OAuth', 'user' => 'user', 'email' => 'email');
				if ($name) {
					$credentials['name'] = 'name';
				}
				$userobj->setCredentials($credentials);

				$userobj->setName($name);
				$userobj->setPass($user . HASH_SEED . gmdate('d M Y H:i:s'));
				$userobj->setObjects(NULL);
				$userobj->setLanguage(getUserLocale());
				$userobj->setObjects($groupobj->getObjects());
				$userobj->setEmail($email);
				if (getOption('register_user_create_album')) {
					$userobj->createPrimealbum();
				}
				$userobj->setRights($groupobj->getRights());
				$userobj->setGroup($group);
				$userobj->save();
			} else {
				$more = sprintf(gettext('Configuration error,%1$s login group %2$s does not exist.'), $class, $groupname);
			}
			if (!$more && getOption('register_user_notify')) {
				$_notify = zp_mail(gettext('ZenPhoto20 Gallery registration'), sprintf(gettext('%1$s (%2$s) has registered for the zenphoto gallery providing an e-mail address of %3$s.'), $userobj->getName(), $userobj->getUser(), $userobj->getEmail()));
			}
		}
		session_unset(); //	need to cleanse out stuff or subsequent logins will fail[sic]
		if ($more) {
			header('Location: ' . WEBPATH . '/' . ZENFOLDER . '/admin.php?_zp_login_error=' . html_encode($more));
			exitZP();
		}
		zp_apply_filter('federated_login_attempt', true, $user, $oAuthAuthority . 'oAuth'); //	we will mascerade as federated logon for this filter
		Zenphoto_Authority::logUser($userobj);
		if ($redirect) {
			header("Location: " . $redirect);
		} else {
			header('Location: ' . FULLWEBPATH);
		}
		exitZP();
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
		global $_zp_current_admin_obj;
		$class = get_called_class();
		$oAuthAuthority = ucfirst(str_replace('Login', '', $class));
		if (empty($_zp_current_admin_obj) || !$userobj->getValid())
			return $html;
		$federated = $userobj->getCredentials(); //	came from federated logon, disable the e-mail field
		if (!in_array($oAuthAuthority . 'OAuth', $federated)) {
			$federated = false;
		}

		if ($userobj->getID() != $_zp_current_admin_obj->getID() && $federated) { //	The current logged on user
			$msg = sprintf(gettext("<strong>NOTE:</strong> This user was created by a %s Account logon."), $oAuthAuthority);
			$myhtml = '<div class="user_left">' . "\n"
							. '<p class="notebox">' . $msg . '</p>' . "\n"
							. '</div>' . "\n"
							. '<br class="clearall">' . "\n";
			$html = $myhtml . $html;
		}
		return $html;
	}

	static function loginButton() {
		$class = get_called_class();
		$oAuthAuthority = ucfirst(str_replace('Login', '', $class));

		if (!zp_loggedin()) {
			?>
			<span class="button">
				<a href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/' . $class . '/' . strtolower($oAuthAuthority) . '.php'; ?>?request=<?php echo $class; ?>&amp;redirect=/dev/index.php?userlog=1" title="<?php echo $oAuthAuthority; ?> login">
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/' . $class; ?>/login_button.png" alt="<?php echo $oAuthAuthority; ?> login">
				</a>
			</span>
			<?php
		}
	}

}
?>