<?php

/**
 *
 * Google reCAPTCHA version 2 handler, See {@link https://www.google.com/recaptcha/ google reCAPTCHA}
 *
 *
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @author Stephen Billard (sbillard)

 * @package plugins
 * @subpackage admin
 */
// force UTF-8 Ø

global $_zp_captcha;
$plugin_is_filter = 5 | THEME_PLUGIN;
$plugin_description = gettext("Google reCAPTCHA handler.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = ($_zp_captcha->name && $_zp_captcha->name != 'reCAPTCHA_v2') ? sprintf(gettext('Only one Captcha handler plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'), $_zp_captcha->name) : '';


$option_interface = 'reCAPTCHA_v2';

class reCAPTCHA_v2 extends _zp_captcha {

	var $name = 'reCAPTCHA_v2';

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('reCAPTCHAKey', '');
			setOptionDefault('reCAPTCHASecret', '');
			setOptionDefault('reCAPTCHATheme', 'light');
			setOptionDefault('reCAPTCHASize', 'normal');
		}
	}

	function getOptionsSupported() {
		$options = array(
				gettext('Site key') => array('key' => 'reCAPTCHAKey', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext('This is your Google <em>reCAPTCHA Site key</em>.')),
				gettext('Secret') => array('key' => 'reCAPTCHASecret', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext('This is your Google <em>reCAPTCHA Secret</em>.')),
				gettext('Theme') => array('key' => 'reCAPTCHATheme', 'type' => OPTION_TYPE_RADIO,
						'order' => 4,
						'buttons' => array(gettext('Light') => 'light', gettext('Dark') => 'dark'),
						'desc' => gettext('Select the theme your Google <em>reCAPTCHA widget</em>.')),
				gettext('Size') => array('key' => 'reCAPTCHASize', 'type' => OPTION_TYPE_RADIO,
						'order' => 5,
						'buttons' => array(gettext('Normal') => 'normal', gettext('Compact') => 'compact'),
						'desc' => gettext('Select the size your Google <em>reCAPTCHA widget</em>.')),
				'' => array('key' => 'recaptcha_link', 'type' => OPTION_TYPE_NOTE,
						'order' => 3,
						'desc' => gettext('You can get your credentials from <a href="http://www.google.com/recaptcha/admin">Google reCAPTCHA</a>. You should choose the <em>client side integration</em>: <strong>a. reCAPTCHA V2</strong>'))
		);
		return $options;
	}

	/**
	 * generates a reCAPTCHA v2 check
	 *
	 * @param type $prompt not used
	 */
	function getCaptcha($prompt = NULL) {
		global $_zp_current_locale;
		if (!getOption('reCAPTCHAKey')) {
			return array('input' => '', 'html' => '<p class="errorbox">' . gettext('reCAPTCHA is not properly configured.') . '</p>', 'hidden' => '');
		} else {
			$html = '<div class="g-recaptcha" data-sitekey="' . getOption('reCAPTCHAKey') . '" data-theme="' . getOption('reCAPTCHATheme') . '" data-size="' . getOption('reCAPTCHASize') . '"></div>';
			$themejs = '<script src="https://www.google.com/recaptcha/api.js?hl=' . trim(substr($_zp_current_locale, 0, 2)) . '"></script>';

			return array('input' => $html, 'hidden' => $themejs);
		}
	}

	/**
	 * checks for confirmed human
	 *
	 * @param type $code not used, included for class conformance
	 * @param type $code_ok not used, included for class conformance
	 * @return bool success
	 */
	function checkCaptcha($code, $code_ok) {
		if (isset($_POST['g-recaptcha-response'])) {
			$captcha = $_POST['g-recaptcha-response'];
			$ip = $_SERVER['REMOTE_ADDR'];
			$secretkey = getOption('reCAPTCHASecret');
			$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $secretkey . "&response=" . $captcha . "&remoteip=" . $ip);
			$responseKeys = json_decode($response, true);

			return intval($responseKeys["success"]) === 1;
		}
		return false;
	}

}

if ($plugin_disable) {
	enableExtension('reCaptcha_v2', 0);
} else {
	$_zp_captcha = new reCAPTCHA_v2();
}

