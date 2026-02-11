<?php

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\POP3;
use PHPMailer\PHPMailer\SMTP;

/**
 * Zenphoto Mail Handler
 *
 * @author Stephen Billard (sbillard), Todd Papaioannou (lucky@luckyspin.org), adapted by Malte Müller (acrylian), Fred Sondaar (fretzl)
 *
 * @since 1.7 - core class replacing PHPMailer and zenphoto_sendmail plugins
 *
 * @package core
 * @subpackage libraries
 */
class mailHandler {

	static public $mailhandler = null;

	/**
	 * Send an mail to the mailing list. We also attempt to intercept any form injection
	 * attacks by slime ball spammers. Returns error message if send failure.
	 * 
	 * @author Todd Papaioannou (lucky@luckyspin.org)
	 * @since 1.0.0
	 * @since 1.7 Moved to mailHandler class
	 *
	 * @param string $subject  The subject of the email.
	 * @param string $message  The message contents of the email.
	 * @param array $email_list a list of email addresses to send to
	 * @param array $cc_addresses a list of addresses to send copies to.
	 * @param array $bcc_addresses a list of addresses to send blind copies to.
	 * @param string $replyTo reply-to address
	 *
	 * @return string
	 *

	 */
	static function mail($subject, $message, $email_list = NULL, $cc_addresses = NULL, $bcc_addresses = NULL, $replyTo = NULL) {
		global $_zp_authority, $_zp_utf8;
		$result = '';
		if ($replyTo) {
			$t = $replyTo;
			if (!self::isValidEmail($m = array_shift($t))) {
				if (empty($result)) {
					$result = gettext('Mail send failed.');
				}
				$result .= sprintf(gettext('Invalid “reply-to” mail address %s.'), $m);
			}
		}
		if (is_null($email_list)) {
			$email_list = $_zp_authority->getAdminEmail();
		} else {
			foreach ($email_list as $key => $email) {
				if (!self::isValidEmail($email)) {
					unset($email_list[$key]);
					if (empty($result)) {
						$result = gettext('Mail send failed.');
					}
					$result .= ' ' . sprintf(gettext('Invalid “to” mail address %s.'), $email);
				}
			}
		}
		if (is_null($cc_addresses)) {
			$cc_addresses = array();
		} else {
			if (empty($email_list) && !empty($cc_addresses)) {
				if (empty($result)) {
					$result = gettext('Mail send failed.');
				}
				$result .= ' ' . gettext('“cc” list provided without “to” address list.');
				return $result;
			}
			foreach ($cc_addresses as $key => $email) {
				if (!self::isValidEmail($email)) {
					unset($cc_addresses[$key]);
					if (empty($result)) {
						$result = gettext('Mail send failed.');
					}
					$result = ' ' . sprintf(gettext('Invalid “cc” mail address %s.'), $email);
				}
			}
		}
		if (is_null($bcc_addresses)) {
			$bcc_addresses = array();
		} else {
			foreach ($bcc_addresses as $key => $email) {
				if (!self::isValidEmail($email)) {
					unset($bcc_addresses[$key]);
					if (empty($result)) {
						$result = gettext('Mail send failed.');
					}
					$result = ' ' . sprintf(gettext('Invalid “bcc” mail address %s.'), $email);
				}
			}
		}
		if (count($email_list) + count($bcc_addresses) > 0) {
			$mailhandler = mailhandler::getMailhandler();
			if ($mailhandler) {
				if (DEBUG_MAIL) {
					debuglog(sprintf(gettext('mailhandler set: %1$s'), $mailhandler));
				}
				$from_mail = getOption('site_email');
				$from_name = i18n::getLanguageString(getOption('site_email_name'));

				// Convert to UTF-8
				if (LOCAL_CHARSET != 'UTF-8') {
					$subject = $_zp_utf8->convert($subject, LOCAL_CHARSET);
					$message = $_zp_utf8->convert($message, LOCAL_CHARSET);
				}

				//	we do not support rich text
				$message = preg_replace('~<p[^>]*>~', "\n", $message); // Replace the start <p> or <p attr="">
				$message = preg_replace('~</p>~', "\n", $message); // Replace the end
				$message = preg_replace('~<br[^>]*>~', "\n", $message); // Replace <br> or <br ...>
				$message = preg_replace('~<ol[^>]*>~', "", $message); // Replace the start <ol> or <ol attr="">
				$message = preg_replace('~</ol>~', "", $message); // Replace the end
				$message = preg_replace('~<ul[^>]*>~', "", $message); // Replace the start <ul> or <ul attr="">
				$message = preg_replace('~</ul>~', "", $message); // Replace the end
				$message = preg_replace('~<li[^>]*>~', ".\t", $message); // Replace the start <li> or <li attr="">
				$message = preg_replace('~</li>~', "", $message); // Replace the end
				$message = getBare($message);
				$message = preg_replace('~\n\n\n+~', "\n\n", $message);

				// Send the mail
				if (count($email_list) > 0) {
					$result = self::handleMail('', $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses, $replyTo);
					if (DEBUG_MAIL && $result) {
						debuglog(sprintf(gettext('Issue with mailing to addresses: %1$s'), $result));
					}
				}
				if (count($bcc_addresses) > 0) {
					if (DEBUG_MAIL) {
						debuglog(sprintf(gettext('We have a bbc  set: %1$s'), implode(',', $bcc_addresses)));
					}
					foreach ($bcc_addresses as $bcc) {
						$result = self::handleMail('', array($bcc), $subject, $message, $from_mail, $from_name, array(), $replyTo);
						if (DEBUG_MAIL && $result) {
							debuglog(sprintf(gettext('Issue with mailing to bbcs: %1$s'), $result));
						}
					}
				}
			} else {
				$result = gettext('Mail send failed. There is no mail handler configured.');
			}
		} else {
			if (empty($result)) {
				$result = gettext('Mail send failed.');
			}
			$result .= ' ' . gettext('No “to” address list provided.');
		}
		if (DEBUG_MAIL && $result) {
			debuglog(sprintf(gettext('mail result: %1$s'), $result));
		}
		return $result;
	}

