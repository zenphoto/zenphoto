<?php

/**
 * reCaptcha v2 handler (http://www.google.com/recaptcha)
 * 
 * Adapted from the third paryt noCaptcha reCaptcha plugin by Ben Feather (Epsilon) 
 * https://github.com/Epsilon8425/Zenphoto-noCaptcha-reCaptcha
 *
 * Note this plugin embeds the external reCaptcha JavaScript library from Google's servers
 *
 * @author Ben Feather (Epsilon), Stephen Billard (sbillard), Malte Müller (acrylian)
 * @package zpcore\plugins\recaptcha
 */
// force UTF-8 Ø
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Google reCaptcha v2 handler.");
$plugin_author = "Ben Feather (Epsilon), Stephen Billard (sbillard), Malte Müller (acrylian)";
$plugin_disable = ($_zp_captcha->name && $_zp_captcha->name != 'reCaptcha') ? sprintf(gettext('Only one Captcha handler plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'), $_zp_captcha->name) : '';
$plugin_notice = array(
		gettext('Google account and reCaptcha key required.'),
		gettext('Privacy note: This plugin uses external third party sources')
);
$option_interface = 'reCaptcha';
$plugin_category = gettext('Spam');

class reCaptcha extends _zp_captcha {

	public $name = 'reCaptcha';

	/**
	 * Class instantiator
	 *
	 * @return captcha
	 */
	function __construct() {
		//setOptionDefault('reCaptcha_theme', 'red');
		setOptionDefault('reCaptcha_theme', 'light');
		setOptionDefault('reCaptcha_type', 'image');
		setOptionDefault('reCaptcha_size', 'normal');
	}

	/**
	 * Returns array of supported options for the admin-options handler
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(
				gettext('Public key') => array(
						'key' => 'reCaptcha_public_key',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext('Enter your <em>reCaptcha v2</em> public key. You can obtain this key from the Google <a href="http://www.google.com/recaptcha">reCaptcha</a> site')),
				gettext('Private key') => array(
						'key' => 'reCaptcha_private_key',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext('Enter your <em>reCaptcha v2</em> private key.')),
				// Dropdown for reCaptcha theme
				gettext('Widget Theme:') => array(
						'key' => 'reCaptcha_theme',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 3,
						'selections' => array(
								gettext('Light') => 'light',
								gettext('Dark') => 'dark'
						),
						'desc' => gettext('Choose the theme for your reCaptcha.')
				),
				gettext('Widget Type:') => array(
						'key' => 'reCaptcha_type',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 4,
						'selections' => array(
								gettext('Audio') => 'audio',
								gettext('Image') => 'image'
						),
						'desc' => gettext('Choose the secondary verification method you would like to use.')
				),
				gettext('Widget Size:') => array(
						'key' => 'reCaptcha_size',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 5,
						'selections' => array(
								gettext('Normal') => 'normal',
								gettext('Compact') => 'compact'
						),
						'desc' => gettext('Choose the size of the reCaptcha widget.')
				)
		);
	}

	/**
	 * Returns HTML for reCaptcha (including required reCaptcha script)
	 * 
	 * @param string $publicKey The public key
	 * @param string $theme The theme to use "light" or "dark"
	 * @param string $type Type to use "audio" or "image"
	 * @param string $size Size to use "normal" or "compact"
	 * @return type
	 */
	function getCaptchaHTML($publicKey, $theme, $type, $size) {
		return '<div class="g-recaptcha" data-sitekey="' . $publicKey . '" data-theme="' . $theme . '" data-type="' . $type . '" data-size="' . $size . '"></div>
				<script src="https://www.google.com/recaptcha/api.js"></script>';
	}

	/**
	 * Called by form (wherever reCaptcha is enabled) on submit to check whether or not the capture has succeeded. $s1, $s2 are required.
	 * 
	 * @param type $s1 Not used
	 * @param type $s2 Not used
	 * @return boolean
	 */
	function checkCaptcha($s1, $s2) {
		$secretKey = getOption('reCaptcha_private_key');
		$captcha = '';
		if(isset($_POST['g-recaptcha-response'])) {
			$captcha = sanitize($_POST['g-recaptcha-response']);
		}

		// verifies reCaptcha
		$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $captcha . '&remoteip=' . sanitize($_SERVER['REMOTE_ADDR']));

		// Changes response value into expected format (for return)
		if (strpos($response, 'true') == true) {
			$valid = true;
		} else {
			$valid = false;
		}
		return $valid;
	}

	/**
	 * Called by form (wherever reCaptcha is enabled) to add reCaptcha widget
	 * 
	 * @param type $prompt
	 * @return type
	 */
	function getCaptcha($prompt) {
		$publicKey = getOption('reCaptcha_public_key');
		$theme = getOption('reCaptcha_theme');
		//handle outdated recaptcha v1 themes if still set
		if (!in_array($theme, array('light', 'dark'))) {
			$theme = 'light';
		}
		$type = getOption('reCaptcha_type');
		$size = getOption('reCaptcha_size');

		// Check for proper configuration of options
		if (!getOption('reCaptcha_public_key') || !getOption('reCaptcha_private_key')) {
			return array(
					'input' => '',
					'html' => '<div class="errorbox"><p>' . gettext('reCAPTCHA v2 keys are not configured properly. Visit <a href="https://www.google.com/recaptcha/intro/index.html">this link</a> to retrieve your reCaptcha keys.') . '</p></div>',
					'hidden' => ''
			);
		} else {
			$html = $this->getCaptchaHTML($publicKey, $theme, $type, $size);
			return array(
					'html' => '<label class="captcha-label">' . $prompt . '</label>',
					'input' => $html
			);
		}
	}

}

// Required for script to be considered a reCaptcha handler
if ($plugin_disable) {
	enableExtension('reCaptcha', 0);
} else {
	$_zp_captcha = new reCaptcha(getOption('reCaptcha_private_key'));
}