<?php
/**
 * provides the Upload tab of admin
 * @package admin
 */

// force UTF-8 Ø

define('OFFSET_PATH', 1);
define('UPLOAD_ERR_QUOTA', -1);

require_once(dirname(__FILE__).'/admin-functions.php');
require_once(dirname(__FILE__).'/admin-globals.php');

admin_securityChecks(UPLOAD_RIGHTS, $return = currentRelativeURL(__FILE__));

$uploadtype = zp_getcookie('uploadtype');
if (isset($_GET['uploadtype'])) {
	$uploadtype = sanitize($_GET['uploadtype'])	;
	zp_setcookie('uploadtype', $uploadtype);
}
if (empty($uploadtype)) $uploadtype = 'multifile';
$gallery = new Gallery();

/* handle posts */
if (isset($_GET['action'])) {
	if ($_GET['action'] == 'upload') {
		$error = false;
		if (isset($_POST['processed'])) {	// sometimes things just go terribly wrong!
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

			$newAlbum = ((isset($_POST['existingfolder']) && $_POST['existingfolder'] == 'false') || isset($_POST['newalbum']));
			// Make sure the folder exists. If not, create it.
			if (isset($_POST['processed']) && !empty($_POST['folder']) && ($newAlbum || !$files_empty)) {
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
						if (!isset($_POST['publishalbum'])) {
							$album->setShow(false);
						}
						$title = sanitize($_POST['albumtitle'], 2);
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
						. gettext("or use your FTP program to give everyone write permissions to those folders."));
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
					if ($error == UPLOAD_ERR_OK) {
						if ($modified_rights & (ALBUM_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) {
							header('Location: '.FULLWEBPATH.'/'.ZENFOLDER.'/admin-edit.php?page=edit&album='.pathurlencode($folder).'&uploaded&subpage=1&tab=imageinfo&albumimagesort=id_desc');
						} else {
							header('Location: '.FULLWEBPATH.'/'.ZENFOLDER.'/admin-upload.php?uploaded=1');
						}
						exit();
					}
				}
			}
		}
		// Handle the error and return to the upload page.
		$page = "upload";
		$_GET['page'] = 'upload';
		if (!isset($_POST['processed'])) {
			$errormsg = gettext("You've most likely exceeded the upload limits. Try uploading fewer files at a time, or use a ZIP file.");
		} else if ($files_empty && !isset($_POST['newalbum'])) {
			$errormsg = gettext("You must upload at least one file.");
		} else if (empty($_POST['folder'])) {
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
		$error = true;
	}
}

printAdminHeader('upload','albums');
/* MULTI FILE UPLOAD: Script additions */ ?>
<link rel="stylesheet" href="admin-uploadify/uploadify.css" type="text/css" />
<script type="text/javascript">
	//<!-- <![CDATA[
	var uploadifier_replace_message =  "<?php echo gettext('Do you want to replace the file %s?'); ?>";
	var uploadifier_queue_full_message =  "<?php echo gettext('Upload queue is full. The upload limit is %u.'); ?>";
	// ]]> -->
</script>

<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER;?>/js/sprintf.js"></script>
<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER;?>/js/upload.js"></script>
<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER;?>/js/flash_detect_min.js"></script>
<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER;?>/admin-uploadify/jquery.uploadify.js"></script>
<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER;?>/admin-uploadify/swfobject.js"></script>
<?php
echo "\n</head>";
echo "\n<body>";
printLogoAndLinks();
?>
<div id="main">
	<?php
	printTabs();
	?>
		<div id="content">
			<?php
			if (zp_loggedin(FILES_RIGHTS)) {
				printSubtabs();
			}
			$albumlist = array();
			genAlbumUploadList($albumlist);
			?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				window.totalinputs = 5;
				// Array of album names for javascript functions.
				var albumArray = new Array (
					<?php
					$separator = '';
					foreach($albumlist as $key => $value) {
						echo $separator . "'" . addslashes($key) . "'";
						$separator = ", ";
					}
					?> );
				// ]]> -->
			</script>

<div class="tabbox">

