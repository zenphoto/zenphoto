<?php
/**
 * Clone Zenphoto tab
 *
 *
 * @package admin
 */

if (!defined('OFFSET_PATH')) define ('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-globals.php');

admin_securityChecks(NULL, currentRelativeURL());

$zenphoto_tabs['overview']['subtabs']=array(gettext('Clone')=>'');
printAdminHeader('overview','clone');

?>
	<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER;?>/js/sprintf.js"></script>
</head>
<body>
<?php printLogoAndLinks(); ?>
<div id="main">
<?php printTabs(); ?>
<div id="content">
	<?php printSubtabs(); ?>
	<div class="tabbox">
	<h1><?php echo (gettext('Create a new install with symbolic links to the current Zenphoto scripts.')); ?></h1>
	<?php zp_apply_filter('admin_note','clone', ''); ?>
	<?php
	if (isset($success)) {
		if ($success) {
			?>
			<div class="notebox">
			<?php
			echo implode("\n", $msg)."\n";
			?>
			</div>
			<?php
		} else {
			?>
			<div class="errorbox">
			<?php
			echo implode("\n", $msg)."\n";
			?>
			</div>
			<?php
		}
	} else {
		?>
		<p class="notebox">
			<?php echo gettext('<strong>Note:</strong> Existing Zenphoto scripts will be removed from the target if they exist.')?>
		</p>
		<?php
	}
	?>

	<br />
	<br />
	<?php

	$folderlist = array();
	if (isset($_POST['path'])) {
		$path = sanitize($_POST['path']);
	} else {
		if (WEBPATH) {
			$path = str_replace(WEBPATH,'/',SERVERPATH);
		} else {
			$path = SERVERPATH.'/';
		}
	}
	$downtitle = '.../'.basename($path);
	$uppath = str_replace('\\','/',dirname($path));

	$up = explode('/',$uppath);
	$uptitle = array_pop($up);
	if (!empty($up)) {
		$uptitle = array_pop($up).'/'.$uptitle;
	}
	if (!empty($up)) {
		$uptitle = '.../'.$uptitle;
	}

	if (substr($uppath, -1) != '/') {
		$uppath .= '/';
	}
	$zp_folders = array(ALBUMFOLDER,BACKUPFOLDER,CACHEFOLDER,STATIC_CACHE_FOLDER,USER_PLUGIN_FOLDER,THEMEFOLDER,UPLOAD_FOLDER,ZENFOLDER,DATA_FOLDER);

	if (($dir=opendir($path))!==false) {
		while(($file=readdir($dir))!==false) {
			if($file{0} != '.' && $file{0} != '$') {
				if ((is_dir($path.$file))) {
					if (!in_array($file, $zp_folders)) {	// no clones "here" or in "hidden" files
						$folderlist[$file]=$path.$file.'/';
					}
				}
			}
		}
		closedir($dir);
	}

	?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			var prime = '<?php echo SERVERPATH; ?>/';
			function buttonAction(data) {
				$('#newDir').val(data);
				$('#changeDir').submit();
			}
			function folderChange() {
				$('#downbutton').attr('title','<?php echo $downtitle; ?>/'+$('#cloneFolder').val().replace(/\/$/,'').replace( /.*\//, '' ));
				$('#cloneButton').attr('title',sprintf('Clone installation to %s',$('#downbutton').attr('title')));
				$('#clonePath').val($('#cloneFolder').val());
				if (prime == $('#clonePath').val()) {
					$('#cloneButton').attr('disabled','disabled');
				} else {
					$('#cloneButton').removeAttr('disabled');
				}

			}
			window.onload = function() {
				folderChange();
			}
			// ]]> -->
		</script>
		<form name="changeDir" id="changeDir" action="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/cloneZenphoto/cloneTab.php'; ?>" method="post">
			<input type="hidden" name="path" id="newDir" value = "" />
			<?php
			if (empty($folderlist)) {
				echo gettext('No subfolders in: ').' ';
			} else {
				echo gettext('Select the destination folder:').' ';
			}
			echo $path;
			if (!empty($folderlist)) {
				?>
				<select id="cloneFolder" name="cloneFolder" onchange="folderChange();">
				<?php	generateListFromArray(array(), $folderlist, false, true);	?>
				</select>
				<?php
			}
			?>
			<span class="icons">
				<a id="upbutton" href="javascript:buttonAction('<?php echo $uppath; ?>');" title="<?php echo $uptitle; ?>">
					<img class="icon-position-top4" src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/arrow_up.png" alt="" />
				</a>
			</span>
			<span class="icons"<?php if (empty($folderlist)) echo ' style="display:none;"'; ?>>
				<a id="downbutton" href="javascript:buttonAction($('#cloneFolder').val());" title="">
					<img class="icon-position-top4" src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/arrow_down.png" alt="" />
				</a>
			</span>
		</form>
		<br class="clearall" />
		<br />
		<br />
		<form name="cloneZenphoto" action="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/cloneZenphoto/clone.php'; ?>">
			<?php XSRFToken('cloneZenphoto');?>
			<input type="hidden" name="clone" value="true" />
			<input type="hidden" name="clonePath" id="clonePath" value="" />
			<?php XSRFToken('cloneZenphoto'); ?>
			<div class="buttons pad_button" id="cloneZP">
			<button id="cloneButton" class="tooltip" type="submit" title=""<?php if (empty($folderlist)) echo ' disabled="disabled"'; ?> >
				<img src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/folder.png" alt="" /> <?php echo gettext("Clone Zenphoto"); ?>
			</button>
			</div>
			<br class="clearall" />
		</form>
</div>
</div><!-- content -->
</div><!-- main -->
<?php printAdminFooter(); ?>
</body>
</html>
