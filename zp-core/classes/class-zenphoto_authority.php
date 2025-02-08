<?php

/**
 * 
 * @package zpcore\classes\deprecated
 * @deprecated 2.0 - Use the class Authority instead
 */
class Zenphoto_Authority extends Authority {

	/**
	 * @deprecated 2.0 - Use the class Authority instead
	 */
	function __construct() {
		parent::__construct();
		deprecationNotice(gettext('Use the Authority class instead'));
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function getVersion() {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::getVersion();
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function passwordHash($user, $pass, $hash_type = NULL) {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::passwordHash($user, $pass, $hash_type);
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function getAnAdmin($criteria) {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::getAnAdmin($criteria);
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function updateAdminField($update, $value, $constraints) {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::updateAdminField($update, $value, $constraints);
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function newAdministrator($name, $valid = 1) {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::newAdministrator($name, $valid);
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function getRights($version = NULL) {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::getRights($version);
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function getResetTicket($user, $pass) {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::getResetTicket($user, $pass);
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function logUser($user) {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::logUser($user);
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function getAuthCookies() {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::getAuthCookies();
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function handleLogout() {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::handleLogout();
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function printPasswordFormJS() {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::printPasswordFormJS();
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function printPasswordForm($id = '', $pad = false, $disable = NULL, $required = false, $flag = '') {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::printPasswordForm($id, $pad, $disable, $required, $flag);
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function pbkdf2($p, $s, $c = 1000, $kl = 32, $a = 'sha256') {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::pbkdf2($p, $s, $c, $kl, $a);
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function getLogoutURLPageParams($returnvalue = 'string') {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::getLogoutURLPageParams($returnvalue);
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function getLogoutURL($mode = 'backend', $redirect = '') {
		deprecationNotice(gettext('Use the Authority class method instead'));
		parent::getLogoutURL($mode, $redirect);
	}
}
