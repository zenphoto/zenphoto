<?php
/**
 * PHP sendmail mailing handler
 * @package plugins
 */
$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext("Zenphoto outgoing mail handler based on the PHP <em>mail</em> facility.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';

zp_register_filter('sendmail', 'zenphoto_sendmail');

function zenphoto_sendmail($msg, $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses) {
	global $_zp_UTF8;
	$headers = sprintf('From: %1$s <%2$s>', $from_name, $from_mail)."\n";
	if (count($cc_addresses) > 0) {
		$cclist = '';
		foreach ($cc_addresses as $cc_name=>$cc_mail) {
			$cclist .= ','.$cc_mail;
		}
		$headers .= 'Cc: '.substr($cclist, 1)."\n";
	}
	$result = true;
	foreach ($email_list as $to_mail) {
		$result = $result && $_zp_UTF8->send_mail($to_mail, $subject, $message, $headers);
	}
	if (!$result) {
		if (!empty($msg)) $msg .= '<br />';
		$msg .= sprintf(gettext('<code>zenphoto_sendmail</code> failed to send <em>%s</em> to one or more recipients.'), $subject);
	}
	return $msg;
}

?>