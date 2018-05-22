<?php
/**
 * user_groups plugin--tabs
 * @author Stephen Billard (sbillard)
 * @package plugins/user_groups
 */
define('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');

admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL());
define('GROUPS_PER_PAGE', max(1, getOption('groups_per_page')));
if (isset($_GET['subpage'])) {
	$subpage = sanitize_numeric($_GET['subpage']);
} else {
	if (isset($_POST['subpage'])) {
		$subpage = sanitize_numeric($_POST['subpage']);
	} else {
		$subpage = 0;
	}
}

$admins = $_zp_authority->getAdministrators('all');

$adminordered = sortMultiArray($admins, 'user');

if (isset($_GET['action'])) {
	$action = sanitize($_GET['action']);
	$themeswitch = false;
	switch ($action) {
		case 'deletegroup':
			XSRFdefender('deletegroup');
			$groupname = trim(sanitize($_GET['group']));
			$groupobj = Zenphoto_Authority::newAdministrator($groupname, 0);
			$groupobj->remove();
			// clear out existing user assignments
			Zenphoto_Authority::updateAdminField('group', NULL, array('`valid`>=' => '1', '`group`=' => $groupname));
			header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/user_groups/user_groups-tab.php?page=admin&tab=groups&deleted&subpage=' . $subpage);
			exitZP();
		case 'savegroups':
			XSRFdefender('savegroups');
			if (isset($_POST['checkForPostTruncation'])) {
				$newgroupid = @$_POST['newgroup'];
				$grouplist = $_POST['user'];
				foreach ($grouplist as $i => $groupelement) {
					$groupname = trim(sanitize($groupelement['group']));
					if (!empty($groupname)) {
						$rights = 0;
						$group = Zenphoto_Authority::newAdministrator($groupname, 0);
						if (isset($groupelement['initgroup']) && !empty($groupelement['initgroup'])) {
							$initgroupname = trim(sanitize($groupelement['initgroup'], 3));
							$initgroup = Zenphoto_Authority::newAdministrator($initgroupname, 0);
							$rights = $initgroup->getRights();
							$group->setObjects(processManagedObjects($group->getID(), $rights));
							$group->setRights(NO_RIGHTS | $rights);
						} else {
							$rights = processRights($i);
							$group->setObjects(processManagedObjects($i, $rights));
							$group->setRights(NO_RIGHTS | $rights);
						}
						$group->setCredentials(trim(sanitize($groupelement['desc'], 3)));
						$group->setName(trim(sanitize($groupelement['type'], 3)));
						$group->setValid(0);
						$group->setDesc(trim(sanitize($groupelement['desc'], 3)));
						zp_apply_filter('save_admin_custom_data', true, $group, $i, true);
						$group->save();

						if ($group->getName() == 'group') {
							//have to update any users who have this group designate.
							$groupname = $group->getUser();
							foreach ($admins as $admin) {
								if ($admin['valid']) {
									$hisgroups = explode(',', $admin['group']);
									if (in_array($groupname, $hisgroups)) {
										$userobj = Zenphoto_Authority::newAdministrator($admin['user'], $admin['valid']);
										user_groups::merge_rights($userobj, $hisgroups, user_groups::getPrimeObjects($userobj));
										$userobj->save();
									}
								}
							}
							//user assignments: first clear out existing ones
							Zenphoto_Authority::updateAdminField('group', NULL, array('`valid`>=' => '1', '`group`=' => $groupname));
							if (isset($groupelement['userlist'])) {
								//then add the ones marked
								foreach ($groupelement['userlist'] as $list) {
									$username = $list['checked'];
									$userobj = $_zp_authority->getAnAdmin(array('`user`=' => $username, '`valid`>=' => 1));
									user_groups::merge_rights($userobj, array(1 => $groupname), user_groups::getPrimeObjects($userobj));
									$userobj->save();
								}
							}
						}
					}
				}
				$notify = '&saved';
			} else {
				$notify = '&post_error';
			}

			header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/user_groups/user_groups-tab.php?page=admin&tab=groups&subpage=' . $subpage . $notify);
			exitZP();
		case 'saveauserassignments':
			XSRFdefender('saveauserassignments');
			if (isset($_POST['checkForPostTruncation'])) {
				$userlist = $_POST['user'];
				foreach ($userlist as $i => $user) {
					if (isset($user['group'])) {
						$newgroups = sanitize($user['group']);
						$username = trim(sanitize($user['userid'], 3));
						$userobj = $_zp_authority->getAnAdmin(array('`user`=' => $username, '`valid`>=' => 1));
						user_groups::merge_rights($userobj, $newgroups, user_groups::getPrimeObjects($userobj));
						$userobj->save();
					}
				}
				$notify = '&saved';
			} else {
				$notify = '&post_error';
			}
			header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/user_groups/user_groups-tab.php?page=admin&tab=assignments&subpage=' . $subpage . $notify);
			exitZP();
	}
}

