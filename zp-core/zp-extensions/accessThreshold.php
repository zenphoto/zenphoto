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
 * @Copyright 2015 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage development
 */
$plugin_is_filter = 990 | FEATURE_PLUGIN;
$plugin_description = gettext("Tools to block denial of service attacks.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'accessThreshold';

if (OFFSET_PATH) {
	zp_register_filter('admin_tabs', 'accessThreshold::admin_tabs');

	class accessThreshold {

		function __construct() {
			if (OFFSET_PATH == 2) {
				setOptionDefault('accessThreshold_IP_RETENTION', 500);
				setOptionDefault('accessThreshold_IP_THRESHOLD', 5000);
				setOptionDefault('accessThreshold_IP_ACCESS_WINDOW', 600);
				setOptionDefault('accessThreshold_SENSITIVITY', '255.255.255.0.0');
				//clear out the recentIP array
				self::handleOptionSave(NULL, NULL);
			}
		}

		function getOptionsSupported() {
			$options = array(
							gettext('Memory')		 => array('key'		 => 'accessThreshold_IP_RETENTION', 'type'	 => OPTION_TYPE_NUMBER,
											'order'	 => 1,
											'desc'	 => gettext('The number unique access attempts to keep.')),
							gettext('Threshold') => array('key'		 => 'accessThreshold_IP_THRESHOLD', 'type'	 => OPTION_TYPE_NUMBER,
											'order'	 => 2,
											'desc'	 => gettext('Attempts will be blocked once the count reaches this level.')),
							gettext('Window')		 => array('key'		 => 'accessThreshold_IP_ACCESS_WINDOW', 'type'	 => OPTION_TYPE_NUMBER,
											'order'	 => 3,
											'desc'	 => gettext('The access counter is reset if the last access is beyond this window.')),
							gettext('Mask')			 => array('key'		 => 'accessThreshold_SENSITIVITY', 'type'	 => OPTION_TYPE_TEXTBOX,
											'order'	 => 4,
											'desc'	 => gettext('IP mask to determine the IP elements sensitivity'))
			);
			return $options;
		}

		static function handleOptionSave($themename, $themealbum) {
			$x = getOption('accessThreshold_SENSITIVITY');
			$sensitivity = 0;
			foreach (explode('.', $x) as $v) {
				if ($v) {
					$sensitivity++;
				} else {
					break;
				}
			}

			$recentIP = array(
							'config' => array(
											'accessThreshold_IP_RETENTION'		 => getOption('accessThreshold_IP_RETENTION'),
											'accessThreshold_IP_THRESHOLD'		 => getOption('accessThreshold_IP_THRESHOLD'),
											'accessThreshold_IP_ACCESS_WINDOW' => getOption('accessThreshold_IP_ACCESS_WINDOW'),
											'accessThreshold_SENSITIVITY'			 => $sensitivity
							)
			);
			file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP', serialize($recentIP));
		}

		static function admin_tabs($tabs) {
			if (zp_loggedin(ADMIN_RIGHTS)) {
				if (!isset($tabs['development'])) {
					$tabs['development'] = array('text'		 => gettext("development"),
									'subtabs'	 => NULL);
				}
				$tabs['development']['subtabs'][gettext("accessThreshold")] = PLUGIN_FOLDER . '/accessThreshold/admin_tab.php?page=development&tab=' . gettext('accessThreshold');
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

	}

} else {
	$recentIP = getSerializedArray(@file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP'));
	if (array_key_exists('config', $recentIP)) {
		$accessThreshold_IP_RETENTION = $recentIP['config']['accessThreshold_IP_RETENTION'];
		$accessThreshold_IP_THRESHOLD = $recentIP['config']['accessThreshold_IP_THRESHOLD'];
		$accessThreshold_IP_ACCESS_WINDOW = $recentIP['config']['accessThreshold_IP_ACCESS_WINDOW'];
		$accessThreshold_SENSITIVITY = $recentIP['config']['accessThreshold_SENSITIVITY'];

		$x = explode('.', getUserIP());
		$x = array_slice($x, 0, $accessThreshold_SENSITIVITY);
		$ip = implode(".", $x);

		if (array_key_exists($ip, $recentIP) && $recentIP[$ip]['accessTime'] > time() - $accessThreshold_IP_ACCESS_WINDOW) {
			$recentIP[$ip]['counter'] ++;
		} else {
			$recentIP[$ip] = array('accessTime' => time(), 'counter' => 1);
			if (count($recentIP) > $accessThreshold_IP_RETENTION) {
				array_shift($recentIP);
			}
		}
		file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP', serialize($recentIP));
		if ($recentIP[$ip]['counter'] > $accessThreshold_IP_THRESHOLD) {
			zp_error(gettext('Access threshold exceeded.'), E_USER_NOTICE);
			exitZP();
		}
	}
}
?>