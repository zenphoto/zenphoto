<?php

/**
 * processes the authorization (or login) of admin users
 * @package admin
 */
// force UTF-8 Ø

global $_zp_current_admin_obj, $_zp_loggedin, $_zp_authority;
$_zp_current_admin_obj = null;
if (file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/alt/lib-auth.php')) { // load a custom authroization package if it is present
	require_once(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/alt/lib-auth.php');
} else {
	require_once(dirname(__FILE__) . '/lib-auth.php');
	$_zp_authority = new Zenphoto_Authority();
}

foreach (Zenphoto_Authority::getRights() as $key => $right) {
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
if (zp_getCookie('zenphoto_ssl') && !secureServer()) {
	$redirect = "https://" . $_SERVER['HTTP_HOST'] . getRequestURI();
	header("Location:$redirect");
	exitZP();
}

if (isset($_POST['login'])) { //	Handle the login form.
	if (secureServer()) {
		// https: set the 'zenphoto_ssl' marker for redirection
		zp_setCookie("zenphoto_ssl", "needed");
	}
	$_zp_loggedin = $_zp_authority->handleLogon();
	if ($_zp_loggedin) {
		if (isset($_POST['redirect'])) {
			$redirect = sanitizeRedirect($_POST['redirect']);
			if (!empty($redirect)) {
				header("Location: " . $redirect);
				exitZP();
			}
		}
	}
} else { //	no login form, check the cookie
	if (isset($_GET['ticket'])) { // password reset query
		$_zp_authority->validateTicket(sanitize($_GET['ticket']), sanitize(@$_GET['user']));
	}
	$_zp_loggedin = $_zp_authority->checkCookieCredentials();
	if ($_zp_loggedin) {
		$locale = $_zp_current_admin_obj->getLanguage();
		if (!empty($locale)) { //	set his prefered language
			setupCurrentLocale($locale);
		}
	}
}
if (!$_zp_loggedin) { //	Clear the ssl cookie
	zp_clearCookie("zenphoto_ssl");
}
// Handle a logout action.
if (isset($_REQUEST['logout'])) {
	$location = Zenphoto_Authority::handleLogout();
	zp_clearCookie("zenphoto_ssl");
	if (empty($location)) {
		$redirect = '?fromlogout';
		if (isset($_GET['p'])) {
			$redirect .= "&p=" . sanitize($_GET['p']);
		}
		if (isset($_GET['searchfields'])) {
			$redirect .= "&searchfields=" . sanitize($_GET['searchfields']);
		}
		if (isset($_GET['words'])) {
			$redirect .= "&words=" . sanitize($_GET['words']);
		}
		if (isset($_GET['date'])) {
			$redirect .= "&date=" . sanitize($_GET['date']);
		}
		if (isset($_GET['album'])) {
			$redirect .= "&album=" . sanitize($_GET['album']);
		}
		if (isset($_GET['image'])) {
			$redirect .= "&image=" . sanitize($_GET['image']);
		}
		if (isset($_GET['title'])) {
			$redirect .= "&title=" . sanitize($_GET['title']);
		}
		if (isset($_GET['page'])) {
			$redirect .= "&page=" . sanitize($_GET['page']);
		}
		if (!empty($redirect))
			$redirect = '?' . substr($redirect, 1);
		if ($_GET['logout']) {
			$rd_protocol = 'https';
		} else {
			$rd_protocol = 'http';
		}
		$location = $rd_protocol . "://" . $_SERVER['HTTP_HOST'] . WEBPATH . '/index.php' . $redirect;
	}
	header("Location: " . $location);
	exitZP();
}
?>
