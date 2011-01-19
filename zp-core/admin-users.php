<?php
/**
 * provides the Options tab of admin
 * @package admin
 */

// force UTF-8 Ø

define('OFFSET_PATH', 1);
define('USERS_PER_PAGE',10);
require_once(dirname(__FILE__).'/admin-functions.php');
require_once(dirname(__FILE__).'/admin-globals.php');

admin_securityChecks(NO_RIGHTS, currentRelativeURL(__FILE__));

$gallery = new Gallery();
if (!isset($_GET['page'])) $_GET['page'] = 'users';
$_current_tab = sanitize($_GET['page'],3);

/* handle posts */
if (isset($_GET['action'])) {
	if (($action = $_GET['action']) != 'saveoptions') {
		admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL(__FILE__));
	}
	$themeswitch = false;
	switch ($action) {
		case 'migrate_rights':
			XSRFdefender('migrate_rights');
			if (isset($_GET['revert'])) {
				$v = getOption('libauth_version')-1;
			} else {
				$v = $_zp_authority->supports_version;
			}
			if ($_zp_authority->migrateAuth($v)) {
				$notify = '';
			} else {
				$notify = '&migration_error';
			}
			header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-users.php?page=users" . $notify);
			exit();
			break;
		case 'deleteadmin':
			XSRFdefender('deleteadmin');
			$adminobj = $_zp_authority->newAdministrator(sanitize($_GET['adminuser']),1);
			zp_apply_filter('save_user', '', $adminobj, 'delete');
			$adminobj->remove();
			header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-users.php?page=users&deleted");
			exit();
			break;
		case 'saveoptions':
			if (!$_zp_null_account) XSRFdefender('saveadmin');
			$notify = '';
			$returntab = '';

			if (isset($_POST['saveadminoptions'])) {
				if ($_zp_null_account || (isset($_POST['alter_enabled'])) || ($_POST['totaladmins'] > 1) ||
				(trim(sanitize($_POST['0-adminuser'],0))) != $_zp_current_admin_obj->getUser() ||
				isset($_POST['0-newuser'])) {
					admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL(__FILE__));
				}
				$alter = isset($_POST['alter_enabled']);
				$nouser = true;
				$returntab = $newuser = false;
				for ($i = 0; $i < $_POST['totaladmins']; $i++) {
					$updated = false;
					$error = false;
					$userobj = NULL;
					$pass = trim($_POST[$i.'-adminpass']);
					$user = trim(sanitize($_POST[$i.'-adminuser'],0));
					if (empty($user) && !empty($pass)) {
						$notify = '?mismatch=nothing';
					}
					if (!empty($user)) {
						$nouser = false;
						if ($pass == trim($_POST[$i.'-adminpass_2'])) {
							$admin_n = trim($_POST[$i.'-admin_name']);
							$admin_e = trim($_POST[$i.'-admin_email']);
							if (isset($_POST[$i.'-newuser'])) {
								$newuser = $user;
								$what = 'new';
								$userobj = $_zp_authority->newAdministrator('');
								$userobj->transient = false;
								$userobj->setUser($user);
							} else {
								$what = 'update';
								$userobj = $_zp_authority->newAdministrator($user);
							}

							if ($admin_n != $userobj->getName()) {
								$updated = true;
								$userobj->setName($admin_n);
							}
							if ($admin_e != $userobj->getEmail()) {
								$updated = true;
								$userobj->setEmail($admin_e);
							}
							if (empty($pass)) {
								if ($newuser) {
									$msg = gettext('Password may not be empty!');
								} else {
									$msg = '';
								}
							} else {
								$msg = $userobj->setPass($pass);
								$updated = true;
							}
							if (isset($_POST['delinkAlbum_'.$i])) {
								$userobj->setAlbum(NULL);
								$updated = true;
							}
							$lang = sanitize($_POST[$i.'-admin_language'],3);
							if ($lang != $userobj->getLanguage()) {
								$userobj->setLanguage($lang);
								$updated = true;
							}
							$oldrights = $userobj->getRights();
							$oldobjects = $userobj->getObjects();
							$rights = 0;
							if ($alter) {
								$objects = processManagedObjects($i, $rights);
								if ($objects != $oldobjects) {
									$userobj->setObjects($objects);
								}
								$rights = processRights($i) | $rights;
								if ($rights != $oldrights) {
									$userobj->setRights($rights);
								}
							} else {
								$oldobjects = $userobj->setObjects(NULL);	// indicates no change
							}
							$updated = zp_apply_filter('save_admin_custom_data', $updated, $userobj, $i, $alter);
							if ($oldrights != $userobj->getRights()) {
								$updated = true;
							}
							$objects = $userobj->getObjects();
							if (!$updated && $oldobjects != $objects && $objects) {
								$objects = sortMultiArray($objects, 'data');
								$oldobjects = sortMultiArray($oldobjects, 'data');
								if ($oldobjects != $objects) {
									$updated = true;
								}
							}
							if ($updated) {
								$returntab .= '&show-'.$user;
							}
							$msg = zp_apply_filter('save_user', $msg, $userobj, $what);
							if (empty($msg)) {
								$userobj->save();
								if ($i == 0) {
									setOption('admin_reset_date', '1');
								}
							} else {
								$notify = '?mismatch=format&error='.urlencode($msg);
								$error = true;
							}
						} else {
							$notify = '?mismatch=password';
							$error = true;
						}
					}
				}
				if ($nouser) {
					$notify = '?mismatch=nothing';
				}
				$returntab .= "&page=users";
				if (!empty($newuser)) {
					$returntab .= '&show-'.$newuser;
					unset($_POST['show-']);
				}
			}

			if (empty($notify)) $notify = '?saved';
			header("Location: " . $notify . $returntab);
			exit();

	}
}