<h1><?php echo gettext("Upload Images"); ?></h1>
<p>
<?php
natcasesort($_zp_supported_images);
$types = array_keys($_zp_extra_filetypes);
$types = array_merge($_zp_supported_images, $types);
$types[] = 'ZIP';
$types = zp_apply_filter('upload_filetypes',$types);
natcasesort($types);
$upload_extensions = $types;
$last = strtoupper(array_pop($types));
$s1 = strtoupper(implode(', ', $types));
$used = 0;

if (count($types)>1) {
	printf(gettext('This web-based upload accepts the file formats: %s, and %s.'), $s1, $last);
} else {
	printf(gettext('This web-based upload accepts the file formats: %s and %s.'), $s1, $last);
}
?>
</p>
<p class="notebox">
	<?php
	echo gettext('<strong>Note: </strong>');
	if ($last == 'ZIP') {
		echo gettext('ZIP files must contain only Zenphoto supported <em>image</em> types.');
	}
	$maxupload = ini_get('upload_max_filesize');
	echo ' '.sprintf(gettext("The maximum size for any one file is <strong>%sB</strong> which is set by your PHP configuration <code>upload_max_filesize</code>."), $maxupload);
	$maxupload = parse_size($maxupload);
	$uploadlimit = zp_apply_filter('get_upload_limit', $maxupload);
	$maxupload = min($maxupload, $uploadlimit);
	?>
	<br />
	<?php
	echo zp_apply_filter('get_upload_header_text', gettext('Don\'t forget, you can also use <acronym title="File Transfer Protocol">FTP</acronym> to upload folders of images into the albums directory!'));
	?>
</p>
<?php if (isset($error) && $error) { ?>
	<div class="errorbox fade-message">
		<h2><?php echo gettext("Something went wrong..."); ?></h2>
		<?php echo (empty($errormsg) ? gettext("There was an error submitting the form. Please try again.") : $errormsg); ?>
	</div>
	<?php
}
if (isset($_GET['uploaded'])) {
	?>
	<div class="messagebox fade-message">
		<h2><?php echo gettext("Upload complete"); ?></h2>
		<?php echo zp_apply_filter('get_upload_header_text',gettext('Your files have been uploaded.')); ?>
	</div>
	<?php
}
if (ini_get('safe_mode')) { ?>
<div class="warningbox fade-message">
<h2><?php echo gettext("PHP Safe Mode Restrictions in effect!"); ?></h2>
<p><?php echo gettext("Zenphoto may be unable to perform uploads when PHP Safe Mode restrictions are in effect"); ?></p>
</div>
<?php
}
?>

