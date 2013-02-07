<?php
/**
 *
 * Ajax Filemanager utility for Zenphoto.
 *
 * This plugin is used by the <i>image insert</i> feature of <var>tinyMCE</var> and as the handler for
 * the <i>Files</i> subtab on the <i>Upload</i> tab.
 *
 * We believe that the filemanager is now completely secure. However we understand that
 * the damage caused by the previous vulnerabilities may lead some sites to decide that
 * the benifits of the filemanager are not worth the risks. Thus this plugin gives you the
 * choice of enabling the feature or not. If disabled <b>no</b> access is allowed to the Ajax FIlemanager scripts.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage uploader
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('Provides file handling in tinyMCE and the "files" upload tab.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'ajaxfilemanager';

/**
 * Option handler class
 *
 */
class ajaxfilemanager {
	/**
	 * class instantiation function
	 *
	 */
	function __construct() {
		setOptionDefault('ajaxfilemanager_files', 0);
		setOptionDefault('ajaxfilemanager_tinymce', 1);
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		$options = array(	gettext('Files tab') => array('key' => 'ajaxfilemanager_files', 'type' => OPTION_TYPE_CHECKBOX,
																											'desc' => gettext('Use as the upload <em>files</em> subtab.')),
											gettext('TinyMCE plugin') => array('key' => 'ajaxfilemanager_tinymce', 'type' => OPTION_TYPE_CHECKBOX,
																											'desc' => gettext('Enable plugin for TinyMCE.'))
		);
		return $options;
	}

	function handleOption($option, $currentValue) {
	}

}

if (getOption('ajaxfilemanager_files') && zp_loggedin(FILES_RIGHTS)) {
	zp_register_filter('admin_tabs', 'ajaxfilemanager_admin_tabs');
}
if (getOption('ajaxfilemanager_tinymce')) {
	zp_register_filter('tinymce_zenpage_config', 'ajaxfilemanager_tinymce');
}

function ajaxfilemanager_admin_tabs($tabs) {
	if (!array_key_exists('upload',$tabs)) {
		$tabs['upload'] = array('text'=>gettext("upload"),	'subtabs'=>NULL, 'link'=>WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/ajaxfilemanager/zp_filemanager.php');
	} else {
		$tabs['upload']['subtabs'][gettext('images')] = $tabs['upload']['link'];
		$tabs['upload']['default']= 'images';
		$tabs['upload']['subtabs'][gettext('files')] = PLUGIN_FOLDER.'/ajaxfilemanager/zp_filemanager.php?page=upload&amp;tab=files';
	}
	return $tabs;
}

function ajaxfilemanager_tinymce($discard) {
	global $locale;
	?>
	<script type="text/javascript">
	// <!-- <![CDATA[
		function ajaxfilemanager(field_name, url, type, win) {
			<?php	echo 'var ajaxfilemanagerurl="'.FULLWEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/ajaxfilemanager/ajaxfilemanager.php?editor=tinymce&XSRFToken='.getXSRFToken('ajaxfilemanager').'";'; ?>
			switch (type) {
				case "image":
					ajaxfilemanagerurl += "&amp;type=img&amp;language=<?php echo $locale; ?>";
					break;
				case "media":
					ajaxfilemanagerurl += "&amp;type=media&amp;language=<?php echo $locale; ?>";
					break;
				case "flash": //for older versions of tinymce
					ajaxfilemanagerurl += "&amp;type=media&amp;language=<?php echo $locale; ?>";
					break;
				case "file":
					ajaxfilemanagerurl += "&amp;type=files&amp;language=<?php echo $locale; ?>";
					break;
				default:
					return false;
			}
			tinyMCE.activeEditor.windowManager.open({
			  file : ajaxfilemanagerurl,
			  input : field_name,
			  width : 750,
			  height : 500,
			  resizable : "yes",
			  inline : "yes",
			  close_previous: "yes"
			},{
			  window: win,
			  input: field_name
		 	});
		}
	// ]]> -->
	</script>
	<?php
	return 'ajaxfilemanager';
}

$htaccess = SERVERPATH.'/'.DATA_FOLDER.'/ajaxfilemanager/.htaccess';
if (!file_exists($htaccess)) {
	@mkdir(SERVERPATH.'/'.DATA_FOLDER.'/ajaxfilemanager/');
	file_put_contents($htaccess, "deny from all\n");
}
@chmod($htaccess,0444);
unset($htaccess);


if (OFFSET_PATH!=99 && session_id() != '') {
	unset($_SESSION['XSRFToken']);
}


?>