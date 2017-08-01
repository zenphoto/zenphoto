<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/admin-globals.php');
XSRFdefender('elFinder');

include_once SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/elFinder/php/elFinderConnector.class.php';
include_once SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/elFinder/php/elFinder.class.php';
include_once SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/elFinder/php/elFinderVolumeDriver.class.php';
include_once SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/elFinder/php/elFinderVolumeLocalFileSystem.class.php';
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
 * */
function access($attr, $path, $data, $volume) {
	return strpos(basename($path), '.') === 0 // if file/folder begins with '.' (dot)
					? !($attr == 'read' || $attr == 'write' ) // set read+write to false, other (locked+hidden) set to true
					: null; // else elFinder decide it itself
}

function accessImage($attr, $path, $data, $volume) {
	global $validSuffix;
	if (access($attr, $path, $data, $volume)) {
		return true;
	}
	//	allow only images
	if (!is_dir($path) && !in_array(getSuffix($path), $validSuffix)) {
		return !($attr == 'read' || $attr == 'write');
	}
	return NULL;
}

function accessMedia($attr, $path, $data, $volume) {
	if (access($attr, $path, $data, $volume)) {
		return true;
	}
	//allow only tinyMCE recognized media suffixes
	$valid = array("mp3", "wav", "mp4", "webm", "ogg", "swf");
	if (!is_dir($path) && !in_array(getSuffix($path), $valid)) {
		return !($attr == 'read' || $attr == 'write');
	}
	return NULL;
}

function accessAlbums($attr, $path, $data, $volume) {
	//	restrict access to his albums
	$base = explode('/', str_replace(getAlbumFolder(SERVERPATH), '', str_replace('\\', '/', $path) . '/'));
	$base = array_shift($base);
	$block = !$base && $attr == 'write';
	if ($block || access($attr, $path, $data, $volume)) {
		return !($attr == 'read' || $attr == 'write');
	}
	return NULL;
}

$opts = array();
$rights = zp_loggedin();
$sidecars = zp_apply_filter('upload_filetypes', array());
$validSuffix = array_keys($_zp_images_classes);
$validSuffix = array_merge($validSuffix, $sidecars);

