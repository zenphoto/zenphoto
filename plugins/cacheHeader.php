<?php

/**
 * Use for installations where the caching of pages is causing problems of expired
 * pages being delivered.
 *
 * In its default configuration this plugin will prevent caching of all class-page pages by
 * any caching agent in the path.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage example
 * @category package
 * @category ZenPhoto20Tools
 */
$plugin_is_filter = 9 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext('Outputs a "Cache-control" header with selected caching options.');
$plugin_author = "Stephen Billard (sbillard)";
$option_interface = 'cacheHeader_options';

$_cacheHeader_side = getOption('cacheHeader_sides');
if ($_cacheHeader_side == 'admin' || $_cacheHeader_side == 'all')
	zp_register_filter('admin_headers', 'cacheHeader');
if ($_cacheHeader_side == 'gallery' || $_cacheHeader_side == 'all')
	zp_register_filter('theme_headers', 'cacheHeader');
zp_register_filter('plugin_tabs', 'cacheHeader_options::tab');
unset($_cacheHeader_side);

class cacheHeader_options {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('cacheHeader_store', 1);
			setOptionDefault('cacheHeader_cache', 1);
			setOptionDefault('cacheHeader_max-age', 0);
			setOptionDefault('cacheHeader_sides', 'all');
		}
	}

	function getOptionsSupported() {
		return array(gettext('Store') => array('key' => 'cacheHeader_store', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 2,
						'desc' => gettext('Lets you specify that caches should not store this response.')),
				gettext('Cache') => array('key' => 'cacheHeader_cache', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 1,
						'desc' => gettext('Lets you specify that caches should revalidate this resource every time.')),
				gettext('Max-age') => array('key' => 'cacheHeader_max-age', 'type' => OPTION_TYPE_NUMBER,
						'order' => 5,
						'desc' => gettext('You may set a max-age, in seconds, which will override the expires header.')),
				gettext('Cache') => array('key' => 'cacheHeader_sides', 'type' => OPTION_TYPE_RADIO,
						'buttons' => array(gettext('Gallery') => 'gallery', gettext('Admin') => 'admin', gettext('All') => 'all'),
						'order' => 0,
						'desc' => gettext('Select where to apply this header')),
				gettext('Header') => array('key' => 'cacheHeader_example', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 6,
						'desc' => gettext('The header that will be generated'))
		);
	}

	function handleOption($option, $currentValue) {
		$head = 'Cache-Control:';
		if (getOption('cacheHeader_store')) {
			$head .= ' no-store,';
		}
		if (getOption('cacheHeader_cache')) {
			$head .= ' no-cache,';
		}
		$head .= ' must-revalidate, max-age=' . getOption('cacheHeader_max-age');
		echo 'header("' . $head . '");';
	}

	static function tab($xlate) {
		$xlate['demo'] = gettext('demo');
		return $xlate;
	}

}

function cacheHeader() {
	$head = 'Cache-Control:';
	if (getOption('cacheHeader_store')) {
		$head .= ' no-store,';
	}
	if (getOption('cacheHeader_cache')) {
		$head .= ' no-cache,';
	}
	$head .= ' must-revalidate, max-age=' . getOption('cacheHeader_max-age');
	header($head);
}

?>