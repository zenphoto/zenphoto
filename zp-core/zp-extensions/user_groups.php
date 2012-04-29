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
 * @subpackage usermanagement
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
			$administrators = $_zp_authority->getAdministrators('all');
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
					$userobj->setRights($group->getRights());
					$userobj->setObjects($group->getObjects());
					if ($group->getName() == 'template') {
						$groupname = '';
					}
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
			$albumlist = array();
			$allalb = array();
			foreach ($_zp_gallery->getAlbums() as $folder) {
				$alb = new Album(NULL, $folder);
				$name = $alb->getTitle();
				$albumlist[$name] = $folder;
				$allalb[] = "'#managed_albums_".$i.'_'.postIndexEncode($folder)."'";
			}
			if (getOption('zp_plugin_zenpage')) {
				$pagelist = array();
				$allpag = array();
				$pages = $_zp_zenpage->getPages(false);
				foreach ($pages as $page) {
					if (!$page['parentid']) {
						$pagelist[get_language_string($page['title'])] = $page['titlelink'];
						$allpag[] = "'#managed_pages_".$i.'_'.postIndexEncode($page['titlelink'])."'";
					}
				}
				$newslist = array();
				$allnew = array();
				$categories = $_zp_zenpage->getAllCategories(false);
				foreach ($categories as $category) {
					$newslist[get_language_string($category['titlelink'])] = $category['title'];
					$allnew[] = "'#managed_news_".$i.'_'.postIndexEncode($category['titlelink'])."'";
				}
			}
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
						var albdisable = false;
						var checkedalbums = [];
						var checked = 0;
						var uncheckedalbums = [];
						var unchecked = 0;
						var allalbums = ['.implode(',', $allalb).'];
						var allalbumsc = '.count($allalb).';';
				if (getOption('zp_plugin_zenpage')) {
					$grouppart .=	'
							var allpages = ['.implode(',', $allpag).'];
							var allpagesc = '.count($allpag).';
							var allnews = ['.implode(',', $allnew).'];
							var allnewsc = '.count($allnew).';';
						}
			$grouppart .=	'
						var rights = ['.implode(',',$rights).'];
						var rightsc = '.count($rights).';
						for (i=0;i<rightsc;i++) {
							$(rights[i]).attr(\'disabled\',disable);
						}
						for (i=0;i<allalbumsc;i++) {
							$(allalbums[i]).attr(\'disabled\',disable);
						}';
				if (getOption('zp_plugin_zenpage')) {
					$grouppart .=	'
						for (i=0;i<allpagesc;i++) {
							$(allpages[i]).attr(\'disabled\',disable);
						}
						for (i=0;i<allnewsc;i++) {
							$(allnews[i]).attr(\'disabled\',disable);
						}';
				}
			$grouppart .=	'
						$(\'#hint'.$i.'\').html(obj.options[obj.selectedIndex].title);
						if (disable) {
							switch (obj.value) {';
			foreach ($groups as $user) {
				$grouppart .= '
								case \''.$user['user'].'\':
									target = '.$user['rights'].';';
				if (getOption('zp_plugin_zenpage')) {
					$codelist = array('album','pages','news');
				} else {
					$codelist = array('album');
				}
				foreach ($codelist as $mo) {
					$cv = populateManagedObjectsList($mo,$user['id']);
					switch ($mo) {
						case 'album':
							$xv = array_diff($albumlist, $cv);
							break;
						case 'pages':
							$xv = array_diff($pagelist, $cv);
							break;
						case 'news':
							$xv = array_diff($newslist, $cv);
							break;
					}

					$cvo = array();
					foreach ($cv as $moid) {
						$cvo[] = "'#managed_".$mo."_".$i.'_'.postIndexEncode($moid)."'";
					}
					$xvo = array();
					foreach ($xv as $moid) {
						$xvo[] = "'#managed_".$mo."_".$i.'_'.postIndexEncode($moid)."'";
					}
					$grouppart .= '
										checked'.$mo.' = ['.implode(',',$cvo).'];
										checked'.$mo.'c = '.count($cvo).';
										unchecked'.$mo.' = ['.implode(',',$xvo).'];
										unchecked'.$mo.'c = '.count($xvo).';';
				}
				if ($user['name']=='template') {
					$albdisable = 'false';
				} else {
					$albdisable = 'true';
				}
				$grouppart .= '
									break;';
			}
			$grouppart .= '
								}
							for (i=0;i<checkedalbumc;i++) {
								$(checkedalbum[i]).attr(\'checked\',\'checked\');
							}
							for (i=0;i<uncheckedalbumc;i++) {
								$(uncheckedalbum[i]).attr(\'checked\',\'\');
							}';
			foreach ($groups as $user) {
				$grouppart .= '
							for (i=0;i<checkedpagesc;i++) {
								$(checkedpages[i]).attr(\'checked\',\'checked\');
							}
							for (i=0;i<uncheckedpagesc;i++) {
								$(uncheckedpages[i]).attr(\'checked\',\'\');
							}
							for (i=0;i<checkednewsc;i++) {
								$(checkednews[i]).attr(\'checked\',\'checked\');
							}
							for (i=0;i<uncheckednewsc;i++) {
								$(uncheckednews[i]).attr(\'checked\',\'\');
							}';
			}
				$grouppart .= '
							for (i=0;i<rightsc;i++) {
								if ($(rights[i]).val()&target) {
									$(rights[i]).attr(\'checked\',\'checked\');
								} else {
									$(rights[i]).attr(\'checked\',\'\');
								}
							}
						}
					}';
			if (is_array($hisgroup)) {
				$grouppart .= '
					window.onload = function() {';
				foreach ($codelist as $mo) {
					$cv = populateManagedObjectsList($mo,$user['id']);
					switch ($mo) {
						case 'album':
							$list = $albumlist;
							break;
						case 'pages':
							$list = $pagelist;
							break;
						case 'news':
							$list = $newslist;
							break;
					}
					foreach ($list as $moid) {
						if (in_array($moid,$cv)) {
							$grouppart .= '
							$(\'#managed_'.$mo.'_'.$i.'_'.postIndexEncode($moid).'\').attr(\'checked\',\'checked\');';
						} else {
							$grouppart .= '
							$(\'#managed_'.$mo.'_'.$i.'_'.postIndexEncode($moid).'\').attr(\'checked\',\'\');';
						}
					}
				}
				$grouppart .= '
					}';
			}

			$grouppart .= '
					//]]> -->
				</script>';
			$grouppart .= '<select name="'.$i.'group" onchange="javascript:groupchange'.$i.'(this);"'.'>'."\n";
			$grouppart .= '<option value="" title="'.gettext('*no group affiliation').'">'.gettext('*no group selected').'</option>'."\n";
			$selected_hint = gettext('no group affiliation');
			foreach ($groups as $user) {
				if ($user['name']=='template') {
					$type = '<strong>'.gettext('Template:').'</strong> ';
				} else {
					$type = '';
				}
				$hint = $type.'<em>'.html_encode($user['custom_data']).'</em>';
				if ($group == $user['user']) {
					$selected = ' selected="selected"';
					$selected_hint = $hint;
					} else {
					$selected = '';
				}
				$grouppart .= '<option'.$selected.' value="'.$user['user'].'" title="'.sanitize($hint,3).'">'.$user['user'].'</option>'."\n";
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
							$grouppart.'<br />'.gettext('<strong>Note:</strong> When a group is assigned <em>rights</em> and <em>managed albums</em> are determined by the group!').'</td>
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