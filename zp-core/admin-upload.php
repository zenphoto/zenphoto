<?php
/**
 * provides the Upload tab of admin
 * @package admin
 */

// force UTF-8 Ø

define('OFFSET_PATH', 1);
define('UPLOAD_ERR_QUOTA', -1);

require_once(dirname(__FILE__).'/admin-globals.php');

admin_securityChecks(UPLOAD_RIGHTS, $return = currentRelativeURL(__FILE__));

if (isset($_GET['uploadtype'])) {
	$uploadtype = sanitize($_GET['uploadtype'])	;
} else {
	$uploadtype = zp_getcookie('uploadtype');
}

if (!file_exists(SERVERPATH.'/'.ZENFOLDER.'/admin-'.$uploadtype.'/upload_form.php')) {
	$uploadtype = 'httpupload';
}
require_once(SERVERPATH.'/'.ZENFOLDER.'/admin-'.$uploadtype.'/upload_form.php');
zp_setCookie('uploadtype', $uploadtype);

$gallery = new Gallery();
$page = "upload";
$_GET['page'] = 'upload';

printAdminHeader('upload','albums');
?>
<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER;?>/js/upload.js"></script>
<?php
//	load the uploader specific header stuff
upload_head();

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
<?php zp_apply_filter('admin_note','upload', 'images'); ?>
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

	<div id="albumselect">

		<form name="file_upload_datum" id="file_upload_datum" action="" method="post" >
			<input type="hidden" name="processed" id="processed" value="1" />
			<input type="hidden" name="existingfolder" id="existingfolder" value="false" />

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

		</form>

		<div id="upload_action">
		<?php
		//	load the uploader specific form stuff
		upload_form($uploadlimit);
		?>
		</div><!-- upload aaction -->
		<script type="text/javascript">
			//<!-- <![CDATA[
			<?php
			echo zp_apply_filter('upload_helper_js', '')."\n";
			if ($passedalbum) {
				?>
				$('#folderdisplay').val('<?php echo $passedalbum; ?>');
				buttonstate(true);
				<?php
			}
			?>
			albumSwitch(document.getElementById('albumselectmenu'),false,'<?php echo gettext('That name is already used.'); ?>','<?php echo gettext('This upload has to have a folder. Type a title or folder name to continue...'); ?>');
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
							buttonstate(true);
						}
						<?php
					} else {
						?>
						$('#autogen').removeAttr('checked');
						$('#folderdisplay').removeAttr('disabled');
						if ($('#folderdisplay').val() != '') {
							$('#foldererror').hide();
							buttonstate(false);
						}
						<?php
					}
				}
			?>
			// ]]> -->
		</script>
		<?php
	} else {
		echo gettext("There are no albums to which you can upload.");
	}
	?>
	</div><!-- albumselect -->

</div><!-- tabbox -->
</div><!-- content -->
</div><!-- main -->
<?php
printAdminFooter();
?>
</body>
</html>




