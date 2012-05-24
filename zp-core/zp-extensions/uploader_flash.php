<?php
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('<em>flash</em> upload handler.');
$plugin_author = 'Stephen Billard (sbillard)';

zp_register_filter('upload_handlers', 'floashUploadHandler');

function floashUploadHandler($uploadHandlers) {
	$uploadHandlers['flash'] = SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/uploader_flash';
	return $uploadHandlers;
}
?>