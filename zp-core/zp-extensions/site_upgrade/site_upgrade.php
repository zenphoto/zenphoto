<?php

define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/functions-config.php');

admin_securityChecks(ALBUM_RIGHTS, currentRelativeURL());

switch (isset($_GET['siteState']) ? $_GET['siteState'] : NULL) {
	case 'closed':
		$report = gettext('Site is now marked in upgrade.');
		setSiteState('closed');
		break;
	case 'open':
		$report = gettext('Site is viewable.');
		setSiteState('open');
		break;
	case 'closed_for_test':
		$report = gettext('Site is avaiable for testing only.');
		setSiteState('closed_for_test');
		break;
}

header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?report=' . $report);
exitZP();

/**
 * updates the site status
 * @param string $state
 */
function setSiteState($state) {
	global $_configMutex;
	$_configMutex->lock();
	$zp_cfg = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
	$zp_cfg = updateConfigItem('site_upgrade_state', $state, $zp_cfg);
	storeConfig($zp_cfg);
	$_configMutex->unlock();
}

?>