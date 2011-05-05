<?php
/**
 * user_groups log--tabs
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
define('OFFSET_PATH', 1);
require_once(dirname(__FILE__).'/admin-functions.php');
require_once(dirname(__FILE__).'/admin-globals.php');

admin_securityChecks(NULL, currentRelativeURL(__FILE__));

if (isset($_GET['action'])) {
	$action = sanitize($_GET['action'],3);
	$file = SERVERPATH.'/'.DATA_FOLDER . '/'.sanitize($_POST['filename'],3);
	XSRFdefender($action);
	if (zp_apply_filter('admin_log_actions', true, $file, $action)) {
		switch ($action) {
			case 'clear_log':
				$f = fopen($file, 'w');
				ftruncate($f,0);
				fclose($f);
				clearstatcache();
				if (basename($file) == 'security_log.txt') {
					zp_apply_filter('admin_log_actions', true, $file, $action);	// have to record the fact
				}
				break;
			case 'delete_log':
				@unlink($file);
				clearstatcache();
				unset($_GET['tab']); // it is gone, after all
				if (basename($file) == 'security_log.txt') {
					zp_apply_filter('admin_log_actions', true, $file, $action);	// have to record the fact
				}
				break;
			case 'download_log':
				include_once(SERVERPATH.'/'.ZENFOLDER . '/archive.php');
				$subtab = sanitize($_GET['tab'],3);
				$dest = SERVERPATH.'/'.DATA_FOLDER . '/'.$subtab. ".zip";
				$rp = dirname($file);
				$z = new zip_file($dest);
				$z->set_options(array('basedir' => $rp, 'inmemory' => 0, 'recurse' => 0, 'storepaths' => 1));
				$z->add_files(array(basename($file)));
				$z->create_archive();
				header('Content-Type: application/zip');
				header('Content-Disposition: attachment; filename="' . $subtab . '.zip"');
				header("Content-Length: " . filesize($dest));
				printLargeFileContents($dest);
				unlink($dest);
				break;
		}
	}
}
// Print our header

$filelist = safe_glob(SERVERPATH . "/" . DATA_FOLDER . '/*.txt');
if (count($filelist)>0) {
	$subtabs = array();
	if (isset($_GET['tab'])) {
		$default = sanitize($_GET['tab'],3);
	} else {
		$default = NULL;
	}
	foreach ($filelist as $logfile) {
		$log = substr(basename($logfile), 0, -4);
		$logfiletext = str_replace('_', ' ',$log);
		$logfiletext = strtoupper(substr($logfiletext, 0, 1)).substr($logfiletext, 1);
		$subtabs = array_merge($subtabs, array($logfiletext => 'admin-logs.php?page=logs&amp;tab='.$log));
		if (filesize($logfile) > 0 && empty($default)) {
			$default = $log;
		}
	}

	$zenphoto_tabs['logs']['subtabs'] = $subtabs;
	$logfiletext = str_replace('_', ' ',$default);
	$logfiletext = strtoupper(substr($logfiletext, 0, 1)).substr($logfiletext, 1);
	$logfile = SERVERPATH . "/" . DATA_FOLDER . '/'.$default.'.txt';
	if (filesize($logfile) > 0) {
		$logtext = explode("\n",file_get_contents($logfile));
	} else {
		$logtext = array();
	}
}
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
	if (count($filelist)>0) {
	?>
		<h1><?php echo gettext("View logs:");?></h1>

		<?php $subtab = printSubtabs($default); ?>
			<!-- A log -->
			<div id="theme-editor" class="tabbox">
				<?php zp_apply_filter('admin_note','logs', $subtab); ?>
				<form name="delete_log" action="?action=delete_log&amp;page=logs&amp;tab=<?php echo $subtab; ?>" method="post" style="float: left">
					<?php XSRFToken('delete_log');?>
					<input type="hidden" name="action" value="delete" />
					<input type="hidden" name="filename" value="<?php echo $subtab; ?>.txt" />
					<div class="buttons">
						<button type="submit" class="tooltip" id="delete_log_<?php echo $subtab; ?>" title="<?php printf(gettext("Delete %s"),$logfiletext);?>">
							<img src="images/edit-delete.png" style="border: 0px;" alt="delete" /> <?php echo gettext("Delete");?>
						</button>
					</div>
				</form>
				<?php
				if (!empty($logtext)) {
					?>
					<form name="clear_log" action="?action=clear_log&amp;page=logs&amp;tab=<?php echo $subtab; ?>" method="post" style="float: left">
						<?php XSRFToken('clear_log');?>
						<input type="hidden" name="action" value="clear" />
						<input type="hidden" name="filename" value="<?php echo $subtab; ?>.txt" />
						<div class="buttons">
							<button type="submit" class="tooltip" id="clear_log_<?php echo $subtab; ?>" title="<?php printf(gettext("Reset %s"),$logfiletext);?>">
								<img src="images/refresh.png" style="border: 0px;" alt="clear" /> <?php echo gettext("Reset");?>
							</button>
						</div>
					</form>

					<form name="download_log" action="?action=download_log&amp;page=logs&amp;tab=<?php echo $subtab; ?>" method="post" style="float: left">
						<?php XSRFToken('download_log');?>
						<input type="hidden" name="action" value="download" />
						<input type="hidden" name="filename" value="<?php echo $subtab; ?>.txt" />
						<div class="buttons">
							<button type="submit" class="tooltip" id="download_log_<?php echo $subtab; ?>" title="<?php printf(gettext("Download %s ZIP file"),$logfiletext);?>">
								<img src="images/down.png" style="border: 0px;" alt="download" /> <?php echo gettext("Download");?>
							</button>
						</div>
					</form>
					<?php
				}
				?>
				<br clear="all" />
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
									$fields = explode("\t", $line);
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
										echo str_replace(' ','&nbsp;',html_encode(strip_tags($line)));
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