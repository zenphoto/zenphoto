<?php
/**
 * user_groups log--tabs
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
define('OFFSET_PATH', 1);
require_once(dirname(__FILE__).'/admin-globals.php');

admin_securityChecks(NULL, currentRelativeURL());

if (isset($_GET['action'])) {
	$action = sanitize($_GET['action'],3);
	$what = sanitize($_GET['filename'],3);
	$file = SERVERPATH.'/'.DATA_FOLDER . '/'.$what.'.log';
	XSRFdefender($action);
	if (zp_apply_filter('admin_log_actions', true, $file, $action)) {
		switch ($action) {
			case 'clear_log':
				$_zp_mutex->lock();
				$f = fopen($file, 'w');
				if (@ftruncate($f,0)) {
					$class = 'messagebox';
					$result = sprintf(gettext('%s log was emptied.'),$what);
				} else {
					$class = 'errorbox';
					$result = sprintf(gettext('%s log could not be emptied.'),$what);
				}
				fclose($f);
				clearstatcache();
				$_zp_mutex->unlock();
				if (basename($file) == 'security.log') {
					zp_apply_filter('admin_log_actions', true, $file, $action);	// have to record the fact
				}
				break;
			case 'delete_log':
				$_zp_mutex->lock();
				@chmod($file, 0666);
				if (@unlink($file)) {
					$class = 'messagebox';
					$result = sprintf(gettext('%s log was removed.'),$what);
				} else {
					$class = 'errorbox';
					$result = sprintf(gettext('%s log could not be removed.'),$what);
				}
				clearstatcache();
				$_zp_mutex->unlock();
				unset($_GET['tab']); // it is gone, after all
				if (basename($file) == 'security.log') {
					zp_apply_filter('admin_log_actions', true, $file, $action);	// have to record the fact
				}
				break;
			case 'download_log':
				include_once(SERVERPATH.'/'.ZENFOLDER.'/lib-zipStream.php');
				$zip = new ZipStream(sanitize($_GET['tab'],3).'.zip');
				$zip->add_file_from_path(basename($file), $file);
				$zip->finish();
				break;
		}
	}
}

list($subtabs, $default) = getLogTabs();
$zenphoto_tabs['logs'] = array(	'text'=>gettext("logs"),
												'link'=>WEBPATH."/".ZENFOLDER.'/admin-logs.php?page=logs',
												'subtabs'=>$subtabs,
												'default'=>$default);

printAdminHeader('logs',$default);
echo "\n</head>";
?>

<body>

<?php	printLogoAndLinks(); ?>
<div id="main">
	<?php
	printTabs();
	?>
	<div id="content">
	<?php
	if ($default) {
		$logfiletext = str_replace('_', ' ',$default);
		$logfiletext = strtoupper(substr($logfiletext, 0, 1)).substr($logfiletext, 1);
		$logfile = SERVERPATH . "/" . DATA_FOLDER . '/'.$default.'.log';
		if (file_exists($logfile) && filesize($logfile) > 0) {
			$logtext = explode("\n",file_get_contents($logfile));
		} else {
			$logtext = array();
		}
		?>
		<h1><?php echo gettext("View logs:");?></h1>

		<?php $subtab = printSubtabs(); ?>
			<!-- A log -->
			<div id="theme-editor" class="tabbox">
				<?php zp_apply_filter('admin_note','logs', $subtab); ?>
				<?php
				if (isset($result)) {
					?>
					<div class="<?php echo $class; ?> fade-message">
						<h2><?php echo $result; ?></h2>
					</div>
					<?php
				}
				?>
				<form method="post" action="<?php echo WEBPATH.'/'.ZENFOLDER.'/admin-logs.php'; ?>?action=change_size&amp;page=logs&amp;tab=<?php echo html_encode($subtab).'&amp;filename='.html_encode($subtab); ?>" >
				<span class="button buttons">
					<a href="<?php echo WEBPATH.'/'.ZENFOLDER.'/admin-logs.php?action=delete_log&amp;page=logs&amp;tab='.html_encode($subtab).'&amp;filename='.html_encode($subtab); ?>&amp;XSRFToken=<?php  echo getXSRFToken('delete_log'); ?>">
					<img src="images/edit-delete.png" /><?php echo gettext('Delete'); ?></a>
				</span>
				<?php
				if (!empty($logtext)) {
					?>
					<span class="button buttons">
						<a href="<?php echo WEBPATH.'/'.ZENFOLDER.'/admin-logs.php?action=clear_log&amp;page=logs&amp;tab='.html_encode($subtab).'&amp;filename='.html_encode($subtab); ?>&amp;XSRFToken=<?php  echo getXSRFToken('clear_log'); ?>">
						<img src="images/refresh.png" /><?php echo gettext('Reset'); ?></a>
					</span>
					<span class="button buttons">
						<a href="<?php echo WEBPATH.'/'.ZENFOLDER.'/admin-logs.php?action=download_log&amp;page=logs&amp;tab='.html_encode($subtab).'&amp;filename='.html_encode($subtab); ?>&amp;XSRFToken=<?php  echo getXSRFToken('download_log'); ?>">
						<img src="images/arrow_down.png" /><?php echo gettext('Download'); ?></a>
					</span>
					<?php
				}
				?>
				</form>
				<br class="clearall" />
				<br />
				<blockquote class="logtext">
					<?php
					if (!empty($logtext)) {
						$header = array_shift($logtext);
						$fields = explode("\t", $header);
						if (count($fields) > 1) { // there is a header row, display in a table
							?>
							<table id="log_table">
								<?php
								if (!empty($header)) {
									?>
									<tr>
										<?php
											foreach ($fields as $field) {
												?>
												<th>
													<span class="nowrap"><?php echo $field; ?></span>
												</th>
												<?php
											}
										?>
									</tr>
									<?php
								}
								foreach ($logtext as $line) {
									?>
									<tr>
									<?php
									$fields = explode("\t", trim($line));
									foreach ($fields as $key=>$field) {
										?>
										<td>
										<?php
										if ($field) {
											?>
											<span class="nowrap"><?php echo html_encode($field); ?></span>
											<?php
										}
										?>
										</td>
										<?php
									}
									?>
									</tr>
									<?php
								}
								?>
							</table>
							<?php
						} else {
							array_unshift($logtext, $header);
							foreach ($logtext as $line) {
								if ($line) {
									?>
									<p>
										<span class="nowrap">
										<?php
										echo str_replace(' ','&nbsp;',html_encode(strip_tags(trim($line))));
										?>
										</span>
									</p>
									<?php
								}
							}
						}
					}
					?>
				</blockquote>
			</div>
			<?php
		} else {
			?>
			<h2><?php echo gettext("There are no logs to view.");?></h2>
			<?php
		}
		?>
	</div>
</div>
<?php printAdminFooter(); ?>
<?php // to fool the validator
echo "\n</body>";
echo "\n</html>";

?>
