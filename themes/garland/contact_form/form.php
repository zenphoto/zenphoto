<?php
/**
 * Form for contact_form plugin
 *
 */
?>
<form id="mailform" action="<?php echo html_encode(getRequestURI()); ?>" method="post" accept-charset="UTF-8">
	<input type="hidden" id="sendmail" name="sendmail" value="sendmail" />
	<table style="border:none">
		<?php if(showOrNotShowField(getOption('contactform_title'))) { ?>
		<tr>
			<td><?php printf(gettext("Title<strong>%s</strong>:"),(checkRequiredField(getOption('contactform_title')))); ?></td>
			<td><input type="text" id="title" name="title" size="50" value="<?php echo html_encode($mailcontent['title']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"';?> />
			</td>
		</tr>
		<?php } ?>
		<?php if(showOrNotShowField(getOption('contactform_name'))) { ?>
		<tr>
			<td><?php printf(gettext("Name<strong>%s</strong>:"),(checkRequiredField(getOption('contactform_name')))); ?></td>
			<td><input type="text" id="name" name="name" size="50" value="<?php echo html_encode($mailcontent['name']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</td>
		</tr>
		<?php } ?>
		<?php if(showOrNotShowField(getOption('contactform_company'))) { ?>
		<tr>
			<td><?php printf(gettext("Company<strong>%s</strong>:"),(checkRequiredField(getOption('contactform_company')))); ?></td>
			<td><input type="text" id="company" name="company" size="50" value="<?php echo html_encode($mailcontent['company']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</td>
		</tr>
		<?php } ?>
		<?php if(showOrNotShowField(getOption('contactform_street'))) { ?>
		<tr>
			<td><?php printf(gettext("Street<strong>%s</strong>:"),(checkRequiredField(getOption('contactform_street')))); ?></td>
			<td><input type="text" id="street" name="street" size="50" value="<?php echo html_encode($mailcontent['street']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</td>
		</tr>
		<?php } ?>
		<?php if(showOrNotShowField(getOption('contactform_city'))) { ?>
		<tr>
			<td><?php printf(gettext("City<strong>%s</strong>:"),(checkRequiredField(getOption('contactform_city')))); ?></td>
			<td><input type="text" id="city" name="city" size="50" value="<?php echo html_encode($mailcontent['city']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</td>
		</tr>
		<?php } ?>
		<?php if(showOrNotShowField(getOption('contactform_state'))) { ?>
		<tr>
			<td><?php printf(gettext("State<strong>%s</strong>:"),(checkRequiredField(getOption('contactform_state')))); ?></td>
			<td><input type="text" id="state" name="state" size="50" value="<?php echo html_encode($mailcontent['city']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</td>
		</tr>
		<?php } ?>
		<?php if(showOrNotShowField(getOption('contactform_country'))) { ?>
		<tr>
			<td><?php printf(gettext("Country<strong>%s</strong>:"),(checkRequiredField(getOption('contactform_country')))); ?></td>
			<td><input type="text" id="country" name="country" size="50" value="<?php echo html_encode($mailcontent['country']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</td>
		</tr>
		<?php } ?>
		<?php if(true || showOrNotShowField(getOption('contactform_postal'))) { ?>
		<tr>
			<td><?php printf(gettext("Postal code<strong>%s</strong>:"),(checkRequiredField(getOption('contactform_postal')))); ?></td>
			<td><input type="text" id="postal" name="postal" size="50" value="<?php echo html_encode($mailcontent['postal']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</td>
		</tr>
		<?php } ?>
		<?php if(showOrNotShowField(getOption('contactform_email'))) { ?>
		<tr>
			<td><?php printf(gettext("E-Mail<strong>%s</strong>:"),(checkRequiredField(getOption('contactform_email')))); ?></td>
			<td><input type="text" id="email" name="email" size="50" value="<?php echo html_encode($mailcontent['email']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</td>
		</tr>
		<?php } ?>
		<?php if(showOrNotShowField(getOption('contactform_website'))) { ?>
		<tr>
			<td><?php printf(gettext("Website<strong>%s</strong>:"),(checkRequiredField(getOption('contactform_website')))); ?></td>
			<td><input type="text" id="website" name="website" size="50" value="<?php echo html_encode($mailcontent['website']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> />
			</td>
		</tr>
		<?php } ?>
		<?php if(showOrNotShowField(getOption('contactform_phone'))) { ?>
		<tr>
			<td><?php printf(gettext("Phone<strong>%s</strong>:"),(checkRequiredField(getOption('contactform_phone')))); ?></td>
			<td><input type="text" id="phone" name="phone" size="50" value="<?php echo html_encode($mailcontent['phone']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> /></td>
		</tr>
		<?php } ?>
		<?php
		if(getOption("contactform_captcha") && !$_processing_post) {
			$captcha = $_zp_captcha->getCaptcha();
		?>
		<tr>
			<td>
				<?php
				echo gettext("Enter CAPTCHA<strong>*</strong>:").'<br />';
				if (isset($captcha['html'])) echo $captcha['html'];
				?>
			</td>
			<td>
				<?php
				if (isset($captcha['input'])) echo $captcha['input'];
				if (isset($captcha['hidden'])) echo $captcha['hidden'];
				?>
			</td>
		</tr>
		<?php } ?>
		<?php if(showOrNotShowField(getOption('contactform_subject'))) { ?>
		<tr>
			<td><?php printf(gettext("Subject<strong>%s</strong>:"),(checkRequiredField(getOption('contactform_subject')))); ?></td>
			<td><input type="text" id="subject" name="subject" size="50" value="<?php echo html_encode($mailcontent['subject']); ?>"<?php if ($_processing_post) echo ' disabled="disabled"'; ?> /></td>
		</tr>
		<?php } ?>
		<?php if(showOrNotShowField(getOption('contactform_message'))) { ?>
		<tr>
			<td><?php printf(gettext("Message<strong>%s</strong>:"),(checkRequiredField(getOption('contactform_message')))); ?></td>
			<td><textarea id="message" name="message" rows="10" cols="57" <?php if ($_processing_post) echo ' disabled="disabled"'; ?>><?php echo $mailcontent['message']; ?></textarea></td>
		</tr>
		<?php } ?>
		<?php if (!$_processing_post) { ?>
		<tr>
			<td></td>
			<td>
				<input type="submit" value="<?php echo gettext("Send e-mail"); ?>" />
				<input type="reset" value="<?php echo gettext("Reset"); ?>" />
			</td>
		</tr>
		<?php } ?>
	</table>
</form>