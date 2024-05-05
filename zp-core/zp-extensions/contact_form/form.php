<?php
/**
 * Form for contact_form plugin
 *
 * @package zpcore\plugins\contactform
 */
?>
<form id="mailform" action="<?php echo html_encode(getRequestURI()); ?>" method="post" accept-charset="UTF-8">
	<input type="hidden" id="sendmail" name="sendmail" value="sendmail" />
	<?php
	if (contactForm::isVisibleField('contactform_title')) {
		?>
		<p>
			<label for="title"><?php printf(gettext("Title%s"), contactform::getRequiredFieldMark('contactform_title')); ?></label>
			<input type="text" id="title" name="title" size="50" value="<?php echo html_encode($mailcontent['title']); ?>"<?php contactForm::printAttributes('contactform_title'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_name')) {
		?>
		<p>
			<label for="name"><?php printf(gettext("Name%s"), contactform::getRequiredFieldMark('contactform_name')); ?></label>
			<input type="text" id="name" name="name" size="50" value="<?php echo html_encode($mailcontent['name']); ?>"<?php contactForm::printAttributes('contactform_name'); ?> />
		</p>
		<?php
	}
	?>
	<p style="display:none;">
		<label for="username"><?php echo gettext('Username:'); ?></label>
		<input type="text" id="username" name="username" size="50" value="<?php echo html_encode($mailcontent['honeypot']); ?>"<?php echo contactform::getProcessedFieldDisabledAttr(); ?> />
	</p>
	<?php
	if (contactForm::isVisibleField('contactform_company')) {
		?>
		<p>
			<label for="company"><?php printf(gettext("Company%s"), contactform::getRequiredFieldMark('contactform_company')); ?></label>
			<input type="text" id="company" name="company" size="50" value="<?php echo html_encode($mailcontent['company']); ?>"<?php contactForm::printAttributes('contactform_company'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_street')) {
		?>
		<p>
			<label for="street"><?php printf(gettext("Street%s"), contactform::getRequiredFieldMark('contactform_street')); ?></label>
			<input type="text" id="street" name="street" size="50" value="<?php echo html_encode($mailcontent['street']); ?>"<?php contactForm::printAttributes('contactform_street'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_city')) {
		?>
		<p>
			<label for="city"><?php printf(gettext("City%s"), contactform::getRequiredFieldMark('contactform_city')); ?></label>
			<input type="text" id="city" name="city" size="50" value="<?php echo html_encode($mailcontent['city']); ?>"<?php contactForm::printAttributes('contactform_city'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_state')) {
		?>
		<p>
			<label for="state"><?php printf(gettext("State%s"), contactform::getRequiredFieldMark('contactform_state')); ?></label>
			<input type="text" id="state" name="state" size="50" value="<?php echo html_encode($mailcontent['city']); ?>"<?php contactForm::printAttributes('contactform_state'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_country')) {
		?>
		<p>
			<label for="country"><?php printf(gettext("Country%s"), contactform::getRequiredFieldMark('contactform_country')); ?></label>
			<input type="text" id="country" name="country" size="50" value="<?php echo html_encode($mailcontent['country']); ?>"<?php contactForm::printAttributes('contactform_country'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_postal')) {
		?>
		<p>
			<label for="postal"><?php printf(gettext("Postal code%s"), contactform::getRequiredFieldMark('contactform_postal')); ?></label>
			<input type="text" id="postal" name="postal" size="50" value="<?php echo html_encode($mailcontent['postal']); ?>"<?php contactForm::printAttributes('contactform_postal'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_email')) {
		?>
		<p>
			<label for="email"><?php printf(gettext("E-Mail%s"), contactform::getRequiredFieldMark('contactform_email')); ?></label>
			<input type="email" id="email" name="email" size="50" value="<?php echo html_encode($mailcontent['email']); ?>"<?php contactForm::printAttributes('contactform_email'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_website')) {
		?>
		<p>
			<label for="website"><?php printf(gettext("Website%s"), contactform::getRequiredFieldMark('contactform_website')); ?></label>
			<input type="text" id="website" name="website" size="50" value="<?php echo html_encode($mailcontent['website']); ?>"<?php contactForm::printAttributes('contactform_website'); ?> />
		</p>
		<?php
	}
	if (contactForm::isVisibleField('contactform_phone')) {
		?>
		<p>
			<label for="phone"><?php printf(gettext("Phone%s"), contactform::getRequiredFieldMark('contactform_phone')); ?></label>
			<input type="tel" id="phone" name="phone" size="50" value="<?php echo html_encode($mailcontent['phone']); ?>"<?php contactForm::printAttributes('contactform_phone'); ?> />
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
		<textarea id="message" name="message" <?php echo contactform::getProcessedFieldDisabledAttr(); ?> required="required"><?php echo $mailcontent['message']; ?></textarea>
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