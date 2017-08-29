<?php
/**
 * Provides users a means to log in or out from the theme pages.
 *
 * Place a call on <var>printUserLogin_out()</var> where you want the link or form to appear.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage users
 */
$plugin_is_filter = 900 | THEME_PLUGIN;
$plugin_description = gettext("Provides a means for users to login/out from your theme pages.");
$plugin_author = "Stephen Billard (sbillard)";

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
		if (OFFSET_PATH == 2) {
			setOptionDefault('user_logout_login_form', 0);
		}
	}

	function getOptionsSupported() {
		return array(gettext('Login form') => array('key' => 'user_logout_login_form', 'type' => OPTION_TYPE_RADIO,
						'buttons' => array(gettext('None') => 0, gettext('Form') => 1, gettext('Colorbox') => 2),
						'desc' => gettext('If the user is not logged-in display an <em>in-line</em> logon form or a link to a modal <em>Colorbox</em> form.'))
		);
	}

	function handleOption($option, $currentValue) {

	}

}

if (in_context(ZP_INDEX)) {
	if (isset($_GET['userlog'])) { // process the logout.
		if ($_GET['userlog'] == 0) {
			$__redirect = array();
			if (in_context(ZP_ALBUM)) {
				$__redirect['album'] = $_zp_current_album->name;
			}
			if (in_context(ZP_IMAGE)) {
				$__redirect['image'] = $_zp_current_image->filename;
			}
			if (in_context(ZP_ZENPAGE_PAGE)) {
				$__redirect['title'] = $_zp_current_page->getTitlelink();
			}
			if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
				$__redirect['title'] = $_zp_current_article->getTitlelink();
			}
			if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
				$__redirect['category'] = $_zp_current_category->getTitlelink();
			}
			if (isset($_GET['p'])) {
				$__redirect['p'] = sanitize($_GET['p']);
			}
			if (isset($_GET['searchfields'])) {
				$__redirect['searchfields'] = sanitize($_GET['searchfields']);
			}
			if (isset($_GET['words'])) {
				$__redirect['words'] = sanitize($_GET['words']);
			}
			if (isset($_GET['date'])) {
				$__redirect['date'] = sanitize($_GET['date']);
			}
			if (isset($_GET['title'])) {
				$__redirect['title'] = sanitize($_GET['title']);
			}
			if (isset($_GET['page'])) {
				$__redirect['page'] = sanitize($_GET['page']);
			}

			$params = '';
			if (!empty($__redirect)) {
				foreach ($__redirect as $param => $value) {
					$params .= '&' . $param . '=' . $value;
				}
			}
			$location = Zenphoto_Authority::handleLogout(FULLWEBPATH . '/index.php?fromlogout' . $params);
			header("Location: " . $location);
			exitZP();
		}
	}
}

/**
 * Prints the logout link if the user is logged in.
 * This is for album passwords only, not admin users;
 *
 * @param string $before before text
 * @param string $after after text
 * @param int $showLoginForm to display a login form
 * 				0: to not display a login form, but just a login link
 * 				1: to display a login form
 * 				2: to display a link to a login form which will display in colorbox if the colorbox_js plugin is enabled.
 * @param string $logouttext optional replacement text for "Logout"
 */
function printUserLogin_out($before = '', $after = '', $showLoginForm = NULL, $logouttext = NULL) {
	global $_zp_gallery, $__redirect, $_zp_current_admin_obj, $_zp_login_error, $_zp_gallery_page;
	$excludedPages = array('password.php', 'register.php', 'favorites.php', '404.php');
	$logintext = gettext('Login');
	if (is_null($logouttext))
		$logouttext = gettext("Logout");
	$params = array("userlog=0");
	if (!empty($__redirect)) {
		foreach ($__redirect as $param => $value) {
			$params[] .= $param . '=' . urlencode($value);
		}
	}
	if (is_null($showLoginForm)) {
		$showLoginForm = getOption('user_logout_login_form');
	}

	if (is_object($_zp_current_admin_obj)) {
		if (!$_zp_current_admin_obj->logout_link) {
			return;
		}
	}
	$cookies = Zenphoto_Authority::getAuthCookies();
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
					if (extensionEnabled('colorbox_js')) {
						if (!zp_has_filter('theme_head', 'colorbox::css')) {
							colorbox::css();
						}
						?>
						<script type="text/javascript">
							// <!-- <![CDATA[
							window.addEventListener('load', function () {
								$(".logonlink").colorbox({
									inline: true,
									innerWidth: "400px",
									href: "#passwordform",
									close: '<?php echo gettext("close"); ?>',
									open: $('#passwordform_enclosure .errorbox').length
								});
							}, false);
							// ]]> -->
						</script>
						<?php
						if ($before) {
							echo '<span class="beforetext">' . html_encodeTagged($before) . '</span>';
						}
						?>
						<a href="#" class="logonlink" title="<?php echo $logintext; ?>"><?php echo $logintext; ?></a>
						<span id="passwordform_enclosure" style="display:none">
							<div class="passwordform">
								<?php printPasswordForm('', true, false); ?>
							</div>
						</span>
						<?php
						if ($after) {
							echo '<span class="aftertext">' . html_encodeTagged($after) . '</span>';
						}
						break;
					}
				default:
					if ($loginlink = zp_apply_filter('login_link', PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . WEBPATH . "/" . ZENFOLDER . '/admin.php')) {
						if ($before) {
							echo '<span class="beforetext">' . html_encodeTagged($before) . '</span>';
						}
						?>
						<a href="<?php echo $loginlink; ?>" class="logonlink" title="<?php echo $logintext; ?>">
							<?php echo $logintext; ?>
						</a>
						<?php
						if ($after) {
							echo '<span class="aftertext">' . html_encodeTagged($after) . '</span>';
						}
					}
			}
		}
	} else {
		if ($before) {
			echo '<span class="beforetext">' . html_encodeTagged($before) . '</span>';
		}
		$logoutlink = FULLWEBPATH . '?' . implode('&', $params);
		?>
		<a href="<?php echo html_encode($logoutlink); ?>" title="<?php echo $logouttext; ?>">
			<?php echo $logouttext; ?>
		</a>
		<?php
		if ($after) {
			echo '<span class="aftertext">' . html_encodeTagged($after) . '</span>';
		}
	}
}
?>