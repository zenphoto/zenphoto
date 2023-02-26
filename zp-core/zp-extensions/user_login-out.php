<?php
/**
 * Provides users a means to log in or out from the theme pages.
 *
 * Place a call on <var>printUserLogin_out()</var> where you want the link or form to appear.
 *
 * @author Stephen Billard (sbillard)
 * @package zpcore\plugins\userloginout
 */
$plugin_is_filter = 900 | THEME_PLUGIN;
$plugin_description = gettext("Provides a means for users to login/out from your theme pages.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_category = gettext('Users');

$option_interface = 'user_logout_options';
if (isset($_zp_gallery_page) && getOption('user_logout_login_form') > 1) {
	setOption('colorbox_' . $_zp_gallery->getCurrentTheme() . '_' . stripSuffix($_zp_gallery_page), 1, false);
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/colorbox_js.php');
	if (!zp_has_filter('theme_head', 'colorbox::css')) {
		zp_register_filter('theme_head', 'colorbox::css');
	}
}

/**
 * Plugin option handling class
 *
 */
class user_logout_options {

	function __construct() {
		setOptionDefault('user_logout_login_form', 0);
	}

	function getOptionsSupported() {
		return array(gettext(
								'Login form') => array(
						'key' => 'user_logout_login_form',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => array(
								gettext('None') => 0,
								gettext('Form') => 1,
								gettext('Colorbox') => 2),
						'desc' => gettext('If the user is not logged-in display an <em>in-line</em> logon form or a link to a modal <em>Colorbox</em> form.'))
		);
	}

	function handleOption($option, $currentValue) {

	}

}


/**
 * Prints the logout link if the user is logged in.
 * This is for album passwords only, not admin users;
 *
 * @param string $before before text
 * @param string $after after text
 * @param int $showLoginForm to display a login form
 * 				to not display a login form, but just a login link, set to 0
 * 				to display a login form set to 1
 * 				to display a link to a login form in colorbox, set to 2, but you must have colorbox enabled for the theme pages where this link appears.)
 * @param string $logouttext optional replacement text for "Logout"
 */
function printUserLogin_out($before = '', $after = '', $showLoginForm = NULL, $logouttext = NULL) {
	global $_zp_gallery, $_zp_current_admin_obj, $_zp_login_error, $_zp_gallery_page;
	$excludedPages = array('password.php', 'register.php', 'favorites.php', '404.php');
	$logintext = gettext('Login');
	if (is_null($logouttext)) {
		$logouttext = gettext("Logout");
	}
	if (is_null($showLoginForm)) {
		$showLoginForm = getOption('user_logout_login_form');
	}
	if (is_object($_zp_current_admin_obj)) {
		if (!$_zp_current_admin_obj->logout_link) {
			return;
		}
	} 
	$cookies = Authority::getAuthCookies();
	if (empty($cookies) || !zp_loggedin()) {
		if (!in_array($_zp_gallery_page, $excludedPages)) {
			switch ($showLoginForm) {
				case 1:
					?>
					<div class="passwordform">
						<?php printPasswordForm('', true, false); ?>
					</div>
					<?php
					break;
				case 2:
					if ((getOption('colorbox_' . $_zp_gallery->getCurrentTheme() . '_' . stripSuffix($_zp_gallery_page))) && (zp_has_filter('theme_head', 'colorbox::css'))) { ?>
					<script>
						$(document).ready(function() {
							$(".logonlink").colorbox({
								inline: true,
								innerWidth: "400px",
								href: "#passwordform",
								close: '<?php echo gettext("close"); ?>',
								open: $('#passwordform_enclosure .errorbox').length
							});
						});
					</script>
					<?php if ($before) { echo '<span class="beforetext">' . html_encodeTagged($before) . '</span>'; } ?>
					<a href="#" class="logonlink" title="<?php echo $logintext; ?>"><?php echo $logintext; ?></a>
					<span id="passwordform_enclosure" style="display:none">
					<div class="passwordform">
						<?php printPasswordForm('', true, false); ?>
					</div>
					</span>
					<?php if ($after) { echo '<span class="aftertext">' . html_encodeTagged($after) . '</span>'; }
					}
					break;
				default:
					if ($loginlink = zp_apply_filter('login_link',getCustomPageURL('password'))) {
						if ($before) { echo '<span class="beforetext">' . html_encodeTagged($before) . '</span>'; } ?>
						<a href="<?php echo $loginlink; ?>" class="logonlink" title="<?php echo $logintext; ?>"><?php echo $logintext; ?></a>
						<?php if ($after) { echo '<span class="aftertext">' . html_encodeTagged($after) . '</span>'; }
					}
			}
		}
	} else {
		if ($before) { 
			echo '<span class="beforetext">' . html_encodeTagged($before) . '</span>';
		}
		$logoutlink = Authority::getLogoutURL('frontend');
		?>
		<a class="logoutlink" href="<?php echo $logoutlink; ?>" title="<?php echo $logouttext; ?>"><?php echo $logouttext; ?></a>
		<?php 
		if ($after) { 
			echo '<span class="aftertext">' . html_encodeTagged($after) . '</span>'; 
		}
	}
}