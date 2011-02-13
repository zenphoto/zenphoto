<?php

require_once("OpenID_common.php");
require_once(dirname(__FILE__).'/Auth/OpenID/AX.php');
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

	$sreg_request = Auth_OpenID_SRegRequest::build(array('fullname', 'email'),array('nickname'));

	if ($sreg_request) {
		$auth_request->addExtension($sreg_request);
	}
	// Create an authentication request to the OpenID provider$auth = $consumer->begin($oid_identifier);
	// Create attribute request object// See http://code.google.com/apis/accounts/docs/OpenID.html#Parameters for parameters
	// Usage: make($type_uri, $count=1, $required=false, $alias=null)
	$attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/email',2,1, 'email');
	$attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson',1,1, 'tname');
	$attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/friendly',1,1, 'nicknamename');
	// Create AX fetch request
	$ax = new Auth_OpenID_AX_FetchRequest;
	// Add attributes to AX fetch request
	foreach($attribute as $attr){
		$ax->add($attr);
	}
	// Add AX fetch request to authentication request
	$auth_request->addExtension($ax);

	// Redirect the user to the OpenID server for authentication.
	// Store the token for this authentication so we can verify the
	// response.

	// For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
	// form to send a POST request to the server.
	if ($auth_request->shouldSendRedirect()) {
		$redirect_url = $auth_request->redirectURL(getTrustRoot(), getReturnTo());
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