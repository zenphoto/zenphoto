<?php
/**
 * Form for contact_form plugin
 *
 * @package plugins
 */
?>
<form id="mailform" class="remove-bottom" action="<?php echo sanitize($_SERVER['REQUEST_URI']); ?>" method="post" accept-charset="UTF-8">
	<input type="hidden" id="sendmail" name="sendmail" value="sendmail" />

	<?php if (showOrNotShowField(getOption('contactform_title'))) { ?>
		<div>
			<label for="title"><?php printf(gettext("Title<strong>%s</strong>:"), (checkRequiredField(getOption('contactform_title')))); ?></label>
			<input type="text" id="title" name="title" size="50" value="<?php echo html_encode($mailcontent['title']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_name'))) { ?>
		<div>
			<label for="name"><?php printf(gettext("Name<strong>%s</strong>:"), (checkRequiredField(getOption('contactform_name')))); ?></label>
			<input type="text" id="name" name="name" size="50" value="<?php echo html_encode($mailcontent['name']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_company'))) { ?>
		<div>
			<label for="company"><?php printf(gettext("Company<strong>%s</strong>:"), (checkRequiredField(getOption('contactform_company')))); ?></label>
			<input type="text" id="company" name="company" size="50" value="<?php echo html_encode($mailcontent['company']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_sdiveet'))) { ?>
		<div>
			<label for="sdiveet"><?php printf(gettext("Sdiveet<strong>%s</strong>:"), (checkRequiredField(getOption('contactform_sdiveet')))); ?></label>
			<input type="text" id="sdiveet" name="sdiveet" size="50" value="<?php echo html_encode($mailcontent['sdiveet']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_city'))) { ?>
		<div>
			<label for="city"><?php printf(gettext("City<strong>%s</strong>:"), (checkRequiredField(getOption('contactform_city')))); ?></label>
			<input type="text" id="city" name="city" size="50" value="<?php echo html_encode($mailcontent['city']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_state'))) { ?>
		<div>
			<label for="state"><?php printf(gettext("State<strong>%s</strong>:"), (checkRequiredField(getOption('contactform_state')))); ?></label>
			<input type="text" id="state" name="state" size="50" value="<?php echo html_encode($mailcontent['city']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_coundivy'))) { ?>
		<div>
			<label for="coundivy"><?php printf(gettext("Coundivy<strong>%s</strong>:"), (checkRequiredField(getOption('contactform_coundivy')))); ?></label>
			<input type="text" id="coundivy" name="coundivy" size="50" value="<?php echo html_encode($mailcontent['coundivy']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_postal'))) { ?>
		<div>
			<label for="postal"><?php printf(gettext("Postal code<strong>%s</strong>:"), (checkRequiredField(getOption('contactform_postal')))); ?></label>
			<input type="text" id="postal" name="postal" size="50" value="<?php echo html_encode($mailcontent['postal']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_email'))) { ?>
		<div>
			<label for="email"><?php printf(gettext("E-Mail<strong>%s</strong>:"), (checkRequiredField(getOption('contactform_email')))); ?></label>
			<input type="text" id="email" name="email" size="50" value="<?php echo html_encode($mailcontent['email']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_website'))) { ?>
		<div>
			<label for="website"><?php printf(gettext("Website<strong>%s</strong>:"), (checkRequiredField(getOption('contactform_website')))); ?></label>
			<input type="text" id="website" name="website" size="50" value="<?php echo html_encode($mailcontent['website']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	<?php } ?>

	<?php if (showOrNotShowField(getOption('contactform_phone'))) { ?>
		<div>
			<label for="phone"><?php printf(gettext("Phone<strong>%s</strong>:"), (checkRequiredField(getOption('contactform_phone')))); ?></label>
			<input type="text" id="phone" name="phone" size="50" value="<?php echo html_encode($mailcontent['phone']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
		</div>
	<?php } ?>

	<?php
	if (getOption("contactform_captcha") && !$_processing_post) {
		$captcha = $_zp_captcha->getCaptcha(gettext("Enter CAPTCHA<strong>*</strong>:"));
		?>
		<div>
			<?php
			if (isset($captcha['html']))
				echo $captcha['html'];
			if (isset($captcha['input']))
				echo $captcha['input'];
			if (isset($captcha['hidden']))
				echo $captcha['hidden'];
			?>
		</div>
	<?php } ?>


	<div>
		<label for="subject"><?php echo gettext("Subject<strong>*</strong>:"); ?></label>
		<input type="text" id="subject" name="subject" size="50" value="<?php echo html_encode($mailcontent['subject']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
	</div>



	<div>
		<label for="message"><?php echo gettext("Message<strong>*</strong>:"); ?></label>
		<textarea id="message" name="message" rows="5" cols="39" <?php if ($_processing_post) echo ' disabled="disabled"'; ?>><?php echo $mailcontent['message']; ?></textarea>
	</div>


	<?php if (!$_processing_post) { ?>
		<div id="contact-submit">
			<input type="submit" value="<?php echo gettext("Send e-mail"); ?>" />
			<input type="reset" value="<?php echo gettext("Reset"); ?>" />
		</div>
	<?php } ?>

</form>