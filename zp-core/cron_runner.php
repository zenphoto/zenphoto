<?php
/**
 *
 * Zenphoto cron task handler
 */
if (isset($_GET['offsetPath'])) {
	define('OFFSET_PATH', (int) $_GET['offsetPath']);
} else {
	define('OFFSET_PATH', 1);
}

require_once(dirname(__FILE__).'/functions.php');

$_zp_current_admin_obj = $_zp_loggedin = NULL;
$link = sanitize($_POST['link']);
if (isset($_POST['auth'])) {
	$auth = sanitize($_POST['auth']);
	$admin = Zenphoto_Authority::getAnAdmin(array('`user`=' => $_zp_authority->master_user, '`valid`=' => 1));
	if (sha1($link.serialize($admin)) == $auth && $admin->getRights()) {
		$_zp_current_admin_obj = $admin;
		$_zp_loggedin = $admin->getRights();
	}
}
require_once('admin-globals.php');
require_once('admin-functions.php');


admin_securityChecks(NULL, currentRelativeURL());
zp_apply_filter('security_misc', true, 'cron_runner', 'zp_admin_auth', sprintf('executing %1$s', $link));

if (isset($_POST['XSRFTag'])) {
	$_REQUEST['XSRFToken'] = $_POST['XSRFToken'] = $_GET['XSRFToken'] = getXSRFToken(sanitize($_POST['XSRFTag']));
} else {
	unset($_POST['XSRFToken']);
	unset($_GET['XSRFToken']);
	unset($_REQUEST['XSRFToken']);
}
require_once($link);

?>