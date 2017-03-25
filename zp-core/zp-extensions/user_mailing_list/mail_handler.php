<?php

/*
 * Handles sending the mailing list e-mails
 */
// UTF-8 Ø
define('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/reconfigure.php');

admin_securityChecks(NULL, currentRelativeURL());

XSRFdefender('mailing_list');

//form handling stuff to add...
$subject = NULL;
$message = NULL;
if (isset($_POST['subject'])) {
	$subject = sanitize($_POST['subject']);
}
if (isset($_POST['message'])) {
	$message = sanitize($_POST['message']);
}
$toList = array();
$admins = $_zp_authority->getAdministrators();
$admincount = count($admins);
foreach ($admins as $admin) {
	if (isset($_POST["admin_" . $admin['id']])) {
		if ($admin['name']) {
			$toList[$admin['name']] = $admin['email'];
		} else {
			$toList[] = $admin['email'];
		}
	}
}
$currentadminmail = $_zp_current_admin_obj->getEmail();
if (!empty($currentadminmail)) {
	$name = $_zp_current_admin_obj->getName();
	if ($name) {
		$toList[$name] = $currentadminmail;
	} else {
		$toList[] = $currentadminmail;
	}
}

foreach ($toList as $name => $email) {
	$err_msg = zp_mail($subject, $message, array($name => $email), array(), array());
	if ($err_msg) {
		debugLogVar(gettext('user_mailing_list error'), $err_msg);
	}
	sleep(10); //	pace the mail send
}
?>