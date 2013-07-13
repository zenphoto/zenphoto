<?php
/**
 * Support for allowing visitors to register to access your site. Users registering
 * are verified via an e-mail to insure the validity of the e-mail address they provide.
 * Options are provided for setting the required registration details and the default
 * user rights that will be granted.
 *
 * Place a call on printRegistrationForm() where you want the form to appear.
 * Probably the best use is to create a new <i>custom page</i> script just for handling these
 * user registrations. Then put a link to that script on your index page so that people
 * who wish to register will click on the link and be taken to the registration page.
 *
 * When successfully registered, a new Zenphoto user will be created with no logon rights. An e-mail
 * will be sent to the user with a link to activate the user ID. When he clicks on that link
 * he will be taken to the registration page and the verification process will be completed.
 * At this point the user ID rights are set to the value of the plugin default user rights option
 * and an email is sent to the Gallery admin announcing the new registration.
 *
 * <b>NOTE:</b> If you change the rights of a user pending verification you have verified the user!
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage users
 */
$plugin_is_filter = 5 | FEATURE_PLUGIN;
$plugin_description = gettext("Provides a means for placing a user registration form on your theme pages.");
$plugin_author = "Stephen Billard (sbillard)";


$option_interface = 'register_user_options';


if (!OFFSET_PATH) {
	if (!$page = getOption('register_user_page_page')) {
		$page = 'register';
	}
	$_zp_conf_vars['special_pages'][$page] = array('define'	 => false, 'rewrite'	 => getOption('register_user_rewrite'), 'rule'		 => '^%REWRITE%/*$		index.php?p=' . $page . ' [L,QSA]');
}

if (getOption('register_user_address_info')) {
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/comment_form/functions.php');
}

/**
 * Plugin option handling class
 *
 */
class register_user_options {

	function __construct() {
		global $_zp_authority;
		$old = getOption('register_user_page_page');
		if (!$old || is_numeric($old) || $old == 'register') {
			purgeOption('register_user_page_page');
		} else {
			setOptionDefault('register_user_rewrite', "_PAGE_/$old");
		}
		setOptionDefault('register_user_rewrite', '_PAGE_/register');
		gettext($str = 'You have received this email because you registered with the user id %3$s on this site.' . "\n" . 'To complete your registration visit %1$s.');
		setOptionDefault('register_user_text', getAllTranslations($str));
		gettext($str = 'Click here to register for this site.');
		setOptionDefault('register_user_page_tip', getAllTranslations($str));
		gettext($str = 'Register');
		setOptionDefault('register_user_page_link', getAllTranslations($str));
		setOptionDefault('register_user_captcha', 0);
		setOptionDefault('register_user_email_is_id', 1);
		setOptionDefault('register_user_create_album', 0);
		$mailinglist = $_zp_authority->getAdminEmail(ADMIN_RIGHTS);
		if (count($mailinglist) == 0) { //	no one to send the notice to!
			setOption('register_user_notify', 0);
		} else {
			setOptionDefault('register_user_notify', 1);
		}
	}

