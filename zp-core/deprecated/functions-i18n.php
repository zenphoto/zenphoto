<?php
/*if(!defined('SITE_LOCALE')) {
	if(function_exists('getOptionFromDB')) {
		define('SITE_LOCALE', getOptionFromDB('locale'));
	} else {
		define('SITE_LOCALE', 'en_US');
	}
}

if (function_exists('date_default_timezone_set')) { // insure a correct time zone
	$tz = getOption('time_zone');
	if (!empty($tz)) {
		$err = error_reporting(0);
		date_default_timezone_set($tz);
		@ini_set('date.timezone', $tz);
		error_reporting($err);
	}
	unset($tz);
} */
// force UTF-8 Ø
/**
 * functions-i18n.php -- support functions for internationalization
 * 
 * @deprecated 2.0 Use the class i18n instead
 * @package core
 * @subpackage functions\functions-i18n
 */

/**
 * 
 * @deprecated 2.0 Use i18n::getLanguageArray()
 * 
 * @return array
 */
function getLanguageArray() {
	deprecationNotice(gettext('Use i18n::getLanguageArray()'));
	return i18n::getLanguageArray();
}

/**
 * Returns an array of available language locales.
 * 
 * @deprecated 2.0 Use i18n::generateLanguageList()
 * 
 * @return array
 *
 */
function generateLanguageList($all = false) {
	deprecationNotice(gettext('Use i18n::generateLanguageList()'));
	return i18n::generateLanguageList($all);
}

/**
 * Sets the locale, etc. to the zenphoto domain details.
 * Returns the result of setupCurrentLocale()
 * 
 * @deprecated 2.0 Use i18n::setMainDomain()
 * 
 */
function setMainDomain() {
	deprecationNotice(gettext('Use i18n::setMainDomain()'));
	return i18n::setMainDomain();
}

/**
 * Gettext replacement function for separate translations of third party themes.
 * 
 * @deprecated 2.0 Use i18n::gettext_th()
 * 
 * @param string $string The string to be translated
 * @param string $theme The name of the plugin. Only required for strings on the 'theme_description.php' file like the general theme description. If the theme is the current theme the function sets it automatically.
 * @return string
 */
function gettext_th($string, $theme = Null) {
	deprecationNotice(gettext('Use i18n::gettext_th()'));
	return i18n::gettext_th($string, $theme);
}

/**
 * ngettext replacement function for separate translations of third party themes.
 * 
 * @deprecated 2.0 Use i18n::ngettext_th()
 * 
 * @param string $msgid1
 * @param string $msgid2
 * @param int $n
 * @param string $theme
 * @return string
 */
function ngettext_th($msgid1, $msgid2, $n, $theme = NULL) {
	deprecationNotice(gettext('Use i18n::ngettext_th()'));
	return i18n::ngettext_th($msgid1, $msgid2, $n, $theme);
}

/**
 * Gettext replacement function for separate translations of third party plugins within the root plugins folder.
 * 
 * @deprecated 2.0 Use i18n::gettext_pl()
 * 
 * @param string $string The string to be translated
 * @param string $plugin The name of the plugin. Required.
 * @return string
 */
function gettext_pl($string, $plugin) {
	deprecationNotice(gettext('Use i18n::gettext_pl()'));
	return i18n::gettext_pl($string, $plugin);
}

/**
 * ngettext replacement function for separate translations of third party plugins within the root plugins folder.
 * 
 * @deprecated 2.0 Use i18n::ngettext_pl()
 * 
 * @param string $msgid1
 * @param string $msgid2
 * @param int $n
 * @param string $plugin
 * @return string
 */
function ngettext_pl($msgid1, $msgid2, $n, $plugin) {
	deprecationNotice(gettext('Use i18n::ngettext_pl()'));
	return i18n::ngettext_pl($msgid1, $msgid2, $n, $plugin);
}

/**
 * Wrapper function for setLocale() so that all the proper permutations are used
 * Returns the result from the setLocale call
 * 
 * @deprecated 2.0 Use i18n::setLocale()
 * 
 * @param $locale the local desired
 * @return string
 */
