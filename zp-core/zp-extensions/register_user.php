<?php
/**
 * Provides a means where visitors can register and get limited site privileges.
 *
 * Place a call on printRegistrationForm() where you want the form to appear.
 * Probably the best use is to create a new 'custom page' script just for handling these
 * user registrations. Then put a link to that script on your index page so that people
 * who wish to register will click on the link and be taken to the registration page.
 *
 * When successfully registered, a new admin user will be created with no logon rights. An e-mail
 * will be sent to the user with a link to activate the user ID. When he clicks on that link
 * he will be taken to the registration page and the verification process will be completed.
 * At this point the user ID rights is set to the value of the plugin default user rights option
 * and an email is sent to the Gallery admin announcing the new registration.
 *
 * NOTE: If you change the rights on a user pending verification you have verified the user.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage usermanagement
 */
$plugin_is_filter = 5|ADMIN_PLUGIN|THEME_PLUGIN;
$plugin_description = gettext("Provides a means for placing a user registration form on your theme pages.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';

$option_interface = 'register_user_options';

zp_register_filter('custom_option_save','register_user_handleOptionSave');

if (getOption('register_user_address_info')) {
	require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/comment_form.php');
}

/**
 * Plugin option handling class
 *
 */
class register_user_options {

	function register_user_options() {
		global $_zp_authority;
		gettext($str = 'You have received this email because you registered on the site. To complete your registration visit %s.');
		setOptionDefault('register_user_text', getAllTranslations($str));
		gettext($str = 'Click here to register for this site.');
		setOptionDefault('register_user_page_tip', getAllTranslations($str));
		gettext($str = 'Register');
		setOptionDefault('register_user_page_link', getAllTranslations($str));
		setOptionDefault('register_user_captcha', 0);
		setOptionDefault('register_user_email_is_id', 1);
		setOptionDefault('register_user_page_page', 'register');
		setOptionDefault('register_user_create_album', 0);
		$mailinglist = $_zp_authority->getAdminEmail(ADMIN_RIGHTS);
		if (count($mailinglist) == 0) {	//	no one to send the notice to!
			setOption('register_user_notify',0);
		} else {
			setOptionDefault('register_user_notify', 1);
		}
	}

	function getOptionsSupported() {
		global $_zp_authority;
		$options = array(	gettext('Notify') => array('key' => 'register_user_notify', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 4,
												'desc' => gettext('If checked, an e-mail will be sent to the gallery admin when a new user has verified his registration.')),
											gettext('Address fields') => array('key' => 'register_user_address_info', 'type' => OPTION_TYPE_RADIO,
												'order' => 4.5,
												'buttons' => array(gettext('Omit')=>0, gettext('Show')=>1, gettext('Require')=>'required'),
												'desc' => gettext('If <em>Address fields</em> are shown or required, the form will include positions for address information. If required, the user must supply data in each address field.')),
											gettext('User album') => array('key' => 'register_user_create_album', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 6,
												'desc' => gettext('If checked, an album will be created and assigned to the user.')),
											gettext('Email ID') => array('key' => 'register_user_email_is_id', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 4,
												'desc' => gettext('If checked, The user\'s e-mail address will be used as his User ID.')),
											gettext('Email notification text') => array('key' => 'register_user_text', 'type' => OPTION_TYPE_TEXTAREA,
												'order' => 3,
												'desc' => gettext('Text for the body of the email sent to the user. <p class="notebox"><strong>Note:</strong> You must include <code>%s</code> in your message where you wish the registration completion link to appear.</p>')),
											gettext('User registration page') => array('key' => 'register_user_page', 'type' => OPTION_TYPE_CUSTOM,
												'order' => 0,
												'desc' => gettext('If this option is set, the visitor login form will include a link to this page. The link text will be labeled with the text provided.')),
											gettext('CAPTCHA') => array('key' => 'register_user_captcha', 'type' => OPTION_TYPE_CHECKBOX,
												'order' => 5,
												'desc' => gettext('If checked, CAPTCHA validation will be required for user registration.'))
											);
		$mailinglist = $_zp_authority->getAdminEmail(ADMIN_RIGHTS);
		if (count($mailinglist) == 0) {	//	no one to send the notice to!
			$options[gettext('Notify')]['disabled'] = true;
			$options[gettext('Notify')]['desc'] .= ' '.gettext('Of course there must be some Administrator with an e-mail address for this option to make sense!');
		}
		if (function_exists('user_groups_admin_tabs')) {
			$admins = $_zp_authority->getAdministrators('groups');
			$defaultrights = ALL_RIGHTS;
			$ordered = array();
			foreach ($admins as $key=>$admin) {
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
			$options[gettext('Default user group')] =  array('key' => 'register_user_user_rights', 'type' => OPTION_TYPE_SELECTOR,
										'order' => 1,
										'selections' => $ordered,
										'desc' => gettext("Initial group assignment for the new user."));
		} else {
			if (is_numeric(getOption('register_user_user_rights'))) {
				setOptionDefault('register_user_user_rights', NO_RIGHTS);
			} else {
				setOption('register_user_user_rights', NO_RIGHTS);
			}
			$options[gettext('Default rights')] = array('key' => 'register_user_user_rights', 'type' => OPTION_TYPE_CUSTOM,
																														'order' => 2,
																														'desc' => gettext("Initial rights for the new user. (If no rights are set, approval of the user will be required.)"));
		}
		return $options;
	}

	function handleOption($option, $currentValue) {
		global $gallery;
		switch ($option) {
			case 'register_user_page':
				?>
				<table>
					<tr>
						<td style="margin:0; padding:0"><?php echo gettext('script'); ?></td>
						<td style="margin:0; padding:0">
							<input type="hidden" name="_ZP_CUSTOM_selector-register_user_page_page" value="0" />
							<select id="register_user_page_page" name="register_user_page_page">
								<option value="" style="background-color:LightGray"><?php echo gettext('*no page selected'); ?></option>
								<?php
								$curdir = getcwd();
								$root = SERVERPATH.'/'.THEMEFOLDER.'/'.$gallery->getCurrentTheme().'/';
								chdir($root);
								$filelist = safe_glob('*.php');
								$list = array();
								foreach($filelist as $file) {
									$list[] = str_replace('.php', '', filesystemToInternal($file));
								}
								$list = array_diff($list, standardScripts());
								generateListFromArray(array(getOption('register_user_page_page')), $list, false, false);
								chdir($curdir);
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td style="margin:0; padding:0"><?php echo gettext('Link text'); ?></td>
						<td style="margin:0; padding:0">
							<input type="hidden" name="_ZP_CUSTOM_text-register_user_page_link" value="0" />
							<?php print_language_string_list(getOption('register_user_page_link'), 'register_user_page_link', false, NULL, '', TEXTAREA_COLUMNS_SHORT, 'language_string_list_short'); ?>
						</td>
					</tr>
					<tr>
						<td style="margin:0; padding:0"><?php echo gettext('Hint text'); ?></td>
						<td style="margin:0; padding:0">
							<input type="hidden" name="_ZP_CUSTOM_text-register_user_page_tip" value="0" />
							<?php print_language_string_list(getOption('register_user_page_tip'), 'register_user_page_tip', false, NULL, '', TEXTAREA_COLUMNS_SHORT, 'language_string_list_short'); ?>
						</td>
					</tr>
				</table>
				<?php
				break;
			case 'register_user_user_rights':
				printAdminRightsTable('register_user', '', '', getOption('register_user_user_rights'));
				break;
		}
	}
}

function register_user_handleOptionSave($notify,$themename,$themealbum) {
	if (!function_exists('user_groups_admin_tabs')) {
		global $_zp_authority;
		$saved_rights = NO_RIGHTS;
		$rightslist = sortMultiArray($_zp_authority->getRights(), array('set', 'value'));
		foreach ($rightslist as $rightselement=>$right) {
			if (isset($_POST['register_user-'.$rightselement])) {
				$saved_rights = $saved_rights | $_POST['register_user-'.$rightselement];
			}
		}
		setOption('register_user_user_rights', $saved_rights);
	}
	return $notify;
}


/**
 * Parses the verification and registration if they have occurred
 * places the user registration form
 *
 * @param string $thanks the message shown on successful registration
 */
function printRegistrationForm($thanks=NULL) {
	global $notify, $admin_e, $admin_n, $user, $_zp_authority, $_zp_captcha, $_zp_gallery_page, $_zp_gallery;
	require_once(dirname(dirname(__FILE__)).'/admin-functions.php');
	$userobj = NULL;
	// handle any postings
	if (isset($_GET['verify'])) {
		$currentadmins = $_zp_authority->getAdministrators();
		$params = unserialize(pack("H*", trim(sanitize($_GET['verify']),'.')));
		$userobj = $_zp_authority->getAnAdmin(array('`user`=' => $params['user'], '`valid`=' => 1));
		if ($userobj->getEmail() == $params['email']) {
			$userobj->setCredentials(array('registered','user','email'));
			$rights = getOption('register_user_user_rights');
			$group = NULL;
			if (!is_numeric($rights)) {	//  a group or template
				$admin = $_zp_authority->getAnAdmin(array('`user`=' => $rights,'`valid`=' => 0));
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
				$notify = zp_mail(gettext('Zenphoto Gallery registration'),sprintf(gettext('%1$s (%2$s) has registered for the zenphoto gallery providing an e-mail address of %3$s.'),$userobj->getName(), $userobj->getUser(), $userobj->getEmail()));
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
			$notify = 'not_verified';	// User ID no longer exists
		}
	}

	if (isset($_POST['register_user'])) {
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
			$admin_e = trim(sanitize($_POST['adminuser']));
		}
		if (!is_valid_email_zp($admin_e)) {
			$notify = 'invalidemail';
		}

		$pass = trim(sanitize($_POST['adminpass']));
		$user = trim(sanitize($_POST['adminuser']));
		if (!empty($user) && !(empty($admin_n)) && !empty($admin_e)) {
			if ($pass == trim(sanitize($_POST['adminpass_2']))) {
				$currentadmin = $_zp_authority->getAnAdmin(array('`user`=' => $user, '`valid`>' => 0));
				if (is_object($currentadmin)) {
					$notify = 'exists';
				}
				if (empty($notify)) {
					$notify = $_zp_authority->validatePassword($pass);	//	test for valid password
					if (empty($notify)) {
						$userobj = $_zp_authority->newAdministrator('');
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
							$link = rewrite_path(	FULLWEBPATH.'/page/'.substr($_zp_gallery_page,0, -4).'?verify='.bin2hex(serialize(array('user'=>$user,'email'=>$admin_e))),
																		FULLWEBPATH.'/index.php?p='.substr($_zp_gallery_page,0, -4).'&verify='.bin2hex(serialize(array('user'=>$user,'email'=>$admin_e))),false);
							$message = sprintf(get_language_string(getOption('register_user_text')), $link);
							$notify = zp_mail(get_language_string(gettext('Registration confirmation')), $message, array($user=>$admin_e));
							if (empty($notify)) {
								$notify = 'accepted';
							}
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

	if (zp_loggedin()) {
		if (isset($_GET['userlog']) && $_GET['userlog'] == 1) {
			echo '<meta http-equiv="refresh" content="1; url='.WEBPATH.'/">';
		} else {
			echo '<div class="errorbox fade-message">';
			echo  '<h2>'.gettext("you are already logged in.").'</h2>';
			echo '</div>';
		}
		return;
	}
	if (!empty($notify)) {
		if ($notify == 'verified' || $notify == 'accepted') {
			?>
			<div class="Messagebox fade-message">
				<p>
				<?php
				if ($notify == 'verified') {
					if (is_null($thanks)) $thanks = gettext("Thank you for registering.");
					echo $thanks;
				} else {
					echo gettext('Your registration information has been accepted. An email has been sent to you to verify your email address.');
				}
				?>
				</p>
			</div>
			<?php
			if ($notify == 'verified') {
				require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/user_login-out.php');
				?>
				<p><?php echo gettext('You may now log onto the site.'); ?></p>
				<?php
				printPasswordForm('', false, true, WEBPATH.'/'.ZENFOLDER.'/admin.php');
			}
			$notify = 'success';
		} else {
			echo '<div class="errorbox fade-message">';
			echo  '<h2>'.gettext("Registration failed.").'</h2>';
			echo '<p>';
			switch ($notify) {
				case 'exists':
					printf(gettext('The user ID <em>%s</em> is already in use.'),$admin_e);
					break;
				case 'mismatch':
					echo gettext('Your passwords did not match.');
					break;
				case 'incomplete':
					echo gettext('You have not filled in all the fields.');
					break;
				case 'notverified':
					echo gettext('Invalid verification link.');
					break;
				case 'invalidemail':
					echo gettext('Enter a valid email address.');
					break;
				case 'invalidcaptcha':
					echo gettext('The CAPTCHA you entered was not correct.');
					break;
				case 'not_verified':
					echo gettext('Your registration request could not be completed.');
					break;
				case 'filter':
					if (is_object($userobj) && !empty($userobj->msg)) {
						echo $userobj->msg;
					} else {
						echo gettext('Your registration attempt failed a <code>register_user_registered</code> filter check.');
					}
					break;
				default:
					echo $notify;
					break;
			}
			echo '</p>';
			echo '</div>';
		}
	}
	if ($notify != 'success') {
		$form = getPlugin('register_user/register_user_form.php', true);
		require_once($form);
	}
}
?>