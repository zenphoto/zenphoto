<?php

/**
 * PHP sendmail mailing handler
 *
 * @author Stephen Billard (sbillard)
 * @package zpcore\plugins\zenphotosendmail
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Zenphoto outgoing mail handler based on the PHP <em>mail</em> facility.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (zp_has_filter('sendmail') && !extensionEnabled('zenphoto_sendmail')) ? sprintf(gettext('Only one Email handler plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'), stripSuffix(get_filterScript('sendmail'))) : '';
$plugin_category = gettext('Mail');
$plugin_deprecated = true;
if ($plugin_disable) {
	enableExtension('zenphoto_sendmail', 0);
} else {
	zp_register_filter('sendmail', 'zenphoto_sendmail');
}

/**
 * @deprecatd 2.0 - Use the phpMailer plugin instead 
 * @param type $msg
 * @param type $email_list
 * @param type $subject
 * @param type $message
 * @param type $from_mail
 * @param type $from_name
 * @param type $cc_addresses
 * @param type $replyTo
 * @return type
 */
function zenphoto_sendmail($msg, $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses, $replyTo) {
	deprecationNotice(gettext('Please use the PHPMailer plugin and its sendmail option instead'));
	$headers = sprintf('From: %1$s <%2$s>', $from_name, $from_mail) . "\n";
	if (count($cc_addresses) > 0) {
		$cclist = '';
		foreach ($cc_addresses as $cc_name => $cc_mail) {
			$cclist .= ',' . $cc_mail;
		}
		$headers .= 'Cc: ' . substr($cclist, 1) . "\n";
	}
	if ($replyTo) {
		$headers .= 'Reply-To: ' . array_shift($replyTo) . "\n";
	}
	$result = true;
	foreach ($email_list as $to_mail) {
		$result = $result && utf8::send_mail($to_mail, $subject, $message, $headers);
	}
	if (!$result) {
		if (!empty($msg))
			$msg .= '<br />';
		$msg .= sprintf(gettext('<code>zenphoto_sendmail</code> failed to send <em>%s</em> to one or more recipients.'), $subject);
	}
	return $msg;
}

?>