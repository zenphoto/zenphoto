<?php
/**
 * Manages user expirations
 *
 * NOTE: does not apply to users with ADMIN_RIGHTS!
 *
 * Set the "interval" to the number of days before expiration
 * Upon expiration, the user will be "disabled". He will not have
 * login access to the gallery.
 *
 * if the user visits the gallery within one week prior to his expiry date
 * a mail will be sent to the user (if there is an email address)
 * warning of the impending expiration.
 *
 * There is a subtab added to the "admin" tab for managing expirations.
 * A list of users without ADMIN_RITGHTS will be presented indicating each
 * user's expiry date. Dates within one week of expiry are shown in orange,
 * expired dates are shown in red.
 *
 * From this tab the user may be removed, disabled (enabled)
 * or renewed. (Renewal is for a new "interval" from his last renewal (or the
 * current date if adding the interval would not bring him up-to-date.)
 *
 *
 * @package plugins
 * @subpackage usermanagement
 */

// force UTF-8 Ø

$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext("Provides management of users based on when they were created.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';

$option_interface = 'user_expiry';

zp_register_filter('admin_tabs', 'user_expiry_admin_tabs', 99);
zp_register_filter('authorization_cookie','user_expiry_checkcookie');
zp_register_filter('admin_login_attempt','user_expiry_checklogon');
zp_register_filter('federated_login_attempt','user_expiry_checklogon');
zp_register_filter('edit_admin_custom_data', 'user_expiry_edit_admin',0);
zp_register_filter('load_theme_script', 'user_expiry_reverify',0);
zp_register_filter('admin_note', 'user_expiry_notify',0);

/**
 * Option handler class
 *
 */
class user_expiry {
	/**
	 * class instantiation function
	 *
	 */
	function user_expiry() {
		setOptionDefault('user_expiry_interval', 365);
		setOptionDefault('user_expiry_warn_interval', 7);
		setOptionDefault('user_expiry_auto_renew', 0);
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return  array(	gettext('Days until expiration') => array('key' => 'user_expiry_interval', 'type' => OPTION_TYPE_TEXTBOX,
																		'order'=>1,
																		'desc' => gettext('The number of days until a user is flagged as expired.')),
		gettext('Warning interval') => array('key' => 'user_expiry_warn_interval', 'type' => OPTION_TYPE_TEXTBOX,
																		'order'=>2,
																		'desc' => gettext('The period in days before the expiry during which a warning message will be sent to the user. (If set to zero, no warning occurs.)')),
		gettext('Auto renew') => array('key' => 'user_expiry_auto_renew', 'type' => OPTION_TYPE_CHECKBOX,
																	'order'=>3,
																	'desc' => gettext('Automatically renew the subscription if the user visits during the warning period.'))
		);
	}

	function handleOption($option, $currentValue) {
	}
}

function user_expiry_admin_tabs($tabs) {
	global $_zp_null_account;
	if (zp_loggedin(ADMIN_RIGHTS) && !$_zp_null_account) {
		if (isset($tabs['users']['subtabs'])) {
			$subtabs = $tabs['users']['subtabs'];
		} else {
			$subtabs = array();
		}
		$subtabs[gettext('users')] = 'admin-users.php?page=users&amp;tab=users';
		$subtabs[gettext('expiry')] = PLUGIN_FOLDER.'/user-expiry/user-expiry-tab.php?page=users&amp;tab=expiry';
		$tabs['users'] = array(	'text'=>gettext("admin"),
														'link'=>WEBPATH."/".ZENFOLDER.'/admin-users.php?page=users&amp;tab=users',
														'subtabs'=>$subtabs,
														'default'=>'users');
	}
	return $tabs;
}

function user_expiry_checkexpires($loggedin, $userobj) {
	$subscription = 86400*getOption('user_expiry_interval');
	$expires = strtotime($userobj->getDateTime())+$subscription;
	if ($expires < time()) {
		$userobj->setValid(2);
		$userobj->save();
		$loggedin = false;
	} else {
		if ($expires < (time() + getOption('user_expiry_warn_interval')*86400)) {	//	expired
			if (getOption('user_expiry_auto_renew')) {
				$newdate = getOption('user_expiry_interval')*86400+strtotime($userobj->getDateTime());
				if ($newdate+getOption('user_expiry_interval')*86400 < time()) {
					$newdate = time()+getOption('user_expiry_interval')*86400;
				}
				$userobj->setDateTime(date('Y-m-d H:i:s',$newdate));
				$userobj->setValid(1);
				$credentials = $userobj->getCredentials();
				$key = array_search('exiry_notice', $credentials);
				if ($key !== false) {
					unset($credentials[$key]);
					$userobj->setCredentials($credentials);
				}
				$userobj->save();
			} else {
				if ($mail = $userobj->getEmail()) {
					$credentials = $userobj->getCredentials();
					if (!in_array('exiry_notice', $credentials)) {
						$credentials[] = 'exiry_notice';
						$userobj->setCredentials($credentials);
						$userobj->save();
						$gallery = new Gallery();
						$message = sprintf(gettext('Your user id for the Zenphoto site %s will expire on %s.'),$gallery->getTitle(),date('Y-m-d',$expires));
						$notify = zp_mail(get_language_string(gettext('User id expiration')), $message, array($userobj->getName()=>$mail));
					}
				}
			}
		} else {
			$credentials = $userobj->getCredentials();
			$key = array_search('exiry_notice', $credentials);
			if ($key !== false) {
				unset($credentials[$key]);
				$userobj->setCredentials($credentials);
				$userobj->save();
			}
		}
	}
	return $loggedin;
}

function user_expiry_checkcookie($loggedin) {
	global $_zp_current_admin_obj;
	if (is_object($_zp_current_admin_obj) && !($_zp_current_admin_obj->getRights() & ADMIN_RIGHTS)) {
		$loggedin = user_expiry_checkexpires($loggedin, $_zp_current_admin_obj);
	}
	return $loggedin;
}

function user_expiry_checklogon($loggedin, $user) {
	global $_zp_authority;
	if ($loggedin) {
		if (!($loggedin & ADMIN_RIGHTS)) {
			$userobj = $_zp_authority->getAnAdmin(array('`user`=' => $user, '`valid`=' => 1));
			$loggedin = user_expiry_checkexpires($loggedin, $userobj);
		}
	}
	return $loggedin;

}

/**
 * Re-validates user's e-mail via ticket.
 */
function user_expiry_reverify($obj) {
	global $_zp_authority;
	//process any verifications posted
	if (isset($_GET['user_expiry_reverify'])) {
		$params = unserialize(pack("H*", trim(sanitize($_GET['user_expiry_reverify']),'.')));
		if ((time() - $params['date']) < 2592000) {
			$userobj = $_zp_authority->getAnAdmin(array('`user`='=>$params['user'], '`email`='=>$params['email'], '`valid`>' => 0));
			if ($userobj) {
				$credentials = $userobj->getCredentials();
				$credentials[] = 'expiry';
				$credentials[] = 'email';
				$credentials = array_unique($credentials);
			}
			$userobj->setCredentials($credentials);
			$userobj->setValid(1);
			$userobj->set('loggedin',date('Y-m-d H:i:s'));
			$userobj->save();

			$_zp_authority->logUser($userobj);
			header("Location: ".FULLWEBPATH.'/' . ZENFOLDER . '/admin.php');
			exit();
		}
	}
	return $obj;
}

function user_expiry_edit_admin($html, $userobj, $i, $background, $current, $local_alterrights) {
	global $_zp_current_admin_obj;
	if (!zp_loggedin(ADMIN_RIGHTS) && $userobj->getID() == $_zp_current_admin_obj->getID()) {
		$subscription = 86400*getOption('user_expiry_interval');
		$now = time();
		$warnInterval = $now + getOption('user_expiry_warn_interval')*86400;
		$expires = strtotime($userobj->getDateTime())+$subscription;
		$expires_display = date('Y-m-d',$expires);
		if ($expires < $warnInterval) {
			$expires_display = '<span style="color:red" class="tooltip" title="'.gettext('Expires soon').'">'.$expires_display.'</span>';
		}
		$msg = sprintf(gettext('Your subscription expires on %s'),$expires_display);
		$myhtml =
			'<tr'.((!$current)? ' style="display:none;"':'').' class="userextrainfo">
				<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top" colspan="3">'."\n".
					'<p class="notebox">'.$msg.'</p>'."\n".
				'</td>
			</tr>'."\n";
		$html = $myhtml.$html;

	}
	return $html;
}

function user_expiry_notify($tab, $subtab) {
	global $_zp_authority;
	if ($tab=='users' && $subtab='users') {
		if ($_zp_authority->getAnAdmin(array('`valid`>' => 1))) {
			echo '<p class="notebox">'.gettext('You have users whose credentials have expired'),'</p>';
		}
	}
}

?>