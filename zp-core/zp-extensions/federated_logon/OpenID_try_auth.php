<?php

require_once("OpenID_common.php");
session_start();

function getOpenIDURL() {
	// Render a default page if we got a submission without an openid
	// value.
	if (empty($_GET['openid_identifier'])) {
		$error = gettext("Expected an OpenID URL.");
		include 'OpenID_logon.php';
		exit(0);
	}

	return $_GET['openid_identifier'];
}

function run() {
	$openid = getOpenIDURL();
	$consumer = getConsumer();

	// Begin the OpenID authentication process.
	$auth_request = $consumer->begin($openid);

	// No auth request means we can't begin OpenID.
	if (!$auth_request) {
		displayError(gettext("Authentication error; not a valid OpenID."));
	}

	$sreg_request = Auth_OpenID_SRegRequest::build(	array('nickname'),// Required
																								// Optional
																								array('fullname', 'email'));

	if ($sreg_request) {
		$auth_request->addExtension($sreg_request);
	}

	// Redirect the user to the OpenID server for authentication.
	// Store the token for this authentication so we can verify the
	// response.

	// For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
	// form to send a POST request to the server.
	if ($auth_request->shouldSendRedirect()) {
		$redirect_url = $auth_request->redirectURL(getTrustRoot(),
		getReturnTo());

		// If the redirect URL can't be built, display an error
		// message.
		if (Auth_OpenID::isFailure($redirect_url)) {
			displayError(sprintf(gettext("Could not redirect to server: %s"), $redirect_url->message));
		} else {
			// Send redirect.
			header("Location: ".$redirect_url);
		}
	} else {
		// Generate form markup and render it.
		$form_id = 'openid_message';
		$form_html = $auth_request->htmlMarkup(getTrustRoot(), getReturnTo(),
		false, array('id' => $form_id));

		// Display an error if the form markup couldn't be generated;
		// otherwise, render the HTML.
		if (Auth_OpenID::isFailure($form_html)) {
			displayError(sprintf(gettext("Could not redirect to server: %s"), $form_html->message));
		} else {
			print $form_html;
		}
	}
}

run();

?>