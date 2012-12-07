<?php
/**
 * Google accounts logon handler.
 *
 * This just supplies the MyOpenID URL to OpenID_try.php. The rest is normal OpenID handling
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage users
 */

require_once("OpenID_common.php");
session_start();

if (isset($_GET['redirect'])) {
	$redirect = sanitizeRedirect($_GET['redirect']);
} else {
	$redirect = '';
}
$_SESSION['OpenID_redirect'] = $redirect;
$_SESSION['OpenID_cleaner_pattern'] = '/(.*)\.myopenid\.com/';
$_SESSION['provider'] = 'MyOpenID';
$_GET['openid_identifier'] = 'https://www.myopenid.com';
$_GET['action'] = 'verify';
require 'OpenID_try_auth.php';
?>