printAdminHeader($_current_tab);
?>
<script type="text/javascript" src="js/farbtastic.js"></script>
<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER;?>/js/sprintf.js"></script>
<link rel="stylesheet" href="js/farbtastic.css" type="text/css" />

</head>
<body>
<?php printLogoAndLinks(); ?>
<div id="main">
<?php printTabs(); ?>
<div id="content">
<?php
if ($_zp_null_account) {
	echo "<div class=\"errorbox space\">";
	echo "<h2>".gettext("Password reset request.<br />You may now set admin usernames and passwords.")."</h2>";
	echo "</div>";
}

/* Page code */
?>
<div id="container">
<?php
	if (isset($_GET['saved'])) {
		echo '<div class="messagebox" id="fade-message">';
		echo  "<h2>".gettext("Saved")."</h2>";
		echo '</div>';
	}
	if (isset($_GET['showgroup'])) {
		$showgroup = sanitize($_GET['showgroup'],3);
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
	$newuser = array();
	$showset = array();
	if (isset($_GET['subpage'])) {
		$subpage = sanitize_numeric($_GET['subpage']);
	} else {
		$subpage = 0;
		foreach ($_GET as $param=>$value) {
			if (strpos($param, 'show-') === 0) {
				$showset[] = substr($param,5);
			}
		}
	}

	$pages = 0;
	if ($_zp_null_account && isset($_zp_reset_admin)) {
		$_zp_current_admin_obj = $_zp_reset_admin;
		setOption('admin_reset_date', $_zp_request_date); // reset the date in case of no save
	}
	if (zp_loggedin(ADMIN_RIGHTS) && !$_zp_reset_admin) {
		$temp = $admins = $_zp_authority->getAdministrators();
		if (empty($admins) || $_zp_null_account) {
			$rights = ALL_RIGHTS;
			$groupname = 'administrators';
			$showset = array('');
		} else {
			if (!empty($showgroup)) {
				foreach ($admins as $key=>$user) {
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
							if ($user['group'] != $showgroup) {
								unset($admins[$key]);
							}
							break;
					}
				}
			}
			$admins = sortMultiArray($admins, 'user');
			$rights = DEFAULT_RIGHTS;
			$groupname = 'default';
			$pages = round(ceil(count($admins) / USERS_PER_PAGE));
			$rangeset = array();
			if ($pages > 1) {
				$page = -1;
				$ranges = array();
				$tadmins = $admins;
				$base = ' ';
				while (!empty($tadmins)) {
					$page++;
					$ranges[$page] = array(0=>0, 1=>0);
					$c = 0;
					while (($c < USERS_PER_PAGE) && !empty($tadmins)) {
						$t = array_shift($tadmins);
						$ranges[$page][$c!=0] = strtolower($t['user']);
						if (in_array($t['user'], $showset)) {
							$subpage = $page;
						}
						$c++;
					}
				}
				$base = ' ';
				foreach ($ranges as $page=>$range) {
					$start = $range[0];
					$end = $range[1];
					if (empty($end)) {
						$rangeset[$page] = minDiff($base, $start);
					} else {
						$rangeset[$page] = minDiff($base, $start).'-'.minDiff($start, $end);
					}
					$base = $end;
				}
			}
		}
		$newuser = array('id' => -1, 'user' => '', 'pass' => '', 'name' => '', 'email' => '', 'rights' => $rights, 'custom_data' => NULL, 'valid' => 1, 'group' => $groupname);
		$alterrights = '';

	} else {
		$alterrights = ' disabled="disabled"';
		$admins = array($_zp_current_admin_obj->getUser() =>
													array('id' => $_zp_current_admin_obj->getID(),
																'user' => $_zp_current_admin_obj->getUser(),
																'pass' => $_zp_current_admin_obj->getPass(),
																'name' => $_zp_current_admin_obj->getName(),
																'email' => $_zp_current_admin_obj->getEmail(),
																'rights' => $_zp_current_admin_obj->getRights(),
																'custom_data' => $_zp_current_admin_obj->getCustomData(),
																'valid' => 1,
																'group' => $_zp_current_admin_obj->getGroup()));
		$showset = array($_zp_current_admin_obj->getUser());
	}

	if (isset($_GET['deleted'])) {
		echo '<div class="messagebox" id="fade-message">';
		echo  "<h2>Deleted</h2>";
		echo '</div>';
	}
	if (isset($_GET['tag_parse_error'])) {
		echo '<div class="errorbox" id="fade-message">';
		echo  "<h2>".gettext("Your Allowed tags change did not parse successfully.")."</h2>";
		echo '</div>';
	}
	if (isset($_GET['migration_error'])) {
		echo '<div class="errorbox" id="fade-message">';
		echo  "<h2>".gettext("Rights migration failed.")."</h2>";
		echo '</div>';
	}
	if (isset($_GET['mismatch'])) {
		echo '<div class="errorbox" id="fade-message">';
		switch ($_GET['mismatch']) {
			case 'gallery':
			case 'search':
				echo  "<h2>".sprintf(gettext("Your %s passwords were empty or did not match"), $_GET['mismatch'])."</h2>";
				break;
			case 'user_gallery':
				echo  "<h2>".gettext("You must supply a password for the Gallery guest user")."</h2>";
				break;
			case 'user_search':
				echo  "<h2>".gettext("You must supply a password for the Search guest user")."</h2>";
				break;
			case 'mismatch':
				echo  "<h2>".gettext('You must supply a password')."</h2>";
				break;
			case 'nothing':
				echo  "<h2>".gettext('User name not provided')."</h2>";
				break;
			case 'format':
				echo '<h2>'.urldecode(sanitize($_GET['error'],2)).'</h2>';
				break;
			default:
				echo  "<h2>".gettext('Your passwords did not match')."</h2>";
				break;
		}
		echo '</div>';
	}
	if (isset($_GET['badurl'])) {
		echo '<div class="errorbox" id="fade-message">';
		echo  "<h2>".gettext("Your Website URL is not valid")."</h2>";
		echo '</div>';
	}

