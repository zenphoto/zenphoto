<?php

/**
 * Places security information in a security log
 * The logged data includes:
 * <ul>
 * 	<li>the ip address of the client browser</li>
 * 	<li>the type of entry</li>
 * 	<li>the user/user name</li>
 * 	<li>the success/failure</li>
 * 	<li>the <i>authority</i> granting/denying the request</li>
 * 	<li>Additional information, for instance on failure, the password used</li>
 * </ul>
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 10 | CLASS_PLUGIN;
$plugin_description = gettext('Logs selected security events.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'security_logger';

setOptionDefault('zp_plugin_security-logger', $plugin_is_filter);

if (getOption('logger_log_admin')) {
	zp_register_filter('admin_login_attempt', 'security_logger::adminLoginLogger');
	zp_register_filter('federated_login_attempt', 'security_logger::federatedLoginLogger');
}
if (getOption('logger_log_guests')) {
	zp_register_filter('guest_login_attempt', 'security_logger::guestLoginLogger');
}
zp_register_filter('admin_allow_access', 'security_logger::adminGate');
zp_register_filter('authorization_cookie', 'security_logger::adminCookie', 0);
zp_register_filter('admin_managed_albums_access', 'security_logger::adminAlbumGate');
zp_register_filter('save_user', 'security_logger::UserSave');
zp_register_filter('admin_XSRF_access', 'security_logger::admin_XSRF_access');
zp_register_filter('admin_log_actions', 'security_logger::log_action');
zp_register_filter('log_setup', 'security_logger::log_setup');
zp_register_filter('security_misc', 'security_logger::security_misc');

/**
 * Option handler class
 *
 */
class security_logger {

