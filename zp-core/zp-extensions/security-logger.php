<?php
/**
 * Places security information in a security log
 * The logged data includes the ip address of the site attempting the login, the type of login, the user/user name,
 * and the success/failure. On failure, the password used in the attempt is also shown.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_is_filter = 9|CLASS_PLUGIN;
$plugin_description = sprintf(gettext("Logs all attempts to login to or illegally access the admin pages. Log is kept in <em>security_log.txt</em> in the %s folder."),DATA_FOLDER);
$plugin_author = "Stephen Billard (sbillard)";
$plugin_URL = "http://www.zenphoto.org/documentation/plugins/".PLUGIN_FOLDER."--security-logger.php.html";
$plugin_version = '1.4.0';
$option_interface = 'security_logger';

if (getOption('logger_log_admin')) zp_register_filter('admin_login_attempt', 'security_logger_adminLoginLogger',1);
if (getOption('logger_log_guests')) zp_register_filter('guest_login_attempt', 'security_logger_guestLoginLogger',1);
zp_register_filter('admin_allow_access', 'security_logger_adminGate',1);
zp_register_filter('admin_managed_albums_access', 'security_logger_adminAlbumGate',1);
zp_register_filter('save_user', 'security_logger_UserSave',1);
zp_register_filter('admin_XSRF_access', 'security_logger_admin_XSRF_access',1);
zp_register_filter('admin_log_actions', 'security_logger_log_action',1);
zp_register_filter('log_setup','security_logger_log_setup',1);

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
	function security_logger() {
		setOptionDefault('logger_log_guests', 1);
		setOptionDefault('logger_log_admin', 1);
		setOptionDefault('logger_log_type', 'all');
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(	gettext('Record logon attempts of') => array('key' => 'logger_log_allowed', 'type' => OPTION_TYPE_CHECKBOX_ARRAY,
										'checkboxes' => array(gettext('Administrators') => 'logger_log_admin', gettext('Guests') => 'logger_log_guests'),
										'desc' => gettext('If checked login attempts will be logged.')),
									gettext('Record') =>array('key' => 'logger_log_type', 'type' => OPTION_TYPE_RADIO,
										'buttons' => array(gettext('All attempts') => 'all', gettext('Successful attempts') => 'success', gettext('unsuccessful attempts') => 'fail'),
										'desc' => gettext('Record login failures, successes, or all attempts.'))
		);
	}

	function handleOption($option, $currentValue) {
	}

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
function security_logger_loginLogger($success, $user, $name, $ip, $type, $authority, $addl=NULL) {
	$file = dirname(dirname(dirname(__FILE__))).'/'.DATA_FOLDER . '/security_log.txt';
	$preexists = file_exists($file) && filesize($file) > 0;
	$f = fopen($file, 'a');
	if (!$preexists) { // add a header
		fwrite($f, gettext('date'."\t".'requestor\'s IP'."\t".'type'."\t".'user ID'."\t".'user name'."\t".'outcome'."\t".'authority'."\tadditional information\n"));
	}
	$message = date('Y-m-d H:i:s')."\t";
	$message .= $ip."\t";
	$message .= $type."\t";
	$message .= $user."\t";
	$message .= $name."\t";
	if ($success) {
		$message .= gettext("Success")."\t";
		$message .= substr($authority, 0, strrpos($authority,'_auth'));
	} else {
		$message .= gettext("Failed")."\t";
	}
	if ($addl) {
		$message .= "\t".$addl;
	}
	fwrite($f, $message . "\n");
	fclose($f);
	chmod($file, 0600);
}

/**
 * rturns the user id and name of the logged in user
 */
