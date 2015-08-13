<?php

/*
 * LDAP authorization module
 * Use to link ZenPhoto20 to an LDAP server for user verification.
 * It assumes that your LDAP server contains posix-style users and groups.
 *
 * @author Stephen Billard (sbillard), Arie (ariep)
 *
 * @package alt
 * @subpackage users
 */

define('LDAP_DOMAIN', getOption('ldap_domain'));
define('LDAP_BASEDN', getOption('ldap_basedn'));
define('LDAP_ID_OFFSET', getOption('ldap_id_offset')); //	number added to LDAP ID to insure it does not overlap any ZP admin ids
define('LDAP_READER_USER', getOption('ldap_reader_user'));
define('LDAP_REAER_PASS', getOption('ldap_reader_pass'));
$_LDAPGroupMap = getSerializedArray(getOption('ldap_group_map'));

require_once(SERVERPATH . '/' . ZENFOLDER . '/lib-auth.php');
if (extensionEnabled('user_groups')) {
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/user_groups.php');
}

class Zenphoto_Authority extends _Authority {

	function getOptionsSupported() {
		setOptionDefault('ldap_id_offset', 100000);
		$options = parent::getOptionsSupported();
		$ldapOptions = LDAP_auth_options::getOptionsSupported();
		return array_merge($ldapOptions, $options);
	}

	function handleOption($option, $currentValue) {
		LDAP_auth_options::handleOption($option, $currentValue);
		parent::handleOption($option, $currentValue);
	}

	function handleOptionSave($themename, $themealbum) {
		LDAP_auth_options::handleOptionSave($themename, $themealbum);
		parent::handleOptionSave($themename, $themealbum);
	}

	function handleLogon() {
		global $_zp_current_admin_obj;
		$user = sanitize(@$_POST['user'], 0);
		$password = sanitize(@$_POST['pass'], 0);
		$loggedin = false;

		$ad = self::ldapInit(LDAP_DOMAIN);
		if ($ad) {
			$userdn = "uid={$user},ou=Users," . LDAP_BASEDN;

			// We suppress errors in the binding process, to prevent a warning
			// in the case of authorisation failure.
			if (@ldap_bind($ad, $userdn, $password)) { //	valid LDAP user
				self::ldapReader($ad);
				$userData = array_change_key_case(self::ldapUser($ad, "(uid={$user})"), CASE_LOWER);
				$userobj = self::setupUser($ad, $userData);
				if ($userobj) {
					$_zp_current_admin_obj = $userobj;
					$loggedin = $_zp_current_admin_obj->getRights();
					self::logUser($_zp_current_admin_obj);
					if (DEBUG_LOGIN) {
						debugLog(sprintf('LDAPhandleLogon: authorized as %1$s->%2$X', $userdn, $loggedin));
					}
				} else {
					if (DEBUG_LOGIN) {
						debugLog("LDAPhandleLogon: no rights");
					}
				}
			} else {
				if (DEBUG_LOGIN) {
					debugLog("LDAPhandleLogon: Could not bind to LDAP");
				}
			}
			@ldap_unbind($ad);
		}
		if ($loggedin) {
			return $loggedin;
		} else {
			// If the LDAP authorisation failed we try the standard logon, e.g. for a master administrator.
			return parent::handleLogon();
		}
	}

	function checkAuthorization($authCode, $id) {
		global $_zp_current_admin_obj;
		if (LDAP_ID_OFFSET && $id > LDAP_ID_OFFSET) { //	LDAP ID
			$ldid = $id - LDAP_ID_OFFSET;
			$ad = self::ldapInit(LDAP_DOMAIN);
			if ($ad) {
				self::ldapReader($ad);
				$userData = self::ldapUser($ad, "(uidNumber={$ldid})");
				if ($userData) {
					$userData = array_change_key_case($userData, CASE_LOWER);
					if (DEBUG_LOGIN) {
						debugLogBacktrace("LDAPcheckAuthorization($authCode, $ldid)");
					}
					$goodAuth = Zenphoto_Authority::passwordHash($userData['uid'][0], serialize($userData));
					if ($authCode == $goodAuth) {
						$userobj = self::setupUser($ad, $userData);
						if ($userobj) {
							$_zp_current_admin_obj = $userobj;
							$rights = $_zp_current_admin_obj->getRights();
						} else {
							$rights = 0;
						}
						if (DEBUG_LOGIN) {
							debugLog(sprintf('LDAPcheckAuthorization: from %1$s->%2$X', $authCode, $rights));
						}
					} else {
						if (DEBUG_LOGIN) {
							debugLog(sprintf('LDAPcheckAuthorization: AuthCode %1$s <> %2$s', $goodAuth, $authCode));
						}
					}
				}
				@ldap_unbind($ad);
			}
		}
		if ($_zp_current_admin_obj) {
			return $_zp_current_admin_obj->getRights();
		} else {
			return parent::checkAuthorization($authCode, $id);
		}
	}

