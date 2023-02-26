<?php

/**
 * processes the authorization (or login) of admin users
 * @package zpcore\admin
 */
// force UTF-8 Ø

global $_zp_current_admin_obj, $_zp_loggedin, $_zp_authority;
$_zp_current_admin_obj = null;

// load a custom authroization package if it is present
if (file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/alt/auth.php')) {
	/**
	 * Zenphoto USER credentials handlers
	 *
	 * An alternate authorization script may be provided to override this script. To do so, make a script that
	 * implements the classes declared below. Place the new script inthe <ZENFOLDER>/plugins/alt/ folder. Zenphoto
	 * will then will be automatically loaded the alternate script in place of this one.
	 *
	 * Replacement libraries must implement two classes:
	 * 		"Authority" class: Provides the methods used for user authorization and management
	 * 			store an instantiation of this class in $_zp_authority.
	 *
	 * 		Administrator: supports the basic Zenphoto needs for object manipulation of administrators.
	 * (You can include this script and extend the classes if that suits your needs.)
	 *
	 * The global $_zp_current_admin_obj represents the current admin with.
	 * The library must instantiate its authority class and store the object in the global $_zp_authority (only if you use t
	 * (Note, this library does instantiate the object as described. This is so its classes can
	 * be used as parent classes for lib-auth implementations. If auth_zp.php decides to use this
	 * library it will instantiate the class and store it into $_zp_authority.
	 *
	 * The following elements need to be present in any alternate implementation in the
	 * array returned by getAdministrators().
	 *
	 * 		In particular, there should be array elements for:
	 * 				'id' (unique), 'user' (unique),	'pass',	'name', 'email', 'rights', 'valid',
	 * 				'group', and 'custom_data'
	 *
	 * 		So long as all these indices are populated it should not matter when and where
	 * 		the data is stored.
	 *
	 * 		Administrator class methods are required for these elements as well.
	 *
	 * 		The getRights() method must define at least the rights defined by the method in
	 * 		this library.
	 *
	 * 		The checkAuthorization() method should promote the "most privileged" Admin to
	 * 		ADMIN_RIGHTS to insure that there is some user capable of adding users or
	 * 		modifying user rights.
	 *
	 * @package zpcore
	 * @subpackage classes\authorization
	 */
	require_once(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/alt/auth.php');
} else if (file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/alt/lib-auth.php')) {
	require_once(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/alt/lib-auth.php');
} else {
	require_once(dirname(__FILE__) . '/classes/class-authority.php');
	require_once(dirname(__FILE__) . '/classes/class-administrator.php');
	$_zp_authority = new Authority();
}

foreach (Authority::getRights() as $key => $right) {
	define($key, $right['value']);
}

define('MANAGED_OBJECT_RIGHTS_EDIT', 1);
define('MANAGED_OBJECT_RIGHTS_UPLOAD', 2);
define('MANAGED_OBJECT_RIGHTS_VIEW', 4);
define('LIST_RIGHTS', NO_RIGHTS);
if (!defined('USER_RIGHTS')) {
	define('USER_RIGHTS', NO_RIGHTS);
}

if (defined('VIEW_ALL_RIGHTS')) {
	define('ALL_ALBUMS_RIGHTS', VIEW_ALL_RIGHTS);
	define('ALL_PAGES_RIGHTS', VIEW_ALL_RIGHTS);
	define('ALL_NEWS_RIGHTS', VIEW_ALL_RIGHTS);
	define('VIEW_SEARCH_RIGHTS', NO_RIGHTS);
	define('VIEW_GALLERY_RIGHTS', NO_RIGHTS);
	define('VIEW_FULLIMAGE_RIGHTS', NO_RIGHTS);
} else {
	define('VIEW_ALL_RIGHTS', ALL_ALBUMS_RIGHTS | ALL_PAGES_RIGHTS | ALL_NEWS_RIGHTS);
}

// If the auth variable gets set somehow before this, get rid of it.
$_zp_loggedin = false;

// we have the ssl marker cookie, normally we are already logged in
// but we need to redirect to ssl to retrive the auth cookie (set as secure).
if (zp_getCookie('zpcms_ssl') && !secureServer()) {
	$redirect = "https://" . $_SERVER['HTTP_HOST'] . getRequestURI();
	redirectURL($redirect);
}

if (isset($_POST['login'])) { //	Handle the login form.
	if (secureServer()) {
		// https: set the 'zpcms_ssl' marker for redirection
		zp_setCookie("zpcms_ssl", "needed");
	}
	$_zp_loggedin = $_zp_authority->handleLogon();
	if ($_zp_loggedin) {
		if (isset($_POST['redirect'])) {
			$redirect = sanitizeRedirect($_POST['redirect']);
			if (!empty($redirect)) {
				redirectURL($redirect);
			}
		}
	}
} else { //	no login form, check the cookie
	if (isset($_GET['ticket'])) { // password reset query
		$_zp_authority->validateTicket(sanitize($_GET['ticket']), sanitize(@$_GET['user']));
	}
	$_zp_loggedin = $_zp_authority->checkCookieCredentials();
	if ($_zp_loggedin && is_object($_zp_current_admin_obj)) {
		$userlocale = getUserLocale();
		$adminlocale = $_zp_current_admin_obj->getLanguage();
		if (OFFSET_PATH == 0 && $userlocale) {
			$locale = $userlocale;
		} else {
			$locale = $adminlocale;
		}
		if (!empty($locale)) { //	set his prefered language
			setupCurrentLocale($locale);
		}
	} else {
		$locale = 'en_US';
	}
}
if (!$_zp_loggedin) { //	Clear the ssl cookie
	zp_clearCookie("zpcms_ssl");
}
// Handle a logout action.
if (isset($_REQUEST['logout'])) {
	$location = Authority::handleLogout();
	zp_clearCookie("zpcms_ssl");
	if (empty($location)) {
		$redirect = '?fromlogout' . Authority::getLogoutURLPageParams();
		$location = FULLWEBPATH . '/index.php' . $redirect;
	}
	header('Cache-Control: no-cache, must-revalidate, max-age=0');
	redirectURL($location);
}