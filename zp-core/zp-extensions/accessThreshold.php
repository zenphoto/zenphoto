<?php

/**
 * This plugin monitors front-end access and shuts down responses when a particular
 * IP sub-network tries to flood the gallery with requests.
 *
 * The sensitivity of the check can be changed by changing the <code>SENSITIVITY>/code> definition.
 * 	4 will resolve to the Host
 *  3 will resolve to the Sub-net
 *  2 will resolve to the Network
 *
 * This definition is used rather than an option to avoid database access as one ot the
 * flooding attacks it to excede the query limit of the database.
 *
 * @author Stephen Billard (sbillard)
 * @Copyright 2016 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage development
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
			setOptionDefault('accessThreshold_LIMIT', 100);
//clear out the recentIP array
			setOption('accessThreshold_CLEAR', 1);
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
				gettext('Limit') => array('key' => 'accessThreshold_LIMIT', 'type' => OPTION_TYPE_NUMBER,
						'order' => 5,
						'desc' => sprintf(gettext('The list will be limited to the top %d accesses.'), getOption('accessThreshold_LIMIT'))),
				gettext('Clear list') => array('key' => 'accessThreshold_CLEAR', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 6,
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
				'accessThreshold_SENSITIVITY' => $sensitivity
		);
		file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP', serialize($recentIP));
	}

	static function admin_tabs($tabs) {
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
		if ($__previous) {
			$__interval = $__interval + ($v - $__previous );
		}
		$__previous = $v;
	}

}

if (OFFSET_PATH) {
	zp_register_filter('admin_tabs', 'accessThreshold::admin_tabs');
} else {
	$__time = time();
	$recentIP = getSerializedArray(@file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP'));
	if (array_key_exists('config', $recentIP)) {
		$accessThreshold_IP_RETENTION = $recentIP['config']['accessThreshold_IP_RETENTION'];
		$accessThreshold_THRESHOLD = $recentIP['config']['accessThreshold_THRESHOLD'];
		$accessThreshold_IP_ACCESS_WINDOW = $recentIP['config']['accessThreshold_IP_ACCESS_WINDOW'];
		$accessThreshold_SENSITIVITY = $recentIP['config']['accessThreshold_SENSITIVITY'];

		$ip = getUserIP();
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
			$recentIP[$ip]['blocked'] = false;
		}
		$recentIP[$ip]['lastAccessed'] = $__time;
		if (@$recentIP[$ip]['blocked']) {
			exitZP();
		} else {
			$recentIP[$ip]['accessed'][] = $__time;
			array_walk($recentIP[$ip]['accessed'], 'accessThreshold::walk');
			if (($recentIP[$ip]['interval'] = $__interval / count($recentIP[$ip]['accessed'])) < $accessThreshold_THRESHOLD && count($recentIP[$ip]['accessed']) >= 10) {
				$recentIP[$ip]['blocked'] = true;
			}
		}
		if (count($recentIP) > $accessThreshold_IP_RETENTION) {
			$recentIP = array_shift(sortMultiArray($recentIP, array('lastAccessed'), true, true, false, true));
		}
		file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP', serialize($recentIP));

		unset($ip);
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