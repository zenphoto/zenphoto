<?php

/*
 * Use when you want to clear out all cookies stored by site visitors. For instance if you have logged
 * in as an admin on a computer and forgot to log out. Then you can press the plugin button to clear
 * this (and other) cookies.
 *
 * The plugin checks for a "base" cookie. If this cookie is from a time less than the current "base" all
 * cookies found will be cleared and a new "base" cookie will be set.
 *
 * Note: you will have to log in again after this action. Your login cookie is cleared as well.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugin
 * @subpackage development
 * @category package
 * @category ZenPhoto20Tools
 */
$plugin_is_filter = 99 | CLASS_PLUGIN;
$plugin_description = gettext('Invalidates all cookies that were created earlier than the invalidate action.');
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('admin_utilities_buttons', 'cookieInvalidator::button');
$_zp_button_actions[] = 'cookieInvalidator::setBase';

class cookieInvalidator {

	static function button($buttons) {
		$base = getOption('cookieInvalidator_base');
		$buttons[] = array(
				'XSRFTag' => 'setInvalidateBase',
				'category' => gettext('Admin'),
				'enable' => true,
				'button_text' => gettext('Invalidate cookies'),
				'formname' => 'cookieInvalidator',
				'action' => FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=cookieInvalidator::setBase',
				'icon' => CROSS_MARK_RED,
				'title' => sprintf(gettext('Cookies prior to %s are invalid'), date('Y-m-d H:i:s', $base)),
				'alt' => '',
				'hidden' => '<input type="hidden" name="action" value="cookieInvalidator::setBase" />',
				'rights' => ADMIN_RIGHTS,
		);
		return $buttons;
	}

	static function invalidate($cookies) {
		global $_zp_loggedin, $_zp_current_admin_obj;
		if (zp_getCookie('cookieInvalidator') != ($newBase = getOption('cookieInvalidator_base'))) {
			foreach ($cookies as $cookie => $value) {
				zp_clearCookie($cookie);
			}
			zp_setCookie('cookieInvalidator', $newBase);
			$_zp_current_admin_obj = $_zp_loggedin = NULL;
		}
	}

	static function setBase() {
		setOption('cookieInvalidator_base', time());
		header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
		exitZP();
	}

}

if (isset($_COOKIE) && OFFSET_PATH != 2) {
	cookieInvalidator::invalidate($_COOKIE);
}
?>