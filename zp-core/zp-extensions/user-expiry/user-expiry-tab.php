<?php
/**
 * user_groups plugin--tabs
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage users
 */
define ('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');

admin_securityChecks(NULL, currentRelativeURL());


$admins = $_zp_authority->getAdministrators('all');

$ordered = array();
foreach ($admins as $key=>$admin) {
	if ($admin['valid']) {
		$ordered[$key] = $admin['date'];
	}
}
asort($ordered);
$adminordered = array();
foreach ($ordered as $key=>$user) {
	$adminordered[] = $admins[$key];
}
$msg = NULL;
if (isset($_GET['action'])) {
	$action = sanitize($_GET['action']);
	XSRFdefender($action);
	if ($action == 'expiry') {
		foreach ($_POST as $key=>$action) {
			if (strpos($key,'r_') === 0) {
				$userobj = Zenphoto_Authority::getAnAdmin(array('`id`=' => str_replace('r_', '', postIndexDecode($key))));
				if ($userobj) {
					switch ($action) {
						case 'delete':
							$userobj->remove();
							break;
						case 'disable':
							$userobj->setValid(2);
							$userobj->save();
							break;
						case 'enable':
							$userobj->setValid(1);
							$userobj->save();
							break;
						case 'renew':
							$newdate = getOption('user_expiry_interval')*86400+strtotime($userobj->getDateTime());
							if ($newdate+getOption('user_expiry_interval')*86400 < time()) {
								$newdate = time()+getOption('user_expiry_interval')*86400;
							}
							$userobj->setDateTime(date('Y-m-d H:i:s',$newdate));
							$userobj->setValid(1);
							$userobj->save();
							break;
						case 'force':
							$userobj->set('passupdate', NULL);
							$userobj->save();
							break;
						case 'revalidate':
							$site = $_zp_gallery->getTitle();
							$user_e = $userobj->getEmail();
							$user = $userobj->getUser();
							$key = bin2hex(serialize(array('user'=>$user,'email'=>$user_e,'date'=>time())));
							$link = FULLWEBPATH.'/index.php?user_expiry_reverify='.$key;
							$message = sprintf(gettext('Your %1$s credentials need to be renewed. Visit %2$s to renew your logon credentials.'), $site, $link);
							$msg = zp_mail(sprintf(gettext('%s renewal required'),$site), $message, array($user=>$user_e));
							break;
					}
				}
			}
		}
		header("Location: ".FULLWEBPATH."/".ZENFOLDER.'/'.PLUGIN_FOLDER.'/user-expiry/user-expiry-tab.php?page=users&tab=groups&applied='.$msg);
		exitZP();
	}
}

printAdminHeader('users');
?>
<?php
echo '</head>'."\n";
?>

