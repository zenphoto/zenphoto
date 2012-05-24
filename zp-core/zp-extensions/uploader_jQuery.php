<?php
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('<em>jQuery</em> upload handler.');
$plugin_author = 'Stephen Billard (sbillard)';
$plugin_disable = (version_compare(PHP_VERSION, '5.3')>=0)?false:gettext('jQuery uploader requires PHP 5.3 or greater.');

zp_register_filter('upload_handlers', 'jQueryUploadHandler');

function jQueryUploadHandler($uploadHandlers) {
	$uploadHandlers['jQuery'] = SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/uploader_jQuery';
	return $uploadHandlers;
}
?>