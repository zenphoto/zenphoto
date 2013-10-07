<?php

/**
 * User group management. You can create groups with common <i>rights</i> and assign users
 * to the groups. Then you can alter these user's rights simply by changing the <i>group</i> rights.
 *
 * Templates can also be used. The difference between a <i>group</i> and a <i>template</i> is that the latter
 * simply sets the user <i>rights</i> one time. Afterwards the user is independent from the <i>template</i>.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage users
 */
// force UTF-8 Ø

$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Provides rudimentary user groups.");
$plugin_author = "Stephen Billard (sbillard)";


zp_register_filter('admin_tabs', 'user_groups::admin_tabs');
zp_register_filter('admin_alterrights', 'user_groups::admin_alterrights');
zp_register_filter('save_admin_custom_data', 'user_groups::save_admin');
zp_register_filter('edit_admin_custom_data', 'user_groups::edit_admin');

class user_groups {

	/**
	 * Merges rights for multiple group memebership or templates
	 * @param object $userobj
	 * @param array $groups
	 */
	static function merge_rights($userobj, $groups) {
		global $_zp_authority;
		$templates = false;
		$custom = $objects = array();
		$oldgroups = $userobj->getGroup();
		$rights = 0;
		foreach ($groups as $key => $groupname) {
			if (empty($groupname)) {
				//	force the first template to happen
				$group = new Zenphoto_Administrator('', 0);
				$group->setName('template');
			} else {
				$group = Zenphoto_Authority::newAdministrator($groupname, 0);
			}
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
			$custom[] = $group->getCustomData();
		}

		$userobj->setCustomData(array_shift($custom)); //	for now it is first come, first served.
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
		return $newgroups != $oldgroups || $templates;
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
				$updated = self::merge_rights($userobj, $newgroups) || $updated;
			}
		}
		return $updated;
	}

	static function groupList($userobj, $i, $background, $current, $template) {
		global $_zp_authority, $_zp_zenpage, $_zp_gallery;
		$group = $userobj->getGroup();
		$admins = $_zp_authority->getAdministrators('groups');
		$groups = array();
		$hisgroups = explode(',', $userobj->getGroup());
		$admins = sortMultiArray($admins, 'user');
		foreach ($admins as $user) {
			if ($template || $user['name'] != 'template') {
				$groups[] = $user;
			}
		}
		if (empty($groups))
			return gettext('no groups established'); // no groups setup yet
		$grouppart = '
		<script type="text/javascript">
			// <!-- <![CDATA[
			function groupchange' . $i . '(type) {
				switch (type) {
				case 0:	//	none
					$(\'.user-' . $i . '\').prop(\'disabled\',false);
					$(\'.templatelist' . $i . '\').prop(\'checked\',false);
					$(\'.grouplist' . $i . '\').prop(\'checked\',false);
					break;
				case 1:	//	group
					$(\'.user-' . $i . '\').prop(\'disabled\',true);
					$(\'.user-' . $i . '\').prop(\'checked\',false);
					$(\'#noGroup_' . $i . '\').prop(\'checked\',false);
					$(\'.templatelist' . $i . '\').prop(\'checked\',false);
					break;
				case 2:	//	template
					$(\'.user-' . $i . '\').prop(\'disabled\',false);
					$(\'#noGroup_' . $i . '\').prop(\'checked\',false);
					$(\'.grouplist' . $i . '\').prop(\'checked\',false);
					break;
			}
		}
		//]]> -->
	</script>' . "\n";

		$grouppart .= '<ul class="customchecklist">' . "\n";
		$grouppart .= '<label title="' . gettext('*no group affiliation') . '"><input type="checkbox" id="noGroup_' . $i . '" name="' . $i . 'group[]" value="" onclick="groupchange' . $i . '(0);" />' . gettext('*no group selected') . '</label>' . "\n";

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
			$grouppart .= '<label title="' . html_encode($user['custom_data']) . $type . '"' . $highlight . '><input type="checkbox" class="' . $class . '" name="' . $i . 'group[]" value="' . $user['user'] . '" onclick="groupchange' . $i . '(' . $case . ');"' . $checked . ' />' . html_encode($user['user']) . '</label>' . "\n";
		}

		$grouppart .= "</ul>\n";

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
			$grouppart = self::groupList($userobj, $i, $background, $current, true);
		} else {
			$notice = '';
			if ($group = $userobj->getGroup()) {
				$grouppart = '<code>' . $group . '</code>';
			} else {
				$grouppart = '<code>' . gettext('no group affiliation') . '</code>';
			}
		}
		$result =
						"\n" . '<tr' . ((!$current) ? ' style="display:none;"' : '') . ' class="userextrainfo">' . "\n" .
						'<td width="20%"' . ((!empty($background)) ? ' style="' . $background . '"' : '') . ' valign="top">' . "\n" . sprintf(gettext('User group membership: %s'), $grouppart) . "\n" .
						"</td>\n<td" . ((!empty($background)) ? ' style="' . $background . '"' : '') . ">" . '<div class="notebox"><p>' . gettext('Templates are highlighted.') . $notice . '</p><p>' . gettext('<strong>Note:</strong> When a group is assigned <em>rights</em> and <em>managed objects</em> are determined by the group!') . '</p></div></td>' . "\n" .
						"</tr>\n";
		return $html . $result;
	}

	static function admin_tabs($tabs) {
		global $_zp_current_admin_obj;
		if ((zp_loggedin(ADMIN_RIGHTS) && $_zp_current_admin_obj->getID())) {
			if (isset($tabs['users']['subtabs'])) {
				$subtabs = $tabs['users']['subtabs'];
			} else {
				$subtabs = array();
			}
			$subtabs[gettext('users')] = 'admin-users.php?page=users&amp;tab=users';
			$subtabs[gettext('assignments')] = PLUGIN_FOLDER . '/user_groups/user_groups-tab.php?page=users&amp;tab=assignments';
			$subtabs[gettext('groups')] = PLUGIN_FOLDER . '/user_groups/user_groups-tab.php?page=users&amp;tab=groups';
			$tabs['users'] = array('text'		 => gettext("admin"),
							'link'		 => WEBPATH . "/" . ZENFOLDER . '/admin-users.php?page=users&amp;tab=users',
							'subtabs'	 => $subtabs,
							'default'	 => 'users');
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

}

?>