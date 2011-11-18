<?php
/**
 * Used for changing permissions on a mass basis
 *
 * @package setup
 *
 */
require_once(dirname(dirname(__FILE__)).'/functions.php');
define('CONFIGFILE',SERVERPATH.'/'.DATA_FOLDER.'/zenphoto.cfg');
$chmod = CHMOD_VALUE;
$f = fopen(dirname(dirname(dirname(__FILE__))).'/'.DATA_FOLDER . '/setup.log', 'a');
if (!isset($_POST['folder'])) exit();
$folder = sanitize($_POST['folder'],3);
if (substr($folder,-1,1) == '/') $folder = substr($folder,0,-1);
if ($_POST['key']==sha1(filemtime(CONFIGFILE).file_get_contents(CONFIGFILE))) {
	if (!folderPermissions($folder)) {
		fwrite($f, sprintf(gettext('Notice: failed setting permissions for %s.'), basename($folder)) . "\n");
	}
} else {
	fwrite($f, sprintf(gettext('Notice: illegal call for permissions setting for %s.'), basename($folder)) . "\n");
}
fclose($f);
clearstatcache();

function folderPermissions($folder) {
	global $chmod, $f;
	$curdir = getcwd();
	chdir($folder);
	$files = safe_glob('*.*');
	chdir($curdir);
	foreach ($files as $file) {
		$path = $folder.'/'.$file;
		if (is_dir($path)) {
				if($file != '.' && $file != '..') {
				@chmod($path,$chmod);
				clearstatcache();
				if((fileperms($path)&0777)==$chmod) {
					if (!folderPermissions($path)) {
						return false;
					}
				} else {
					return false;
				}
			}
		} else {
			@chmod($path,0666&$chmod);
			clearstatcache();
			if ((fileperms($path)&0777)!=(0666&$chmod)) {
				return false;
			}
		}
	}
	return true;
}

?>