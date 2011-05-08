<?php
/**
 * Pseudo mailing handler for localhost testing
 *
 * A "mail" file is created in the zp-data folder named by the subject
 *
 * @package plugins
 */
$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext("Pseudo mailing handler for localhost testing.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';

zp_register_filter('sendmail', 'pseudo_sendmail');

function pseudo_sendmail($msg, $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses) {
	global $_zp_UTF8;
	$filename = str_replace(array('<', '>', ':', '"'. '/'. '\\', '|', '?', '*'), '_', $subject);
	$path = dirname(dirname(__FILE__)) . '/' . DATA_FOLDER . '/'.$filename.'.txt';
	$f = fopen($path, 'w');
	fwrite($f,str_pad('*', 49, '-')."\n");
	$tolist = '';
	foreach ($email_list as $to) {
		$tolist .= ','.$to;
	}
	fwrite($f, sprintf(gettext('To: %s'),substr($tolist, 1)) . "\n");
	fwrite($f, sprintf('From: %1$s <%2$s>', $from_name, $from_mail) . "\n");
	if (count($cc_addresses) > 0) {
		$cclist = '';
		foreach ($cc_addresses as $cc_name=>$cc_mail) {
			$cclist .= ','.$cc_mail;
		}
		fwrite($f, sprintf(gettext('Cc: %s'),substr($cclist, 1)) . "\n");
	}
	fwrite($f, sprintf(gettext('Subject: %s'),$subject)."\n");
	fwrite($f,str_pad('*', 49, '-')."\n");
	fwrite($f, $message . "\n");
	fclose($f);
	clearstatcache();
	return $msg;
}

?>