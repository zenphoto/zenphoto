<?php
function upload_head() {
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		window.totalinputs = 5;
		// ]]> -->
	</script>
	<?php
}

function upload_form($uploadlimit) {
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		function http_submit() {
			$('#http_processed').val($('#processed').val());
			$('#http_publishalbum').val($('#publishalbum').val());
			$('#http_albumtitle').val($('#albumtitle').val());
			$('#http_folder').val($('#folderdisplay').val());
			$('#http_existingfolder').val($('#existingfolder').val());
			return true;
		}
		// ]]> -->
	</script>
	<form name="file_upload" id="file_upload" action="?action=upload&amp;uploadtype=httpupload"
				enctype="multipart/form-data" method="post" onsubmit="http_submit()">
		<?php XSRFToken('upload');?>
		<input type="hidden" name="http_processed" id="http_processed" value="1" />
		<input type="hidden" name="http_publishalbum" id="http_publishalbum" value="1" />
		<input type="hidden" name="http_albumtitle" id="http_albumtitle" value="" />
		<input type="hidden" name="http_folder" id="http_folder" value="/" />
		<input type="hidden" name="http_existingfolder" id="http_existingfolder" value="false" />
		<div id="fileUploadbuttons" style="display: none;">
			<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>
			<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>
			<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>
			<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>
			<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>

			<div id="place" style="display: none;"></div>
			<!-- New boxes get inserted before this -->

			<div style="display:none">
			<!-- This is the template that others are copied from -->
			<div class="fileuploadbox" id="filetemplate" ><input type="file" size="40" name="files[]" value="x" /></div>
			</div>
			<p id="addUploadBoxes"><a href="javascript:addUploadBoxes('place','filetemplate',5)" title="<?php echo gettext("Doesn't reload!"); ?>">+ <?php echo gettext("Add more upload boxes"); ?></a>
				<small><?php echo gettext("(won't reload the page, but remember your upload limits!)"); ?></small>
			</p>

			<p class="buttons">
				<button type="submit" value="<?php echo gettext('Upload'); ?>" class="button">
					<img src="images/pass.png" alt="" /><?php echo gettext('Upload'); ?>
				</button>
			</p>
			<br /><br clear="all" />
		</div>
	</form>
	<p id="uploadswitch"><?php echo gettext('Try the <a href="javascript:switchUploader(\'admin-upload.php?uploadtype=uploadify\');" >multi file upload</a>'); ?></p>
	<?php
}

