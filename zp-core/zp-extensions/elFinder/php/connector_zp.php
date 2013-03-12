<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/admin-functions.php');
zp_session_start();
XSRFdefender('elFinder');

include_once SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/php/elFinderConnector.class.php';
include_once SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/php/elFinder.class.php';
include_once SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/php/elFinderVolumeDriver.class.php';
include_once SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/php/elFinderVolumeLocalFileSystem.class.php';
// Required for MySQL storage connector
// include_once SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/php/elFinderVolumeMySQL.class.php';
// Required for FTP connector support
// include_once SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/php/elFinderVolumeFTP.class.php';

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from  '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/

function access($attr, $path, $data, $volume) {
	return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}

function accessImage($attr, $path, $data, $volume) {
	//	allow only images
	if (access($attr, $path, $data, $volume) || (!is_dir($path) && !is_valid_image($path))) {
		return !($attr == 'read' || $attr == 'write');
	}
	return NULL;
}

function accessData($attr, $path, $data, $volume) {
	//	restrict access
	if (access($attr, $path, $data, $volume) || (is_dir($path) && basename($path)!=DATA_FOLDER)) {
		return !($attr == 'read' || $attr == 'write');
	}
	return NULL;
}

function accessThemes($attr, $path, $data, $volume) {
$path = ltrim(str_replace(SERVERPATH.'/'.THEMEFOLDER, '', str_replace('\\', '/', $path)),'/');
$base = explode('/',$path);
$theme = array_shift($base);
$block = zenPhotoTheme($theme);
	//	restrict access
	if ($block || access($attr, $path, $data, $volume)) {
		return !($attr == 'read' || $attr == 'write');
	}
	return NULL;
}

function accessAlbums($attr, $path, $data, $volume) {
	global $_managed_folders;
	//	restrict access to his albums
	if (zp_loggedin(ADMIN_RIGHTS)) {
		$block = false;
	} else {
		$path = str_replace('\\', '/', $path).'/';
		$base = explode('/',str_replace(getAlbumFolder(SERVERPATH), '', $path));
		$base = array_shift($base);
		$block = !$base && $attr == 'write' || $base && !in_array($base, $_managed_folders);
	}
	if ($block || access($attr, $path, $data, $volume)) {
		return !($attr == 'read' || $attr == 'write');
	}
	return NULL;
}

$opts = array();

