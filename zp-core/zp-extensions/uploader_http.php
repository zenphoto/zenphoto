<?php

/**
 *
 * This plugin provides an HTTP based image upload handler for the <i>upload/images</i> admin tab.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage admin
 *
 */
$plugin_is_filter = defaultExtension(30 | ADMIN_PLUGIN);
$plugin_description = gettext('<em>http</em> image upload handler.');
$plugin_author = 'Stephen Billard (sbillard)';

zp_register_filter('admin_tabs', 'httpUploadHandler_admin_tabs');
if (zp_loggedin(UPLOAD_RIGHTS)) {
	zp_register_filter('upload_handlers', 'httpUploadHandler');
}

function httpUploadHandler($uploadHandlers) {
	$uploadHandlers['http'] = SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/uploader_http';
	return $uploadHandlers;
}

function httpUploadHandler_admin_tabs($tabs) {
	if (zp_loggedin(UPLOAD_RIGHTS)) {
		$me = sprintf(gettext('images (%s)'), 'http');
		$mylink = 'admin-upload.php?page=upload&tab=http&type=' . gettext('images');
		if (is_null($tabs['upload'])) {
			$tabs['upload'] = array('text' => gettext("upload"),
					'link' => WEBPATH . "/" . ZENFOLDER . '/' . $mylink,
					'subtabs' => NULL,
					'default' => 'http'
			);
		}
		$tabs['upload']['subtabs'][$me] = $mylink;
	}
	return $tabs;
}

?>