<form name="uploaderform" id="uploaderform" enctype="multipart/form-data" action="?action=upload&amp;uploadtype=http" method="post"
												onsubmit="return validateFolder(document.uploaderform.folder,'<?php echo gettext('That name is already used.'); ?>','<?php echo gettext('This upload has to have a folder. Type a title or folder name to continue...'); ?>');">
	<?php XSRFToken('upload');?>
	<input type="hidden" name="processed" value="1" />
	<input type="hidden" name="existingfolder" value="false" />

	<div id="albumselect">
	<?php
	$rootrights = zp_apply_filter('upload_root_ui',accessAllAlbums(UPLOAD_RIGHTS));
	if ($rootrights || !empty($albumlist)) {
		echo gettext("Upload to:");
		if (isset($_GET['new'])) {
			$checked = ' checked="checked"';
		} else {
			$checked = '';
		}
		$defaultjs = "
			<script type=\"text/javascript\">
				// <!-- <![CDATA[
				function soejs(fname) {
					fname = fname.replace(/[\!@#$\%\^&*()\~`\'\"]/g, '');
					fname = fname.replace(/^\s+|\s+$/g, '');
					fname = fname.replace(/[^a-zA-Z0-9]/g, '-');
					fname = fname.replace(/--*/g, '-');
					return fname;
				}
				// ]]> -->
			</script>
		";
		echo zp_apply_filter('seoFriendly_js', $defaultjs)."\n";
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			function buttonstate(good) {
				if (good) {
					$('#fileUploadbuttons').show();
				} else {
					$('#fileUploadbuttons').hide();
				}
			}
			function albumSelect() {
				var sel = document.getElementById('albumselectmenu');
				var state = albumSwitch(sel, true, '<?php echo gettext('That name is already used.'); ?>','<?php echo gettext('This upload has to have a folder. Type a title or folder name to continue...'); ?>');
				buttonstate(state);
			}
			// ]]> -->
		</script>
		<select id="albumselectmenu" name="albumselect" onchange="albumSelect()">
			<?php
			if ($rootrights) {
				?>
				<option value="" selected="selected" style="font-weight: bold;">/</option>
				<?php
			}
			$bglevels = array('#fff','#f8f8f8','#efefef','#e8e8e8','#dfdfdf','#d8d8d8','#cfcfcf','#c8c8c8');
			if (isset($_GET['album'])) {
				$passedalbum = sanitize($_GET['album']);
			} else {
				if ($rootrights) {
					$passedalbum = NULL;
				} else {
					$alist = $albumlist;
					$passedalbum = array_shift($alist);
				}
			}
			foreach ($albumlist as $fullfolder => $albumtitle) {
				$singlefolder = $fullfolder;
				$saprefix = "";
				$salevel = 0;
				if (!is_null($passedalbum) && ($passedalbum == $fullfolder)) {
					$selected = " selected=\"selected\" ";
				} else {
					$selected = "";
				}
				// Get rid of the slashes in the subalbum, while also making a subalbum prefix for the menu.
				while (strstr($singlefolder, '/') !== false) {
					$singlefolder = substr(strstr($singlefolder, '/'), 1);
					$saprefix = "&nbsp; &nbsp;&raquo;&nbsp;" . $saprefix;
					$salevel++;
				}
				echo '<option value="' . $fullfolder . '"' . ($salevel > 0 ? ' style="background-color: '.$bglevels[$salevel].'; border-bottom: 1px dotted #ccc;"' : '')
						. "$selected>" . $saprefix . $singlefolder . " (" . $albumtitle . ')' . "</option>\n";
			}
			if (isset($_GET['publishalbum'])) {
				if ($_GET['publishalbum']=='true') {
					$publishchecked = ' checked="checked"';
				} else {
					$publishchecked = '';
				}
			} else {
				if ($albpublish = getOption('album_publish')) {
					$publishchecked = ' checked="checked"';
				} else {
					$publishchecked = '';
				}
			}
			?>
		</select>

		<?php
		if (empty($passedalbum)) {
			$modified_rights = MANAGED_OBJECT_RIGHTS_EDIT;
		} else {
			$rightsalbum = $rightsalbum = new Album($gallery, $passedalbum);
			$modified_rights = $rightsalbum->albumSubRights();
		}
		if ($modified_rights & MANAGED_OBJECT_RIGHTS_EDIT) {	//	he has edit rights, allow new album creation
			$display = '';
		} else {
			$display = ' display:none;';
		}
			?>
			<div id="newalbumbox" style="margin-top: 5px;<?php echo $display; ?>">
				<div>
					<input type="checkbox" name="newalbum" id="newalbumcheckbox"<?php echo $checked; ?> onclick="albumSwitch(this.form.albumselect,false,'<?php echo gettext('That name is already used.'); ?>','<?php echo gettext('This upload has to have a folder. Type a title or folder name to continue...'); ?>')" />
					<label for="newalbumcheckbox"><?php echo gettext("Make a new Album"); ?></label>
				</div>
				<div id="publishtext"><?php echo gettext("and"); ?>
					<input type="checkbox" name="publishalbum" id="publishalbum" value="1" <?php echo $publishchecked; ?> />
					<label for="publishalbum"><?php echo gettext("Publish the album so everyone can see it."); ?></label>
				</div>
			</div>
			<div id="albumtext" style="margin-top: 5px;<?php echo $display; ?>">
				<?php echo gettext("titled:"); ?>
				<input type="text" name="albumtitle" id="albumtitle" size="42"
											onkeyup="buttonstate(updateFolder(this, 'folderdisplay', 'autogen','<?php echo gettext('That name is already used.'); ?>','<?php echo gettext('This upload has to have a folder. Type a title or folder name to continue...'); ?>'));" />

				<div style="position: relative; margin-top: 4px;"><?php echo gettext("with the folder name:"); ?>
					<div id="foldererror" style="display: none; color: #D66; position: absolute; z-index: 100; top: 2.5em; left: 0px;"></div>
					<input type="text" name="folderdisplay" disabled="disabled" id="folderdisplay" size="18"
												onkeyup="buttonstate(validateFolder(this,'<?php echo gettext('That name is already used.'); ?>','<?php echo gettext('This upload has to have a folder. Type a title or folder name to continue...'); ?>'));" />
					<input type="checkbox" name="autogenfolder" id="autogen" checked="checked"
												onclick="buttonstate(toggleAutogen('folderdisplay', 'albumtitle', this));" />
												<label for="autogen"><?php echo gettext("Auto-generate"); ?></label>
					<br />
					<br />
				</div>

				<input type="hidden" name="folder" id="folderslot" value="<?php echo html_encode($passedalbum); ?>" />
			</div>

			<hr />

		<?php
		if($uploadtype != 'http') {
			?>
			<div id="uploadboxes" style="display: none;"></div> <!--  need this so that toggling it does not fail. -->
			<div id="upload_action">
			<!-- UPLOADIFY JQUERY/FLASH MULTIFILE UPLOAD TEST -->
				<script type="text/javascript">
					// <!-- <![CDATA[
					if (FlashDetect.installed) {
						$(document).ready(function() {
							$('#fileUpload').uploadify({
								'uploader': 'admin-uploadify/uploadify.swf',
								'cancelImg': 'images/fail.png',
								'script': 'admin-uploadify/uploader.php',
								'scriptData': {
															'auth': '<?php echo $_zp_current_admin_obj->getPass(); ?>',
															'id': '<?php echo $_zp_current_admin_obj->getID(); ?>',
															'XSRFToken': '<?php echo getXSRFToken('upload')?>'
															},
								'folder': '/',
								'multi': true,
								<?php
								$uploadbutton = SERVERPATH.'/'.ZENFOLDER.'/locale/'.getOption('locale').'/select_files_button.png';
								if(!file_exists($uploadbutton)) {
									$uploadbutton = SERVERPATH.'/'.ZENFOLDER.'/images/select_files_button.png';
								}
								$discard = NULL;
								$info = zp_imageDims($uploadbutton, $discard);
								if ($info['height']>60) {
									$info['height'] = round($info['height']/3);
									$rollover = "'rollover': true,";
								} else {
									$rollover = "";
								}
								$uploadbutton = str_replace(SERVERPATH, WEBPATH, $uploadbutton);
								?>
								'buttonImg': '<?php echo $uploadbutton; ?>',
								'height': '<?php echo $info['height'] ?>',
								'width': '<?php echo $info['width'] ?>',
								<?php echo $rollover; ?>
								'checkScript': 'admin-uploadify/check.php',
<?php
/* Uploadify does not really support this onCheck facility (it is unusable as implemented, this gets called fore each element
										passing the whole queue each time!)
							'onCheck':	function(event, script, queue, folder, single) {

														alert('folder: '+folder);
														alert('single: '+single);
														for (var key in queue ) {
															if (queue[key] != folder) {
																var replaceFile = confirm("Do you want to replace the file " + queue[key] + "?");
																if (!replaceFile) {
																	document.getElementById(jQuery(event.target).attr('id') + 'Uploader').cancelFileUpload(key, true,true);
																}
															}
														}
														return false;
													},
*/
?>
								'displayData': 'speed',
								'simUploadLimit': 3,
								'sizeLimit': <?php echo $uploadlimit; ?>,
								'onAllComplete':	function(event, data) {
																		if (data.errors) {
																			return false;
																		} else {
																		<?php
																		if (zp_loggedin(ALBUM_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) {
																			?>
																			launchScript('admin-edit.php',['page=edit','subpage=1','tab=imageinfo','album='+encodeURIComponent($('#folderdisplay').val()),'uploaded=1','albumimagesort=id_desc']);
																			<?php
																		} else {
																			?>
																			launchScript('admin-upload.php',['uploaded=1']);
																			<?php
																		}
																		?>
																		}
																	},
								'fileDesc': '<?php echo gettext('Zenphoto supported file types | all files'); ?>',
								'fileExt': '<?php
														$list = implode(';*.',$upload_extensions);
														echo '*.'.$list.' | *.*';
														?>'
							});
						buttonstate($('#folderdisplay').val() != "");
					});
					}
					// ]]> -->
				</script>
				<div id="fileUpload" style="color:red">
					<?php echo gettext("There appears to be no <em>Flash</em> plugin installed in your browser."); ?>
				</div>
				<p class="buttons" id="fileUploadbuttons" style="display: none;">
					<a href="javascript:$('#fileUpload').uploadifySettings('folder','/'+$('#publishalbum').attr('checked')+':'+$('#folderdisplay').val()+':'+$('#albumtitle').val());
															$('#fileUpload').uploadifyUpload()"><img src="images/pass.png" alt="" /><?php echo gettext("Upload"); ?></a>
					<a href="javascript:$('#fileUpload').uploadifyClearQueue()"><img src="images/fail.png" alt="" /><?php echo gettext("Cancel"); ?></a>
				<br clear="all" /><br />
				</p>
				<p id="uploadswitch"><?php echo gettext('If your upload does not work try the <a href="javascript:switchUploader(\'admin-upload.php?uploadtype=http\');" >http-browser single file upload</a> or use FTP instead.'); ?></p>
			</div>
			<?php
		} else {
			?>
			<div id="upload_action">
				<div id="uploadboxes" style="display: none;">
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
					<p id="addUploadBoxes"><a href="javascript:addUploadBoxes('place','filetemplate',5)" title="<?php echo gettext("Doesn't reload!"); ?>">+ <?php echo gettext("Add more upload boxes"); ?></a> <small>
					<?php echo gettext("(won't reload the page, but remember your upload limits!)"); ?></small></p>


					<p id="fileUploadbuttons" class="buttons">
						<button type="submit" value="<?php echo gettext('Upload'); ?>"
							onclick="this.form.folder.value = this.form.folderdisplay.value;" class="button">
							<img src="images/pass.png" alt="" /><?php echo gettext('Upload'); ?>
						</button>
					</p>
					<br /><br clear="all" />
				</div>
				<p id="uploadswitch"><?php echo gettext('Try the <a href="javascript:switchUploader(\'admin-upload.php?uploadtype=multifile\');" >multi file upload</a>'); ?></p>
			</div>
			<?php
		}
	} else {
		echo gettext("There are no albums to which you can upload.");
	}
	?>
	</div>
</form>
<script type="text/javascript">
	//<!-- <![CDATA[
	<?php echo zp_apply_filter('upload_helper_js', '')."\n"; ?>

	albumSwitch(document.uploaderform.albumselect,false,'<?php echo gettext('That name is already used.'); ?>','<?php echo gettext('This upload has to have a folder. Type a title or folder name to continue...'); ?>');
	<?php
		if (isset($_GET['folderdisplay'])) {
			?>
			$('#folderdisplay').val('<?php echo sanitize($_GET['folderdisplay']); ?>');
			<?php
		}
		if (isset($_GET['albumtitle'])) {
			?>
			$('#albumtitle').val('<?php echo sanitize($_GET['albumtitle']); ?>');
			<?php
		}
		if (isset($_GET['autogen'])) {
			if ($_GET['autogen'] == 'true') {
				?>
				$('#autogen').attr('checked', 'checked');
				$('#folderdisplay').attr('disabled', 'disabled');
				if ($('#albumtitle').val() != '') {
					$('#foldererror').hide();
					<?php
					if($uploadtype == 'http') {
						?>
						$('#uploadboxes').show();
						buttonstate(true);
						<?php
					}
					?>
				}
				<?php
			} else {
				?>
				$('#autogen').removeAttr('checked');
				$('#folderdisplay').removeAttr('disabled');
				if ($('#folderdisplay').val() != '') {
					<?php
					if($uploadtype == 'http') {
						?>
						$('#uploadboxes').show();
						buttonstate(true);
						<?php
					}
					?>
					$('#foldererror').hide();
					buttonstate(false);
				}
				<?php
			}
		}
	?>
	// ]]> -->
</script>
</div>
</div>
</div>
<?php
printAdminFooter();
?>
</body>
</html>




