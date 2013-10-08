<?php
/**
 * provides the Options tab of admin
 * @package admin
 */
// force UTF-8 Ø

define('OFFSET_PATH', 1);

function markUpdated() {
	global $updated;
	$updated = true;
//for finding out who did it!	debugLogBacktrace('updated');
}

require_once(dirname(__FILE__) . '/admin-globals.php');
define('USERS_PER_PAGE', max(1, getOption('users_per_page')));

if (isset($_GET['ticket'])) {
	$ticket = '&ticket=' . sanitize($_GET['ticket']) . '&user=' . sanitize(@$_GET['user']);
} else {
	$ticket = '';
}
admin_securityChecks(USER_RIGHTS, currentRelativeURL());

$newuser = array();
$showset = array();
foreach ($_REQUEST as $param => $value) {
	if (strpos($param, 'show-') === 0) {
		$showset[] = substr($param, 5);
	}
}
if (isset($_GET['subpage'])) {
	$subpage = sanitize_numeric($_GET['subpage']);
} else {
	if (isset($_POST['subpage'])) {
		$subpage = sanitize_numeric($_POST['subpage']);
	} else {
		$subpage = 0;
	}
}

if (!isset($_GET['page']))
	$_GET['page'] = 'users';
$_current_tab = sanitize($_GET['page'], 3);

