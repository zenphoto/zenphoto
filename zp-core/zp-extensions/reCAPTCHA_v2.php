<?php

/**
 *
 * Google reCAPTCHA version 2 handler, See {@link https://www.google.com/recaptcha/ google reCAPTCHA}
 *
 * This plugin supports the three reCAPTCHA v2 themes: <i>light</i>, <i>dark</i>, and <i>hidden</i>.
 * The <i>hidden</i> theme requires theme support on the submit button of the form. The button must
 * include the class <var>g-recaptcha</var> and must also have the reCaptcha data elements <var>data-sitekey="<i>your key</i>"</var>
 * and <var>data-callback="reCAPTCHAonSubmit"</var>. See, for example, the <i>register_user_form</i> script.
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 *
 * @author Stephen Billard (sbillard)

 * @package plugins/reCAPTCHA_v2
 * @pluginCategory admin
 */
// force UTF-8 Ø

global $_zp_captcha;
$plugin_is_filter = 500 | CLASS_PLUGIN;
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
			setOptionDefault('reCAPTCHAType', 'image');
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
						'buttons' => array(gettext('Light') => 'light', gettext('Dark') => 'dark', gettext('Hidden') => 'hidden'),
						'desc' => gettext('Select the theme your Google <em>reCAPTCHA widget</em>.<br />Note: if you select <em>Hidden</em> the form\'s <em>submit</em> button must include the reCAPTCHA class and data elements.')),
				gettext('Widget') => array('key' => 'reCAPTCHAType', 'type' => OPTION_TYPE_RADIO,
						'order' => 5,
						'buttons' => array(gettext('Audio') => 'audio', gettext('Image') => 'image'),
						'desc' => gettext('Choose the secondary verification method you would like to use.')
				),
				gettext('Size') => array('key' => 'reCAPTCHASize', 'type' => OPTION_TYPE_RADIO,
						'order' => 6,
						'buttons' => array(gettext('Normal') => 'normal', gettext('Compact') => 'compact'),
						'desc' => gettext('Select the size your Google <em>reCAPTCHA widget</em>.')),
				'' => array('key' => 'recaptcha_link', 'type' => OPTION_TYPE_NOTE,
						'order' => 3,
						'desc' => gettext('You can get your credentials from <a href="http://www.google.com/recaptcha/admin">Google reCAPTCHA</a>.'))
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
		if (getOption('reCAPTCHAKey')) {
			$hidden = ($theme = getOption('reCAPTCHATheme')) == 'hidden';
			$captcha = array();
			if ($hidden) {
				$captcha['hidden'] = '<script src = "https://www.google.com/recaptcha/api.js?hl=' . trim(substr($_zp_current_locale, 0, 2)) . '" async defer></script>
<script>
	function reCAPTCHAonSubmit(token) {
		document.getElementById($(".g-recaptcha").closest("form").attr("id")).submit();
	}
</script>';
				$captcha['submitButton'] = array('class' => 'g-recaptcha', 'extra' => 'data-sitekey="' . getOption('reCAPTCHAKey') . '" data-callback="reCAPTCHAonSubmit" data-type="' . getOption('reCAPTCHAType') . '"');
			} else {
				$captcha['hidden'] = '<script src="https://www.google.com/recaptcha/api.js?hl=' . trim(substr($_zp_current_locale, 0, 2)) . '"></script>';
				$captcha['input'] = '<div class="g-recaptcha" data-sitekey="' . getOption('reCAPTCHAKey') . '" data-theme="' . $theme . '" data-type="' . getOption('reCAPTCHAType') . '" data-size="' . getOption('reCAPTCHASize') . '"></div>' . "\n" . '
<noscript>
	<div>
		<div style="width: 302px; height: 422px; position: relative;">
			<div style="width: 302px; height: 422px; position: absolute;">
				<iframe src="https://www.google.com/recaptcha/api/fallback?k=your_site_key"
								frameborder="0" scrolling="no"
								style="width: 302px; height:422px; border-style: none;">
				</iframe>
			</div>
		</div>
		<div style="width: 300px; height: 60px; border-style: none;
								bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px;
								background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
			<textarea id="g-recaptcha-response" name="g-recaptcha-response"
							  class="g-recaptcha-response"
							  style="width: 250px; height: 40px; border: 1px solid #c1c1c1;
								margin: 10px 25px; padding: 0px; resize: none;" >
			</textarea>
		</div>
	</div>
</noscript>
';
			}
			return $captcha;
		}

		return array('input' => '', 'html' => '<p class="errorbox">' . gettext('reCAPTCHA_v2 is not properly configured.') . '</p>', 'hidden' => '');
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

