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

printAdminHeader(gettext('utilities'),gettext('reference'));

?>
</head>
<body>
<?php printLogoAndLinks(); ?>
<div id="main">
<?php printTabs(); ?>
<div id="content">
	<?php zp_apply_filter('admin_note','clone', ''); ?>
	<?php
	if (isset($success)) {
		if ($success) {
			echo '<p class="notebox">'.implode('<br />', $msg).'</p>';
		} else {
			echo '<p class="errorbox">'.implode('<br />', $msg).'</p>';
		}
	}
	?>
	<h1><?php echo (gettext('Create a new install with symbolic links to the current Zenphoto scripts.')); ?></h1>
	</form>
	<br />
	<br />
	<?php

	$folderlist = array();
	$path = str_replace(WEBPATH,'/',SERVERPATH);

	if (($dir=opendir($path))!==false) {
		while(($file=readdir($dir))!==false) {
			if($file!='.' && $file!='..') {
				if ((is_dir($path.$file))) {
					if ($file != trim(WEBPATH,'/')) {
						$folderlist[$file]=$path.$file.'/';
					}
				}
			}
		}
		closedir($dir);
	}

	?>
		<form name="cloneZenphoto" action="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/cloneZenphoto/clone.php'; ?>">
		<?php XSRFToken('cloneZenphoto');?>
		<?php echo gettext('Select the destination folder:'); ?>
		<select id="cloneFolder" name="cloneFolder">
		<?php	generateListFromArray(array(), $folderlist, false, true);	?>
		</select>
		<br clear="all" />
		<br clear="all" />
		<input type="hidden" name="clone" value="true" />
		<?php XSRFToken('cloneZenphoto'); ?>
		<div class="buttons pad_button" id="cloneZP">
		<button class="tooltip" type="submit" title="<?php echo gettext("Clone the installation."); ?>">
			<img src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/folder.png" alt="" /> <?php echo gettext("Clone Zenphoto"); ?>
		</button>
		</div>
		<br clear="all" />
		</form>

</div><!-- content -->
</div><!-- main -->
<?php printAdminFooter(); ?>
</body>
</html>