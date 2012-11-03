<?php
/**
 * reCaptcha handler (http://www.google.com/recaptcha)
 *
 * @package plugins
 */

// force UTF-8 Ø
require_once(dirname(__FILE__).'/reCaptcha/recaptchalib.php');

class captcha {
	/**
	 * Class instantiator
	 *
	 * @return captcha
	 */
	function captcha() {
	}

	/**
	 * Returns array of supported options for the admin-options handler
	 *
	 * @return unknown
	 */
	function getOptionsSupported() {
		return array(
								gettext('Public key') => array('key' => 'reCaptcha_public_key', 'type' => OPTION_TYPE_TEXTBOX,
												'order' => 1,
												'desc' => gettext('Enter your <em>reCaptcha</em> public key. You can obtain this key from the Google <a href="http://www.google.com/recaptcha">reCaptcha</a> site')),
								gettext('Private key') => array('key' => 'reCaptcha_private_key', 'type' => OPTION_TYPE_TEXTBOX,
												'order' => 2,
												'desc' => gettext('Enter your <em>reCaptcha</em> private key.'))
		);
	}
	function handleOption($key, $cv) {
	}



	/**
	 * Checks reCaptcha
	 *
	 * @return bool
	 */
	function checkCaptcha() {
		$resp = recaptcha_check_answer (getOption('reCaptcha_private_key'), @$_SERVER["REMOTE_ADDR"], @$_POST["recaptcha_challenge_field"], @$_POST["recaptcha_response_field"]);
		return $resp->is_valid;
	}

	/**
	 * generates a simple captcha for comments
	 *
	 * Thanks to gregb34 who posted the original code
	 *
	 * Returns the captcha code string and image URL (via the $image parameter).
	 *
	 * @return string;
	 */
	function getCaptcha() {
		return array('input'=>recaptcha_get_html(getOption('reCaptcha_public_key')), NULL, secureServer());
	}
}

?>