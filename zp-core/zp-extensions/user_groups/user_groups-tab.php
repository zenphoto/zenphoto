<?php
/**
 * user_groups plugin--tabs
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage usermanagement
 */
define ('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-functions.php');
require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');

admin_securityChecks(NULL, currentRelativeURL(__FILE__));


$admins = $_zp_authority->getAdministrators('all');

$ordered = array();
foreach ($admins as $key=>$admin) {
	$ordered[$key] = $admin['user'];
}
asort($ordered);
$adminordered = array();
foreach ($ordered as $key=>$user) $adminordered[] = $admins[$key];

if (isset($_GET['action'])) {
	$action = sanitize($_GET['action']);
	XSRFdefender($action);
	$themeswitch = false;
	if ($action == 'deletegroup') {
		$groupname = trim(sanitize($_GET['group'],0));
		$groupobj = $_zp_authority->newAdministrator($groupname, 0);
		$groupobj->remove();
		// clear out existing user assignments
		$_zp_authority->updateAdminField('group', NULL, array('`valid`>='=>'1', '`group`='=>$groupname));
		header("Location: ".FULLWEBPATH."/".ZENFOLDER.'/'.PLUGIN_FOLDER.'/user_groups/user_groups-tab.php?page=users&tab=groups&deleted');
		exit();
	} else if ($action == 'savegroups') {
		for ($i = 0; $i < $_POST['totalgroups']; $i++) {
			$groupname = trim(sanitize($_POST[$i.'-group'],0));
			if (!empty($groupname)) {
				$rights = 0;
				$group = $_zp_authority->newAdministrator($groupname, 0);
				if (isset($_POST[$i.'-initgroup']) && !empty($_POST[$i.'-initgroup'])) {
					$initgroupname = trim(sanitize($_POST[$i.'-initgroup'],3));
					$initgroup = $_zp_authority->newAdministrator($initgroupname, 0);
					$rights = $initgroup->getRights();
					$group->setObjects(processManagedObjects($group->getID(),$rights));
					$group->setRights(NO_RIGHTS | $rights);
				} else {
					$rights = processRights($i);
					$group->setObjects(processManagedObjects($i,$rights));
					$group->setRights(NO_RIGHTS | $rights);
				}
				$group->setCustomData(trim(sanitize($_POST[$i.'-desc'], 3)));
				$group->setName(trim(sanitize($_POST[$i.'-type'], 3)));
				$group->setValid(0);
				$group->save();

				if ($group->getName()=='group') {
					//have to update any users who have this group designate.
					foreach ($admins as $admin) {
						if ($admin['valid'] && $admin['group']===$groupname) {
							$user = $_zp_authority->newAdministrator($admin['user'], $admin['valid']);
							$user->setRights($group->getRights());
							$user->setObjects($group->getObjects());
							$user->save();
						}
					}
					//user assignments: first clear out existing ones
					$_zp_authority->updateAdminField('group', NULL, array('`valid`>='=>'1', '`group`='=>$groupname));
					//then add the ones marked
					$target = 'user_'.$i.'-';
					foreach ($_POST as $item=>$username) {
						$item = sanitize(postIndexDecode($item));
						if (strpos($item, $target)!==false) {
							$username = substr($item, strlen($target));
							$user = $_zp_authority->getAnAdmin(array('`user`=' => $username, '`valid`>=' => 1));
							$user->setRights($group->getRights());
							$user->setObjects($group->getObjects());
							$user->setGroup($groupname);
							$user->save();
						}
					}
				}
			}
		}
		header("Location: ".FULLWEBPATH."/".ZENFOLDER.'/'.PLUGIN_FOLDER.'/user_groups/user_groups-tab.php?page=users&tab=groups&saved');
		exit();
	} else if ($action == 'saveauserassignments') {
		for ($i = 0; $i < $_POST['totalusers']; $i++) {
			$username = trim(sanitize($_POST[$i.'-user'],3));
			$user = $_zp_authority->getAnAdmin(array('`user`=' => $username, '`valid`>=' => 1));
			$groupname = trim(sanitize($_POST[$i.'-group'],3));
			$group = $_zp_authority->newAdministrator($groupname, 0);
			if (empty($groupname)) {
				$user->setGroup(NULL);
			} else {
				$user->setObjects(processManagedObjects($group->getID(),$rights));
				$user->setRights($group->getRights() | NO_RIGHTS);
				$user->setGroup($groupname);
			}
			$user->save();
		}
		header("Location: ".FULLWEBPATH."/".ZENFOLDER.'/'.PLUGIN_FOLDER.'/user_groups/user_groups-tab.php?page=users&tab=assignments&saved');
		exit();
	}
}

printAdminHeader('users');
?>
<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER;?>/js/sprintf.js"></script>
<?php
echo '</head>'."\n";
?>

