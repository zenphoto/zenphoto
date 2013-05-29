<?php
/**
 * provides the Upload tab of admin
 * @package admin
 */

// force UTF-8 Ø

define('OFFSET_PATH', 1);

require_once(dirname(__FILE__).'/admin-globals.php');

admin_securityChecks(UPLOAD_RIGHTS | FILES_RIGHTS, $return = currentRelativeURL());

if (isset($_GET['uploadtype'])) {
	$uploadtype = sanitize($_GET['uploadtype'])	;
} else {
	$uploadtype = zp_getcookie('uploadtype');
}

$handlers = array_keys($uploadHandlers = zp_apply_filter('upload_handlers',array()));
if (!zp_loggedin(UPLOAD_RIGHTS) || empty($handlers)) {
	//	redirect to the files page if present
	if (isset($zenphoto_tabs['upload'])) {
		header('location: '.$zenphoto_tabs['upload']['link']);
		exit();
	}
	$handlers = array();
}

if (count($handlers) > 0) {
	if (!isset($uploadHandlers[$uploadtype]) || !file_exists($uploadHandlers[$uploadtype].'/upload_form.php')) {
		$uploadtype = array_shift($handlers);
	}
	require_once($uploadHandlers[$uploadtype].'/upload_form.php');
	zp_setCookie('uploadtype', $uploadtype);
} else {

	require_once(SERVERPATH.'/'.ZENFOLDER.'/no_uploader.php');
	exitZP();
}

$page = "upload";
$_GET['page'] = 'upload';

printAdminHeader('upload','albums');
?>
<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER;?>/js/upload.js"></script>
<?php
//	load the uploader specific header stuff
$formAction = upload_head();

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
			if (!empty($zenphoto_tabs['upload']['subtabs'])) {
				printSubtabs();
			}
			$albumlist = array();
			genAlbumList($albumlist);
			//	remove dynamic albums--can't upload to them
			foreach ($albumlist as $key=>$albumname) {
				if(hasDynamicAlbumSuffix($key)) {
					unset($albumlist[$key]);
				}
			}

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
	?>
	<br />
	<?php
	if ($last == 'ZIP') {
		echo gettext('ZIP files must contain only Zenphoto supported <em>image</em> types.');
		?>
		<br />
		<?php
	}
	$maxupload = ini_get('upload_max_filesize');
	$maxpost = ini_get('post_max_size');
	$maxuploadint = parse_size($maxupload);
	$maxpostint = parse_size($maxpost);
	if ($maxuploadint < $maxpostint) {
		echo sprintf(gettext("The maximum size for any one file is <strong>%sB</strong> and the maximum size for one total upload is <strong>%sB</strong> which are set by your PHP configuration <code>upload_max_filesize</code> and <code>post_max_size</code>."), $maxupload, $maxpost);
	} else {
		echo ' '.sprintf(gettext("The maximum size for your total upload is <strong>%sB</strong> which is set by your PHP configuration <code>post_max_size</code>."), $maxpost);
	}
	$uploadlimit = zp_apply_filter('get_upload_limit', $maxuploadint);
	$maxuploadint = min($maxuploadint, $uploadlimit);
	?>
	<br />
	<?php
	echo zp_apply_filter('get_upload_header_text', gettext('Don\'t forget, you can also use <acronym title="File Transfer Protocol">FTP</acronym> to upload folders of images into the albums directory!'));
	?>