/* handle posts */
if (isset($_GET['action'])) {
	if (($action = sanitize($_GET['action'])) != 'saveoptions') {
		admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL());
	}
	$themeswitch = false;
	switch ($action) {
		case 'migrate_rights':
			XSRFdefender('migrate_rights');
			if (isset($_GET['revert'])) {
				$v = getOption('libauth_version') - 1;
			} else {
				$v = Zenphoto_Authority::$supports_version;
			}
			if ($_zp_authority->migrateAuth($v)) {
				$notify = '';
			} else {
				$notify = '&migration_error';
			}
			header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-users.php?page=users&subpage=" . $subpage . $notify);
			exitZP();
			break;
		case 'deleteadmin':
			XSRFdefender('deleteadmin');
			$adminobj = Zenphoto_Authority::newAdministrator(sanitize($_GET['adminuser']), 1);
			zp_apply_filter('save_user', '', $adminobj, 'delete');
			$adminobj->remove();
			header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-users.php?page=users&deleted&subpage=" . $subpage);
			exitZP();
			break;
		case 'saveoptions':
			XSRFdefender('saveadmin');
			$notify = $returntab = $msg = '';
			if (isset($_POST['saveadminoptions'])) {
				if (isset($_POST['checkForPostTruncation'])) {
					if (isset($_POST['alter_enabled']) || sanitize_numeric($_POST['totaladmins']) > 1 ||
									trim(sanitize($_POST['adminuser0'])) != $_zp_current_admin_obj->getUser() ||
									isset($_POST['0-newuser'])) {
						if (!$_zp_current_admin_obj->reset) {
							admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL());
						}
					}
					$alter = isset($_POST['alter_enabled']);
					$nouser = true;
					$returntab = $newuser = false;
					for ($i = 0; $i < sanitize_numeric($_POST['totaladmins']); $i++) {
						$updated = false;
						$error = false;
						$userobj = NULL;
						$pass = trim(sanitize($_POST['pass' . $i]));
						$user = trim(sanitize($_POST['adminuser' . $i]));
						if (empty($user) && !empty($pass)) {
							$notify = '?mismatch=nothing';
						}
						if (!empty($user)) {
							$nouser = false;
							if (isset($_POST[$i . '-newuser'])) {
								$newuser = $user;
								$userobj = Zenphoto_Authority::getAnAdmin(array('`user`='	 => $user, '`valid`>' => 0));
								if (is_object($userobj)) {
									$notify = '?exists';
									break;
								} else {
									$what = 'new';
									$userobj = Zenphoto_Authority::newAdministrator('');
									$userobj->setUser($user);
									markUpdated();
								}
							} else {
								$what = 'update';
								$userobj = Zenphoto_Authority::newAdministrator($user);
							}

							if (isset($_POST[$i . '-admin_name'])) {
								$admin_n = trim(sanitize(sanitize($_POST[$i . '-admin_name'])));
								if ($admin_n != $userobj->getName()) {
									markUpdated();
									$userobj->setName($admin_n);
								}
							}
							if (isset($_POST[$i . '-admin_email'])) {
								$admin_e = trim(sanitize($_POST[$i . '-admin_email']));
								if ($admin_e != $userobj->getEmail()) {
									markUpdated();
									$userobj->setEmail($admin_e);
								}
							}
							if (empty($pass)) {
								if ($newuser || @$_POST['passrequired' . $i]) {
									$msg = sprintf(gettext('%s password may not be empty!'), $admin_n);
								}
							} else {
								if (isset($_POST['disclose_password' . $i]) && $_POST['disclose_password' . $i] == 'on') {
									$pass2 = $pass;
								} else {
									$pass2 = trim(sanitize(@$_POST['pass_r' . $i]));
								}
								if ($pass == $pass2) {
									$pass2 = $userobj->getPass($pass);
									if ($msg = zp_apply_filter('can_set_user_password', false, $pass, $userobj)) {
										$notify = '?mismatch=format&error=' . urlencode($msg);
									} else {
										$userobj->setPass($pass);
										markUpdated();
									}
								} else {
									$notify = '?mismatch=password';
									$error = true;
								}
							}
							if (isset($_POST[$i . '-challengephrase'])) {
								$challenge = sanitize($_POST[$i . '-challengephrase']);
								$response = sanitize($_POST[$i . '-challengeresponse']);
								$info = $userobj->getChallengePhraseInfo();
								if ($challenge != $info['challenge'] || $response != $info['response']) {
									$userobj->setChallengePhraseInfo($challenge, $response);
									markUpdated();
								}
							}
							$lang = sanitize($_POST[$i . '-admin_language'], 3);
							if ($lang != $userobj->getLanguage()) {
								$userobj->setLanguage($lang);
								markUpdated();
							}
							$rights = 0;
							if ($alter) {
								$oldrights = $userobj->getRights() & ~(ALBUM_RIGHTS | ZENPAGE_PAGES_RIGHTS | ZENPAGE_NEWS_RIGHTS);
								$rights = processRights($i);
								if (($rights & ~(ALBUM_RIGHTS | ZENPAGE_PAGES_RIGHTS | ZENPAGE_NEWS_RIGHTS)) != $oldrights) {
									$userobj->setRights($rights | NO_RIGHTS);
									markUpdated();
								}
								$oldobjects = sortMultiArray($userobj->getObjects(), 'data');
								$objects = sortMultiArray(processManagedObjects($i, $rights), 'data');
								if ($objects != $oldobjects) {
									$userobj->setObjects($objects);
									markUpdated();
								}
							} else {
								$oldobjects = $userobj->setObjects(NULL); // indicates no change
							}
							$updated = zp_apply_filter('save_admin_custom_data', $updated, $userobj, $i, $alter);
							if (isset($_POST['delinkAlbum_' . $i])) {
								$userobj->setAlbum(NULL);
								markUpdated();
							}
							if (isset($_POST['createAlbum_' . $i])) {
								$userobj->createPrimealbum();
								markUpdated();
							}
							if ($updated) {
								$returntab .= '&show-' . $user;
								$msg = zp_apply_filter('save_user', $msg, $userobj, $what);
								if (empty($msg)) {
									if (!$notify)
										$userobj->transient = false;
									$userobj->save();
								} else {
									$notify = '?mismatch=format&error=' . urlencode($msg);
									$error = true;
								}
							}
						}
					}
					if ($nouser) {
						$notify = '?mismatch=nothing';
					}
				} else {
					$notify = '?post_error';
				}
			}
			break;
	}
	$returntab .= "&page=users";
	if (!empty($newuser)) {
		$returntab .= '&show-' . $newuser;
		unset($_POST['show-']);
	}
	if (empty($notify)) {
		$notify = '?saved';
	}
	header("Location: " . $notify . $returntab . $ticket);
	exitZP();
}
$refresh = false;

if ($_zp_current_admin_obj->reset) {
	if (isset($_GET['saved'])) {
		$refresh = '<meta http-equiv="refresh" content="3; url=admin.php" />';
	}
}

if (!$_zp_current_admin_obj && $_zp_current_admin_obj->getID()) {
	header("HTTP/1.0 302 Found");
	header("Status: 302 Found");
	header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
	exitZP();
}

