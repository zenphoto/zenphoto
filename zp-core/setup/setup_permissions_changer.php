<?php

/**
 * Used for changing permissions on a mass basis
 *
 * @package zpcore\setup
 *
 */
define('OFFSET_PATH', 2);
require_once(dirname(dirname(__FILE__)) . '/functions/functions.php');
require_once(dirname(__FILE__) . '/class-setup.php');
if (!isset($_POST['folder'])) {
	exit();
}
$folder = rtrim(sanitize($_POST['folder'], 3), '/');

if ($_POST['key'] == sha1(filemtime(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE) . file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE))) {
	if (setup::folderPermissions($folder)) {
		setup::Log(sprintf(gettext('Setting permissions (0%o) for %s.'), FILE_MOD, basename($folder)), true);
	} else {
		setup::Log(sprintf(gettext('Notice: failed setting permissions (0%o) for %s.'), FILE_MOD, basename($folder)), true);
	}
} else {
	setup::Log(sprintf(gettext('Notice: illegal call for permissions setting for %s.'), basename($folder)), true);
}
clearstatcache();