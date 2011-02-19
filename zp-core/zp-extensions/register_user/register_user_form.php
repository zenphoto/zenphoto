<?php
/**
 * Form for registering users
 *
 * @package plugins
 * @subpackage usermanagement
 */

?>
	<form action="<?php echo sanitize($_SERVER['REQUEST_URI']); ?>" method="post" autocomplete="off">
		<input type="hidden" name="register_user" value="yes" />
		<table class="register_user">
		<tr>
			<td><?php echo gettext("Name:"); ?></td>
			<td><input type="text" id="admin_name" name="admin_name" value="<?php echo html_encode($admin_n); ?>" size="22" /></td>
		</tr>
		<tr>
			<td><?php if (getOption('register_user_email_is_id')) echo gettext("Email:"); else echo gettext("User ID:"); ?></td>
			<td><input type="text" id="adminuser" name="adminuser" value="<?php echo html_encode($user); ?>" size="22" /></td>
		</tr>
		<tr>
			<td valign="top"><?php echo gettext("Password:"); ?></td>
			<td width=400 valign="top">
				<p style="line-height: 1em;">
					<input type="password" id="adminpass" name="adminpass"	value="" size="23" />
				</p>
			</td>
		</tr>
		<tr>
			<td valign="top"><?php echo gettext("re-enter:"); ?></td>
			<td>
				<input type="password" id="adminpass_2" name="adminpass_2"	value="" size="23" />
				<?php
				$msg = $_zp_authority->passwordNote();
				if (!empty($msg)) {
					?>
					<br />
					<?php
					echo $msg;
				}
				?>
			</td>
		</tr>
		<?php
		if (!getOption('register_user_email_is_id')) {
			?>
			<tr>
				<td><?php echo gettext("Email:"); ?></td>
				<td><input type="text" id="admin_email" name="admin_email" value="<?php echo html_encode($admin_e); ?>" size="22" /></td>
			</tr>
			<?php
		}
		$html = zp_apply_filter('register_user_form', '');
		if (!empty($html)) echo $html;
		if (getOption('register_user_captcha')) {
			?>
			<tr>
				<td>
					<?php
					$captchaCode = generateCaptcha($img);
					$html = "<img src=\"" . $img . "\" alt=\"Code\" align=\"bottom\"/>";
					?>
					<input type="hidden" name="code_h" value="<?php echo $captchaCode; ?>" size="22" />
					<?php
					printf(gettext("Enter %s"),$html);
					?>
				</td>
				<td><input type="text" id="code" name="code" value="" size="22" /></td>
			</tr>
			<?php
		}
		?>
		</table>
		<input type="submit" value="<?php echo gettext('Submit') ?>" />
		<?php
		if (function_exists('federated_login_buttons')) {

			?>
			<fieldset id="Federated_buttons_fieldlist">
				<legend><?php echo gettext('You may also register using federated credentials'); ?></legend>
				<?php federated_login_buttons(WEBPATH.'/index.php'); ?>
			</fieldset>
			<?php
		}
		?>
	</form>