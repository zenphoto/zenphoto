<?php
/**
 *
 * This plugin provides an image upload handler for the <i>upload/images</i> admin tab
 * based on the {@link http://www.uploadify.com/ <i>uploadify</i> jQuery plugin script.}
 *
 *
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('<em>flash</em> image upload handler.');
$plugin_author = 'Stephen Billard (sbillard)';

zp_register_filter('upload_handlers', 'floashUploadHandler');

function floashUploadHandler($uploadHandlers) {
	$uploadHandlers['flash'] = SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/uploader_flash';
	return $uploadHandlers;
}
?>