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
// Required for Dropbox.com connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDropbox.class.php';
// # Dropbox volume driver need "dropbox-php's Dropbox" and "PHP OAuth extension" or "PEAR's HTTP_OAUTH package"
// * dropbox-php: http://www.dropbox-php.com/
// * PHP OAuth extension: http://pecl.php.net/package/oauth
// * PEAR�s HTTP_OAUTH package: http://pear.php.net/package/http_oauth
//  * HTTP_OAUTH package require HTTP_Request2 and Net_URL2
// Dropbox driver need next two settings. You can get at https://www.dropbox.com/developers
// define('ELFINDER_DROPBOX_CONSUMERKEY',    '');
// define('ELFINDER_DROPBOX_CONSUMERSECRET', '');

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

function accessAlbums($attr, $path, $data, $volume) {
	//	restrict access to his albums
	$base = explode('/',str_replace(getAlbumFolder(SERVERPATH), '', str_replace('\\', '/', $path).'/'));
	$base = array_shift($base);
	$block = !$base && $attr == 'write';
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
		$zplist = unserialize(getOption('Zenphoto_theme_list'));
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
				'accessControl' => 'access',
				'acceptedName'    => '/^[^\.].*$/',
				'attributes'	=>	$attr = array(
																				array(
																						'pattern' => '/.('.implode('$|',$zplist).'$)/', // Dont write or delete to this but subfolders and files
																						'read'  => true,
																						'write' => false,
																						'locked' => true
																						),
																				array(
																						'pattern' => '/.('.implode('\/|',$zplist).'\/)/', // Dont write or delete to this but subfolders and files
																						'read'  => true,
																						'write' => false,
																						'locked' => true
																						)
																				)
			);
	}

	if (zp_loggedin(UPLOAD_RIGHTS)) {
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
				'acceptedName'  => '/^[^\.].*$/'
		);
		if (zp_loggedin(ADMIN_RIGHTS)) {
			$opts['roots'][2]['accessControl'] = 'access';
		} else {
			$opts['roots'][2]['accessControl'] = 'accessAlbums';
			$_managed_folders = getManagedAlbumList();
			$excluded_folders = $_zp_gallery->getAlbums(0);
			$excluded_folders = array_diff($excluded_folders, $_managed_folders);
			//	albums he view but may not edit
			foreach ($_managed_folders as $key=>$folder) {
				$rightsalbum = newAlbum($folder);
				$modified_rights = $rightsalbum->albumSubRights();
				if ($modified_rights & MANAGED_OBJECT_RIGHTS_EDIT) {
					unset($_managed_folders[$key]);
				}
			}
			$opts['roots'][2]['attributes'] = array(
																						array(	//	albums he does not manage
																								'pattern' => '/.('.implode('$|',$excluded_folders).'$)/', // Dont write or delete to this but subfolders and files
																								'read'  => false,
																								'write' => false,
																								'locked' => true
																						),
																						array(	//	xmp for albums he does not manage
																								'pattern' => '/.('.implode('.xmp|',$excluded_folders).'.xmp)/', // Dont write or delete to this but subfolders and files
																								'read'  => false,
																								'write' => false,
																								'locked' => true
																						),
																						array(	//	albums he can manage but not edit
																								'pattern' => '/.('.implode('$|',$_managed_folders).'$)/', // Dont write or delete to this but subfolders and files
																								'read'  => true,
																								'write' => false,
																								'locked' => true
																						),
																						array(	//	albums content he can manage but not edit
																								'pattern' => '/.('.implode('\/|',$_managed_folders).'\/)/', // Dont write or delete to this but subfolders and files
																								'read'  => true,
																								'write' => false,
																								'locked' => true
																						),
																						array(	//	xmp for albums he can manage but not edit
																								'pattern' => '/.('.implode('\/|',$_managed_folders).'.xmp)/', // Dont write or delete to this but subfolders and files
																								'read'  => true,
																								'write' => false,
																								'locked' => true
																						)
			);
		}

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
				'accessControl' => 'access',
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

