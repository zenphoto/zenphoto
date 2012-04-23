<?php
/**
 * Plugin to automatically select themes based on the mobile device identification.
 *
 * Mobile devices are detected with {@link http://jquerymobile.com/gbs/ jquery Mobile }
 * Themes may be associated with each type of moble device OS.
 *
 * @package plugins
 */

$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext('Select your theme based on the device connecting to your site');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'mobileTheme';

zp_register_filter('setupTheme', 'mobileTheme::theme');

class mobileTheme {

	function __construct() {
	}

	function getOptionsSupported() {
		global $_zp_gallery;
		$themes = array();
		foreach ($_zp_gallery->getThemes() as $theme=>$details) {
			$themes[$details['name']] = $theme;
		}
		return  array(gettext('Phone theme') => array('key' => 'mobileTheme_phone', 'type' => OPTION_TYPE_SELECTOR,
														'selections'=>$themes,
														'null_selection' => gettext('gallery theme'),
														'desc' => gettext('Select the theme to be used when a phone device connects.')),
									gettext('Tablet theme') => array('key' => 'mobileTheme_tablet', 'type' => OPTION_TYPE_SELECTOR,
														'selections'=>$themes,
														'null_selection' => gettext('gallery theme'),
														'desc' => gettext('Select the theme to be used when a tablet device connects.'))
		);
	}

	function handleOption($option, $currentValue) {
	}

	static function theme($theme) {
		require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/mobileTheme/Mobile_Detect.php');
		$detect = new Mobile_Detect();
		if ($detect->isMobile()) {
			if ($detect->isTablet()) {
				$new = getOption('mobileTheme_tablet');
			} else {
				$new = getOption('mobileTheme_phone');
			}
		} else {
			$new = false;
		}
		if ($new) {
			$theme = $new;
		}
		return $theme;
	}
}


?>