function security_logger_populate_user() {
	global $_zp_current_admin_obj;
	if (is_object($_zp_current_admin_obj)) {
		$user = $_zp_current_admin_obj->getUser();
		$name = $_zp_current_admin_obj->getName();
	} else {
		$user= $name = '';
	}
	return array($user,$name);
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
function security_logger_adminLoginLogger($success, $user, $pass) {
	global $_zp_authority;
	switch (getOption('logger_log_type')) {
		case 'all':
			break;
		case 'success':
			if (!$success) return false;
			break;
		case 'fail':
			if ($success) return true;
			break;
	}
	if ($success) {
		$admin = $_zp_authority->getAnAdmin(array('`user`=' => $user, '`valid`=' => 1));
		$pass = '';	// mask it from display
		if (is_object($admin)) {
			$name = $admin->getName();
		}
	} else {
		$name = '';
	}
	security_logger_loginLogger($success, $user, $name, getUserIP(), gettext('Back-end'), 'zp_admin_auth',$pass);
	return $success;
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
function security_logger_guestLoginLogger($success, $user, $pass, $athority) {
	global $_zp_authority;
	switch (getOption('logger_log_type')) {
		case 'all':
			break;
		case 'success':
			if (!$success) return false;
			break;
		case 'fail':
			if ($success) return true;
			break;
	}
	if ($success) {
		$admin = $_zp_authority->getAnAdmin(array('`user`=' => $user, '`valid`=' => 1));
		$pass = '';	// mask it from display
		if (is_object($admin)) {
			$name = $admin->getName();
		}
	} else {
		$name = '';
	}
	security_logger_loginLogger($success, $user, $name, getUserIP(), gettext('Front-end'), $athority, $pass);
	return $success;
}

/**
 * Logs blocked accesses to Admin pages
 * @param bool $allow set to true to override the block
 * @param string $page the "return" link
 */
function security_logger_adminGate($allow, $page) {
	list($user,$name) = security_logger_populate_user();
	security_logger_loginLogger(false, $user, $name, getUserIP(), gettext('Blocked access'), '', $page);
	return $allow;
}

/**
 * Logs blocked accesses to Managed albums
 * @param bool $allow set to true to override the block
 * @param string $page the "return" link
 */
function security_logger_adminAlbumGate($allow, $page) {
	list($user,$name) = security_logger_populate_user();
	security_logger_loginLogger(false, $user, $name, getUserIP(), gettext('Blocked album'), '', $page);
	return $allow;
}

/**
 * logs attempts to save on the user tab
 * @param string $discard
 * @param object $userobj user object upon which the save was targeted
 * @param string $class what the action was.
 */
function security_logger_UserSave($discard, $userobj, $class) {
	list($user,$name) = security_logger_populate_user();
	switch ($class) {
		case 'new':
			$what = gettext('Request add user');
			break;
		case 'update':
			$what = gettext('Request update user');
			break;
		case 'delete':
			$what = gettext('Request delete user');
			break;
	}
	security_logger_loginLogger(true, $user, $name, getUserIP(), $what, 'zp_admin_auth', $userobj->getUser());
	return $discard;
}

/**
 * Loggs Cross Site Request Forgeries
 *
 * @param bool $discard
 * @param string $token
 * @return bool
 */
function security_logger_admin_XSRF_access($discard, $token) {
	list($user,$name) = security_logger_populate_user();
	security_logger_loginLogger(false, $user, $name, getUserIP(), gettext('XSRF access blocked'), '', $token);
	return false;
}

/**
 * logs security log actions
 * @param bool $allow
 * @param string $log
 * @param string $action
 */
function security_logger_log_action($allow, $log, $action) {
	list($user,$name) = security_logger_populate_user();
	switch ($action) {
		case 'clear_log':
			$act = gettext('Log reset');
			break;
		case 'delete_log':
			$act = gettext('Log deleted');
			break;
		case 'download_log':
			$act = gettext('Log downloaded');
			break;
		default:
			$act = $action;
	}
	security_logger_loginLogger(true, $user, $name, getUserIP(), $act, 'zp_admin_auth', basename($log));
	return $allow;
}

/**
 * Logs setup actions
 * @param bool $success
 * @param string $action
 * @param string $file
 */
function security_logger_log_setup($success, $action, $txt) {
	list($user,$name) = security_logger_populate_user();
	switch ($action) {
		case 'install':
			$act = gettext('Installed');
			$txt = gettext('version').' '.ZENPHOTO_VERSION.'['.ZENPHOTO_RELEASE."]";
			break;
		case 'delete':
			$act = gettext('Removed setup file');
			break;
		default:
			$act = $action;
	}
	security_logger_loginLogger($success, $user, $name, getUserIP(), $act, 'zp_admin_auth', $txt);
	return $success;
}
?>