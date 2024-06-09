<?php
/**
 * Prints an e-mail contact form that uses Zenphoto's internal validation functions for <i>e-mail</i> and <i>URL</i>.
 * <i>Name</i>, <i>e-mail address</i>, <i>subject</i> and <i>message</i> are required fields by default.
 * You need to set a custom mail address to be used for the messages destination.
 *
 * Support is included for <i>CAPTCHA</i> and for confirmation before the message is sent. No other spam filter support is provided.
 * Your mail client will provide filtering on receipt of the message.
 *
 * The contact form itself is a separate file and located within <var>%ZENFOLDER%/%PLUGIN_FOLDER%/contact_form/form.php</var>. Place a customized
 * version of the form in a similar folder in your theme if you wish something different from the standard form.
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\contactform
 */
$plugin_is_filter = 5 | FEATURE_PLUGIN;
$plugin_description = gettext("Prints an e-mail contact so that visitors may e-mail the site administrator.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$plugin_category = gettext('Mail');
$option_interface = 'contactformOptions';

$_zp_conf_vars['special_pages']['contact'] = array('define' => '_CONTACT_', 'rewrite' => getOption('contactform_rewrite'), 'option' => 'contactform_rewrite', 'default' => '_PAGE_/contact');
$_zp_conf_vars['special_pages'][] = array('definition' => '%CONTACT%', 'rewrite' => '_CONTACT_');
$_zp_conf_vars['special_pages'][] = array('define' => false, 'rewrite' => '%CONTACT%', 'rule' => '^%REWRITE%/*$		index.php?p=contact [L,QSA]');

zp_register_filter('content_macro', 'contactForm::getMacros');

/**
 * Plugin option handling class
 *
 */
class contactformOptions {

	function __construct() {
		global $_zp_authority;

		if (OFFSET_PATH == 2 && !getOption('contactform_mailaddress')) {
			purgeOption('contactform_mailaddress');
		}
		setOptionDefault('contactform_rewrite', '_PAGE_/contact');
		gettext($str = '<p>Fields with <strong>*</strong> are required. HTML or any other code is not allowed.</p>');
		setOptionDefault('contactform_introtext', getAllTranslations($str));
		gettext($str = '<p>Please confirm that you really want to send this email. Thanks.</p>');
		setOptionDefault('contactform_confirmtext', getAllTranslations($str));
		gettext($str = '<p>Thanks for your message.</p>');
		setOptionDefault('contactform_thankstext', getAllTranslations($str));
		gettext($str = 'Send another message.');
		setOptionDefault('contactform_newmessagelink', getAllTranslations($str));
		setOptionDefault('contactform_title', "show");
		setOptionDefault('contactform_name', "required");
		setOptionDefault('contactform_company', "show");
		setOptionDefault('contactform_street', "show");
		setOptionDefault('contactform_city', "show");
		setOptionDefault('contactform_state', "show");
		setOptionDefault('contactform_postal', "show");
		setOptionDefault('contactform_country', "show");
		setOptionDefault('contactform_email', "required");
		setOptionDefault('contactform_website', "show");
		setOptionDefault('contactform_phone', "show");
		setOptionDefault('contactform_captcha', 0);
		setOptionDefault('contactform_confirm', 1);
		setOptionDefault('contactform_sendcopy', 0);
		gettext($str = '<p>A copy of your e-mail will automatically be sent to the address you provided for your own records.</p>');
		setOptionDefault('contactform_sendcopy_text', getAllTranslations($str));
		$mailings = $_zp_authority->getAdminEmail();
		$email_list = '';
		foreach ($mailings as $email) {
			$email_list .= ';' . $email;
		}
		if ($email_list) {
			setOptionDefault('contactform_mailaddress', substr($email_list, 1));
		}
		setOptionDefault('contactform_dataconfirmation', 0);
		setOptionDefault('contactform_autocomplete', 0);
	}

	function getOptionsSupported() {
		global $_zp_captcha;
		$mailinglist = explode(';', getOption("contactform_mailaddress"));
		array_walk($mailinglist, 'contactformOptions::trim_value');
		setOption('contactform_mailaddress', implode(';', $mailinglist));
		$list = array(
				gettext("required") => "required",
				gettext("show") => "show",
				gettext("omitted") => "omitted"
		);
		$mailfieldinstruction = gettext("Set if the <code>%s</code> field should be required, just shown or omitted");
		$options = array(
				gettext('Intro text') => array(
						'key' => 'contactform_introtext',
						'type' => OPTION_TYPE_TEXTAREA,
						'desc' => gettext("The intro text for your contact form")),
				gettext('Confirm text') => array(
						'key' => 'contactform_confirmtext',
						'type' => OPTION_TYPE_TEXTAREA,
						'desc' => gettext("The text that asks the visitor to confirm that he really wants to send the message.")),
				gettext('Thanks text') => array(
						'key' => 'contactform_thankstext',
						'type' => OPTION_TYPE_TEXTAREA,
						'desc' => gettext("The text that is shown after a message has been confirmed and sent.")),
				gettext('New message link text') => array(
						'key' => 'contactform_newmessagelink',
						'type' => OPTION_TYPE_TEXTAREA,
						'desc' => gettext("The text for the link after the thanks text to return to the contact page to send another message.")),
				gettext('Require confirmation') => array(
						'key' => 'contactform_confirm',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext("If checked, a confirmation form will be presented before sending the contact message.")),
				gettext('Send copy') => array(
						'key' => 'contactform_sendcopy',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext("If checked, a copy of the message will be sent to the address provided. <p class='notebox'><strong>Caution: </strong> If you check this option it is strongly recommend to use Captcha and the confirmation option. Be aware that someone could misuse the e-mail address entered for spamming with this form and that in some countries’ jurisdictions(e.g. most European countries) you may be made responsible for this then!</p>")),
				gettext('Send copy note text') => array(
						'key' => 'contactform_sendcopy_text',
						'type' => OPTION_TYPE_TEXTAREA,
						'desc' => gettext("The text for the note about sending a copy to the address provided in case that option is set.")),
				gettext('Contact recipients') => array(
						'key' => 'contactform_mailaddress',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("The e-mail address the messages should be sent to. Enter one or more address separated by semicolons.")),
				gettext('Title') => array(
						'key' => 'contactform_title',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => $list,
						'desc' => sprintf($mailfieldinstruction, gettext("Title"))),
				gettext('Name') => array(
						'key' => 'contactform_name',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => $list,
						'desc' => sprintf($mailfieldinstruction, gettext("Name"))),
				gettext('Company') => array(
						'key' => 'contactform_company',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => $list,
						'desc' => sprintf($mailfieldinstruction, gettext("Company"))),
				gettext('Street') => array(
						'key' => 'contactform_street',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => $list,
						'desc' => sprintf($mailfieldinstruction, gettext("Street"))),
				gettext('City') => array(
						'key' => 'contactform_city',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => $list,
						'desc' => sprintf($mailfieldinstruction, gettext("City"))),
				gettext('State') => array(
						'key' => 'contactform_state',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => $list,
						'desc' => sprintf($mailfieldinstruction, gettext("State"))),
				gettext('Postal code') => array(
						'key' => 'contactform_postal',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => $list,
						'desc' => sprintf($mailfieldinstruction, gettext("Postal code"))),
				gettext('Country') => array(
						'key' => 'contactform_country',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => $list,
						'desc' => sprintf($mailfieldinstruction, gettext("Country"))),
				gettext('E-mail') => array(
						'key' => 'contactform_email',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => $list,
						'desc' => sprintf($mailfieldinstruction, gettext("E-mail"))),
				gettext('Website') => array(
						'key' => 'contactform_website',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => $list,
						'desc' => sprintf($mailfieldinstruction, gettext("Website"))),
				gettext('CAPTCHA') => array(
						'key' => 'contactform_captcha',
						'type' => OPTION_TYPE_CHECKBOX,
						'disabled' => ($_zp_captcha->name) ? false : true,
						'desc' => ($_zp_captcha->name) ? gettext('If checked, the form will include a Captcha verification.') : '<span class="warningbox">' . gettext('No captcha handler is enabled.') . '</span>'),
				gettext('Phone') => array(
						'key' => 'contactform_phone',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => $list,
						'desc' => sprintf($mailfieldinstruction, gettext("Phone number"))),
				gettext('Data usage confirmation') => array(
						'key' => 'contactform_dataconfirmation',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('If checked a mandatory checkbox is added for users to confirm about data storage and handling by your site. This is recommend to comply with the European GDPR.')),
				gettext('Autocomplete') => array(
						'key' => 'contactform_autocomplete',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('If checked the form allows autocompletion by the browser. Note that this may be of privacy concerns.'))
		);
		return $options;
	}

	/**
	 *
	 * Used in array_walk to trim the e-mail addresses
	 * @param string $value
	 */
	static function trim_value(&$value) {
		$value = trim($value);
	}

}

/**
 * The plugin class
 * @since 1.6.3 Procedural functions moved to class
 */
class contactForm {
	
	private static $processing_post = false;

	/**
	 * Retrieves the post field if it exists
	 *
	 * @param string $field
	 * @param int $level
	 * @return string
	 */
	static function getField($field, $level = 3) {
		if (isset($_POST[$field])) {
			return sanitize($_POST[$field], $level);
		} else {
			return '';
		}
	}

	/**
	 * Prints the mail contact form, handles checks and the mail sending. It uses Zenphoto's check for valid e-mail address and website URL and also supports CAPTCHA.
	 * The contact form itself is a separate file and is located within the /contact_form/form.php so that it can be style as needed.
	 *
	 * @param string $subject_override set to override the subject.
	 */
	static function printContactForm($subject_override = '') {
		global $_zp_utf8, $_zp_captcha, $_processing_post, $_zp_current_admin_obj;
		$error = array();
		$error_dataconfirmation = null;
		if (isset($_POST['sendmail'])) {
			$mailcontent = array();
			$mailcontent['title'] = self::getField('title');
			$mailcontent['name'] = self::getField('name');
			$mailcontent['honeypot'] = self::getField('username');
			$mailcontent['company'] = self::getField('company');
			$mailcontent['street'] = self::getField('street');
			$mailcontent['city'] = self::getField('city');
			$mailcontent['state'] = self::getField('state');
			$mailcontent['postal'] = self::getField('postal');
			$mailcontent['country'] = self::getField('country');
			$mailcontent['email'] = self::getField('email');
			$mailcontent['website'] = self::getField('website');
			$mailcontent['phone'] = self::getField('phone');
			$mailcontent['subject'] = self::getField('subject');
			$mailcontent['message'] = self::getField('message', 1);
			$mailcontent['dataconfirmation'] = self::getField('dataconfirmation', 1);

			// if you want other required fields or less add/modify their checks here
			if (getOption('contactform_title') == "required" && empty($mailcontent['title'])) {
				$error[1] = gettext("a title");
			}
			if (getOption('contactform_name') == "required" && empty($mailcontent['name'])) {
				$error[2] = gettext("a name");
			}
			if (getOption('contactform_company') == "required" && empty($mailcontent['company'])) {
				$error[3] = gettext("a company");
			}
			if (getOption('contactform_street') == "required" && empty($mailcontent['street'])) {
				$error[4] = gettext("a street");
			}
			if (getOption('contactform_city') == "required" && empty($mailcontent['city'])) {
				$error[5] = gettext("a city");
			}
			if (getOption('contactform_state') == "required" && empty($mailcontent['state'])) {
				$error[6] = gettext("a state");
			}
			if (getOption('contactform_country') == "required" && empty($mailcontent['country'])) {
				$error[7] = gettext("a country");
			}
			if (getOption('contactform_postal') == "required" && empty($mailcontent['postal'])) {
				$error[8] = gettext("a postal code");
			}
			if (getOption('contactform_email') == "required" && (empty($mailcontent['email']) || !isValidEmail($mailcontent['email']))) {
				$error[9] = gettext("a valid email address");
			}
			if (getOption('contactform_website') == "required" && empty($mailcontent['website'])) {
				$error[10] = gettext('a website');
			} else {
				if (!empty($mailcontent['website'])) {
					if (substr($mailcontent['website'], 0, 7) != "http://" || substr($mailcontent['website'], 0, 8) != "https://") {
						$mailcontent['website'] = "http://" . $mailcontent['website'];
					}
				}
			}
			if (getOption("contactform_phone") == "required" && empty($mailcontent['phone'])) {
				$error[11] = gettext("a phone number");
			}
			if (empty($mailcontent['subject'])) {
				$error[12] = gettext("a subject");
			}
			if (empty($mailcontent['message'])) {
				$error[13] = gettext("a message");
			}
			// CAPTCHA start
			if ($_zp_captcha->name && getOption("contactform_captcha")) {
				$code_ok = trim(sanitize(isset($_POST['code_h']) ? $_POST['code_h'] : NULL));
				$code = trim(sanitize(isset($_POST['code']) ? $_POST['code'] : NULL));
				if (!$_zp_captcha->checkCaptcha($code, $code_ok)) {
					$error[14] = gettext("the correct CAPTCHA verification code");
				} // no ticket
			}
			// CAPTCHA end
			if (getOption('contactform_dataconfirmation') && empty($mailcontent['dataconfirmation'])) {
				$error_dataconfirmation = $error[15] = gettext('Please agree to storage and handling of your data by this website.');
			}
			// If required fields are empty or not valide print note
			if (count($error) != 0) {
				?>
				<div class="errorbox">
					<?php
					$err = $error;
					if ($error_dataconfirmation) {
						echo '<p>' . $error_dataconfirmation . '</p>';
						// remove data confirmation error so we re-print it with the wrong generic text below
						unset($err[15]);
					}
					switch (count($err)) {
						case 1:
							printf(gettext('Please enter %s. Thanks.'), array_shift($err));
							break;
						case 2:
							printf(gettext('Please enter %1$s and %2$s. Thanks.'), array_shift($err), array_shift($err));
							break;
						default:
							if (!empty($err)) { // no data confirmation may result in this although there is one error
								$list = '<ul class="errorlist">';
								foreach ($err as $item) {
									$list .= '<li>' . $item . '</li>';
								}
								$list .= '</ul>';
								printf(gettext('Please enter: %sThanks.'), $list);
							}
							break;
					}
					?>
				</div>
				<?php
			} else {
				$mailaddress = $mailcontent['email'];
				$name = $mailcontent['name'];
				$subject = $mailcontent['subject'] . " (" . getBareGalleryTitle() . ")";
				$message = '';
				if (!empty($mailcontent['title'])) {
					$message .= $mailcontent['title'] . "\n";
				}
				if (!empty($mailcontent['name'])) {
					$message .= $mailcontent['name'] . "\n";
				}
				if (!empty($mailcontent['email'])) {
					$message .= $mailcontent['email'] . "\n";
				}
				if (!empty($mailcontent['company'])) {
					$message .= $mailcontent['company'] . "\n";
				}
				if (!empty($mailcontent['street'])) {
					$message .= $mailcontent['street'] . "\n";
				}
				if (!empty($mailcontent['city'])) {
					$message .= $mailcontent['city'] . "\n";
				}
				if (!empty($mailcontent['state'])) {
					$message .= $mailcontent['state'] . "\n";
				}
				if (!empty($mailcontent['postal'])) {
					$message .= $mailcontent['postal'] . "\n";
				}
				if (!empty($mailcontent['country'])) {
					$message .= $mailcontent['country'] . "\n";
				}
				if (!empty($mailcontent['phone'])) {
					$message .= $mailcontent['phone'] . "\n";
				}
				if (!empty($mailcontent['website'])) {
					$message .= $mailcontent['website'] . "\n";
				}
				$message .= "\n\n" . $mailcontent['message'];
				if (!empty($mailcontent['dataconfirmation'])) {
					$message .= "\n\n" . gettext('I agree to storage and handling of my data by this website.') . "\n";
				}
				$message .= "\n\n";

				if (getOption('contactform_confirm')) {
					echo get_language_string(getOption("contactform_confirmtext"));
					if (getOption('contactform_sendcopy')) {
						echo get_language_string(getOption("contactform_sendcopy_text"));
					}
					?>
					<div>
						<?PHP
						self::$processing_post = $_processing_post = true;
						include(getPlugin('contact_form/form.php', true));
						$message = str_replace("\n", '<br>', $message);
						?>
						<form id="confirm" action="<?php echo html_encode(getRequestURI()); ?>" method="post" accept-charset="UTF-8" style="float: left">
							<input type="hidden" id="confirm" name="confirm" value="confirm" />
							<input type="hidden" id="name" name="name"	value="<?php echo html_encode($name); ?>" />
							<input type="hidden" id="subject" name="subject"	value="<?php echo html_encode($subject); ?>" />
							<input type="hidden" id="message"	name="message" value="<?php echo html_encode($message); ?>" />
							<input type="hidden" id="mailaddress" name="mailaddress" value="<?php echo html_encode($mailaddress); ?>" />
							<input type="text" id="username" name="username" value="<?php echo html_encode($mailcontent['honeypot']); ?>" style="display: none" />
							<input type="submit" value="<?php echo gettext("Confirm"); ?>" />
						</form>
						<form id="discard" action="<?php echo html_encode(getRequestURI()); ?>" method="post" accept-charset="UTF-8">
							<input type="hidden" id="discard" name="discard" value="discard" />
							<input type="submit" value="<?php echo gettext("Discard"); ?>" />
						</form>
					</div>
					<?php
					return;
				} else {
					// simulate confirmation action
					$_POST['confirm'] = true;
					$_POST['subject'] = $subject;
					$_POST['message'] = $message;
					$_POST['mailaddress'] = $mailaddress;
					$_POST['name'] = $name;
				}
			}
		}
		if (isset($_POST['confirm'])) {
			$subject = sanitize($_POST['subject']);
			$message = str_replace('<br>', "\n", sanitize($_POST['message'], 1));
			$mailaddress = sanitize($_POST['mailaddress']);
			$honeypot = sanitize($_POST['username']);
			$name = sanitize($_POST['name']);
			$mailinglist = explode(';', getOption("contactform_mailaddress"));
			if (getOption('contactform_sendcopy')) {
				$sendcopy = array($name => $mailaddress);
			} else {
				$sendcopy = NULL;
			}
			// If honeypot was triggered, silently don't send the message
			$err_msg = false;
			if (empty($honeypot)) {
				$err_msg = zp_mail($subject, $message, $mailinglist, $sendcopy, NULL, array($name => $mailaddress));
			}
			if ($err_msg) {
				$msgs = explode('. ', $err_msg);
				foreach ($msgs as $key => $line) {
					if (empty($line) || $line == gettext('Mail send failed') || strpos($line, 'github')) {
						unset($msgs[$key]);
					}
				}
				?>
				<div class="errorbox">
					<strong><?php echo ngettext('Error sending mail:', 'Errors sending mail:', count($msgs)); ?></strong>
					<ul class="errorlist">
						<?php
						foreach ($msgs as $line) {
							echo '<li>' . trim($line) . '</li>';
						}
						?>
					</ul>
				</div>
				<?php
			} else {
				echo get_language_string(getOption("contactform_thankstext"));
			}
			echo '<p><a  href="?again">' . get_language_string(getOption('contactform_newmessagelink')) . '</a></p>';
		} else {
			if (count($error) <= 0) {
				if (zp_loggedin()) {
					$mailcontent = array(
							'title' => '',
							'name' => $_zp_current_admin_obj->getName(),
							'company' => '',
							'street' => '',
							'city' => '',
							'state' => '',
							'country' => '',
							'postal' => '',
							'email' => $_zp_current_admin_obj->getEmail(),
							'website' => '', 'phone' => '',
							'subject' => $subject_override,
							'message' => '', 'honeypot' => '');
					if (extensionEnabled('comment_form')) {
						$address = getSerializedArray($_zp_current_admin_obj->getCustomData());
						foreach ($address as $key => $field) {
							$mailcontent[$key] = $field;
						}
					}
				} else {
					$mailcontent = array(
							'title' => '',
							'name' => '',
							'company' => '',
							'street' => '',
							'city' => '',
							'state' => '',
							'country' => '',
							'email' => '',
							'postal' => '',
							'website' => '',
							'phone' => '',
							'subject' => $subject_override,
							'message' => '',
							'honeypot' => '');
				}
			}
			echo get_language_string(getOption("contactform_introtext"));
			if (getOption('contactform_sendcopy')) {
				echo get_language_string(getOption("contactform_sendcopy_text"));
			}
			self::$processing_post = $_processing_post = false;
			include(getPlugin('contact_form/form.php', true));
		}
	}
	
	/**
	 * Returns true if the form is being processed.
	 * @since 1.6.3
	 * 
	 * @return bool
	 */
	static function isProcessingPost() {
		if (self::$processing_post) {
			return true;
		}
		return false;
	}
	
	/**
	 * Helper function that checks if a field should be shown ("required" or "show") or omitted ("ommitt").
	 * Only for the fields set by radioboxes.
	 *
	 * @param string $option The field option name or the field option value (legacy)
	 * @return bool
	 */
	static function isVisibleField($option) {
		$optionvalue = self::getFieldVisiblilityOptionValue($option);
		return $optionvalue == "required" || $optionvalue == "show";
	}

	/**
	 * Helper function that returns '*" to be appended to the field name as an indicator for required fields
	 * Not for the CAPTCHA field that is always required if shown...
	 *
	 * @param string $option The field option name or the field option value (legacy)
	 * @return string
	 */
	static function getRequiredFieldMark($option) {
		if (self::isRequiredField($option)) {
			return "<strong>*</strong>";
		} else {
			return '';
		}
	}
	
	/**
	 * Checks if a field is a required one
	 * 
	 * @since 1.6.3
	 * 
	 * @global bool $_processing_post
	 * @param string $option The field option name or the field option value (legacy)
	 * @return bool
	 */
	static function isRequiredField($option) {
		$optionvalue = self::getFieldVisiblilityOptionValue($option);
		if ($optionvalue == "required" && !self::isProcessingPost()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns the required element attribute if the field is required
	 * 
	 * @since 1.6.3
	 * 
	 * @param string $option The field option name or the field option value (legacy)
	 * @return string
	 */
	static function getRequiredAttr($option) {
		if (self::isRequiredField($option)) {
			return ' required';
		}
		return '';
	}
	
	/**
	 * Returns the disabled attribute if the field is being processed
	 * 
	 * @since 1.6.3
	 * 
	 * @param string $option The field option name or the field option value (legacy)
	 * @return string
	 */
	static function getProcessedFieldDisabledAttr() {
		if (self::isProcessingPost()) {
			return ' disabled'; 
		}
		return '';
	}

	/**
	 * Returns the autocomplete attribute for the form depending if autocomplete is enabled
	 *  
	 * @since 1.6.3
	 * 
	 * @return string
	 */
	static function getFormAutocompleteAttr() {
		if (getOption('contactform_autocomplete')) {
			return self::getAutocompleteAttr('on');
		}
		return self::getAutocompleteAttr();
	}

	/**
	 * Gets the autocomplete attribute with the value passed if autocomplete is enabled
	 * Note that the value is not validated. See See e.g. https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/autocomplete for valid values and tokens
	 *  
	 * @since 1.6.3
	 * 
	 * @param string $value Default "on" if autocomplete is ebabled
	 * @param bool $skip_off Set to true to skip returing autocomplete="off"
	 * @return string
	 */
	static function getAutocompleteAttr($value = 'on', $skip_off = false) {
		if (getOption('contactform_autocomplete')) {
			return ' autocomplete="' . sanitize($value) . '"';
		}
		if (!$skip_off) {
			return ' autocomplete="off"';
		}
		return '';
	}

	/**
	 * Prints the autocomplete attribute with the value passed if autocomplete is enabled
	 * Note that the value is not validated. See See e.g. https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/autocomplete for valid values and tokens
	 *  
	 * @since 1.6.3
	 * 
	 * @param string $value Default "on" if autocomplete is ebabled
	 * @param bool $skip_off Set to true to skip printing autocomplete="off"
	 */
	static function printAutocompleteAttr($value = "on", $skip_off = false) {
		echo self::getAutocompleteAttr($value, $skip_off);
	}

	/**
	 * Wrapper for printing the disabled and required attributes as needed
	 * @param string $option The field option name or the field option value (legacy)
	 * @param string $autocomplete_value Default "on" if autocomplete is ebabled
	 * 
	 * @since 1.6.3
	 */
	static function printAttributes($option, $autocomplete_value = 'on') {
		echo self::getProcessedFieldDisabledAttr();
		echo self::getRequiredAttr($option);
		self::printAutocompleteAttr($autocomplete_value, true);
	}

	/**
	 * Compatibility helper for parameters that formerly required the field visibility option values to be passed via e.g. getOption('contactform_title');
	 * 
	 * @since 1.6.3
	 * 
	 * @param string $value The field option name or the field option value (legacy)
	 * @return string
	 */
	static function getFieldVisiblilityOptionValue($value) {
		if (in_array($value, array('required','show', 'omitted'))) { // old way 
			return $value;
		} else {
			return getOption($value);
		}
	}

	/**
	 * Buffers the contact form print out so it can be passed to its content macro
	 * @param type $subject_override
	 * @return type
	 */
	static function printMacro($subject_override = '') {
		ob_start();
		self::printContactForm($subject_override);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Registers the content macro(s)
	 * 
	 * @param array $macros Passes through the array of already registered 
	 * @return array
	 */
	static function getMacros($macros) {
		$macros['CONTACTFORM'] = array(
				'class' => 'function',
				'params' => array('string*'),
				'value' => 'self::printMacro',
				'owner' => 'contact_form',
				'desc' => gettext('Set %1 to optionally override the subject.')
		);
		return $macros;
	}

}

/**
 * Retrieves the post field if it exists
 * 
 * @deprecated 1.6.3 – Use contactForm::getField() instead
 *
 * @param string $field
 * @param int $level
 * @return string
 */
function getField($field, $level = 3) {
	deprecationNotice(gettext('Use contactForm::getField() instead'));
	return contactForm::getField($field, $level);
}

/**
 * Prints the mail contact form, handles checks and the mail sending. It uses Zenphoto's check for valid e-mail address and website URL and also supports CAPTCHA.
 * The contact form itself is a separate file and is located within the /contact_form/form.php so that it can be style as needed.
 * 
 * @deprecated 1.6.3 – Use contactForm::printContactForm() instead
 *
 * @param string $subject_override set to override the subject.
 */
function printContactForm($subject_override = '') {
	deprecationNotice(gettext('Use contactForm::printContactForm() instead'));
	contactForm::printContactForm($subject_override);
}

/**

 * Helper function that checks if a field should be shown ("required" or "show") or omitted ("ommitt").
 * Only for the fields set by radioboxes.
 * 
 * @deprecated 1.6.3 – Use contactForm::isVisibleField() instead
 *
 * @param string $option The option value
 * @return bool
 */
function showOrNotShowField($option) {
	deprecationNotice(gettext('Use contactForm::isVisibleField() instead.'));
	return contactForm::isVisibleField($option);
}

/**
 * Helper function that checks if the field is a required one. If it returns '*" to be appended to the field name as an indicator.
 * Not for the CAPTCHA field that is always required if shown...
 * 
 * @deprecated 2.0 – Use contactForm::getRequiredFieldMark() instead
 *
 * @param string $option the option value
 * @return string
 */
function checkRequiredField($option) {
	deprecationNotice(gettext('Use contactForm::getRequiredFieldMark() instead'));
	return contactForm::checkRequiredField($option);
}

/**
 * Buffers the contact form print out so it can be passed to its content macro
 * 
 * @deprecated 2.0 – Use contactForm::printMacro() instead
 * 
 * @param type $subject_override
 * @return type
 */
function printContactFormMacro($subject_override = '') {
	deprecationNotice(gettext('Use contactForm::printMacro() instead'));
	return contactForm::printMacro($subject_override);
}

/**
 * Registers the content macro(s)
 * 
 * @deprecated 2.0 – Use contactForm::getMacros() instead
 * 
 * @param array $macros Passes through the array of already registered 
 * @return array
 */
function getContactFormMacros($macros) {
	deprecationNotice(gettext('Use contactForm::getMacros() instead'));
	return contactForm::getMacros($macros);
}