<?php

if (!defined('OFFSET_PATH'))
	define('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-functions.php');


define('INSTAGRAM_CLIENT_ID', getOption('instagramClientID'));
define('INSTAGRAM_CLIENT_SECRET', getOption('instagramClientSecret'));
define('INSTAGRAM_REDIRECT_URI', FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/instagramLogin/instagram-auth.php');

zp_session_start();

if (isset($_REQUEST['redirect'])) {
	$_SESSION['redirect'] = filter_var($_REQUEST['redirect'], FILTER_SANITIZE_URL);
} else {
	if (!isset($_SESSION['redirect'])) {
		$_SESSION['redirect'] = FULLWEBPATH;
	}
}

header('Location: https://api.instagram.com/oauth/authorize/?client_id=' . INSTAGRAM_CLIENT_ID . '&redirect_uri=' . urlencode(INSTAGRAM_REDIRECT_URI) . '&response_type=code&scope=basic');
exitZP();
?>