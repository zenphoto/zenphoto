<?php

/**
 * Option definition class for core e-mail options
 *
 * @since 1.7 Consolidated from former zenphoto_sendmail and PHPMailer plugins
 * 
 * @package admin
 * @subpackage admin-functions\options
 */
class emailOptions {

	function __construct() {
		setOptionDefault('PHPMailer_mail_protocol', 'sendmail');
		setOptionDefault('PHPMailer_server', '');
		setOptionDefault('PHPMailer_pop_port', '110');
		setOptionDefault('PHPMailer_smtp_port', '25');
		setOptionDefault('PHPMailer_user', '');
		setOptionDefault('PHPMailer_password', '');
		setOptionDefault('PHPMailer_secure', 0);
		if (getOption('PHPMailer_secure') == 1) {
			setOption('PHPMailer_secure', 'ssl');
		}
		setOptionDefault('zpcore_mailhandler', 'mailHandler::sendmail');
		if (extensionEnabled('zenphoto_sendmail')) {
			setOption('zpcore_mailhandler', 'mailHandler::sendmail');
			disableExtension('zenphoto_sendmail');
		}
		if (extensionEnabled('PHPMailer')) {
			setOption('zpcore_mailhandler', 'mailHandler::PHPMailer');
			disableExtension('PHPMailer');
		}
		setOptionDefault('zpcore_mailhandler', 'mailHandler::sendmail');
	}

	function getOptionsSupported() {
		$options = array(
				gettext('Send Emails From:') => array(
						'key' => 'site_email_name',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext("This email name as the <em>From</em> name for all mails sent by Zenphoto.")
				),
				gettext('Email:') => array(
						'key' => 'site_email',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext("This email address will be used as the <em>From</em> address for all mails sent by Zenphoto.")
				),
				gettext('E-mail handler:') => array(
						'key' => 'zpcore_mailhandler',
						'type' => OPTION_TYPE_RADIO,
						'order' => 3,
						'buttons' => array(
								'Sendmail' => 'mailHandler::sendmail',
								'PHPMailer' => 'mailHandler::PHPMailer'
						),
						'desc' => gettext("Zenphoto outgoing mail handler based on the PHP <em>mail()</em> facility or using <a href='http://sourceforge.net/projects/phpmailer/'>PHPMailer</a> classes.")
				),
				gettext('PHPMailer – Mail protocol:') => array(
						'key' => 'PHPMailer_mail_protocol',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => array(
								'POP3' => 'pop3',
								'SMTP' => 'smtp',
								'SendMail' => 'sendmail'
						),
						'desc' => gettext("Zenphoto outgoing mail handler based on the PHP <em>mail()</em> facility or using <a href='http://sourceforge.net/projects/phpmailer/'>PHPMailer</a> classes.")
				),
				gettext('PHPMailer – Outgoing mail server:') => array(
						'key' => 'PHPMailer_server',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("")
				),
				gettext('PHPMailer – POP port:') => array(
						'key' => 'PHPMailer_pop_port',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("")
				),
				gettext('PHPMailer – SMPT port:') => array(
						'key' => 'PHPMailer_smtp_port',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("")
				),
				gettext('PHPMailer – Mail user:') => array(
						'key' => 'PHPMailer_use',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("")
				),
				gettext('PHPMailer – Mail password:') => array(
						'key' => 'PHPMailer_password',
						'type' => OPTION_TYPE_PASSWORD,
						'desc' => gettext("")
				),
				gettext('PHPMailer – Secure mail:') => array(
						'key' => 'PHPMailer_secure',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => array(
								gettext('no') => 0,
								gettext('SSL') => 'ssl',
								gettext('TLS') => 'tls'
						),
						'desc' => gettext("")
				)
		);
		return $options;
	}

	function handleOption($option, $currentValue) {
		
	}

	function getOptionsDisabled() {
		
	}

	function handleOptionSave($themename, $themealbum) {
		//return "&tab=email";
	}
}
