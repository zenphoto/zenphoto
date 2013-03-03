<?php
/**
 *
 * This plugin provides an HTTP based image upload handler for the <i>upload/images</i> admin tab.
 *
 * @package plugins
 * @subpackage uploader
 *
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('<em>http</em> image upload handler.');
$plugin_author = 'Stephen Billard (sbillard)';

if (zp_loggedin(UPLOAD_RIGHTS)) {
	zp_register_filter('upload_handlers', 'httpUploadHandler');
	zp_register_filter('admin_tabs', 'httpUploadHandler_admin_tabs');
}

function httpUploadHandler($uploadHandlers) {
	$uploadHandlers['http'] = SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/uploader_http';
	return $uploadHandlers;
}

function httpUploadHandler_admin_tabs($tabs) {
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