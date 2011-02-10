<?php
/**
 * Interface to federated login handlers
 *
 * The plugin will use a federated login such as Google Accounts as a logon server.
 * If the logon succeeds it will log that user onto Zenphoto. An attempt will be
 * made to match the user to an existing Zenphoto user. If such is found, then the
 * user is logged in as that Zenphoto user. If not, a Zenphoto user will be created
 * and the user logged in as him.
 *
 * The default priviledges for a created user are obtained from the "viewers" group.
 * (The user will belong to that group.) This will be the case whether or not the
 * user_groups plugin is enabled. If you want to be able to specify unique default
 * priviledges you will have to use the user_groups plugin at least until you have
 * defined your default group.
 *
 * You may also find the user_expiry plugin useful in conjunction with this plugin.
 * Since users may arbitrarily be created from those visitors who login with their
 * federated credentials you may want to "age" these users and remove them after some
 * period of time. That is done by the user_expiry plugin.
 *
 * Currently there is only one handle available. It is an OpenID handler based on the
 * Janrain OpenID Enabled library (http://www.janrain.com/openid-enabled). There are
 * some server requirements for this library. To see if you server meets them run
 * the script zp-core/zp-extensions/federated_logon/OpenID_detect.php. It will give
 * you a report on what might need be done. You can ignore items about data stores as
 * they are not used in this implementation.
 *
 * See also the Janrain Readme.txt file.
 *
 *
 * Other handlers can be created and placed in the plugins/federated_logon folder.
 * Integration with Zenphoto is fairly simple. The logon handler script should be
 * named ending in "_logon.php". The plugin will use the name up to that point as the
 * selector on the logon form.
 *
 * You need to preserver the $_GET['redirect'] parameter for use after the authentication
 * is successful at which time you call the logonFederatedCredentials() function passing a
 * user ID, e-mail and name (if you have them) and the redirection link you saved above.
 * For an example, the former is done at the beginning of the OpenID_logon.php script. The
 * latter is done in the "run()" function of OpenID_finish_auth.php
 *
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage usermanagement
 */
