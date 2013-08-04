<?php

/**
 *
 * This plugin provides an HTTP based image upload handler for the <i>upload/images</i> admin tab.
 *
 * @package plugins
 * @subpackage uploader
 *
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('<em>http</em> image upload handler.');
$plugin_author = 'Stephen Billard (sbillard)';

setOptionDefault('zp_plugin_uploader_http', $plugin_is_filter);

if (zp_loggedin(UPLOAD_RIGHTS)) {
	zp_register_filter('upload_handlers', 'httpUploadHandler');
	zp_register_filter('admin_tabs', 'httpUploadHandler_admin_tabs', 10);
}

function httpUploadHandler($uploadHandlers) {
	$uploadHandlers['http'] = SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/uploader_http';
	return $uploadHandlers;
}

function httpUploadHandler_admin_tabs($tabs) {
	$me = sprintf(gettext('images (%s)'), 'http');
	$mylink = 'admin-upload.php?page=upload&amp;tab=' . $me . '&amp;uploadtype=http';
	if (is_null($tabs['upload'])) {
		$tabs['upload'] = array('text'		 => gettext("upload"),
						'link'		 => WEBPATH . "/" . ZENFOLDER . '/' . $mylink,
						'subtabs'	 => NULL);
	} else {
		$default = str_replace(WEBPATH . '/' . ZENFOLDER . '/', '', $tabs['upload']['link']);
		preg_match('|&amp;tab=([^&]*)|', $default, $matches);
		$tabs['upload']['subtabs'][$matches[1]] = $default;
		$tabs['upload']['subtabs'][$me] = $mylink;
		$tabs['upload']['default'] = $me;
		$tabs['upload']['link'] = WEBPATH . "/" . ZENFOLDER . '/' . $mylink;
	}
	return $tabs;
}

?>