	function getOptionsSupported() {
		global $_zp_authority, $_common_notify_handler, $_zp_captcha;
		$options = array(
						gettext('Registration page link')	 => array('key'		 => 'register_user_rewrite', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 0,
										'desc'	 => gettext('The link to use for the registration page. Note: the token <code>_PAGE_</code> stands in for the current <em>page</em> definition.')),
						gettext('Link text')							 => array('key'		 => 'register_user_page_link', 'type'	 => OPTION_TYPE_TEXTAREA,
										'order'	 => 2,
										'desc'	 => gettext('If this option is set, the visitor login form will include a link to this page. The link text will be labeled with the text provided.')),
						gettext('Hint text')							 => array('key'		 => 'register_user_page_tip', 'type'	 => OPTION_TYPE_TEXTAREA,
										'order'	 => 2.5,
										'desc'	 => gettext('If this option is set, the visitor login form will include a link to this page. The link text will be labeled with the text provided.')),
						gettext('Notify*')								 => array('key'		 => 'register_user_notify', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 4,
										'desc'	 => gettext('If checked, an e-mail will be sent to the gallery admin when a new user has verified his registration.')),
						gettext('Address fields')					 => array('key'			 => 'register_user_address_info', 'type'		 => OPTION_TYPE_RADIO,
										'order'		 => 4.5,
										'buttons'	 => array(gettext('Omit')		 => 0, gettext('Show')		 => 1, gettext('Require') => 'required'),
										'desc'		 => gettext('If <em>Address fields</em> are shown or required, the form will include positions for address information. If required, the user must supply data in each address field.') . '<p class="notebox">' . gettext('<strong>Note:</strong> Address fields are handled by the <em>comment_form</em> plugin.') . '</p>'),
						gettext('User album')							 => array('key'		 => 'register_user_create_album', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 6,
										'desc'	 => gettext('If checked, an album will be created and assigned to the user.')),
						gettext('Email ID')								 => array('key'		 => 'register_user_email_is_id', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 4,
										'desc'	 => gettext('If checked, The user\'s e-mail address will be used as his User ID.')),
						gettext('Email notification text') => array('key'		 => 'register_user_text', 'type'	 => OPTION_TYPE_TEXTAREA,
										'order'	 => 3,
										'desc'	 => gettext('Text for the body of the email sent to the registrant for registration verification. <p class="notebox"><strong>Note:</strong> You must include <code>%1$s</code> in your message where you wish the <em>registration verification</em> link to appear. You may also insert the registrant\'s <em>name</em> (<code>%2$s</code>), <em>user id</em> (<code>%3$s</code>), and <em>password</em>* (<code>%4$s</code>).<br /><br />*For security reasons we recommend <strong>not</strong> inserting the <em>password</em>.</p>')),
						gettext('CAPTCHA')								 => array('key'		 => 'register_user_captcha', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 5,
										'desc'	 => ($_zp_captcha->name) ? gettext('If checked, the form will include a Captcha verification.') : '<span class="notebox">' . gettext('No captcha handler is enabled.') . '</span>'),
		);
		if (getOption('register_user_page_page')) {
			$options[gettext('Standard script naming')] = array('key'		 => 'register_user_page_page', 'type'	 => OPTION_TYPE_CHECKBOX,
							'order'	 => 0,
							'desc'	 => '<p class="notebox">' . gettext('<strong>Note:</strong> The <em>register user</em> theme script should be named <em>register.php</em>. Check this box to use the standard script name.') . '</p>');
		}
		if ($_common_notify_handler) {
			$options['note'] = array('key'		 => 'menu_truncate_note', 'type'	 => OPTION_TYPE_NOTE,
							'order'	 => 8,
							'desc'	 => '<p class="notebox">' . $_common_notify_handler . '</p>');
		} else {
			$_common_notify_handler = gettext('* The option may be set via the <a href="javascript:gotoName(\'register_user\');"><em>register_user</em></a> plugin options.');
			$options['note'] = array('key'		 => 'menu_truncate_note',
							'type'	 => OPTION_TYPE_NOTE,
							'order'	 => 8,
							'desc'	 => gettext('<p class="notebox">*<strong>Note:</strong> The setting of this option is shared with other plugins.</p>'));
		}
		$mailinglist = $_zp_authority->getAdminEmail(ADMIN_RIGHTS);
		if (count($mailinglist) == 0) { //	no one to send the notice to!
			$options[gettext('Notify*')]['disabled'] = true;
			$options[gettext('Notify*')]['desc'] .= ' ' . gettext('Of course there must be some Administrator with an e-mail address for this option to make sense!');
		}
		if (class_exists('user_groups')) {
			$admins = $_zp_authority->getAdministrators('groups');
			$defaultrights = ALL_RIGHTS;
			$ordered = array();
			foreach ($admins as $key => $admin) {
				$ordered[$admin['user']] = $admin['user'];
				if ($admin['rights'] < $defaultrights && $admin['rights'] >= NO_RIGHTS) {
					$nullselection = $admin['user'];
					$defaultrights = $admin['rights'];
				}
			}
			if (!empty($nullselection)) {
				if (is_numeric(getOption('register_user_user_rights'))) {
					setOption('register_user_user_rights', $nullselection);
				} else {
					setOptionDefault('register_user_user_rights', $nullselection);
				}
			}
			$options[gettext('Default user group')] = array('key'				 => 'register_user_user_rights', 'type'			 => OPTION_TYPE_SELECTOR,
							'order'			 => 1,
							'selections' => $ordered,
							'desc'			 => gettext("Initial group assignment for the new user."));
		} else {
			if (is_numeric(getOption('register_user_user_rights'))) {
				setOptionDefault('register_user_user_rights', NO_RIGHTS);
			} else {
				setOption('register_user_user_rights', NO_RIGHTS);
			}
			$options[gettext('Default rights')] = array('key'		 => 'register_user_user_rights', 'type'	 => OPTION_TYPE_CUSTOM,
							'order'	 => 2,
							'desc'	 => gettext("Initial rights for the new user. (If no rights are set, approval of the user will be required.)"));
		}
		return $options;
	}

