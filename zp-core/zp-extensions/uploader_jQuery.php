<?php
/**
 *
 * This plugin provides an image upload handler for the <i>upload/images</i> admin tab
 * based on the {@link https://github.com/blueimp/jQuery-File-Upload <i>jQuery File Upload Plugin</i>}
 * by Sebastian Tschan.
 *
 * PHP 5.3 or greater is required by the encorporated software.
 *
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('<em>jQuery</em> image upload handler.');
$plugin_author = 'Stephen Billard (sbillard)';
$plugin_disable = (version_compare(PHP_VERSION, '5.3')>=0)?false:gettext('jQuery uploader requires PHP 5.3 or greater.');

if ($plugin_disable) {
	setOption('zp_plugin_uploader_jQuery',0);
} else {
	zp_register_filter('upload_handlers', 'jQueryUploadHandler');
}

function jQueryUploadHandler($uploadHandlers) {
	$uploadHandlers['jQuery'] = SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/uploader_jQuery';
	return $uploadHandlers;
}
?>