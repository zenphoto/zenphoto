<?php

/**
 * User group management. You can create groups with common <i>rights</i> and assign users
 * to the groups. Then you can alter these user's rights simply by changing the <i>group</i> rights.
 *
 * Templates can also be used. The difference between a <i>group</i> and a <i>template</i> is that the latter
 * simply sets the user <i>rights</i> one time. Afterwards the user is independent from the <i>template</i>.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage users
 */
// force UTF-8 Ø

$plugin_is_filter = 10 | ADMIN_PLUGIN;
$plugin_description = gettext("Provides rudimentary user groups.");
$plugin_author = "Stephen Billard (sbillard)";


zp_register_filter('admin_tabs', 'user_groups::admin_tabs', 2000);
zp_register_filter('admin_alterrights', 'user_groups::admin_alterrights');
zp_register_filter('save_admin_custom_data', 'user_groups::save_admin');
zp_register_filter('edit_admin_custom_data', 'user_groups::edit_admin');

class user_groups {

	/**
	 * Merges rights for multiple group memebership or templates
	 * @param object $userobj
	 * @param array $groups
	 */
	static function merge_rights($userobj, $groups, $primeObjects) {
		global $_zp_authority;
		$templates = false;
		$objects = $primeObjects;
		$custom = array();
		$oldgroups = $userobj->getGroup();
		$oldrights = $userobj->getRights();
		$oldobjects = $userobj->getObjects();
		$rights = 0;
		foreach ($groups as $key => $groupname) {
			if (empty($groupname)) {
				//	force the first template to happen
				$group = new Zenphoto_Administrator('', 0);
				$group->setName('template');
			} else {
				$group = Zenphoto_Authority::newAdministrator($groupname, 0, false);
			}
			if ($group->loaded) {
				if ($group->getName() == 'template') {
					unset($groups[$key]);
					if ($userobj->getID() > 0 && !$templates) {
						//	fetch the existing rights and objects
						$templates = true; //	but only once!
						$rights = $userobj->getRights();
						$objects = $userobj->getObjects();
					}
				}
				$rights = $group->getRights() | $rights;
				$objects = array_merge($group->getObjects(), $objects);
			} else {
				unset($groups[$key]);
			}
		}

		$userobj->setCustomDataset(array_shift($custom)); //	for now it is first come, first served.
		// unique objects
		$newobjects = array();
		foreach ($objects as $object) {
			$key = serialize(array('type' => $object['type'], 'data' => $object['data']));
			if (array_key_exists($key, $newobjects)) {
				if (array_key_exists('edit', $object)) {
					$newobjects[$key]['edit'] = @$newobjects[$key]['edit'] | $object['edit'];
				}
			} else {
				$newobjects[$key] = $object;
			}
		}
		$objects = array();
		foreach ($newobjects as $object) {
			$objects[] = $object;
		}
		$userobj->setGroup($newgroups = implode(',', $groups));
		$userobj->setRights($rights);
		$userobj->setObjects($objects);

		$updated = $newgroups != $oldgroups || $oldobjects != $objects || (empty($newgroups) && $rights != $oldrights);
		return $updated;
	}

	/**
	 * Saves admin custom data
	 * Called when an admin is saved
	 *
	 * @param string $updated true if there has been an update to the user
	 * @param object $userobj admin user object
	 * @param string $i prefix for the admin
	 * @param bool $alter will be true if critical admin data may be altered
	 * @return bool
	 */
	static function save_admin($updated, $userobj, $i, $alter) {
		if ($alter && $userobj->getValid()) {
			if (isset($_POST[$i . 'group'])) {
				$newgroups = sanitize($_POST[$i . 'group']);
				$updated = self::merge_rights($userobj, $newgroups, self::getPrimeObjects($userobj)) || $updated;
			}
		}
		return $updated;
	}