	function validID($id) {
		return $id > LDAP_ID_OFFSET || parent::validID($id);
	}

	static function setupUser($ad, $userData) {
		global $_zp_authority;
		$user = $userData['uid'][0];
		$id = $userData['uidnumber'][0] + LDAP_ID_OFFSET;
		$name = $userData['cn'][0];
		$groups = self::getZPGroups($ad, $user);

		$adminObj = Zenphoto_Authority::newAdministrator('');
		$adminObj->setID($id);
		$adminObj->transient = true;

		if (isset($userData['email'][0])) {
			$adminObj->setEmail($userData['email'][0]);
		}
		$adminObj->setUser($user);
		$adminObj->setName($name);
		$adminObj->setPass(serialize($userData));
		if (class_exists('user_groups')) {
			user_groups::merge_rights($adminObj, $groups, array());
			if (DEBUG_LOGIN) {
				debugLogVar("LDAsetupUser: groups:", $adminObj->getGroup());
			}
			$rights = $adminObj->getRights() & ~ USER_RIGHTS;
			$adminObj->setRights($rights);
		} else {
			$rights = DEFAULT_RIGHTS & ~ USER_RIGHTS;
			$adminObj->setRights(DEFAULT_RIGHTS & ~ USER_RIGHTS);
		}

		if ($rights) {
			$_zp_authority->addOtherUser($adminObj);
			return $adminObj;
		}
		return NULL;
	}

	/*
	 * This function searches in LDAP tree ($ad -LDAP link identifier),
	 * starting under the branch specified by $basedn, for a single entry
	 * specified by $filter, and returns the requested attributes or null
	 * on failure.
	 */

	static function ldapSingle($ad, $filter, $basedn, $attributes) {
		$search = NULL;
		$lfdp = ldap_search($ad, $basedn, $filter, $attributes);
		if ($lfdp) {
			$entries = ldap_get_entries($ad, $lfdp);
			if ($entries['count'] != 0) {
				$search = $entries[0];
			}
		}
		ldap_free_result($lfdp);
		return $search;
	}

	static function ldapUser($ad, $filter) {
		return self::ldapSingle($ad, $filter, 'ou=Users,' . LDAP_BASEDN, array('uid', 'uidNumber', 'cn', 'email'));
	}

	/**
	 * returns an array the user's of ZenPhoto20 groups
	 * @param type $ad
	 */
	static function getZPGroups($ad, $user) {
		global $_LDAPGroupMap;
		$groups = array();
		foreach ($_LDAPGroupMap as $ZPgroup => $LDAPgroup) {
			if (!empty($LDAPgroup)) {
				$group = self::ldapSingle($ad, '(cn=' . $LDAPgroup . ')', 'ou=Groups,' . LDAP_BASEDN, array('memberUid'));
				if ($group) {
					$group = array_change_key_case($group, CASE_LOWER);
					$members = $group['memberuid'];
					unset($members['count']);
					$isMember = in_array($user, $members, true);
					if ($isMember) {
						$groups[] = $ZPgroup;
					}
				}
			}
		}
		return $groups;
	}

	static function ldapInit($domain) {
		if ($domain) {
			if ($ad = ldap_connect("ldap://{$domain}")) {

				ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);
				return $ad;
			} else {
				zp_error(gettext('Could not connect to LDAP server.'));
			}
		}
		return false;
	}

	/**
	 * login the ldapReader user if defined
	 */
	static function ldapReader($ad) {
		if (LDAP_READER_USER) {
			if (!@ldap_bind($ad, "uid=" . LDAP_READER_USER . ",ou=Users," . LDAP_BASEDN, LDAP_REAER_PASS)) {
				debugLog('LDAP reader authorization failed.');
			}
		}
	}

}

class Zenphoto_Administrator extends _Administrator {

	function setID($id) {
		$this->set('id', $id);
	}

	function setPass($pwd) {
		$hash = parent::setPass($pwd);
		$this->set('passupdate', NULL);
		return $hash;
	}

}

?>
