<?php
/**
 * Used to set the mod_rewrite option.
 * This script is accessed via a /page/setup_set-mod_rewrite?z.
 * It will not be found unless mod_rewrite is working.
 *
 * @package setup
 *
 */
require_once(dirname(__FILE__).'/functions-basic.php');
$mod_rewrite = getOption('mod_rewrite');
if (is_null($mod_rewrite)) {
	$msg = gettext('The Zenphoto option "mod_rewrite" will be set to "enabled".');
	setOption('mod_rewrite', 1);
} else if ($mod_rewrite) {
	$msg = gettext('The Zenphoto option "mod_rewrite" is "enabled".');
} else {
	$msg = gettext('The Zenphoto option "mod_rewrite" is "disabled".');
}
$f = fopen(SERVERPATH.'/'.DATA_FOLDER . '/setup_log.txt', 'a');
fwrite($f, gettext('Notice: "Module mod_rewrite" is working.').' '.$msg."\n");
fclose($f);
?>