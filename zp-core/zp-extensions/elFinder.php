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
 *
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = defaultExtension(50 | ADMIN_PLUGIN);
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
		if (OFFSET_PATH == 2) {
			setOptionDefault('elFinder_files', 1);
			setOptionDefault('elFinder_themeeditor', 1);
			setOptionDefault('elFinder_tinymce', 0);
		}
	}

	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		$options = array(gettext('Files tab') => array('key' => 'elFinder_files', 'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Use as the upload <em>files</em> subtab.')),
				gettext('Edit themes') => array('key' => 'elFinder_themeeditor', 'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Enable elFinder for editing themes.')),
				gettext('TinyMCE plugin') => array('key' => 'elFinder_tinymce', 'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Enable plugin for TinyMCE.'))
		);
		return $options;
	}

	function handleOption($option, $currentValue) {

	}

}

if (getOption('elFinder_files') && zp_loggedin(FILES_RIGHTS | UPLOAD_RIGHTS)) {
	zp_register_filter('admin_tabs', 'elFinder_admin_tabs');
	if (getOption('elFinder_themeeditor')) {
		zp_register_filter('theme_editor', 'elFinderThemeEdit');
	}
}
if (getOption('elFinder_tinymce')) {
	zp_register_filter('tinymce_config', 'elFinder_tinymce');
}

function elFinder_admin_tabs($tabs) {
	if (zp_loggedin(UPLOAD_RIGHTS)) {
		$me = sprintf(gettext('files (%s)'), 'elFinder');
		$mylink = PLUGIN_FOLDER . '/' . 'elFinder/filemanager.php?page=upload&tab=elFinder&type=' . gettext('files');
		if (is_null($tabs['upload'])) {
			$tabs['upload'] = array('text' => gettext("upload"),
					'link' => WEBPATH . "/" . ZENFOLDER . '/' . $mylink,
					'subtabs' => NULL,
					'default' => 'elFinder'
			);
		}
		$tabs['upload']['subtabs'][$me] = $mylink;
	}
	return $tabs;
}

function elFinder_tinymce($discard) {

	$file = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/elFinder/elfinder.php?XSRFToken=' . getXSRFToken('elFinder') . '&type=';
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		function elFinderBrowser(field_name, url, type, win) {
			tinymce.activeEditor.windowManager.open({
				file: '<?php echo $file; ?>' + type, // use an absolute path!
				title: 'elFinder 2.0',
				width: 900,
				height: 450,
				close_previous: 'no',
				inline: 'yes', // This parameter only has an effect if you use the inlinepopups plugin!
				popup_css: false, // Disable TinyMCE's default popup CSS
				resizable: 'yes'
			}, {
				setUrl: function (url) {
					win.document.getElementById(field_name).value = url;
				}
			});
			return false;
		}
		// ]]> -->
	</script>

	<?php
	return 'elFinderBrowser';
}

function elFinderThemeEdit($html, $theme) {
	$html = "launchScript('" . PLUGIN_FOLDER . "/elFinder/filemanager.php', [
													'page=upload',
													'tab=elFinder',
													'type=files',
													'themeEdit=" . urlencode($theme) . "'
												]);";
	return $html;
}
?>