<?php
/**
 * Provides for using URLs to force language selection.
 * This filter will detect a language setting from the URI and
 * set the locale accordingly.
 *
 * The URL format is:
 * mod_rewrite
 *			/<languageid>/<standard url>
 * else
 * 			<standard url>?locale=<languageid>
 * Where <languageid> is the local identifier (e.g. en, en_US, fr_FR, etc.)
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */

$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext("Allows setting language locale through the URI.").
											'<p class="notebox">'.gettext('<strong>Note:</strong> This plugin is not activated for <em>back&#8209;end</em> (administrative) URLs. However, once activated, the language is remembered, even for the <em>back&#8209;end</em>.').'</p>';
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (!MOD_REWRITE)?gettext('<em>mod_rewrite</em> must be enabled for this plugin to function.'):false;
$plugin_version = '1.4.1';

if ($plugin_disable) {
	setOption('zp_plugin_seo_locale',0);
} else {
	zp_register_filter('load_request', 'filterLocale_load_request');
}

function filterLocale_load_request($allow) {
	$uri = urldecode(sanitize($_SERVER['REQUEST_URI'], 0));
	$path = substr($uri, strlen(WEBPATH)+1);
	$path = str_replace('\\','/',$path);
	if (substr($path,0,1) == '/') $path = substr($path,1);
	if (empty($path)) {
		return $allow;
	} else {
		$rest = strpos($path, '/');
		if ($rest === false) {
			if (strpos($path,'?') === 0) {	// only a parameter string
				return $allow;
			}
			$l = $path;
		} else {
			$l = substr($path,0,$rest);
		}
	}
	$locale = validateLocale($l, 'seo_locale');
	if ($locale) {	// set the language cookie and redirect to the "base" url
		zp_setCookie('dynamic_locale', $locale);
		if (substr($path, -1, 1) == '/') $path = substr($path, 0, strlen($path)-1);
		$path = FULLWEBPATH.substr($path, strlen($l));
		header("HTTP/1.0 302 Found");
		header("Status: 302 Found");
		header('Location: '.$path);
		exit();
	}
	return $allow;
}
?>