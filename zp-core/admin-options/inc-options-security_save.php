<?php

$protocol = sanitize($_POST['server_protocol'], 3);
if ($protocol != SERVER_PROTOCOL) {
	// force https if required to be sure it works, otherwise the "save" will be the last thing we do
	httpsRedirect();
}
if (getOption('server_protocol') != $protocol) {
	setOption('server_protocol', $protocol);
	$_zp_mutex->lock();
	$zp_cfg = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
	$zp_cfg = config::updateConfigItem('server_protocol', $protocol, $zp_cfg);
	config::storeConfig($zp_cfg);
	$_zp_mutex->unlock();
}

$_zp_gallery->setUserLogonField(isset($_POST['login_user_field']));
if ($protocol == 'http') {
	zp_clearCookie("zpcms_ssl");
}
setOption('IP_tied_cookies', (int) isset($_POST['IP_tied_cookies']));

$_zp_gallery->save();
setOption('anonymize_ip', sanitize_numeric($_POST['anonymize_ip']));
setOption('dataprivacy_policy_notice', process_language_string_save('dataprivacy_policy_notice', 3));
setOption('dataprivacy_policy_custompage', sanitize($_POST['dataprivacy_policy_custompage']));
if (extensionEnabled('zenpage') && ZP_PAGES_ENABLED) {
	setOption('dataprivacy_policy_zenpage', sanitize($_POST['dataprivacy_policy_zenpage']));
}
setOption('dataprivacy_policy_customlinktext', process_language_string_save('dataprivacy_policy_customlinktext', 3));
$returntab = "&tab=security";
