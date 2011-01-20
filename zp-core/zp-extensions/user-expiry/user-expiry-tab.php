<?php
/**
 * user_groups plugin--tabs
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
define ('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-functions.php');
require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');

admin_securityChecks(NULL, currentRelativeURL(__FILE__));


$admins = $_zp_authority->getAdministrators();

$ordered = array();
foreach ($admins as $key=>$admin) {
	$ordered[$key] = $admin['date'];
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
	if ($action == 'expiry') {
		foreach ($_POST as $key=>$action) {
			if (strpos($key,'r_') == 0) {
				$user = str_replace('r_','', $key);
				if ($userobj = $_zp_authority->getAnAdmin(array('`user`=' => $user, '`valid`=' => 1))) {
					switch ($action) {
						case 'delete':
							$userobj->remove();
							break;
						case 'renew':
							$newdate = getOption('user_expiry_interval')*86400+strtotime($userobj->getDateTime());
							$userobj->setDateTime(date('Y-m-d H:i:s',$newdate));
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
						$subscription = 86400;
						$now = time();
						$week_from_now = $now + 604800;
						?>
						<p>
							<?php
							echo gettext("Manage user expiry.");
							?>
						</p>
						<form action="?action=expiry" method="post" autocomplete="off" >
							<?php XSRFToken('expiry'); ?>
							<p class="buttons">
							<button type="submit" title="<?php echo gettext("Apply"); ?>"><img src="../../images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
							<button type="reset" title="<?php echo gettext("Reset"); ?>"><img src="../../images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
							</p>
							<br clear="all" /><br /><br />
							<ul class="widechecklist">
								<?php
								foreach ($adminordered as $user) {
									if (!($user['rights'] & ADMIN_RIGHTS)) {
										$expires = strtotime($user['date'])+$subscription;
										$expires_display = date('Y-m-d',$expires);
										if ($expires < $now) {
											$checked = ' checked="chedked"';
											$expires_display = '<span style="color:red">'.$expires_display.'</span>';
										} else {
											$checked = '';
											if ($expires < $week_from_now) {
												$expires_display = '<span style="color:orange">'.$expires_display.'</span>';
											}
										}
										$id = $user['user'];
										$r1 = '<input type="radio" name="r_'.$id.'" value="delete" '.$checked.'/>';
										$r2 = '<input type="radio" name="r_'.$id.'" value="renew" />';
										?>
										<li>
											<?php printf(gettext('%1$s Remove %2$s Renew <strong>%3$s</strong> (%4$s)'),$r1,$r2,$id,$expires_display); ?>
										</li>
										<?php
									}
								}
								?>
							</ul>
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