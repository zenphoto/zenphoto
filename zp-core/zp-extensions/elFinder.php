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
 * @package zpcore\plugins\elfinder
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('Provides file handling for the <code>Upload > Files</code> admin page and the <em>TinyMCE</em> file browser.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_category = gettext('Uploader');

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
		$options = array(gettext('Files tab')			 => array('key'	 => 'elFinder_files', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext('Use as the upload <em>files</em> subtab.')),
						gettext('TinyMCE plugin')	 => array('key'	 => 'elFinder_tinymce', 'type' => OPTION_TYPE_CHECKBOX,
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
	$me = sprintf(gettext('files (%s)'), 'elFinder');
	$mylink =  FULLWEBPATH  . '/'.  ZENFOLDER . '/' . PLUGIN_FOLDER . '/elFinder/filemanager.php?page=upload&tab=elFinder&type=' . gettext('files');
	if (is_null($tabs['upload'])) {
		$tabs['upload'] = array(
				'text' => gettext("upload"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-upload.php',
				'subtabs' => NULL);
	}
	$tabs['upload']['subtabs'][$me] = $mylink;
	if (zp_getcookie('zpcms_admin_uploadtype') == 'elFinder')
		$tabs['upload']['link'] = $mylink;
	return $tabs;
}

function elFinder_tinymce($discard) {

	$file = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/elFinder/elfinder.php?XSRFToken=' . getXSRFToken('elFinder');
	?>
	<script>
		function elFinderBrowser(field_name, url, type, win) {
			if (tinymce.majorVersion == 4) {
				tinymce.activeEditor.windowManager.open({
					file: '<?php echo $file; ?>', // use an absolute path!
					title: 'elFinder 2.0',
					width: 900,
					height: 450,
					close_previous: 'no',
					inline: 'yes', // This parameter only has an effect if you use the inlinepopups plugin!
					popup_css: false, // Disable TinyMCE's default popup CSS
					resizable: 'yes'
				}, {
					setUrl: function(url) {
						win.document.getElementById(field_name).value = url;
					}
				});
			} else {
				tinymce.activeEditor.windowManager.openUrl({
					url: '<?php echo $file; ?>', // use an absolute path!
					title: 'elFinder 2.0',
					body: {
						type: 'bar',
					},
					width: 1000,
					height: 500,
					onMessage: function (api, data) {
						if (data.mceAction === 'insertMyURL') {
							field_name(data.url);
							api.close();
						}
					}
				});
			}
			return false;
		}
	</script>

	<?php
	return 'elFinderBrowser';
}
?>