<?php
/**
 * Zenphoto package list generator
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */

// force UTF-8 Ø

define("OFFSET_PATH",3);
require_once("../../zp-core/admin-functions.php");
$stdExclude = Array( '.', '..','.DS_Store','.cache','Thumbs.db','.htaccess','.svn','debug.html','.buildpath','.project','.settings','session');
$_zp_resident_files = getResidentFiles(SERVERPATH,array_merge($stdExclude, array('favicon.ico','robots.txt','albums','backup','cache','cache_html','plugins','themes','uploaded','zp-core','zp-data','doc_files','dev-tools')));

$_zp_resident_files[] = THEMEFOLDER;

$_zp_resident_files[] = THEMEFOLDER.'/default';
$_zp_resident_files = array_merge($_zp_resident_files,getResidentFiles(SERVERPATH.'/'.THEMEFOLDER.'/default',$stdExclude));

$_zp_resident_files[] = THEMEFOLDER.'/effervescence_plus';
$_zp_resident_files = array_merge($_zp_resident_files,getResidentFiles(SERVERPATH.'/'.THEMEFOLDER.'/effervescence_plus',$stdExclude));


$_zp_resident_files[] = THEMEFOLDER.'/garland';
$_zp_resident_files = array_merge($_zp_resident_files,getResidentFiles(SERVERPATH.'/'.THEMEFOLDER.'/garland',$stdExclude));

$_zp_resident_files[] = THEMEFOLDER.'/stopdesign';
$_zp_resident_files = array_merge($_zp_resident_files,getResidentFiles(SERVERPATH.'/'.THEMEFOLDER.'/stopdesign',$stdExclude));

$_zp_resident_files[] = THEMEFOLDER.'/zenpage';
$_zp_resident_files = array_merge($_zp_resident_files,getResidentFiles(SERVERPATH.'/'.THEMEFOLDER.'/zenpage',$stdExclude));

$_zp_resident_files[] = ZENFOLDER;
$_zp_resident_files = array_merge($_zp_resident_files,getResidentFiles(SERVERPATH.'/'.ZENFOLDER,$stdExclude));

natsort($_zp_resident_files);
$filepath = SERVERPATH.'/'.getOption('zenphoto_package_path').'/Zenphoto.package';
$fp = fopen($filepath, 'w');
foreach ($_zp_resident_files as $component) {
	fwrite($fp,$component."\n");
}
fwrite($fp,count($_zp_resident_files));
fclose($fp);
clearstatcache();
header('Location: '.FULLWEBPATH.'/'.ZENFOLDER.'/admin.php?action=external&msg=Zenphoto package created and stored in the '.getOption('zenphoto_package_path').' folder.');
exit();

/**
 *
 * enumerates the files in folder(s)
 * @param $folder
 */
function getResidentFiles($folder,$exclude) {
	global $_zp_resident_files;
	$dirs = array_diff(scandir($folder),$exclude);
	$localfiles = array();
	$localfolders = array();
	foreach($dirs as $file) {
		$file = str_replace('\\','/',$file);
		$key = str_replace(SERVERPATH.'/', '', filesystemToInternal($folder.'/'.$file));
		if (is_dir($folder.'/'.$file)) {
			$localfolders[] = $key;
			$localfolders = array_merge($localfolders, getResidentFiles($folder.'/'.$file,$exclude));
		} else {
			$localfiles[] = $key;
		}
	}
	return  array_merge($localfiles,$localfolders);
}

?>