</p>
<?php
if (isset($_GET['error'])) {
	$errormsg = sanitize($_GET['error']);
	?>
	<div class="errorbox fade-message">
		<h2><?php echo gettext("Upload Error"); ?></h2>
		<?php echo (empty($errormsg) ? gettext("There was an error submitting the form. Please try again.") : $errormsg); ?>
	</div>
	<?php
}
if (isset($_GET['uploaded'])) {
	?>
	<div class="messagebox fade-message">
		<h2><?php echo gettext("Upload complete"); ?></h2>
		<?php echo gettext('Your files have been uploaded.'); ?>
	</div>
	<?php
}
if (SAFE_MODE) {
?>
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
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		<?php 	seoFriendlyJS(); ?>
		function buttonstate(good) {
			$('#albumtitleslot').val($('#albumtitle').val());
			$('#publishalbumslot').val($('#publishalbum').prop('checked'));
			if (good) {
				$('#fileUploadbuttons').show();
			} else {
				$('#fileUploadbuttons').hide();
			}
		}
		function publishCheck() {
			$('#publishalbumslot').val($('#publishalbum').prop('checked'));
		}
		function albumSelect() {
			var sel = document.getElementById('albumselectmenu');
			var selected = sel.options[sel.selectedIndex].value;
			$('#folderslot').val(selected);
			var state = albumSwitch(sel, true, '<?php echo gettext('That name is already used.'); ?>','<?php echo gettext('This upload has to have a folder. Type a title or folder name to continue...'); ?>');
			buttonstate(state);
		}
		// ]]> -->
	</script>
	<div id="albumselect">

		<form name="file_upload_datum" id="file_upload_datum" method="post" action="<?php  echo $formAction; ?>" enctype="multipart/form-data" >

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
						$alist = array_keys($albumlist);
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
					$publishchecked = ' checked="checked"';
				} else {
					if ($albpublish = $_zp_gallery->getAlbumPublish()) {
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
				$rightsalbum = $rightsalbum = newAlbum($passedalbum);
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
					<input type="checkbox" name="publishalbum" id="publishalbum" value="1" <?php echo $publishchecked; ?> onchange="publishCheck();" />
					<label for="publishalbum"><?php echo gettext("Publish the album so everyone can see it."); ?></label>
				</div>
			</div>
			<div id="albumtext" style="margin-top: 5px;<?php echo $display; ?>">
				<?php echo gettext("titled:"); ?>
				<input type="text" name="albumtitle" id="albumtitle" size="42"
											onkeyup="buttonstate(updateFolder(this, 'folderdisplay', 'autogen','<?php echo gettext('That name is already used.'); ?>','<?php echo gettext('This upload has to have a folder. Type a title or folder name to continue...'); ?>'));" />

				<div style="position: relative; margin-top: 4px;">
					<?php echo gettext("with the folder name:"); ?>
					<div id="foldererror" style="display: none; color: #D66; position: absolute; z-index: 100; top: 2.5em; left: 0px;"></div>
					<input type="text" name="folderdisplay" disabled="disabled" id="folderdisplay" size="18"
												onkeyup="buttonstate(validateFolder(this,'<?php echo gettext('That name is already used.'); ?>','<?php echo gettext('This upload has to have a folder. Type a title or folder name to continue...'); ?>'));" />
					<input type="checkbox" name="autogenfolder" id="autogen" checked="checked"
												onclick="buttonstate(toggleAutogen('folderdisplay', 'albumtitle', this));" />
												<label for="autogen"><?php echo gettext("Auto-generate"); ?></label>
					<br />
					<br />
				</div>
			</div>
			<hr />
			<?php upload_form($uploadlimit, $passedalbum); ?>
		</form>
		<div id="upload_action">
			<?php
			//	load the uploader specific form stuff
			upload_extra($uploadlimit, $passedalbum);
			?>
		</div><!-- upload action -->

		<script type="text/javascript">
			//<!-- <![CDATA[
			<?php
			echo zp_apply_filter('upload_helper_js', '')."\n";
			if ($passedalbum) {
				?>
				buttonstate(true);
				$('#folderdisplay').val('<?php echo html_encode($passedalbum); ?>');
				<?php
			}
			?>
			albumSwitch(document.getElementById('albumselectmenu'),false,'<?php echo gettext('That name is already used.'); ?>','<?php echo gettext('This upload has to have a folder. Type a title or folder name to continue...'); ?>');
			<?php
				if (isset($_GET['folderdisplay'])) {
					?>
					$('#folderdisplay').val('<?php echo html_encode(sanitize($_GET['folderdisplay'])); ?>');
					<?php
				}
				if (isset($_GET['albumtitle'])) {
					?>
					$('#albumtitle').val('<?php echo html_encode(sanitize($_GET['albumtitle'])); ?>');
					<?php
				}
				if (isset($_GET['autogen']) && !$_GET['autogen']) {
					?>
					$('#autogen').prop('checked',false);
					$('#folderdisplay').removeAttr('disabled');
					if ($('#folderdisplay').val() != '') {
						$('#foldererror').hide();
					}
					<?php
				} else {
					?>
					$('#autogen').checked;
					$('#folderdisplay').attr('disabled', 'disabled');
					if ($('#albumtitle').val() != '') {
						$('#foldererror').hide();
					}
					<?php
				}
				?>
				buttonstate($('#folderdisplay').val() != '');
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




