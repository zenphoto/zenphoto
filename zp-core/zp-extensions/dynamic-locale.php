<?php
/**
 * Allow the site viewer to select a localization.
 *
 * Only the zenphoto and theme gettext() string are localized by this facility.
 *
 * If you want to support image descriptions, etc. in multiple languages you will
 * have to enable the <i>multi-lingual</i> option found next to the language selector on
 * the admin gallery configuration page. Then you will have to provide appropriate
 * alternate translations for the fields you use. While there is a field for
 * strings for all zenphoto supported languages you need supply only those you choose.
 * The others language strings will default to your local language.
 *
 * Locale selection may occur in several ways:
 * <ul>
 * 	<li>A cookie stored when the user chooses his language</li>
 * 	<li>The URI language selection provided by the <i>URI</i> selection</li>
 * 	<li>The subdomain provided by the <i>subdomain</i> selection</li>
 * </ul>
 *
 * <b>URL format</b>
 * <dl>
 * 	<dd>/ <i>language-id</i> / <i>standard url</i></dd>
 * </dl>
 * Where <i>language-id</i> is the local identifier (e.g. en, en_US, fr_FR, etc.) URL format
 * requires <i>mod_rewrite</i> be enabled.
 *
 * <b>subdomain format</b>
 * <dl>
 * 	<dd><i>language-id</i>.<code>host name</code></dd>
 * </dl>
 *
 * If neither <b>subdomain format</b> nor <b>URL format</b> is enabled then the format will be
 * <dl>
 * 	<dd><i>standard url</i>?locale=<i>language-id</i></dd>
 * </dl>
 *
 * Subdomain format requires that you have created the appropriate subdomains pointing to your installation.
 * That is <code>fr.host name</code> must point to the same location as <code>host name</code>.
 * (Some providers will automatically redirect undefined subdomains to the main domain. If your
 * provider does this, no subdomain creation is needed.)
 *
 * <b>NOTE:</b> the implementation of URLs requires that zenphoto parse the URL, save the
 * language request to a cookie, then redirect to the "native" URL. This means that there is an extra
 * redirect for <b>EACH</b> page request!
 *
 * This plugiin applies only to the theme pages--not Admin. The <em>language cookie</i>, if set, will
 * carry over to the admin pages. As will using <i>subdomains</i>.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage seo
 */
