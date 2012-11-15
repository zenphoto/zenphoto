<?php
/**
 * Google accounts logon handler.
 *
 * This just supplies the Yahoo URL to OpenID_try.php. The rest is normal OpenID handling
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
$_SESSION['OpenID_cleaner_pattern'] = '/me.yahoo.com\/.*\/(.*)/';
$_SESSION['provider'] = 'Yahoo';
$_GET['openid_identifier'] = 'https://Yahoo.com';
$_GET['action'] = 'verify';
require 'OpenID_try_auth.php';
?>