<?php
/**
 * Form for contact_form plugin
 *
 * @package plugins
 */
?>
<form id="mailform" action="<?php echo html_encode(getRequestURI()); ?>" method="post" accept-charset="UTF-8" class="form-horizontal" role="form">
	<input type="hidden" id="sendmail" name="sendmail" value="sendmail" />
	<?php
	$star = '<strong>*</strong>';
	if (showOrNotShowField(getOption('contactform_title'))) {
		?>
		<div class="form-group">
			<label for="title" class="col-sm-3 control-label"><?php printf(gettext("Title%s"), checkRequiredField(getOption('contactform_title'))); ?></label>
			<div class="col-sm-9">
				<input class="form-control" type="text" id="title" name="title" size="50" value="<?php echo html_encode($mailcontent['title']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</div>	
		</div>
		<?php
	}
	if (showOrNotShowField(getOption('contactform_name'))) {
		?>
		<div class="form-group">
			<label for="name" class="col-sm-3 control-label"><?php printf(gettext("Name%s"), checkRequiredField(getOption('contactform_name'))); ?></label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="name" name="name" size="50" value="<?php echo html_encode($mailcontent['name']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</div>
		</div>
		<?php
	}
	?>
	<div class="form-group" style="display:none;">
		<label for="username" class="col-sm-3 control-label">Username:</label>
		<div class="col-sm-9">	
			<input type="text" class="form-control" id="username" name="username" size="50" value="<?php echo html_encode($mailcontent['honeypot']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	</div>
	<?php
	if (showOrNotShowField(getOption('contactform_company'))) {
		?>
		<div class="form-group">
			<label for="company" class="col-sm-3 control-label"><?php printf(gettext("Company%s"), checkRequiredField(getOption('contactform_company'))); ?></label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="company" name="company" size="50" value="<?php echo html_encode($mailcontent['company']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</div>
		</div>
		<?php
	}
	if (showOrNotShowField(getOption('contactform_street'))) {
		?>
		<div class="form-group">
			<label for="street" class="col-sm-3 control-label"><?php printf(gettext("Street%s"), checkRequiredField(getOption('contactform_street'))); ?></label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="street" name="street" size="50" value="<?php echo html_encode($mailcontent['street']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</div>	
		</div>
		<?php
	}
	if (showOrNotShowField(getOption('contactform_city'))) {
		?>
		<div class="form-group">
			<label for="city" class="col-sm-3 control-label"><?php printf(gettext("City%s"), checkRequiredField(getOption('contactform_city'))); ?></label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="city" name="city" size="50" value="<?php echo html_encode($mailcontent['city']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</div>
		</div>
		<?php
	}
	if (showOrNotShowField(getOption('contactform_state'))) {
		?>
		<div class="form-group">
			<label for="state" class="col-sm-3 control-label"><?php printf(gettext("State%s"), checkRequiredField(getOption('contactform_state'))); ?></label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="state" name="state" size="50" value="<?php echo html_encode($mailcontent['city']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</div>
		</div>
		<?php
	}
	if (showOrNotShowField(getOption('contactform_country'))) {
		?>
		<div class="form-group">
			<label for="country" class="col-sm-3 control-label"><?php printf(gettext("Country%s"), checkRequiredField(getOption('contactform_country'))); ?></label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="country" name="country" size="50" value="<?php echo html_encode($mailcontent['country']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</div>
		</div>
		<?php
	}
	if (showOrNotShowField(getOption('contactform_postal'))) {
		?>
		<div class="form-group">
			<label for="postal" class="col-sm-3 control-label"><?php printf(gettext("Postal code%s"), checkRequiredField(getOption('contactform_postal'))); ?></label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="postal" name="postal" size="50" value="<?php echo html_encode($mailcontent['postal']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</div>
		</div>
		<?php
	}
	if (showOrNotShowField(getOption('contactform_email'))) {
		?>
		<div class="form-group">
			<label for="email" class="col-sm-3 control-label"><?php printf(gettext("E-Mail%s"), checkRequiredField(getOption('contactform_email'))); ?></label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="email" name="email" size="50" value="<?php echo html_encode($mailcontent['email']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</div>
		</div>
		<?php
	}
	if (showOrNotShowField(getOption('contactform_website'))) {
		?>
		<div class="form-group">
			<label for="website" class="col-sm-3 control-label"><?php printf(gettext("Website%s"), checkRequiredField(getOption('contactform_website'))); ?></label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="website" name="website" size="50" value="<?php echo html_encode($mailcontent['website']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</div>
		</div>
		<?php
	}
	if (showOrNotShowField(getOption('contactform_phone'))) {
		?>
		<div class="form-group">
			<label for="phone" class="col-sm-3 control-label"><?php printf(gettext("Phone%s"), checkRequiredField(getOption('contactform_phone'))); ?></label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="phone" name="phone" size="50" value="<?php echo html_encode($mailcontent['phone']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</div>
		</div>
		<?php
	}
	if (getOption("contactform_captcha") && !$_processing_post) {
		$captcha = $_zp_captcha->getCaptcha(gettext("Enter CAPTCHA<strong>*</strong>"));
		?>
		<div class="form-group">
			<?php
			if (isset($captcha['html']))
				echo $captcha['html'];
				echo '<div class="col-sm-9">';
			if (isset($captcha['input']))
				echo $captcha['input'];
			if (isset($captcha['hidden']))
				echo $captcha['hidden'];
				echo '</div>';
			?>
		</div>
		<?php
	}
	?>
		<div class="form-group">
			<label for="subject" class="col-sm-3 control-label"><?php echo gettext("Subject<strong>*</strong>"); ?></label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="subject" name="subject" size="50" value="<?php echo html_encode($mailcontent['subject']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</div>
		</div>
	<div class="mailmessage form-group">
		<label for="message" class="col-sm-3 control-label"><?php echo gettext("Message<strong>*</strong>"); ?></label>
		<div class="col-sm-9">
			<textarea class="form-control" id="message" name="message" <?php if ($_processing_post) echo ' disabled="disabled"'; ?>><?php echo $mailcontent['message']; ?></textarea>
		</div>
	</div>
	<?php
	if (!$_processing_post) {
		?>
		<div class="col-sm-9 col-sm-offset-3">
			<input type="submit" class="button buttons" value="<?php echo gettext("Send e-mail"); ?>" onclick="ga('send', 'event', 'contact-form', 'submit');"/>
		</div>
	<?php } ?>
</form>