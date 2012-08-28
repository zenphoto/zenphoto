<?php

require_once('OpenID_common.php');
require_once(dirname(__FILE__).'/Auth/OpenID/AX.php');
if (!defined('OFFSET_PATH')) define('OFFSET_PATH',4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-functions.php');
session_start();

function escape($thing) {
	return htmlentities($thing);
}

function run() {

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

		$email = $name = NULL;
		$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
		$sreg = $sreg_resp->contents();
		if ($sreg) {
			if (@$sreg['email']) {
				$email = trim($sreg['email']);
			}
			if (@$sreg['nickname']) {
				$name = $sreg['nickname'];
			}
			if (@$sreg['fullname']) {
				$name = $sreg['fullname'];
			}
		}
		$ax_resp = Auth_OpenID_AX_FetchResponse::fromSuccessResponse($response);
		if ($ax_resp) {
			$arr_ax_resp = get_object_vars($ax_resp);
			$arr_ax_data = $arr_ax_resp['data'];
			if(empty($email) && isset($arr_ax_data["http://axschema.org/contact/email"]) && count($arr_ax_data["http://axschema.org/contact/email"])>0) {
				$email = $arr_ax_data["http://axschema.org/contact/email"][0];
			}
			if(empty($name) && isset($arr_ax_data["http://axschema.org/namePerson"]) && count($arr_ax_data["http://axschema.org/namePerson"])>0) {
				$name = $arr_ax_data["http://axschema.org/namePerson"][0];
			}
			if (empty($name)) {
				$name_first = '';
				$name_middle = '';
				$name_last = '';
				if(isset($arr_ax_data["http://axschema.org/namePerson/first"]) && count($arr_ax_data["http://axschema.org/namePerson/first"])>0) {
					$name_first = $arr_ax_data["http://axschema.org/namePerson/first"][0];
				}
				if(isset($arr_ax_data["http://axschema.org/namePerson/middle"]) && count($arr_ax_data["http://axschema.org/namePerson/middle"])>0) {
					$name_middle = $arr_ax_data["http://axschema.org/namePerson/middle"][0];
				}
				if(isset($arr_ax_data["http://axschema.org/namePerson/last"]) && count($arr_ax_data["http://axschema.org/namePerson/last"])>0) {
					$name_last = $arr_ax_data["http://axschema.org/namePerson/last"][0];
				}
				$fullname = trim(trim(trim($name_first).' '.$name_middle).' '.$name_last);
				if (!empty($fullname)) {
					$name = $fullname;
				}
			}
			if(empty($name) && isset($arr_ax_data["http://axschema.org/namePerson/friendly"]) && count($arr_ax_data["http://axschema.org/namePerson/friendly"])>0) {
				$name = $arr_ax_data["http://axschema.org/namePerson/friendly"][0];
			}
		}
		$userid = trim(str_replace(array('http://','https://'), '', $openid), '/');	//	always remove the protocol
		$pattern = @$_SESSION['OpenID_cleaner_pattern'];
		if ($pattern) {
			if(preg_match($pattern, $userid, $matches)) {
				$userid = $matches[1];
			}
		}
		$provider = @$_SESSION['provider'];
		if (strlen($userid)+strlen($provider) > 63) {
			$userid = sha1($userid);
		}
		if ($provider) {
			$userid = $provider.':'.$userid;
		}
		$redirect = @$_SESSION['OpenID_redirect'];
		$success .= federated_logon::credentials($userid, $email, $name, $redirect);

	}
	return $success;
}

$error = run();
if ($success) {
	header('Location: '.FULLWEBPATH.'/'.ZENFOLDER.'/admin.php?_zp_login_error='.sprintf(gettext('Federated logon error:<br />%s'), $error));
	exitZP();
}

?>