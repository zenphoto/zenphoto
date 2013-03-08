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
	zp_register_filter('admin_tabs', 'flashUploadHandler_admin_tabs', 15);
}

function flashUploadHandler($uploadHandlers) {
	$me = sprintf(gettext('images (%s)'),'flash');
	$uploadHandlers[$me] = SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/uploader_flash';
	return $uploadHandlers;
}

function flashUploadHandler_admin_tabs($tabs) {
	$me = sprintf(gettext('images (%s)'),'flash');
	$mylink = 'admin-upload.php?page=upload&amp;tab='.$me.'&amp;uploadtype=flash';
	if (is_null($tabs['upload'])) {
		$tabs['upload'] =  array('text'=>gettext("upload"),
				'link'=>WEBPATH."/".ZENFOLDER.'/'.$mylink,
				'subtabs'=>NULL);
	} else {
		$default = str_replace(WEBPATH.'/'.ZENFOLDER.'/','',$tabs['upload']['link']);
		preg_match('|&amp;tab=([^&]*)|', $default, $matches);
		$tabs['upload']['subtabs'][$matches[1]] = $default;
		$tabs['upload']['subtabs'][$me] = $tabs['upload']['link'] = $mylink;
		$tabs['upload']['default']= $me;
	}
	return $tabs;
}

?>