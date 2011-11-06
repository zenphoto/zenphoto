<?php
define('OFFSET_PATH', 3);
require_once(dirname(dirname(__FILE__)).'/admin-functions.php');

$_zp_loggedin = NULL;
if (isset($_POST['auth'])) {
	$hash = sanitize($_POST['auth']);
	$id = sanitize($_POST['id']);
	$_zp_loggedin = $_zp_authority->checkAuthorization($hash, $id);
}

admin_securityChecks(UPLOAD_RIGHTS, $return = currentRelativeURL(__FILE__));
if (!empty($_FILES)) {
	$gallery = new Gallery();
	$name = trim(basename(sanitize($_FILES['file']['name'],3)));
	if (isset($_FILES['Filedata']['error']) && $_FILES['file']['error']) {
		$error = $_FILES['Filedata']['error'];
		debugLogArray('Uploadify error:', $_FILES);
		trigger_error(sprintf(gettext('Uploadify error on %1$s. Review your debug log.'),$name));
	} else {
		$tempFile = sanitize($_FILES['file']['tmp_name'],3);
		$folder = trim(sanitize($_POST['http_folder'],3));
		if (substr($folder,0,1) == '/') {
			$folder = substr($folder,1);
		}
		if (substr($folder,0,1) == '/') {
			$folder = substr($folder,1);
		}
		if (substr($folder,-1) == '/') {
			$folder = substr($folder,0,-1);
		}
		$folder = zp_apply_filter('admin_upload_process',$folder);
		$targetPath = ALBUM_FOLDER_SERVERPATH.internalToFilesystem($folder);
		$new = !is_dir($targetPath);
		if (!empty($folder)) {
			if ($new) {
				$rightsalbum = new Album($gallery, dirname($folder));
			} else{
				$rightsalbum = new Album($gallery, $folder);
			}
			if (!$rightsalbum->isMyItem(UPLOAD_RIGHTS)) {
				if (!zp_apply_filter('admin_managed_albums_access',false, $return)) {
					header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
					exit();
				}
			}
			if ($new) {
				mkdir_recursive($targetPath, CHMOD_VALUE);
				$album = new Album($gallery, $folder);
				$album->setShow($_POST['http_publishalbum']);
				$album->setTitle(sanitize($_POST['http_albumtitle']));
				$album->setOwner($_zp_current_admin_obj->getUser());
				$album->save();
			}
			@chmod($targetPath, CHMOD_VALUE);
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
						@chmod($targetFile, 0666 & CHMOD_VALUE);
						$album = new Album($gallery, $folder);
						$image = newImage($album, $seoname);
						$image->setOwner($_zp_current_admin_obj->getUser());
						if ($name != $seoname && $image->getTitle() == substr($seoname, 0, strrpos($seoname, '.'))) {
							$image->setTitle(substr($name, 0, strrpos($name, '.')));
						}
						$image->save();
					} else {
						$error = UPLOAD_ERR_NO_FILE;
					}
				} else if (is_zip($name)) {
					unzip($tempFile, $targetPath);
				}
			}
		}
	}
}

$file = $_FILES['file'];
echo '{"name":"'.$file['name'].'","type":"'.$file['type'].'","size":"'.$file['size'].'","error":'.$error.'}';

?>