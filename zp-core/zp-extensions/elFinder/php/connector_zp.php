<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/admin-globals.php');
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
		$opts['roots'][0] =	array(
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
		$opts['roots'][1] = array(
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
		$opts['roots'][2] = array(
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
			foreach ($excluded_folders as $key=>$folder) {
				$excluded_folders[$key] = preg_quote($folder);
			}

			$maxupload = ini_get('upload_max_filesize');
			$maxuploadint = parse_size($maxupload);
			$uploadlimit = zp_apply_filter('get_upload_limit', $maxuploadint);
			$all_actions = $_not_upload = $_not_edit= array();

			foreach ($_managed_folders as $key=>$folder) {
				$rightsalbum = newAlbum($folder);
				$modified_rights = $rightsalbum->albumSubRights();
				if ($uploadlimit <= 0) {
					$modified_rights = $modified_rights & ~MANAGED_OBJECT_RIGHTS_UPLOAD;
				}
				$_not_edit[$key] = $_not_upload[$key] = $folder = preg_quote($folder);
				switch ($modified_rights & (MANAGED_OBJECT_RIGHTS_UPLOAD | MANAGED_OBJECT_RIGHTS_EDIT)) {
					case MANAGED_OBJECT_RIGHTS_UPLOAD:															// upload but not edit
						unset($_not_upload[$key]);
						break;
					case MANAGED_OBJECT_RIGHTS_EDIT:																// edit but not upload
						unset($_not_edit[$key]);
						break;
					case MANAGED_OBJECT_RIGHTS_UPLOAD | MANAGED_OBJECT_RIGHTS_EDIT:	// edit and upload
						unset($_not_edit[$key]);
						unset($_not_upload[$key]);
						$all_actions[$key] = $folder;
						break;
				}
			}

			$opts['roots'][2]['attributes'] = array();
			if (!empty($excluded_folders)) {
				$opts['roots'][2]['attributes'][0] = array(	//	albums he does not manage
																									'pattern' => '/.('.implode('$|',$excluded_folders).'$)/', // Dont write or delete to this but subfolders and files
																									'read'  => false,
																									'write' => false,
																									'locked' => true
																							);

				$opts['roots'][2]['attributes'][1] = array(	//	xmp for albums he does not manage
																									'pattern' => '/.('.implode('.xmp|',$excluded_folders).'.xmp)/', // Dont write or delete to this but subfolders and files
																									'read'  => false,
																									'write' => false,
																									'locked' => true
																							);
			}
			if (!empty($_not_upload)) {
				$opts['roots'][2]['attributes'][2] = array(	//	albums he can not upload
																									'pattern' => '/.('.implode('$|',$_not_upload).'$)/', // Dont write or delete to this but subfolders and files
																									'read'  => true,
																									'write' => false,
																									'locked' => true
																							);
			}
			if (!empty($_not_edit)) {
				$opts['roots'][2]['attributes'][3] = array(	//	albums content he not edit
																									'pattern' => '/.('.implode('\/|',$_not_edit).'\/)/', // Dont write or delete to this but subfolders and files
																									'read'  => true,
																									'write' => false,
																									'locked' => true
																							);
				$opts['roots'][2]['attributes'][4] = array(	//	xmp for albums he can not edit
																									'pattern' => '/.('.implode('\/|',$_not_edit).'.xmp)/', // Dont write or delete to this but subfolders and files
																									'read'  => true,
																									'write' => false,
																									'locked' => true
																							);
			}
			if (!empty($all_actions)) {
				$opts['roots'][2]['attributes'][5] = array(	//	albums he can not upload
																									'pattern' => '/.('.implode('$|',$all_actions).'$)/', // Dont write or delete to this but subfolders and files
																									'read'  => true,
																									'write' => true,
																									'locked' => false
																							);
			}
		}
	}

	if (zp_loggedin(ADMIN_RIGHTS)) {
		$opts['roots'][3] = array(
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
		$opts['roots'][4] = array(
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
		$opts['roots'][5] = array(
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
		$opts['roots'][0] = array(
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

