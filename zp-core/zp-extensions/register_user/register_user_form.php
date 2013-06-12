<?php
/**
 * Form for registering users
 *
 * @package plugins
 * @subpackage users
 */

Zenphoto_Authority::printPasswordFormJS();
$action = preg_replace('/\?verify=(.*)/', '', getRequestURI());
?>
<div id="registration_form">
	<form action="<?php echo $action; ?>" method="post" autocomplete="off">
		<input type="hidden" name="register_user" value="yes" />
		<p style="display:none;">
			<label for="username"><?php echo gettext("Username* (this will be your user username)"); ?></label>
			<input type="text" id="username" name="username" value="" size="<?php echo TEXT_INPUT_SIZE; ?>" />
		</p>
		<p>
			<label for="adminuser">
				<?php
					if ($emailid = getOption('register_user_email_is_id')) {
						echo gettext("Email<strong>*</strong> (this will be your user id)");
					} else {
						echo gettext("User ID").'<strong>*</strong>';
					} ?>
			</label>
			<input type="text" id="adminuser" name="user" value="<?php echo html_encode($user); ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
	  </p>
		<?php $_zp_authority->printPasswordForm('', false, NULL, false, $flag='<strong>*</strong>'); ?>
		<p>
			<label for="admin_name"><?php echo gettext("Name"); ?><strong>*</strong></label>
			<input type="text" id="admin_name" name="admin_name" value="<?php echo html_encode($admin_n); ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
		</p>
		<?php
		if (!getOption('register_user_email_is_id')) {
			?>
			<p>
				<label for="admin_email"><?php echo gettext("Email"); ?><?php if (!$emailid) echo '<strong>*</strong>'; ?></label>
				<input type="text" id="admin_email" name="admin_email" value="<?php echo html_encode($admin_e); ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
			</p>
			<?php
		}
		$html = zp_apply_filter('register_user_form', '');
		if (!empty($html)) {
			$rows = explode('</tr>', $html);
			foreach ($rows as $row) {
				if (!empty($row)) {
					$row = str_replace('<tr>','',$row);
					$elements = explode('</td>',$row);
					$col1 = trim(str_replace(array('<td>',':'), '', $elements[0]));
					if (count($elements)==1) {	//	new style form
						echo $col1;
					} else {										//	old table style form
						$col2 = str_replace('size="40"', 'size="'.TEXT_INPUT_SIZE.'"', $elements[1]);
						$col2 = str_replace('class="inputbox"', '', $input);
						?>
						<p>
							<label><?php echo $col1; ?></label>
							<?php echo trim(str_replace('<td>', '', $col2)); ?>
						</p>
						<?php
					}
				}
			}
		}
		if (getOption('register_user_captcha')) {
			$captcha = $_zp_captcha->getCaptcha(gettext("Enter CAPTCHA<strong>*</strong>"));
			?>
			<p>
				<?php
				if (isset($captcha['html'])) echo $captcha['html'];
				if (isset($captcha['input'])) echo $captcha['input'];
				if (isset($captcha['hidden'])) echo $captcha['hidden'];
				?>
			</p>
			<?php
		}
		?>
		<p><?php echo gettext('<strong>*</strong>Required'); ?></p>
		<input type="submit" class="button buttons" value="<?php echo gettext('Submit') ?>" />
		<?php
		if (class_exists('federated_logon')) {
			?>
			<p id="Federated_buttons_fieldlist">
				<?php echo gettext('You may also register using federated credentials'); ?>
				<?php federated_logon::buttons(WEBPATH.'/index.php'); ?>
			</p>
			<?php
		}
		?>
	</form>
</div>