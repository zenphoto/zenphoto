<?php

/**
 * This plugin monitors front-end access and shuts down responses when a particular
 * source tries to flood the gallery with requests.
 *
 * A mask is used to control the scope of the data collection. For a IPv4 addresses
 * 	255.255.255.255 will resolve to the Host.
 *  255.255.255.0 will resolve to the Sub-net (data for all hosts in the Sub-net are grouped.)
 *  255.255.0.0 will resolve to the Network (data for the Newtork is grouped.)
 *
 * Access data is not acted upon until there is at least 10 access attempts. This insures
 * that flooding is not prematurely indicated.
 *
 * @author Stephen Billard (sbillard)
 * @Copyright 2016 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 990 | FEATURE_PLUGIN;
$plugin_description = gettext("Tools to block denial of service attacks.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'accessThreshold';

class accessThreshold {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('accessThreshold_IP_RETENTION', 500);
			setOptionDefault('accessThreshold_THRESHOLD', 5);
			setOptionDefault('accessThreshold_IP_ACCESS_WINDOW', 3600);
			setOptionDefault('accessThreshold_SENSITIVITY', '255.255.255.0');
			setOptionDefault('accessThreshold_LocaleCount', 5);
			setOptionDefault('accessThreshold_LIMIT', 100);
			if (!isset($_GET['from']) || version_compare($_GET['from'], '1.2.6.32', '<')) {
				//clear out the recentIP array
				setOption('accessThreshold_CLEAR', 1);
			}
			self::handleOptionSave(NULL, NULL);
		}
	}

	function getOptionsSupported() {
		$options = array(
				gettext('Memory') => array('key' => 'accessThreshold_IP_RETENTION', 'type' => OPTION_TYPE_NUMBER,
						'order' => 1,
						'desc' => gettext('The number unique access attempts to keep.')),
				gettext('Threshold') => array('key' => 'accessThreshold_THRESHOLD', 'type' => OPTION_TYPE_NUMBER,
						'order' => 2,
						'desc' => gettext('Attempts will be blocked if the average access interval is less than this number of seconds.')),
				gettext('Window') => array('key' => 'accessThreshold_IP_ACCESS_WINDOW', 'type' => OPTION_TYPE_NUMBER,
						'order' => 3,
						'desc' => gettext('The access interval is reset if the last access is beyond this window.')),
				gettext('Mask') => array('key' => 'accessThreshold_SENSITIVITY', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 4,
						'desc' => gettext('IP mask to determine the IP elements sensitivity')),
				gettext('Locale limit') => array('key' => 'accessThreshold_LocaleCount', 'type' => OPTION_TYPE_NUMBER,
						'order' => 5,
						'desc' => sprintf(gettext('Requests will be blocked if more than %d locales are requested.'), getOption('accessThreshold_LocaleCount'))),
				gettext('Limit') => array('key' => 'accessThreshold_LIMIT', 'type' => OPTION_TYPE_NUMBER,
						'order' => 6,
						'desc' => sprintf(gettext('The list will be limited to the top %d accesses.'), getOption('accessThreshold_LIMIT'))),
				gettext('Clear list') => array('key' => 'accessThreshold_CLEAR', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 99,
						'desc' => gettext('Clear the access list.'))
		);
		return $options;
	}

	static function handleOptionSave($themename, $themealbum) {
		$x = str_replace(':', '.', getOption('accessThreshold_SENSITIVITY'));
		$sensitivity = 0;
		foreach (explode('.', $x) as $v) {
			if ($v) {
				$sensitivity++;
			} else {
				break;
			}
		}
		if (getOption('accessThreshold_CLEAR')) {
			$recentIP = array();
			setOption('accessThreshold_CLEAR', 0);
		} else {
			$recentIP = getSerializedArray(@file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP'));
		}
		$recentIP['config'] = array(
				'accessThreshold_IP_RETENTION' => getOption('accessThreshold_IP_RETENTION'),
				'accessThreshold_THRESHOLD' => getOption('accessThreshold_THRESHOLD'),
				'accessThreshold_IP_ACCESS_WINDOW' => getOption('accessThreshold_IP_ACCESS_WINDOW'),
				'accessThreshold_LocaleCount' => getOption('accessThreshold_LocaleCount'),
				'accessThreshold_SENSITIVITY' => $sensitivity
		);
		file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP', serialize($recentIP));
	}

	static function admin_tabs($tabs) {
		global $_zp_current_admin_obj;
		if ((zp_loggedin(ADMIN_RIGHTS) && $_zp_current_admin_obj->getID())) {
			if (isset($tabs['users']['subtabs'])) {
				$subtabs = $tabs['users']['subtabs'];
			} else {
				$subtabs = array(
						gettext('users') => 'admin-users.php?page=users&tab=users'
				);
			}
			$subtabs[gettext("access")] = PLUGIN_FOLDER . '/accessThreshold/admin_tab.php?page=users&tab=access';
			ksort($subtabs, SORT_LOCALE_STRING);
			$tabs['users'] = array('text' => gettext("admin"),
					'link' => WEBPATH . "/" . ZENFOLDER . '/admin-users.php?page=users&tab=users',
					'subtabs' => $subtabs,
					'default' => 'users');
		}
		return $tabs;

		if (zp_loggedin(ADMIN_RIGHTS)) {
			if (!isset($tabs['development'])) {
				$tabs['development'] = array('text' => gettext("development"),
						'subtabs' => NULL);
			}
			$tabs['development']['subtabs'][gettext("accessThreshold")] = PLUGIN_FOLDER . '/accessThreshold/admin_tab.php?page=development&tab=accessThreshold';
			$named = array_flip($tabs['development']['subtabs']);
			natcasesort($named);
			$tabs['development']['subtabs'] = $named = array_flip($named);
			$link = array_shift($named);
			if (strpos($link, '/') !== 0) { // zp_core relative
				$tabs['development']['link'] = WEBPATH . '/' . ZENFOLDER . '/' . $link;
			} else {
				$tabs['development']['link'] = WEBPATH . $link;
			}
		}
		return $tabs;
	}

	static function walk($v, $key) {
		global $__previous, $__interval;
		if (is_array($v)) {
			$v = $v['time'];
		}
		if ($__previous) {
			$__interval = $__interval + ($v - $__previous );
		}
		$__previous = $v;
	}

}

if (OFFSET_PATH) {
	zp_register_filter('admin_tabs', 'accessThreshold::admin_tabs');
} else {
	$mu = new zpMutex('aT');
	$mu->lock();
	$__time = time();
	$recentIP = getSerializedArray(@file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP'));
	if (array_key_exists('config', $recentIP)) {
		$accessThreshold_IP_RETENTION = $recentIP['config']['accessThreshold_IP_RETENTION'];
		$accessThreshold_THRESHOLD = $recentIP['config']['accessThreshold_THRESHOLD'];
		$accessThreshold_IP_ACCESS_WINDOW = $recentIP['config']['accessThreshold_IP_ACCESS_WINDOW'];
		$accessThreshold_SENSITIVITY = $recentIP['config']['accessThreshold_SENSITIVITY'];
		if (array_key_exists('accessThreshold_LocaleCount', $recentIP['config'])) {
			$accessThreshold_LocaleCount = $recentIP['config']['accessThreshold_LocaleCount'];
		} else {
			$accessThreshold_LocaleCount = 5;
		}

		$full_ip = $ip = getUserIP();
		if (strpos($ip, '.') === false) {
			$separator = ':';
		} else {
			$separator = '.';
		}
		$x = explode($separator, $ip);
		$x = array_slice($x, 0, $accessThreshold_SENSITIVITY);
		$ip = implode($separator, $x);

		if (isset($recentIP[$ip]['lastAccessed']) && $recentIP[$ip]['lastAccessed'] < $__time - $accessThreshold_IP_ACCESS_WINDOW) {
			$recentIP[$ip]['accessed'] = array();
			$recentIP[$ip]['locales'] = array();
			$recentIP[$ip]['blocked'] = false;
		}
		$recentIP[$ip]['lastAccessed'] = $__time;
		if (@$recentIP[$ip]['blocked']) {
			$mu->unlock();
			exitZP();
		} else {
			$recentIP[$ip]['accessed'][] = array('time' => $__time, 'ip' => $full_ip);
			$recentIP[$ip]['locales'][getUserLocale()] = 1;
			array_walk($recentIP[$ip]['accessed'], 'accessThreshold::walk');
			if ((count($recentIP[$ip]['locales']) > $accessThreshold_LocaleCount) || (($recentIP[$ip]['interval'] = $__interval / count($recentIP[$ip]['accessed'])) < $accessThreshold_THRESHOLD && count($recentIP[$ip]['accessed']) >= 10)) {
				$recentIP[$ip]['blocked'] = true;
			}
		}
		if (count($recentIP) > $accessThreshold_IP_RETENTION) {
			$recentIP = array_shift(sortMultiArray($recentIP, array('lastAccessed'), true, true, false, true));
		}
		file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP', serialize($recentIP));
		$mu->unlock();

		unset($ip);
		unset($full_ip);
		unset($recentIP);
		unset($__time);
		unset($__interval);
		unset($__previous);
		unset($accessThreshold_IP_RETENTION);
		unset($accessThreshold_THRESHOLD);
		unset($accessThreshold_IP_ACCESS_WINDOW);
		unset($accessThreshold_SENSITIVITY);
	}
}
?>