<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php
			if (isset($_GET['applied'])) {
				$msg = sanitize($_GET['applied']);
				if ($msg) {
					echo "<div class=\"errorbox space\">";
					echo "<h2>".$msg."</h2>";
					echo "</div>";
				} else {
					echo '<div class="messagebox fade-message">';
					echo  "<h2>".gettext('Processed')."</h2>";
					echo '</div>';
				}
			}
			$subtab = printSubtabs();
			?>
			<div id="tab_users" class="tabbox">
				<?php
				zp_apply_filter('admin_note','users', $subtab);
				$groups = array();
				$subscription = 86400*getOption('user_expiry_interval');
				$now = time();
				$warnInterval = $now + getOption('user_expiry_warn_interval')*86400;
				?>
				<p>
				<?php	echo gettext("Manage user expiry."); ?>
				</p>
				<form action="?action=expiry" method="post" autocomplete="off" >
					<?php XSRFToken('expiry'); ?>
					<span class="buttons">
						<button type="submit"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
						<button type="reset"><img src="../../images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
						<div class="floatright">
							<a href="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/admin-options.php?'page=options&amp;tab=plugin&amp;show-user-expiry#user-expiry">
								<img src="../../images/options.png" alt="" /> <strong><?php echo gettext('Options')?></strong>
							</a>
						</div>
					</span>
					<br class="clearall" />
					<br /><br />
					<ul class="fullchecklist">
						<?php
						foreach ($adminordered as $user) {
							if (!($user['rights'] & ADMIN_RIGHTS)) {
								$checked_delete = $checked_disable = $checked_renew = $dup = '';
								$expires = strtotime($user['date'])+$subscription;
								$expires_display = date('Y-m-d',$expires);
								$loggedin = $user['loggedin'];
								if (empty($loggedin)) {
									$loggedin = gettext('never');
								} else {
									$loggedin = date('Y-m-d',strtotime($loggedin));
								}
								if ($subscription) {
									if ($expires < $now) {
										if ($user['valid'] == 1) {
											$checked_delete = ' checked="chedked"';
										}
										$expires_display = sprintf(gettext('Expired:%s; '),'<span style="color:red" >'.$expires_display.'</span>');
									} else {
										if ($expires < $warnInterval) {
											$expires_display = sprintf(gettext('Expires:%s; '),'<span style="color:orange" class="tooltip" title="'.gettext('Expires soon').'">'.$expires_display.'</span>');
										} else {
											$expires_display = sprintf(gettext('Expires:%s; '),$expires_display);
										}
									}
								} else {
									$expires_display = $r3 = $r4 = '';
								}
								if ($user['valid'] == 2) {
									$hits = 0;
									foreach ($adminordered as $tuser) {
										if ($tuser['user'] == $user['user']) {
											$hits++;
										}
									}
									if ($hits > 1) {
										$checked_delete = ' checked="chedked"';
										$checked_disable = ' disabled="disabled"';
										$expires_display = ' <span style="color:red">'.gettext('User id has been preempted').'</span>';
									}
								}
								$id = postIndexEncode($user['id']);
								$r1 = '<img src="../../images/fail.png" title="'.gettext('delete').'" /><input type="radio" name="r_'.$id.'" value="delete"'.$checked_delete.' />&nbsp;';
								if ($user['valid'] == 2) {
									$r2 = '<img src="../../images/lock_open.png" title="'.gettext('enable').'" /><input type="radio" name="r_'.$id.'" value="enable"'.$checked_disable.' />&nbsp;';
								} else {
									$r2 = '<img src="../../images/lock_2.png" title="'.gettext('disable').'" /><input type="radio" name="r_'.$id.'" value="disable"'.$checked_disable.' />&nbsp;';
								}
								if ($subscription) {
									$r3 = '<img src="../../images/pass.png" title="'.gettext('renew').'" /><input type="radio" name="r_'.$id.'" value="renew"'.$checked_renew.$checked_disable.' />&nbsp;';
									if (!$user['email']) {
										$checked_disable = ' disabled="disabled"';
									}
									$r4 = '<img src="../../images/envelope.png" title="'.gettext('Email renewal').'" /><input type="radio" name="r_'.$id.'" value="revalidate"'.$checked_disable.' />&nbsp;';
								}
								if (getOption('user_expiry_password_cycle')) {
									$r5 =  '<img src="../../images/reset.png" title="'.gettext('Force password renewal').'" /><input type="radio" name="r_'.$id.'" value="force"'.$checked_delete.' />&nbsp;';
								} else {
									$r5 = '';
								}
								?>
								<li>
									<?php printf(gettext('%1$s <strong>%2$s</strong> (%3$slast logon:%4$s)'),$r1.$r2.$r3.$r4.$r5,html_encode($user['user']),$expires_display,$loggedin); ?>
								</li>
								<?php
							}
						}
						?>
					</ul>
					<img src="../../images/fail.png" /> <?php echo gettext('Remove'); ?>
					<img src="../../images/lock_2.png" /> <?php echo gettext('Disable'); ?>
					<img src="../../images/lock_open.png" /> <?php echo gettext('Enable'); ?>
					<?php
					if (getOption('user_expiry_password_cycle')) {
						?>
						<img src="../../images/reset.png" /> <?php echo gettext('Force password renewal'); ?>
						<?php
					}
					if ($subscription) {
						?>
						<img src="../../images/pass.png" /> <?php echo gettext('Renew'); ?>
						<img src="../../images/envelope.png" /> <?php echo gettext('Email renewal link'); ?>
						<?php
					}
					?>
					<p class="buttons">
					<button type="submit"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
					<button type="reset"><img src="../../images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
					</p>
					<br class="clearall" /><br /><br />
				</form>
				<br class="clearall" /><br />
			</div>

		</div>
	</div>
</body>
</html>