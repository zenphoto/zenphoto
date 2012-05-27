<?php
/**
 *
 * This plugin provides an HTTP based image upload handler for the <i>upload/images</i> admin tab.
 *
 *
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('<em>http</em> image upload handler.');
$plugin_author = 'Stephen Billard (sbillard)';

zp_register_filter('upload_handlers', 'httpUploadHandler');

function httpUploadHandler($uploadHandlers) {
	$uploadHandlers['http'] = SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/uploader_http';
	return $uploadHandlers;
}
?>