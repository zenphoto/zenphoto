<?php
/*
 * ZenPhoto20 adaptation of the upload handler
 */


define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/admin-globals.php' );

$_zp_loggedin = NULL;
if (isset($_POST['auth'])) {
	$hash = sanitize($_POST['auth']);
	$id = sanitize($_POST['id']);
	$_zp_loggedin = $_zp_authority->checkAuthorization($hash, $id);
	admin_securityChecks(UPLOAD_RIGHTS, $return = currentRelativeURL());
} else {
	?>
	{"files": [
	{
	"error": "<?php echo gettext('Upload not allowed'); ?>"
	}
	]}
	<?php
	exitZP();
}

$folder = zp_apply_filter('admin_upload_process', sanitize_path($_POST['folder']));
$types = array_keys($_zp_images_classes);
$types = zp_apply_filter('upload_filetypes', $types);

$options = array(
		'upload_dir' => $targetPath = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($folder) . '/',
		'upload_url' => imgSrcURI(ALBUM_FOLDER_WEBPATH . $folder) . '/',
		'accept_file_types' => '/(' . implode('|\.', $types) . ')$/i'
);

$new = !is_dir($targetPath);

if (!empty($folder)) {
	if ($new) {
		$rightsalbum = newAlbum(dirname($folder), true, true);
	} else {
		$rightsalbum = newAlbum($folder, true, true);
	}
	if ($rightsalbum->exists) {
		if (!$rightsalbum->isMyItem(UPLOAD_RIGHTS)) {
			if (!zp_apply_filter('admin_managed_albums_access', false, $return)) {
				header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
				exitZP();
			}
		}
	} else {
		// upload to the root
		if (!zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
			exitZP();
		}
	}
	if ($new) {
		mkdir_recursive($targetPath, FOLDER_MOD);
		$album = newAlbum($folder);
		$album->setTitle(sanitize($_POST['albumtitle']));
		$album->setOwner($_zp_current_admin_obj->getUser());
		$album->setShow((int) ($_POST['publishalbum'] == 'true'));
		$album->save();
	}
	@chmod($targetPath, FOLDER_MOD);
}

require('UploadHandler.php');
$upload_handler = new UploadHandler($options);