?>
<script type="text/javascript">
function languageChange(id,lang) {
	var oldid = '#'+$('#admin_language_'+id).val()+'_'+id;
	var newid = '#'+lang+'_'+id;
	$(oldid).attr('class','');
	if (oldid == newid) {
		$('#admin_language_'+id).val('');
	} else {
		$(newid).attr('class','currentLanguage');
		$('#admin_language_'+id).val(lang);
	}
}
</script>
<form action="?action=saveoptions<?php if (isset($_zp_ticket)) echo '&amp;ticket='.$_zp_ticket.'&amp;user='.$post_user; ?>" method="post" autocomplete="off" onsubmit="return checkNewuser();" >
	<?php XSRFToken('saveadmin');?>
	<input type="hidden" name="saveadminoptions" value="yes" />
	<?php
	if (empty($alterrights)) {
		?>
		<input type="hidden" name="alter_enabled" value="1" />
		<?php
	}
	?>
	<p class="buttons">
		<button type="submit" value="<?php echo gettext('Apply') ?>" title="<?php echo gettext("Apply"); ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
		<button type="reset" value="<?php echo gettext('reset') ?>" title="<?php echo gettext("Reset"); ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
	</p>
	<br clear="all" /><br />
<table class="bordered"> <!-- main table -->

	<tr>
		<th>
			<span style="font-weight: normal">
			<a href="javascript:setShow(1);toggleExtraInfo('','user',true);"><?php echo gettext('Expand all');?></a>
			|
			<a href="javascript:setShow(0);toggleExtraInfo('','user',false);"><?php echo gettext('Collapse all');?></a>
			</span>
		</th>
		<th>
			<?php echo gettext('show'); ?>
			<select name="showgroup" id="showgroup" onchange="launchScript('<?php echo WEBPATH.'/'.ZENFOLDER; ?>/admin-users.php',['showgroup='+$('#showgroup').val()]);" >
				<option value=""<?php if (!$showgroup) echo ' selected="selected"'; ?>><?php echo gettext('all'); ?></option>
				<option value="*"<?php if ($showgroup=='*') echo ' selected="selected"'; ?>><?php echo gettext('pending verification'); ?></option>
				<option value="$"<?php if ($showgroup=='$') echo ' selected="selected"'; ?>><?php echo gettext('no group'); ?></option>
				<?php
				if (getOption('zp_plugin_user_groups')) {
					$groups = $_zp_authority->getAdministrators('groups');
					foreach ($groups as $group) {
						?>
						<option value="<?php echo $group['user']; ?>"<?php if ($showgroup==$group['user']) echo ' selected="selected"'; ?>><?php printf('%s group', $group['user']); ?></option>
						<?php
					}
				}
				?>
			</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php
			if ($subpage > 0) {
				?>
				<a href="?subpage=<?php echo ($subpage-1); ?>&amp;showgroup=<?php echo $showgroup; ?>" ><?php echo gettext('prev'); ?></a>
				<?php
			}
			if ($pages > 2) {
				if ($subpage > 0) {
					?>
					|
					<?php
				}
				?>
				<select name="subpage" id="subpage" onchange="launchScript('<?php echo WEBPATH.'/'.ZENFOLDER; ?>/admin-users.php',['subpage='+$('#subpage').val(),'showgroup='+$('#showgroup').val()]);" >
					<?php
					foreach ($rangeset as $page=>$range) {
						?>
						<option value="<?php echo $page; ?>" <?php if ($page==$subpage) echo ' selected="selected"'; ?>><?php echo $range; ?></option>
						<?php
					}
					?>
				</select>
				<?php
			}
			if ($pages > $subpage+1) {
				if ($pages > 2) {
					?>
					|
					<?php
				}?>
				<a href="?subpage=<?php echo ($subpage+1); ?>&amp;showgroup=<?php echo $showgroup; ?>" ><?php echo gettext('next'); ?></a>
				<?php
			}
			?>
		</th>
	</tr>
	<?php
	$id = 0;
	$albumlist = array();
	foreach ($gallery->getAlbums() as $folder) {
		if (hasDynamicAlbumSuffix($folder)) {
			$name = substr($folder, 0, -4); // Strip the .'.alb' suffix
		} else {
			$name = $folder;
		}
		$albumlist[$name] = $folder;
	}
	$background = '';
	$showlist = array();
	$userlist = array_slice($admins,$subpage*USERS_PER_PAGE,USERS_PER_PAGE);
	if (!empty($newuser)) {
		$userlist[-1] = $newuser;
	}
	foreach($userlist as $key=>$user) {
		$local_alterrights = $alterrights;
		$userid = $user['user'];
		$current = in_array($userid,$showset);
		$showlist[] = '#show-'.$userid;
		$userobj = $_zp_authority->newAdministrator($userid);
		if (empty($userid)) {
			$userobj->setGroup($user['group']);
			$userobj->setRights($user['rights']);
			$userobj->setValid(1);
		}
		$groupname = $userobj->getGroup();
		if ($pending = $userobj->getRights() == 0) {
			$master = '(<em>'.gettext('pending verification').'</em>)';
		} else {
			$master = '&nbsp;';
		}
		if ($userobj->master && !$_zp_null_account) {
			if (zp_loggedin(ADMIN_RIGHTS)) {
				$master = "(<em>".gettext("Master")."</em>)";
				$userobj->setRights($userobj->getRights() | ADMIN_RIGHTS);
				$ismaster = true;
			}
		} else {
			$ismaster = false;
		}
		if ($background) {
			$background = "";
		} else {
			$background = "background-color:#ECF1F2;";
		}

		?>
		<!-- apply alterrights filter -->
		<?php $local_alterrights = zp_apply_filter('admin_alterrights', $local_alterrights, $userobj); ?>
		<!-- apply admin_custom_data filter -->
		<?php $custom_row = zp_apply_filter('edit_admin_custom_data', '', $userobj, $id, $background, $current, $local_alterrights); ?>
		<!-- finished with filters -->
		<tr>
			<td colspan="2" style="margin: 0pt; padding: 0pt;">
			<!-- individual admin table -->
			<input type="hidden" name="show-<?php echo $userid; ?>" id="show-<?php echo $userid; ?>" value="<?php echo ($current);?>" />
			<table class="bordered" style="border: 0" id='user-<?php echo $id;?>'>
			<tr>
				<td colspan="2" width="80%" style="border-top: 4px solid #D1DBDF;<?php echo $background; ?>" valign="top">
				<?php
				if (empty($userid)) {
					$displaytitle = gettext("Show details");
					$hidetitle = gettext("Hide details");
				} else {
					$displaytitle = sprintf(gettext('Show details for user %s'),$userid);
					$hidetitle = sprintf(gettext('Hide details for user %s'),$userid);
				}
				?>
					<span <?php if ($current) echo 'style="display:none;"'; ?> class="userextrashow">
						<a href="javascript:$('#show-<?php echo $userid; ?>').val(1);toggleExtraInfo('<?php echo $id;?>','user',true);" title="<?php echo $displaytitle; ?>" >
							<?php
							if (empty($userid)) {
								?>
								<input type="hidden" name="<?php echo $id ?>-newuser" value="1" />
								<em><?php echo gettext("Add New User"); ?></em>
								<?php
							} else {
								?>
								<input type="hidden" id="adminuser-<?php echo $id; ?>" name="<?php echo $id ?>-adminuser" value="<?php echo $userid ?>" />
								<?php
								echo '<strong>'.$userid.'</strong>';
							}
							?>
						</a>
					</span>
					<span <?php if ($current) echo 'style="display:inline;"'; else echo 'style="display:none;"'; ?> class="userextrahide">
						<a href="javascript:$('#show-<?php echo $userid; ?>').val(0);toggleExtraInfo('<?php echo $id;?>','user',false);" title="<?php echo $hidetitle; ?>">
							<?php
							if (empty($userid)) {
								echo '<em>'.gettext("Add New User").'</em>';
							} else {
								echo '<strong>'.$userid.'</strong>';
							}
							?>
						</a>
					</span>

				<?php
				if (!$alterrights) {
					?>
					<?php
					if (empty($userid)) {
							?>
							<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>" id="adminuser-<?php echo $id; ?>" name="<?php echo $id; ?>-adminuser" value=""
								onclick="toggleExtraInfo('<?php echo $id;?>','user',true);" />
							<?php
						} else {
							echo $master;
						}
						if ($pending) {
							?>
							<input type="checkbox" name="<?php echo $id ?>-confirmed" value="<?php echo NO_RIGHTS; echo $alterrights; ?>" />
							<?php echo gettext("Authenticate user"); ?>
							<?php
						} else {
							?>
							<input type = "hidden" name="<?php echo $id ?>-confirmed"	value="<?php echo NO_RIGHTS; ?>" />
							<?php
						}
						?>
						<?php
						if(!empty($userid) && count($admins) > 1) {
							$msg = gettext('Are you sure you want to delete this user?');
							if ($ismaster) {
								$msg .= ' '.gettext('This is the master user account. If you delete it another user will be promoted to master user.');
							}
							?>
							<td style="border-top: 4px solid #D1DBDF;<?php echo $background; ?>" valign="top">
							<a href="javascript:if(confirm(<?php echo "'".js_encode($msg)."'"; ?>)) { window.location='?action=deleteadmin&adminuser=<?php echo addslashes($user['user']); ?>&amp;XSRFToken=<?php echo getXSRFToken('deleteadmin')?>'; }"
								title="<?php echo gettext('Delete this user.'); ?>" style="color: #c33;"> <img
								src="images/fail.png" style="border: 0px;" alt="Delete" /></a>
							</td>
							<?php
						} else {
							?>
							<td style="border-top: 4px solid #D1DBDF;<?php echo $background; ?>" valign="top"></td>
							<?php
						}
						?>
						&nbsp;
						<?php
				} else  {
					?>
					<td style="border-top: 4px solid #D1DBDF;<?php echo $background; ?>" valign="top">
					</td>
					<?php
				}
				?>

			</tr>
			<?php
				if (!zp_loggedin(ADMIN_RIGHTS)) {
				?>
				<tr <?php if (!$current) echo 'style="display:none;"'; ?> class="userextrainfo">
					<td colspan="2" <?php if (!empty($background)) echo " style=\"$background\""; ?>>
						<p class="notebox">
							<?php echo gettext('<strong>Note:</strong> You must have ADMIN rights to alter anything but your personal information.');?>
						</p>
					</td>
					<td <?php if (!empty($background)) echo " style=\"$background\""; ?>></td>
				</tr>
				<?php
				}
			?>
		<tr <?php if (!$current) echo 'style="display:none;"'; ?> class="userextrainfo">
			<td width="35%" <?php if (!empty($background)) echo " style=\"$background\""; ?> valign="top">
				<?php
				if (!empty($userid)) {
					?>
					<p>
						<?php
						$d = strtotime($userobj->getDateTime());
						printf(gettext('User since %s'),date('Y-m-d'),$d);
						?>
					</p>
					<?php $x = $userobj->getPass(); if (!empty($x)) { $x = '          '; } ?>
					<p>
					<?php
				}
				?>
				<label for="<?php echo $id ?>-adminpass"><?php echo gettext("Password:"); ?><br />
				<input type="password" size="<?php echo TEXT_INPUT_SIZE; ?>" name="<?php echo $id ?>-adminpass"
					value="<?php echo $x; ?>" /></label></p>
					<p>
				<label for="<?php echo $id ?>-adminpass_2"><?php echo gettext("(repeat)"); ?><br />
				<input type="password" size="<?php echo TEXT_INPUT_SIZE; ?>" name="<?php echo $id ?>-adminpass_2"
					value="<?php echo $x; ?>" /></label></p>
				<?php
				$msg = $_zp_authority->passwordNote();
				if (!empty($msg)) {
					echo $msg;
				}
				?>
				<p>
					<label for="<?php echo $id ?>-admin_name"><?php echo gettext("Full name:"); ?><br />
					<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>" name="<?php echo $id ?>-admin_name"
					value="<?php echo html_encode($userobj->getName()); ?>" /></label>
				</p>
				<p>
					<label for="<?php echo $id ?>-admin_email"><?php echo gettext("Email:"); ?><br />
					<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>" name="<?php echo $id ?>-admin_email"
						value="<?php echo html_encode($userobj->getEmail()); ?>" /></label>
				</p>
				<?php
				$primeAlbum = $userobj->getAlbum();
				if (!empty($primeAlbum)) {
					?>
					<p>
					<label>
						<input type="checkbox" name="delinkAlbum_<?php echo $id ?>" id="delinkAlbum_<?php echo $id ?>" value="1" <?php echo $alterrights; ?>/>
						<?php printf(gettext('delink primary album (<em>%s</em>)'),$primeAlbum->name); ?>
					</label>
					</p>
					<p class="notebox">
						<?php echo gettext('The primary album was created in association with the user. It will be removed if the user is deleted. Delinking the album removes this association.'); ?>
					</p>
					<?php
				}
				$currentValue = $userobj->getLanguage();
				?>
				<p>
				<label for="admin_language_<?php echo $id ?>"><?php echo gettext('Language:'); ?></label></p>
				<input type=hidden name="<?php echo $id ?>-admin_language" id="admin_language_<?php echo $id ?>" value="<?php echo $currentValue; ?>" />
				<ul class="flags" style="margin-left: 0px;">
					<?php
					$_languages = generateLanguageList();
					$c = 0;
					foreach ($_languages as $text=>$lang) {
						?>
						<li id="<?php echo $lang.'_'.$id; ?>"<?php if ($lang==$currentValue) echo ' class="currentLanguage"'; ?>>
							<a onclick="javascript:languageChange('<?php echo $id; ?>','<?php echo $lang; ?>');" >
							<?php
							if (file_exists(SERVERPATH.'/'.ZENFOLDER.'/locale/'.$lang.'/flag.png')) {
								$flag = WEBPATH.'/'.ZENFOLDER.'/locale/'.$lang.'/flag.png';
							} else {
								$flag = WEBPATH.'/'.ZENFOLDER.'/locale/missing_flag.png';
							}
							?>
							<img src="<?php echo $flag; ?>" alt="<?php echo $text; ?>" title="<?php echo $text; ?>" />
							</a>
						</li>
						<?php
						$c++;
						if (($c % 7) == 0) echo '<br clear="all" />';
					}
					?>
				</ul>


			</td>

			<td width="45%" <?php if (!empty($background)) echo " style=\"$background\""; ?>>
				<?php printAdminRightsTable($id, $background, $local_alterrights, $userobj->getRights()); ?>

				<?php
				if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
					$album_alter_rights = $local_alterrights;
				} else {
					$album_alter_rights = ' disabled="disabled"';
				}
				if ($current && $ismaster) {
					echo '<p>'.gettext("The <em>master</em> account has full rights to all albums.").'</p>';
				} else {
					printManagedObjects('albums',$albumlist, $album_alter_rights, $user['id'], $id, $userobj->getRights(), gettext('user'));
					if (getOption('zp_plugin_zenpage')) {
						$pagelist = array();
						$pages = getPages(false);
						foreach ($pages as $page) {
							if (!$page['parentid']) {
								$pagelist[get_language_string($page['title'])] = $page['titlelink'];
							}
						}
						printManagedObjects('pages',$pagelist, $album_alter_rights, $user['id'], $id, $userobj->getRights(), gettext('user'));
						$newslist = array();
						$categories = getAllCategories();
						foreach ($categories as $category) {
							$newslist[get_language_string($category['title'])] = $category['titlelink'];
						}
						printManagedObjects('news',$newslist, $album_alter_rights, $user['id'], $id, $userobj->getRights(), gettext('user'));
					}
				}
				?>

			</td>
			<td <?php if (!empty($background)) echo " style=\"$background\""; ?>></td>
		</tr>
		<?php echo $custom_row; ?>



	</table> <!-- end individual admin table -->
	</td>
	</tr>
	<?php
	$current = false;
	$id++;
}
?>
</table> <!-- main admin table end -->
<input type="hidden" name="totaladmins" value="<?php echo $id; ?>" />
<br />
<p class="buttons">
<button type="submit" title="<?php echo gettext("Apply"); ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
<button type="reset" title="<?php echo gettext("Reset"); ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
</p>
</form>

