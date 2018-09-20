<?php

/**
 * Used to set the mod_rewrite option.
 * This script is accessed via a /page/setup_set-mod_rewrite?z=setup.
 * It will not be found unless mod_rewrite is working.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package setup
 *
 */
require_once(dirname(dirname(__FILE__)) . '/functions.php');
require_once(dirname(__FILE__) . '/setup-functions.php');
zp_session_start();
if (sanitize($_POST['errors'])) {
	$result = '<span class="logerror">' . gettext('Completed with errors') . '</span>';
} else {
	$result = gettext('Completed');
}
setupLog($result, true);
zp_apply_filter('log_setup', true, 'install', $result);
unset($_SESSION['SetupStarted']);
exitZP();
?>
