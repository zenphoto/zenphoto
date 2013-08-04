<?php
/**
 * Prints an e-mail contact form that uses Zenphoto's internal validation functions for <i>e-mail</i> and <i>URL</i>.
 * <i>Name</i>, <i>e-mail address</i>, <i>subject</i> and <i>message</i> are required fields by default.
 * You need to set a custom mail address to be used for the messages destination.
 *
 * Support is included <i>CAPTCHA</i> and for confirmation before the message is sent. No other spam filter support is provided.
 * Your mail client will provide filtering on receipt of the message.
 *
 * The contact form itself is a separate file and located within <var>%ZENFOLDER%/%PLUGIN_FOLDER%/contact_form/form.php</var>. Place a customized
 * version of the form in a similar folder in your theme if you wish something different from the standard form.
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_is_filter = 5 | FEATURE_PLUGIN;
$plugin_description = gettext("Prints an e-mail contact so that visitors may e-mail the site administrator.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";

$option_interface = 'contactformOptions';

if (!OFFSET_PATH) {
	$_zp_conf_vars['special_pages']['contact'] = array('define'	 => false, 'rewrite'	 => getOption('contactform_rewrite'), 'rule'		 => '^%REWRITE%/*$		index.php?p=contact [L,QSA]');
}

/**
 * Plugin option handling class
 *
 */
class contactformOptions {

	function contactformOptions() {
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
	}