	/**
	 * class instantiation function
	 *
	 * @return security_logger
	 */
	function __construct() {
		setOptionDefault('logger_log_guests', 1);
		setOptionDefault('logger_log_admin', 1);
		setOptionDefault('logger_log_type', 'all');
		setOptionDefault('logge_access_log_type', 'all_user');
		setOptionDefault('security_log_size', 5000000);
	}

	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(gettext('Record logon attempts of')		 => array('key'				 => 'logger_log_allowed', 'type'			 => OPTION_TYPE_CHECKBOX_ARRAY,
										'checkboxes' => array(gettext('Administrators')	 => 'logger_log_admin', gettext('Guests')					 => 'logger_log_guests'),
										'desc'			 => gettext('If checked login attempts will be logged.')),
						gettext('Record failed admin access')	 => array('key'			 => 'logge_access_log_type', 'type'		 => OPTION_TYPE_RADIO,
										'buttons'	 => array(gettext('All attempts')				 => 'all', gettext('Only user attempts')	 => 'all_user'),
										'desc'		 => gettext('Record admin page access failures.')),
						gettext('Record logon')								 => array('key'			 => 'logger_log_type', 'type'		 => OPTION_TYPE_RADIO,
										'buttons'	 => array(gettext('All attempts')					 => 'all', gettext('Successful attempts')	 => 'success', gettext('unsuccessful attempts') => 'fail'),
										'desc'		 => gettext('Record login failures, successes, or all attempts.'))
		);
	}

	function handleOption($option, $currentValue) {

	}

	/**
	 * Does the log handling
	 *
	 * @param int $success
	 * @param string $user
	 * @param string $name
	 * @param string $ip
	 * @param string $type
	 * @param string $authority kind of login
	 * @param string $addl more info
	 */
	private static function Logger($success, $user, $name, $action, $authority, $addl = NULL) {
		global $_zp_authority, $_zp_mutex;
		$pattern = '~^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$~';
		$forwardedIP = NULL;
		$ip = sanitize($_SERVER['REMOTE_ADDR']);
		if (!preg_match($pattern, $ip)) {
			$ip = NULL;
		}
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$forwardedIP = sanitize($_SERVER['HTTP_X_FORWARDED_FOR']);
			if (preg_match($pattern, $forwardedIP)) {
				$ip .= ' {' . $forwardedIP . '}';
			}
		}
		$admin = Zenphoto_Authority::getAnAdmin(array('`user`='	 => $_zp_authority->master_user, '`valid`=' => 1));
		if ($admin) {
			$locale = $admin->getLanguage();
		}
		if (empty($locale)) {
			$locale = 'en_US';
		}
		$cur_locale = getUserLocale();
		setupCurrentLocale($locale); //	the log will be in the language of the master user.
		switch ($action) {
			case 'clear_log':
				$type = gettext('Log reset');
				break;
			case 'delete_log':
				$type = gettext('Log deleted');
				break;
			case 'download_log':
				$type = gettext('Log downloaded');
				break;
			case 'setup_install':
				$type = gettext('Install');
				$addl = gettext('version') . ' ' . ZENPHOTO_VERSION . '[' . ZENPHOTO_RELEASE . "]";
				if (!zpFunctions::hasPrimaryScripts()) {
					$addl .= ' ' . gettext('clone');
				}
				break;
			case 'setup_proptect':
				$type = gettext('Protect setup scripts');
				break;
			case 'user_new':
				$type = gettext('Request add user');
				break;
			case 'user_update':
				$type = gettext('Request update user');
				break;
			case 'user_delete':
				$type = gettext('Request delete user');
				break;
			case 'XSRF_blocked':
				$type = gettext('Cross Site Reference');
				break;
			case 'blocked_album':
				$type = gettext('Album access');
				break;
			case 'blocked_access':
				$type = gettext('Admin access');
				break;
			case 'Front-end':
				$type = gettext('Guest login');
				break;
			case 'Back-end':
				$type = gettext('Admin login');
				break;
			case 'auth_cookie':
				$type = gettext('Authorization cookie check');
				break;
			default:
				$type = $action;
				break;
		}

		$file = SERVERPATH . '/' . DATA_FOLDER . '/security.log';
		$max = getOption('security_log_size');
		$_zp_mutex->lock();
		if ($max && @filesize($file) > $max) {
			switchLog('security');
		}
		$preexists = file_exists($file) && filesize($file) > 0;
		$f = fopen($file, 'a');
		if ($f) {
			if (!$preexists) { // add a header
				fwrite($f, gettext('date' . "\t" . 'requestor\'s IP' . "\t" . 'type' . "\t" . 'user ID' . "\t" . 'user name' . "\t" . 'outcome' . "\t" . 'authority' . "\tadditional information\n"));
			}
			$message = date('Y-m-d H:i:s') . "\t";
			$message .= $ip . "\t";
			$message .= $type . "\t";
			$message .= $user . "\t";
			$message .= $name . "\t";
			switch ($success) {
				case 0:
					$message .= gettext("Failed") . "\t";
					break;
				case 1:
					$message .= gettext("Success") . "\t";
					$message .= substr($authority, 0, strrpos($authority, '_auth'));
					break;
				case 2:
					$message .= gettext("Blocked") . "\t";
					break;
				default:
					$message .= $success . "\t";
			}
			if ($addl) {
				$message .= "\t" . $addl;
			}
			fwrite($f, $message . "\n");
			fclose($f);
			clearstatcache();
			if (!$preexists) {
				@chmod($file, 0660 & CHMOD_VALUE);
				if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
					$permission = fileperms($file) & 0700; //	on Windows owner==group==public
					$check = $permission != 0600 & CHMOD_VALUE;
				} else {
					$permission = fileperms($file) & 0777;
					$check = $permission != 0660 & CHMOD_VALUE;
				}
				if ($check) {
					$f = fopen($file, 'a');
					fwrite($f, "\t\t" . gettext('Set Security log permissions') . "\t\t\t" . gettext('Failed') . "\t\t" . sprintf(gettext('File permissions of Security log are %04o'), $permission) . "\n");
					fclose($f);
					clearstatcache();
				}
			}
		}
		$_zp_mutex->unlock();
		setupCurrentLocale($cur_locale); //	restore to whatever was in effect.
	}

	/**
	 * returns the user id and name of the logged in user
	 */
	private static function populate_user() {
		global $_zp_current_admin_obj;
		if (is_object($_zp_current_admin_obj)) {
			$user = $_zp_current_admin_obj->getUser();
			$name = $_zp_current_admin_obj->getName();
		} else {
			$user = $name = '';
		}
		return array($user, $name);
	}

	/**
	 * Logs an attempt to log onto the back-end or as an admin user
	 * Returns the rights to grant
	 *
	 * @param int $success the admin rights granted
	 * @param string $user
	 * @param string $pass
	 * @return int
	 */
	static function adminLoginLogger($success, $user, $pass, $auth = 'zp_admin_auth') {
		switch (getOption('logger_log_type')) {
			case 'all':
				break;
			case 'success':
				if (!$success)
					return false;
				break;
			case 'fail':
				if ($success)
					return true;
				break;
		}
		$name = '';
		if ($success) {
			$admin = Zenphoto_Authority::getAnAdmin(array('`user`='	 => $user, '`valid`=' => 1));
			$pass = ''; // mask it from display
			if (is_object($admin)) {
				$name = $admin->getName();
			}
		}
		security_logger::Logger((int) ($success && true), $user, $name, 'Back-end', $auth, $pass);
		return $success;
	}

	/**
	 * Logs an attempt to log on via the federated_logon plugin
	 * Returns the rights to grant
	 *
	 * @param int $success the admin rights granted
	 * @param string $user
	 * @param string $pass
	 * @return int
	 */
	static function federatedLoginLogger($success, $user) {
		return security_logger::adminLoginLogger($success, $user, 'n/a', 'federated_logon_auth');
	}

	/**
	 * Logs an attempt for a guest user to log onto the site
	 * Returns the "success" parameter.
	 *
	 * @param bool $success
	 * @param string $user
	 * @param string $pass
	 * @param string $athority what kind of login
	 * @return bool
	 */
	static function guestLoginLogger($success, $user, $pass, $athority) {
		switch (getOption('logger_log_type')) {
			case 'all':
				break;
			case 'success':
				if (!$success)
					return false;
				break;
			case 'fail':
				if ($success)
					return true;
				break;
		}
		$name = '';
		if ($success) {
			$admin = Zenphoto_Authority::getAnAdmin(array('`user`='	 => $user, '`valid`=' => 1));
			$pass = ''; // mask it from display
			if (is_object($admin)) {
				$name = $admin->getName();
			}
		}
		security_logger::Logger((int) ($success && true), $user, $name, 'Front-end', $athority, $pass);
		return $success;
	}

	/**
	 * Logs blocked accesses to Admin pages
	 * @param bool $allow set to true to override the block
	 * @param string $page the "return" link
	 */
	static function adminGate($allow, $page) {
		list($user, $name) = security_logger::populate_user();
		switch (getOption('logger_log_type')) {
			case 'all':
				break;
			case 'all_user':
				if (!$user)
					return $allow;
				break;
		}
		security_logger::Logger(0, $user, $name, 'blocked_access', '', $page);
		return $allow;
	}

	static function adminCookie($allow, $auth, $id) {
		if (!$allow && $auth) {
			switch (getOption('logger_log_type')) {
				case 'all':
				case 'fail':
					security_logger::Logger(0, NULL, NULL, 'auth_cookie', '', $id . ':' . $auth);
			}
		}
		return $allow;
	}

	/**
	 * Logs blocked accesses to Managed albums
	 * @param bool $allow set to true to override the block
	 * @param string $page the "return" link
	 */
	static function adminAlbumGate($allow, $page) {
		list($user, $name) = security_logger::populate_user();
		switch (getOption('logger_log_type')) {
			case 'all':
				break;
			case 'all_user':
				if (!$user)
					return $allow;
				break;
		}
		if (!$allow)
			security_logger::Logger(2, $user, $name, 'blocked_album', '', $page);
		return $allow;
	}

	/**
	 * logs attempts to save on the user tab
	 * @param string $discard
	 * @param object $userobj user object upon which the save was targeted
	 * @param string $class what the action was.
	 */
	static function UserSave($discard, $userobj, $class) {
		list($user, $name) = security_logger::populate_user();
		security_logger::Logger(1, $user, $name, 'user_' . $class, 'zp_admin_auth', $userobj->getUser());
		return $discard;
	}

	/**
	 * Loggs Cross Site Request Forgeries
	 *
	 * @param bool $discard
	 * @param string $token
	 * @return bool
	 */
	static function admin_XSRF_access($discard, $token) {
		list($user, $name) = security_logger::populate_user();
		security_logger::Logger(2, $user, $name, 'XSRF_blocked', '', $token);
		return false;
	}

	/**
	 * logs security log actions
	 * @param bool $allow
	 * @param string $log
	 * @param string $action
	 */
	static function log_action($allow, $log, $action) {
		list($user, $name) = security_logger::populate_user();
		security_logger::Logger((int) ($allow && true), $user, $name, $action, 'zp_admin_auth', basename($log));
		return $allow;
	}

	/**
	 * Logs setup actions
	 * @param bool $success
	 * @param string $action
	 * @param string $file
	 */
	static function log_setup($success, $action, $txt) {
		list($user, $name) = security_logger::populate_user();
		security_logger::Logger((int) ($success && true), $user, $name, 'setup_' . $action, 'zp_admin_auth', $txt);
		return $success;
	}

	/**
	 * Catch all logger for miscellaneous security records
	 * @param bool $success
	 * @param string $requestor
	 * @param string $auth
	 * @param string $txt
	 */
	static function security_misc($success, $requestor, $auth, $txt) {
		security_logger::Logger((int) ($success && true), NULL, NULL, $requestor, 'zp_admin_auth', $txt);
		return $success;
	}

}

?>