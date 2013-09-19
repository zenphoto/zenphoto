<?php
/**
 * Zenphoto default captcha handler
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage spam
 */
// force UTF-8 Ø

$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Zenphoto captcha handler.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = ($_zp_captcha->name && $_zp_captcha->name != 'zpCaptcha') ? sprintf(gettext('Only one Captcha handler plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'), $_zp_captcha->name) : '';

$option_interface = 'zpCaptcha';

class zpCaptcha extends _zp_captcha {

	var $name = 'zpCaptcha';

	/**
	 * Class instantiator
	 *
	 * @return captcha
	 */
	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('zenphoto_captcha_length', 5);
			setOptionDefault('zenphoto_captcha_font_size', 18);
			setOptionDefault('zenphoto_captcha_key', sha1($_SERVER['HTTP_HOST'] . 'a9606420399a77387af2a4b541414ee5' . getUserIP()));
			setOptionDefault('zenphoto_captcha_string', 'abcdefghijkmnpqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWXYZ');
		}
	}

	/**
	 * Returns array of supported options for the admin-options handler
	 *
	 * @return unknown
	 */
	function getOptionsSupported() {
		$fontlist = zp_getFonts();
		$options = array(
						gettext('Hash key')						 => array('key'		 => 'zenphoto_captcha_key', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext('The key used in hashing the CAPTCHA string. Note: this key will change with each successful CAPTCHA verification.')),
						gettext('Allowed characters')	 => array('key'		 => 'zenphoto_captcha_string', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1,
										'desc'	 => gettext('The characters which may appear in the CAPTCHA string.')),
						gettext('CAPTCHA length')			 => array('key'			 => 'zenphoto_captcha_length', 'type'		 => OPTION_TYPE_RADIO,
										'order'		 => 0,
										'buttons'	 => array(gettext('3') => 3, gettext('4') => 4, gettext('5') => 5, gettext('6') => 6),
										'desc'		 => gettext('The number of characters in the CAPTCHA.')),
						gettext('CAPTCHA font')				 => array('key'				 => 'zenphoto_captcha_font', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 3,
										'selections' => array_merge(array('*' . gettext('random') . '*' => '*'), $fontlist),
										'desc'			 => gettext('The font to use for CAPTCHA characters.')),
						gettext('CAPTCHA font size')	 => array('key'		 => 'zenphoto_captcha_font_size', 'type'	 => OPTION_TYPE_CLEARTEXT,
										'order'	 => 3.5,
										'desc'	 => gettext('The size to use if the font is scalable (<em>TTF</em> and <em>Imagick</em> fonts.)')),
						''														 => array('key'		 => 'zenphoto_captcha_image', 'type'	 => OPTION_TYPE_CUSTOM,
										'order'	 => 4,
										'desc'	 => gettext('Sample CAPTCHA image'))
		);
		return $options;
	}

	function handleOption($key, $cv) {
		$captcha = $this->getCaptcha(NULL);
		?>
		<span id="zenphoto_captcha_image_loc"><?php echo $captcha['html']; ?></span>
		<script type="text/javascript">
			// <!-- <![CDATA[
			$(document).ready(function() {
				$('#zenphoto_captcha_font').change(function() {
					var base = $('#zenphoto_captcha_image_loc').html();
					var match = base.match(/src=".*"\s/gi) + '%';
					if ((i = match.indexOf('&')) <= 0) {
						i = match.indexOf('" %');
					}
					var path = match.substr(0, i);
					var nbase = base.replace(path, path + '&amp;f=' + $('#zenphoto_captcha_font').val());
					$('#zenphoto_captcha_image_loc').html(nbase);
				});
			});
			// ]]> -->
		</script>
		<?php
	}

	/**
	 * gets (or creates) the CAPTCHA encryption key
	 *
	 * @return string
	 */
	function getCaptchaKey() {
		global $_zp_authority;
		$key = getOption('zenphoto_captcha_key');
		if (empty($key)) {
			$admin = Zenphoto_Authority::getAnAdmin(array('`user`='	 => $_zp_authority->master_user, '`valid`=' => 1));
			if (is_object($admin)) {
				$key = $admin->getPass();
			} else {
				$key = 'No admin set';
			}
			$key = sha1('zenphoto' . $key . 'captcha key');
			setOption('zenphoto_captcha_key', $key);
		}
		return $key;
	}

	/**
	 * Checks if a CAPTCHA string matches the CAPTCHA attached to the comment post
	 * Returns true if there is a match.
	 *
	 * @param string $code
	 * @param string $code_ok
	 * @return bool
	 */
	function checkCaptcha($code, $code_ok) {
		$captcha_len = getOption('zenphoto_captcha_length');
		$key = $this->getCaptchaKey();
		$code_cypher = sha1(bin2hex(rc4($key, trim($code))));
		$code_ok = trim($code_ok);
		if ($code_cypher != $code_ok || strlen($code) != $captcha_len) {
			return false;
		}
		query('DELETE FROM ' . prefix('captcha') . ' WHERE `ptime`<' . (time() - 3600)); // expired tickets
		$result = query('DELETE FROM ' . prefix('captcha') . ' WHERE `hash`="' . $code_cypher . '"');
		if ($result && db_affected_rows() == 1) {
			$len = rand(0, strlen($key) - 1);
			$key = sha1(substr($key, 0, $len) . $code . substr($key, $len));
			setOption('zenphoto_captcha_key', $key);
			return true;
		}
		return false;
	}

	/**
	 * generates a simple captcha
	 *
	 * @return array;
	 */
	function getCaptcha($prompt) {
		global $_zp_HTML_cache;
		$_zp_HTML_cache->disable();
		$captcha_len = getOption('zenphoto_captcha_length');
		$key = $this->getCaptchaKey();
		$lettre = getOption('zenphoto_captcha_string');
		$numlettre = strlen($lettre) - 1;

		$string = '';
		for ($i = 0; $i < $captcha_len; $i++) {
			$string .= $lettre[rand(0, $numlettre)];
		}
		$cypher = bin2hex(rc4($key, $string));
		$code = sha1($cypher);
		query('DELETE FROM ' . prefix('captcha') . ' WHERE `ptime`<' . (time() - 3600), false); // expired tickets
		query("INSERT INTO " . prefix('captcha') . " (ptime, hash) VALUES (" . db_quote(time()) . "," . db_quote($code) . ")", false);
		$html = '<label for="code" class="captcha_label">' . $prompt . '</label><img id="captcha" src="' . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zpCaptcha/c.php?i=' . $cypher . '" alt="Code" />';
		$input = '<input type="text" id="code" name="code" class="captchainputbox" />';
		$hidden = '<input type="hidden" name="code_h" value="' . $code . '" />';
		return array('input'	 => $input, 'html'	 => $html, 'hidden' => $hidden);
	}

}

if ($plugin_disable) {
	enableExtension('zpCaptcha', 0);
} else {
	$_zp_captcha = new zpCaptcha();
}
?>
