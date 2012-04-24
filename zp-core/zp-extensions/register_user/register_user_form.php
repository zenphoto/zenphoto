<?php
/**
 * Form for registering users
 *
 * @package plugins
 * @subpackage usermanagement
 */

Zenphoto_Authority::printPasswordFormJS();
$action = preg_replace('/\?verify=(.*)/', '', sanitize($_SERVER['REQUEST_URI']));
?>
<div id="registration_form">
	<form action="<?php echo $action; ?>" method="post" autocomplete="off">
		<input type="hidden" name="register_user" value="yes" />

		<fieldset><legend><?php if ($emailid = getOption('register_user_email_is_id')) echo gettext("Email* (this will be your user id)"); else echo gettext("User ID").'*'; ?></legend>
			<input type="text" id="adminuser" name="user" value="<?php echo html_encode($user); ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
		</fieldset>
		<?php $_zp_authority->printPasswordForm('', false, NULL, false, $flag='*'); ?>
		<fieldset><legend><?php echo gettext("Name"); ?>*</legend>
			<input type="text" id="admin_name" name="admin_name" value="<?php echo html_encode($admin_n); ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
		</fieldset>
		<?php
		if (!getOption('register_user_email_is_id')) {
			?>
			<fieldset><legend><?php echo gettext("Email"); ?><?php if (!$emailid) echo '*'; ?></legend>
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
					$legend = trim(str_replace(array('<td>',':'), '', $elements[0]));
					if (!empty($legend)) {
						$input = str_replace('size="40"', 'size="'.TEXT_INPUT_SIZE.'"', $elements[1]);
						$input = str_replace('class="inputbox"', '', $input);
						?>
						<fieldset><legend><?php echo $legend; ?></legend>
							<?php echo trim(str_replace('<td>', '', $input)); ?>
						</fieldset>
						<?php
					}
				}
			}
		}
		if (getOption('register_user_captcha')) {
			$captcha = $_zp_captcha->getCaptcha();
			?>
			<fieldset><legend><?php echo gettext("Enter"); ?></legend>
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
		?>
		<div style="text-align:right"><?php echo gettext('*Required'); ?></div>
		<input type="submit" value="<?php echo gettext('Submit') ?>" />
		<?php
		if (class_exists('federated_logon')) {

			?>
			<fieldset id="Federated_buttons_fieldlist">
				<legend><?php echo gettext('You may also register using federated credentials'); ?></legend>
				<?php federated_logon::buttons(WEBPATH.'/index.php'); ?>
			</fieldset>
			<?php
		}
		?>
	</form>
</div>