<?php
/**
 * Sends new users an e-mail when their user code is created
 *
 * @package plugins
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Emails a password set request to a newly created user.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';

zp_register_filter('save_user', 'email_new_user_save');
zp_register_filter('edit_admin_custom_data', 'email_new_edit_admin', 0);

function email_new_user_save($savemsg, $userobj, $what) {
	global $_zp_authority;
	if ($what=='new' && ($mail = $userobj->getEmail())) {
		$gallery = new Gallery();
		$ref = $_zp_authority->getResetTicket($adm = $userobj->getUser(), $userobj->getPass());
		$msg = "\n".sprintf(gettext('You are receiving this e-mail because a user code (%1$s) has been created for you on the Zenphoto gallery %2$s.'),$adm,$gallery->getTitle()).
								"\n".sprintf(gettext('To set your Zenphoto User password visit: %s'),FULLWEBPATH."/".ZENFOLDER."/admin-users.php?ticket=$ref&user=$adm") .
								"\n".gettext("This ticket will automatically expire in 3 days.");
		$err_msg = zp_mail(gettext("The Zenphoto user created"), $msg, array($mail));
		if (!empty($err_msg)) {
			$savemsg .= $err_msg;
		}
	}
	return $savemsg;
}

function email_new_edit_admin($html, $userobj, $i, $background, $current) {
	$user = $userobj->getUser();
	if (empty($user)) {
	$result =
		'<tr'.((!$current)? ' style="display:none;"':'').' class="userextrainfo">
			<td colspan="3" '.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top"><p class="notebox">'.gettext('New users will be mailed a password set link').'</p></td>
		</tr>'."\n";
		$html = $result.$html;
	}
	return $html;
}
?>