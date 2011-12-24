<?php
/**
 * Form for registering users
 *
 * @package plugins
 * @subpackage usermanagement
 */

$_zp_authority->printPasswordFormJS();
?>
<div id="registration_form">
	<form action="<?php echo sanitize($_SERVER['REQUEST_URI']); ?>" method="post" autocomplete="off">
		<input type="hidden" name="register_user" value="yes" />

		<fieldset style="text-align:center"><legend><?php echo gettext("Name"); ?></legend>
			<input type="text" id="admin_name" name="admin_name" value="<?php echo html_encode($admin_n); ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
		</fieldset>
		<fieldset style="text-align:center"><legend><?php if (getOption('register_user_email_is_id')) echo gettext("Email"); else echo gettext("User ID"); ?></legend>
			<input type="text" id="adminuser" name="adminuser" value="<?php echo html_encode($user); ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
		</fieldset>
		<?php $_zp_authority->printPasswordForm(); ?>
		<?php
		if (!getOption('register_user_email_is_id')) {
			?>
			<fieldset style="text-align:center"><legend><?php echo gettext("Email"); ?></legend>
				<input type="text" id="admin_email" name="admin_email" value="<?php echo html_encode($admin_e); ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
			</fieldset>
			<?php
		}
		$html = zp_apply_filter('register_user_form', '');
		if (!empty($html)) {

			$rows = explode('</tr>', $html);
			foreach ($rows as $row) {
				if (!empty($row)) {
					$row = str_replace('<tr>','',$row);
					$elements = explode('</td>',$row);
					$legend = trim($elements[0]);
					if (!empty($legend)) {
						$input = str_replace('size="40"', 'size="'.TEXT_INPUT_SIZE.'"', $elements[1]);
						$input = str_replace('class="inputbox"', '', $input);
						?>
						<fieldset style="text-align:center"><legend><?php echo trim(str_replace(array('<td>',':'), '', $legend)); ?></legend>
							<?php echo trim(str_replace('<td>', '', $input)); ?>
						</fieldset>
						<?php
					}
				}
			}
		}
		if (getOption('register_user_captcha')) {
			$captcha = $_zp_captcha->getCaptcha();
			if (isset($captcha['html'])) {
			?>
			<fieldset style="text-align:center"><legend><?php echo gettext("Enter"); ?></legend>
					<?php
					if (isset($captcha['html'])) echo $captcha['html'];
					?>
					&nbsp;&nbsp;&nbsp;
					<?php
					if (isset($captcha['input'])) echo $captcha['input'];
					if (isset($captcha['hidden'])) echo $captcha['hidden'];
					?>
			</fieldset>
			<?php
			}
		}
		?>
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
</div>