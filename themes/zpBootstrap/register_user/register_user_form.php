<?php
/**
 * Form for registering users
 */
Zenphoto_Authority::printPasswordFormJS(true);
$action = preg_replace('/\?verify=(.*)/', '', getRequestURI());
$emailid = getOption('register_user_email_is_id');
?>
<form id="registration_form" class="form-horizontal" action="<?php echo $action; ?>" method="post" autocomplete="off">
	<input type="hidden" name="register_user" value="yes" />
	<div class="control-group hide">
		<label class="control-label" for="username">Username</label>
		<div class="controls">
			<input type="text" id="username" class="span3" name="username" value="" size="<?php echo TEXT_INPUT_SIZE; ?>" />
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="adminuser">
			<?php
			if ($emailid) {
				echo gettext("Email<strong>*</strong> (this will be your user id)");
			} else {
				echo gettext("User ID") . ' <strong>*</strong>';
			}
			?>
		</label>
		<div class="controls">
			<input type="text" id="adminuser" class="input-large" name="user" value="<?php echo html_encode($user); ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
		</div>
	</div>

<?php $_zp_authority->printPasswordForm(NULL, false, NULL, false, $flag = '<strong>*</strong>'); ?>

	<div class="control-group">
		<label class="control-label" for="admin_name"><?php echo gettext("Name"); ?><strong>*</strong></label>
		<div class="controls">
			<input type="text" id="admin_name" class="input-large" name="admin_name" value="<?php echo html_encode($admin_n); ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
		</div>
	</div>

<?php if (!$emailid) { ?>
		<div class="control-group">
			<label class="control-label" for="admin_email"><?php echo gettext("Email"); ?><strong>*</strong></label>
			<div class="controls">
				<input type="text" id="admin_email" class="input-large" name="admin_email" value="<?php echo html_encode($admin_e); ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
			</div>
		</div>
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
			<div class="control-group">
				<label class="control-label" for="0-comment_form_street"><?php printf(gettext('Street%s'), $required); ?></label>
				<div class="controls">
					<input type="text" id="0-comment_form_street" class="input-large" name="0-comment_form_street" value="<?php echo $address['street']; ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="0-comment_form_city"><?php printf(gettext('City%s'), $required); ?></label>
				<div class="controls">
					<input type="text" id="0-comment_form_city" class="input-large" name="0-comment_form_city" value="<?php echo $address['city']; ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
				</div>
			</div>


			<div class="control-group">
				<label class="control-label" for="0-comment_form_state"><?php printf(gettext('State%s'), $required); ?></label>
				<div class="controls">
					<input type="text" id="0-comment_form_state" class="input-large" name="0-comment_form_state" value="<?php echo $address['state']; ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="0-comment_form_country"><?php printf(gettext('Country%s'), $required); ?></label>
				<div class="controls">
					<input type="text" id="0-comment_form_country" class="input-large" name="0-comment_form_country" value="<?php echo $address['country']; ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="0-comment_form_postal"><?php printf(gettext('Postal code%s'), $required); ?></label>
				<div class="controls">
					<input type="text" id="0-comment_form_postal" class="input-large" name="0-comment_form_postal" value="<?php echo $address['postal']; ?>" size="<?php echo TEXT_INPUT_SIZE; ?>" />
				</div>
			</div>
			<?php
		}
	}

	if (getOption('register_user_captcha')) {
		?>
		<div class="control-group">
			<label class="control-label" for="code">
				<?php echo gettext("Enter CAPTCHA<strong>*</strong>"); ?>
			</label>
			<div class="controls">
				<?php
				$captcha = $_zp_captcha->getCaptcha('');
				if (isset($captcha['html']))
					echo $captcha['html'];
				if (isset($captcha['input']))
					echo $captcha['input'];
				if (isset($captcha['hidden']))
					echo $captcha['hidden'];
				?>
			</div>
		</div>
<?php } ?>

	<div><?php echo gettext('<strong>*</strong>Required'); ?></div>

	<div id="contact-submit" class="form-actions">
		<input class="btn btn-inverse" type="submit" value="<?php echo gettext('Submit') ?>" />
	</div>

		<?php if (extensionEnabled('federated_logon')) { ?>
		<fieldset id="Federated_buttons_fieldlist">
			<legend><?php echo gettext('You may also register using federated credentials'); ?></legend>
	<?php federated_logon::buttons(WEBPATH . '/index.php'); ?>
		</fieldset>
<?php } ?>

</form>