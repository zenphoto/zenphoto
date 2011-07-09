<?php
/**
 * Provides users then means to  log in or out from the theme pages.
 *
 * Place a call on printUserLogin_out() where you want the logout link to appear.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage usermanagement
 */

$plugin_description = gettext("Provides a means for placing a user login form or logout link on your theme pages.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';
$option_interface = 'user_logout_options';

/**
 * Plugin option handling class
 *
 */
class user_logout_options {

	function user_logout_options() {
		setOptionDefault('user_logout_login_form', 0);
	}

	function getOptionsSupported() {
		return array(	gettext('Enable login form') => array('key' => 'user_logout_login_form', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext('If enabled, a login form will be displayed if the viewer is not logged in.'))
		);
	}
	function handleOption($option, $currentValue) {
	}
}


$__redirect = array();
if (in_context(ZP_ALBUM)) {
	$__redirect['album'] = $_zp_current_album->name;
}
if (in_context(ZP_IMAGE)) {
	 $__redirect['image'] = $_zp_current_image->filename;
}
if (in_context('ZP_ZENPAGE_PAGE')) {
	$__redirect['title'] = $_zp_current_zenpage_page->getTitlelink();
}
if (in_context('ZP_ZENPAGE_NEWS_ARTICLE')) {
	$__redirect['title'] = $_zp_current_zenpage_news->getTitlelink();
}
if (in_context('ZP_ZENPAGE_NEWS_CATEGORY')) {
	$__redirect['category'] = $_zp_current_category->getTitlelink();
}
if (isset($_GET['p'])) { $__redirect['p'] = sanitize($_GET['p']); }
if (isset($_GET['searchfields'])) { $__redirect['searchfields'] = sanitize($_GET['searchfields']); }
if (isset($_GET['words'])) { $__redirect['words'] = sanitize($_GET['words']); }
if (isset($_GET['date'])) { $__redirect['date'] = sanitize($_GET['date']); }
if (isset($_GET['title'])) { $__redirect['title'] = "title=" . sanitize($_GET['title']); }
if (isset($_GET['page'])) { $__redirect['page'] = sanitize($_GET['page']); }

if (in_context(ZP_INDEX)) {
	if (isset($_GET['userlog'])) { // process the logout.
		if ($_GET['userlog'] == 0) {
			$_zp_authority->handleLogout();
			if (empty($__redirect)) {
				$params = '';
			} else {
				$params = '?';
				foreach ($__redirect as $param=>$value) {
					$params .= $param.'='.$value.'&';
				}
				$params = substr($params,0,-1);
			}
			header("Location: " . FULLWEBPATH . '/index.php'.$params);
			exit();
		}
	}
}

/**
 * Prints the logout link if the user is logged in.
 * This is for album passwords only, not admin users;
 *
 * @param string $before before text
 * @param string $after after text
 * @param bool $showLoginForm set to true to display a login form if no one is logged in
 * @param string $logouttext optional replacement text for "Logout"
 */
function printUserLogin_out($before='', $after='', $showLoginForm=NULL, $logouttext=NULL) {
	global $__redirect;
	$cookies = array();
	$candidate = array();
	if (isset($_COOKIE)) {
		$candidate = $_COOKIE;
	}
	if (isset($_SESSION)) {
		$candidate = array_merge($candidate, $_SESSION);
	}

	$candidate = array_unique($candidate);
	foreach ($candidate as $cookie=>$value) {
		if ($cookie == 'zenphoto_auth' || $cookie == 'zp_gallery_auth' ||
				$cookie == 'zp_search_auth' || $cookie == 'zp_image_auth' ||
				strpos($cookie, 'zp_album_auth_') !== false ||
				strpos($cookie, 'zp_page_auth_') !== false ||
				strpos($cookie, 'zp_category_auth_') !== false) {
			$cookies[] = $cookie;
		}
	}
	if (is_null($logouttext)) $logouttext = gettext("Logout");
	if (is_null($showLoginForm) && getOption('user_logout_login_form')) {
		$showLoginForm = true;
	}
	if (empty($cookies)) {
		if ($showLoginForm) {
			?>
			<div class="passwordform">
				<?php printPasswordForm('', false); ?>
			</div>
			<?php
		}
	} else {
		$params = array("'userlog=0'");
		if (!empty($__redirect)) {
			foreach ($__redirect as $param=>$value) {
				$params[] .= "'".$param.'='.urlencode($value)."'";
			}
		}
		echo "\n".$before;
		?>
		<a href="javascript:launchScript('<?php echo FULLWEBPATH.'/'; ?>',[<?php echo implode(',',$params); ?>]);" title="<?php echo $logouttext; ?>" ><?php echo $logouttext; ?></a>
		<?php
		echo $after."\n";
	}
}

?>