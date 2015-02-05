<?php
/**
 * Form for contact_form plugin
 */
?>
<form id="mailform" class="form-horizontal" action="<?php echo html_encode(getRequestURI()); ?>" method="post" accept-charset="UTF-8">
	<input type="hidden" id="sendmail" name="sendmail" value="sendmail" />

	<?php if (showOrNotShowField(getOption('contactform_title'))) { ?>
	<div class="control-group">
		<label class="control-label" for="title"><?php printf(gettext("Title%s"), (checkRequiredField(getOption('contactform_title')))); ?></label>
		<div class="controls">
			<input type="text" id="title" name="title" class="input-large" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($mailcontent['title']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_name'))) { ?>
	<div class="control-group">
		<label class="control-label" for="name"><?php printf(gettext("Name%s"), (checkRequiredField(getOption('contactform_name')))); ?></label>
		<div class="controls">
			<input type="text" id="name" name="name" class="input-large" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($mailcontent['name']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_company'))) { ?>
	<div class="control-group">
		<label class="control-label" for="company"><?php printf(gettext("Company%s"), (checkRequiredField(getOption('contactform_company')))); ?></label>
		<div class="controls">
			<input type="text" id="company" name="company" class="input-large" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($mailcontent['company']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_street'))) { ?>
	<div class="control-group">
		<label class="control-label" for="street"><?php printf(gettext("Street%s"), (checkRequiredField(getOption('contactform_street')))); ?></label>
		<div class="controls">
			<input type="text" id="street" name="street" class="input-large" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($mailcontent['street']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_city'))) { ?>
	<div class="control-group">
		<label class="control-label" for="city"><?php printf(gettext("City%s"), (checkRequiredField(getOption('contactform_city')))); ?></label>
		<div class="controls">
			<input type="text" id="city" name="city" class="input-large" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($mailcontent['city']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_state'))) { ?>
	<div class="control-group">
		<label class="control-label" for="state"><?php printf(gettext("State%s"), (checkRequiredField(getOption('contactform_state')))); ?></label>
		<div class="controls">
			<input type="text" id="state" name="state" class="input-large" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($mailcontent['city']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_country'))) { ?>
	<div class="control-group">
		<label class="control-label" for="country"><?php printf(gettext("Country%s"), (checkRequiredField(getOption('contactform_country')))); ?></label>
		<div class="controls">
			<input type="text" id="country" name="country" class="input-large" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($mailcontent['country']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_postal'))) { ?>
	<div class="control-group">
		<label class="control-label" for="postal"><?php printf(gettext("Postal code%s"), (checkRequiredField(getOption('contactform_postal')))); ?></label>
		<div class="controls">
			<input type="text" id="postal" name="postal" class="input-large" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($mailcontent['postal']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_email'))) { ?>
	<div class="control-group">
		<label class="control-label" for="email"><?php printf(gettext("E-Mail%s"), (checkRequiredField(getOption('contactform_email')))); ?></label>
		<div class="controls">
			<input type="text" id="email" name="email" class="input-large" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($mailcontent['email']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_website'))) { ?>
	<div class="control-group">
		<label class="control-label" for="website"><?php printf(gettext("Website%s"), (checkRequiredField(getOption('contactform_website')))); ?></label>
		<div class="controls">
			<input type="text" id="website" name="website" class="input-large" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($mailcontent['website']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_phone'))) { ?>
	<div class="control-group">
		<label class="control-label" for="phone"><?php printf(gettext("Phone%s"), (checkRequiredField(getOption('contactform_phone')))); ?></label>
		<div class="controls">
			<input type="text" id="phone" name="phone" class="input-large" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($mailcontent['phone']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	</div>
	<?php } ?>

	<?php if (getOption("contactform_captcha") && !$_processing_post) { ?>
	<div class="control-group">
		<label class="control-label" for="code"><?php echo gettext("Enter CAPTCHA<strong>*</strong>"); ?></label>
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

	<div class="control-group">
		<label class="control-label" for="subject"><?php echo gettext("Subject<strong>*</strong>"); ?></label>
		<div class="controls">
			<input type="text" id="subject" name="subject" class="input-xxlarge" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($mailcontent['subject']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="message"><?php echo gettext("Message<strong>*</strong>"); ?></label>
		<div class="controls">
			<textarea id="message" class="input-xxlarge" rows="5" cols="39" name="message" <?php if ($_processing_post) echo ' disabled="disabled"'; ?>><?php echo $mailcontent['message']; ?></textarea>
		</div>
	</div>

	<?php if (!$_processing_post) { ?>
	<div class="form-actions">
			<input class="btn btn-inverse" type="submit" value="<?php echo gettext("Send e-mail"); ?>" />
			<input class="btn btn-inverse" type="reset" value="<?php echo gettext("Reset"); ?>" />
	</div>
	<?php } ?>

</form>