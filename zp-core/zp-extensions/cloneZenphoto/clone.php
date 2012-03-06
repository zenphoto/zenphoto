<?php
/**
 *
 * Zenphoto site cloner
 *
 * @package admin
 */
define ('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');

admin_securityChecks(NULL, currentRelativeURL());
XSRFdefender('cloneZenphoto');

$folder = sanitize($_GET['cloneFolder']);
$path = str_replace(WEBPATH,'/',SERVERPATH);
$newinstall = str_replace($path, '', $folder);

$msg = array();
$success = true;

$targets = array(ZENFOLDER, THEMEFOLDER, USER_PLUGIN_FOLDER, 'index.php');
foreach ($targets as $target) {
	if (file_exists($folder.$target)) {
		$msg[] = sprintf(gettext('<code>%s</code> exists at the destination.'), $target);
		$success = false;
	}
	if (!@symlink(SERVERPATH.'/'.$target, $folder.$target)) {
		$msg[] = sprintf(gettext('Link creation for the <code>%s</code> folder failed.'),$target);
		$success = false;
	}
}

if ($success = empty($msg)) {
	$msg[] = sprintf(gettext('Successful clone to %s'),$folder);
	$msg[] = '<span class="buttons"><a href="/'.$newinstall.ZENFOLDER.'/setup.php">'.gettext('setup the new install').'</a></span><br clear="all">';
}
require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/cloneZenphoto/cloneTab.php');

?>