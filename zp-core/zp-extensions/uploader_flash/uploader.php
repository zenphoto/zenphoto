<?php
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-functions.php');

$_zp_loggedin = NULL;
if (isset($_POST['auth'])) {
	$hash = sanitize($_POST['auth']);
	$id = sanitize($_POST['id']);
	$_zp_loggedin = $_zp_authority->checkAuthorization($hash, $id);
}

admin_securityChecks(UPLOAD_RIGHTS, $return = currentRelativeURL());

if (!empty($_FILES)) {
	$name = trim(basename(sanitize($_FILES['Filedata']['name'],3)));
	if (isset($_FILES['Filedata']['error']) && $_FILES['Filedata']['error']) {
		$error = $_FILES['Filedata']['error'];
	} else {
		$tempFile = sanitize($_FILES['Filedata']['tmp_name'],3);
		$folder = trim(sanitize($_POST['folder'],3),'/');
		$albumparmas = explode(':', $folder,3);
		$folder = zp_apply_filter('admin_upload_process',sanitize_path($albumparmas[1]));
		$targetPath = ALBUM_FOLDER_SERVERPATH.internalToFilesystem($folder);
		$new = !is_dir($targetPath);
		if (!empty($folder)) {
			if ($new) {
				$rightsalbum = new Album(NULL, dirname($folder));
			} else{
				$rightsalbum = new Album(NULL, $folder);
			}
			if (!$rightsalbum->isMyItem(UPLOAD_RIGHTS)) {
				if (!zp_apply_filter('admin_managed_albums_access',false, $return)) {
					header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
					exitZP();
				}
			}
			if ($new) {
				mkdir_recursive($targetPath, FOLDER_MOD);
				$album = new Album(NULL, $folder);
				$album->setShow($albumparmas[0]!='false');
				$album->setTitle($albumparmas[2]);
				$album->setOwner($_zp_current_admin_obj->getUser());
				$album->save();
			}
			@chmod($targetPath, FOLDER_MOD);
			$error = zp_apply_filter('check_upload_quota', UPLOAD_ERR_OK, $tempFile);
			if (!$error) {
				if (is_valid_image($name) || is_valid_other_type($name)) {
					$seoname = seoFriendly($name);
					if (strrpos($seoname,'.')===0) $seoname = sha1($name).$seoname; // soe stripped out all the name.
					$targetFile =  $targetPath.'/'.internalToFilesystem($seoname);
					if (file_exists($targetFile)) {
						$append = '_'.time();
						$seoname = stripSuffix($seoname).$append.'.'.getSuffix($seoname);
						$targetFile =  $targetPath.'/'.internalToFilesystem($seoname);
					}
					if (move_uploaded_file($tempFile,$targetFile)) {
						@chmod($targetFile, FILE_MOD);
						$album = new Album(NULL, $folder);
						$image = newImage($album, $seoname);
						$image->setOwner($_zp_current_admin_obj->getUser());
						if ($name != $seoname && $image->getTitle() == substr($seoname, 0, strrpos($seoname, '.'))) {
							$image->setTitle(stripSuffix($name, '.'));
						}
						$image->save();
					} else {
						$error = UPLOAD_ERR_NO_FILE;
					}
				} else if (is_zip($name)) {
					$error = !unzip($tempFile, $targetPath);
				}
			}
		}
	}
	if ($error) {
		if (is_bool($error)) {
			$error = '';
		} else {
			$error = ' ('.$error.')';
		}
		debugLog(sprintf(gettext('Uploadify error%1$s on %2$s.'),$error,$name));
	}
}

echo '1';

?>