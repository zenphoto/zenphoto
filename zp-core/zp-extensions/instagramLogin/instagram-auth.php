<?php

if (!defined('OFFSET_PATH'))
	define('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-functions.php');

define('INSTAGRAM_CLIENT_ID', getOption('instagramClientID'));
define('INSTAGRAM_CLIENT_SECRET', getOption('instagramClientSecret'));
define('INSTAGRAM_REDIRECT_URI', FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/instagramLogin/instagram-auth.php');

zp_session_start();

require_once('instagram-login-api.php');

// Instagram passes a parameter 'code' in the Redirect Url
if (isset($_GET['code'])) {
	try {
		$instagram_ob = new InstagramApi();

		// Get the access token
		$access_token = $instagram_ob->GetAccessToken(INSTAGRAM_CLIENT_ID, INSTAGRAM_REDIRECT_URI, INSTAGRAM_CLIENT_SECRET, $_GET['code']);

		// Get user information
		$user_info = $instagram_ob->GetUserProfileInfo($access_token);

		instagramLogin::credentials($user_info['username'], @$user_info['email'], $user_info['full_name'], $_SESSION['redirect']);
	} catch (Exception $e) {
		$error = $e->getMessage();
	}
} else {
	$code = gettext('Instagram did not provide a response code.');
}
session_unset();
header('Location: ' . WEBPATH . '/' . ZENFOLDER . '/admin.php?_zp_login_error=' . html_encode($error));
exitZP();
?>