printAdminHeader($_current_tab);
echo $refresh;
?>
<script type="text/javascript" src="js/farbtastic.js"></script>
<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/sprintf.js"></script>
<link rel="stylesheet" href="js/farbtastic.css" type="text/css" />
<script type='text/javascript'>
	var visible = false;
	function getVisible(id, category, show, hide) {
		prefix = '#' + category + '-' + id + ' ';
		v = $(prefix + '.' + category + 'extrainfo').is(':hidden');
		if (v) {
			$('#toggle_' + id).prop('title', hide);
		} else {
			$('#toggle_' + id).prop('title', show);
		}
		return v;
	}
</script>
<?php Zenphoto_Authority::printPasswordFormJS(); ?>
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php
			if ($_zp_current_admin_obj->reset && !$refresh) {
				echo "<div class=\"errorbox space\">";
				echo "<h2>" . gettext("Password reset request.<br />You may now set admin usernames and passwords.") . "</h2>";
				echo "</div>";
			}

			/* Page code */
			?>
			<div id="container">
				<?php
				if (isset($_GET['post_error'])) {
					echo '<div class="errorbox">';
					echo "<h2>" . gettext('Error') . "</h2>";
					echo gettext('The form submission is incomplete. Perhaps the form size exceeds configured server or browser limits.');
					echo '</div>';
				}
				if (isset($_GET['saved'])) {
					echo '<div class="messagebox fade-message">';
					echo "<h2>" . gettext("Saved") . "</h2>";
					echo '</div>';
				}
				if (isset($_GET['showgroup'])) {
					$showgroup = sanitize($_GET['showgroup'], 3);
				} else {
					$showgroup = '';
				}
				?>
				<?php
				printSubtabs();
				global $_zp_authority;
				?>
				<div id="tab_admin" class="tabbox">
					<?php
					zp_apply_filter('admin_note', 'users', 'users');

					$pages = 0;
					$clearPass = false;
					if (!$_zp_current_admin_obj->getID() && $_zp_current_admin_obj->reset) {
						$clearPass = true;
					}
					$alladmins = array();
					if (zp_loggedin(ADMIN_RIGHTS) && !$_zp_current_admin_obj->reset || !$_zp_current_admin_obj->getID()) {
						$admins = $_zp_authority->getAdministrators('allusers');
						foreach ($admins as $key => $user) {
							$alladmins[] = $user['user'];
							if ($user['valid'] > 1) {
								unset($admins[$key]);
							}
						}
						if (empty($admins) || !$_zp_current_admin_obj->getID()) {
							$rights = ALL_RIGHTS;
							$groupname = 'administrators';
							$showset = array('');
							$rangeset = array();
						} else {
							if (!empty($showgroup)) {
								foreach ($admins as $key => $user) {
									switch ($showgroup) {
										case '*':
											if ($user['rights'] != 0) {
												unset($admins[$key]);
											}
											break;
										case '$':
											if (!empty($user['group'])) {
												unset($admins[$key]);
											}
											break;
										default:
											$hisgroups = explode(',', $user['group']);
											if (!in_array($showgroup, $hisgroups)) {
												unset($admins[$key]);
											}
											break;
									}
								}
							}
							$admins = sortMultiArray($admins, 'user');
							$rights = DEFAULT_RIGHTS;
							$groupname = 'default';
							$list = array();
							foreach ($admins as $admin) {
								$list[] = $admin['user'];
							}
							$rangeset = getPageSelector($list, USERS_PER_PAGE);
						}
						$newuser = array('id'					 => -1, 'user'				 => '', 'pass'				 => '', 'name'				 => '', 'email'				 => '', 'rights'			 => $rights, 'custom_data'	 => NULL, 'valid'				 => 1, 'group'				 => $groupname);
						$alterrights = '';
					} else {
						$alterrights = ' disabled="disabled"';
						$rangeset = array();
						if ($_zp_current_admin_obj) {
							$admins = array($_zp_current_admin_obj->getUser() =>
											array('id'					 => $_zp_current_admin_obj->getID(),
															'user'				 => $_zp_current_admin_obj->getUser(),
															'pass'				 => $_zp_current_admin_obj->getPass(),
															'name'				 => $_zp_current_admin_obj->getName(),
															'email'				 => $_zp_current_admin_obj->getEmail(),
															'rights'			 => $_zp_current_admin_obj->getRights(),
															'custom_data'	 => $_zp_current_admin_obj->getCustomData(),
															'valid'				 => 1,
															'group'				 => $_zp_current_admin_obj->getGroup()));
							$showset = array($_zp_current_admin_obj->getUser());
						} else {
							$admins = $showset = array();
						}
					}
					$max = floor((count($admins) - 1) / USERS_PER_PAGE);
					if ($subpage > $max) {
						$subpage = $max;
					}
					$userlist = array_slice($admins, $subpage * USERS_PER_PAGE, USERS_PER_PAGE);

					if (isset($_GET['deleted'])) {
						echo '<div class="messagebox fade-message">';
						echo "<h2>Deleted</h2>";
						echo '</div>';
					}
					if (isset($_GET['tag_parse_error'])) {
						echo '<div class="errorbox fade-message">';
						echo "<h2>" . gettext("Your Allowed tags change did not parse successfully.") . "</h2>";
						echo '</div>';
					}
					if (isset($_GET['migration_error'])) {
						echo '<div class="errorbox fade-message">';
						echo "<h2>" . gettext("Rights migration failed.") . "</h2>";
						echo '</div>';
					}
					if (isset($_GET['exists'])) {
						echo '<div class="errorbox fade-message">';
						echo "<h2>" . gettext("User id already used.") . "</h2>";
						echo '</div>';
					}
					if (isset($_GET['mismatch'])) {
						echo '<div class="errorbox fade-message">';
						switch ($_GET['mismatch']) {
							case 'mismatch':
								echo "<h2>" . gettext('You must supply a password') . "</h2>";
								break;
							case 'nothing':
								echo "<h2>" . gettext('User name not provided') . "</h2>";
								break;
							case 'format':
								echo '<h2>' . html_encode(urldecode(sanitize($_GET['error'], 2))) . '</h2>';
								break;
							default:
								echo "<h2>" . gettext('Your passwords did not match') . "</h2>";
								break;
						}
						echo '</div>';
					}
					if (isset($_GET['badurl'])) {
						echo '<div class="errorbox fade-message">';
						echo "<h2>" . gettext("Your Website URL is not valid") . "</h2>";
						echo '</div>';
					}
					?>
					<script type="text/javascript">
						function languageChange(id, lang) {
							var oldid = '#' + $('#admin_language_' + id).val() + '_' + id;
							var newid = '#' + lang + '_' + id;
							$(oldid).attr('class', '');
							if (oldid == newid) {
								$('#admin_language_' + id).val('');
							} else {
								$(newid).attr('class', 'currentLanguage');
								$('#admin_language_' + id).val(lang);
							}
						}
					</script>
					<form action="?action=saveoptions<?php echo str_replace('&', '&amp;', $ticket); ?>" method="post" autocomplete="off" onsubmit="return checkNewuser();" >
						<?php XSRFToken('saveadmin'); ?>
						<input type="hidden" name="saveadminoptions" value="yes" />
						<input type="hidden" name="subpage" value="<?php echo $subpage; ?>" />
						<?php
						if (empty($alterrights)) {
							?>
							<input type="hidden" name="alter_enabled" value="1" />
							<?php
						}
						?>
						<p class="buttons">
							<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
							<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
						</p>
						<br class="clearall" /><br />
						<table class="bordered"> <!-- main table -->

							<tr>
								<?php
								if ($subpage || count($userlist) > 1) {
									?>
									<th>
										<span style="font-weight: normal">
											<a href="javascript:setShow(1);toggleExtraInfo('','user',true);"><?php echo gettext('Expand all'); ?></a>
											|
											<a href="javascript:setShow(0);toggleExtraInfo('','user',false);"><?php echo gettext('Collapse all'); ?></a>
										</span>
									</th>
									<th>
										<?php echo gettext('show'); ?>
										<select name="showgroup" id="showgroup" onchange="launchScript('<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-users.php', ['showgroup=' + $('#showgroup').val()]);" >
											<option value=""<?php if (!$showgroup) echo ' selected="selected"'; ?>><?php echo gettext('all'); ?></option>
											<option value="*"<?php if ($showgroup == '*') echo ' selected="selected"'; ?>><?php echo gettext('pending verification'); ?></option>
											<?php
											if (extensionEnabled('user_groups')) {
												?>
												<option value="$"<?php if ($showgroup == '$') echo ' selected="selected"'; ?>><?php echo gettext('no group'); ?></option>
												<?php
												$groups = $_zp_authority->getAdministrators('groups');
												foreach ($groups as $group) {
													if ($group['name'] != 'template') {
														?>
														<option value="<?php echo $group['user']; ?>"<?php if ($showgroup == $group['user']) echo ' selected="selected"'; ?>><?php printf('%s group', $group['user']); ?></option>
														<?php
													}
												}
											}
											?>
										</select>
									</th>
									<th>
										<?php printPageSelector($subpage, $rangeset, 'admin-users.php', array('page' => 'users')); ?>
									</th>
									<?php
								} else {
									?>
									<th colspan=3>&nbsp;</th>
									<?php
								}
								?>
							</tr>
							<?php
							$id = 0;
							$albumlist = array();
							foreach ($_zp_gallery->getAlbums() as $folder) {
								$alb = newAlbum($folder);
								$name = $alb->getTitle();
								$albumlist[$name] = $folder;
							}
							$background = '';
							$showlist = array();
							if (!empty($newuser)) {
								$userlist[-1] = $newuser;
							}
							foreach ($userlist as $key => $user) {
								$ismaster = false;
								$local_alterrights = $alterrights;
								$userid = $user['user'];
								$current = in_array($userid, $showset);
								$showlist[] = '#show-' . $userid;
								if ($userid == $_zp_current_admin_obj->getuser()) {
									$userobj = $_zp_current_admin_obj;
								} else {
									$userobj = Zenphoto_Authority::newAdministrator($userid);
								}
								if (empty($userid)) {
									$userobj->setGroup($user['group']);
									$userobj->setRights($user['rights']);
									$userobj->setValid(1);
								}
								$groupname = $userobj->getGroup();
								if ($pending = $userobj->getRights() == 0) {
									$master = '(<em>' . gettext('pending verification') . '</em>)';
								} else {
									$master = '&nbsp;';
								}
								if ($userobj->master && $_zp_current_admin_obj->getID()) {
									if (zp_loggedin(ADMIN_RIGHTS)) {
										$master = "(<em>" . gettext("Master") . "</em>)";
										$userobj->setRights($userobj->getRights() | ADMIN_RIGHTS);
										$ismaster = true;
									}
								}
								if ($background) {
									$background = "";
								} else {
									$background = "background-color:#ECF1F2;";
								}
								if ($_zp_current_admin_obj->reset) {
									$custom_row = NULL;
								} else {
									?>
									<!-- apply alterrights filter -->
									<?php
									$local_alterrights = zp_apply_filter('admin_alterrights', $local_alterrights, $userobj);
									?>
									<!-- apply admin_custom_data filter -->
									<?php
									$custom_row = zp_apply_filter('edit_admin_custom_data', '', $userobj, $id, $background, $current, $local_alterrights);
								}
								?>
								<!-- finished with filters -->
								<tr>
									<td colspan="3" style="margin: 0pt; padding: 0pt;border-top: 4px solid #D1DBDF;<?php echo $background; ?>">
										<table class="bordered" style="border: 0" id='user-<?php echo $id; ?>'>
											<tr>
												<td style="margin-top: 0px; width:20em;<?php echo $background; ?>" valign="top">
													<input type="hidden" name="show-<?php echo $userid; ?>" id="show_<?php echo $id; ?>" value="<?php echo ($current); ?>" />
													<?php
													if (empty($userid)) {
														$displaytitle = gettext("Show details");
														$hidetitle = gettext("Hide details");
													} else {
														$displaytitle = sprintf(gettext('Show details for user %s'), $userid);
														$hidetitle = sprintf(gettext('Hide details for user %s'), $userid);
													}
													?>
													<a id="toggle_<?php echo $id; ?>" href="javascript:visible=getVisible('<?php echo $id; ?>','user', '<?php echo $displaytitle; ?>', '<?php echo $hidetitle; ?>');
														 $('#show_<?php echo $id; ?>').val(visible);toggleExtraInfo('<?php echo $id; ?>','user',visible);" title="<?php echo $displaytitle; ?>" >
															 <?php
															 if (empty($userid)) {
																 ?>
															<input type="hidden" name="<?php echo $id ?>-newuser" value="1" />

															<em><?php echo gettext("New User"); ?></em>
															<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>" id="adminuser<?php echo $id; ?>" name="adminuser<?php echo $id; ?>" value=""
																		 onclick="toggleExtraInfo('<?php echo $id; ?>', 'user', visible);
									$('#adminuser<?php echo $id; ?>').focus();" />

															<?php
														} else {
															?>
															<input type="hidden" id="adminuser<?php echo $id; ?>" name="adminuser<?php echo $id ?>" value="<?php echo $userid ?>" />
															<?php
															echo '<strong>' . $userid . '</strong> ';
															if (!empty($userid)) {
																echo $master;
															}
														}
														?>
													</a>

													<?php
													if (!$alterrights || !$userobj->getID()) {
														if ($pending) {
															?>
															<input type="checkbox" name="<?php echo $id ?>-confirmed" value="<?php
															echo NO_RIGHTS;
															echo $alterrights;
															?>" />
																		 <?php echo gettext("Authenticate user"); ?>
																		 <?php
																	 } else {
																		 ?>
															<input type = "hidden" name="<?php echo $id ?>-confirmed"	value="<?php echo NO_RIGHTS; ?>" />
															<?php
														}
														?>
													</td>
													<td style="margin-top: 0px;<?php echo $background; ?>" valign="top">
														<?php
														if (!empty($userid) && count($admins) > 1) {
															$msg = gettext('Are you sure you want to delete this user?');
															if ($ismaster) {
																$msg .= ' ' . gettext('This is the master user account. If you delete it another user will be promoted to master user.');
															}
															?>

															<span class="floatright">
																<a href="javascript:if(confirm(<?php echo "'" . js_encode($msg) . "'"; ?>)) { window.location='?action=deleteadmin&adminuser=<?php echo addslashes($user['user']); ?>&amp;subpage=<?php echo $subpage; ?>&amp;XSRFToken=<?php echo getXSRFToken('deleteadmin') ?>'; }"
																	 title="<?php echo gettext('Delete this user.'); ?>" style="color: #c33;">
																	<img src="images/fail.png" style="border: 0px;" alt="Delete" /></a>
															</span>
															<?php
														}
														?>
														&nbsp;
													</td>
													<?php
												} else {
													?>
													<td style="margin-top: 0px;<?php echo $background; ?>" valign="top"></td>
													<?php
												}
												?>
											</tr>
											<?php
											$no_change = array();
											if (!zp_loggedin(ADMIN_RIGHTS) && !$_zp_current_admin_obj->reset) {
												$no_change = $userobj->getCredentials();
												?>
												<tr <?php if (!$current) echo 'style="display:none;"'; ?> class="userextrainfo">
													<td <?php if (!empty($background)) echo " style=\"$background\""; ?> colspan="2">
														<p class="notebox">
															<?php echo gettext('<strong>Note:</strong> You must have ADMIN rights to alter anything but your personal information.'); ?>
														</p>
													</td>
												</tr>
												<?php
											}
											?>
											<tr <?php if (!$current) echo 'style="display:none;"'; ?> class="userextrainfo">
												<td <?php if (!empty($background)) echo " style=\"$background\""; ?> valign="top">
													<p>
														<?php
														$pad = false;
														if (!empty($userid) && !$clearPass) {
															$x = $userobj->getPass();
															if (!empty($x)) {
																$pad = true;
															}
														}
														if (in_array('password', $no_change)) {
															$password_disable = ' disabled="disabled"';
														} else {
															$password_disable = '';
														}
														Zenphoto_Authority::printPasswordForm($id, $pad, $password_disable, $clearPass);
														?>
													</p>
													<?php
													if (in_array('challenge_phrase', $no_change)) {
														$_disable = ' disabled="disabled"';
													} else {
														$_disable = '';
													}
													$challenge = $userobj->getChallengePhraseInfo();
													?>
													<p>
														<?php echo gettext('Challenge phrase') ?>
														<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>" id="challengephrase-<?php echo $id ?>" name="<?php echo $id ?>-challengephrase"
																	 value="<?php echo html_encode($challenge['challenge']); ?>"<?php echo $_disable; ?> />
														<br />
														<?php echo gettext('Challenge response') ?>
														<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>" id="challengeresponse-<?php echo $id ?>" name="<?php echo $id ?>-challengeresponse"
																	 value="<?php echo html_encode($challenge['response']); ?>"<?php echo $_disable; ?> />

													</p>
													<?php echo gettext("Full name"); ?>
													<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>" id="admin_name-<?php echo $id ?>" name="<?php echo $id ?>-admin_name"
																 value="<?php echo html_encode($userobj->getName()); ?>"<?php if (in_array('name', $no_change)) echo ' disabled="disabled"'; ?> />
													<p>
														<?php echo gettext("Email"); ?>
														<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>" id="admin_email-<?php echo $id ?>" name="<?php echo $id ?>-admin_email"
																	 value="<?php echo html_encode($userobj->getEmail()); ?>"<?php if (in_array('email', $no_change)) echo ' disabled="disabled"'; ?> />
													</p>
													<?php
													$primeAlbum = $userobj->getAlbum();
													if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
														if (empty($primeAlbum)) {
															if (!($userobj->getRights() & (ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS))) {
																?>
																<p>
																	<label>
																		<input type="checkbox" name="createAlbum_<?php echo $id ?>" id="createAlbum_<?php echo $id ?>" value="1" <?php echo $alterrights; ?>/>
																		<?php echo gettext('create primary album'); ?>
																	</label>
																</p>
																<?php
															}
														} else {
															?>
															<p>
																<label>
																	<input type="checkbox" name="delinkAlbum_<?php echo $id ?>" id="delinkAlbum_<?php echo $id ?>" value="1" <?php echo $alterrights; ?>/>
																	<?php printf(gettext('delink primary album <strong>%1$s</strong>(<em>%2$s</em>)'), $primeAlbum->getTitle(), $primeAlbum->name); ?>
																</label>
															</p>
															<p class="notebox">
																<?php echo gettext('The primary album was created in association with the user. It will be removed if the user is deleted. Delinking the album removes this association.'); ?>
															</p>
															<?php
														}
													}
													$currentValue = $userobj->getLanguage();
													?>
													<p>
														<label for="admin_language_<?php echo $id ?>"><?php echo gettext('Language:'); ?></label></p>
													<input type="hidden" name="<?php echo $id ?>-admin_language" id="admin_language_<?php echo $id ?>" value="<?php echo $currentValue; ?>" />
													<ul class="flags" style="margin-left: 0px;">
														<?php
														$_languages = generateLanguageList();
														$c = 0;
														foreach ($_languages as $text => $lang) {
															?>
															<li id="<?php echo $lang . '_' . $id; ?>"<?php if ($lang == $currentValue) echo ' class="currentLanguage"'; ?>>
																<a onclick="javascript:languageChange('<?php echo $id; ?>', '<?php echo $lang; ?>');" >
																	<img src="<?php echo getLanguageFlag($lang); ?>" alt="<?php echo $text; ?>" title="<?php echo $text; ?>" />
																</a>
															</li>
															<?php
															$c++;
															if (($c % 7) == 0)
																echo '<br class="clearall" />';
														}
														?>
													</ul>
												</td>

												<td <?php if (!empty($background)) echo " style=\"$background\""; ?>>
													<?php printAdminRightsTable($id, $background, $local_alterrights, $userobj->getRights()); ?>

													<?php
													if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
														$album_alter_rights = $local_alterrights;
													} else {
														$album_alter_rights = ' disabled="disabled"';
													}
													if ($ismaster) {
														echo '<p>' . gettext("The <em>master</em> account has full rights to all albums.") . '</p>';
													} else {
														if (is_object($primeAlbum)) {
															$flag = array($primeAlbum->name);
														} else {
															$flag = array();
														}
														printManagedObjects('albums', $albumlist, $album_alter_rights, $userobj, $id, gettext('user'), $flag);
														if (extensionEnabled('zenpage')) {
															$pagelist = array();
															$pages = $_zp_zenpage->getPages(false);
															foreach ($pages as $page) {
																if (!$page['parentid']) {
																	$pagelist[get_language_string($page['title'])] = $page['titlelink'];
																}
															}
															printManagedObjects('pages', $pagelist, $album_alter_rights, $userobj, $id, gettext('user'), NULL);
															$newslist = array();
															$categories = $_zp_zenpage->getAllCategories(false);
															foreach ($categories as $category) {
																$newslist[get_language_string($category['title'])] = $category['titlelink'];
															}
															printManagedObjects('news', $newslist, $album_alter_rights, $userobj, $id, gettext('user'), NULL);
														}
													}
													?>

												</td>
											</tr>
											<?php echo $custom_row; ?>



										</table> <!-- end individual admin table -->
									</td>
								</tr>
								<?php
								$id++;
							}
							?>
							<tr>
								<th></th>
								<th></th>
								<th>
									<?php printPageSelector($subpage, $rangeset, 'admin-users.php', array('page' => 'users')); ?>
								</th>
							</tr>
						</table> <!-- main admin table end -->

						<input type="hidden" name="totaladmins" value="<?php echo $id; ?>" />
						<input type="hidden" name="checkForPostTruncation" value="1" />
						<br />
						<?php
						if (!$_zp_current_admin_obj->transient) {
							?>
							<p class="buttons">
								<button type="submit"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
								<button type="reset"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
							</p>
							<?php
						}
						?>
					</form>
					<?php
					if (zp_loggedin(ADMIN_RIGHTS)) {
						if (Zenphoto_Authority::getVersion() < Zenphoto_Authority::$supports_version) {
							?>
							<br class="clearall" />
							<p class="notebox">
								<?php printf(gettext('The <em>Zenphoto_Authority</em> object supports a higher version of user rights than currently selected. You may wish to migrate the user rights to gain the new functionality this version provides.'), Zenphoto_Authority::getVersion(), Zenphoto_Authority::$supports_version); ?>
								<br class="clearall" />
								<span class="buttons">
									<a onclick="launchScript('', ['action=migrate_rights', 'XSRFToken=<?php echo getXSRFToken('migrate_rights') ?>']);"><?php echo gettext('Migrate rights'); ?></a>
								</span>
								<br class="clearall" />
							</p>
							<br class="clearall" />
							<?php
						} else if (Zenphoto_Authority::getVersion() > Zenphoto_Authority::$preferred_version) {
							?>
							<br class="clearall" />
							<p class="notebox">
								<?php printf(gettext('You may wish to revert the <em>Zenphoto_Authority</em> user rights to version %s for backwards compatibility with prior Zenphoto releases.'), Zenphoto_Authority::getVersion() - 1); ?>
								<br class="clearall" />
								<span class="buttons">
									<a onclick="launchScript('', ['action=migrate_rights', 'revert=true', 'XSRFToken=<?php echo getXSRFToken('migrate_rights') ?>']);"><?php echo gettext('Revert rights'); ?></a>
								</span>
								<br class="clearall" />
							</p>
							<br class="clearall" />
							<?php
						}
					}
					?>
					<script type="text/javascript">
						//<!-- <![CDATA[
						var admins = ["<?php echo implode('","', $alladmins); ?>"];
						function checkNewuser() {
							newuserid = <?php echo ($id - 1); ?>;
							newuser = $('#adminuser' + newuserid).val().replace(/^\s+|\s+$/g, "");
							if (newuser == '')
								return true;
							if (newuser.indexOf('?') >= 0 || newuser.indexOf('&') >= 0 || newuser.indexOf('"') >= 0 || newuser.indexOf('\'') >= 0) {
								alert('<?php echo js_encode(gettext('User names may not contain "?", "&", or quotation marks.')); ?>');
								return false;
							}
							for (i = 0; i < admins.length; i++) {
								if (admins[i] == newuser) {
									alert(sprintf('<?php echo js_encode(gettext('The user "%s" already exists.')); ?>', newuser));
									return false;
								}
							}
							return true;
						}
						function setShow(v) {
<?php
foreach ($showlist as $show) {
	?>
								$('<?php echo $show; ?>').val(v);
	<?php
}
?>
						}
						// ]]> -->
					</script>

					<br class="clearall" />
					<br />
				</div><!-- end of tab_admin div -->

			</div><!-- end of container -->
		</div><!-- end of content -->
	</div><!-- end of main -->
	<?php
	printAdminFooter();
	?>
</body>
</html>



