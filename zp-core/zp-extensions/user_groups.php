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

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Provides rudimentary user groups.");
$plugin_author = "Stephen Billard (sbillard)";


zp_register_filter('admin_tabs', 'user_groups::admin_tabs');
zp_register_filter('admin_alterrights', 'user_groups::admin_alterrights');
zp_register_filter('save_admin_custom_data', 'user_groups::save_admin');
zp_register_filter('edit_admin_custom_data', 'user_groups::edit_admin');

class user_groups {

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
		global $_zp_authority;
		if ($alter) {
			if (isset($_POST[$i.'group'])) {
				$groupname = sanitize($_POST[$i.'group']);
				$oldgroup = $userobj->getGroup();
				if (empty($groupname)) {
					if (!empty($oldgroup)) {
						$group = Zenphoto_Authority::newAdministrator($oldgroup, 0);
						$userobj->setRights($group->getRights());
						$userobj->setObjects($group->getObjects());
					}
				} else {
					$group = Zenphoto_Authority::newAdministrator($groupname, 0);
					$rights = $group->getRights();
					$objects = $group->getObjects();
					if ($group->getName() == 'template') {
						$groupname = '';
						$updated = true;
						if ($userobj->getID() > 0) {
							$before = Zenphoto_Authority::newAdministrator($userobj->getUser(), 1);
							$rights = $rights | $before->getRights();
							$objects = array_merge($objects, $before->getObjects());
						}
					}
					$userobj->setRights($rights);
					$userobj->setObjects($objects);
				}
				if ($groupname != $oldgroup) {
					$updated = true;
					$userobj->setGroup($groupname);
				}
			}
		}
		return $updated;
	}

	/**
	 * Returns table row(s) for edit of an admin user's custom data
	 *
	 * @param string $html always empty
	 * @param $userobj Admin user object
	 * @param string $i prefix for the admin
	 * @param string $background background color for the admin row
	 * @param bool $current true if this admin row is the logged in admin
	 * @return string
	 */
	static function edit_admin($html, $userobj, $i, $background, $current) {
		global $_zp_authority, $_zp_zenpage, $_zp_gallery;
		$group = $userobj->getGroup();
		$admins = $_zp_authority->getAdministrators('all');
		$ordered = array();
		$groups = array();
		$hisgroup = NULL;
		$adminordered = array();
		foreach ($admins as $key=>$admin) {
			$ordered[$key] = $admin['user'];
			if ($group == $admin['user']) $hisgroup = $admin;
		}
		asort($ordered);
		foreach ($ordered as $key=>$user) {
			$adminordered[] = $admins[$key];
			if (!$admins[$key]['valid']) {
				$groups[] = $admins[$key];
			}
		}
		if (empty($groups)) return ''; // no groups setup yet
		if (zp_loggedin(ADMIN_RIGHTS)) {
			$rights = array();
			foreach (Zenphoto_Authority::getRights() as $rightselement=>$right) {
				if ($right['display']) {
					$rights[] = "'#".$rightselement.'-'.$i."'";
				}
			}
			$grouppart =	'
				<script type="text/javascript">
					// <!-- <![CDATA[
					function groupchange'.$i.'(obj) {
						var disable = obj.value != \'\';
						var rights = ['.implode(',',$rights).'];
						$(\'.user-'.$i.'\').attr(\'disabled\',disable);
						$(\'#hint'.$i.'\').html(obj.options[obj.selectedIndex].title);
						if (disable) {
							$(\'.user-'.$i.'\').removeAttr(\'checked\');
							switch (obj.value) {';
			foreach ($groups as $user) {
				$grouppart .= '
								case \''.$user['user'].'\':
									target = '.$user['rights'].';
									break;';
			}
			$grouppart .= '
							}
							for (i=0;i<'.count($rights).';i++) {
								if ($(rights[i]).val()&target) {
									$(rights[i]).attr(\'checked\',\'checked\');
								}
							}
						}
					}';


			$grouppart .= '
					//]]> -->
				</script>';
			$grouppart .= '<select name="'.$i.'group" onchange="javascript:groupchange'.$i.'(this);"'.'>'."\n";
			$grouppart .= '<option value="" title="'.gettext('*no group affiliation').'">'.gettext('*no group selected').'</option>'."\n";
			$selected_hint = gettext('no group affiliation');
			if ($userobj->getID()>=0) {
				$notice = ' '.gettext("Applying a template to will merge the template with the current <em>rights</em> and <em>objects</em>.");
			} else {
				$notice = '';
			}
			foreach ($groups as $user) {
				if ($user['name']=='template') {
					$type = '<strong>'.gettext('Template:').'</strong> ';
					$background = ' style="background-color:#FFEFB7;"';
				} else {
					$background = $type = '';
				}
				$hint = $type.'<em>'.html_encode($user['custom_data']).'</em>';
				if ($group == $user['user']) {
					$selected = ' selected="selected"';
					$selected_hint = $hint;
					} else {
					$selected = '';
				}
				$grouppart .= '<option'.$selected.$background.' value="'.$user['user'].'" title="'.sanitize($hint,3).'">'.$user['user'].'</option>'."\n";
			}
			$grouppart .= '</select>'."\n";
			$grouppart .= '<span class="hint'.$i.'" id="hint'.$i.'" style="width:15em;">'.$selected_hint."</span>\n";
		} else {
			if ($group) {
				$grouppart = $group;
			} else {
				$grouppart = gettext('no group affiliation');
			}
			$grouppart = ' <em>'.$grouppart.'</em><input type="hidden" name="'.$i.'group" value="'.$group.'" />'."\n";
		}
		$result =
			'<tr'.((!$current)? ' style="display:none;"':'').' class="userextrainfo">
				<td colspan="2" width="20%"'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top">'.gettext('User group membership').
							$grouppart.'<p class="notebox">'.gettext('<strong>Note:</strong> When a group is assigned <em>rights</em> and <em>managed objects</em> are determined by the group!').$notice.'</p></td>
				</tr>'."\n";
		return $html.$result;
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
			$subtabs[gettext('assignments')] = PLUGIN_FOLDER.'/user_groups/user_groups-tab.php?page=users&amp;tab=assignments';
			$subtabs[gettext('groups')] = PLUGIN_FOLDER.'/user_groups/user_groups-tab.php?page=users&amp;tab=groups';
			$tabs['users'] = array(	'text'=>gettext("admin"),
															'link'=>WEBPATH."/".ZENFOLDER.'/admin-users.php?page=users&amp;tab=users',
															'subtabs'=>$subtabs,
															'default'=>'users');
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