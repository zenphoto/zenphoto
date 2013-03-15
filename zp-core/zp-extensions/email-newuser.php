<?php
/**
 * Sends new users an e-mail message urging the user to change his password.
 * It contains a link allowing him to do a password reset.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage users
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Emails a password reset request to a newly created user.");
$plugin_author = "Stephen Billard (sbillard)";


zp_register_filter('save_user', 'email_new_user::save');
zp_register_filter('edit_admin_custom_data', 'email_new_user::edit_admin', 9999);

class email_new_user {

	static function save($savemsg, $userobj, $what) {
		global $_zp_gallery;
		if ($what=='new' && ($mail = $userobj->getEmail())) {
			$ref = Zenphoto_Authority::getResetTicket($adm = $userobj->getUser(), $userobj->getPass());
			$msg = "\n".sprintf(gettext('You are receiving this e-mail because a user code (%1$s) has been created for you on the Zenphoto gallery %2$s.'),$adm,$_zp_gallery->getTitle()).
									"\n".sprintf(gettext('To set your Zenphoto User password visit: %s'),FULLWEBPATH."/".ZENFOLDER."/admin-users.php?ticket=$ref&user=$adm") .
									"\n".gettext("This ticket will automatically expire in 3 days.");
			$err_msg = zp_mail(gettext("The Zenphoto user created"), $msg, array($mail));
			if (!empty($err_msg)) {
				$savemsg .= $err_msg;
			}
		}
		return $savemsg;
	}

	static function edit_admin($html, $userobj, $i, $background, $current) {
		if ($userobj->getValid()) {
			$user = $userobj->getUser();
			if (empty($user)) {
				$result =
				'<tr'.((!$current)? ' style="display:none;"':'').' class="userextrainfo">
				<td colspan="2" '.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top"><p class="notebox">'.gettext('New users will be mailed a password set link').'</p></td>
				</tr>'."\n";
				$html = $result.$html;
			}
		}
		return $html;
	}

}
?>