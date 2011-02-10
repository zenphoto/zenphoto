<?php
/**
 * Google accounts logon handler.
 *
 * This just supplies the google URL to OpenID_try.php. The rest is normal OpenID handling
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage usermanagement
 */

require_once("OpenID_common.php");
if (!defined('OFFSET_PATH')) define('OFFSET_PATH',4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-functions.php');

if (isset($_GET['redirect'])) {
	$redirect = sanitize($_GET['redirect']);
} else {
	$redirect = '';
}
zp_setCookie('OpenID_redirect', $redirect, 60);
zp_setCookie('OpenID_cleaner_pattern', '/^.*?id=(.*)/',  60);
$_GET['openid_identifier'] = 'https://www.google.com/accounts/o8/id';
$_GET['action'] = 'verify';

require 'OpenID_try_auth.php';
?>