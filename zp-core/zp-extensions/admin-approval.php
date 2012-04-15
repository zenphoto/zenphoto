<?php
/**
 * Overrides of the <i>publish</i> save handling use such that only
 * a User with <var>ADMIN_RIGHTS</var> or <var>MANAGE_ALL_<i>object</i></var> rights may
 * mark an object published.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 */

$plugin_is_filter = 9|ADMIN_PLUGIN;
$plugin_description = gettext('Allows only users with Admin or Manage All rights to change the publish state of objects.');
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('save_album_utilities_data', 'admin_approval::publishZenphoto');
zp_register_filter('save_image_utilities_data', 'admin_approval::publishZenphoto');
zp_register_filter('new_page','admin_approval::Zenpage');
zp_register_filter('update_page','admin_approval::Zenpage');
zp_register_filter('new_article','admin_approval::Zenpage');
zp_register_filter('update_article','admin_approval::Zenpage');
zp_register_filter('new_article','admin_approval::Zenpage');
zp_register_filter('update_article','admin_approval::Zenpage');

class admin_approval {

	static function publish_object($object) {
		$msg = '';
		if (!zp_loggedin($object->manage_rights)) {	// not allowed to change the published status
			$data = $object->getData();
			if (isset($data['show'])) {
				$show = $data['show'];
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
	static function publishZenphoto($object, $i) {
		global $_admin_approval_error;
		$msg = admin_approval::publish_object($object);
		if ($msg) {
			$_admin_approval_error = $msg;
			zp_register_filter('edit_error', 'admin_approval::post_error');
		}
		return $object;
	}
	static function Zenpage($report, $object) {
		$msg = admin_approval::publish_object($object);
		if ($msg) {
			$msg = '<p class="errorbox fade-message">'.$msg.'</p>';
		}
		return $report.$msg;
	}
	static function post_error() {
		global $_admin_approval_error;
		return $_admin_approval_error;
	}

}
?>