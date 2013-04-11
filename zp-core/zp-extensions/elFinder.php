<?php
/**
 *
 *
 * The Admin <var>upload/files</var> tab and the <i>TinyMCE</i> file browser (if configured) use
 * a plugin to supply file handling and uploading.
 * This plugin supplies file handling using <i>elFinder</i> by {@link http://elfinder.org/ Studio-42 }.
 *
 * <hr>
 * <img src="%WEBPATH%/%ZENFOLDER%/%PLUGIN_FOLDER%/elFInder/elfinder-logo.png" />
 * "<i>elFinder</i> is a file manager for web similar to that you use on your computer. Written in JavaScript
 * using jQuery UI, it just work's in any modern browser. Its creation is inspired by simplicity and
 * convenience of Finder.app program used in Mac OS X."
 *
 * elFinder uses UNIX command line utils <var>zip</var>, <var>unzip</var>, <var>rar</var>, <var>unrar</var>, <var>tar</var>,
 * <var>gzip</var>, <var>bzip2</var>, and <var>7za</var> for archives support,
 * on windows you need to have full {@link http://www.cygwin.com/ cygwin} support in your webserver environment.
 *
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage uploader
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('Provides file handling for the <code>upload/files</code> tab and the <em>TinyMCE</em> file browser.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'elFinder_options';

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
	zp_register_filter('admin_tabs', 'elFinder_admin_tabs', 50);
}
if (getOption('elFinder_tinymce')) {
	zp_register_filter('tinymce_zenpage_config', 'elFinder_tinymce');
}

function elFinder_admin_tabs($tabs) {
	$me = sprintf(gettext('files (%s)'),'elFinder');
	$mylink = PLUGIN_FOLDER.'/'.'elFinder/filemanager.php?page=upload&amp;tab='.$me;
	if (is_null($tabs['upload'])) {
		$tabs['upload'] =  array('text'=>gettext("upload"),
				'link'=>WEBPATH."/".ZENFOLDER.'/'.$mylink,
				'subtabs'=>NULL);
	} else {
		$default = str_replace(WEBPATH.'/'.ZENFOLDER.'/','',$tabs['upload']['link']);
		preg_match('|&amp;tab=([^&]*)|', $default, $matches);
		$tabs['upload']['subtabs'][$matches[1]] = $default;
		$tabs['upload']['subtabs'][$me] = $mylink;
		$tabs['upload']['default']= $me;
		$tabs['upload']['link'] = WEBPATH."/".ZENFOLDER.'/'.$mylink;
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