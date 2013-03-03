<?php
/**
 *
 * This plugin provides an image upload handler for the <i>upload/images</i> admin tab
 * based on the {@link http://www.uploadify.com/ <i>uploadify</i> jQuery plugin script.}
 *
 * @package plugins
 * @subpackage uploader
 *
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('<em>flash</em> image upload handler.');
$plugin_author = 'Stephen Billard (sbillard)';

if (zp_loggedin(UPLOAD_RIGHTS)) {
	zp_register_filter('upload_handlers', 'flashUploadHandler');
	zp_register_filter('admin_tabs', 'flashUploadHandler_admin_tabs');
}

function flashUploadHandler($uploadHandlers) {
	$uploadHandlers['flash'] = SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/uploader_flash';
	return $uploadHandlers;
}

function flashUploadHandler_admin_tabs($tabs) {
	if (is_null($tabs['upload'])) {
		$tabs['upload'] =  array('text'=>gettext("upload"),
				'link'=>WEBPATH."/".ZENFOLDER.'/admin-upload.php?page=upload&amp;tab=images',
				'subtabs'=>NULL);
	} else {
		if (!isset($tabs['upload']['subtabs'][gettext('files')])) {
			$tabs['upload']['subtabs'][gettext('files')] = str_replace(WEBPATH."/".ZENFOLDER.'/','',$tabs['upload']['link']);
		}
		$tabs['upload']['link'] = $tabs['upload']['subtabs'][gettext('images')] = 'admin-upload.php?page=upload&amp;tab=images';
		$tabs['upload']['default']= 'images';
	}
	return $tabs;
}

?>