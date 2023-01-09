<?php

/**
 *
 * This plugin provides an image upload handler for the <i>upload/images</i> admin tab
 * based on the {@link https://github.com/blueimp/jQuery-File-Upload <i>jQuery File Upload Plugin</i>}
 * by Sebastian Tschan.
 *
 * PHP 5.3 or greater is required by the encorporated software.
 *
 * @author Stephen Billard (sbillard)
 * @package zpcore\plugins\uploaderjquery
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('<em>jQuery</em> image upload handler.');
$plugin_author = 'Stephen Billard (sbillard)';
$plugin_disable = (version_compare(PHP_VERSION, '5.3') >= 0) ? false : gettext('jQuery uploader requires PHP 5.3 or greater.');
$plugin_category = gettext('Upload');
$option_interface = 'uploaderjQueryOptions';

if ($plugin_disable) {
	enableExtension('uploader_jQuery', 0);
} else {
	if (OFFSET_PATH == 2)
		setoptiondefault('zp_plugin_uploader_jQuery', $plugin_is_filter);
	if (zp_loggedin(UPLOAD_RIGHTS)) {
		zp_register_filter('upload_handlers', 'jQueryUploadHandler');
		zp_register_filter('admin_tabs', 'jQueryUploadHandler_admin_tabs', 5);
	}
}

class uploaderjQueryOptions {

	/**
	 * class instantiation function
	 */
	function __construct() {
		setOptionDefault('uploaderjquery_behaviour', 'rename');
	}

	function getOptionsSupported() {
		return array(
				gettext('Duplicates behaviour') => array(
						'key' => 'uploaderjquery_behaviour',
						'type' => OPTION_TYPE_RADIO,
						'order' => 3,
						'buttons' => array(
								gettext('Rename (append time string)') => 'rename',
								gettext('Disallow') => 'disallow',
								gettext('Overwrite') => 'overwrite'
						),
						'null_selection' => 'rename',
						'desc' => gettext('Define how the uploader should treat filename duplicates on upload.'))
		);
	}

}

function jQueryUploadHandler($uploadHandlers) {
	$uploadHandlers['jQuery'] = SERVERPATH .'/'. ZENFOLDER . '/' . PLUGIN_FOLDER . '/uploader_jQuery';
	return $uploadHandlers;
}

function jQueryUploadHandler_admin_tabs($tabs) {
	$me = sprintf(gettext('images (%s)'), 'jQuery');
	$mylink = FULLWEBPATH . '/' . ZENFOLDER . '/admin-upload.php?page=upload&tab=jQuery&type=' . gettext('images');
	if (is_null($tabs['upload'])) {
		$tabs['upload'] = array(
				'text' => gettext("upload"),
				'link' => FULLWEBPATH . '/' .ZENFOLDER . '/admin-upload.php',
				'subtabs' => NULL);
	}
	$tabs['upload']['subtabs'][$me] = $mylink;
	if (zp_getcookie('zpcms_admin_uploadtype') == 'jQuery') {
		$tabs['upload']['link'] = $mylink;
	}
	return $tabs;
}