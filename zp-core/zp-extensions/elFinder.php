<?php
/**
 *
 * This plugin enables the elFinder filemanager plugin.
 *
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage uploader
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('Provides file handling in the "files" upload tab.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'elFinder_options';

zp_register_filter('admin_tabs', 'elFinder_admin_tabs');

/**
 * Option handler class
 *
 */
class elFinder_options {
	/**
	 * class instantiation function
	 *
	 */
	function __construct() {
		setOptionDefault('elFinder_files', 1);
		setOptionDefault('elFinder_tinymce', 0);
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		$options = array(	gettext('Files tab') => array('key' => 'elFinder_files', 'type' => OPTION_TYPE_CHECKBOX,
																											'desc' => gettext('Use as the upload <em>files</em> subtab.')),
											gettext('TinyMCE plugin') => array('key' => 'elFinder_tinymce', 'type' => OPTION_TYPE_CHECKBOX,
																											'desc' => gettext('Enable plugin for TinyMCE.'))
		);
		return $options;
	}

	function handleOption($option, $currentValue) {
	}

}

if (getOption('elFinder_files') && zp_loggedin(FILES_RIGHTS)) {
	zp_register_filter('admin_tabs', 'elFinder_admin_tabs');
}
if (getOption('elFinder_tinymce')) {
	zp_register_filter('tinymce_zenpage_config', 'elFinder_tinymce');
}

function elFinder_admin_tabs($tabs) {
	if (!array_key_exists('upload',$tabs)) {
		$tabs['upload'] = array('text'=>gettext("upload"),	'subtabs'=>NULL, 'link'=>WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/elFinder/filemanager.php');
	} else {
		$tabs['upload']['subtabs'][gettext('images')] = $tabs['upload']['link'];
		$tabs['upload']['default']= 'images';
		$tabs['upload']['subtabs'][gettext('files')] = PLUGIN_FOLDER.'/elFinder/filemanager.php?page=upload&amp;tab=files';
	}
	return $tabs;
}

function elFinder_tinymce($discard) {
	?>
	<script type="text/javascript">
	// <!-- <![CDATA[
	function elFinderBrowser (field_name, url, type, win) {
	  var elfinder_url = '<?php echo FULLWEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER; ?>/elfinder/elfinder.php?XSRFToken=<?php echo getXSRFToken('elFinder'); ?>';    // use an absolute path!
	  tinyMCE.activeEditor.windowManager.open({
	    file: elfinder_url,
	    title: 'elFinder 2.0',
	    width: 900,
	    height: 450,
	    resizable: 'yes',
	    inline: 'yes',    // This parameter only has an effect if you use the inlinepopups plugin!
	    popup_css: false, // Disable TinyMCE's default popup CSS
	    close_previous: 'no'
	  }, {
	    window: win,
	    input: field_name
	  });
	  return false;
	}
	// ]]> -->
	</script>
	<?php
	return 'elFinderBrowser';
}

?>