$plugin_is_filter = 10 | CLASS_PLUGIN;
$plugin_description = gettext("Allows viewers of your site to select the language translation of their choice.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'dynamic_locale';

define('LOCALE_TYPE', getOption('dynamic_locale_subdomain'));
define('BASE_LOCALE', getOption('dynamic_locale_base'));

if (OFFSET_PATH != 2) {
	zp_register_filter('theme_head', 'dynamic_locale::dynamic_localeCSS');
	if (LOCALE_TYPE && extensionEnabled('dynamic-locale')) {
		if (LOCALE_TYPE == 1) {
			zp_register_filter('load_request', 'seo_locale::load_request');
			define('SEO_WEBPATH', seo_locale::localePath());
			define('SEO_FULLWEBPATH', seo_locale::localePath(true));
		}
	}
}

/**
 * prints a form for selecting a locale
 * The POST handling is by getUserLocale() called in functions.php
 *
 */
function printLanguageSelector($flags = NULL) {
	global $_locale_Subdomains;
	$localeOption = getOption('locale');
	$languages = generateLanguageList();
	if (isset($_REQUEST['locale'])) {
		$locale = sanitize($_REQUEST['locale']);
		if ($localeOption != $locale) {
			?>
			<div class="errorbox">
				<h2>
					<?php printf(gettext('<em>%s</em> is not available.'), html_encode($locale)); ?>
					<?php printf(gettext('The locale %s is not supported on your server.'), html_encode($locale)); ?>
					<br />
					<?php echo gettext('You can use the <em>debug</em> plugin to see which locales your server supports.'); ?>
				</h2>
			</div>
			<?php
		}
	}
	if (is_null($flags)) {
		$flags = getOption('dynamic_locale_visual');
	}
	$request = parse_url(getRequestURI());
	if (isset($request['query'])) {
		$query = explode('&', $request['query']);
		$uri['query'] = '';
		foreach ($query as $key => $str) {
			if (preg_match('/^locale\s*=/', $str)) {
				unset($query[$key]);
			}
		}
		if (empty($query)) {
			unset($request['query']);
		} else {
			$request['query'] = implode('&', $query);
		}
	}
	$uri = pathurlencode(@$request['path']);
	$separator = '?';
	if (isset($request['query'])) {
		$uri .= '?' . $request['query'];
		$separator = '&';
	}
	if ($flags) {
		asort($languages);
		?>
		<ul class="flags">
			<?php
			foreach ($languages as $text => $lang) {
				?>
				<li<?php if ($lang == $localeOption) echo ' class="currentLanguage"'; ?>>
					<?php
					$flag = getLanguageFlag($lang);
					$path = dynamic_locale::localLink($uri, $separator, $lang);
					if ($lang != $localeOption) {
						?>
						<a href="<?php echo html_encode($path); ?>" >
							<?php
						}
						?>
						<img src="<?php echo $flag; ?>" alt="<?php echo $text; ?>" title="<?php echo $text; ?>" />
						<?php
						if ($lang != $localeOption) {
							?>
						</a>
						<?php
					}
					?>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	} else {
		?>
		<div class="languageSelect">
			<form id="language_change" action="#" method="post">
				<select id="dynamic-locale" class="languageSelector" name="locale" onchange="switch_language();">
					<?php
					foreach ($languages as $text => $lang) {
						$path = dynamic_locale::localLink($uri, $separator, $lang);
						?>
						<option value="<?php echo html_encode(html_encode($path)); ?>"<?php if ($lang == $localeOption) echo ' selected="selected"'; ?>>
						<span class="locale_name">
							<?php echo html_encode($text); ?>
						</span>
						</option>
						?>
						<?php
					}
					?>
				</select>
			</form>
		</div>
		<?php
	}
}

class dynamic_locale {

	function __construct() {
		if (OFFSET_PATH == 2) {
			$seo_locale = extensionEnabled('seo_locale') && getOption('dynamic_locale_subdomain') != 2;
			setOptionDefault('dynamic_locale_visual', 0);
			setOptionDefault('dynamic_locale_subdomain', (int) $seo_locale);
			setOptionDefault('dynamic_locale_base', getUserLocale());
		}
	}

	function getOptionsSupported() {
		$host = $_SERVER['HTTP_HOST'];
		$matches = explode('.', $host);
		if (validateLocale($matches[0], 'Dynamic Locale')) {
			array_shift($matches);
			$host = implode('.', $matches);
		}
		$localdesc = '<p>' . sprintf(gettext('Select <em>Use subdomains</em> and links will be in the form <code><em>language</em>.%s</code> where <code><em>language</em></code> is the language code, e.g. <code><em>fr</em></code> for French.'), $host) . '</p>';

		$locales = generateLanguageList();
		$buttons = array(gettext('subdomain') => 2, gettext('URL') => 1, gettext('disabled') => 0);
		if (MOD_REWRITE) {
			$buttons[gettext('URL')] = 1;
			$localdesc .= '<p>' . sprintf(gettext('Select <em>URL</em> and links paths will have the language selector prepended in the form <code>%1$s/<em>language</em>/...</code>'), ltrim(WEBPATH, '/')) . '</p>';
		} else {
			unset($buttons[gettext('URL')]);
			if (getOption('dynamic_locale_subdomain') == 1) {
				setOption('dynamic_locale_subdomain', 0);
			}
		}
		$options = array(gettext('Use flags') => array('key' => 'dynamic_locale_visual', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 0,
						'desc' => gettext('Checked produces an array of flags. Not checked produces a selector.')),
				gettext('Language links') => array('key' => 'dynamic_locale_subdomain', 'type' => OPTION_TYPE_RADIO,
						'order' => 1,
						'buttons' => $buttons,
						'desc' => $localdesc),
				gettext('Site language') => array('key' => 'dynamic_locale_base', 'type' => OPTION_TYPE_RADIO,
						'order' => 2,
						'buttons' => $locales,
						'desc' => gettext('Set the primary language for your site.'))
		);

		return $options;
	}

	static function dynamic_localeCSS() {
		?>
		<link type="text/css" rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/dynamic-locale/locale.css" />
		<?php
	}

	static function fullHostPath($lang) {
		global $_locale_Subdomains;
		$host = $_SERVER['HTTP_HOST'];
		$matches = explode('.', $host);
		if (validateLocale($matches[0], 'Dynamic Locale')) {
			array_shift($matches);
			$host = implode('.', $matches);
		}
		if (($lang != BASE_LOCALE) && $l = $_locale_Subdomains[$lang]) {
			$host = $l . '.' . $host;
		}
		if (SERVER_PROTOCOL == 'https') {
			$host = 'https://' . $host;
		} else {
			$host = 'http://' . $host;
		}
		return $host;
	}

	static function localLink($uri, $separator, $lang) {
		switch (LOCALE_TYPE) {
			case 2:
				$path = dynamic_locale::fullHostPath($lang) . $uri;
				break;
			case 1:
				$path = seo_locale::localePath(false, $lang) . str_replace(WEBPATH, '', $uri);
				break;
			default:
				$path = $uri . $separator . 'locale=' . $lang;
				break;
		}
		return $path;
	}

}

class seo_locale {

	static function load_request($allow) {
		$uri = getRequestURI();
		$parts = explode('?', $uri);
		$uri = $parts[0];
		$path = ltrim(substr($uri, strlen(WEBPATH) + 1), '/');
		if (empty($path)) {
			return $allow;
		} else {
			$rest = strpos($path, '/');
			if ($rest === false) {
				if (strpos($path, '?') === 0) {
					// only a parameter string
					return $allow;
				}
				$l = $path;
			} else {
				$l = substr($path, 0, $rest);
			}
		}
		$locale = validateLocale($l, 'seo_locale');
		if ($locale) {
			// set the language cookie and redirect to the "base" url
			zp_setCookie('dynamic_locale', $locale);
			$uri = pathurlencode(preg_replace('|/' . $l . '[/$]|', '/', $uri));
			if (isset($parts[1])) {
				$uri .= '?' . $parts[1];
			}
			header("HTTP/1.0 302 Found");
			header("Status: 302 Found");
			header('Location: ' . $uri);
			exitZP();
		}
		return $allow;
	}

	static function localePath($full = false, $loc = NULL) {
		global $_zp_page, $_zp_gallery_page, $_zp_current_locale;
		if ($full) {
			$path = FULLWEBPATH;
		} else {
			$path = WEBPATH;
		}
		if (is_null($loc)) {
			$loc = zp_getCookie('dynamic_locale');
		}
		if ($loc != $_zp_current_locale) {
			if ($locale = zpFunctions::getLanguageText($loc)) {
				$path .= '/' . $locale;
			}
		}
		return $path;
	}

}
?>
