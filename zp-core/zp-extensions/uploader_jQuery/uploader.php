<?php
/**
 * 
 * @package zpcore\plugins\uploaderjquery
 */
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once 'class-uploadhandler.php';
require_once 'class-uploadhandlerZP.php';

$_zp_loggedin = NULL;
if (isset($_POST['auth'])) {
	$hash = sanitize($_POST['auth']);
	$id = sanitize($_POST['id']);
	$_zp_loggedin = $_zp_authority->checkAuthorization($hash, $id);
}

admin_securityChecks(UPLOAD_RIGHTS, $return = currentRelativeURL());
$_zp_uploader_folder = zp_apply_filter('admin_upload_process', sanitize_path($_POST['folder']));
$types = array_keys($_zp_extra_filetypes);
if (function_exists('zip_open')) {
	$types[] = 'ZIP';
}
$types = array_merge($_zp_supported_images, $types);

$types = zp_apply_filter('upload_filetypes', $types);
$types = array_unique($types);
$options = array(
		'upload_dir' => $_zp_uploader_targetpath = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($_zp_uploader_folder) . '/',
		'upload_url' => imgSrcURI(ALBUM_FOLDER_WEBPATH . $_zp_uploader_folder) . '/',
		'accept_file_types' => '/(' . implode('|\.', $types) . ')$/i'
);
$new = !is_dir($_zp_uploader_targetpath);
if (!empty($_zp_uploader_folder)) {
	if ($new) {
		$rightsalbum = Albumbase::newAlbum(dirname($_zp_uploader_folder), true, true);
	} else {
		$rightsalbum = Albumbase::newAlbum($_zp_uploader_folder, true, true);
	}
	if ($rightsalbum->exists) {
		if (!$rightsalbum->isMyItem(UPLOAD_RIGHTS)) {
			if (!zp_apply_filter('admin_managed_albums_access', false, $return)) {
				redirectURL(FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
			}
		}
	} else {
		// upload to the root
		if (!zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			redirectURL(FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
		}
	}
	if ($new) {
		mkdir_recursive($_zp_uploader_targetpath, FOLDER_MOD);
		$album = Albumbase::newAlbum($_zp_uploader_folder);
		$album->setPublished((int) !empty($_POST['publishalbum']));
		$album->setTitle(sanitize($_POST['albumtitle']));
		$album->setOwner($_zp_current_admin_obj->getUser());
		$album->save();
	}
	@chmod($_zp_uploader_targetpath, FOLDER_MOD);
}

$upload_handler = new UploadHandlerZP($options);

header('Pragma: no-cache');
header('Cache-Control: private, no-cache');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');

switch ($_SERVER['REQUEST_METHOD']) {
	case 'POST':
		//$upload_handler->post();
		break;
	case 'OPTIONS':
		break;
	default:
		header('HTTP/1.0 405 Method Not Allowed');
}
