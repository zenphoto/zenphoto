<?php
/**
 * Database quick reference
 *
 * @package admin
 */
define('OFFSET_PATH', 3);

require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');

admin_securityChecks(NULL, currentRelativeURL());

if (isset($_POST['dbname']) || isset($_POST['dbuser']) || isset($_POST['dbpass']) || isset($_POST['dbhost'])) {
	XSRFdefender('databaseinfo');
}

printAdminHeader('overview', 'Database');
?>
<link rel="stylesheet" href="../admin-statistics.css" type="text/css" media="screen" />
<style>

	.bordered td {
		border: 1px solid  #E5E5E5;
		width:16%;
	}

	.bordered tr.grayback td {
		background-color: #FAFAFA !important;
	}

	.field {
		font-weight: bold;
	}

	h2, h3 {
		font-weight: bold;
		margin-top: 30px;
		font-size: 15px;
	}

	h2 {
		margin: 0;
	}
</style>
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php zp_apply_filter('admin_note', 'database', ''); ?>
			<h1><span id="top"><?php echo gettext('Database quick reference'); ?></span></h1>
			<div class="tabbox">
				<p>
					<?php echo gettext('Shows all database table and field info for quick reference.'); ?>
					<?php echo gettext("The internal table relations can be viewed on the PDF database reference that is included in the release package within the /docs_files folder of your installation. For more detailed info about the database use tools like phpMyAdmin."); ?>
				</p>
				<?php
				$database_name = db_name();
				$prefix = trim(prefix(), '`');
				$resource = db_show('tables');
				if ($resource) {
					$result = array();
					while ($row = db_fetch_assoc($resource)) {
						$result[] = $row;
					}
					db_free_result($resource);
				} else {
					$result = false;
				}
				$tables = array();
				if (is_array($result)) {
					foreach ($result as $row) {
						$tables[] = array_shift($row);
					}
				}
				?>
				<hr />
				<ul>
					<li>
						<?php
						$dbsoftware = db_software();
						printf(gettext('%1$s version: <strong>%2$s</strong>'), $dbsoftware['application'], $dbsoftware['version']);
						?>
					</li>
					<li><?php printf(gettext('Database name: <strong>%1$s</strong>'), $database_name); ?></li>
					<li>
						<?php
						if (empty($prefix)) {
							echo gettext('Table prefix: no prefix');
						} else {
							echo sprintf(gettext('Table prefix: <strong>%1$s</strong>'), $prefix);
						}
						?>
					</li>
				</ul>
				<ul>
					<?php
					$result = db_show('variables', 'character_set%');
					if (is_array($result)) {
						foreach ($result as $row) {
							?>
							<li><?php echo $row['Variable_name']; ?>: <strong><?php echo $row['Value']; ?></strong></li>
							<?php
						}
					}
					?>
				</ul>
				<ul>
					<?php
					$result = db_show('variables', 'collation%');
					if (is_array($result)) {
						foreach ($result as $row) {
							?>
							<li><?php echo $row['Variable_name']; ?>: <strong><?php echo $row['Value']; ?></strong></li>
							<?php
						}
					}
					?>
				</ul>
				<hr />
				<script type="text/javascript">
					function toggleRow(id) {
						if ($('#' + id).is(":visible")) {
							$('#' + id + '_k').hide();
							$('#' + id).hide();
						} else {
							$('#' + id + '_k').show();
							$('#' + id).show();
						}
					}
				</script>
				<?php
				$i = 0;
				foreach ($tables as $table) {
					$table = substr($table, strlen($prefix));
					$i++;
					?>
					<h3><a onclick="toggleRow('t_<?php echo $i; ?>')"><?php echo $table; ?></a></h3>
					<table id = "t_<?php echo $i; ?>" class="bordered" <?php if ($i > 1) { ?>style="display: none;" <?php } ?>>
						<tr>
							<?php
							$cols = $tablecols = db_list_fields($table);
							$cols = array_shift($cols);
							foreach ($cols as $col => $value) {
								?>
								<th><?php echo $col; ?></th>
								<?php
							}
							?>
						</tr>
						<?php
						$rowcount = 0;
						foreach ($tablecols as $col) {
							$rowcount++;
							if ($rowcount % 2 == 0) {
								$rowclass = ' class="grayback"';
							} else {
								$rowclass = '';
							}
							?>
							<tr<?php echo $rowclass; ?>>
								<?php
								$fieldcount = '';
								foreach ($col as $field) {
									$fieldcount++;
									$class = '';
									if ($fieldcount == 1) {
										$class = ' class="field"';
									}
									?>
									<td<?php echo $class; ?>><?php echo $field; ?></td>
									<?php
								}
								?>
							</tr>
							<?php
						}
						?>
					</table>
					<?php
					$sql = 'SHOW KEYS FROM ' . prefix($table);
					$result = query_full_array($sql);
					$nest = '';
					?>
					<div style="width:40%">
						<table id = "t_<?php echo $i; ?>_k" class="bordered" <?php if ($i > 1) { ?>style="display: none;" <?php } ?>>
							<tr>
								<th<?php echo $class; ?>>
									<?php echo gettext('Key'); ?>
								</th>
								<th<?php echo $class; ?>>
									<?php echo gettext('Column'); ?>
								</th>
								<th<?php echo $class; ?>>
									<?php echo gettext('Comment'); ?>
								</th>
							</tr>
							<?php
							foreach ($result as $key) {
								?>
								<tr>
									<td<?php echo $class; ?>>
										<?php
										if ($nest != $key['Key_name']) {
											echo $nest = $key['Key_name'];
											if (!$key['Non_unique']) {
												echo '*';
											}
										}
										?>
									</td>
									<td<?php echo $class; ?>>
										<?php
										echo $key['Column_name'];
										if ($key['Sub_part']) {
											echo ' (' . $key['Sub_part'] . ')';
										}
										?>
									</td>
									<td<?php echo $class; ?>>
										<?php echo $key['Index_comment']; ?>
									</td>
								</tr>
								<?php
							}
							?>
						</table>
					</div>
					<?php
				}
				?>
			</div>
		</div><!-- content -->
	</div><!-- main -->
	<?php printAdminFooter(); ?>
</body>
</html>
