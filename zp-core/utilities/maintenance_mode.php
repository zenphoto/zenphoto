<?php
/**
 * Maintenance mode utility based on the former site_upgrade plugin 
 * 
 * @author Stephen Billard (sbillard), adapted by Malte Müller (acrylian)
 * @package zpcore\admin\utilities
 */

define ('OFFSET_PATH', 3);
require_once(dirname(dirname(__FILE__)).'/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/functions/functions-config.php');

$buttonlist[] = $button = maintenanceMode::getButtonDefinition();

admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL());

if (isset($_POST['maintenance_mode'])) {
	$message = '';
	XSRFdefender('maintenance_mode');
	switch ($_POST['maintenance_mode']) {
		default:
		case 'open':
			$message = gettext('Site is open.');
			maintenanceMode::setState('open', $_zp_mutex);
			break;
		case 'closed':
			$message = gettext('Site is now closed for upgrade.');
			maintenanceMode::setState('closed', $_zp_mutex);
			break;
		case 'closed_for_test':
			$message = gettext('Site is now closed for testing only.');
			maintenanceMode::setState('closed_for_test', $_zp_mutex);
			break;
	}
	if (isset($_POST['maintenance_mode_auto-open'])) {
		setOption('maintenance_mode_auto-open', 1);
	} else {
		setOption('maintenance_mode_auto-open', 0);
	}
	if(isset($_POST['maintenance_mode_restorefiles'])) {
		maintenanceMode::restorePlaceholderFiles();
		$message .= ' ' . gettext('site_upgrade files restored to original.');
	}
	redirectURL(FULLWEBPATH . '/' . ZENFOLDER . '/utilities/maintenance_mode.php?report=' . $message);
}
$auto_open = getOption('maintenance_mode_auto-open');
$sitestate = maintenanceMode::getState();
$_zp_admin_menu['overview']['subtabs'] = array(gettext('Maintenance mode') => FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/maintenance_mode.php');
printAdminHeader('overview');
?>
</head>
<body>
<?php printLogoAndLinks(); ?>
<div id="main">
<?php printTabs(); ?>
<div id="content">
	<?php zp_apply_filter('admin_note','maintenance_mode', ''); 
	if (isset($_GET['report'])) {
		$message = sanitize($_GET['report']);
		if(!empty($message)) {
			echo '<p class="notebox fade-message"">' . html_encode($message) . '</p>';
		}
	}
	?>
	<?php printSubtabs(); ?>
	<div class="tabbox">
		<h1><?php echo $button['button_text']; ?></h1>
	<?php
	if (!MOD_REWRITE) { 
		echo '<p class="warningbox">';
		echo gettext('<em>mod_rewrite</em> is not enabled. The plugin works best if <var>mod_rewrite</var> is active and the <var>htaccess</var> file exists. If this is not the case the plugin will still work in most cases. However if the release you are upgrading to has significant changes involving plugin loading of the front-end site there may be PHP failures due if the site is accessed while the files being uploaded are in a mixed release state.');
		echo '</p>';
	}
	?>
	<p><?php echo $button['title']; ?></p>
	<form id="maintenance_mode-form" class="dirty-check" name="maintenance_mode-form" method="post">
		<?php XSRFToken('maintenance_mode'); ?>
		<ul>
			<li><label><input type="radio" name="maintenance_mode" value="open"<?php checked('open', $sitestate); ?>><strong><?php echo gettext('Open the site'); ?></strong></label></li>
			<li>
				<label><input type="radio" name="maintenance_mode" value="closed"<?php checked('closed', $sitestate); ?>><strong><?php echo gettext('Close the site'); ?></strong></label>
			</li>
			<li><label><input type="radio" name="maintenance_mode" value="closed_for_test"<?php checked('closed_for_test', $sitestate); ?>><strong><?php echo gettext('Test mode'); ?></strong></label></li>
			
		</ul>
		<p><label><input type="checkbox" name="maintenance_mode_auto-open" value="1""<?php checked(1, $auto_open); ?>><?php echo gettext('Open the site automatically after running setup (not recommended)'); ?></label>
		<p><label><input type="checkbox" name="maintenance_mode_restorefiles" value="restorefiles"><?php echo gettext('Restore the files in the <code>plugins/site_upgrade</code> folder to their default state. <strong>Note: this will overwrite any custom edits you may have made.</strong>'); ?></label>
	</p>
	<p class="buttons clearfix"><button type="submit"> <img src="../images/pass.png" alt=""> <strong><?php echo gettext('Apply'); ?></strong></button></p>
	</form>
	<h2><?php echo gettext('Usage info'); ?></h2>
	<ol>
		<li><?php echo gettext('<strong>Closing</strong> the site will cause links to the site <code>front end</code> to be redirected to a script in the folder <code>plugins/site_upgrade</code>. Access to the admin pages remains available. You should close the site while you are uploading a new Zenphoto release or doing other significant updates be it code or content wise so that users will not catch the site in an unstable state. Running setup will always close the site automatically.'); ?></li>

		<li><?php echo gettext('After you have uploaded the new release and ran Setup you place the site in <strong>test mode</strong>. In this mode only logged in <i>Administrators</i> can access the <i>front end</i>. You can then, as the administrator, view the site to be sure that all your changes are as you wish them to be.'); ?></li>

		<li><?php echo gettext('Once your testing is completed you <strong>open</strong> your site to all visitors.'); ?></li>
	</ol>
 
<h2><?php echo gettext('Custom placeholder files'); ?></h2>	
<p><?php echo gettext('Change the files in <code>plugins/site_upgrade</code> to meet your needs.'); ?></p>
<p class="warningbox"><?php echo gettext('<strong>Note:</strong> these files will
 be copied to that folder during setup the first time you do an install. Setup will not overrite any existing
 versions of these files, so if a change is made to the Zenphoto versions of the files you will have to update
 your copies either by removing them before running setup or by manually applying the Zenphoto changes to your
 files.'); ?></p>
	
	</div>
</div><!-- content -->
</div><!-- main -->
<?php printAdminFooter(); ?>
</body>
</html>