printAdminHeader('admin');
$background = '';
?>
<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/sprintf.js"></script>
<?php
echo '</head>' . "\n";
?>

<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php
			if (isset($_GET['post_error'])) {
				echo '<div class="errorbox">';
				echo "<h2>" . gettext('Error') . "</h2>";
				echo gettext('The form submission is incomplete. Perhaps the form size exceeds configured server or browser limits.');
				echo '</div>';
			}
			if (isset($_GET['deleted'])) {
				echo '<div class="messagebox fade-message">';
				echo "<h2>" . gettext('Deleted') . "</h2>";
				echo '</div>';
			}
			if (isset($_GET['saved'])) {
				echo '<div class="messagebox fade-message">';
				echo "<h2>" . gettext('Saved') . "</h2>";
				echo '</div>';
			}
			$subtab = getCurrentTab();
			zp_apply_filter('admin_note', 'admin', $subtab);
			?>
			<h1>
				<?php
				if ($subtab == 'groups') {
					echo gettext('Groups');
				} else {
					echo gettext('Assignments');
				}
				?>
			</h1>

			<div id = "tab_users" class = "tabbox">
				<?php
				switch ($subtab) {
					case 'groups':
						$adminlist = $adminordered;
						$users = array();
						$groups = array();
						foreach ($adminlist as $user) {
							if ($user['valid']) {
								$users[] = $user['user'];
							} else {
								$groups[] = $user;
								$list[] = $user['user'];
							}
						}
						$max = floor((count($list) - 1) / GROUPS_PER_PAGE);
						if ($subpage > $max) {
							$subpage = $max;
						}
						$rangeset = getPageSelector($list, GROUPS_PER_PAGE);
						$groups = array_slice($groups, $subpage * GROUPS_PER_PAGE, GROUPS_PER_PAGE);
						if (count($groups) == 1) {
							$display = '';
						} else {
							$display = ' style="display:none"';
						}
						$albumlist = array();
						foreach ($_zp_gallery->getAlbums() as $folder) {
							$alb = newAlbum($folder);
							$name = $alb->getTitle();
							$albumlist[$name] = $folder;
						}
						?>
						<p>
							<?php
							echo gettext("Set group rights and select one or more albums for the users in the group to manage. Users with <em>User admin</em> or <em>Manage all albums</em> rights can manage all albums. All others may manage only those that are selected.");
							?>
						</p>
						<form class="dirtylistening" onReset="setClean('savegroups_form');" id="savegroups_form" action="?action=savegroups&amp;tab=groups" method="post" autocomplete="off" onsubmit="return checkSubmit()" >
							<?php XSRFToken('savegroups'); ?>
							<p class="buttons">
								<button type="submit"><?php echo CHECKMARK_GREEN; ?> <?php echo gettext("Apply"); ?></strong></button>
								<button type="reset">
									<?php echo CROSS_MARK_RED; ?>
									<strong><?php echo gettext("Reset"); ?></strong>
								</button>
							</p>
							<br class="clearall">
							<br />
							<input type="hidden" name="savegroups" value="yes" />
							<input type="hidden" name="subpage" value="<?php echo $subpage; ?>" />

							<table class="bordered">
								<tr>
									<th>
										<?php
										if (count($groups) != 1) {
											?>
											<span style="font-weight: normal">
												<a onclick="toggleExtraInfo('', 'user', true);"><?php echo gettext('Expand all'); ?></a>
												|
												<a onclick="toggleExtraInfo('', 'user', false);"><?php echo gettext('Collapse all'); ?></a>
											</span>
											<?php
										}
										?>
									</th>
									<th>
										<?php printPageSelector($subpage, $rangeset, PLUGIN_FOLDER . '/user_groups/user_groups-tab.php', array('page' => 'users', 'tab' => 'groups')); ?>
									</th>
								</tr>

								<?php
								$user_count = array();
								foreach ($admins as $key => $user) {
									if ($user['valid'] >= 1) {
										if (!empty($user['group'])) {
											$membership[$user['user']] = $belongs = explode(',', $user['group']);
											foreach ($belongs as $group) {
												if (!isset($user_count[$group])) {
													$user_count[$group] = 1;
												} else {
													$user_count[$group] ++;
												}
											}
										} else {
											$membership[$user['user']] = array();
										}
									}
								}

								$id = 0;
								$groupselector = $groups;
								$groupselector[''] = array('id' => -1, 'user' => '', 'name' => 'group', 'rights' => ALL_RIGHTS ^ MANAGE_ALL_ALBUM_RIGHTS, 'valid' => 0, 'other_credentials' => '');
								foreach ($groupselector as $key => $user) {
									$groupname = $user['user'];
									$groupid = $user['id'];
									$rights = $user['rights'];
									$grouptype = $user['name'];
									$desc = get_language_string($user['other_credentials']);
									$groupobj = new Zenphoto_Administrator($groupname, 0);
									if ($grouptype == 'group') {
										$kind = gettext('group');
										$count = ' (' . (int) @$user_count[$groupname] . ')';
									} else {
										$kind = gettext('template');
										$count = '';
									}
									if ($background) {
										$background = "";
									} else {
										$background = "background-color:#f0f4f5;";
									}
									?>
									<tr id="user-<?php echo $id; ?>">

										<td style="border-top: 4px solid #D1DBDF;<?php echo $background; ?>" valign="top" colspan="100%">
											<div class="user_left">
												<?php
												if (empty($groupname)) {
													?>
													<input type="hidden" name="newgroupid" value="<?php echo $id; ?>" />
													<em>
														<label>
															<input type="radio" name="user[<?php echo $id; ?>][type]" value="group" checked="checked" onclick="javascrpt:$('#users<?php echo $id; ?>').toggle();
																					toggleExtraInfo('<?php echo $id; ?>', 'user', true);" /><?php echo gettext('group'); ?>
														</label>
														<label>
															<input type="radio" name="user[<?php echo $id; ?>][type]" value="template" onclick="javascrpt:$('#users<?php echo $id; ?>').toggle();
																					toggleExtraInfo('<?php echo $id; ?>', 'user', true);" /><?php echo gettext('template'); ?>
														</label>
													</em>
													<br />
													<input type="text" size="35" id="group-<?php echo $id ?>" name="user[<?php echo $id ?>][group]" value=""
																 onclick="toggleExtraInfo('<?php echo $id; ?>', 'user', true);" />
																 <?php
															 } else {
																 ?>
													<span class="userextrashow">
														<em><?php echo $kind; ?></em>:
														<a onclick="toggleExtraInfo('<?php echo $id; ?>', 'user', true);" title="<?php echo $groupname; ?>" >
															<strong><?php echo $groupname; ?></strong> <?php echo $count; ?>
														</a>
													</span>
													<span style="display:none;" class="userextrahide">
														<em><?php echo $kind; ?></em>:
														<a onclick="toggleExtraInfo('<?php echo $id; ?>', 'user', false);" title="<?php echo $groupname; ?>" >
															<strong><?php echo $groupname; ?></strong> <?php echo $count; ?>
														</a>
													</span>
													<input type="hidden" id="group-<?php echo $id ?>" name="user[<?php echo $id ?>][group]" value="<?php echo html_encode($groupname); ?>" />
													<input type="hidden" name="user[<?php echo $id ?>][type]" value="<?php echo html_encode($grouptype); ?>" />
													<?php
												}
												?>
												<input type="hidden" name="user[<?php echo $id ?>][confirmed]" value="1" />
											</div>
											<div class="floatright">
												<?php
												if (!empty($groupname)) {
													$msg = gettext('Are you sure you want to delete this group?');
													?>
													<a href="javascript:if(confirm(<?php echo "'" . $msg . "'"; ?>)) { launchScript('',['tab=groups', 'action=deletegroup','group=<?php echo addslashes($groupname); ?>','XSRFToken=<?php echo getXSRFToken('deletegroup') ?>']); }"
														 title="<?php echo gettext('Delete this group.'); ?>" style="color: #c33;">
															 <?php echo WASTEBASKET; ?>
													</a>
													<?php
												}
												?>
											</div>
											<br class="clearall">
											<div class="user_left userextrainfo"<?php echo $display; ?>>
												<?php
												printAdminRightsTable($id, '  ', ' ', $rights);

												if (empty($groupname) && !empty($groups)) {
													?>
													<?php echo gettext('clone:'); ?>
													<br />
													<select name="user[<?php echo $id; ?>][initgroup]" onchange="javascript:$('#hint<?php echo $id; ?>').html(this.options[this.selectedIndex].title);">
														<option title=""></option>
														<?php
														foreach ($groups as $user) {
															$hint = '<em>' . html_encode($desc) . '</em>';
															if ($groupname == $user['user']) {
																$selected = ' selected="selected"';
															} else {
																$selected = '';
															}
															?>
															<option<?php echo $selected; ?> title="<?php echo $hint; ?>"><?php echo $user['user']; ?></option>
															<?php
														}
														?>
													</select>
													<span class="hint<?php echo $id; ?>" id="hint<?php echo $id; ?>"></span><br /><br />
													<?php
												}
												?>

											</div>
											<div class="user_right userextrainfo" <?php echo $display; ?>>
												<strong><?php echo gettext('description:'); ?></strong>
												<br />
												<textarea name="user[<?php echo $id; ?>][desc]" cols="40" rows="4"><?php echo html_encode($desc); ?></textarea>

												<br /><br />
												<div id="users<?php echo $id; ?>" <?php if ($grouptype == 'template') echo ' style="display:none"' ?>>
													<h2 class="h2_bordered_edit"><?php echo gettext("Assign users"); ?></h2>
													<div class="box-tags-unpadded">
														<?php
														$members = array();
														if (!empty($groupname)) {
															foreach ($adminlist as $user) {
																if ($user['valid']) {
																	if (in_array($groupname, $membership[$user['user']])) {
																		$members[] = $user['user'];
																	}
																}
															}
														}
														?>
														<ul class="shortchecklist">
															<?php generateUnorderedListFromArray($members, $members, 'user[' . $id . '][userlist]', false, true, false, NULL, NULL, 2); ?>
															<?php generateUnorderedListFromArray(array(), array_diff($users, $members), 'user[' . $id . '][userlist]', false, true, false, NULL, NULL, 2); ?>
														</ul>
													</div>
												</div>

												<?php
												printManagedObjects('albums', $albumlist, NULL, $groupobj, $id, $kind, array());
												if (extensionEnabled('zenpage')) {
													$newslist = array();
													$categories = $_zp_CMS->getAllCategories(false);
													foreach ($categories as $category) {
														$newslist[get_language_string($category['title'])] = $category['titlelink'];
													}
													printManagedObjects('news_categories', $newslist, NULL, $groupobj, $id, $kind, NULL);
													$pagelist = array();
													$pages = $_zp_CMS->getPages(false);
													foreach ($pages as $page) {
														if (!$page['parentid']) {
															$pagelist[get_language_string($page['title'])] = $page['titlelink'];
														}
													}
													printManagedObjects('pages', $pagelist, NULL, $groupobj, $id, $kind, NULL);
												}
												?>

											</div>
											<br class="clearall">
											<div class="userextrainfo" <?php echo $display; ?>>
												<?php
												$custom = zp_apply_filter('edit_admin_custom_data', '', $groupobj, $id, $background, true, '');
												if ($custom) {
													echo stripTableRows($custom);
												}
												?>
											</div>
										</td>
									</tr>
									<?php
									$id++;
									$display = ' style="display:none"';
								}
								?>
								<tr>
									<th>
										<?php
										if (count($groups) != 1) {
											?>
											<span style="font-weight: normal">
												<a onclick="toggleExtraInfo('', 'user', true);"><?php echo gettext('Expand all'); ?></a>
												|
												<a onclick="toggleExtraInfo('', 'user', false);"><?php echo gettext('Collapse all'); ?></a>
											</span>
											<?php
										}
										?>
									</th>
									<th>
										<?php printPageSelector($subpage, $rangeset, PLUGIN_FOLDER . '/user_groups/user_groups-tab.php', array('page' => 'users', 'tab' => 'groups')); ?>
									</th>
								</tr>
							</table>
							<p class="buttons">
								<button type="submit"><?php echo CHECKMARK_GREEN; ?> <?php echo gettext("Apply"); ?></strong></button>
								<button type="reset">
									<?php echo CROSS_MARK_RED; ?>
									<strong><?php echo gettext("Reset"); ?></strong>
								</button>
							</p>
							<input type="hidden" name="totalgroups" value="<?php echo $id; ?>" />
							<input type="hidden" name="checkForPostTruncation" value="1" />
						</form>
						<script type="text/javascript">
							//<!-- <![CDATA[
							function checkSubmit() {
								newgroupid = <?php echo ($id - 1); ?>;
								var c = 0;
		<?php
		foreach ($users as $name) {
			?>
									c = 0;
									for (i = 0; i <= newgroupid; i++) {
										if ($('#user_' + i + '-<?php echo postIndexEncode($name); ?>').prop('checked'))
											c++;
									}
									if (c > 1) {
										alert('<?php echo sprintf(gettext('User %s is assigned to more than one group.'), $name); ?>');
										return false;
									}
			<?php
		}
		?>
								newgroup = $('#group-' + newgroupid).val().replace(/^\s+|\s+$/g, "");
								if (newgroup == '')
									return true;
								if (newgroup.indexOf('?') >= 0 || newgroup.indexOf('&') >= 0 || newgroup.indexOf('"') >= 0 || newgroup.indexOf('\'') >= 0) {
									alert('<?php echo gettext('Group names may not contain “?”, “&”, or quotation marks.'); ?>');
									return false;
								}
								for (i = newgroupid - 1; i >= 0; i--) {
									if ($('#group-' + i).val() == newgroup) {
										alert(sprintf('<?php echo gettext('The group “%s” already exists.'); ?>', newgroup));
										return false;
									}
								}
								return true;
							}
							// ]]> -->
						</script>
						<br class="clearall">
						<?php
						break;
					case 'assignments':
						$groups = array();
						foreach ($adminordered as $user) {
							if (!$user['valid'] && $user['name'] == 'group') {
								$groups[] = $user;
							}
						}
						?>
						<p>
							<?php
							echo gettext("Assign users to groups.");
							?>
						</p>
						<form class="dirtylistening" onReset="setClean('saveAssignments_form');" id="saveAssignments_form" action="?tab=assignments&amp;action=saveauserassignments" method="post" autocomplete="off" >
							<?php XSRFToken('saveauserassignments'); ?>
							<p class="buttons">
								<button type="submit"><?php echo CHECKMARK_GREEN; ?> <?php echo gettext("Apply"); ?></strong></button>
								<button type="reset">
									<?php echo CROSS_MARK_RED; ?>
									<strong><?php echo gettext("Reset"); ?></strong>
								</button>
							</p>
							<br class="clearall">
							<br />
							<div class="notebox">
								<?php echo gettext('<strong>Note:</strong> When a group is assigned <em>rights</em> and <em>managed objects</em> are determined by the group!'); ?>
							</div>
							<input type="hidden" name="saveauserassignments" value="yes" />
							<table class="bordered">
								<?php
								$id = 0;
								foreach ($adminordered as $user) {
									if ($user['valid']) {
										$userobj = new Zenphoto_Administrator($user['user'], $user['valid']);
										$group = $user['group'];
										?>
										<tr>
											<td width="20%" style="border-top: 1px solid #D1DBDF;" valign="top">
												<input type="hidden" name="user[<?php echo $id; ?>][userid]" value="<?php echo $user['user']; ?>" />
												<?php echo $user['user']; ?>
											</td>
											<td style="border-top: 1px solid #D1DBDF;" valign="top" >
												<?php echo user_groups::groupList($userobj, $id, '', $user['group'], false); ?>
											</td>
										</tr>
										<?php
										$id++;
									}
								}
								?>
							</table>
							<br />
							<p class="buttons">
								<button type="submit"><?php echo CHECKMARK_GREEN; ?> <?php echo gettext("Apply"); ?></strong></button>
								<button type="reset">
									<?php echo CROSS_MARK_RED; ?>
									<strong><?php echo gettext("Reset"); ?></strong>
								</button>
							</p>
							<input type="hidden" name="totalusers" value="<?php echo $id; ?>" />
							<input type="hidden" name="checkForPostTruncation" value="1" />
						</form>
						<br class="clearall">
						<?php
						break;
				}
				?>
			</div>

		</div>
	</div>
	<?php printAdminFooter(); ?>
</body>

</html>