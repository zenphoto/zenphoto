<?php
/**
 * Provides rudimentary user groups
 *
 * @package plugins
 * @subpackage usermanagement
 */

// force UTF-8 Ø

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Provides rudimentary user groups.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';

zp_register_filter('admin_tabs', 'user_groups_admin_tabs');
zp_register_filter('admin_alterrights', 'user_groups_admin_alterrights');
zp_register_filter('save_admin_custom_data', 'user_groups_save_admin');
zp_register_filter('edit_admin_custom_data', 'user_groups_edit_admin');

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
function user_groups_save_admin($updated, $userobj, $i, $alter) {
	global $_zp_authority;
	if ($alter) {
		$administrators = $_zp_authority->getAdministrators('all');
		if (isset($_POST[$i.'group'])) {
			$groupname = sanitize($_POST[$i.'group']);
			$oldgroup = $userobj->getGroup();
			if (empty($groupname)) {
				if (!empty($oldgroup)) {
					$group = $_zp_authority->newAdministrator($oldgroup, 0);
					$userobj->setRights($group->getRights());
					$userobj->setObjects($group->getObjects());
				}
			} else {
				$group = $_zp_authority->newAdministrator($groupname, 0);
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
function user_groups_edit_admin($html, $userobj, $i, $background, $current) {
	global $gallery, $_zp_authority, $_zp_zenpage;
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
		foreach ($gallery->getAlbums() as $folder) {
			if (hasDynamicAlbumSuffix($folder)) {
				$name = substr($folder, 0, -4); // Strip the .'.alb' suffix
			} else {
				$name = $folder;
			}
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
		foreach ($_zp_authority->getRights() as $rightselement=>$right) {
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
			<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top" width="345">'.
				'</td>
			</tr>'."\n";
	return $html.$result;
}

function user_groups_admin_tabs($tabs) {
	global $_zp_null_account;
	if ((zp_loggedin(ADMIN_RIGHTS) && !$_zp_null_account)) {
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

function user_groups_admin_alterrights($alterrights, $userobj) {
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

?>