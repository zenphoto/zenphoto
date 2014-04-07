<?php

require_once("OpenID_common.php");
require_once(dirname(__FILE__).'/Auth/OpenID/AX.php');
if (!defined('OFFSET_PATH')) define('OFFSET_PATH',4);
require_once(dirname(dirname(dirname(__FILE__))).'/admin-functions.php');
if (session_id() == '') session_start();

function getOpenIDURL() {
	// Render a default page if we got a submission without an openid
	// value.
	if (empty($_GET['openid_identifier'])) {
		return false;
	}

	return $_GET['openid_identifier'];
}

function run() {
	if (!($openid = getOpenIDURL())) {
		return gettext('Not a valid OpenID URL');
	}

	$consumer = getConsumer();

	// Begin the OpenID authentication process.
	$auth_request = $consumer->begin($openid);

	// No auth request means we can't begin OpenID.
	if (!$auth_request) {
		return gettext("Authentication error; not a valid OpenID.");
	}
	$sreg_attribute = array();
	// Create an authentication request to the OpenID provider$auth = $consumer->begin($oid_identifier);
	// Create attribute request object// See http://code.google.com/apis/accounts/docs/OpenID.html#Parameters for parameters
	// Usage: make($type_uri, $count=1, $required=false, $alias=null)
	$attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/email',2,1, 'email');
	$sreg_attribute[] = 'email';

	$attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson',1,1, 'name');
	$ax_attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/first',1,1,'firstname');
	$ax_attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/middle',1,1,'middlename');
	$ax_attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/last',1,1,'lastname');
	$sreg_attribute[] = 'fullname';

	$attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/friendly',1,1, 'nicknamename');
	$sreg_attribute[] = 'nickname';

	// Create AX fetch request
	$sreg_request = Auth_OpenID_SRegRequest::build($sreg_attribute);
	if ($sreg_request) {
		$auth_request->addExtension($sreg_request);
	}
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
			return sprintf(gettext("Could not redirect to server: %s"), $redirect_url->message);
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
			return sprintf(gettext("Could not redirect to server: %s"), $form_html->message);
		} else {
			print $form_html;
		}
	}
	return false;
}

$error = run();
if ($error) {
	header('Location: '.FULLWEBPATH.'/'.ZENFOLDER.'/admin.php?_zp_login_error='.sprintf(gettext('Federated logon error:<br />%s'), $error));
	exitZP();
}

?>