<?php
/**
 * reCaptcha handler (http://www.google.com/recaptcha)
 *
 * This plugin lets you select from one of Google's reCaptcha themes (<i>Red</i>, <i>White</i>, <i>Black Glass</i>, or <i>Clean</i>)
 * or from a custom reCaptcha theme such as the <i>lt-blue</i> theme included.
 *
 * The <i>lt-blue></i> theme is intended as an example of how to create a custom theme.
 * You can create themes and place them into the %PLUGINS%/reCpatcha folder. The folder name will be the theme name. The only
 * required file is <var>reCaptcha.html</var> but you can include other items like images, etc. in the folder.
 *
 * Your custom <var>reCaptcha.html</var> will be processed at run time making substitutions of the followng "definitions":
 * <ul>
 * 	<li><var>__GETHELP__</var> => <i>localized text for</i> "Help"</li>
 * 	<li><var>__GETIMAGE__</var> => <i>localized text for</i> "Get an image CAPTCHA"</li>
 * 	<li><var>__GETAUDIO__</var> => <i>localized text for</i> "Get an audio CAPTCHA"</li>
 * 	<li><var>__RELOAD__</var> => <i>localized text for</i> "Get another CAPTCHA"</li>
 * 	<li><var>__WORDS__</var> => <i>localized text for</i> "Enter the words above"</li>
 * 	<li><var>__NUMBERS__</var> => <i>localized text for</i> "Enter the numbers you hear"</li>
 * 	<li><var>__ERROR__</var> => <i>localized text for</i> "Incorrect please try again"</li>
 * 	<li><var>__SOURCEWEBPATH__</var> => <i>the WEB path to your folder (for url references)</i></li>
 * </ul>
 *
 * @package plugins
 * @subpackage spam
 */
// force UTF-8 Ø
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Google reCaptcha handler.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = ($_zp_captcha->name && $_zp_captcha->name != 'reCaptcha') ? sprintf(gettext('Only one Captcha handler plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'), $_zp_captcha->name) : '';

$option_interface = 'reCaptcha';

require_once(dirname(__FILE__) . '/reCaptcha/recaptchalib.php');

class reCaptcha extends _zp_captcha {

	var $name = 'reCaptcha';

	/**
	 * Class instantiator
	 *
	 * @return captcha
	 */
	function __construct() {
		setOptionDefault('reCaptcha_theme', 'red');
	}

	/**
	 * Returns array of supported options for the admin-options handler
	 *
	 * @return unknown
	 */
	function getOptionsSupported() {
		$themes = array(gettext('Red')				 => 'red', gettext('White')			 => 'white', gettext('Black Glass') => 'blackglass', gettext('Clean')			 => 'clean');
		$custom = getPluginFiles('*', 'reCaptcha', false);
		foreach ($custom as $theme => $path) {
			if (is_dir($path)) {
				$themes[$theme = basename($theme)] = $theme;
			}
		}

		return array(
						gettext('Public key')	 => array('key'		 => 'reCaptcha_public_key', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1,
										'desc'	 => gettext('Enter your <em>reCaptcha</em> public key. You can obtain this key from the Google <a href="http://www.google.com/recaptcha">reCaptcha</a> site')),
						gettext('Private key') => array('key'		 => 'reCaptcha_private_key', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext('Enter your <em>reCaptcha</em> private key.')),
						gettext('Theme')			 => array('key'				 => 'reCaptcha_theme', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 3,
										'selections' => $themes,
										'desc'			 => gettext('Select the <em>reCaptcha</em> theme.')),
						''										 => array('key'		 => 'reCcaptcha_image', 'type'	 => OPTION_TYPE_CUSTOM,
										'order'	 => 4,
										'desc'	 => gettext('Sample CAPTCHA image'))
		);
	}

	function handleOption($key, $cv) {
		$captcha = $this->getCaptcha(NULL);
		?>
		<span id="zenphoto_captcha_image_loc"><?php echo $captcha['input']; ?></span>
		<?php
	}

	/**
	 * Checks reCaptcha
	 *
	 * @return bool
	 */
	function checkCaptcha($s1, $s2) {
		$resp = recaptcha_check_answer(getOption('reCaptcha_private_key'), @$_SERVER["REMOTE_ADDR"], @$_POST["recaptcha_challenge_field"], @$_POST["recaptcha_response_field"]);
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
	function getCaptcha($prompt) {
		$theme = getOption('reCaptcha_theme');
		$publicKey = getOption('reCaptcha_public_key');
		$lang = strtolower(substr(ZENPHOTO_LOCALE, 0, 2));

		if (!getOption('reCaptcha_public_key')) {
			return array('input'	 => '', 'html'	 => '<p class="errorbox">' . gettext('reCAPTCHA is not properly configured.') . '</p>', 'hidden' => '');
		} else {

			$source = getPlugin('reCaptcha/' . $theme . '/reCaptcha.html');
			if ($source) {
				$webpath = dirname(getplugin('reCaptcha/' . $theme . '/reCaptcha.html', false, true));
				$tr = array('__GETHELP__'				 => gettext("Help"),
								'__GETIMAGE__'			 => gettext("Get a visual challenge"),
								'__GETAUDIO__'			 => gettext("Get an audio challenge"),
								'__RELOAD__'				 => gettext("Get another challenge"),
								'__WORDS__'					 => gettext("Type the two words"),
								'__NUMBERS__'				 => gettext("Type what you hear"),
								'__ERROR__'					 => gettext("Incorrect please try again"),
								'__SOURCEWEBPATH__'	 => $webpath);
				$html = strtr(file_get_contents($source), $tr);
				$theme = 'custom'; //	to tell google to use the above
			} else {
				$html = '';
			}
			$themejs = '<script type="text/javascript">' . "\n" .
							"  var RecaptchaOptions = {\n";
			if (!in_array($lang, array('de', 'en', 'es', 'fr', 'nl', 'ru', 'pt', 'tr'))) { // google's list as of June 2013
				$themejs .= "      custom_translations : {\n" .
								"               instructions_visual : 'Type the two words',\n" .
								"               instructions_audio : 'Type what you hear',\n" .
								"               play_again : 'Play sound again',\n" .
								"               cant_hear_this : 'Download the sound as MP3',\n" .
								"               visual_challenge : 'Get a visual challenge',\n" .
								"               audio_challenge : 'Get an audio challenge',\n" .
								"               refresh_btn : 'Get another challenge',\n" .
								"               help_btn : 'Help',\n" .
								"               incorrect_try_again : 'Incorrect please try again',\n" .
								"      },\n";
			}
			$themejs .= "       lang : '$lang',\n" .
							"				theme : '$theme'\n" .
							"				};\n" .
							"</script>\n";
			$html .= recaptcha_get_html($publicKey, NULL, secureServer());
			return array('html'	 => '<label class="captcha_label">' . $prompt . '</label>', 'input'	 => $themejs . $html);
		}
	}

}

if ($plugin_disable) {
	enableExtension('reCaptcha', 0);
} else {
	$_zp_captcha = new reCaptcha();
}
?>
