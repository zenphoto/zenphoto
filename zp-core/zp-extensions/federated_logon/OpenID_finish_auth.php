<?php

require_once('OpenID_common.php');
if (!defined('OFFSET_PATH')) define('OFFSET_PATH',4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-functions.php');
session_start();

function escape($thing) {
	return htmlentities($thing);
}

function run() {

	$redirect = zp_getCookie('OpenID_redirect');
	$consumer = getConsumer();

	// Complete the authentication process using the server's
	// response.
	$return_to = getReturnTo();
	$response = $consumer->complete($return_to);

	// Check the response status.
	if ($response->status == Auth_OpenID_CANCEL) {
		// This means the authentication was cancelled.
		$msg = gettext('Verification cancelled.');
	} else if ($response->status == Auth_OpenID_FAILURE) {
		// Authentication failed; display the error message.
		$msg = sprintf(gettext("OpenID authentication failed: %s"), $response->message);
	} else if ($response->status == Auth_OpenID_SUCCESS) {
		// This means the authentication succeeded; extract the
		// identity URL and Simple Registration data (if it was
		// returned).
		$openid = $response->getDisplayIdentifier();
		$esc_identity = escape($openid);

		$success = sprintf(gettext('You have successfully verified <a href="%s">%s</a> as your identity.'),
		$esc_identity, $esc_identity);

		if ($response->endpoint->canonicalID) {
			$escaped_canonicalID = escape($response->endpoint->canonicalID);
			$success .= '  (XRI CanonicalID: '.$escaped_canonicalID.') ';
		}

		$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);

		$sreg = $sreg_resp->contents();

		if (@$sreg['email']) {
			$email = trim($sreg['email']);
		} else {
			$email = NULL;
		}

		if (@$sreg['nickname']) {
			$name = $sreg['nickname'];
		} else {
			$name = NULL;
		}

		if (@$sreg['fullname']) {
			$name = $sreg['fullname'];
		}
		if (false !== strpos($openid, GOOGLE_ACCOUNT)) {
			$idparts = explode('?id=',$openid);
			$userid = $idparts[1];
		} else {
			$userid = trim(str_replace('https://', '', $openid), '/');
			if (strlen($userid) > 64) {
				$userid = sha1($userid);
			}
		}
		$success .= logonFederatedCredentials($userid, $email, $name, $redirect);

		}


	include 'OpenID_logon.php';
}

run();

?>