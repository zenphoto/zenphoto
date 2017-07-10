<?php

/**
 * Adapted from Google documentation at {@link https://developers.google.com/api-client-library/php/auth/web-app}
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

//Google API PHP Library includes
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/googleAPI/autoload.php');
// Fill CLIENT ID, CLIENT SECRET ID, REDIRECT URI from Google Developer Console
$client_id = getOption('googleLogin_ClientID');
$client_secret = getOption('googleLogin_ClientSecret');
$redirect_uri = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/googleLogin/google.php';
$simple_api_key = getOption('gmap_map_api_key');

//Create Client Request to access Google API
$client = new Google_Client();
if (isset($_SERVER['SERVER_ADDR'])) {
	if ($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1') {
		//for testing when certificate verification fails locally
		$http = new GuzzleHttp\Client(['verify' => false]);
		$client->setHttpClient($http);
	}
}
$client->setApplicationName("ZenPhoto20 Google OAuth Login");
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->setDeveloperKey($simple_api_key);
$client->addScope("https://www.googleapis.com/auth/userinfo.email");

//Send Client Request
$objOAuthService = new Google_Service_Oauth2($client);

if (isset($_REQUEST['redirect'])) {
	$_SESSION['redirect'] = filter_var($_REQUEST['redirect'], FILTER_SANITIZE_URL);
} else {
	if (!isset($_SESSION['redirect'])) {
		$_SESSION['redirect'] = FULLWEBPATH;
	}
}

//Authenticate code from Google OAuth Flow
//Add Access Token to Session
if (isset($_GET['code'])) {
	$client->authenticate($_GET['code']);
	$_SESSION['access_token'] = $client->getAccessToken();
	header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

//Set Access Token to make Request
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
	$client->setAccessToken($_SESSION['access_token']);
}

//Get User Data from Google
if ($client->getAccessToken()) {
	$userData = $objOAuthService->userinfo->get();
	if (!empty($userData)) {
		$email = $userData->email;
		$name = $userData->name;
		$googleid = $userData->id;
		//don't need the Google access anymore as we will be using the zenphoto user mechanism
		$accessToken = $client->getAccessToken();
		$client->revokeToken($accessToken);

		googleLogin::credentials($googleid, $email, $name, $_SESSION['redirect']);
	}
	$_SESSION['access_token'] = $client->getAccessToken();
} else {
	$authUrl = $client->createAuthUrl();

	header('Location: ' . $authUrl);
}
?>