	function handleOption($option, $currentValue) {
		global $_zp_gallery;
		switch ($option) {
			case 'register_user_user_rights':
				printAdminRightsTable('register_user', '', '', getOption('register_user_user_rights'));
				break;
		}
	}

	static function handleOptionSave($themename, $themealbum) {
		if (!class_exists('user_groups')) {
			$saved_rights = NO_RIGHTS;
			$rightslist = sortMultiArray(Zenphoto_Authority::getRights(), array('set', 'value'));
			foreach ($rightslist as $rightselement => $right) {
				if (isset($_POST['register_user-' . $rightselement])) {
					$saved_rights = $saved_rights | $_POST['register_user-' . $rightselement];
				}
			}
			setOption('register_user_user_rights', $saved_rights);
		}
		return false;
	}

}

/**
 * Parses the verification and registration if they have occurred
 * places the user registration form
 *
 * @param string $thanks the message shown on successful registration
 */
function printRegistrationForm($thanks = NULL) {
	global $notify, $admin_e, $admin_n, $user, $_zp_authority, $_zp_captcha, $_zp_gallery_page, $_zp_gallery;
	require_once(dirname(dirname(__FILE__)) . '/admin-functions.php');
	$userobj = NULL;
	// handle any postings
	if (isset($_GET['verify'])) {
		$currentadmins = $_zp_authority->getAdministrators();
		$params = unserialize(pack("H*", trim(sanitize($_GET['verify']), '.')));
		// expung the verify query string as it will cause us to come back here if login fails.
		unset($_GET['verify']);
		$link = explode('?', getRequestURI());
		if (isset($link[1])) {
			$p = explode('&', $link[1]);
			foreach ($p as $k => $v) {
				if (strpos($v, 'verify=') === 0) {
					unset($p[$k]);
				}
			}
			unset($p['verify']);
			$_SERVER['REQUEST_URI'] = $link[0] . '?' . implode('&', $p);
		} else {
			$_SERVER['REQUEST_URI'] = $link[0];
		}

		$userobj = Zenphoto_Authority::getAnAdmin(array('`user`='	 => $params['user'], '`valid`=' => 1));
		if ($userobj->getEmail() == $params['email']) {
			if (!$userobj->getRights()) {
				$userobj->setCredentials(array('registered', 'user', 'email'));
				$rights = getOption('register_user_user_rights');
				$group = NULL;
				if (!is_numeric($rights)) { //  a group or template
					$admin = Zenphoto_Authority::getAnAdmin(array('`user`='	 => $rights, '`valid`=' => 0));
					if ($admin) {
						$userobj->setObjects($admin->getObjects());
						if ($admin->getName() != 'template') {
							$group = $rights;
						}
						$rights = $admin->getRights();
					} else {
						$rights = NO_RIGHTS;
					}
				}
				$userobj->setRights($rights | NO_RIGHTS);
				$userobj->setGroup($group);
				zp_apply_filter('register_user_verified', $userobj);
				$notify = false;
				if (getOption('register_user_notify')) {
					$notify = zp_mail(gettext('Zenphoto Gallery registration'), sprintf(gettext('%1$s (%2$s) has registered for the zenphoto gallery providing an e-mail address of %3$s.'), $userobj->getName(), $userobj->getUser(), $userobj->getEmail()));
				}
				if (empty($notify)) {
					if (getOption('register_user_create_album')) {
						$userobj->createPrimealbum();
					}
					$notify = 'verified';
					$_POST['user'] = $userobj->getUser();
				}
				$userobj->save();
			} else {
				$notify = 'already_verified';
			}
		} else {
			$notify = 'not_verified'; // User ID no longer exists
		}
	}

	if (isset($_POST['register_user'])) {

		if (isset($_POST['username']) && !empty($_POST['username'])) {
			$notify = 'honeypot'; // honey pot check
		}
		if (getOption('register_user_captcha')) {
			if (isset($_POST['code'])) {
				$code = sanitize($_POST['code'], 3);
				$code_ok = sanitize($_POST['code_h'], 3);
			} else {
				$code = '';
				$code_ok = '';
			}
			if (!$_zp_captcha->checkCaptcha($code, $code_ok)) {
				$notify = 'invalidcaptcha';
			}
		}
		$admin_n = trim(sanitize($_POST['admin_name']));
		if (empty($admin_n)) {
			$notify = 'incomplete';
		}
		if (isset($_POST['admin_email'])) {
			$admin_e = trim(sanitize($_POST['admin_email']));
		} else {
			$admin_e = trim(sanitize($_POST['user']));
		}
		if (!is_valid_email_zp($admin_e)) {
			$notify = 'invalidemail';
		}

		$pass = trim(sanitize($_POST['pass']));
		$user = trim(sanitize($_POST['user']));
		if (empty($pass)) {
			$notify = 'empty';
		} else if (!empty($user) && !(empty($admin_n)) && !empty($admin_e)) {
			if (isset($_POST['disclose_password']) || $pass == trim(sanitize($_POST['pass_r']))) {
				$currentadmin = Zenphoto_Authority::getAnAdmin(array('`user`='	 => $user, '`valid`>' => 0));
				if (is_object($currentadmin)) {
					$notify = 'exists';
				}
				if (empty($notify)) {
					$userobj = Zenphoto_Authority::newAdministrator('');
					$userobj->transient = false;
					$userobj->setUser($user);
					$userobj->setPass($pass);
					$userobj->setName($admin_n);
					$userobj->setEmail($admin_e);
					$userobj->setRights(0);
					$userobj->setObjects(NULL);
					$userobj->setGroup('');
					$userobj->setCustomData('');
					$userobj->setLanguage(getUserLocale());
					zp_apply_filter('register_user_registered', $userobj);
					if ($userobj->transient) {
						if (empty($notify)) {
							$notify = 'filter';
						}
					} else {
						$userobj->save();
						$link = rewrite_path('/' . _PAGE_ . '/' . substr($_zp_gallery_page, 0, -4) . '?verify=' . bin2hex(serialize(array('user'	 => $user, 'email'	 => $admin_e))), '/index.php?p=' . substr($_zp_gallery_page, 0, -4) . '&verify=' . bin2hex(serialize(array('user'	 => $user, 'email'	 => $admin_e))), FULLWEBPATH);
						$message = sprintf(get_language_string(getOption('register_user_text')), $link, $admin_n, $user, $pass);
						$notify = zp_mail(get_language_string(gettext('Registration confirmation')), $message, array($user => $admin_e));
						if (empty($notify)) {
							$notify = 'accepted';
						}
					}
				}
			} else {
				$notify = 'mismatch';
			}
		} else {
			$notify = 'incomplete';
		}
	}

	if (isset($_GET['login'])) { //presumably the user failed to login....
		$notify = 'loginfailed';
	}

	if (zp_loggedin()) {
		if (isset($_GET['userlog']) && $_GET['userlog'] == 1) {
			echo '<meta http-equiv="refresh" content="1; url=' . WEBPATH . '/">';
		} else {
			echo '<div class="errorbox fade-message">';
			echo '<h2>' . gettext("you are already logged in.") . '</h2>';
			echo '</div>';
		}
		return;
	}
	if (!empty($notify)) {
		switch ($notify) {
			case'verified':
				if (is_null($thanks))
					$thanks = gettext("Thank you for registering.");
				?>
				<div class="Messagebox fade-message">
					<p><?php echo $thanks; ?></p>
					<p><?php echo gettext('You may now log onto the site and verify your personal information.'); ?></p>
				</div>
			<?php
			case 'already_verified':
			case 'loginfailed':
				$link = getRequestURI();
				if (strpos($link, '?') === false) {
					$_SERVER['REQUEST_URI'] = $link . '?login';
				} else {
					$_SERVER['REQUEST_URI'] = $link . '&login';
				}
				require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/user_login-out.php');
				printPasswordForm('', true, false, WEBPATH . '/' . ZENFOLDER . '/admin-users.php?page=users');
				$notify = 'success';
				break;
			case 'honeypot': //pretend it was accepted
			case 'accepted':
				?>
				<div class="Messagebox fade-message">
					<p><?php echo gettext('Your registration information has been accepted. An email has been sent to you to verify your email address.'); ?></p>
				</div>
				<?php
				if ($notify != 'honeypot')
					$notify = 'success'; // of course honeypot catches are no success!
				break;
			case 'exists':
				?>
				<div class="errorbox fade-message">
					<h2><?php echo gettext("Registration failed."); ?></h2>
					<p><?php printf(gettext('The user ID <em>%s</em> is already in use.'), $admin_e); ?></p>
				</div>
				<?php
				break;
			case 'empty':
				?>
				<div class="errorbox fade-message">
					<h2><?php echo gettext("Registration failed."); ?></h2>
					<p><?php echo gettext('Passwords may not be empty.'); ?></p>
				</div>
				<?php
				break;
			case 'mismatch':
				?>
				<div class="errorbox fade-message">
					<h2><?php echo gettext("Registration failed."); ?></h2>
					<p><?php echo gettext('Your passwords did not match.'); ?></p>
				</div>
				<?php
				break;
			case 'incomplete':
				?>
				<div class="errorbox fade-message">
					<h2><?php echo gettext("Registration failed."); ?></h2>
					<p><?php echo gettext('You have not filled in all the fields.'); ?></p>
				</div>
				<?php
				break;
			case 'notverified':
				?>
				<div class="errorbox fade-message">
					<h2><?php echo gettext("Registration failed."); ?></h2>
					<p><?php echo gettext('Invalid verification link.'); ?></p>
				</div>
				<?php
				break;
			case 'invalidemail':
				?>
				<div class="errorbox fade-message">
					<h2><?php echo gettext("Registration failed."); ?></h2>
					<p><?php echo gettext('Enter a valid email address.'); ?></p>
				</div>
				<?php
				break;
			case 'invalidcaptcha':
				?>
				<div class="errorbox fade-message">
					<h2><?php echo gettext("Registration failed."); ?></h2>
					<p><?php echo gettext('The CAPTCHA you entered was not correct.'); ?></p>
				</div>
				<?php
				break;
			case 'not_verified':
				?>
				<div class="errorbox fade-message">
					<h2><?php echo gettext("Verification failed."); ?></h2>
					<p><?php echo gettext('Your registration request could not be completed.'); ?></p>
				</div>
				<?php
				break;
			case 'filter':
				?>
				<div class="errorbox fade-message">
					<h2><?php echo gettext("Registration failed."); ?></h2>
					<p>
						<?php
						if (is_object($userobj) && !empty($userobj->msg)) {
							echo $userobj->msg;
						} else {
							echo gettext('Your registration attempt failed a <code>register_user_registered</code> filter check.');
						}
						?>
					</p>
				</div>
				<?php
				break;
			default:
				?>
				<div class="errorbox fade-message">
					<h2><?php echo gettext("Registration failed."); ?></h2>
					<p><?php echo $notify; ?></p>
				</div>
				<?php
				break;
		}
	}
	if ($notify != 'success') {
		$form = getPlugin('register_user/register_user_form.php', true);
		require_once($form);
	}
}
?>