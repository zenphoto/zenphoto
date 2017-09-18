<?php
/**
 * user_groups log--tabs
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 */
define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . '/admin-globals.php');

admin_securityChecks(NULL, currentRelativeURL());

if (isset($_GET['action'])) {
	$action = sanitize($_GET['action'], 3);
	$what = sanitize($_GET['filename'], 3);
	$file = SERVERPATH . '/' . DATA_FOLDER . '/' . $what . '.log';
	XSRFdefender($action, $what);
	if (zp_apply_filter('admin_log_actions', true, $file, $action)) {
		switch ($action) {
			case 'clear_log':
				$_zp_mutex->lock();
				$f = fopen($file, 'w');
				if (@ftruncate($f, 0)) {
					$class = 'messagebox';
					$result = sprintf(gettext('%s log was emptied.'), $what);
				} else {
					$class = 'errorbox';
					$result = sprintf(gettext('%s log could not be emptied.'), $what);
				}
				fclose($f);
				clearstatcache();
				$_zp_mutex->unlock();
				if (basename($file) == 'security.log') {
					zp_apply_filter('admin_log_actions', true, $file, $action); // have to record the fact
				}
				break;
			case 'delete_log':
				purgeOption('logviewed_' . $what);
				$_zp_mutex->lock();
				@chmod($file, 0777);
				if (@unlink($file)) {
					$class = 'messagebox';
					$result = sprintf(gettext('%s log was removed.'), $what);
				} else {
					$class = 'errorbox';
					$result = sprintf(gettext('%s log could not be removed.'), $what);
				}
				clearstatcache();
				$_zp_mutex->unlock();
				if (basename($file) == 'security.log') {
					zp_apply_filter('admin_log_actions', true, $file, $action); // have to record the fact
				}
				header('location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-logs.php');
				exitZP();
			case 'download_log':
				putZip($what . '.zip', $file);
				exitZP();
		}
	}
}

list($logtabs, $subtab, $new) = getLogTabs();
$logname = $subtab;

printAdminHeader('logs', $subtab);

$_GET['tab'] = $subtab;
echo "\n</head>";
?>

<body>

	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php
		printTabs();

		setOption('logviewed_' . $subtab, time());
		foreach ($logtabs as $text => $link) {
			preg_match('~tab=(.*?)(&|$)~', $link, $matches);
			if (isset($matches[1])) {
				if ($matches[1] == $subtab) {
					$logname = $text;
					break;
				}
			}
		}
		?>
		<div id="content">
			<?php zp_apply_filter('admin_note', 'logs', $subtab); ?>
			<h1><?php echo ucfirst($logname); ?></h1>

			<div id="container">
				<?php
				if ($subtab) {
					$logfiletext = str_replace('_', ' ', $subtab);
					$logfiletext = strtoupper(substr($logfiletext, 0, 1)) . substr($logfiletext, 1);
					$logfile = SERVERPATH . "/" . DATA_FOLDER . '/' . $subtab . '.log';
					if (file_exists($logfile) && filesize($logfile) > 0) {
						$logtext = explode("\n", file_get_contents($logfile));
					} else {
						$logtext = array();
					}
					?>

					<!-- A log -->
					<div class="tabbox">
						<?php
						if (isset($result)) {
							?>
							<div class="<?php echo $class; ?> fade-message">
								<h2><?php echo $result; ?></h2>
							</div>
							<?php
						}
						?>
						<form method="post" action="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-logs.php'; ?>?action=change_size&amp;page=logs&amp;tab=<?php echo html_encode($subtab) . '&amp;filename=' . html_encode($subtab); ?>" >
							<span class="button buttons">
								<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-logs.php?action=delete_log&amp;page=logs&amp;tab=' . html_encode($subtab) . '&amp;filename=' . html_encode($subtab); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete_log', $subtab); ?>">
									<?php echo WASTEBASKET; ?>
									<?php echo gettext('Delete'); ?></a>
							</span>
							<?php
							if (!empty($logtext)) {
								?>
								<span class="button buttons">
									<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-logs.php?action=clear_log&amp;page=logs&amp;tab=' . html_encode($subtab) . '&amp;filename=' . html_encode($subtab); ?>&amp;XSRFToken=<?php echo getXSRFToken('clear_log', $subtab); ?>">
										<?php echo CLOCKWISE_OPEN_CIRCLE_ARROW_GREEN; ?>
										<?php echo gettext('Reset'); ?>
									</a>
								</span>
								<span class="button buttons">
									<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-logs.php?action=download_log&amp;page=logs&amp;tab=' . html_encode($subtab) . '&amp;filename=' . html_encode($subtab); ?>&amp;XSRFToken=<?php echo getXSRFToken('download_log', $subtab); ?>">
										<?php echo ARROW_DOWN_GREEN; ?>
										<?php echo gettext('Download'); ?>
									</a>
								</span>
								<?php
							}
							?>
						</form>
						<br class="clearall">
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
												foreach ($fields as $key => $field) {
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
											$line = str_replace("\t", '  ', $line);
											?>
											<p>
												<span class="nowrap">
													<?php
													echo str_replace(' ', '&nbsp;', html_encode($line));
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
					<h2><?php echo gettext("There are no logs to view."); ?></h2>
					<?php
				}
				?>
			</div>
		</div>
	</div>
	<?php printAdminFooter(); ?>
	<?php
	// to fool the validator
	echo "\n</body>";
	echo "\n</html>";
	?>
