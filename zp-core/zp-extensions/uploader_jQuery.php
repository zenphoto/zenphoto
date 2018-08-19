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
 *
 * @package plugins/uploader_jQuery
 * @pluginCategory admin
 */
$plugin_is_filter = defaultExtension(40 | ADMIN_PLUGIN);
$plugin_description = gettext('<em>jQuery</em> image upload handler.');
$plugin_disable = (version_compare(PHP_VERSION, '5.3') >= 0) ? false : gettext('jQuery uploader requires PHP 5.3 or greater.');

if ($plugin_disable) {
	enableExtension('uploader_jQuery', 0);
} else {
	if (zp_loggedin(UPLOAD_RIGHTS)) {
		zp_register_filter('upload_handlers', 'jQueryUploadHandler');
	}
	zp_register_filter('admin_tabs', 'jQueryUploadHandler_admin_tabs');
}

function jQueryUploadHandler($uploadHandlers) {
	$uploadHandlers['jQuery'] = SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/uploader_jQuery';
	return $uploadHandlers;
}

function jQueryUploadHandler_admin_tabs($tabs) {
	if (zp_loggedin(UPLOAD_RIGHTS)) {
		$me = sprintf(gettext('images (%s)'), 'jQuery');
		$mylink = 'admin-upload.php?page=upload&tab=jQuery&type=' . gettext('images');
		if (is_null($tabs['upload'])) {
			$tabs['upload'] = array('text' => gettext("upload"),
					'link' => WEBPATH . "/" . ZENFOLDER . '/' . $mylink,
					'subtabs' => NULL,
					'default' => 'jQuery'
			);
		}
		$tabs['upload']['subtabs'][$me] = $mylink;
	}
	return $tabs;
}

?>