	/**
	 * Wrapper for the selected outgoing mailhandler within mail()
	 * 
	 * @since 1.7
	 *
	 * @param string $subject  The subject of the email.
	 * @param string $message  The message contents of the email.
	 * @param array $email_list a list of email addresses to send to
	 * @param array $cc_addresses a list of addresses to send copies to.
	 * @param array $bcc_addresses a list of addresses to send blind copies to.
	 * @param string $replyTo reply-to address
	 * 
	 * @param type $msg Additional Message text to return on success/error
	 * @param type $email_list a list of email addresses to send to
	 * @param type $subject The subject of the email.
	 * @param type $message The message contents of the email.
	 * @param type $from_mail
	 * @param type $from_name
	 * @param type $cc_addresses a list of addresses to send copies to
	 * @param type $replyTo
	 */
	static private function handleMail($msg, $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses, $replyTo) {
		$mailhandler = mailhandler::getMailhandler();
		if ($mailhandler) {
			if (self::hasDefaultMailhandler()) {
				switch ($mailhandler) {
					case 'mailHander::sendmail':
						return self::sendmail($msg, $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses, $replyTo);
					case 'mailHandler::PHPMailer':
						return self::PHPMailer($msg, $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses, $replyTo);
				}
			} else {
				// we cannot use filter::applyFilter() here as the filter registration gets lost due to file load order
				return callUserFunction($mailhandler, $msg, $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses, $replyTo);
			}
		}
	}