<?php
if ($_zp_authority->getVersion() < $_zp_authority->supports_version) {
	?>
	<br clear="all" />
	<p>
	<?php printf(gettext('The <em>Zenphoto_Authority</em> object supports a higher version of user rights than currently selected. You wish may wish to migrate the user rights to gain the new functioality this version provides.'),$_zp_authority->getVersion(),$_zp_authority->supports_version); ?>
	</p>
	<p class="buttons">
		<a onclick="launchScript('',['action=migrate_rights','XSRFToken=<?php echo getXSRFToken('migrate_rights')?>']);"><?php echo gettext('Migrate rights');?></a>
	</p>
	<br clear="all" />
	<?php
} else if ($_zp_authority->getVersion() > $_zp_authority->preferred_version) {
	?>
	<br clear="all" />
	<p>
	<?php printf(gettext('You may wish to revert the user rights <em>Zenphoto_Authority</em> to version %s for backwards compatibility with prior Zenphoto releases.'),$_zp_authority->getVersion()-1); ?>
	</p>
	<p class="buttons">
		<a onclick="launchScript('',['action=migrate_rights','revert=true','XSRFToken=<?php echo getXSRFToken('migrate_rights')?>']);"><?php echo gettext('Revert rights');?></a>
	</p>
	<br clear="all" />
	<?php
}
?>
<script language="javascript" type="text/javascript">
	//<!-- <![CDATA[
	function checkNewuser() {
		newuserid = <?php echo ($id-1); ?>;
		newuser = $('#adminuser-'+newuserid).val().replace(/^\s+|\s+$/g,"");;
		if (newuser=='') return true;
		if (newuser.indexOf('?')>=0 || newuser.indexOf('&')>=0 || newuser.indexOf('"')>=0 || newuser.indexOf('\'')>=0) {
			alert('<?php echo js_encode(gettext('User names may not contain "?", "&", or quotation marks.')); ?>');
			return false;
		}
		for (i=newuserid-1;i>=0;i--) {
			if ($('#adminuser-'+i).val() == newuser) {
				alert(sprintf('<?php echo js_encode(gettext('The user "%s" already exists.')); ?>',newuser));
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

<br clear="all" />
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



