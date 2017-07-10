<?php

/**
 * Facebook authentication protocol
 *
 * Adapted from {@link http://www.krizna.com/general/login-with-facebook-using-php/ Login with facebook using PHP}
 * by Stephen Billard
 *
 * @author Stephen Billard (sbillard)
 * @Copyright 2017 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage users
 *
 */
if (!defined('OFFSET_PATH'))
	define('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-functions.php');

zp_session_start();

if (isset($_REQUEST['redirect'])) {
	$_SESSION['redirect'] = filter_var($_REQUEST['redirect'], FILTER_SANITIZE_URL);
} else {
	if (!isset($_SESSION['redirect'])) {
		$_SESSION['redirect'] = FULLWEBPATH;
	}
}

require_once 'autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\Entities\AccessToken;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookHttpable;

// init app with app id and secret
FacebookSession::setDefaultApplication(getOption('facebookLogin_APPID'), getOption('facebookLogin_APPSecret'));
// login helper with redirect_uri
$helper = new FacebookRedirectLoginHelper(FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/facebookLogin/facebook.php');
try {
	$session = $helper->getSessionFromRedirect();
} catch (FacebookRequestException $ex) {
	// When Facebook returns an error
} catch (Exception $ex) {
	// When validation fails or other local issues
}
// see if we have a session
if (isset($session)) {
	// graph api request for user data
	$request = new FacebookRequest($session, 'GET', '/me');
	$response = $request->execute();
	// get response
	$graphObject = $response->getGraphObject();
	$fbid = $graphObject->getProperty('id'); // To Get Facebook ID
	$fbfullname = $graphObject->getProperty('name'); // To Get Facebook full name
	$femail = $graphObject->getProperty('email'); // To Get Facebook email ID

	facebookLogin::credentials($fbid, $femail, $fbfullname, $_SESSION['redirect']);
} else {
	$loginUrl = $helper->getLoginUrl();
	header("Location: " . $loginUrl);
}
?>