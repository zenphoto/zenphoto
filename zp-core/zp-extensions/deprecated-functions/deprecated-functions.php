<?php
/**
 * @package plugins
 * @subpackage deprecated-functions
 */

/**
 * Zenphoto general deprecated functions
 *
 * @package plugins
 * @subpackage deprecated-functions
 */
class internal_deprecations {

	/**
	 * @deprecated 2.0 Use getLanguageSubdomains() instead
	 * @since 1.5
	 */
	static function LanguageSubdomains() {
		deprecated_functions::notify(gettext('Use getLanguageSubdomains() instead'));
	}
	/**
	 * @deprecated 2.0 Use getLanguageText() instead
	 * @since 1.5
	 */
	static function getLanguageText() {
		deprecated_functions::notify(gettext('Use getLanguageText() instead'));
	}
	/**
	 * @deprecated 2.0 Use setexifvars() instead
	 * @since 1.5
	 */
	static function setexifvars() {
		deprecated_functions::notify(gettext('Use setexifvars() instead'));
	}
	/**
	 * @deprecated 2.0 Use hasPrimaryScripts() instead
	 * @since 1.5
	 */
	static function hasPrimaryScripts() {
		deprecated_functions::notify(gettext('Use removeTrailingSlash() instead'));
	}
	/**
	 * @deprecated 2.0 Use removeDir() instead
	 * @since 1.5
	 */
	static function removeDir() {
		deprecated_functions::notify(gettext('Use removeDir() instead'));
	}
	/**
	 * @deprecated 2.0 Use tagURLs() instead
	 * @since 1.5
	 */
	static function tagURLs() {
		deprecated_functions::notify(gettext('Use tagURLs() instead'));
	}
	/**
	 * @deprecated 2.0 Use unTagURLs() instead
	 * @since 1.5
	 */
	static function unTagURLs() {
		deprecated_functions::notify(gettext('Use unTagURLs() instead'));
	}

	/**
	 * @deprecated 2.0 Use updateImageProcessorLink() instead
	 * @since 1.5
	 */
	static function updateImageProcessorLink() {
		deprecated_functions::notify(gettext('Use updateImageProcessorLink() instead'));
	}
	/**
	 * @deprecated 2.0 Use pluginDebug() instead
	 * @since 1.5
	 */
	static function pluginDebug() {
		deprecated_functions::notify(gettext('Use pluginDebug() instead'));
	}

	/**
	 * @deprecated 2.0 Use removeTrailingSlash() instead
	 * @since 1.5
	 */
	static function removeTrailingSlash($string) {
		deprecated_functions::notify(gettext('Use removeTrailingSlash() instead'));
	}

	/**
	 * @deprecated 2.0 Use htmlTidy() instead
	 * @since 1.5
	 */
	static function tidyHTML() {
		deprecated_functions::notify(gettext('Use tidyHTML() instead'));
	}

	/**
	 * @deprecated 2.0 Use instantiate() method
	 * @since 1.4.6
	 */
	static function PersistentObject() {
		deprecated_functions::notify(gettext('Use the instantiate method instead'));
	}

}

/**
 *
 * fixes unbalanced HTML tags. Used by shortenContent when PHP tidy is not present
 *
 * @deprecated 2.0 Use tidyHTML() instead
 * @since 1.5
 *
 * @param string $html
 * @return string
 */
function cleanHTML($html) {
	deprecated_functions::notify(gettext("Use tidyHTML() instead"));
	return tidyHTML($html);
}

/**
 * @deprecated 2.0
 * @since 1.5
 */
class zpFunctions {

	/**
	 * @deprecated 2.0 Use getLanguageSubdomains()
	 * @since 1.5
	 * @see getLanguageSubdomains()
	 */
	static function LanguageSubdomains() {
		internal_deprecations::LanguageSubdomains();
		return getLanguageSubdomains();
	}

	/**
	 * @deprecated 2.0 Use getLanguageText()
	 * @since 1.5
	 * @see getLanguageText()
	 */
	static function getLanguageText($loc = NULL, $separator = NULL) {
		internal_deprecations::getLanguageText();
		return getLanguageText($loc, $separator);
	}

	/**
	 * @deprecated 2.0 Use setexifvars()
	 * @since 1.5
	 * @see setexifvars()
	 */
	static function setexifvars() {
		internal_deprecations::setexifvars();
		setexifvars();
	}

	/**
	 * @deprecated 2.0 Use hasPrimaryScripts()
	 * @since 1.5
	 * @see hasPrimaryScripts()
	 */
	static function hasPrimaryScripts() {
		internal_deprecations::hasPrimaryScripts();
		return hasPrimaryScripts();
	}

	/**
	 * @deprecated 2.0 Use removeDir()
	 * @since 1.5
	 * @see removeDir()
	 */
	static function removeDir($path, $within = false) {
		internal_deprecations::removeDir();
		return removeDir($path, $within);
	}

	/**
	 * @deprecated 2.0 Use tagURLs()
	 * @since 1.5
	 * @see tagURLs()
	 */
	static function tagURLs($text) {
		internal_deprecations::tagURLs();
		return tagURLs($text);
	}

	/**
	 * @deprecated 2.0 Use untagURLs()
	 * @since 1.5
	 * @see untagURLs()
	 */
	static function unTagURLs($text) {
		internal_deprecations::unTagURLs();
		return unTagURLs($text);
	}

	/**
	 * @deprecated 2.0 Use updateImageProcessorLink()
	 * @since 1.5
	 * @see updateImageProcessorLink()
	 */
	static function updateImageProcessorLink($text) {
		internal_deprecations::updateImageProcessorLink();
		return updateImageProcessorLink($text);
	}

	/**
	 * @deprecated 2.0 Use pluginDebug()
	 * @since 1.5
	 * @see pluginDebug()
	 */
	static function pluginDebug($extension, $priority, $start) {
		internal_deprecations::pluginDebug();
		pluginDebug($extension, $priority, $start);
	}

	/**
	 * @deprecated 2.0 Use removeTrailingSlash()
	 * @since 1.5
	 * @see removeTrailingSlash()
	 */
	static function removeTrailingSlash($string) {
		internal_deprecations::removeTrailingSlash();
		return removeTrailingSlash($string);
	}

	/**
	 * @deprecated 2.0 Use tidyHTML()
	 * @since 1.5
	 * @see tidyHTML()
	 */
	static function tidyHTML($html) {
		internal_deprecations::tidyHTML();
		return tidyHTML($html);
	}

}