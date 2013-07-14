<?php
/**
 * Changes <i>white space</i> characters to hyphens.
 * @package plugins
 * @subpackage seo
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('SEO <em>Null</em> filter.');
$plugin_notice = gettext('The only translation performed is one or more <em>white space</em> characters are converted to a <em>hyphen</em>.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (zp_has_filter('seoFriendly') && !extensionEnabled('seo_null'))?sprintf(gettext('Only one SEO filter plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'),stripSuffix(get_filterScript('seoFriendly'))):'';

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
		$string = preg_replace("/\s+/","-",$string);
		return $string;
	}

	static function js($string) {
		$js = "
			function seoFriendlyJS(fname) {
				fname = fname.replace(/\s+/g, '-');
				return fname;
			}\n";
		return $js;
	}

}
?>