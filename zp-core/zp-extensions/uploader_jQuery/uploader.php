<?php

/*
 * jQuery File Upload Plugin PHP Example 5.2.9
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://creativecommons.org/licenses/MIT/
 */

define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');

$_zp_loggedin = NULL;
if (isset($_POST['auth'])) {
	$hash = sanitize($_POST['auth']);
	$id = sanitize($_POST['id']);
	$_zp_loggedin = $_zp_authority->checkAuthorization($hash, $id);
}

admin_securityChecks(UPLOAD_RIGHTS, $return = currentRelativeURL());

$folder = zp_apply_filter('admin_upload_process', sanitize_path($_POST['folder']));
$types = array_keys($_zp_extra_filetypes);
$types = array_merge($_zp_supported_images, $types);
$types = zp_apply_filter('upload_filetypes', $types);

$options = array(
				'upload_dir'				 => $targetPath = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($folder) . '/',
				'upload_url'				 => imgSrcURI(ALBUM_FOLDER_WEBPATH . $folder) . '/',
				'accept_file_types'	 => '/(' . implode('|\.', $types) . ')$/i'
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
		$album->setShow((int) !empty($_POST['publishalbum']));
		$album->setTitle(sanitize($_POST['albumtitle']));
		$album->setOwner($_zp_current_admin_obj->getUser());
		$album->save();
	}
	@chmod($targetPath, FOLDER_MOD);
}

class UploadHandler {

	private $options;

	function __construct($options = null) {
		$this->options = array(
						'script_url'							 => $_SERVER['PHP_SELF'],
						'upload_dir'							 => dirname(__FILE__) . '/files/',
						'upload_url'							 => dirname($_SERVER['PHP_SELF']) . '/files/',
						'param_name'							 => 'files',
						// The php.ini settings upload_max_filesize and post_max_size
						// take precedence over the following max_file_size setting:
						'max_file_size'						 => null,
						'min_file_size'						 => 1,
						'accept_file_types'				 => '/.+$/i',
						'max_number_of_files'			 => null,
						'discard_aborted_uploads'	 => true,
						'image_versions'					 => array(
						// Uncomment the following version to restrict the size of
						// uploaded images. You can also add additional versions with
						// their own upload directories:
						/*
						  'large' => array(
						  'upload_dir' => dirname(__FILE__).'/files/',
						  'upload_url' => dirname($_SERVER['PHP_SELF']).'/files/',
						  'max_width' => 1920,
						  'max_height' => 1200
						  ),

						  'thumbnail' => array(
						  'upload_dir' => dirname(__FILE__).'/thumbnails/',
						  'upload_url' => dirname($_SERVER['PHP_SELF']).'/thumbnails/',
						  'max_width' => 80,
						  'max_height' => 80
						  )
						 */
						)
		);
		if ($options) {
			$this->options = array_replace_recursive($this->options, $options);
		}
	}

	private function get_file_object($file_name) {
		$file_path = $this->options['upload_dir'] . $file_name;
		if (is_file($file_path) && $file_name[0] !== '.') {
			$file = new stdClass();
			$file->name = $file_name;
			$file->size = filesize($file_path);
			$file->url = $this->options['upload_url'] . rawurlencode($file->name);
			foreach ($this->options['image_versions'] as $version => $options) {
				if (is_file($options['upload_dir'] . $file_name)) {
					$file->{$version . '_url'} = $options['upload_url']
									. rawurlencode($file->name);
				}
			}
			$file->delete_url = $this->options['script_url']
							. '?file=' . rawurlencode($file->name);
			$file->delete_type = 'DELETE';
			return $file;
		}
		return null;
	}

	private function get_file_objects() {
		return array_values(array_filter(array_map(
														array($this, 'get_file_object'), scandir($this->options['upload_dir'])
		)));
	}

