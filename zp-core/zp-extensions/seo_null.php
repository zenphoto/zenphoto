<?php

/**
 * Changes <i>white space</i> characters to hyphens. Bypasses the standard replacement of
 * non-ascii characaters with a hyphen
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/seo_null
 * @pluginCategory seo
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('SEO <em>Null</em> filter.');
$plugin_notice = gettext('The only translation performed is one or more <em>white space</em> characters are converted to a <em>hyphen</em>.');

zp_register_filter('seoFriendly', 'null_seo::filter');
zp_register_filter('seoFriendly_js', 'null_seo::js');

/**
 * Option handler class
 *
 */
class null_seo {

	/**
	 * class instantiation function
	 *
	 * @return zenphoto_seo
	 */
	function __construct() {

	}

	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {

	}

	function handleOption($option, $currentValue) {

	}

	/**
	 * translates whitespace to underscore
	 *
	 * @param string $string
	 * @return string
	 */
	static function filter($string) {
		return $string;
	}

	static function js($string) {
		return $string;
	}

}

?>