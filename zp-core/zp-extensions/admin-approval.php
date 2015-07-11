<?php

/**
 * Overrides of the <i>publish</i> save handling such that only
 * a User with <var>ADMIN_RIGHTS</var> or <var>MANAGE_ALL_<i>object</i></var> rights may
 * mark an object published.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 980 | ADMIN_PLUGIN;
$plugin_description = gettext('Allows only users with Admin or Manage All rights to change the publish state of objects.');
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('save_object', 'admin_approval::publish_object');
zp_register_filter('edit_error', 'admin_approval::post_error');

class admin_approval {

	static function publish_object($save, $object) {
		global $_admin_approval_error;
		if (is_subclass_of($object, 'ThemeObject') && !zp_loggedin($object->manage_rights)) { // not allowed to change the published status
			//	retrieve the original value of publish details
			$data = $object->getData();
			$show = (int) @$data['show'];
			$pub = @$data['publishdate'];
			$exp = @$data['expiredate'];
			if ($object->getShow() != $show || $object->getPublishDate() != $pub || $object->getExpireDate() != $exp) { //	publish details have been changed, restore the original publish details
				$object->set('show', $show);
				$object->set('publishdate', $pub);
				$object->set('expiredate', $exp);
				$_admin_approval_error = gettext('You do not have rights to change the <em>publish</em> state.');
				if (is_subclass_of($object, 'CMSItems')) {
					$_admin_approval_error = '<p class="errorbox fade-message">' . $_admin_approval_error . '</p>';
				}
			}
		}

		return $save;
	}

	static function post_error() {
		global $_admin_approval_error;
		return $_admin_approval_error;
	}

}

?>