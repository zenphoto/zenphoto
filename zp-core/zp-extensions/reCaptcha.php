<?php
/**
 * reCaptcha handler (http://www.google.com/recaptcha)
 *
 * @package plugins
 * @subpackage spam
 */

// force UTF-8 Ø
$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext("Google reCaptcha handler.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = ($_zp_captcha->name && $_zp_captcha->name != 'reCaptcha')?sprintf(gettext('Only one Captcha handler plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'),$_zp_captcha->name):'';

$option_interface = 'reCaptcha';

require_once(dirname(__FILE__).'/reCaptcha/recaptchalib.php');

class reCaptcha extends _zp_captcha{

	var $name='reCaptcha';

	/**
	 * Class instantiator
	 *
	 * @return captcha
	 */
	function __construct() {
		setOptionDefault('reCaptcha_theme','red');
	}

	/**
	 * Returns array of supported options for the admin-options handler
	 *
	 * @return unknown
	 */
	function getOptionsSupported() {
		$themes = array(gettext('Red') => 'red', gettext('White') => 'white', gettext('Black Glass') => 'blackglass', gettext('Clean') => 'clean');
		if (TEST_RELEASE) {
			$themes['custom'] = gettext("custom");
		}
		return array(
								gettext('Public key') => array('key' => 'reCaptcha_public_key', 'type' => OPTION_TYPE_TEXTBOX,
												'order' => 1,
												'desc' => gettext('Enter your <em>reCaptcha</em> public key. You can obtain this key from the Google <a href="http://www.google.com/recaptcha">reCaptcha</a> site')),
								gettext('Private key') => array('key' => 'reCaptcha_private_key', 'type' => OPTION_TYPE_TEXTBOX,
												'order' => 2,
												'desc' => gettext('Enter your <em>reCaptcha</em> private key.')),
								gettext('Theme') => array('key' => 'reCaptcha_theme', 'type' => OPTION_TYPE_SELECTOR,
												'order' => 3,
												'selections' => $themes,
												'desc' => gettext('Select the <em>reCaptcha</em> theme.')),
								'' 			=> array('key' => 'reCcaptcha_image', 'type' => OPTION_TYPE_CUSTOM,
												'order' => 4,
												'desc' => gettext('Sample CAPTCHA image'))

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
	function getCaptcha($prompt) {
		parent::getCaptcha($prompt);
		$theme = getOption('reCaptcha_theme');
		$publicKey = getOption('reCaptcha_public_key');

		if (!getOption('reCaptcha_public_key')) {
			return array('input'=>'', 'html'=>'<p class="errorbox">'.gettext('reCAPTCHA is not properly configured.').'</p>', 'hidden'=>'');
		} else {
			$themejs =	'<script type="text/javascript">'."\n".
				 				"  var RecaptchaOptions = {\n".
				    		"				theme : '$theme'\n".
				 				"				};\n".
				 				"</script>\n";
			if ($theme=='custom') {
				if (secureServer()) {
					$server = RECAPTCHA_API_SECURE_SERVER;
				} else {
					$server = RECAPTCHA_API_SERVER;
				}
				$source = getPlugin('reCaptcha/custom.txt');
				$webpath = dirname(getplugin('reCaptcha/custom.txt',false,true));
				$tr = array('__SERVER__'=>$server,
										'__GETHELP__'=>gettext("Help"),
										'__GETIMAGE__'=>gettext("Get an image CAPTCHA"),
										'__GETAUDIO__'=>gettext("Get an audio CAPTCHA"),
										'__RELOAD__'=>gettext("Get another CAPTCHA"),
										'__WORDS__'=>gettext("Enter the words above"),
										'__NUMBERS__'=>gettext("Enter the numbers you hear"),
										'__ERROR__'=>gettext("n correct please try again"),
										'__PUBLICKEY__'=>$publicKey,
										'__SOURCEWEBPATH__'=>$webpath);
				if (OFFSET_PATH) {
					$tr['__FLOAT__'] = '';
				} else {
					$tr['__FLOAT__'] = 'float:right;';
				}
				$html = strtr(file_get_contents($source),$tr);
			} else {
				$html = recaptcha_get_html($publicKey, NULL, secureServer());
			}
			return array('html'=>'<label class="captcha_label">'.$prompt.'</label>', 'input'=>$themejs.$html);
		}
	}
}

if ($plugin_disable) {
	setOption('zp_plugin_reCaptcha', 0);
} else {
	$_zp_captcha = new reCaptcha();
}

?>