if ($_REQUEST['origin']=='upload') {

	if (zp_loggedin(FILES_RIGHTS)) {
		$opts['roots'][0] =
		array(
				'driver'     => 'LocalFileSystem',
				'startPath'  => SERVERPATH.'/'.UPLOAD_FOLDER.'/',
				'path'       =>	SERVERPATH.'/'.UPLOAD_FOLDER.'/',
				'URL'        =>	WEBPATH.'/'.UPLOAD_FOLDER.'/',
				'alias' 		 => sprintf(gettext('Upload folder (%s)'),UPLOAD_FOLDER),
				'mimeDetect' => 'internal',
				'tmbPath'    => '.tmb',
				'utf8fix'    => true,
				'tmbCrop'    => false,
				'tmbBgColor' => 'transparent',
				'accessControl' => 'access',
				'acceptedName'    => '/^[^\.].*$/'
		);
	}

	if (zp_loggedin(THEMES_RIGHTS)) {
		$opts['roots'][1] =
			array(
				'driver'     => 'LocalFileSystem',
				'startPath'  => SERVERPATH.'/'.THEMEFOLDER.'/',
				'path'       =>	SERVERPATH.'/'.THEMEFOLDER.'/',
				'URL'        =>	WEBPATH.'/'.THEMEFOLDER.'/',
				'alias' 		 => sprintf(gettext('Zenphoto themes (%s)'),THEMEFOLDER),
				'mimeDetect' => 'internal',
				'tmbPath'    => '.tmb',
				'utf8fix'    => true,
				'tmbCrop'    => false,
				'tmbBgColor' => 'transparent',
				'accessControl' => 'accessThemes',
				'acceptedName'    => '/^[^\.].*$/'
			);
	}

	if (zp_loggedin(ALBUM_RIGHTS)) {
		if (!zp_loggedin(ADMIN_RIGHTS)) {
			$_managed_folders = getManagedAlbumList();
			//	remove albums he may not edit
			foreach ($_managed_folders as $key=>$folder) {
				$rightsalbum = newAlbum($folder);
				$modified_rights = $rightsalbum->albumSubRights();
				if (!($modified_rights & MANAGED_OBJECT_RIGHTS_EDIT)) {
					unset($_managed_folders[$key]);
				}
			}
		}
		$opts['roots'][2] =
			array(
				'driver'     => 'LocalFileSystem',
				'startPath'  => getAlbumFolder(SERVERPATH),
				'path'       =>	getAlbumFolder(SERVERPATH),
				'URL'        =>	getAlbumFolder(WEBPATH),
				'alias' 		 => sprintf(gettext('Albums folder (%s)'),basename(getAlbumFolder())),
				'mimeDetect' => 'internal',
				'tmbPath'    => '.tmb',
				'utf8fix'    => true,
				'tmbCrop'    => false,
				'tmbBgColor' => 'transparent',
				'uploadAllow' => array('image'),
				'accessControl' => 'accessAlbums',
				'acceptedName'  => '/^[^\.].*$/'
		);
	}

	if (zp_loggedin(ADMIN_RIGHTS)) {
		$opts['roots'][3] =
			array(
				'driver'     => 'LocalFileSystem',
				'startPath'  => SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/',
				'path'       =>	SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/',
				'URL'        =>	WEBPATH.'/'.USER_PLUGIN_FOLDER.'/',
				'alias' 		 => sprintf(gettext('Third party plugins (%s)'),USER_PLUGIN_FOLDER),
				'mimeDetect' => 'internal',
				'tmbPath'    => '.tmb',
				'utf8fix'    => true,
				'tmbCrop'    => false,
				'tmbBgColor' => 'transparent',
				'accessControl' => 'access',
				'acceptedName'  => '/^[^\.].*$/'
		);
		$opts['roots'][4] =
			array(
				'driver'     => 'LocalFileSystem',
				'startPath'  => SERVERPATH.'/'.DATA_FOLDER.'/',
				'path'       =>	SERVERPATH.'/'.DATA_FOLDER.'/',
				'URL'        =>	WEBPATH.'/'.DATA_FOLDER.'/',
				'alias' 		 => sprintf(gettext('Zenphoto data (%s)'),DATA_FOLDER),
				'mimeDetect' => 'internal',
				'tmbPath'    => '.tmb',
				'utf8fix'    => true,
				'tmbCrop'    => false,
				'tmbBgColor' => 'transparent',
				'accessControl' => 'accessData',
				'acceptedName'  => '/^[^\.].*$/'
		);
		$opts['roots'][5] =
			array(
				'driver'     => 'LocalFileSystem',
				'startPath'  => SERVERPATH.'/'.BACKUPFOLDER.'/',
				'path'       =>	SERVERPATH.'/'.BACKUPFOLDER.'/',
				'URL'        =>	WEBPATH.'/'.BACKUPFOLDER.'/',
				'alias' 		 => sprintf(gettext('Backup files (%s)'),BACKUPFOLDER),
				'mimeDetect' => 'internal',
				'tmbPath'    => '.tmb',
				'utf8fix'    => true,
				'tmbCrop'    => false,
				'tmbBgColor' => 'transparent',
				'accessControl' => 'access',
				'acceptedName'  => '/^[^\.].*$/'
		);

	}

} else {	//	origin == 'tinyMCE

	if (zp_loggedin(FILES_RIGHTS)) {
		$opts['roots'][0] =
			array(
				'driver'     => 'LocalFileSystem',
				'startPath'  => SERVERPATH.'/'.UPLOAD_FOLDER.'/',
				'path'       =>	SERVERPATH.'/'.UPLOAD_FOLDER.'/',
				'URL'        =>	WEBPATH.'/'.UPLOAD_FOLDER.'/',
				'alias' 		 => sprintf(gettext('Upload folder (%s)'),UPLOAD_FOLDER),
				'mimeDetect' => 'internal',
				'tmbPath'    => '.tmb',
				'utf8fix'    => true,
				'tmbCrop'    => false,
				'tmbBgColor' => 'transparent',
				'uploadAllow' => array('image'),
				'accessControl' => 'accessImage',
				'acceptedName'    => '/^[^\.].*$/'
		);
	}

}

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