	/**
	 * Zenphoto outgoing mail handler based on the <em>PHPMailer</em> class mailing facility.
	 * 
	 * @author Stephen Billard (sbillard)
	 * @since 1.2.7 former PHPMailer plugin
	 * @since 1.7 moved to mailhandler class
	 *
	 * @param type $msg Additional Message text to return on success/error
	 * @param type $email_list
	 * @param type $subject
	 * @param type $message
	 * @param type $from_mail
	 * @param type $from_name
	 * @param type $cc_addresses
	 * @param type $replyTo
	 *
	 * @return type
	 */
	static private function PHPMailer($msg, $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses, $replyTo) {
		require_once SERVERPATH . '/' . ZENFOLDER . '/libs/PHPMailer/PHPMailer.php';
		require_once SERVERPATH . '/' . ZENFOLDER . '/libs/PHPMailer/POP3.php';
		require_once SERVERPATH . '/' . ZENFOLDER . '/libs/PHPMailer/SMTP.php';
		require_once SERVERPATH . '/' . ZENFOLDER . '/libs/PHPMailer/Exception.php';

		switch (getOption('PHPMailer_mail_protocol')) {
			case 'pop3':
				$pop = new POP3();
				$authorized = $pop->authorise(getOption('PHPMailer_server'), getOption('PHPMailer_pop_port'), 30, getOption('PHPMailer_user'), getOption('PHPMailer_password'), 0);
				$mail = new PHPMailer();
				$mail->isSMTP();
				$mail->Host = getOption('PHPMailer_server');
				$mail->Port = getOption('PHPMailer_smtp_port');
				break;
			case 'smtp':
				$mail = new PHPMailer();
				$mail->SMTPAuth = true; // enable SMTP authentication
				$mail->isSMTP();
				$mail->Username = getOption('PHPMailer_user');
				$mail->Password = getOption('PHPMailer_password');
				$mail->Host = getOption('PHPMailer_server');
				$mail->Port = getOption('PHPMailer_smtp_port');
				break;
			case 'sendmail':
				$mail = new PHPMailer();
				$mail->isSendmail();
				break;
		}

		switch (getOption('PHPMailer_secure')) {
			case 'ssl':
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
				break;
			case 'tls':
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
				break;
			case 0:
				$mail->SMTPSecure = '';
				break;
		}

		$mail->SMTPAutoTLS = false;
		$mail->CharSet = PHPMailer::CHARSET_UTF8;
		$mail->From = $from_mail;
		$mail->FromName = $from_name;
		$mail->Subject = $subject;
		$mail->Body = $message;
		$mail->AltBody = '';
		$mail->IsHTML(false);

		foreach ($email_list as $to_name => $to_mail) {
			if (is_numeric($to_name)) {
				$mail->addAddress($to_mail);
			} else {
				$mail->addAddress($to_mail, $to_name);
			}
		}
		if (count($cc_addresses) > 0) {
			foreach ($cc_addresses as $cc_name => $cc_mail) {
				$mail->addCC($cc_mail);
			}
		}
		if ($replyTo) {
			$names = array_keys($replyTo);
			$mail->addReplyTo(array_shift($replyTo), array_shift($names));
		}
		if (!$mail->send()) {
			if (!empty($msg)) {
				$msg .= '<br />';
			}
			$msg .= sprintf(gettext('Error info: %1$s'), $mail->ErrorInfo);
			if (DEBUG_MAIL) {
				debuglog(sprintf(gettext('phpmailer object:  %1$s'), $mail));
				debuglog(sprintf(gettext('Error info: %1$s'), $mail->ErrorInfo));
			}
		}
		return $msg;
	}

