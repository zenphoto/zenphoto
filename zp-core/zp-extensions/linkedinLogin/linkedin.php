<?php

/**
 * Adapted from DiscusDesk at {@link http://www.discussdesk.com/login-with-linkedin-using-php-and-oauth-api-with-live-demo-and-download.htm}
 * by Stephen Billard
 *
 * @author Stephen Billard (sbillard)
 * @Copyright 2017 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage users
 */
if (!defined('OFFSET_PATH'))
	define('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-functions.php');

zp_session_start();

$callbackURL = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/linkedinLogin/linkedin.php';
$linkedinApiKey = getOption('linkedinLogin_ClientID');
$linkedinApiSecret = getOption('linkedinLogin_ClientSecret');
$linkedinScope = 'r_basicprofile r_emailaddress';

if (isset($_REQUEST['redirect'])) {
	$_SESSION['redirect'] = filter_var($_REQUEST['redirect'], FILTER_SANITIZE_URL);
} else {
	if (!isset($_SESSION['redirect'])) {
		$_SESSION['redirect'] = FULLWEBPATH;
	}
}

include_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . "/common/oAuth/http.php");
include_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . "/common/oAuth/oauth_client.php");

$client = new oauth_client_class;

$client->debug = false;
$client->debug_http = true;
$client->redirect_uri = $callbackURL;

$client->client_id = $linkedinApiKey;
$application_line = __LINE__;
$client->client_secret = $linkedinApiSecret;
$_SESSION['requestToken'] = serialize($client->client_secret);

/* API permissions
 */
$client->scope = $linkedinScope;
if (($success = $client->Initialize())) {
	if (($success = $client->Process())) {
		if (strlen($client->authorization_error)) {
			$client->error = $client->authorization_error;
			$success = false;
		} elseif (strlen($client->access_token)) {
			$success = $client->CallAPI(
							'http://api.linkedin.com/v1/people/~:(id,email-address,first-name,last-name,location,picture-url,public-profile-url,formatted-name)', 'GET', array(
					'format' => 'json'
							), array('FailOnAccessError' => true), $user);
		}
	}
	$success = $client->Finalize($success);
}
if ($client->exit) {
	exitZP();
}
if ($success) {
	linkedinLogin::credentials($user->id, $user->emailAddress, $user->firstName . ' ' . $user->lastName, $_SESSION['redirect']);
} else {
	session_unset();
	header('Location: ' . WEBPATH . '/' . ZENFOLDER . '/admin.php?_zp_login_error=' . html_encode($client->error));
	exitZP();
}
?>
