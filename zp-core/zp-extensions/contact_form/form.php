<?php
/**
 * Form for contact_form plugin
 *
 * @package zpcore\plugins\contactform
 */
?>
<form id="mailform" action="<?php echo html_encode(getRequestURI()); ?>" method="post" accept-charset="UTF-8"<?php echo contactForm::getFormAutocompleteAttr(); ?>>
	<input type="hidden" id="sendmail" name="sendmail" value="sendmail" />
	<?php
	if (contactForm::isVisibleField('contactform_title')) {
		?>
		<p>
			<label for="title"><?php printf(gettext("Title%s"), contactForm::getRequiredFieldMark('contactform_title')); ?></label>
			<input type="text" id="title" name="title" size="50" value="<?php echo html_encode($mailcontent['title']); ?>"<?php contactForm::printAttributes('contactform_title','honorific-prefix'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_name')) {
		?>
		<p>
			<label for="name"><?php printf(gettext("Name%s"), contactForm::getRequiredFieldMark('contactform_name')); ?></label>
			<input type="text" id="name" name="name" size="50" value="<?php echo html_encode($mailcontent['name']); ?>"<?php contactForm::printAttributes('contactform_name','name'); ?> />
		</p>
		<?php
	}
	?>
	<p style="display:none;">
		<label for="username"><?php echo gettext('Username:'); ?></label>
		<input type="text" id="username" name="username"<?php contactForm::printAutocompleteAttr('username', true); ?> size="50" value="<?php echo html_encode($mailcontent['honeypot']); ?>"<?php echo contactForm::getProcessedFieldDisabledAttr(); ?> />
	</p>
	<?php
	if (contactForm::isVisibleField('contactform_company')) {
		?>
		<p>
			<label for="company"><?php printf(gettext("Company%s"), contactForm::getRequiredFieldMark('contactform_company')); ?></label>
			<input type="text" id="company" name="company" size="50" value="<?php echo html_encode($mailcontent['company']); ?>"<?php contactForm::printAttributes('contactform_company', 'organization'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_street')) {
		?>
		<p>
			<label for="street"><?php printf(gettext("Street%s"), contactForm::getRequiredFieldMark('contactform_street')); ?></label>
			<input type="text" id="street" name="street" size="50" value="<?php echo html_encode($mailcontent['street']); ?>"<?php contactForm::printAttributes('contactform_street', 'street-address'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_city')) {
		?>
		<p>
			<label for="city"><?php printf(gettext("City%s"), contactForm::getRequiredFieldMark('contactform_city')); ?></label>
			<input type="text" id="city" name="city" size="50" value="<?php echo html_encode($mailcontent['city']); ?>"<?php contactForm::printAttributes('contactform_city', 'address-level2'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_state')) {
		?>
		<p>
			<label for="state"><?php printf(gettext("State%s"), contactForm::getRequiredFieldMark('contactform_state')); ?></label>
			<input type="text" id="state" name="state size="50" value="<?php echo html_encode($mailcontent['city']); ?>"<?php contactForm::printAttributes('contactform_state', 'address-level1'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_country')) {
		?>
		<p>
			<label for="country"><?php printf(gettext("Country%s"), contactForm::getRequiredFieldMark('contactform_country')); ?></label>
			<input type="text" id="country" name="country" size="50" value="<?php echo html_encode($mailcontent['country']); ?>"<?php contactForm::printAttributes('contactform_country', 'country'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_postal')) {
		?>
		<p>
			<label for="postal"><?php printf(gettext("Postal code%s"), contactForm::getRequiredFieldMark('contactform_postal')); ?></label>
			<input type="text" id="postal" name="postal" size="50" value="<?php echo html_encode($mailcontent['postal']); ?>"<?php contactForm::printAttributes('contactform_postal', 'postal-code'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_email')) {
		?>
		<p>
			<label for="email"><?php printf(gettext("E-Mail%s"), contactForm::getRequiredFieldMark('contactform_email')); ?></label>
			<input type="email" id="email" name="email" size="50" value="<?php echo html_encode($mailcontent['email']); ?>"<?php contactForm::printAttributes('contactform_email', 'email'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_website')) {
		?>
		<p>
			<label for="website"><?php printf(gettext("Website%s"), contactForm::getRequiredFieldMark('contactform_website')); ?></label>
			<input type="url" id="website" name="website" size="50" value="<?php echo html_encode($mailcontent['website']); ?>"<?php contactForm::printAttributes('contactform_website', 'url'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_phone')) {
		?>
		<p>
			<label for="phone"><?php printf(gettext("Phone%s"), contactForm::getRequiredFieldMark('contactform_phone')); ?></label>
			<input type="tel" id="phone" name="phone" size="50" value="<?php echo html_encode($mailcontent['phone']); ?>"<?php contactForm::printAttributes('contactform_phone', 'tel'); ?> />
		</p>
		<?php
	}
	if ($_zp_captcha->name && getOption("contactform_captcha") && !contactForm::isProcessingPost()) {
		$captcha = $_zp_captcha->getCaptcha(gettext("Enter CAPTCHA<strong>*</strong>"));
		?>
		<p>
			<?php
			if (isset($captcha['html'])) {
				echo $captcha['html'];
			}
			if (isset($captcha['input'])) {
				echo $captcha['input'];
			}
			if (isset($captcha['hidden'])) {
				echo $captcha['hidden'];
			}
			?>
		</p>
		<?php
	}
	?>
	<p>
		<label for="subject"><?php echo gettext("Subject<strong>*</strong>"); ?></label>
		<input type="text" id="subject" name="subject" size="50" value="<?php echo html_encode($mailcontent['subject']); ?>"<?php echo contactForm::getProcessedFieldDisabledAttr(); ?> required />
	</p>
	<p class="mailmessage">
		<label for="message"><?php echo gettext("Message<strong>*</strong>"); ?></label>
		<textarea id="message" name="message" <?php echo contactForm::getProcessedFieldDisabledAttr(); ?> required><?php echo $mailcontent['message']; ?></textarea>
	</p>
	<?php 
	if(getOption('contactform_dataconfirmation')) { 
		$dataconfirmation_checked = '';
		if(!empty($mailcontent['dataconfirmation'])) {
			$dataconfirmation_checked = ' checked="checked"';
		} 
		?>
		<p>
			<label for="dataconfirmation">
				<input type="checkbox" name="dataconfirmation" id="dataconfirmation" value="1"<?php echo $dataconfirmation_checked; contactForm::getProcessedFieldDisabledAttr(); ?> required>
				<?php printDataUsageNotice(); echo '<strong>*</strong>'; ?>
			</label>
		</p>
	<?php } 
	if (!contactForm::isProcessingPost()) {
		?>
		<p>
			<input type="submit" class="button buttons" value="<?php echo gettext("Send e-mail"); ?>" />
			<input type="reset" class="button buttons" value="<?php echo gettext("Reset"); ?>" />
		</p>
	<?php } ?>
</form>