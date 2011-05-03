<?php
/**
 * Provides override of the "show" seting such that only
 * someone with ADMIN_RIGHTS or MANAGE_ALL_ALBUM rights may
 * mark an object published.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */

$plugin_is_filter = 9|ADMIN_PLUGIN;
$plugin_description = gettext('Allows only users with Admin or Manage All rights to change the publish state of objects.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';

zp_register_filter('save_album_utilities_data', 'admin_approval_publishZenphoto');
zp_register_filter('save_image_utilities_data', 'admin_approval_publishZenphoto');
zp_register_filter('new_page','saveLayoutSelection');
zp_register_filter('update_page','admin_approvalZenpage');
zp_register_filter('new_article','admin_approvalZenpage');
zp_register_filter('update_article','admin_approvalZenpage');
zp_register_filter('new_article','admin_approvalZenpage');
zp_register_filter('update_article','admin_approvalZenpage');

function admin_approval_publish_object($object) {
	$msg = '';
	if (!zp_loggedin($object->manage_rights)) {	// not allowed to change the published status
		if (isset($object->data['show'])) {
			$show = $object->data['show'];
		} else {
			$show = 0;
		}
		$newshow = $object->getShow();
		$object->setShow($show);
		if ($newshow != $show) {
			$msg = gettext('You do not have rights to change the <em>publish</em> state.');
		}
	}
	return $msg;
}
function admin_approval_publishZenphoto($object, $i) {
	global $_admin_approval_error;
	$msg = admin_approval_publish_object($object);
	if ($msg) {
		$_admin_approval_error = $msg;
		zp_register_filter('edit_error', 'admin_approval_post_error');
	}
	return $object;
}
function admin_approvalZenpage($report, $object) {
	$msg = admin_approval_publish_object($object);
	if ($msg) {
		$msg = '<p class="errorbox fade-message">'.$msg.'</p>';
	}
	return $report.$msg;
}
function admin_approval_post_error() {
	global $_admin_approval_error;
	return $_admin_approval_error;
}
?>