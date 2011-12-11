<?php
/**
 * Used for changing permissions on a mass basis
 *
 * @package setup
 *
 */
require_once(dirname(dirname(__FILE__)).'/functions.php');
require_once(dirname(__FILE__).'/setup-functions.php');
define('CONFIGFILE',SERVERPATH.'/'.DATA_FOLDER.'/zenphoto.cfg');
define('FOLDER_MOD',CHMOD_VALUE | ((CHMOD_VALUE & 0444)>>1));
define('FILE_MOD',0666&CHMOD_VALUE);
if (!isset($_POST['folder'])) {
	exit();
}
$folder = sanitize($_POST['folder'],3);
if (substr($folder,-1,1) == '/') $folder = substr($folder,0,-1);
$f = fopen(dirname(dirname(dirname(__FILE__))).'/'.DATA_FOLDER . '/setup.log', 'a');

if ($_POST['key']==sha1(filemtime(CONFIGFILE).file_get_contents(CONFIGFILE))) {
	if (folderPermissions($folder)) {
		fwrite($f, sprintf(gettext('Setting permissions for %s.'), basename($folder)) . "\n");
	} else {
		fwrite($f, sprintf(gettext('Notice: failed setting permissions for %s.'), basename($folder)) . "\n");
	}
} else {
	fwrite($f, sprintf(gettext('Notice: illegal call for permissions setting for %s.'), basename($folder)) . "\n");
}
fclose($f);
clearstatcache();
function folderPermissions($folder) {
	$files = array();
	if (($dir=opendir($folder))!==false) {
		while(($file=readdir($dir))!==false) {
			if($file != '.' && $file != '..') {
				$files[] = $file;
			}
		}
		closedir($dir);
	}
	foreach ($files as $file) {
		$path = $folder.'/'.$file;
		if (is_dir($path)) {
			@chmod($path,FOLDER_MOD);
			clearstatcache();
			if(checkPermissions(fileperms($path)&0777,FOLDER_MOD)) {
				if (!folderPermissions($path)) {
					return false;
				}
			} else {
				return false;
			}
		} else {
			@chmod($path,FILE_MOD);
			clearstatcache();
			if (!checkPermissions(fileperms($path)&0777,FILE_MOD)) {
				return false;
			}
		}
	}
	return true;
}
?>