if ($_REQUEST['origin'] == 'upload') {
	$themeAlias = sprintf(gettext('Themes (%s)'), THEMEFOLDER);
	if (isset($_REQUEST['themeEdit'])) {
		$rights = 0;
		$themeRequest = sanitize($_REQUEST['themeEdit']);
		if (zp_loggedin(THEMES_RIGHTS) && file_exists(SERVERPATH . '/' . THEMEFOLDER . '/' . $themeRequest)) {
			if (!protectedTheme($themeRequest)) {
				$themeAlias = sprintf(gettext('%s'), $themeRequest);
				$themeRequest .= '/';
				$rights = THEMES_RIGHTS;
			}
		}
	} else {
		$themeRequest = '';
	}

	if (CASE_INSENSITIVE) { //	ignore case on case insensitive file systems!
		$i = 'i';
	} else {
		$i = '';
	}

	if ($rights & FILES_RIGHTS) {
		$opts['roots'][0] = array(
				'driver' => 'LocalFileSystem',
				'startPath' => SERVERPATH . '/' . UPLOAD_FOLDER . '/',
				'path' => SERVERPATH . '/' . UPLOAD_FOLDER . '/',
				'URL' => WEBPATH . '/' . UPLOAD_FOLDER . '/',
				'alias' => sprintf(gettext('Upload folder (%s)'), UPLOAD_FOLDER),
				'mimeDetect' => 'internal',
				'tmbPath' => '.tmb',
				'utf8fix' => true,
				'tmbCrop' => false,
				'tmbBgColor' => 'transparent',
				'accessControl' => 'access',
				'acceptedName' => '/^[^\.].*$/'
		);
	}

	if ($rights & THEMES_RIGHTS) {
		$zplist = array();
		foreach ($_zp_gallery->getThemes() as $theme => $data) {
			if (protectedTheme($theme)) {
				$zplist[] = preg_quote($theme);
			}
		}
		$opts['roots'][1] = array(
				'driver' => 'LocalFileSystem',
				'startPath' => SERVERPATH . '/' . THEMEFOLDER . '/' . $themeRequest,
				'path' => SERVERPATH . '/' . THEMEFOLDER . '/' . $themeRequest,
				'URL' => WEBPATH . '/' . THEMEFOLDER . '/' . $themeRequest,
				'alias' => $themeAlias,
				'mimeDetect' => 'internal',
				'tmbPath' => '.tmb',
				'utf8fix' => true,
				'tmbCrop' => false,
				'tmbBgColor' => 'transparent',
				'accessControl' => 'access',
				'acceptedName' => '/^[^\.].*$/',
				'attributes' => $attr = array(
		array(
				'pattern' => '/.(' . implode('$|', $zplist) . '$)/' . $i, // Dont write or delete to this but subfolders and files
				'read' => true,
				'write' => false,
				'locked' => true
		),
		array(
				'pattern' => '/.(' . implode('\/|', $zplist) . '\/)/' . $i, // Dont write or delete to this but subfolders and files
				'read' => true,
				'write' => false,
				'locked' => true
		)
				)
		);
	}

	if ($rights & UPLOAD_RIGHTS) {
		$opts['roots'][2] = array(
				'driver' => 'LocalFileSystem',
				'startPath' => getAlbumFolder(SERVERPATH),
				'path' => getAlbumFolder(SERVERPATH),
				'URL' => getAlbumFolder(WEBPATH),
				'alias' => sprintf(gettext('Albums folder (%s)'), basename(getAlbumFolder())),
				'mimeDetect' => 'internal',
				'tmbPath' => '.tmb',
				'utf8fix' => true,
				'tmbCrop' => false,
				'tmbBgColor' => 'transparent',
				'uploadAllow' => array('image'),
				'acceptedName' => '/^[^\.].*$/'
		);
		if ($rights & ADMIN_RIGHTS) {
			$opts['roots'][2]['accessControl'] = 'access';
		} else {
			if ($rights & FILES_RIGHTS) {
				$opts['roots'][2]['accessControl'] = 'accessAlbums';
			} else {
				$opts['roots'][2]['accessControl'] = 'accessImage';
			}

			$_managed_folders = getManagedAlbumList();
			$excluded_folders = $_zp_gallery->getAlbums(0, null, null, false, true); //	get them all!
			$excluded_folders = array_diff($excluded_folders, $_managed_folders);
			foreach ($excluded_folders as $key => $folder) {
				$excluded_folders[$key] = preg_quote($folder);
			}

			$maxupload = ini_get('upload_max_filesize');
			$maxuploadint = parse_size($maxupload);
			$uploadlimit = zp_apply_filter('get_upload_limit', $maxuploadint);
			$all_actions = $_not_upload = $_not_edit = array();

			foreach ($_managed_folders as $key => $folder) {
				$rightsalbum = newAlbum($folder);
				$modified_rights = $rightsalbum->subRights();
				if ($uploadlimit <= 0) {
					$modified_rights = $modified_rights & ~MANAGED_OBJECT_RIGHTS_UPLOAD;
				}
				$_not_edit[$key] = $_not_upload[$key] = $folder = preg_quote($folder);
				switch ($modified_rights & (MANAGED_OBJECT_RIGHTS_UPLOAD | MANAGED_OBJECT_RIGHTS_EDIT)) {
					case MANAGED_OBJECT_RIGHTS_UPLOAD: // upload but not edit
						unset($_not_upload[$key]);
						break;
					case MANAGED_OBJECT_RIGHTS_EDIT: // edit but not upload
						unset($_not_edit[$key]);
						break;
					case MANAGED_OBJECT_RIGHTS_UPLOAD | MANAGED_OBJECT_RIGHTS_EDIT: // edit and upload
						unset($_not_edit[$key]);
						unset($_not_upload[$key]);
						$all_actions[$key] = $folder;
						break;
				}
			}

			$excludepattern = '';
			$noteditpattern = '';
			foreach ($sidecars as $car) {
				$excludepattern .='|' . implode('.' . $car . '|', $excluded_folders) . '.' . $car;
				$noteditpattern .= '|' . implode('.' . $car . '|', $_not_edit) . '.' . $car;
			}

			$opts['roots'][2]['attributes'] = array();
			if (!empty($excluded_folders)) {
				$opts['roots'][2]['attributes'][] = array(//	albums he does not manage
						'pattern' => '/.(' . implode('$|', $excluded_folders) . '$)/' . $i, // Dont write or delete to this but subfolders and files
						'read' => false,
						'write' => false,
						'hidden' => true,
						'locked' => true
				);

				$opts['roots'][2]['attributes'][] = array(//	sidecars for albums he does not manage
						'pattern' => '/.(' . ltrim($excludepattern, '|') . ')/i', // Dont write or delete to this but subfolders and files
						'read' => false,
						'write' => false,
						'hidden' => true,
						'locked' => true
				);
			}
			if (!empty($_not_upload)) {
				$opts['roots'][2]['attributes'][] = array(//	albums he can not upload
						'pattern' => '/.(' . implode('$|', $_not_upload) . '$)/' . $i, // Dont write or delete to this but subfolders and files
						'read' => true,
						'write' => false,
						'locked' => true
				);
			}
			if (!empty($_not_edit)) {
				$opts['roots'][2]['attributes'][] = array(//	albums content he not edit
						'pattern' => '/.(' . implode('\/|', $_not_edit) . '\/)/' . $i, // Dont write or delete to this but subfolders and files
						'read' => true,
						'write' => false,
						'locked' => true
				);
				$opts['roots'][2]['attributes'][] = array(//	sidecars for albums he can not edit
						'pattern' => '/.(' . ltrim($noteditpattern, '|') . ')/i', // Dont write or delete to this but subfolders and files
						'read' => true,
						'write' => false,
						'locked' => true
				);
			}
			if (!empty($all_actions)) {
				$opts['roots'][2]['attributes'][] = array(//	albums he can not upload
						'pattern' => '/.(' . implode('$|', $all_actions) . '$)/' . $i, // Dont write or delete to this but subfolders and files
						'read' => true,
						'write' => true,
						'hidden' => false,
						'locked' => false
				);
			}
		}
	}

	if ($rights & ADMIN_RIGHTS) {
		$opts['roots'][3] = array(
				'driver' => 'LocalFileSystem',
				'startPath' => SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/',
				'path' => SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/',
				'URL' => WEBPATH . '/' . USER_PLUGIN_FOLDER . '/',
				'alias' => sprintf(gettext('Third party plugins (%s)'), USER_PLUGIN_FOLDER),
				'mimeDetect' => 'internal',
				'tmbPath' => '.tmb',
				'utf8fix' => true,
				'tmbCrop' => false,
				'tmbBgColor' => 'transparent',
				'accessControl' => 'access',
				'acceptedName' => '/^[^\.].*$/'
		);
		$opts['roots'][4] = array(
				'driver' => 'LocalFileSystem',
				'startPath' => SERVERPATH . '/' . DATA_FOLDER . '/',
				'path' => SERVERPATH . '/' . DATA_FOLDER . '/',
				'URL' => WEBPATH . '/' . DATA_FOLDER . '/',
				'alias' => sprintf(gettext('Data (%s)'), DATA_FOLDER),
				'mimeDetect' => 'internal',
				'tmbPath' => '.tmb',
				'utf8fix' => true,
				'tmbCrop' => false,
				'tmbBgColor' => 'transparent',
				'accessControl' => 'access',
				'acceptedName' => '/^[^\.].*$/'
		);
		$opts['roots'][5] = array(
				'driver' => 'LocalFileSystem',
				'startPath' => SERVERPATH . "/" . DATA_FOLDER . "/" . BACKUPFOLDER . '/',
				'path' => SERVERPATH . "/" . DATA_FOLDER . "/" . BACKUPFOLDER . '/',
				'URL' => WEBPATH . "/" . DATA_FOLDER . '/' . BACKUPFOLDER . '/',
				'alias' => sprintf(gettext('Backup files (%s)'), BACKUPFOLDER),
				'mimeDetect' => 'internal',
				'tmbPath' => '.tmb',
				'utf8fix' => true,
				'tmbCrop' => false,
				'tmbBgColor' => 'transparent',
				'accessControl' => 'access',
				'acceptedName' => '/^[^\.].*$/'
		);
	}
} else { //	origin == 'tinyMCE
	if ($rights & FILES_RIGHTS) {
		$opts['roots'][0] = array(
				'driver' => 'LocalFileSystem',
				'startPath' => SERVERPATH . '/' . UPLOAD_FOLDER . '/',
				'path' => SERVERPATH . '/' . UPLOAD_FOLDER . '/',
				'URL' => WEBPATH . '/' . UPLOAD_FOLDER . '/',
				'alias' => sprintf(gettext('Upload folder (%s)'), UPLOAD_FOLDER),
				'mimeDetect' => 'internal',
				'tmbPath' => '.tmb',
				'utf8fix' => true,
				'tmbCrop' => false,
				'tmbBgColor' => 'transparent',
				'uploadAllow' => array('image'),
				'accessControl' => 'accessImage',
				'acceptedName' => '/^[^\.].*$/'
		);
		switch (@$_GET['type']) {
			case 'media':
				$opts['roots'][0]['accessControl'] = 'accessMedia';
				break;
			case 'image':
				$opts['roots'][0]['accessControl'] = 'accessImage';
				break;
			default:
				$opts['roots'][0]['accessControl'] = 'access';
				break;
		}
	}
}

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

