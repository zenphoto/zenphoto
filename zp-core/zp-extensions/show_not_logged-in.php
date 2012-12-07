<?php
/**
 * When enabled, Zenphoto users will be appear not to be logged-in when viewing gallery pages
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage development
 */
$plugin_is_filter = 95|THEME_PLUGIN;
$plugin_description = sprintf(gettext("Treats users as not logged in for gallery pages."),DATA_FOLDER);
$plugin_author = "Stephen Billard (sbillard)";


zp_register_filter('guest_login_attempt', 'show_not_loggedin::adminLoginAttempt');
zp_register_filter('load_theme_script', 'show_not_loggedin::hideAdmin');

class show_not_loggedin {

	static function hideAdmin($param) {
		global $_zp_loggedin, $_zp_current_admin_obj, $_showNotLoggedin_real_auth;
		if (!OFFSET_PATH && is_object($_zp_current_admin_obj)) {
			$_showNotLoggedin_real_auth = $_zp_current_admin_obj;
			if (isset($_SESSION)) {
				unset($_SESSION['zp_user_auth']);
			}
			if (isset($_COOKIE)) {
				unset($_COOKIE['zp_user_auth']);
			}
			$_zp_current_admin_obj = $_zp_loggedin = NULL;
		}
		return $param;
	}

	static function adminLoginAttempt($success, $user, $pass, $athority) {
		if ($athority == 'zp_admin_auth' && $success) {
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
			exitZP();
		}
		return $success;
	}

}
?>