	/**
	 * PHP sendmail mailing handler
	 *
	 * @author Stephen Billard (sbillard)
	 * @since 1.2.7 former zenphoto_sendmail plugin
	 * @since 1.7 Movid to mailHandler class
	 * 
	 * @param type $msg
	 * @param type $email_list
	 * @param type $subject
	 * @param type $message
	 * @param type $from_mail
	 * @param type $from_name
	 * @param type $cc_addresses
	 * @param type $replyTo
	 *
	 * @return type
	 */
	static private function sendmail($msg, $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses, $replyTo) {
		$headers = sprintf('From: %1$s <%2$s>', $from_name, $from_mail) . "\n";
		if (count($cc_addresses) > 0) {
			$cclist = '';
			foreach ($cc_addresses as $cc_name => $cc_mail) {
				$cclist .= ',' . $cc_mail;
			}
			$headers .= 'Cc: ' . substr($cclist, 1) . "\n";
		}
		if ($replyTo) {
			$headers .= 'Reply-To: ' . array_shift($replyTo) . "\n";
		}
		$result = true;
		if (function_exists('mb_encode_mimeheader')) {
			$subject = mb_encode_mimeheader($subject);
		} else {
			$subject = UTF8::encode_mimeheader($subject);
		}
		$message_final = chunk_split(base64_encode($message));
		$additional_headers = trim($additional_headers);
		if ($additional_headers != '') {
			$additional_headers .= "\r\n";
		}
		$additional_headers .= "Mime-Version: 1.0\r\n" .
						"Content-Type: text/plain; charset=UTF-8\r\n" .
						"Content-Transfer-Encoding: base64";
		foreach ($email_list as $to_mail) {
			$failed = @mail($to_mail, $subject, $message_final, $additional_headers);
			$result = $result && $failed;
			if (DEBUG_MAIL) {
				debuglog(sprintf(gettext('sendmail failed to send %1$s to %2$s'), $subject, $to_mail));
			}
		}
		if (!$result) {
			if (!empty($msg)) {
				$msg .= '<br />';
			}
			$msg .= sprintf(gettext('<code>sendmail</code> failed to send <em>%s</em> to one or more recipients.'), $subject);
			if (DEBUG_MAIL) {
				debuglog($msg);
			}
		}
		return $msg;
	}

	/**
	 * Determines if the input is an e-mail address. Adapted from WordPress.
	 * Name changed to avoid conflicts in WP integrations.
	 * 
	 * @since 1.5.2
	 * @since 1.7 moved to mailHandler class
	 * 
	 * @param string $email email address?
	 * @return bool
	 */
	static function isValidEmail($email) {
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return true;
		}
		return false;
	}

	/**
	 * Returns the default mailhandler methods 
	 * 
	 * @since 1.7
	 * 
	 * @return array
	 */
	static function getDefaultMailhandlers() {
		return array('mailhandler::sendmail', 'mailHandler::PHPMailer');
	}

	/**
	 * Returns the name of the method/function attached as mailhandler.
	 * 
	 * @since 1.7
	 * 
	 * @return string
	 */
	static function getMailhandler() {
		// we need to cache the mailhandler as the "sendmail" filter gets lost laster due to weird load order issues
		if (!is_null(self::$mailhandler)) {
			return self::$mailhandler;
		}
		if (filter::hasFilter('sendmail')) {
			$sendmail_filter = filter::$filters['sendmail'];
			$filter = array_values(array_shift($sendmail_filter));
			if (DEBUG_MAIL) {
				debuglog(sprintf(gettext('sendmail filter set: %1$s'), $filter));
			}
			return self::$mailhandler = $filter[0]['function'];
		} else {
			$mailhandler = getOption('zpcore_mailhandler');
			if (empty($mailhandler) || !in_array($mailhandler, mailhandler::getDefaultMailhandlers())) {
				return self::$mailhandler = 'mailHandler::sendmail';
			} else {
				return self::$mailhandler = $mailhandler;
			}
		}
	}

	/**
	 * Returns true if the attached mailhandlers are the default ones and not from any plugin
	 * 
	 * @since 1.7
	 * 
	 * @return bool
	 */
	static function hasDefaultMailhandler() {
		$mailhandlers = mailhandler::getDefaultMailhandlers();
		$mailhandler = mailhandler::getMailhandler();
		if (in_array($mailhandler, $mailhandlers)) {
			return true;
		} else {
			return false;
		}
	}
}
