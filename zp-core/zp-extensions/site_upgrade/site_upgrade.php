<?php

define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/functions-config.php');

admin_securityChecks(ALBUM_RIGHTS, currentRelativeURL());

switch (isset($_GET['siteState']) ? $_GET['siteState'] : NULL) {
	case 'closed':
		$report = '';
		setSiteState('closed');
		zp_apply_filter('security_misc', true, 'site_upgrade', 'zp_admin_auth', 'closed');

		if (extensionEnabled('cloneZenphoto')) {
			require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cloneZenphoto.php');
			if (class_exists('cloneZenphoto')) {
				$clones = cloneZenphoto::clones();
				foreach ($clones as $clone => $data) {
					setSiteState('closed', $clone . '/');
				}
			}
		}
		break;
	case 'open':
		$report = gettext('Site is viewable.');
		setSiteState('open');
		zp_apply_filter('security_misc', true, 'site_upgrade', 'zp_admin_auth', 'open');

		if (extensionEnabled('cloneZenphoto')) {
			require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cloneZenphoto.php');
			if (class_exists('cloneZenphoto')) {
				$clones = cloneZenphoto::clones();
				foreach ($clones as $clone => $data) {
					setSiteState('open', $clone . '/');
				}
			}
		}
		break;
	case 'closed_for_test':
		$report = '';
		setSiteState('closed_for_test');
		zp_apply_filter('security_misc', true, 'site_upgrade', 'zp_admin_auth', 'closed_for_test');

		if (extensionEnabled('cloneZenphoto')) {
			require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cloneZenphoto.php');
			if (class_exists('cloneZenphoto')) {
				$clones = cloneZenphoto::clones();
				foreach ($clones as $clone => $data) {
					setSiteState('closed_for_test', $clone . '/');
				}
			}
		}
		break;
}

header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?report=' . $report);
exitZP();

/**
 * updates the site status
 * @param string $state
 */
function setSiteState($state, $folder = NULL) {
	if (is_null($folder)) {
		$folder = SERVERPATH . '/';
	}
	$_configMutex = new zpMutex('cF', NULL, $folder);
	$_configMutex->lock();
	$zp_cfg = @file_get_contents($folder . DATA_FOLDER . '/' . CONFIGFILE);
	$zp_cfg = updateConfigItem('site_upgrade_state', $state, $zp_cfg);
	storeConfig($zp_cfg, $folder);
	$_configMutex->unlock();
}

?>