	static function groupList($userobj, $i, $background, $current, $template) {
		global $_zp_authority;
		$group = $userobj->getGroup();
		$admins = $_zp_authority->getAdministrators('groups');
		$membership = $groups = array();
		$hisgroups = explode(',', $userobj->getGroup());

		$userid = $userobj->getUser();
		$admins = sortMultiArray($admins, 'user');
		foreach ($admins as $user) {
			if (in_array($user['user'], $hisgroups)) {
				$membership[] = $user;
			} else {
				if ($template || $user['name'] != 'template') {
					$groups[] = $user;
				}
			}
		}
		$groups = array_merge($membership, array(array('name' => '', 'user' => '', 'other_credentials' => '')), $groups);
		if (empty($groups))
			return gettext('no groups established'); // no groups setup yet

		$grouppart = '<ul class="customchecklist scrollable_list" >' . "\n";

		foreach ($groups as $key => $user) {
			if ($user['name'] == 'template') {
				$type = gettext(' (Template)');
				$highlight = ' class="grouphighlight"';
				$class = 'templatelist' . $i;
				$case = 2;
			} else {
				$type = $highlight = '';
				$class = 'grouplist' . $i;
				$case = 1;
			}
			if (in_array($user['user'], $hisgroups)) {
				$checked = ' checked="checked"';
			} else {
				$checked = '';
			}
			if (empty($user['user'])) {
				$display = gettext('*no group selected');
				$case = 0;
				if (empty($hisgroups)) {
					$checked = ' checked="checked"';
				}
			} else {
				$display = $user['user'];
			}
			$grouppart .= '<label title="' . html_encode($user['other_credentials']) . $type . '"' . $highlight . '><input type="checkbox" class="' . $class . '" name="' . $i . 'group[]" id="' . $user['user'] . '_' . $i . '" value="' . $user['user'] . '" onclick="groupchange' . $i . '(' . $case . ');"' . $checked . ' />' . html_encode($display) . '</label>' . "\n";
		}

		$grouppart .= "</ul>\n";
		$grouppart .= '
		<script type="text/javascript">
			// <!-- <![CDATA[' . "\n";
		if ($primealbum = $userobj->getAlbum()) {
			//	allow editing of primary album management
			$grouppart .= '
			$(\'#managed_albums_list_' . $i . '_' . postIndexEncode($primealbum->name) . '_element\').find(\'input\').removeAttr(\'class\');
				$(\'#managed_albums_list_' . $i . '_' . postIndexEncode($primealbum->name) . '_element\').find(\'input\').removeAttr(\'disabled\');	' . "\n";
		}
		$grouppart .= '
			function groupchange' . $i . '(type) {
				switch (type) {
				case 0:	//	none
					$(\'.user-' . $i . '\').prop(\'disabled\',false);
					$(\'.templatelist' . $i . '\').prop(\'checked\',false);
					$(\'.grouplist' . $i . '\').prop(\'checked\',false);
					$(\'#_' . $i . '\').prop(\'checked\',true);
					break;
				case 1:	//	group
					$(\'#_' . $i . '\').prop(\'disabled\',false);
					$(\'#_' . $i . '\').prop(\'checked\',false);
					$(\'.user-' . $i . '\').prop(\'disabled\',true);
					$(\'.user-' . $i . '\').prop(\'checked\',false);
					$(\'.templatelist' . $i . '\').prop(\'checked\',false);
					break;
				case 2:	//	template
					$(\'.user-' . $i . '\').prop(\'disabled\',false);
					$(\'.grouplist' . $i . '\').prop(\'checked\',false);
					$(\'#_' . $i . '\').prop(\'checked\',false);
					break;
			}
		}
		//]]> -->';
		$grouppart .= '</script>' . "\n";
		return $grouppart;
	}

	/**
	 * Returns table row(s) for edit of an admin user's custom data
	 *
	 * @param string $html
	 * @param $userobj Admin object
	 * @param string $i prefix for the admin
	 * @param string $background background color for the admin row
	 * @param bool $current true if this admin row is the logged in admin
	 * @return string
	 */
	static function edit_admin($html, $userobj, $i, $background, $current) {
		if (!$userobj->getValid())
			return $html;
		if (zp_loggedin(ADMIN_RIGHTS)) {
			if ($userobj->getID() >= 0) {
				$notice = ' ' . gettext("Applying a template will merge the template with the current <em>rights</em> and <em>objects</em>.");
			} else {
				$notice = '';
			}
			$notice = '<div class="notebox"><p>' . gettext('Templates are highlighted.') . $notice . '</p><p>' . gettext('<strong>Note:</strong> When a group is assigned <em>rights</em> and <em>managed objects</em> are determined by the group!') . '</p></div>';
			$grouppart = self::groupList($userobj, $i, $background, $current, true);
		} else {
			$notice = '';
			if ($group = $userobj->getGroup()) {
				$grouppart = '<code>' . $group . '</code>';
			} else {
				$grouppart = '<code>' . gettext('no group affiliation') . '</code>';
			}
		}
		$result = '<div class="user_left">' . "\n" . sprintf(gettext('User group membership: %s'), $grouppart) . "\n"
						. '</div>' . "\n"
						. '<div class="user_right user_column">' . "\n"
						. '<br />'
						. $notice
						. '</div>' . "\n"
						. '<br class="clearall">' . "\n";
		return $html . $result;
	}

	static function admin_tabs($tabs) {
		global $_zp_current_admin_obj;
		if ((zp_loggedin(ADMIN_RIGHTS) && $_zp_current_admin_obj->getID())) {
			$subtabs = $tabs['admin']['subtabs'];
			$subtabs[gettext('groups')] = PLUGIN_FOLDER . '/user_groups/user_groups-tab.php?page=admin&tab=groups';
			$subtabs[gettext('assignments')] = PLUGIN_FOLDER . '/user_groups/user_groups-tab.php?page=admin&tab=assignments';
			$tabs['admin']['subtabs'] = $subtabs;
		}
		return $tabs;
	}

	static function admin_alterrights($alterrights, $userobj) {
		global $_zp_authority;
		$group = $userobj->getGroup();
		$admins = $_zp_authority->getAdministrators('groups');
		foreach ($admins as $admin) {
			if ($group == $admin['user']) {
				return ' disabled="disabled"';
			}
		}
		return $alterrights;
	}

	static function getPrimeObjects($user) {
		if ($primeAlbum = $user->getAlbum()) {
			$saveobjects = $user->getObjects();
			$prime = $primeAlbum->name;
			foreach ($saveobjects as $key => $oldobj) {
				if ($oldobj['type'] != 'album' || $oldobj['name'] != $prime) {
					unset($saveobjects[$key]);
				}
			}
		} else {
			$saveobjects = array();
		}
		return $saveobjects;
	}

}

?>