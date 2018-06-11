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
list($usec, $sec) = explode(" ", microtime());
$start = (float) $usec + (float) $sec;

require_once(dirname(dirname(__FILE__)) . '/functions-basic.php');
require_once(dirname(__FILE__) . '/setup-functions.php');
$testRelease = $_SESSION['testrelease'];

setupLog(sprintf(gettext('Mod_rewrite setup started')), $testRelease);

$mod_rewrite = MOD_REWRITE;
if (is_null($mod_rewrite)) {
	$msg = gettext('The option “mod_rewrite” will be set to “enabled”.');
	setOption('mod_rewrite', 1);
} else if ($mod_rewrite) {
	$msg = gettext('The option “mod_rewrite” is “enabled”.');
} else {
	$msg = gettext('The option “mod_rewrite” is “disabled”.');
}
setOption('mod_rewrite_detected', 1);
setupLog(gettext('Notice: “Module mod_rewrite” is working.') . ' ' . $msg, $testRelease);

list($usec, $sec) = explode(" ", microtime());
$last = (float) $usec + (float) $sec;
/* and record that we finished */
setupLog(sprintf(gettext('Mod_rewrite setup completed in %1$.4f seconds'), $last - $start), $testRelease);

sendImage(false, 'mod_rewrite');
exitZP();
?>