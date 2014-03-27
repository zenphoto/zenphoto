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
					echo gettext("User ID") . '<strong>*</strong>';
				}
				?>
			</label>
			<input type="text" id="adminuser" name="user" value="<?php echo html_encode($user); ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
		</p>
		<?php $_zp_authority->printPasswordForm(NULL, false, NULL, false, $flag = '<strong>*</strong>'); ?>
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
		if (extensionEnabled('userAddressFields')) {
			$address = getSerializedArray(zp_getCookie('reister_user_form_addresses'));
			if (empty($address)) {
				$address = array('street' => '', 'city' => '', 'state' => '', 'country' => '', 'postal' => '', 'website' => '');
			}
			$show = $required = getOption('register_user_address_info');
			if ($required == 'required') {
				$required = '<strong>*</strong>';
			} else {
				$required = false;
			}
			if ($show) {
				?>
				<p>
					<label for="comment_form_street">
						<?php printf(gettext('Street%s'), $required); ?>
					</label>
					<input type="text" name="0-comment_form_street" id="comment_form_street" class="inputbox" size="40" value="<?php echo $address['street']; ?>">
				</p>
				<p>
					<label for="comment_form_city">
						<?php printf(gettext('City%s'), $required); ?>
					</label>
					<input type="text" name="0-comment_form_city" id="comment_form_city" class="inputbox" size="40" value="<?php echo $address['city']; ?>">
				</p>
				<p>
					<label for="comment_form_state">
						<?php printf(gettext('State%s'), $required); ?>
					</label>
					<input type="text" name="0-comment_form_state" id="comment_form_state" class="inputbox" size="40" value="<?php echo $address['state']; ?>">
				</p>
				<p>
					<label for="comment_form_country">
						<?php printf(gettext('Country%s'), $required); ?>
					</label>
					<input type="text" name="0-comment_form_country" id="comment_form_country" class="inputbox" size="40" value="<?php echo $address['country']; ?>">
				</p>
				<p>
					<label for="comment_form_postal">
						<?php printf(gettext('Postal code%s'), $required); ?>
					</label>
					<input type="text" name="0-comment_form_postal" id="comment_form_postal" class="inputbox" size="40" value="<?php echo $address['postal']; ?>">
				</p>
				<?php
			}
		}

		if (getOption('register_user_captcha')) {
			$captcha = $_zp_captcha->getCaptcha(gettext("Enter CAPTCHA<strong>*</strong>"));
			?>
			<p>
				<?php
				if (isset($captcha['html']))
					echo $captcha['html'];
				if (isset($captcha['input']))
					echo $captcha['input'];
				if (isset($captcha['hidden']))
					echo $captcha['hidden'];
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
				<?php federated_logon::buttons(WEBPATH . '/index.php'); ?>
			</p>
			<?php
		}
		?>
	</form>
</div>