$plugin_is_filter = 9|CLASS_PLUGIN;
$plugin_description = gettext('Handles federated logon. See <em>Usage information</em> for details.').' '.
											sprintf(gettext('Run the <a href="%s">OpenID detect</a> script to check compatibility of your server configuration.'),FULLWEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/federated_logon/Auth/OpenID_detect.php');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_URL = "http://www.zenphoto.org/documentation/plugins/_".PLUGIN_FOLDER."---federated_login.php.html";
$plugin_version = '1.4.1';
$plugin_disable = (version_compare(PHP_VERSION, '5.0.0') != 1) ?
		gettext('PHP version 5 or greater is required.') : (getOption('federated_logon_detect')) ?
		false : sprintf(gettext('Run the <a href="%s">OpenID detect</a> script to check compatibility of your server configuration.'),FULLWEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/federated_logon/Auth/OpenID_detect.php');
if ($plugin_disable) {
	setOption('zp_plugin_federated_login',0);
} else {
	$option_interface = 'federated_login_options';
	zp_register_filter('alt_login_handler','federated_login_alt_login_handler');
	zp_register_filter('save_admin_custom_data', 'federated_login_save_custom');
	zp_register_filter('edit_admin_custom_data', 'federated_login_edit_admin',0);
	zp_register_filter('theme_head', 'federated_login_verify');
}



/**
 * Option class
 *
 */
class federated_login_options {

	/**
	 * Option instantiation
	 */
	function federated_login_options() {
		global $_zp_authority;
		setOptionDefault('federated_login_group', 'viewers');
		$mailinglist = $_zp_authority->getAdminEmail(ADMIN_RIGHTS);
		if (count($mailinglist) == 0) {	//	no one to send the notice to!
			setOption('register_user_notify',0);
		} else {
			setOptionDefault('register_user_notify', 1);
		}
	}

	/**
	 * Provides option list
	 */
	function getOptionsSupported() {
		global $_zp_authority;
		$admins = $_zp_authority->getAdministrators('groups');
		$ordered = array();
		foreach ($admins as $key=>$admin) {
			if ($admin['rights'] && !($admin['rights']&ADMIN_RIGHTS)) {
				$ordered[$admin['user']] = $admin['user'];
			}
		}
		if (getOption('zp_plugin_register_user')) {
			$disable = gettext('* The option may be set via the <a href="javascript:gotoName(\'register_user\');"><em>register_user</em></a> plugin options..');
		} else {
			$disable = false;
		}
		$options = array(	gettext('Assign user to') => array('key' => 'federated_login_group', 'type' => OPTION_TYPE_SELECTOR,
												'order' => 1,
												'selections' => $ordered,
												'desc' => gettext('The user group to which to map the federated login.')),
											sprintf(gettext('Notify%s'),($disable)?'*':'') => array('key' => 'register_user_notify', 'type' => OPTION_TYPE_CHECKBOX,
												'disabled' => $disable,
												'order' => 4,
												'desc' => gettext('If checked, an e-mail will be sent to the gallery admin when a new user has verified his registration.'))
		);
		if ($disable) {
			$options['<p class="notebox">'.$disable.'</p>'] = array('key' => 'federated_logon_truncate_note', 'type' => OPTION_TYPE_CUSTOM,
																															'order' => 8,
																															'desc' => '');
		} else {
			if (getOption('zp_plugin_zenpage')) {
				$options[gettext('<p class="notebox">*<strong>Note:</strong> The setting of these options are shared with other the <em>register_user</em> plugin.</p>')] =
																									array('key' => 'federated_logon_truncate_note',
																												'type' => OPTION_TYPE_CUSTOM,
																												'order' => 8,
																												'desc' => '');
			}
		}
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

/**
 * Provides a list of alternate handlers for logon
 * @param $handler_list
 */
function federated_login_alt_login_handler($handler_list) {
	$files = getPluginFiles('*_logon.php','federated_logon');
	foreach ($files as $link) {
		$link = str_replace(SERVERPATH, WEBPATH, str_replace('\\', '/', $link));
		$name = str_replace('_', ' ', substr(basename($link), 0, -10));
		$handler_list[$name] = array('script'=>$link, 'params'=>array());
	}
	return $handler_list;
}

/**
 * Common logon handler.
 * Will log the user on if he exists. Otherwise it will create a user accoung and log
 * on that account.
 *
 * Redirects into Zenphoto on success presuming there is a redirect link.
 *
 * @param $user
 * @param $email
 * @param $name
 * @param $redirect
 */
function logonFederatedCredentials($user, $email, $name, $redirect) {
	global $_zp_authority;
	$userobj = $_zp_authority->getAnAdmin(array('`user`=' => $user, '`valid`=' => 1));
	$more = false;
	if (!$userobj) {	//	User does not exist, create him
		$groupname = getOption('federated_login_group');
		$groupobj = $_zp_authority->getAnAdmin(array('`user`=' => $groupname, '`valid`=' => 0));
		if ($groupobj) {
			$group = NULL;
			if ($groupobj->getName() != 'template') {
				$group = $groupname;
			}
			$userobj = $_zp_authority->newAdministrator('');
			$userobj->transient = false;
			$userobj->setUser($user);
			$userobj->setName($name);
			$userobj->setPass($user.gmdate('d M Y H:i:s').getOption('password_pattern'));
			$userobj->setObjects(NULL);
			$userobj->setCustomData('');
			$userobj->setLanguage(getUserLocale());
			if (is_valid_email_zp($email)) {
				$userobj->setEmail($email);
				if (getOption('register_user_create_album')) {
					$userobj->createPrimealbum();
				}
			} else {
				$groupobj = $_zp_authority->getAnAdmin(array('`user`='=>'federated_verify','`valid`='=>0));
				if (empty($verify_group)) {
					$groupobj = $_zp_authority->newAdministrator('federated_verify',0);
					$groupobj->setName('group');
					$groupobj->setRights(NO_RIGHTS);
					$groupobj->save();
				}
				$group = 'federated_verify';
				$redirect = '/' . ZENFOLDER . '/admin.php';
			}
			$userobj->setRights($groupobj->getRights());
			$userobj->setGroup($group);
			if (!empty($group)) {
				$userobj->setObjects($groupobj->getObjects());
			}
			$userobj->save();
		} else {
			$more = sprintf(gettext('Group %s does not exist.'),$groupname);
		}
	}
	if (!$more) {
		zp_apply_filter('federated_login_attempt', true, $user);
		$_zp_authority->logUser($userobj);
		if ($redirect) {
			header("Location: ".FULLWEBPATH.$redirect);
		}
	}
	return $more;
}

/**
 * Check if an e-mail address has been provided
 * @param $updated
 * @param $userobj
 * @param $i
 * @param $alter
 */
function federated_login_save_custom($updated, $userobj, $i, $alter) {
	global $_notification_sent;
	if (($userobj->getGroup() == 'federated_verify') && is_valid_email_zp($userobj->getEmail()))  {
		$userobj->save();
		$admin_e = $userobj->getEmail();
		$user = $userobj->getUser();
		$key = bin2hex(serialize(array('user'=>$user,'email'=>$admin_e)));
		$link = FULLWEBPATH.'/index.php?verify_federated_user='.$key;
		$message = sprintf(gettext('To validate your federated logon credentials visit %s.'), $link);
		zp_mail(get_language_string(gettext('Federated user confirmation')), $message, array($user=>$admin_e));


debugLog($link);

	}
	return $updated;
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
function federated_login_edit_admin($html, $userobj, $i, $background, $current, $local_alterrights) {
	global $_zp_current_admin_obj;
	if (($userobj->getGroup() == 'federated_verify') && ($userobj->getID() == $_zp_current_admin_obj->getID()))  {
		$email = $userobj->getEmail();
		if (empty($email)) {
			$msg = gettext('<strong>NOTE:</strong> Update your profile with a valid <em>e-mail</em> address and you will be sent a link to validate your access to the site.');
			$myhtml =
				'<tr'.((!$current)? ' style="display:none;"':'').' class="userextrainfo">
					<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top" colspan="3">'."\n".
						'<p class="notebox">'.$msg.'</p>'."\n".
					'</td>
				</tr>'."\n";
			$html = $myhtml.$html;
		}
	}
	return $html;
}

function federated_login_verify() {
	global $_zp_authority;
	//process any verifications posted
	if (isset($_GET['verify_federated_user'])) {
		$params = unserialize(pack("H*", $_GET['verify_federated_user']));
		$userobj = $_zp_authority->getAnAdmin(array('`user`='=>$params['user'], '`email`='=>$params['email'], '`valid`='=>1));
		if ($userobj) {
			$groupname = getOption('federated_login_group');
			$groupobj = $_zp_authority->getAnAdmin(array('`user`=' => $groupname, '`valid`=' => 0));
			if ($groupobj) {
				$userobj->setRights($groupobj->getRights());
				$userobj->setGroup($groupname);
				$userobj->setObjects($groupobj->getObjects());
				if (getOption('register_user_create_album')) {
					$userobj->createPrimealbum();
				}
				$userobj->save();
			}
			zp_apply_filter('register_user_verified', $userobj);
			if (getOption('register_logon_user_notify')) {
				zp_mail(gettext('Zenphoto Gallery registration'),
				sprintf(gettext('%1$s (%2$s) has registered for the zenphoto gallery providing an e-mail address of %3$s.'),$userobj->getName(), $userobj->getUser(), $userobj->getEmail()));
			}
			$redirect = '/' . ZENFOLDER . '/admin.php';
			$success = logonFederatedCredentials($params['user'], $params['user'], NULL, $redirect);
		}
	}
}


?>