function i18nSetLocale($locale) {
	deprecationNotice(gettext('Use i18n::setLocale()'));
	return i18n::setLocale($locale);
}

/**
 * Sets the translation domain and type for optional theme or plugin based translations
 * Each plugins and themes that wants to use a separate translation needs to setup (register) its domain once, 
 * for example right before or with the theme/plugin options.
 * 
 * @deprecated 2.0 Use i18n::setupDomain()
 * 
 * @param $domain If $type "plugin" or "theme" the folder name of the theme or plugin
 * @param $type NULL (Zenphoto main translation), "theme" or "plugin"
 */
function setupDomain($domain = NULL, $type = NULL) {
	deprecationNotice(gettext('Use i18n::setupDomain()'));
	i18n::setupDomain($domain, $type);
}

/**
 * Setup code for gettext translation
 * Returns the result of the setlocale call
 * 
 * @deprecated 2.0 Use i18n::setupCurrentLocale()
 * 
 * @param string $override force locale to this
 * @return mixed
 */
function setupCurrentLocale($override = NULL) {
		deprecationNotice(gettext('Use i18n::setupCurrentLocale()'));
	return i18n::setupCurrentLocale($override);
}

/**
 * This function will parse a given HTTP Accepted language instruction
 * (or retrieve it from $_SERVER if not provided) and will return a sorted
 * array. For example, it will parse fr;en-us;q=0.8
 *
 * Thanks to Fredbird.org for this code.
 * 
 * @deprecated 2.0 Use i18n::parseHttpAcceptLanguage()
 * 
 * @param string $str optional language string
 * @return array
 */
function parseHttpAcceptLanguage($str = NULL) {
	deprecationNotice(gettext('Use i18n::parseHttpAcceptLanguage()'));
	return i18n::parseHttpAcceptLanguage($str);
}

/**
 * checks a "supplied" locale against the valid locales.
 * Returns a valid locale if one exists else returns NULL
 * 
 * @deprecated 2.0 Use i18n::validateLocale()
 *  
 * @param string $userlocale
 */
function validateLocale($userlocale, $source) {
	deprecationNotice(gettext('Use i18n::validateLocale()'));
	return i18n::validateLocale($userlocale, $source);
}

/**
 * Returns a saved (or posted) locale. Posted locales are stored as a cookie.
 *
 * Sets the 'locale' option to the result (non-persistent)
 * 
 * @deprecated 2.0 Use i18n::getUserLocale()
 * 
 */
function getUserLocale() {
	deprecationNotice(gettext('Use i18n::getUserLocale()'));
	return i18n::getUserLocale();
}

/**
 * Returns the string for the current language from a serialized set of language strings
 * Defaults to the string for the current locale, the en_US string, or the first string which ever is present
 * 
 * @deprecated 2.0 Use i18n::getLanguageString()
 * 
 * @param string $dbstring either a serialized languag string array or a single string
 * @param string $locale optional locale of the translation desired
 * @return string
 */
function get_language_string($dbstring, $locale = NULL) {
	deprecationNotice(gettext('Use i18n::getLanguageString()'));
	return i18n::getLanguageString($dbstring, $locale);
}

/**
 * Returns a list of timezones
 * 
 * @deprecated 2.0 Use i18n::getTimezones()
 * 
 * @return unknown
 */
function getTimezones() {
	deprecationNotice(gettext('Use i18n::getTimezones()'));
	return i18n::getTimezones();
}

/**
 * Returns the difference between the server timez one and the local (users) time zone
 * 
 * @deprecated 2.0 Use i18n::timezoneDiff()
 * 
 * @param string $server
 * @param string $local
 * @return int
 */
function timezoneDiff($server, $local) {
	deprecationNotice(gettext('Use i18n::timezoneDiff()'));
	return i18n::timezoneDiff($server, $local);
}

/**
 * Returns a serialized "multilingual array" of translations of the currently active translations and if there is an gettext translation
 * Used for setting static multi-lingual strings in the db if a dynamic gettexted text doesn't work.
 * 
 * @deprecated 2.0 Use i18n::getAllTranslations()
 * 
 * @param string $text to be translated
 */
