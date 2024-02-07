<?php
/**
 * Backup and restore of the ZenPhoto database table content
 *
 * This plugin provides a means to make backups of your ZenPhoto database content and
 * at a later time restore the database to the contents of one of these backups.
 *
 * @package zpcore\admin\utilities
 */
if (!defined('OFFSET_PATH'))
	define('OFFSET_PATH', 3);

require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');
require_once(dirname(dirname(__FILE__)) . '/template-functions.php');
require_once SERVERPATH . '/' . ZENFOLDER . '/classes/class-backuprestore.php';

$buttonlist[] = array(
		'category' => gettext('Admin'),
		'enable' => true,
		'button_text' => gettext('Backup/Restore'),
		'formname' => 'backup_restore',
		'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/backup_restore.php',
		'icon' => FULLWEBPATH . '/' . ZENFOLDER . '/images/folder.png',
		'title' => gettext('Backup and restore your gallery database.'),
		'alt' => '',
		'hidden' => '',
		'rights' => ADMIN_RIGHTS
);

if (!$_zp_current_admin_obj || $_zp_current_admin_obj->getID()) {
	$rights = NULL;
} else {
	$rights = USER_RIGHTS;
}
admin_securityChecks($rights, currentRelativeURL());

if (isset($_REQUEST['backup']) || isset($_REQUEST['restore'])) {
	XSRFDefender('backup');
}

if ($_zp_current_admin_obj->reset) {
	printAdminHeader('restore');
} else {
	$_zp_admin_menu['overview']['subtabs'] = array(gettext('Backup') => FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/backup_restore.php');
	printAdminHeader('overview', 'backup');
}

echo '</head>';

$backuprestore = new backupRestore();
if (isset($_REQUEST['autobackup'])) {
	$backuprestore->autobackup = true;
}
if (isset($_REQUEST['backup'])) {
	$backuprestore->compression_level = sanitize($_REQUEST['compress'], 3);
	$backuprestore->createBackup();
} else if (isset($_REQUEST['restore']) && isset($_REQUEST['backupfile'])) {
	$backupfile = sanitize($_REQUEST['backupfile'], 3);
	$backuprestore->restoreBackup($backupfile);
}
?>

<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php
			if (!$_zp_current_admin_obj->reset) {
				printSubtabs();
			}
			?>
			<div class="tabbox">
				<?php zp_apply_filter('admin_note', 'backkup', ''); ?>
				<h1>
					<?php
					if ($_zp_current_admin_obj->reset) {
						echo (gettext('Restore your database content'));
					} else {
						echo (gettext('Backup and Restore your database content'));
					}
					?>
				</h1>
				<?php
				$backuprestore->printMessages();
				$compression_level = getOption('backup_compression');
				?>
				<p>
					<?php printf(gettext("Database software <strong>%s</strong>"), DATABASE_SOFTWARE); ?><br />
					<?php printf(gettext("Database name <strong>%s</strong>"), $_zp_db->getDBName()); ?><br />
					<?php printf(gettext("Tables prefix <strong>%s</strong>"), $_zp_db->getPrefix()); ?>
				</p>
				<?php
				if (!$_zp_current_admin_obj->reset) {
					echo '<p>';
					echo gettext('The backup facility creates database content snapshots in the <code>backup</code> folder of your installation. These backups are named in according to the date and time the backup was taken.' .
									'The compression level goes from 0 (no compression) to 9 (maximum compression). Higher compression requires more processing and may not result in much space savings.');
					echo '</p>';
				}
				if (!$_zp_current_admin_obj->reset) {
					?>
					<hr>
					<form name="backup_gallery" action="">
						<?php XSRFToken('backup'); ?>
						<h2><?php echo gettext('Create backup'); ?></h2>
						<input type="hidden" name="backup" value="true" />
						<div class="buttons pad_button" id="dbbackup">
							<button class="fixedwidth tooltip" type="submit" title="<?php echo gettext("Backup the table content in your database."); ?>">
								<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/burst.png" alt="" /> <?php echo gettext("Backup the Database"); ?>
							</button>
							<select name="compress">
								<?php
								for ($v = 0; $v <= 9; $v++) {
									?>
									<option value="<?php echo $v; ?>"<?php if ($compression_level == $v) echo ' selected="selected"'; ?>><?php echo $v; ?></option>
									<?php
								}
								?>
							</select> <?php echo gettext('Compression level'); ?>
						</div>

					</form>
					<br />
					<?php
				}
				$filelist = safe_glob(getBackupFolder(SERVERPATH) . '*.zdb');
				if (count($filelist) <= 0) {
					echo gettext('You have not yet created a backup set.');
				} else {
					?>
					<hr>
					<h2><?php echo gettext('Backup restore'); ?></h2>
					<?php
					echo gettext('You restore your database content by selecting a backup and pressing the <em>Restore the Database</em> button.');
					echo '</p><p class="warningbox">' . gettext('<strong>Note:</strong> Each database table is emptied before the restore is attempted. After a successful restore the database content will be in the same state as when the backup was created.');
					echo '</p><p class="notebox">';
					echo gettext('Ideally a restore should be done only on the same version of Zenphoto on which the backup was created. If you are intending to upgrade, first do the restore on the version of Zenphoto you were running, then install the new Zenphoto. If this is not possible the restore can still be done, but if the database fields have changed between versions, data from changed fields will not be restored.');
					echo '</p>';
					?>
					<form name="restore_gallery" action="">
						<?php XSRFToken('backup'); ?>

						<?php echo gettext('Select the database restore file:'); ?>
						<br />
						<select id="backupfile" name="backupfile">
							<?php generateListFromFiles('', getBackupFolder(SERVERPATH), '.zdb', true); ?>
						</select>
						<input type="hidden" name="restore" value="true" />
						<script>
							$(document).ready(function () {
								$("#restore_button").click(function () {
									if (!confirm('<?php echo gettext('Do you really want to restore the database content? Restoring the wrong backup might result in data loss!'); ?>')) {
										return false;
									}
									;
								});
							});
						</script>
						<div class="buttons pad_button" id="dbrestore">
							<button id="restore_button" class="fixedwidth tooltip" type="submit" title="<?php echo gettext("Restore the table content in your database from a previous backup."); ?>">
								<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/redo.png" alt="" /> <?php echo gettext("Restore the Database"); ?>
							</button>
						</div>
						<br class="clearall" />
						<br class="clearall" />
					</form>
					<?php
				}
				?>
			</div>
		</div><!-- content -->
	</div><!-- main -->
	<?php printAdminFooter(); ?>
</body>
<?php echo "</html>"; ?>