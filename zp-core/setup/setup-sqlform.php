	<li>
	<div class="sqlform">
	<p><?php echo gettext("Fill in the information below and <strong>setup</strong> will attempt to update your configuration file."); ?><br />
	</p>
	<form action="" method="post"><input type="hidden" name="db" value="yes" />
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
			foreach ($engines as $engine) {
				if ($engine) {
					$handler = $engine['engine'];
					?>
					case '<?php echo $handler; ?>':
						<?php
						foreach ($engine as $field=>$show) {
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
				foreach ($engines as $engine) {
					$handler = $engine['engine'];
					$modifiers = '';
					if ($engine['enabled']) {
						if ($handler == $selected_database) {
							$modifiers = ' selected="selected"';
						}
					} else {
						$modifiers = ' disabled="disabled"';
					}
					?>
				<option value="<?php echo $handler; ?>" <?php echo $modifiers; ?>>
					<?php if (isset($engine['experimental'])) printf(gettext('%s (experimental)'),$handler); else echo $handler;?>
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
			<td><input type="password" size="40" name="db_pass" value="<?php echo $_zp_conf_vars['mysql_pass']; ?>" />&nbsp;</td>
		</tr>
		<tr id="host" >
			<td><?php echo gettext("Database host"); ?>
			</td>
			<td><input type="text" size="40" name="db_host" value="<?php echo $_zp_conf_vars['mysql_host']; ?>" /></td>
		</tr>
		<tr id="database" >
			<td><?php echo gettext("Database name"); ?></td>
			<td><input type="text" size="40" name="db_database" value="<?php echo $_zp_conf_vars['mysql_database']?>" />&nbsp;</td>
		</tr>
		<tr id="prefix" >
			<td><?php echo gettext("Database table prefix"); ?></td>
			<?php
			if($_zp_conf_vars['mysql_prefix']=='.') {
				$path = str_replace(array(' ','/'), '_', trim(WEBPATH,'/')).'_';
			} else {
				$path = $_zp_conf_vars['mysql_prefix'];
			}
			?>
			<td><input type="text" size="40" name="db_prefix" value="<?php echo $path; ; ?>" /></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="<?php echo gettext('save'); ?>" /></td>
		</tr>
	</table>
	</form>
	</div>
	</li>