	private function create_scaled_image($file_name, $options) {
		$file_path = $this->options['upload_dir'] . $file_name;
		$new_file_path = $options['upload_dir'] . $file_name;
		list($img_width, $img_height) = @getimagesize($file_path);
		if (!$img_width || !$img_height) {
			return false;
		}
		$scale = min(
						$options['max_width'] / $img_width, $options['max_height'] / $img_height
		);
		if ($scale > 1) {
			$scale = 1;
		}
		$new_width = $img_width * $scale;
		$new_height = $img_height * $scale;
		$new_img = @imagecreatetruecolor($new_width, $new_height);
		switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
			case 'jpg':
			case 'jpeg':
				$src_img = @imagecreatefromjpeg($file_path);
				$write_image = 'imagejpeg';
				break;
			case 'gif':
				@imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
				$src_img = @imagecreatefromgif($file_path);
				$write_image = 'imagegif';
				break;
			case 'png':
				@imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
				@imagealphablending($new_img, false);
				@imagesavealpha($new_img, true);
				$src_img = @imagecreatefrompng($file_path);
				$write_image = 'imagepng';
				break;
			default:
				$src_img = $image_method = null;
		}
		$success = $src_img && @imagecopyresampled(
										$new_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height
						) && $write_image($new_img, $new_file_path);
		// Free up memory (imagedestroy does not delete files):
		@imagedestroy($src_img);
		@imagedestroy($new_img);
		return $success;
	}

	private function has_error($uploaded_file, $file, $error) {
		if ($error) {
			return $error;
		}
		if (!preg_match($this->options['accept_file_types'], $file->name)) {
			return 'acceptFileTypes';
		}
		if ($uploaded_file && is_uploaded_file($uploaded_file)) {
			$file_size = filesize($uploaded_file);
		} else {
			$file_size = $_SERVER['CONTENT_LENGTH'];
		}
		if ($this->options['max_file_size'] && (
						$file_size > $this->options['max_file_size'] ||
						$file->size > $this->options['max_file_size'])
		) {
			return 'maxFileSize';
		}
		if ($this->options['min_file_size'] &&
						$file_size < $this->options['min_file_size']) {
			return 'minFileSize';
		}
		if (is_int($this->options['max_number_of_files']) && (
						count($this->get_file_objects()) >= $this->options['max_number_of_files'])
		) {
			return 'maxNumberOfFiles';
		}
		return $error;
	}

	private function trim_file_name($name, $type) {
		// Remove path information and dots around the filename, to prevent uploading
		// into different directories or replacing hidden system files.
		// Also remove control characters and spaces (\x00..\x20) around the filename:
		$file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
		// Add missing file extension for known image types:
		if (strpos($file_name, '.') === false &&
						preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
			$file_name .= '.' . $matches[1];
		}
		return $file_name;
	}

	private function handle_file_upload($uploaded_file, $name, $size, $type, $error) {
		global $folder, $targetPath, $_zp_current_admin_obj;
		$file = new stdClass();
		$name = $this->trim_file_name($name, $type);
		$seoname = seoFriendly($name);
		if (strrpos($seoname, '.') === 0)
			$seoname = sha1($name) . $seoname; // soe stripped out all the name.
		$targetFile = $targetPath . '/' . internalToFilesystem($seoname);
		if (file_exists($targetFile)) {
			$append = '_' . time();
			$seoname = stripSuffix($seoname) . $append . '.' . getSuffix($seoname);
			$targetFile = $targetPath . '/' . internalToFilesystem($seoname);
		}
		$file->name = $seoname;

		$file->size = intval($size);
		$file->type = $type;
		$error = $this->has_error($uploaded_file, $file, $error);
		if (!$error && $file->name) {
			$file_path = $this->options['upload_dir'] . $file->name;
			$append_file = !$this->options['discard_aborted_uploads'] &&
							is_file($file_path) && $file->size > filesize($file_path);
			clearstatcache();
			if ($uploaded_file && is_uploaded_file($uploaded_file)) {
				// multipart/formdata uploads (POST method uploads)
				if ($append_file) {
					file_put_contents($file_path, fopen($uploaded_file, 'r'), FILE_APPEND);
				} else {
					move_uploaded_file($uploaded_file, $file_path);
					if (is_valid_image($name) || is_valid_other_type($name)) {
						@chmod($targetFile, FILE_MOD);
						$album = newAlbum($folder);
						$image = newImage($album, $seoname);
						$image->setOwner($_zp_current_admin_obj->getUser());
						if ($name != $seoname && $image->getTitle() == substr($seoname, 0, strrpos($seoname, '.'))) {
							$image->setTitle(stripSuffix($name, '.'));
						}
						$image->save();
					} else if (is_zip($targetFile)) {
						unzip($targetFile, $targetPath);
						unlink($targetFile);
					} else {
						$error = UPLOAD_ERR_EXTENSION; // invalid file uploaded
						break;
					}
				}
			} else {
				// Non-multipart uploads (PUT method support)
				file_put_contents(
								$file_path, fopen('php://input', 'r'), $append_file ? FILE_APPEND : 0);
			}
			$file_size = filesize($file_path);
			if ($file_size === $file->size) {
				$file->url = $this->options['upload_url'] . rawurlencode($file->name);
				foreach ($this->options['image_versions'] as $version => $options) {
					if ($this->create_scaled_image($file->name, $options)) {
						$file->{$version . '_url'} = $options['upload_url'] . rawurlencode($file->name);
					}
				}
			} else if ($this->options['discard_aborted_uploads']) {
				@chmod($file_path, 0666);
				unlink($file_path);
				$file->error = 'abort';
			}
			$file->size = $file_size;
			$file->delete_url = $this->options['script_url'] . '?file=' . rawurlencode($file->name);
			$file->delete_type = 'DELETE';
		} else {
			$file->error = $error;
		}
		return $file;
	}

	public function get() {
		$file_name = isset($_REQUEST['file']) ?
						basename(stripslashes($_REQUEST['file'])) : null;
		if ($file_name) {
			$info = $this->get_file_object($file_name);
		} else {
			$info = $this->get_file_objects();
		}
		header('Content-type: application/json');
		echo json_encode($info);
	}

	public function post() {
		$upload = isset($_FILES[$this->options['param_name']]) ?
						$_FILES[$this->options['param_name']] : null;
		$info = array();
		if ($upload && is_array($upload['tmp_name'])) {
			foreach ($upload['tmp_name'] as $index => $value) {
				$info[] = $this->handle_file_upload(
								$upload['tmp_name'][$index], isset($_SERVER['HTTP_X_FILE_NAME']) ?
												$_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index], isset($_SERVER['HTTP_X_FILE_SIZE']) ?
												$_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index], isset($_SERVER['HTTP_X_FILE_TYPE']) ?
												$_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index], $upload['error'][$index]
				);
			}
		} elseif ($upload) {
			$info[] = $this->handle_file_upload(
							$upload['tmp_name'], isset($_SERVER['HTTP_X_FILE_NAME']) ?
											$_SERVER['HTTP_X_FILE_NAME'] : $upload['name'], isset($_SERVER['HTTP_X_FILE_SIZE']) ?
											$_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'], isset($_SERVER['HTTP_X_FILE_TYPE']) ?
											$_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'], $upload['error']
			);
		}
		header('Vary: Accept');
		if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
			header('Content-type: application/json');
		} else {
			header('Content-type: text/plain');
		}
		echo json_encode($info);
	}

}

$upload_handler = new UploadHandler($options);

header('Pragma: no-cache');
header('Cache-Control: private, no-cache');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');

switch ($_SERVER['REQUEST_METHOD']) {
	case 'POST':
		$upload_handler->post();
		break;
	case 'OPTIONS':
		break;
	default:
		header('HTTP/1.0 405 Method Not Allowed');
}
?>