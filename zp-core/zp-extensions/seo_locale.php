<?php
/**
 * Forces language selection via an URI parameter.
 *
 * This filter will detect a language setting from the URI and
 * set the locale accordingly.
 *
 * In addition, theme URLs will have the languageid inserted into them
 * so that the above processing will result in the language being set.
 *
 * This plugin should not be used if you are using <i>subdomain locales</i>
 * (See the dynamic_locales plugin.)
 *
 * The URL format is:<br>
 * <var>mod_rewrite</var><br>
 *			/ <i>languageid</i> / <i>standard url</i><br>
 * <var>else</var><br>
 * 			<i>standard url</i>?locale=<i>languageid</i><br>
 * Where <i>languageid</i> is the local identifier (e.g. en, en_US, fr_FR, etc.)
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */

$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext("Allows setting language locale through the URI.");
$plugin_notice = gettext('<strong>Note:</strong> This plugin is not activated for <em>back&#8209;end</em> (administrative) URLs. However, once activated, the language is remembered, even for the <em>back&#8209;end</em>.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (!MOD_REWRITE)?gettext('<em>mod_rewrite</em> must be enabled for this plugin to function.'):(getOption('dynamic_locale_subdomain') && getOption('zp_plugin_dynamic-locale'))?gettext('This plugin is not compatible with the <code>dynamic locale</code> <em>Use subdomains</em> option'):false;

if ($plugin_disable) {
	setOption('zp_plugin_seo_locale',0);
} else {
	zp_register_filter('load_request', 'seo_locale::load_request');
}
if (!defined('SEO_WEBPATH')) {
	define('SEO_WEBPATH',seo_locale::localePath());
	define('SEO_FULLWEBPATH',seo_locale::localePath(true));
}

class seo_locale {

	static function load_request($allow) {
		$uri = str_replace('\\','/',sanitize($_SERVER['REQUEST_URI'], 0));
		$parts = explode('?', $uri);
		$uri = urldecode($parts[0]);
		$path = ltrim(substr($uri, strlen(WEBPATH)+1),'/');
		if (empty($path)) {
			return $allow;
		} else {
			$rest = strpos($path, '/');
			if ($rest === false) {
				if (strpos($path,'?') === 0) {
					// only a parameter string
					return $allow;
				}
				$l = $path;
			} else {
				$l = substr($path,0,$rest);
			}
		}
		$locale = validateLocale($l, 'seo_locale');
		if ($locale) {
			// set the language cookie and redirect to the "base" url
			zp_setCookie('dynamic_locale', $locale);
			$uri = pathurlencode(preg_replace('|/'.$l.'[/$]|', '/', $uri));
			if (isset($parts[1])) {
				$uri .= '?'.$parts[1];
			}
			header("HTTP/1.0 302 Found");
			header("Status: 302 Found");
			header('Location: '.$uri);
			exitZP();
		}
		return $allow;
	}

	static function localePath($full=false) {
		global $_locale_Subdomains;
		if ($full) {
			$path = FULLWEBPATH;
		} else {
			$path =  WEBPATH;
		}
		if($locale = @$_locale_Subdomains[zp_getCookie('dynamic_locale')]) {
			$path .= '/'.$locale;
		}
		return $path;
	}

}

?>