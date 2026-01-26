<?php
if(!defined('SITE_LOCALE')) {
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
}

require_once(SERVERPATH . '/' . ZENFOLDER . '/classes/class-i18n.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/deprecated/functions-i18n.php');
