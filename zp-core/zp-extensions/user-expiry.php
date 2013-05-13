<?php
/**
 *
 * Set the "interval" to the number of days before expiration
 * Upon expiration, the user will be "disabled". He will not have
 * login access to the gallery.
 *
 * If the user visits the gallery within one week prior to his expiry date
 * a mail will be sent to the user (if there is an email address)
 * warning of the impending expiration.
 *
 * There is a subtab added to the "admin" tab for managing expirations.
 * A list of users without <var>ADMIN_RITGHTS</var> will be presented indicating each
 * user's expiry date. Dates within one week of expiry are shown in orange,
 * expired dates are shown in red.
 *
 * From this tab the user may be removed, disabled (enabled)
 * or renewed. (Renewal is for a new "interval" from his last renewal (or the
 * current date if adding the interval would not bring him up-to-date.)
 *
 * <b>NOTE:</b> This plugin does not expire users with <var>ADMIN_RIGHTS</var>!
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage users
 */

// force UTF-8 Ø

$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext("Provides management of users based on when they were created.");
$plugin_author = "Stephen Billard (sbillard)";


$option_interface = 'user_expiry';

zp_register_filter('admin_tabs', 'user_expiry::admin_tabs', 0);
zp_register_filter('authorization_cookie','user_expiry::checkcookie');
zp_register_filter('admin_login_attempt','user_expiry::checklogon');
zp_register_filter('federated_login_attempt','user_expiry::checklogon');
zp_register_filter('edit_admin_custom_data', 'user_expiry::edit_admin',999);
zp_register_filter('load_theme_script', 'user_expiry::reverify',999);
zp_register_filter('admin_note', 'user_expiry::notify',999);
zp_register_filter('can_set_user_password', 'user_expiry::passwordAllowed');
zp_register_filter('remove_user', 'user_expiry::cleanup');


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
		setOptionDefault('user_expiry_password_cycle', 0);
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return
				array(	gettext('Days until expiration') => array('key' => 'user_expiry_interval', 'type' => OPTION_TYPE_CLEARTEXT,
																		'order'=>1,
																		'desc' => gettext('The number of days until a user is flagged as expired. Set to zero for no expiry.')),
								gettext('Warning interval') => array('key' => 'user_expiry_warn_interval', 'type' => OPTION_TYPE_CLEARTEXT,
																		'order'=>2,
																		'desc' => gettext('The period in days before the expiry during which a warning message will be sent to the user. (If set to zero, no warning occurs.)')),
								gettext('Auto renew') => array('key' => 'user_expiry_auto_renew', 'type' => OPTION_TYPE_CHECKBOX,
																	'order'=>3,
																	'desc' => gettext('Automatically renew the subscription if the user visits during the warning period.')),
								gettext('Password cycle') => array('key' => 'user_expiry_password_cycle', 'type' => OPTION_TYPE_CLEARTEXT,
																	'order' => 4,
																	'desc' => gettext('Number of days between required password changes. Set to zero for no required changes.'))
		);
	}

	function handleOption($option, $currentValue) {
	}


	static function admin_tabs($tabs) {
		global $_zp_current_admin_obj, $_zp_loggedin;
		if (user_expiry::checkPasswordRenew()) {
			$_zp_current_admin_obj->setRights($_zp_loggedin = USER_RIGHTS | NO_RIGHTS);
			$tabs = array('users' => array('text'=>gettext("users"),
					'link'=>WEBPATH."/".ZENFOLDER.'/admin-users.php?page=users',
					'subtabs'=>NULL));
		}
		if (zp_loggedin(ADMIN_RIGHTS) && $_zp_current_admin_obj->getID()) {
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

	private static function checkexpires($loggedin, $userobj) {
		global $_zp_gallery;

		if ($userobj->logout_link!==true) {
			return $loggedin;
		}
		if (!$subscription = 86400*getOption('user_expiry_interval')) {
			// expiry is disabled
			return $loggedin;
		}
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
							$message = sprintf(gettext('Your user id for the Zenphoto site %s will expire on %s.'),$_zp_gallery->getTitle(),date('Y-m-d',$expires));
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

	static function checkPasswordRenew() {
		global $_zp_current_admin_obj;
		$threshold = getOption('user_expiry_password_cycle')*86400;
		if ($threshold && is_object($_zp_current_admin_obj) && !($_zp_current_admin_obj->getRights() & ADMIN_RIGHTS)) {
			if (strtotime($_zp_current_admin_obj->get('passupdate'))+$threshold < time()) {
				return true;
			}
		}
		return false;
	}

	static function cleanup($user) {
		query('DELETE FROM '.prefix('plugin_storage').' WHERE `type`='.db_quote('user_expiry_usedPasswords').' AND `aux`='.$user->getID());
	}

	static function passwordAllowed($msg, $pwd, $user) {
		if ($id = $user->getID()>0) {
			$store = query_single_row('SELECT * FROM '.prefix('plugin_storage').' WHERE `type`='.db_quote('user_expiry_usedPasswords').' AND `aux`='.$id);
			if ($store) {
				$used = unserialize($store['data']);
				if (in_array($pwd, $used)) {
					if (zp_loggedin(ADMIN_RIGHTS)) {	// persons with ADMIN_RIGHTS get to override this so they can reset a passwrod for a user
						unset($used[$pwd]);
					} else {
						return gettext('You have used that password recently. Please choose a different password.');
					}
				}
				if (count($used)>9) {
					$used = array_slice($used, 1);
				}
			} else {
				$used = array();
			}
			array_push($used, $pwd);
			if ($store) {
				query('UPDATE '.prefix('plugin_storage').'SET `data`='.db_quote(serialize($used)).' WHERE `type`='.db_quote('user_expiry_usedPasswords').' AND `aux`='.$id);
			} else {
				query('INSERT INTO '.prefix('plugin_storage').' (`type`, `aux`, `data`) VALUES ('.db_quote('user_expiry_usedPasswords').','.$id.','.db_quote(serialize($used)).')');
			}
		}
		return $msg;
	}

	static function checkcookie($loggedin) {
		global $_zp_current_admin_obj;
		if (is_object($_zp_current_admin_obj) && !($_zp_current_admin_obj->getRights() & ADMIN_RIGHTS)) {
			$loggedin = user_expiry::checkexpires($loggedin, $_zp_current_admin_obj);
		}
		return $loggedin;
	}

	static function checklogon($loggedin, $user) {
		if ($loggedin) {
			if (!($loggedin & ADMIN_RIGHTS)) {
				if ($userobj = Zenphoto_Authority::getAnAdmin(array('`user`=' => $user, '`valid`=' => 1))) {
					$loggedin = user_expiry::checkexpires($loggedin, $userobj);
				}
			}
		}
		return $loggedin;

	}

	/**
	 * Re-validates user's e-mail via ticket.
	 * @param string $path the script (which we ignore)
	 * @return string
	 */
	static function reverify($path) {
		//process any verifications posted
		if (isset($_GET['user_expiry_reverify'])) {
			$params = unserialize(pack("H*", trim(sanitize($_GET['user_expiry_reverify']),'.')));
			if ((time() - $params['date']) < 2592000) {
				$userobj = Zenphoto_Authority::getAnAdmin(array('`user`='=>$params['user'], '`email`='=>$params['email'], '`valid`>' => 0));
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

				Zenphoto_Authority::logUser($userobj);
				header("Location: ".FULLWEBPATH.'/' . ZENFOLDER . '/admin.php');
				exitZP();
			}
		}
		if (user_expiry::checkPasswordRenew()) {
			header("Location: ".FULLWEBPATH.'/' . ZENFOLDER . '/admin-users.php?page=users&amp;tab=users');
			exitZP();
		}
		return $path;
	}

	static function edit_admin($html, $userobj, $i, $background, $current, $local_alterrights) {
		global $_zp_current_admin_obj;
		if (!$userobj->getValid()) return $html;
		$subscription = 86400*getOption('user_expiry_interval');
		if ($subscription && !zp_loggedin(ADMIN_RIGHTS) && $userobj->getID() == $_zp_current_admin_obj->getID()) {
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
					<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top" colspan="2">'."\n".
						'<p class="notebox">'.$msg.'</p>'."\n".
					'</td>
				</tr>'."\n";
			$html = $myhtml.$html;

		}
		return $html;
	}

	static function notify($tab, $subtab) {
		if ($tab=='users' && $subtab='users') {
					if (user_expiry::checkPasswordRenew()) {
				echo '<p class="errorbox">'.gettext('You must change your password.'),'</p>';
			} else {
				if (Zenphoto_Authority::getAnAdmin(array('`valid`>' => 1))) {
					echo '<p class="notebox">'.gettext('You have users whose credentials have expired.'),'</p>';
				}
			}
		}
	}

}
?>
