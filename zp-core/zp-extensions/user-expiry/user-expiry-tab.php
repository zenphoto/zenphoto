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
	if ($admin['valid']) {
		$ordered[$key] = $admin['date'];
	}
}
asort($ordered);
$adminordered = array();
foreach ($ordered as $key=>$user) {
	$adminordered[] = $admins[$key];
}

if (isset($_GET['action'])) {
	$action = $_GET['action'];
	XSRFdefender($action);
	$themeswitch = false;

echo "<br/>action=$action";

	if ($action == 'expiry') {
		foreach ($_POST as $key=>$action) {
			if (strpos($key,'r_') == 0) {
				$user = str_replace('r_','', $key);
				if ($userobj = $_zp_authority->getAnAdmin(array('`user`=' => $user, '`valid`>' => 0))) {
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
					}
				}
			}
		}
		header("Location: ".FULLWEBPATH."/".ZENFOLDER.'/'.PLUGIN_FOLDER.'/user-expiry/user-expiry-tab.php?page=users&tab=groups&applied');
		exit();
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
				echo '<div class="messagebox" id="fade-message">';
				echo  "<h2>".gettext('Processed')."</h2>";
				echo '</div>';
			}
			$subtab = printSubtabs();
			?>
			<div id="tab_users" class="tabbox">
				<?php
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
								<button type="submit" title="<?php echo gettext("Apply"); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
								<button type="reset" title="<?php echo gettext("Reset"); ?>"><img src="../../images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
								<div class="floatright">
									<a title="<?php echo gettext('options')?>" href="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/admin-options.php?'page=options&amp;tab=plugin&amp;show-user-expiry#user-expiry">
										<strong><?php echo gettext('Options')?></strong>
									</a>
								</div>
							</span>
							<br clear="all" />
							<br /><br />
							<ul class="fullchecklist">
								<?php
								foreach ($adminordered as $user) {
									if (!($user['rights'] & ADMIN_RIGHTS)) {
										$checked_delete = $checked_disable = $checked_renew = '';
										$expires = strtotime($user['date'])+$subscription;
										$expires_display = date('Y-m-d',$expires);
										$loggedin = $user['loggedin'];
										if (empty($loggedin)) {
											$loggedin = gettext('never');
										} else {
											$loggedin = date('Y-m-d',strtotime($loggedin));
										}
										if ($expires < $now) {
											$checked_delete = ' checked="chedked"';
											$expires_display = '<span style="color:red" class="tooltip" title="'.gettext('Expired').'">'.$expires_display.'</span>';
										} else {
											if ($expires < $warnInterval) {
												$expires_display = '<span style="color:orange" class="tooltip" title="'.gettext('Expires soon').'">'.$expires_display.'</span>';
											}
										}
										if ($user['valid'] == 2) {
											$checked_delete = '';
										}
										$id = $user['user'];
										$r1 = '<img src="../../images/fail.png" title="'.gettext('delete').'" /><input type="radio" name="r_'.$id.'" value="delete"'.$checked_delete.' />&nbsp;';
										if ($user['valid'] == 2) {
											$r2 = '<img src="../../images/lock_open.png" title="'.gettext('enable').'" /><input type="radio" name="r_'.$id.'" value="enable"'.$checked_disable.' />&nbsp;';
										} else {
											$r2 = '<img src="../../images/lock_2.png" title="'.gettext('disable').'" /><input type="radio" name="r_'.$id.'" value="disable"'.$checked_disable.' />&nbsp;';
										}
										$r3 = '<img src="../../images/pass.png" title="'.gettext('renew').'" /><input type="radio" name="r_'.$id.'" value="renew"'.$checked_renew.' />&nbsp;';
										?>
										<li>
											<?php printf(gettext('%1$s <strong>%2$s</strong> (expires:%3$s; last logon:%4$s)'),$r1.$r2.$r3,$id,$expires_display,$loggedin); ?>
										</li>
										<?php
									}
								}
								?>
							</ul>
							<img src="../../images/fail.png" /> <?php echo gettext('Remove'); ?>
							<img src="../../images/lock_2.png" /> <?php echo gettext('Disable'); ?>
							<img src="../../images/lock_open.png" /> <?php echo gettext('Enable'); ?>
							<img src="../../images/pass.png" /> <?php echo gettext('Renew'); ?>
							<p class="buttons">
							<button type="submit" title="<?php echo gettext("Apply"); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
							<button type="reset" title="<?php echo gettext("Reset"); ?>"><img src="../../images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
							</p>
							<br clear="all" /><br /><br />
						</form>
						<br clear="all" /><br />
			</div>

		</div>
	</div>
</body>
</html>