<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php
			if (isset($_GET['deleted'])) {
				echo '<div class="messagebox fade-message">';
				echo  "<h2>".gettext('Deleted')."</h2>";
				echo '</div>';
			}
			if (isset($_GET['saved'])) {
				echo '<div class="messagebox fade-message">';
				echo  "<h2>".gettext('Saved')."</h2>";
				echo '</div>';
			}
			$subtab = printSubtabs();
			?>
			<div id="tab_users" class="tabbox">
				<?php
				zp_apply_filter('admin_note','users', $subtab);
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
							}
						}
						$gallery = new Gallery();
						$albumlist = array();
						foreach ($gallery->getAlbums() as $folder) {
							if (hasDynamicAlbumSuffix($folder)) {
								$name = substr($folder, 0, -4); // Strip the .'.alb' suffix
							} else {
								$name = $folder;
							}
							$albumlist[$name] = $folder;
						}
						?>
						<p>
							<?php
							echo gettext("Set group rights and select one or more albums for the users in the group to manage. Users with <em>User admin</em> or <em>Manage all albums</em> rights can manage all albums. All others may manage only those that are selected.");
							?>
						</p>
						<form action="?action=savegroups&amp;tab=groups" method="post" autocomplete="off" onsubmit="return checkSubmit()" >
							<?php XSRFToken('savegroups');?>
							<p class="buttons">
							<button type="submit" title="<?php echo gettext("Apply"); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
							<button type="reset" title="<?php echo gettext("Reset"); ?>"><img src="../../images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
							</p>
							<br clear="all" /><br /><br />
							<input type="hidden" name="savegroups" value="yes" />
							<table class="bordered">
								<?php
								$id = 0;
								$groupselector = $groups;
								$groupselector[''] = array('id' => -1,  'user' => '', 'name'=>'group', 'rights' => ALL_RIGHTS ^ MANAGE_ALL_ALBUM_RIGHTS, 'valid' => 0, 'custom_data'=>'');
								foreach($groupselector as $key=>$user) {
									$groupname = $user['user'];
									$groupid = $user['id'];
									$rights = $user['rights'];
									$grouptype = $user['name'];
									$desc = $user['custom_data'];
									if ($grouptype == 'group') {
										$kind = gettext('group');
									} else {
										$kind = gettext('template');
									}
									?>
									<tr>
										<td style="border-top: 4px solid #D1DBDF;?>" valign="top">
										<?php
											if (empty($groupname)) {
												?>
												<em>
													<label><input type="radio" name="<?php echo $id; ?>-type" value="group" checked="checked" onclick="javascrpt:toggle('users<?php echo $id; ?>');" /><?php echo gettext('group'); ?></label>
													<label><input type="radio" name="<?php echo $id; ?>-type" value="template" onclick="javascrpt:toggle('users<?php echo $id; ?>');" /><?php echo gettext('template'); ?></label>
												</em>
												<br />
												<input type="text" size="35" id="group-<?php echo $id ?>" name="<?php echo $id ?>-group" value="" />
												<?php
											} else {
												?>
												<em><?php echo $kind; ?></em>: <strong><?php echo $groupname; ?></strong>
												<input type="hidden" id="group-<?php echo $id ?>" name="<?php echo $id ?>-group" value="<?php echo html_encode($groupname); ?>" />
												<input type="hidden" name="<?php echo $id ?>-type" value="<?php echo html_encode($grouptype); ?>" />
												<?php
											}
											?>
											<br /><br />
											<input type="hidden" name="<?php echo $id ?>-confirmed" value="1" />
											<?php
											printAdminRightsTable($id, '', '', $rights);
											?>
										</td>
										<td style="border-top:4px solid #D1DBDF;width:20em;" valign="top" >
										<?php
											if (empty($groupname) && !empty($groups)) {
												?>
												<?php echo gettext('clone:'); ?>
												<br />
												<select name="<?php echo $id; ?>-initgroup" onchange="javascript:$('#hint<?php echo $id; ?>').html(this.options[this.selectedIndex].title);">
													<option title=""></option>
													<?php
													foreach ($groups as $user) {
														$hint = '<em>'.html_encode($user['custom_data']).'</em>';
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
											<?php echo gettext('description:'); ?>
											<br />
											<textarea name="<?php echo $id; ?>-desc" cols="40" rows="4"><?php echo html_encode($desc); ?></textarea>

											<br /><br />
												<div id="users<?php echo $id; ?>" <?php if ($grouptype=='template') echo ' style="display:none"' ?>>
												<h2 class="h2_bordered_edit"><?php echo gettext("Assign users"); ?></h2>
												<div class="box-tags-unpadded">
													<?php
													$members = array();
													if (!empty($groupname)) {
														foreach ($adminlist as $user) {
															if ($user['valid'] && $user['group']==$groupname) {
																$members[] = $user['user'];
															}
														}
													}
													?>
													<ul class="shortchecklist">
													<?php generateUnorderedListFromArray($members, $users, 'user_'.$id.'-', false, true, false); ?>
													</ul>
												</div>
											</div>
										<?php
											printManagedObjects('albums', $albumlist, '', $groupid, $id, $rights, $kind);
											if (getOption('zp_plugin_zenpage')) {
												$pagelist = array();
												$pages = $_zp_zenpage->getPages(false);
												foreach ($pages as $page) {
													if (!$page['parentid']) {
														$pagelist[get_language_string($page['title'])] = $page['titlelink'];
													}
												}
												printManagedObjects('pages',$pagelist, '', $groupid, $id, $rights, $kind);
												$newslist = array();
												$categories = $_zp_zenpage->getAllCategories(false);
												foreach ($categories as $category) {
													$newslist[get_language_string($category['title'])] = $category['titlelink'];
												}
												printManagedObjects('news',$newslist, '', $groupid, $id, $rights, $kind);
											}
											?>
										</td>
										<td style="border-top: 4px solid #D1DBDF;" valign="top">
										<?php
										if (!empty($groupname)) {
											$msg = gettext('Are you sure you want to delete this group?');
											?>
											<a href="javascript:if(confirm(<?php echo "'".$msg."'"; ?>)) { launchScript('',['action=deletegroup','group=<?php echo addslashes($groupname); ?>','XSRFToken=<?php echo getXSRFToken('deletegroup')?>']); }"
																title="<?php echo gettext('Delete this group.'); ?>" style="color: #c33;">
												<img src="../../images/fail.png" style="border: 0px;" alt="Delete" />
											</a>
											<?php
										}
										?>
										</td>
									</tr>
									<?php
									$id++;
								}
								?>
							</table>
							<br />
							<p class="buttons">
							<button type="submit" title="<?php echo gettext("Apply"); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
							<button type="reset" title="<?php echo gettext("Reset"); ?>"><img src="../../images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
							</p>
							<input type="hidden" name="totalgroups" value="<?php echo $id; ?>" />
						</form>
						<script language="javascript" type="text/javascript">
							//<!-- <![CDATA[
							function checkSubmit() {
								newgroupid = <?php echo ($id-1); ?>;
								var c = 0;
								<?php
								foreach ($users as $name) {
									?>
									c = 0;
								  for (i=0;i<=newgroupid;i++) {
									  if ($('#user_'+i+'-<?php echo postIndexEncode($name); ?>').attr('checked')) c++;
									}
									if (c>1) {
										alert('<?php echo sprintf(gettext('User %s is assigned to more than one group.'), $name); ?>');
										return false;
									}
									<?php
								}
								?>
								newgroup = $('#group-'+newgroupid).val().replace(/^\s+|\s+$/g,"");
								if (newgroup=='') return true;
								if (newgroup.indexOf('?')>=0 || newgroup.indexOf('&')>=0 || newgroup.indexOf('"')>=0 || newgroup.indexOf('\'')>=0) {
									alert('<?php echo gettext('Group names may not contain "?", "&", or quotation marks.'); ?>');
									return false;
								}
								for (i=newgroupid-1;i>=0;i--) {
									if ($('#group-'+i).val() == newgroup) {
										alert(sprintf('<?php echo gettext('The group "%s" already exists.'); ?>',newgroup));
										return false;
									}
								}
								return true;
							}
							// ]]> -->
						</script>
						<br clear="all" /><br />
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
						<form action="?action=saveauserassignments" method="post" autocomplete="off" >
							<?php XSRFToken('saveauserassignments');?>
							<p class="buttons">
							<button type="submit" title="<?php echo gettext("Apply"); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
							<button type="reset" title="<?php echo gettext("Reset"); ?>"><img src="../../images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
							</p>
							<br clear="all" /><br /><br />
							<input type="hidden" name="saveauserassignments" value="yes" />
							<table class="bordered">
								<?php
								$id = 0;
								foreach ($adminordered as $user) {
									if ($user['valid']) {
										$group = $user['group'];
										?>
										<tr>
											<td width="20%" style="border-top: 1px solid #D1DBDF;" valign="top">
												<input type="hidden" name="<?php echo $id; ?>-user" value="<?php echo $user['user']; ?>" />
												<?php echo $user['user']; ?>
											</td>
											<td style="border-top: 1px solid #D1DBDF;" valign="top" >
												<select name="<?php echo $id; ?>-group" onchange="javascript:$('#hint<?php echo $id; ?>').html(this.options[this.selectedIndex].title);">
													<option title="<?php echo gettext('no group affiliation'); ?>"></option>
													<?php
													$selected_hint = gettext('no group affiliation');
													foreach ($groups as $user) {
														$hint = '<em>'.html_encode($user['custom_data']).'</em>';
														if ($group == $user['user']) {
															$selected = ' selected="selected"';
															$selected_hint = $hint;
															} else {
															$selected = '';
														}
														?>
														<option<?php echo $selected; ?> title="<?php echo $hint; ?>"><?php echo $user['user']; ?></option>
														<?php
													}
													?>
												</select>
												<span class="hint<?php echo $id; ?>" id="hint<?php echo $id; ?>" style="width:15em;"><?php echo $selected_hint; ?></span>
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
							<button type="submit" title="<?php echo gettext("Apply"); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
							<button type="reset" title="<?php echo gettext("Reset"); ?>"><img src="../../images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
							</p>
						<input type="hidden" name="totalusers" value="<?php echo $id; ?>" />
						</form>
						<br clear="all" /><br />
						<?php
						break;
				}
				?>
			</div>

		</div>
	</div>
</body>
</html>