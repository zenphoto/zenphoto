	<li>
	<div class="sqlform">
	<p><?php echo gettext("Fill in the information below and <strong>setup</strong> will attempt to update your <code>zp-config.php</code> file."); ?><br />
	</p>
	<form action="setup.php" method="post"><input type="hidden" name="db" value="yes" />
		<input type="hidden" name="xsrfToken" value="<?php echo $xsrftoken?>" />
		<?php
		if ($debug) {
			?>
			<input type="hidden" name="debug" />
			<?php
		}
		?>
	<script type="text/javascript">
		function showFields() {
			switch ($('#dbselect').val()) {
			<?php
			foreach ($engines as $engine=>$enabled) {
				if ($enabled) {
					?>
					case '<?php echo $engine; ?>':
						<?php
						foreach ($enabled as $field=>$show) {
							if ($show) {
								?>
								$('#<?php echo $field; ?>').show();
								<?php
							} else {
								?>
								$('#<?php echo $field; ?>').hide();
								<?php
							}
						}
						?>
						break;
					<?php
				}
			}
			?>
			}
		}
		$(document).ready(function() {
			showFields();
		});
	</script>
	<table class="inputform">
		<tr>
			<td><?php echo gettext("Database engine") ?></td>
			<td><select id="dbselect" name="db_software" onchange="showFields();">
				<?php
				foreach ($engines as $engine=>$enabled) {
					$modifiers = '';
					if ($enabled) {
						if ($engine == $selected_database) {
							$modifiers = ' selected="selected"';
						}
					} else {
						$modifiers = ' disabled="disabled"';
					}
					?>
				<option value="<?php echo $engine; ?>" <?php echo $modifiers; ?>>
					<?php if (isset($enabled['experimental'])) printf(gettext('%s (experimental)'),$engine); else echo $engine;?>
				</option>
				<?php
				}
			?>
			</select></td>
		</tr>
		<tr id="user" >
			<td><?php echo gettext("Database admin user") ?></td>
			<td><input type="text" size="40" name="db_user"
				value="<?php echo $_zp_conf_vars['mysql_user']; ?>" />&nbsp;</td>
		</tr>
		<tr id="pass" >
			<td><?php echo gettext("Database admin password") ?></td>
			<td><input type="password" size="40" name="db_pass"
				value="<?php echo $_zp_conf_vars['mysql_pass']; ?>" />&nbsp;</td>
		</tr>
		<tr id="path" >
			<td><?php echo gettext("Database folder"); ?>
			</td>
			<td><input type="text" size="40" name="db_host" value="<?php
				$folder = $_zp_conf_vars['mysql_host'];
				if ($folder == 'localhost') {
					echo SERVERPATH.'/zp-data/';
				} else {
					echo $_zp_conf_vars['mysql_host'];
				}
				?>" /></td>
		</tr>
		<tr id="host" >
			<td><?php echo gettext("Database host"); ?>
			</td>
			<td><input type="text" size="40" name="db_host"
				value="<?php echo $_zp_conf_vars['mysql_host']; ?>" /></td>
		</tr>
		<tr id="name" >
			<td><?php echo gettext("Database name"); ?></td>
			<td><input type="text" size="40" name="db_database"
				value="<?php echo $_zp_conf_vars['mysql_database']?>" />&nbsp;</td>
		</tr>
		<tr id="prefix" >
			<td><?php echo gettext("Database table prefix"); ?></td>
			<td><input type="text" size="40" name="db_prefix"
				value="<?php echo $_zp_conf_vars['mysql_prefix']; ?>" /></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="<?php echo gettext('save'); ?>" /></td>
		</tr>
	</table>
	</form>
	</div>
	</li>