	function getOptionsSupported() {
		global $_zp_captcha;
		$mailinglist = explode(';', getOption("contactform_mailaddress"));
		array_walk($mailinglist, 'contactformOptions::trim_value');
		setOption('contactform_mailaddress', implode(';', $mailinglist));
		$list = array(gettext("required")	 => "required", gettext("show")			 => "show", gettext("omitted")	 => "omitted");
		$mailfieldinstruction = gettext("Set if the <code>%s</code> field should be required, just shown or omitted");
		$options = array(
						gettext('Contact page link')		 => array('key'		 => 'contactform_rewrite', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 0,
										'desc'	 => gettext('The link to use for the contact page. Note: the token <code>_PAGE_</code> stands in for the current <em>page</em> definition.')),
						gettext('Intro text')						 => array('key'		 => 'contactform_introtext', 'type'	 => OPTION_TYPE_TEXTAREA,
										'order'	 => 13,
										'desc'	 => gettext("The intro text for your contact form")),
						gettext('Confirm text')					 => array('key'		 => 'contactform_confirmtext', 'type'	 => OPTION_TYPE_TEXTAREA,
										'order'	 => 14,
										'desc'	 => gettext("The text that asks the visitor to confirm that he really wants to send the message.")),
						gettext('Thanks text')					 => array('key'		 => 'contactform_thankstext', 'type'	 => OPTION_TYPE_TEXTAREA,
										'order'	 => 15,
										'desc'	 => gettext("The text that is shown after a message has been confirmed and sent.")),
						gettext('New message link text') => array('key'		 => 'contactform_newmessagelink', 'type'	 => OPTION_TYPE_TEXTAREA,
										'order'	 => 16,
										'desc'	 => gettext("The text for the link after the thanks text to return to the contact page to send another message.")),
						gettext('Require confirmation')	 => array('key'		 => 'contactform_confirm', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 0.1,
										'desc'	 => gettext("If checked, a confirmation form will be presented before sending the contact message.")),
						gettext('Send copy')						 => array('key'		 => 'contactform_sendcopy', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 0.3,
										'desc'	 => gettext("If checked, a copy of the message will be sent to the address provided. <p class='notebox'><strong>Caution: </strong> If you check this option it is strongly recommend to use Captcha and the confirmation option. Be aware that someone could misuse the e-mail address entered for spamming with this form and that in some countries' jurisdictions (e.g. most European countries) you may be made responsible for this then!</p>")),
						gettext('Send copy note text')	 => array('key'		 => 'contactform_sendcopy_text', 'type'	 => OPTION_TYPE_TEXTAREA,
										'order'	 => 0.2,
										'desc'	 => gettext("The text for the note about sending a copy to the address provided in case that option is set.")),
						gettext('Contact recipients')		 => array('key'		 => 'contactform_mailaddress', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 17,
										'desc'	 => gettext("The e-mail address the messages should be sent to. Enter one or more address separated by semicolons.")),
						gettext('Title')								 => array('key'			 => 'contactform_title', 'type'		 => OPTION_TYPE_RADIO, 'buttons'	 => $list,
										'order'		 => 1,
										'desc'		 => sprintf($mailfieldinstruction, gettext("Title"))),
						gettext('Name')									 => array('key'			 => 'contactform_name', 'type'		 => OPTION_TYPE_RADIO, 'buttons'	 => $list,
										'order'		 => 2,
										'desc'		 => sprintf($mailfieldinstruction, gettext("Name"))),
						gettext('Company')							 => array('key'			 => 'contactform_company', 'type'		 => OPTION_TYPE_RADIO, 'buttons'	 => $list,
										'order'		 => 3,
										'desc'		 => sprintf($mailfieldinstruction, gettext("Company"))),
						gettext('Street')								 => array('key'			 => 'contactform_street', 'type'		 => OPTION_TYPE_RADIO, 'buttons'	 => $list,
										'order'		 => 4,
										'desc'		 => sprintf($mailfieldinstruction, gettext("Street"))),
						gettext('City')									 => array('key'			 => 'contactform_city', 'type'		 => OPTION_TYPE_RADIO, 'buttons'	 => $list,
										'order'		 => 5,
										'desc'		 => sprintf($mailfieldinstruction, gettext("City"))),
						gettext('State')								 => array('key'			 => 'contactform_state', 'type'		 => OPTION_TYPE_RADIO, 'buttons'	 => $list,
										'order'		 => 5.1,
										'desc'		 => sprintf($mailfieldinstruction, gettext("State"))),
						gettext('Postal code')					 => array('key'			 => 'contactform_postal', 'type'		 => OPTION_TYPE_RADIO, 'buttons'	 => $list,
										'order'		 => 5.2,
										'desc'		 => sprintf($mailfieldinstruction, gettext("Postal code"))),
						gettext('Country')							 => array('key'			 => 'contactform_country', 'type'		 => OPTION_TYPE_RADIO, 'buttons'	 => $list,
										'order'		 => 6,
										'desc'		 => sprintf($mailfieldinstruction, gettext("Country"))),
						gettext('E-mail')								 => array('key'			 => 'contactform_email', 'type'		 => OPTION_TYPE_RADIO, 'buttons'	 => $list,
										'order'		 => 7,
										'desc'		 => sprintf($mailfieldinstruction, gettext("E-mail"))),
						gettext('Website')							 => array('key'			 => 'contactform_website', 'type'		 => OPTION_TYPE_RADIO, 'buttons'	 => $list,
										'order'		 => 8,
										'desc'		 => sprintf($mailfieldinstruction, gettext("Website"))),
						gettext('CAPTCHA')							 => array('key'		 => 'contactform_captcha', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 9,
										'desc'	 => ($_zp_captcha->name) ? gettext('If checked, the form will include a Captcha verification.') : '<span class="notebox">' . gettext('No captcha handler is enabled.') . '</span>'),
						gettext('Phone')								 => array('key'			 => 'contactform_phone', 'type'		 => OPTION_TYPE_RADIO, 'buttons'	 => $list,
										'order'		 => 10,
										'desc'		 => sprintf($mailfieldinstruction, gettext("Phone number")))
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
 * Retrieves the post field if it exists
 *
 * @param string $field
 * @param int $level
 * @return string
 */
function getField($field, $level = 3) {
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
function printContactForm($subject_override = '') {
	global $_zp_UTF8, $_zp_captcha, $_processing_post, $_zp_current_admin_obj;
	$error = array();
	if (isset($_POST['sendmail'])) {
		$mailcontent = array();
		$mailcontent['title'] = getField('title');
		$mailcontent['name'] = getField('name');
		$mailcontent['honeypot'] = getField('username');
		$mailcontent['company'] = getField('company');
		$mailcontent['street'] = getField('street');
		$mailcontent['city'] = getField('city');
		$mailcontent['state'] = getField('state');
		$mailcontent['postal'] = getField('postal');
		$mailcontent['country'] = getField('country');
		$mailcontent['email'] = getField('email');
		$mailcontent['website'] = getField('website');
		$mailcontent['phone'] = getField('phone');
		$mailcontent['subject'] = getField('subject');
		$mailcontent['message'] = getField('message', 1);

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
			$error[5] = gettext("a state");
		}
		if (getOption('contactform_postal') == "required" && empty($mailcontent['postal'])) {
			$error[5] = gettext("a postal code");
		}
		if (getOption('contactform_country') == "required" && empty($mailcontent['country'])) {
			$error[6] = gettext("a country");
		}
		if (getOption('contactform_email') == "required" && (empty($mailcontent['email']) || !is_valid_email_zp($mailcontent['email']))) {
			$error[7] = gettext("a valid email address");
		}
		if (getOption('contactform_website') == "required" && empty($mailcontent['website'])) {
			$error[8] = gettext('a website');
		} else {
			if (!empty($mailcontent['website'])) {
				if (substr($mailcontent['website'], 0, 7) != "http://") {
					$mailcontent['website'] = "http://" . $mailcontent['website'];
				}
			}
		}
		if (getOption("contactform_phone") == "required" && empty($mailcontent['phone'])) {
			$error[9] = gettext("a phone number");
		}
		if (empty($mailcontent['subject'])) {
			$error[10] = gettext("a subject");
		}
		if (empty($mailcontent['message'])) {
			$error[11] = gettext("a message");
		}

		// CAPTCHA start
		if (getOption("contactform_captcha")) {
			$code_ok = trim(sanitize(isset($_POST['code_h']) ? $_POST['code_h'] : NULL));
			$code = trim(sanitize(isset($_POST['code']) ? $_POST['code'] : NULL));
			if (!$_zp_captcha->checkCaptcha($code, $code_ok)) {
				$error[5] = gettext("the correct CAPTCHA verification code");
			} // no ticket
		}
		// CAPTCHA end
		// If required fields are empty or not valide print note
		if (count($error) != 0) {
			?>
			<div class="errorbox">
				<h2>
					<?php
					$err = $error;
					switch (count($err)) {
						case 1:
							printf(gettext('Please enter %s. Thanks.'), array_shift($err));
							break;
						case 2:
							printf(gettext('Please enter %1$s and %2$s. Thanks.'), array_shift($err), array_shift($err));
							break;
						default:
							$list = '<ul class="errorlist">';
							foreach ($err as $item) {
								$list .= '<li>' . $item . '</li>';
							}
							$list .= '</ul>';
							printf(gettext('Please enter: %sThanks.'), $list);
							break;
					}
					?>
				</h2>
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
			$message .= "\n\n";

			if (getOption('contactform_confirm')) {
				echo get_language_string(getOption("contactform_confirmtext"));
				if (getOption('contactform_sendcopy'))
					echo get_language_string(getOption("contactform_sendcopy_text"));
				?>
				<div>
					<?PHP
					$_processing_post = true;
					include(getPlugin('contact_form/form.php', true));
					?>
					<form id="confirm" action="<?php echo html_encode(getRequestURI()); ?>" method="post" accept-charset="UTF-8" style="float: left">
						<input type="hidden" id="confirm" name="confirm" value="confirm" />
						<input type="hidden" id="name" name="name"	value="<?php echo html_encode($name); ?>" />
						<input type="hidden" id="subject" name="subject"	value="<?php echo html_encode($subject); ?>" />
						<input type="hidden" id="message"	name="message" value="<?php echo html_encode($message); ?>" />
						<input type="hidden" id="mailaddress" name="mailaddress" value="<?php echo html_encode($mailaddress); ?>" />
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
		$message = sanitize($_POST['message'], 1);
		$mailaddress = sanitize($_POST['mailaddress']);
		$name = sanitize($_POST['name']);
		$mailinglist = explode(';', getOption("contactform_mailaddress"));
		if (getOption('contactform_sendcopy')) {
			$sendcopy = array($name => $mailaddress);
		} else {
			$sendcopy = NULL;
		}

		// If honeypot was triggered, silently don't send the message
		if (empty($mailcontent['honeypot'])) {
			$err_msg = zp_mail($subject, $message, $mailinglist, $sendcopy, NULL, array($name => $mailaddress));
		}
		if ($err_msg) {
			$msgs = explode('.', $err_msg);
			unset($msgs[0]);	 //	the "mail send failed" text
			unset($msgs[count($msgs)]); //	a trailing empty one
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
		echo '<p><a href="?again">' . get_language_string(getOption('contactform_newmessagelink')) . '</a></p>';
	} else {
		if (count($error) <= 0) {
			if (zp_loggedin()) {
				$mailcontent = array('title'		 => '', 'name'		 => $_zp_current_admin_obj->getName(), 'company'	 => '', 'street'	 => '', 'city'		 => '', 'state'		 => '',
								'country'	 => '', 'postal'	 => '', 'email'		 => $_zp_current_admin_obj->getEmail(), 'website'	 => '', 'phone'		 => '',
								'subject'	 => $subject_override, 'message'	 => '', 'honeypot' => '');
				if (extensionEnabled('comment_form')) {
					$address = getSerializedArray($_zp_current_admin_obj->getCustomData());
					foreach ($address as $key => $field) {
						$mailcontent[$key] = $field;
					}
				}
			} else {
				$mailcontent = array('title'		 => '', 'name'		 => '', 'company'	 => '', 'street'	 => '', 'city'		 => '', 'state'		 => '', 'country'	 => '', 'email'		 => '',
								'postal'	 => '', 'website'	 => '', 'phone'		 => '', 'subject'	 => $subject_override, 'message'	 => '', 'honeypot' => '');
			}
		}
		echo get_language_string(getOption("contactform_introtext"));
		if (getOption('contactform_sendcopy'))
			echo get_language_string(getOption("contactform_sendcopy_text"));
		$_processing_post = false;
		include(getPlugin('contact_form/form.php', true));
	}
}

/**
 * Helper function that checks if a field should be shown ("required" or "show") or omitted ("ommitt").
 * Only for the fields set by radioboxes.
 *
 * @param string $option The option value
 * @return bool
 */
function showOrNotShowField($option) {
	return $option == "required" || $option == "show";
}

/**
 * Helper function that checks if the field is a required one. If it returns '*" to be appended to the field name as an indicator.
 * Not for the CAPTCHA field that is always required if shown...
 *
 * @param string $option the option value
 * @return string
 */
function checkRequiredField($option) {
	global $_processing_post;
	if ($option == "required" && !$_processing_post) {
		return "<strong>*</strong>";
	} else {
		return "";
	}
}
?>