function handle_upload() {
	global $_zp_current_admin_obj;
	$gallery = new Gallery();
	$error = false;
	if (isset($_POST['http_processed'])) {	// sometimes things just go terribly wrong!
		XSRFdefender('upload');
		// Check for files.
		if (isset($_FILES['files'])) {
			foreach($_FILES['files']['name'] as $key=>$name) {
				if (empty($name)) {	// purge empty slots
					unset($_FILES['files']['name'][$key]);
					unset($_FILES['files']['type'][$key]);
					unset($_FILES['files']['tmp_name'][$key]);
					unset($_FILES['files']['error'][$key]);
					unset($_FILES['files']['size'][$key]);
				}
			}
		}
		$files_empty = count($_FILES['files']) == 0;
		$newAlbum = ((isset($_POST['http_existingfolder']) && $_POST['http_existingfolder'] == 'false') || isset($_POST['newalbum']));
		// Make sure the folder exists. If not, create it.
		if (isset($_POST['http_processed']) && !empty($_POST['http_folder']) && ($newAlbum || !$files_empty)) {
			$folder = zp_apply_filter('admin_upload_process',trim(sanitize_path($_POST['folder'])));

			if ($newAlbum) {
				$rightsalbum = new Album($gallery, dirname($folder));
			} else{
				$rightsalbum = new Album($gallery, $folder);
			}
			// see if he has rights to the album.
			$modified_rights = $rightsalbum->isMyItem(UPLOAD_RIGHTS);
			if (!$modified_rights) {
				if (!zp_apply_filter('admin_managed_albums_access',false, $return)) {
					$error = UPLOAD_ERR_CANT_WRITE;
				}
			}

			if (!$error) {
				$uploaddir = $gallery->albumdir . internalToFilesystem($folder);
				if (!is_dir($uploaddir)) {
					mkdir_recursive($uploaddir, CHMOD_VALUE);
				}
				@chmod($uploaddir, CHMOD_VALUE);
				$album = new Album($gallery, $folder);
				if ($album->exists) {
					if (!isset($_POST['http_publishalbum'])) {
						$album->setShow(false);
					}
					$title = sanitize($_POST['http_albumtitle'], 2);
					if ($newAlbum) {
						$album->setOwner($_zp_current_admin_obj->getUser());
						if (!empty($title)) {
							$album->setTitle($title);
						}
					}
					$album->save();
				} else {
					$AlbumDirName = str_replace(SERVERPATH, '', $gallery->albumdir);
					zp_error(gettext("The album couldn't be created in the 'albums' folder. This is usually a permissions problem. Try setting the permissions on the albums and cache folders to be world-writable using a shell:")." <code>chmod 777 " . $AlbumDirName . '/'.CACHEFOLDER.'/' ."</code>, "
													.gettext("or use your FTP program to give everyone write permissions to those folders."));
				}
				foreach ($_FILES['files']['error'] as $key => $error) {
					if ($error == UPLOAD_ERR_OK) {
						$tmp_name = $_FILES['files']['tmp_name'][$key];
						$name = trim($_FILES['files']['name'][$key]);
						$seoname = seoFriendly($name);
						$error = zp_apply_filter('check_upload_quota', UPLOAD_ERR_OK, $tmp_name);
						if (!$error) {
							if (is_valid_image($name) || is_valid_other_type($name)) {
								if (strrpos($seoname,'.')===0) $seoname = sha1($name).$seoname; // soe stripped out all the name.
								if (!$error) {
									$uploadfile = $uploaddir . '/' . internalToFilesystem($seoname);
									if (file_exists($uploadfile)) {
										$append = '_'.time();
										$seoname = stripSuffix($seoname).$append.'.'.getSuffix($seoname);
										$uploadfile = $uploaddir . '/' . internalToFilesystem($seoname);
									}
									move_uploaded_file($tmp_name, $uploadfile);
									@chmod($uploadfile, 0666 & CHMOD_VALUE);
									$image = newImage($album, $seoname);
									$image->setOwner($_zp_current_admin_obj->getUser());
									if ($name != $seoname) {
										$image->setTitle($name);
									}
									$image->save();
								}
							} else if (is_zip($name)) {
								unzip($tmp_name, $uploaddir);
							} else {
								$error = UPLOAD_ERR_EXTENSION;	// invalid file uploaded
								break;
							}
						}
					} else {
						break;
					}
				}
			}
		}
	}
	// Handle the error and return to the upload page.
	if (!isset($_POST['http_processed'])) {
		$errormsg = gettext("You've most likely exceeded the upload limits. Try uploading fewer files at a time, or use a ZIP file.");
	} else if ($files_empty && !isset($_POST['http_newalbum'])) {
		$errormsg = gettext("You must upload at least one file.");
	} else if (empty($_POST['http_folder'])) {
		$errormsg = gettext("You must enter a folder name for your new album.");
	} else {
		switch ($error) {
			case UPLOAD_ERR_CANT_WRITE:
				$errormsg = gettext('You have attempted to upload to an album for which you do not have upload rights');
				break;
			case UPLOAD_ERR_EXTENSION:
				$errormsg = gettext('You have attempted to upload one or more files which are not Zenphoto supported file types');
				break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$errormsg = gettext('You have attempted to upload too large a file');
				break;
			case UPLOAD_ERR_QUOTA:
				$errormsg = gettext('You have exceeded your upload quota');
				break;
			default:
				$errormsg = sprintf(gettext("The error %s was reported when submitting the form. Please try again. If this keeps happening, check your server and PHP configuration (make sure file uploads are enabled, and upload_max_filesize is set high enough.) If you think this is a bug, file a bug report. Thanks!"),$error);
				break;
		}
	}

	if ($error == UPLOAD_ERR_OK) {
		if ($modified_rights & (ALBUM_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) {
			header('Location: '.FULLWEBPATH.'/'.ZENFOLDER.'/admin-edit.php?page=edit&album='.pathurlencode($folder).'&uploaded&subpage=1&tab=imageinfo&albumimagesort=id_desc');
		} else {
			header('Location: '.FULLWEBPATH.'/'.ZENFOLDER.'/admin-upload.php?uploaded=1');
		}
		exit();
	}
	return array($errormsg,$error);
}
?>