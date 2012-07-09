<?php
/**
 * Provides users a means to log in or out from the theme pages.
 *
 * Place a call on <var>printUserLogin_out()</var> where you want the link or form to appear.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage usermanagement
 */

$plugin_is_filter = 9999|THEME_PLUGIN;
$plugin_description = gettext("Provides a means for users to login/out from your theme pages.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'user_logout_options';
if (isset($_zp_gallery_page) && getOption('user_logout_login_form') > 1) {
	setOption('colorbox_'.$_zp_gallery->getCurrentTheme().'_'.stripSuffix($_zp_gallery_page), 1, false);
	require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/colorbox_js.php');
	if(!zp_has_filter('theme_head','colorbox::css')) {
		zp_register_filter('theme_head','colorbox::css');
	}
}

/**
 * Plugin option handling class
 *
 */
class user_logout_options {

	function user_logout_options() {
		setOptionDefault('user_logout_login_form', 0);
	}

	function getOptionsSupported() {
		return array(	gettext('Login form') => array('key' => 'user_logout_login_form', 'type' => OPTION_TYPE_RADIO,
												'buttons' => array(gettext('None')=>0, gettext('Form')=>1,gettext('Colorbox')=>2),
												'desc' => gettext('If the user is not logged-in display an <em>in-line</em> logon form or a link to a modal <em>Colorbox</em> form.'))
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
if (isset($_GET['title'])) { $__redirect['title'] = sanitize($_GET['title']); }
if (isset($_GET['page'])) { $__redirect['page'] = sanitize($_GET['page']); }

if (in_context(ZP_INDEX)) {
	if (isset($_GET['userlog'])) { // process the logout.
		if ($_GET['userlog'] == 0) {
			Zenphoto_Authority::handleLogout();
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
 * @param bool $showLoginForm to display a login form if no one is logged in set to 1 (for in colorbox, set to, but
 * 							but you must have colorbox enabled for the theme pages where this link appears.)
 * @param string $logouttext optional replacement text for "Logout"
 */
function printUserLogin_out($before='', $after='', $showLoginForm=NULL, $logouttext=NULL) {
	global $__redirect, $_zp_current_admin_obj,	$_zp_login_error;
	if (is_object($_zp_current_admin_obj)) {
		if ($_zp_current_admin_obj->no_zp_login)  {
			return;
		}
	}
	if (is_null($logouttext)) $logouttext = gettext("Logout");
	if (is_null($showLoginForm)) {
		$showLoginForm = getOption('user_logout_login_form');
	}
	$logintext = gettext('Login');
	$cookies = Zenphoto_Authority::getAuthCookies();
	if (empty($cookies)) {
		if ($showLoginForm) {
			if ($showLoginForm > 1) {
				if(zp_has_filter('theme_head','colorbox::css')) {
					?>
					<script type="text/javascript">
						// <!-- <![CDATA[
						$(document).ready(function(){
							$(".logonlink").colorbox({
								inline:true,
								innerWidth: "400px",
								href:"#passwordform",
								close: '<?php echo gettext("close"); ?>',
								open: $('#passwordform_enclosure .errorbox').length
							});
						});
						// ]]> -->
					</script>
					<?php
				}
				echo html_encode($before);
				?>
				<a href="#" class="logonlink" title="<?php echo $logintext; ?>"><?php echo $logintext; ?></a>
				<span id="passwordform_enclosure" style="display:none">
				<?php
			}
			?>
			<div class="passwordform">
				<?php printPasswordForm('', true, false); ?>
			</div>
			<?php
			if ($showLoginForm>1) {
				?>
				</span>
				<?php
				echo html_encode($after);
			}
		}
	} else {
		$params = array("'userlog=0'");
		if (!empty($__redirect)) {
			foreach ($__redirect as $param=>$value) {
				$params[] .= "'".$param.'='.urlencode($value)."'";
			}
		}
		echo "\n".html_encode($before);
		?>
		<a href="javascript:launchScript('<?php echo FULLWEBPATH.'/'; ?>',[<?php echo implode(',',$params); ?>]);" title="<?php echo $logouttext; ?>" ><?php echo $logouttext; ?></a>
		<?php
		echo html_encode($after)."\n";
	}
}

?>