function getAllTranslations($text) {
	deprecationNotice(gettext('Use i18n::getAllTranslations()'));
	return i18n::getAllTranslations($text);
}


/**
 * Converts underscore locales like "en_US" to valid IANA/BCP 47 hyphen locales like "en-US"
 * Needed for example in JS or HTML "lang" attributes.
 * 
 * @since 1.5.7
 * 
 * @deprecated 2.0 Use i18n::getLangAttributeLocale()
 * 
 * @param string $locale a locale like "en_US", if empty the current locale is used
 * @return string
 */
function getLangAttributeLocale($locale = NULL) {
	deprecationNotice(gettext('Use i18n::getLangAttributeLocale()'));
	return i18n::getLangAttributeLocale($locale);
}


/**
 * Prints the lang="" attribute for the main <html> element with a trailing space is included.
 * 
 * @since 1.5.7
 * 
 * @deprecated 2.0 Use i18n::printLangAttribute(
 * 
 * @param string $locale Default null so the current locale is usd. Or a locale like "en_US" which will get the underscores replaces by hyphens to be valid
 */
function printLangAttribute($locale = null) {
	deprecationNotice(gettext('Use i18n::printLangAttribute()'));
	i18n::printLangAttribute($locale);
}

/**
 * Returns a locale aware - e.g. translated day and month names -  formatted date. Requires the PHP intl extension to work properly
 * Otherwise returns standard formatted date.
 * 
 * @since 1.6
 * @since 1.6.1 Parameter value requirements changed
 * 
 * @deprecated 2.0 Use i18n::getFormattedLocaleDate()
 * 
 * @param string $format An ICU dateformat string
 * @param string|int $datetime A date() compatible string or a timestamp. If empty "now" is used
 * @return string
 */
function getFormattedLocaleDate($format = 'Y-m-dd', $datetime = '') {
	deprecationNotice(gettext('Use i18n::getFormattedLocaleDate()'));
	return i18n::getFormattedLocaleDate($format, $datetime);
}


/**
 * Creates an SEO language prefix list
 * 
 * @deprecated 2.0 Use i18n::getLanguageSubdomains(
 * 
 */
function getLanguageSubdomains() {
	deprecationNotice(gettext('Use i18n::getLanguageSubdomains()'));
	return i18n::getLanguageSubdomains();
}


/**
 * Gets all locales suppported on the current server as a multidimensional array
 * 
 * @deprecated 2.0 Use i18n::getSystemLocales()
 * 
 * @param bool $plainarray Default false for a multidimensial array grouped by locale base. Set to true to generate a single dimensional array with all locales. 
 * 
 * @author Stephen Billard (sbillard), Malte Müller (acrylian) - adapted from the old former unsupported tool `list_locales.php`
 * @since 1.5.2
 * @return array
 */
function getSystemLocales($plainarray = false) {
	deprecationNotice(gettext('Use i18n::getSystemLocales()'));
	return i18n::getSystemLocales($plainarray);
}

/**
 * Returns the real language name to the locale passed.
 * 
 * If available it will use the native PHP Locale class. It returns the name in the language/locale currently set.
 * Otherwise the far more limited internal Zenphoto catalogue stored in getLanguageArray() will be used.
 * 
 * @since 1.5.2
 * 
 * @deprecated 2.0 Use i18n::getLanguageDisplayName()
 * 
 * @param string $locale A vaild locale.
 * @return string
 */
function getLanguageDisplayName($locale) {
	deprecationNotice(gettext('Use i18n::getLanguageDisplayName()'));
	return i18n::getLanguageDisplayName($locale);
}

/**
 * Returns a canonical language name string for the location
 * 
 * @deprecated 2.0 Use i18n::getLanguageText()
 * 
 * @param string $loc the location. If NULL use the current cookie
 * @param string separator will be used between the major and qualifier parts, e.g. en_US
 *
 * @return string
 */
function getLanguageText($loc = NULL, $separator = NULL) {
	deprecationNotice(gettext('Use i18n::getLanguageText()'));
	return i18n::getLanguageText($loc, $separator);
}

//$_zp_locale_subdomains = getLanguageSubdomains();