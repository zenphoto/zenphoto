<?php
/**
 * USER credentials library
 *
 * @package classes
 */
// force UTF-8 Ø
require_once(dirname(__FILE__) . '/classes.php');

class _Authority {

	var $admin_users = NULL;
	var $admin_groups = NULL;
	var $admin_other = NULL;
	var $admin_all = NULL;
	var $rightsset = NULL;
	protected $master_user = NULL;
	protected $master_userObj = NULL;
	static $preferred_version = 4;
	static $supports_version = 4;
	static $hashList = array('pbkdf2' => 3, 'pbkdf2*' => 2, 'sha1' => 1, 'md5' => 0);

	/**
	 * class instantiation function
	 *
	 * @return lib_auth_options
	 */
	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('password_strength', 10);
			setOptionDefault('min_password_lenght', 6);
			setOptionDefault('user_album_edit_default', 1);
			setOptionDefault('challenge_foil_enabled', 1);
			setOptionDefault('libauth_version', self::$preferred_version);
		}
		$this->admin_all = $this->admin_groups = $this->admin_users = $this->admin_other = array();

		$sql = 'SELECT * FROM ' . prefix('administrators') . ' ORDER BY `rights` DESC, `id`';
		$admins = query($sql, false);
		if ($admins) {
			while ($user = db_fetch_assoc($admins)) {
				$this->admin_all[$user['id']] = $user;
				switch ($user['valid']) {
					case 1:
						$this->admin_users[$user['id']] = $user;
						if (empty($this->master_user))
							$this->master_user = $user['user'];
						break;
					case 0:
						$this->admin_groups[$user['id']] = $user;
						break;
					default:
						$this->admin_other[$user['id']] = $user;
						break;
				}
			}
			db_free_result($admins);
		}
	}

	function addOtherUser($adminObj) {
		$this->admin_users[$adminObj->getID()] = $adminObj->getData();
	}

	function getMasterUser() {
		if (!is_object($this->master_userObj)) {
			$this->master_userObj = new Zenphoto_Administrator($this->master_user, 1);
		}
		return $this->master_userObj;
	}

	function isMasterUser($user) {
		return $user == $this->master_user;
	}

	function validID($id) {
		return array_key_exists($id, $this->admin_all);
	}

	/**
	 * Declares options used by lib-auth
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		$encodings = self::$hashList;
		unset($encodings['pbkdf2*']); // don't use this one any more
		if (!function_exists('hash')) {
			unset($encodings['pbkdf2']);
		}
		$options = array(gettext('Primary album edit') => array('key' => 'user_album_edit_default', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 0,
						'desc' => gettext('Check if you want <em>edit rights</em> automatically assigned when a user <em>primary album</em> is created.')),
				gettext('Minimum password strength') => array('key' => 'password_strength', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 1,
						'desc' => sprintf(gettext('Users must provide passwords a strength of at least %s. The repeat password field will be disabled until this floor is met.'), '<span id="password_strength_display">' . getOption('password_strength') . '</span>')),
				gettext('Password hash algorithm') => array('key' => 'strong_hash', 'type' => OPTION_TYPE_SELECTOR,
						'order' => 3,
						'selections' => $encodings,
						'desc' => sprintf(gettext('The hashing algorithm to be used. In order of robustness the choices are %s'), '<code>' . implode('</code> > <code>', array_flip($encodings)) . '</code>')),
				gettext('Enable Challenge phrase') => array('key' => 'challenge_foil_enabled', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 3,
						'desc' => gettext('Check to allow password reset by challenge phrase responses.'))
		);
		if (getOption('challenge_foil_enabled')) {
			$options[gettext('Challenge phrase foils')] = array('key' => 'challenge_foil', 'type' => OPTION_TYPE_CUSTOM,
					'order' => 4,
					'desc' => gettext('These <em>foil</em> challenge phrases will be presented randomly. This list should not be empty, otherwise hackers can discover valid user IDs and know that there is an answer to any presented challenge phrase.'));
		}
		return $options;
	}

	/**
	 * Dummy for object inheritance purposes
	 */
	function handleOption($option, $currentValue) {
		global $_zp_current_admin_obj;
		switch ($option) {
			case 'password_strength':
				?>
				<input type="hidden" size="3" id="password_strength" name="password_strength" value="<?php echo getOption('password_strength'); ?>" />
				<script type="text/javascript">
					// <!-- <![CDATA[
					function sliderColor(strength) {
						var url = 'url(<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/strengths/strength' + strength + '.png)';
						$('#slider-password_strength').css('background-image', url);
					}
					$(function () {
						$("#slider-password_strength").slider({
				<?php $v = getOption('password_strength'); ?>
							startValue: <?php echo $v; ?>,
							value: <?php echo $v; ?>,
							min: 1,
							max: 30,
							slide: function (event, ui) {
								$("#password_strength").val(ui.value);
								$('#password_strength_display').html(ui.value);
								sliderColor(ui.value);
							}
						});
						var strength = $("#slider-password_strength").slider("value");
						$("#password_strength").val(strength);
						$('#password_strength_display').html(strength);
						sliderColor(strength);
					});
					// ]]> -->
				</script>
				<div id="slider-password_strength"></div>
				<?php
				break;
			case 'challenge_foil':
				$questions = getSerializedArray(getOption('challenge_foils'));
				$questions[] = array('');
				foreach ($questions as $key => $question) {
					?>
					<?php print_language_string_list($question, 'challenge_foil_' . $key, false, NULL, '100%'); ?>	<br/>
					<?php
				}
				break;
		}
	}

	function handleOptionSave($themename, $themealbum) {
		$questions = array();
		foreach ($_POST as $key => $value) {
			if (!empty($value)) {
				preg_match('~challenge_foil_(\d+)_(.*)~', $key, $matches);
				if (!empty($matches)) {
					$questions[$matches[1]][$matches[2]] = sanitize($value);
				}
			}
		}
		setOption('challenge_foils', serialize($questions));
	}

	static function getVersion() {
		$v = getOption('libauth_version');
		if (empty($v)) {
			return self::$preferred_version;
		} else {
			return $v;
		}
	}

	/**
	 * Returns the hash of the zenphoto password
	 *
	 * @param string $user
	 * @param string $pass
	 * @return string
	 */
	static function passwordHash($user, $pass, $hash_type = NULL) {
		if (is_null($hash_type)) {
			$hash_type = getOption('strong_hash');
		}
		switch ($hash_type) {
			case 1:
				$hash = sha1($user . $pass . HASH_SEED);
				break;
			case 2:
				//	deprecated beause of possible "+" in the text
				$hash = base64_encode(self::pbkdf2($pass, $user . HASH_SEED));
				break;
			case 3:
				$hash = str_replace('+', '-', base64_encode(self::pbkdf2($pass, $user . HASH_SEED)));
				break;
			default:
				$hash = md5($user . $pass . HASH_SEED);
				break;
		}
		if (DEBUG_LOGIN) {
			debugLog("passwordHash($user, $pass, $hash_type)[ " . HASH_SEED . " ]:$hash");
		}
		return $hash;
	}

	/**
	 * Returns an array of admin users, indexed by the userid and ordered by "privileges"
	 *
	 * The array contains the id, hashed password, user's name, email, and admin privileges
	 *
	 * @param string $what: 'all' for everything, 'users' for just users 'groups' for groups and templates
	 * @return array
	 */
	function getAdministrators($what = 'users') {
		switch ($what) {
			case 'users':
				return $this->admin_users;
			case 'groups':
				return $this->admin_groups;
			case 'allusers':
				return array_merge($this->admin_users, $this->admin_other);
			default:
				return $this->admin_all;
		}
	}

	/**
	 * Returns an admin object from the $pat:$criteria
	 * @param array $criteria [ match => criteria ]
	 * @return Zenphoto_Administrator
	 */
	function getAnAdmin($criteria) {
		$find = array();
		foreach ($criteria as $match => $value) {
			preg_match('/(.*)([<>=!])/', $match, $detail);
			$find[trim($detail[1], '`')] = array('field' => trim($detail[1]), 'op' => trim($detail[2]), 'value' => $value);
		}
		if (isset($find['id'])) {
			$id = $find['id']['value'];
			if (isset($this->admin_all[$id])) {
				$admin = $this->admin_all[$id];
				foreach ($find as $field => $select) {
					if ($r = strcmp($admin[$field], $select['value']) == 0) {
						$ops = array('=', '>=', '<=');
					} else if ($r > 0) {
						$ops = array('>', '>=', '!=');
					} else {
						$ops = array('<', '<=', '!=');
					}
					if (!in_array($select['op'], $ops)) {
						unset($admin);
						break;
					}
				}
			}
		}
		if (!isset($admin)) {
			$selector = array();
			foreach ($find as $field => $select) {
				if (!is_numeric($value = $select['value'])) {
					$value = db_quote($value);
				}
				$selector[] = $select['field'] . $select['op'] . $value;
			}
			$sql = 'SELECT * FROM ' . prefix('administrators') . ' WHERE ' . implode(' AND ', $selector) . ' LIMIT 1';
			$admin = query_single_row($sql);
		}
		if ($admin) {
			return self::newAdministrator($admin['user'], $admin['valid']);
		} else {
			return NULL;
		}
	}

	/**
	 * Retuns the administration rights of a saved authorization code
	 * Will promote an admin to ADMIN_RIGHTS if he is the most privileged admin
	 *
	 * @param string $authCode the hash code to check
	 * @param int $id whom we think this is
	 *
	 * @return bit
	 */
	function checkAuthorization($authCode, $id) {
		global $_zp_current_admin_obj;
		if (DEBUG_LOGIN) {
			debugLogBacktrace("checkAuthorization($authCode, $id)");
		}

		$admins = $this->getAdministrators();
		if (count($admins) == 0) {
			if (DEBUG_LOGIN) {
				debugLog("checkAuthorization: no admins");
			}
			$_zp_current_admin_obj = new Zenphoto_Administrator('', 1);
			$_zp_current_admin_obj->set('id', 0);
			$_zp_current_admin_obj->reset = true;
			return ADMIN_RIGHTS;
		}
		if (is_object($_zp_current_admin_obj) && $_zp_current_admin_obj->reset) {
			if (DEBUG_LOGIN) {
				debugLog("checkAuthorization: reset request");
			}
			return $_zp_current_admin_obj->getRights();
		}

		$_zp_current_admin_obj = NULL;
		if (empty($authCode) || empty($id))
			return 0; //  so we don't "match" with an empty password
		if (DEBUG_LOGIN) {
			debugLogVar("checkAuthorization: admins", $admins);
		}
		$rights = 0;
		$criteria = array('`pass`=' => $authCode, '`id`=' => (int) $id, '`valid`=' => 1);
		$user = $this->getAnAdmin($criteria);
		if (is_object($user)) {
			$_zp_current_admin_obj = $user;
			$rights = $user->getRights();
			if (DEBUG_LOGIN) {
				debugLog(sprintf('checkAuthorization: from %1$s->%2$X', $authCode, $rights));
			}
			return $rights;
		}
		$_zp_current_admin_obj = NULL;
		if (DEBUG_LOGIN) {
			debugLog("checkAuthorization: no match");
		}
		return 0; // no rights
	}

	/**
	 * Checks a logon user/password against admins
	 *
	 * Returns the user object if there is a match
	 *
	 * @param string $user
	 * @param string $pass
	 * @return object
	 */
	function checkLogon($user, $pass) {
		$userobj = $this->getAnAdmin(array('`user`=' => $user, '`valid`=' => 1));
		if ($userobj) {
			$hash = self::passwordHash($user, $pass, $userobj->get('passhash'));
			if ($hash != $userobj->getPass()) {
				//	maybe not yet updated passhash field
				foreach (self::$hashList as $hashv) {
					$hash = self::passwordHash($user, $pass, $hashv);
					if ($hash == $userobj->getPass()) {
						break;
					} else {
						$hash = -1;
					}
				}
				if ($hash === -1) {
					$userobj = NULL;
				}
			}
		} else {
			$hash = -1;
		}

		if (DEBUG_LOGIN) {
			if ($userobj) {
				$rights = sprintf('%X', $userobj->getRights());
			} else {
				$rights = false;
			}
			debugLog(sprintf('checkLogon(%1$s, %2$s)->%3$s', $user, $hash, $rights));
		}
		return $userobj;
	}

	/**
	 * Returns the email addresses of the Admin with ADMIN_USERS rights
	 *
	 * @param bit $rights what kind of admins to retrieve
	 * @return array
	 */
	function getAdminEmail($rights = NULL) {
		if (is_null($rights)) {
			$rights = ADMIN_RIGHTS;
		}
		$emails = array();
		$admins = $this->getAdministrators();
		foreach ($admins as $user) {
			if (($user['rights'] & $rights) && is_valid_email_zp($user['email'])) {
				$name = $user['name'];
				if (empty($name)) {
					$name = $user['user'];
				}
				$emails[$name] = $user['email'];
			}
		}
		return $emails;
	}

	/**
	 * Migrates credentials
	 *
	 * @param int $oldversion
	 */
	function migrateAuth($to) {
		if ($to > self::$supports_version || $to < self::$preferred_version - 1) {
			zp_error(sprintf(gettext('Cannot migrate rights to version %1$s (Zenphoto_Authority supports only %2$s and %3$s.)'), $to, self::$supports_version, self::$preferred_version), E_USER_NOTICE);
			return false;
		}
		$success = true;
		$oldversion = self::getVersion();
		setOption('libauth_version', $to);
		$this->admin_users = array();
		$sql = "SELECT * FROM " . prefix('administrators') . "ORDER BY `rights` DESC, `id`";
		$admins = query($sql, false);
		if ($admins) { // something to migrate
			$oldrights = array();
			foreach (self::getRights($oldversion) as $key => $right) {
				$oldrights[$key] = $right['value'];
			}
			$currentrights = self::getRights($to);
			while ($user = db_fetch_assoc($admins)) {
				$update = false;
				$rights = $user['rights'];
				$newrights = $currentrights['NO_RIGHTS']['value'];
				foreach ($currentrights as $key => $right) {
					if ($right['display']) {
						if (array_key_exists($key, $oldrights) && $rights & $oldrights[$key]) {
							$newrights = $newrights | $right['value'];
						}
					}
				}
				if ($oldversion < 4) {
					$newrights = $newrights | $currentrights['USER_RIGHTS']['value'];
				}
				if ($to >= 3 && $oldversion < 3) {
					if ($rights & $oldrights['VIEW_ALL_RIGHTS']) {
						$updaterights = $currentrights['ALL_ALBUMS_RIGHTS']['value'] | $currentrights['ALL_PAGES_RIGHTS']['value'] |
										$currentrights['ALL_NEWS_RIGHTS']['value'] | $currentrights['VIEW_SEARCH_RIGHTS']['value'] |
										$currentrights['VIEW_GALLERY_RIGHTS']['value'] | $currentrights['VIEW_FULLIMAGE_RIGHTS']['value'];
						$newrights = $newrights | $updaterights;
					}
				}
				if ($oldversion >= 3 && $to < 3) {
					if ($oldrights['ALL_ALBUMS_RIGHTS'] || $oldrights['ALL_PAGES_RIGHTS'] || $oldrights['ALL_NEWS_RIGHTS']) {
						$newrights = $newrights | $currentrights['VIEW_ALL_RIGHTS']['value'];
					}
				}
				if ($oldversion == 1) { // need to migrate zenpage rights
					if ($rights & $oldrights['ZENPAGE_RIGHTS']) {
						$newrights = $newrights | $currentrights['ZENPAGE_PAGES_RIGHTS'] | $currentrights['ZENPAGE_NEWS_RIGHTS'] | $currentrights['FILES_RIGHTS'];
					}
				}
				if ($to >= 3) {
					if ($newrights & $currentrights['ADMIN_RIGHTS']['value']) {
						$newrights = $currentrights['ALL_RIGHTS']['value'];
					} else {
						if ($newrights & $currentrights['MANAGE_ALL_ALBUM_RIGHTS']['value']) {
							// these are lock-step linked!
							$newrights = $newrights | $currentrights['ALBUM_RIGHTS']['value'];
						}
						if ($newrights & $currentrights['MANAGE_ALL_NEWS_RIGHTS']['value']) {
							// these are lock-step linked!
							$newrights = $newrights | $currentrights['ZENPAGE_NEWS_RIGHTS']['value'];
						}
						if ($newrights & $currentrights['MANAGE_ALL_PAGES_RIGHTS']['value']) {
							// these are lock-step linked!
							$newrights = $newrights | $currentrights['ZENPAGE_PAGES_RIGHTS']['value'];
						}
					}
				}

				$sql = 'UPDATE ' . prefix('administrators') . ' SET `rights`=' . $newrights . ' WHERE `id`=' . $user['id'];
				$success = $success && query($sql);
			} // end loop
			db_free_result($admins);
		}
		return $success;
	}

	/**
	 * Updates a field in admin record(s)
	 *
	 * @param string $update name of the field
	 * @param mixed $value what to store
	 * @param array $constraints on the update [ field<op>,value ]
	 * @return mixed Query result
	 */
	static function updateAdminField($update, $value, $constraints) {
		$where = '';
		foreach ($constraints as $field => $clause) {
			if (!empty($where))
				$where .= ' AND ';
			if (is_numeric($clause)) {
				$where .= $field . $clause;
			} else {
				$where .= $field . db_quote($clause);
			}
		}
		if (is_null($value)) {
			$value = 'NULL';
		} else {
			$value = db_quote($value);
		}
		$sql = 'UPDATE ' . prefix('administrators') . ' SET `' . $update . '`=' . $value . ' WHERE ' . $where;
		$result = query($sql);
		return $result;
	}

	/**
	 * Instantiates and returns administrator object
	 * @param $name
	 * @param $valid
	 * @return object
	 */
	static function newAdministrator($name, $valid = 1, $allowCreate = true) {
		$user = new Zenphoto_Administrator($name, $valid, $allowCreate);
		return $user;
	}

	/**
	 * Returns an array of the rights definitions for $version (default returns current version rights)
	 *
	 * @param $version
	 */
	static function getRights($version = NULL) {
		if (empty($version)) {
			$v = self::getVersion();
		} else {
			$v = $version;
		}
		switch ($v) {
			case 1:
				$rightsset = array('NO_RIGHTS' => array('value' => 2, 'name' => gettext('No rights'), 'set' => '', 'display' => false, 'hint' => ''),
						'OVERVIEW_RIGHTS' => array('value' => 4, 'name' => gettext('Overview'), 'set' => '', 'display' => true, 'hint' => ''),
						'VIEW_ALL_RIGHTS' => array('value' => 8, 'name' => gettext('View all'), 'set' => '', 'display' => true, 'hint' => ''),
						'UPLOAD_RIGHTS' => array('value' => 16, 'name' => gettext('Upload'), 'set' => '', 'display' => true, 'hint' => ''),
						'POST_COMMENT_RIGHTS' => array('value' => 32, 'name' => gettext('Post comments'), 'set' => '', 'display' => true, 'hint' => ''),
						'COMMENT_RIGHTS' => array('value' => 64, 'name' => gettext('Comments'), 'set' => '', 'display' => true, 'hint' => ''),
						'ALBUM_RIGHTS' => array('value' => 256, 'name' => gettext('Album'), 'set' => '', 'display' => true, 'hint' => ''),
						'MANAGE_ALL_ALBUM_RIGHTS' => array('value' => 512, 'name' => gettext('Manage all albums'), 'set' => '', 'display' => true, 'hint' => ''),
						'THEMES_RIGHTS' => array('value' => 1024, 'name' => gettext('Themes'), 'set' => '', 'display' => true, 'hint' => ''),
						'ZENPAGE_RIGHTS' => array('value' => 2049, 'name' => gettext('Zenpage'), 'set' => '', 'display' => true, 'hint' => ''),
						'TAGS_RIGHTS' => array('value' => 4096, 'name' => gettext('Tags'), 'set' => '', 'display' => true, 'hint' => ''),
						'OPTIONS_RIGHTS' => array('value' => 8192, 'name' => gettext('Options'), 'set' => '', 'display' => true, 'hint' => ''),
						'ADMIN_RIGHTS' => array('value' => 65536, 'name' => gettext('Admin'), 'set' => '', 'display' => true, 'hint' => ''));
				break;
			case 2:
				$rightsset = array('NO_RIGHTS' => array('value' => 1, 'name' => gettext('No rights'), 'set' => '', 'display' => false, 'hint' => ''),
						'OVERVIEW_RIGHTS' => array('value' => pow(2, 2), 'name' => gettext('Overview'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may view the admin overview page.')),
						'VIEW_ALL_RIGHTS' => array('value' => pow(2, 4), 'name' => gettext('View all'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may view all of the gallery regardless of protection of the page. Without this right, the user can view only public ones and those checked in his managed object lists or as granted by View Search or View Gallery.')),
						'UPLOAD_RIGHTS' => array('value' => pow(2, 6), 'name' => gettext('Upload'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may upload to the albums for which they have management rights.')),
						'POST_COMMENT_RIGHTS' => array('value' => pow(2, 8), 'name' => gettext('Post comments'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('When the comment_form plugin is used for comments and its "Only members can comment" option is set, only users with this right may post comments.')),
						'COMMENT_RIGHTS' => array('value' => pow(2, 10), 'name' => gettext('Comments'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may make comments tab changes.')),
						'ALBUM_RIGHTS' => array('value' => pow(2, 12), 'name' => gettext('Albums'), 'set' => gettext('Albums'), 'display' => true, 'hint' => gettext('Users with this right may access the “albums” tab to make changes.')),
						'ZENPAGE_PAGES_RIGHTS' => array('value' => pow(2, 14), 'name' => gettext('Pages'), 'set' => gettext('Pages'), 'display' => true, 'hint' => gettext('Users with this right may edit and manage Zenpage pages.')),
						'ZENPAGE_NEWS_RIGHTS' => array('value' => pow(2, 16), 'name' => gettext('News'), 'set' => gettext('News'), 'display' => true, 'hint' => gettext('Users with this right may edit and manage Zenpage articles and categories.')),
						'FILES_RIGHTS' => array('value' => pow(2, 18), 'name' => gettext('Files'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Allows the user access to the “filemanager” located on the upload: files sub-tab.')),
						'MANAGE_ALL_PAGES_RIGHTS' => array('value' => pow(2, 20), 'name' => gettext('Manage all pages'), 'set' => gettext('Pages'), 'display' => true, 'hint' => gettext('Users who do not have “Admin” rights normally are restricted to manage only objects to which they have been assigned. This right allows them to manage any Zenpage page.')),
						'MANAGE_ALL_NEWS_RIGHTS' => array('value' => pow(2, 22), 'name' => gettext('Manage all news'), 'set' => gettext('News'), 'display' => true, 'hint' => gettext('Users who do not have “Admin” rights normally are restricted to manage only objects to which they have been assigned. This right allows them to manage any Zenpage news article or category.')),
						'MANAGE_ALL_ALBUM_RIGHTS' => array('value' => pow(2, 24), 'name' => gettext('Manage all albums'), 'set' => gettext('Albums'), 'display' => true, 'hint' => gettext('Users who do not have “Admin” rights normally are restricted to manage only objects to which they have been assigned. This right allows them to manage any album in the gallery.')),
						'THEMES_RIGHTS' => array('value' => pow(2, 26), 'name' => gettext('Themes'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may make themes related changes. These are limited to the themes associated with albums checked in their managed albums list.')),
						'TAGS_RIGHTS' => array('value' => pow(2, 28), 'name' => gettext('Tags'), 'set' => gettext('General'), 'display' => true, 'hint' => gettext('Users with this right may make additions and changes to the set of tags.')),
						'OPTIONS_RIGHTS' => array('value' => pow(2, 29), 'name' => gettext('Options'), 'set' => gettext('General'), 'display' => true, 'hint' => gettext('Users with this right may make changes on the options.')),
						'ADMIN_RIGHTS' => array('value' => pow(2, 30), 'name' => gettext('Admin'), 'set' => gettext('General'), 'display' => true, 'hint' => gettext('The master privilege. A user with "Admin" can do anything. (No matter what his other rights might indicate!)')));
				break;
			case 3:
				$rightsset = array('NO_RIGHTS' => array('value' => 1, 'name' => gettext('No rights'), 'set' => '', 'display' => false, 'hint' => ''),
						'OVERVIEW_RIGHTS' => array('value' => pow(2, 2), 'name' => gettext('Overview'), 'set' => gettext('General'), 'display' => true, 'hint' => gettext('Users with this right may view the admin overview page.')),
						'VIEW_GALLERY_RIGHTS' => array('value' => pow(2, 4), 'name' => gettext('View gallery'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may view otherwise protected generic gallery pages.')),
						'VIEW_SEARCH_RIGHTS' => array('value' => pow(2, 5), 'name' => gettext('View search'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may view search pages even if password protected.')),
						'VIEW_FULLIMAGE_RIGHTS' => array('value' => pow(2, 6), 'name' => gettext('View fullimage'), 'set' => gettext('Albums'), 'display' => true, 'hint' => gettext('Users with this right may view all full sized (raw) images.')),
						'ALL_NEWS_RIGHTS' => array('value' => pow(2, 7), 'name' => gettext('Access all'), 'set' => gettext('News'), 'display' => true, 'hint' => gettext('Users with this right have access to all zenpage news articles.')),
						'ALL_PAGES_RIGHTS' => array('value' => pow(2, 8), 'name' => gettext('Access all'), 'set' => gettext('Pages'), 'display' => true, 'hint' => gettext('Users with this right have access to all zenpage pages.')),
						'ALL_ALBUMS_RIGHTS' => array('value' => pow(2, 9), 'name' => gettext('Access all'), 'set' => gettext('Albums'), 'display' => true, 'hint' => gettext('Users with this right have access to all albums.')),
						'VIEW_UNPUBLISHED_RIGHTS' => array('value' => pow(2, 10), 'name' => gettext('View unpublished'), 'set' => gettext('Albums'), 'display' => true, 'hint' => gettext('Users with this right will see all unpublished items.')),
						'POST_COMMENT_RIGHTS' => array('value' => pow(2, 11), 'name' => gettext('Post comments'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('When the comment_form plugin is used for comments and its "Only members can comment" option is set, only users with this right may post comments.')),
						'COMMENT_RIGHTS' => array('value' => pow(2, 12), 'name' => gettext('Comments'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may make comments tab changes.')),
						'UPLOAD_RIGHTS' => array('value' => pow(2, 13), 'name' => gettext('Upload'), 'set' => gettext('Albums'), 'display' => true, 'hint' => gettext('Users with this right may upload to the albums for which they have management rights.')),
						'ZENPAGE_NEWS_RIGHTS' => array('value' => pow(2, 15), 'name' => gettext('News'), 'set' => gettext('News'), 'display' => false, 'hint' => gettext('Users with this right may edit and manage Zenpage articles and categories.')),
						'ZENPAGE_PAGES_RIGHTS' => array('value' => pow(2, 16), 'name' => gettext('Pages'), 'set' => gettext('Pages'), 'display' => false, 'hint' => gettext('Users with this right may edit and manage Zenpage pages.')),
						'FILES_RIGHTS' => array('value' => pow(2, 17), 'name' => gettext('Files'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Allows the user access to the “filemanager” located on the upload: files sub-tab.')),
						'ALBUM_RIGHTS' => array('value' => pow(2, 18), 'name' => gettext('Albums'), 'set' => gettext('Albums'), 'display' => false, 'hint' => gettext('Users with this right may access the “albums” tab to make changes.')),
						'MANAGE_ALL_NEWS_RIGHTS' => array('value' => pow(2, 21), 'name' => gettext('Manage all'), 'set' => gettext('News'), 'display' => true, 'hint' => gettext('Users who do not have “Admin” rights normally are restricted to manage only objects to which they have been assigned. This right allows them to manage any Zenpage news article or category.')),
						'MANAGE_ALL_PAGES_RIGHTS' => array('value' => pow(2, 22), 'name' => gettext('Manage all'), 'set' => gettext('Pages'), 'display' => true, 'hint' => gettext('Users who do not have “Admin” rights normally are restricted to manage only objects to which they have been assigned. This right allows them to manage any Zenpage page.')),
						'MANAGE_ALL_ALBUM_RIGHTS' => array('value' => pow(2, 23), 'name' => gettext('Manage all'), 'set' => gettext('Albums'), 'display' => true, 'hint' => gettext('Users who do not have “Admin” rights normally are restricted to manage only objects to which they have been assigned. This right allows them to manage any album in the gallery.')),
						'THEMES_RIGHTS' => array('value' => pow(2, 26), 'name' => gettext('Themes'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may make themes related changes. These are limited to the themes associated with albums checked in their managed albums list.')),
						'TAGS_RIGHTS' => array('value' => pow(2, 28), 'name' => gettext('Tags'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may make additions and changes to the set of tags.')),
						'OPTIONS_RIGHTS' => array('value' => pow(2, 29), 'name' => gettext('Options'), 'set' => gettext('General'), 'display' => true, 'hint' => gettext('Users with this right may make changes on the options.')),
						'ADMIN_RIGHTS' => array('value' => pow(2, 30), 'name' => gettext('Admin'), 'set' => gettext('General'), 'display' => true, 'hint' => gettext('The master privilege. A user with "Admin" can do anything. (No matter what his other rights might indicate!)')));
				break;
			case 4:
				$rightsset = array('NO_RIGHTS' => array('value' => 1, 'name' => gettext('No rights'), 'set' => '', 'display' => false, 'hint' => ''),
						'OVERVIEW_RIGHTS' => array('value' => pow(2, 2), 'name' => gettext('Overview'), 'set' => gettext('General'), 'display' => true, 'hint' => gettext('Users with this right may view the admin overview page.')),
						'USER_RIGHTS' => array('value' => pow(2, 3), 'name' => gettext('User'), 'set' => gettext('General'), 'display' => true, 'hint' => gettext('Users must have this right to change their credentials.')),
						'DEBUG_RIGHTS' => array('value' => pow(2, 4), 'name' => gettext('Debug'), 'set' => gettext('General'), 'display' => true, 'hint' => gettext('Allows viewing of the debug tab items.')),
						'VIEW_GALLERY_RIGHTS' => array('value' => pow(2, 5), 'name' => gettext('View gallery'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may view otherwise protected generic gallery pages.')),
						'VIEW_SEARCH_RIGHTS' => array('value' => pow(2, 6), 'name' => gettext('View search'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may view search pages even if password protected.')),
						'VIEW_FULLIMAGE_RIGHTS' => array('value' => pow(2, 7), 'name' => gettext('View fullimage'), 'set' => gettext('Albums'), 'display' => true, 'hint' => gettext('Users with this right may view all full sized (raw) images.')),
						'ALL_NEWS_RIGHTS' => array('value' => pow(2, 8), 'name' => gettext('Access all'), 'set' => gettext('News'), 'display' => true, 'hint' => gettext('Users with this right have access to all zenpage news articles.')),
						'ALL_PAGES_RIGHTS' => array('value' => pow(2, 9), 'name' => gettext('Access all'), 'set' => gettext('Pages'), 'display' => true, 'hint' => gettext('Users with this right have access to all zenpage pages.')),
						'ALL_ALBUMS_RIGHTS' => array('value' => pow(2, 10), 'name' => gettext('Access all'), 'set' => gettext('Albums'), 'display' => true, 'hint' => gettext('Users with this right have access to all albums.')),
						'VIEW_UNPUBLISHED_RIGHTS' => array('value' => pow(2, 11), 'name' => gettext('View unpublished'), 'set' => gettext('Albums'), 'display' => true, 'hint' => gettext('Users with this right will see all unpublished items.')),
						'VIEW_UNPUBLISHED_NEWS_RIGHTS' => array('value' => pow(2, 12), 'name' => gettext('View unpublished'), 'set' => gettext('News'), 'display' => true, 'hint' => gettext('Users with this right will see all unpublished items.')),
						'POST_COMMENT_RIGHTS' => array('value' => pow(2, 13), 'name' => gettext('Post comments'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('When the comment_form plugin is used for comments and its "Only members can comment" option is set, only users with this right may post comments.')),
						'COMMENT_RIGHTS' => array('value' => pow(2, 14), 'name' => gettext('Comments'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may make comments tab changes.')),
						'UPLOAD_RIGHTS' => array('value' => pow(2, 15), 'name' => gettext('Upload'), 'set' => gettext('Albums'), 'display' => true, 'hint' => gettext('Users with this right may upload to the albums for which they have management rights.')),
						'VIEW_UNPUBLISHED_PAGE_RIGHTS' => array('value' => pow(2, 16), 'name' => gettext('View unpublished'), 'set' => gettext('Pages'), 'display' => true, 'hint' => gettext('Users with this right will see all unpublished items.')),
						'ZENPAGE_NEWS_RIGHTS' => array('value' => pow(2, 17), 'name' => gettext('News'), 'set' => gettext('News'), 'display' => false, 'hint' => gettext('Users with this right may edit and manage Zenpage articles and categories.')),
						'ZENPAGE_PAGES_RIGHTS' => array('value' => pow(2, 18), 'name' => gettext('Pages'), 'set' => gettext('Pages'), 'display' => false, 'hint' => gettext('Users with this right may edit and manage Zenpage pages.')),
						'FILES_RIGHTS' => array('value' => pow(2, 19), 'name' => gettext('Files'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Allows the user access to the “filemanager” located on the upload: files sub-tab.')),
						'ALBUM_RIGHTS' => array('value' => pow(2, 20), 'name' => gettext('Albums'), 'set' => gettext('Albums'), 'display' => false, 'hint' => gettext('Users with this right may access the “albums” tab to make changes.')),
						'MANAGE_ALL_NEWS_RIGHTS' => array('value' => pow(2, 21), 'name' => gettext('Manage all'), 'set' => gettext('News'), 'display' => true, 'hint' => gettext('Users who do not have “Admin” rights normally are restricted to manage only objects to which they have been assigned. This right allows them to manage any Zenpage news article or category.')),
						'MANAGE_ALL_PAGES_RIGHTS' => array('value' => pow(2, 22), 'name' => gettext('Manage all'), 'set' => gettext('Pages'), 'display' => true, 'hint' => gettext('Users who do not have “Admin” rights normally are restricted to manage only objects to which they have been assigned. This right allows them to manage any Zenpage page.')),
						'MANAGE_ALL_ALBUM_RIGHTS' => array('value' => pow(2, 23), 'name' => gettext('Manage all'), 'set' => gettext('Albums'), 'display' => true, 'hint' => gettext('Users who do not have “Admin” rights normally are restricted to manage only objects to which they have been assigned. This right allows them to manage any album in the gallery.')),
						//2**24
						'CODEBLOCK_RIGHTS' => array('value' => pow(2, 25), 'name' => gettext('Codeblock'), 'set' => gettext('General'), 'display' => true, 'hint' => gettext('Users with this right may edit Codeblocks.')),
						'THEMES_RIGHTS' => array('value' => pow(2, 26), 'name' => gettext('Themes'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may make themes related changes. These are limited to the themes associated with albums checked in their managed albums list.')),
						//2*27
						'TAGS_RIGHTS' => array('value' => pow(2, 28), 'name' => gettext('Tags'), 'set' => gettext('Gallery'), 'display' => true, 'hint' => gettext('Users with this right may make additions and changes to the set of tags.')),
						'OPTIONS_RIGHTS' => array('value' => pow(2, 29), 'name' => gettext('Options'), 'set' => gettext('General'), 'display' => true, 'hint' => gettext('Users with this right may make changes on the options.')),
						'ADMIN_RIGHTS' => array('value' => pow(2, 30), 'name' => gettext('Admin'), 'set' => gettext('General'), 'display' => true, 'hint' => gettext('The master privilege. A user with "Admin" can do anything. (No matter what his other rights might indicate!)')));
				break;
		}
		$allrights = 0;
		foreach ($rightsset as $key => $right) {
			$allrights = $allrights | $right['value'];
		}
		$rightsset['ALL_RIGHTS'] = array('value' => $allrights, 'name' => gettext('All rights'), 'set' => '', 'display' => false, 'hint' => '');
		$rightsset['DEFAULT_RIGHTS'] = array('value' => $rightsset['OVERVIEW_RIGHTS']['value'] + $rightsset['POST_COMMENT_RIGHTS']['value'], 'name' => gettext('Default rights'), 'set' => '', 'display' => false, 'hint' => '');
		if (isset($rightsset['VIEW_ALL_RIGHTS']['value'])) {
			$rightsset['DEFAULT_RIGHTS']['value'] = $rightsset['DEFAULT_RIGHTS']['value'] | $rightsset['VIEW_ALL_RIGHTS']['value'];
		} else {
			$rightsset['DEFAULT_RIGHTS']['value'] = $rightsset['DEFAULT_RIGHTS'] | $rightsset['ALL_ALBUMS_RIGHTS']['value'] |
							$rightsset['ALL_PAGES_RIGHTS']['value'] | $rightsset['ALL_NEWS_RIGHTS']['value'] |
							$rightsset['VIEW_SEARCH_RIGHTS']['value'] | $rightsset['VIEW_GALLERY_RIGHTS']['value'];
		}
		$rightsset = sortMultiArray($rightsset, 'value', false, true, false, true);
		return $rightsset;
	}

	static function getResetTicket($user, $pass) {
		$req = time();
		$ref = sha1($req . $user . $pass);
		$time = bin2hex(rc4('ticket' . HASH_SEED, $req));
		return $time . $ref;
	}

	function validateTicket($ticket, $user) {
		global $_zp_current_admin_obj;
		$admins = $this->getAdministrators();
		foreach ($admins as $tuser) {
			if ($tuser['user'] == $user) {
				if ($tuser['rights'] & USER_RIGHTS) {
					$request_date = rc4('ticket' . HASH_SEED, pack("H*", $time = substr($ticket, 0, 20)));
					$ticket = substr($ticket, 20);
					$ref = sha1($request_date . $user . $tuser['pass']);
					if ($ref === $ticket) {
						if (time() <= ($request_date + (3 * 24 * 60 * 60))) {
							// limited time offer
							$_zp_current_admin_obj = new Zenphoto_Administrator($user, 1);
							$_zp_current_admin_obj->reset = true;
						}
					}
					break;
				}
			}
		}
	}

	/**
	 * Set log-in cookie for a user
	 * @param object $user
	 */
	static function logUser($user) {
		$user->set('lastloggedin', $user->get('loggedin'));
		$user->set('loggedin', date('Y-m-d H:i:s'));
		$user->save();
		zp_setCookie("zp_user_auth", $user->getPass() . '.' . $user->getID());
	}

	/**
	 * User authentication support
	 */
	function handleLogon() {
		global $_zp_current_admin_obj, $_zp_login_error, $_zp_captcha, $_zp_loggedin;
		if (isset($_POST['login'])) {
			$post_user = sanitize(@$_POST['user'], 0);
			$post_pass = sanitize(@$_POST['pass'], 0);
			$_zp_loggedin = false;

			switch (@$_POST['password']) {
				default:
					if (isset($_POST['user'])) { //	otherwise must be a guest logon, don't even try admin path
						$user = self::checkLogon($post_user, $post_pass);
						if ($user) {
							$_zp_loggedin = $user->getRights();
						}
						$_zp_loggedin = zp_apply_filter('admin_login_attempt', $_zp_loggedin, $post_user, $post_pass);
						if ($_zp_loggedin) {
							self::logUser($user);
							$_zp_current_admin_obj = $user;
							zp_session_destroy();
							zp_session_start();
						} else {
							zp_clearCookie("zp_user_auth"); // Clear the cookie, just in case
							$_zp_login_error = 1;
						}
					}
					break;
				case 'challenge':
					$user = $this->getAnAdmin(array('`user`=' => $post_user, '`valid`=' => 1));
					if (is_object($user)) {
						$info = $user->getChallengePhraseInfo();
						if ($post_pass && $info['response'] == $post_pass) {
							$ref = self::getResetTicket($post_user, $user->getPass());
							header('location:' . WEBPATH . '/' . ZENFOLDER . '/admin-users.php?ticket=' . $ref . '&user=' . $post_user);
							exitZP();
						}
					}
					$_zp_login_error = gettext('Sorry, that is not the answer.');
					$_REQUEST['logon_step'] = 'challenge';
					break;
				case 'captcha':
					if ($_zp_captcha->checkCaptcha(trim(@$_POST['code']), sanitize(@$_POST['code_h'], 3))) {
						require_once(dirname(__FILE__) . '/load_objectClasses.php'); // be sure that the plugins are loaded for the mail handler
						if (empty($post_user)) {
							$requestor = gettext('You are receiving this e-mail because of a password reset request on your ZenPhoto20 gallery.');
						} else {
							$requestor = sprintf(gettext("You are receiving this e-mail because of a password reset request on your ZenPhoto20 gallery from a user who tried to log in as %s."), $post_user);
						}
						$admins = $this->getAdministrators();
						$mails = array();
						$user = NULL;
						foreach ($admins as $key => $tuser) {
							if (!empty($tuser['email'])) {
								if (!empty($post_user) && ($tuser['user'] == $post_user || $tuser['email'] == $post_user)) {
									$name = $tuser['name'];
									if (empty($name)) {
										$name = $tuser['user'];
									}
									$mails[$name] = $tuser['email'];
									$user = $tuser;
									unset($admins[$key]); // drop him from alternate list.
								} else {
									if (!($tuser['rights'] & ADMIN_RIGHTS)) {
										unset($admins[$key]); // eliminate any peons from the list
									}
								}
							} else {
								unset($admins[$key]); // we want to ignore groups and users with no email address here!
							}
						}

						$cclist = array();
						foreach ($admins as $tuser) {
							$name = $tuser['name'];
							if (empty($name)) {
								$name = $tuser['user'];
							}
							if (is_null($user)) {
								$user = $tuser;
								$mails[$name] = $tuser['email'];
							} else {
								$cclist[$name] = $tuser['email'];
							}
						}
						if (is_null($user)) {
							$_zp_login_error = gettext('There was no one to which to send the reset request.');
						} else {
							$ref = self::getResetTicket($user['user'], $user['pass']);
							$msg = "\n" . $requestor .
											"\n" . sprintf(gettext("To reset your Admin passwords visit: %s"), FULLWEBPATH . "/" . ZENFOLDER . "/admin-users.php?ticket=$ref&user=" . $user['user']) .
											"\n" . gettext("If you do not wish to reset your passwords just ignore this message. This ticket will automatically expire in 3 days.");
							$err_msg = zp_mail(gettext("The ZenPhoto20 information you requested"), $msg, $mails, $cclist, NULL, NULL, sprintf(gettext('%1$s password reset request mail failed.'), $user['user']));
							if (empty($err_msg)) {
								$_zp_login_error = 2;
							} else {
								debugLog($err_msg);
								$_zp_login_error = gettext('Reset request failed.');
							}
						}
					} else {
						$_zp_login_error = gettext('Your input did not match the captcha');
						$_REQUEST['logon_step'] = 'captcha';
					}
					break;
			}
		}
		return $_zp_loggedin;
	}

	/**
	 *
	 * returns an array of the active "password" cookies
	 *
	 * NOTE: this presumes the general form of an authrization cookie is:
	 * zp_xxxxx_auth{_dddd) where xxxxx is the authority (e.g. gallery, image, search, ...)
	 * and dddd if present is the object id.
	 *
	 */
	static function getAuthCookies() {
		$candidates = array();
		if (isset($_COOKIE)) {
			$candidates = $_COOKIE;
		}
		if (isset($_SESSION)) {
			$candidates = array_merge($candidates, $_SESSION);
		}
		foreach ($candidates as $key => $candidate) {
			if (strpos($key, '_auth') === false) {
				unset($candidates[$key]);
			}
		}
		return $candidates;
	}

	/**
	 * Cleans up on logout
	 *
	 */
	static function handleLogout($location) {
		global $_zp_loggedin, $_zp_pre_authorization, $_zp_current_admin_obj;
		$location = zp_apply_filter('zp_logout', $location, $_zp_current_admin_obj);
		foreach (self::getAuthCookies() as $cookie => $value) {
			zp_clearCookie($cookie);
		}
		zp_clearCookie('zenphoto_ssl');
		$_zp_loggedin = false;
		$_zp_pre_authorization = array();
		zp_session_destroy();
		return $location;
	}

	/**
	 * Checks saved cookies to see if a user is logged in
	 */
	function checkCookieCredentials() {
		list($auth, $id) = explode('.', zp_getCookie('zp_user_auth') . '.');
		$loggedin = $this->checkAuthorization($auth, (int) $id);
		$loggedin = zp_apply_filter('authorization_cookie', $loggedin, $auth, $id);
		if ($loggedin) {
			return $loggedin;
		}
		zp_clearCookie("zp_user_auth");
		return NULL;
	}

	/**
	 * Print the login form for ZP. This will take into account whether mod_rewrite is enabled or not.
	 *
	 * @param string $redirect URL to return to after login
	 * @param bool $logo set to true to display the ADMIN zenphoto logo.
	 * @param bool $showUserField set to true to display the user input
	 * @param bool [deprecated] $deprecated set to false to not display the forgot password captcha.
	 * @param string $hint optional hint for the password
	 *
	 */
	function printLoginForm($redirect = null, $logo = true, $showUserField = true, $deprecated = NULL, $hint = '') {
		global $_zp_login_error, $_zp_captcha, $_zp_gallery;
		if (is_null($redirect)) {
			$redirect = getRequestURI();
		}
		if (is_null($showUserField)) {
			$showUserField = $_zp_gallery->getUserLogonField();
		}

		$cycle = sanitize_numeric(@$_GET['cycle']) + 1;
		if (isset($_POST['user'])) {
			$requestor = sanitize($_POST['user'], 0);
		} else {
			$requestor = '';
		}
		if (empty($requestor)) {
			if (isset($_GET['ref'])) {
				$requestor = sanitize($_GET['ref']);
			}
		}
		$alt_handlers = zp_apply_filter('alt_login_handler', array());
		ksort($alt_handlers);

		$star = false;
		$mails = array();
		$info = array('challenge' => '', 'response' => '');
		if (!empty($requestor)) {
			if ($admin = $this->getAnAdmin(array('`user`=' => $requestor, '`valid`=' => 1))) {
				$info = $admin->getChallengePhraseInfo();
			} else {
				$info = array('challenge' => '');
			}
			if (empty($info['challenge']) || ($cycle > 2 && ($cycle % 5) != 1)) {
				$locale = getUserLocale();
				$questions = array();
				foreach (getSerializedArray(getOption('challenge_foils')) as $question) {
					$questions[] = get_language_string($question);
				}
				$rslt = query('SELECT `challenge_phrase`,`language` FROM ' . prefix('administrators') . ' WHERE `challenge_phrase` IS NOT NULL');
				while ($row = db_fetch_assoc($rslt)) {
					if (is_null($row['language']) || $row['language'] == $locale) {
						$q = getSerializedArray($row['challenge_phrase']);
						$questions[] = $q['challenge'];
					}
				}
				db_free_result($rslt);
				$questions = array_unique($questions);
				if (!empty($questions)) {
					shuffle($questions);
					$info = array('challenge' => $questions[$cycle % count($questions)], 'response' => 0x00);
				}
			} else {
				if ($admin->getEmail()) {
					$star = true;
				}
			}
		}
		if (!$star) {
			$admins = $this->getAdministrators();
			while (count($admins) > 0) {
				$user = array_shift($admins);
				if ($user['email']) {
					$star = true;
				}
			}
		}
		$whichForm = sanitize(@$_REQUEST['logon_step']);
		?>

		<div id="loginform">
			<?php
			if ($logo) {
				?>
				<p>
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/zen-logo.png" title="ZenPhoto" alt="ZenPhoto" />
				</p>
				<?php
			}
			switch ($_zp_login_error) {
				case 1:
					?>
					<div class="errorbox" id="message"><h2><?php echo gettext("There was an error logging in."); ?></h2>
						<?php
						if ($showUserField) {
							echo gettext("Check your username and password and try again.");
						} else {
							echo gettext("Check password and try again.");
						}
						?>
					</div>
					<?php
					break;
				case 2:
					?>
					<div class="messagebox fade-message">
						<h2><?php echo gettext("A reset request has been sent."); ?></h2>
					</div>
					<?php
					break;
				default:
					if (!empty($_zp_login_error)) {
						?>
						<div class="errorbox fade-message">
							<h2><?php echo $_zp_login_error; ?></h2>
						</div>
						<?php
					}
					break;
			}

			switch ($whichForm) {
				case 'challenge':
					?>
					<form name="login" action="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.php" method="post">
						<fieldset id="logon_box">
							<input type="hidden" name="login" value="1" />
							<input type="hidden" name="password" value="challenge" />
							<input type="hidden" name="redirect" value="<?php echo html_encode(pathurlencode($redirect)); ?>" />
							<fieldset>
								<legend><?php echo gettext('User') ?></legend>
								<input class="textfield" name="user" id="user" type="text" size="35" value="<?php echo html_encode($requestor); ?>" />
							</fieldset>
							<?php
							if ($requestor) {
								?>
								<p class="logon_form_text"><?php echo gettext('Supply the correct response to the question below and you will be directed to a page where you can change your password.'); ?></p>
								<fieldset><legend><?php echo gettext('Challenge question:') ?></legend>
									<?php
									echo html_encode($info['challenge']);
									?>
								</fieldset>
								<fieldset><legend><?php echo gettext('Your response') ?></legend>
									<input class="textfield" name="pass" id="pass" type="text" size="35" />
								</fieldset>
								<br />
								<?php
							} else {
								?>
								<p class="logon_form_text">
									<?php
									echo gettext('Enter your User ID and press <code>Refresh</code> to get your challenge question.');
									?>
								</p>
								<?php
							}
							?>
							<div class="buttons">
								<button type="submit" value="<?php echo gettext("Submit"); ?>"<?php if (!$info['challenge']) echo ' disabled="disabled"'; ?> >
									<?php echo CHECKMARK_GREEN; ?>
									<?php echo gettext("Submit"); ?>
								</button>
								<button type="button" value="<?php echo gettext("Refresh"); ?>" id="challenge_refresh" onclick="window.location = '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.php?logon_step=challenge&amp;ref=' + $('#user').val();" >
									<?php echo CLOCKWISE_OPEN_CIRCLE_ARROW_GREEN; ?>
									<?php echo gettext("Refresh"); ?>
								</button>
								<button type="button" value="<?php echo gettext("Return"); ?>" onclick="window.location = '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.php?logon_step=&amp;ref=' + $('#user').val();" >
									<?php echo BACK_ARROW_BLUE; ?>
									<?php echo gettext("Return"); ?>
								</button>
							</div>
							<br class="clearall">
						</fieldset>
						<br />
						<?php
						if ($star) {
							?>
							<p class="logon_link">
								<a onclick="window.location = '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.php?logon_step=captcha&amp;ref=' + $('#user').val();" >
									<?php echo gettext('Request reset by e-mail'); ?>
								</a>
							</p>
							<?php
						}
						?>
					</form>
					<?php
					break;
				default:
					Zenphoto_Authority::printPasswordFormJS();
					if (empty($alt_handlers)) {
						$legend = gettext('Login');
					} else {
						?>
						<script type="text/javascript">
							// <!-- <![CDATA[
							var handlers = [];
					<?php
					$list = '<select id="logon_choices" onchange="changeHandler(handlers[$(this).val()]);">' .
									'<option value="0">' . html_encode(get_language_string($_zp_gallery->getTitle())) . "</option>\n";
					$c = 0;
					foreach ($alt_handlers as $handler => $details) {
						$c++;
						$details['params'][] = 'redirect=' . $redirect;
						if (!empty($requestor)) {
							$details['params'][] = 'requestor=' . $requestor;
						}
						echo "handlers[" . $c . "]=['" . $details['script'] . "','" . implode("','", $details['params']) . "'];\n";

						$list .= '<option value="' . $c . '">' . $handler . "</option>\n";
					}
					$list .= '</select>';
					$legend = sprintf(gettext('Logon using:%s'), $list);
					?>
							function changeHandler(handler) {
								handler.push('user=' + $('#user').val());
								var script = handler.shift();
								window.location = script + '?' + handler.join('&');
							}
							// ]]> -->
						</script>
						<?php
					}
					$redirect = zp_apply_filter('login_redirect_link', $redirect);
					?>
					<form name="login" action="<?php echo html_encode(pathurlencode($redirect)); ?>" method="post">
						<input type="hidden" name="login" value="1" />
						<input type="hidden" name="password" value="1" />
						<input type="hidden" name="redirect" value="<?php echo html_encode(pathurlencode($redirect)); ?>" />
						<fieldset id="logon_box"><legend><?php echo $legend; ?></legend>
							<?php
							if ($showUserField) { //	requires a "user" field
								?>
								<fieldset><legend><?php echo gettext("User"); ?></legend>
									<input class="textfield" name="user" id="user" type="text" size="35" value="<?php echo html_encode($requestor); ?>" />
								</fieldset>
								<?php
							}
							?>
							<label class="show_checkbox">
								<input type="checkbox" name="disclose_password" id="disclose_password" onclick="togglePassword('');" />
								<?php echo gettext('Show') ?>
							</label>
							<fieldset><legend><?php echo gettext("Password"); ?></legend>
								<input class="textfield" name="pass" id="pass" type="password" size="35" />
							</fieldset>
							<br />
							<div class="buttons">
								<button type="submit" value="<?php echo gettext("Log in"); ?>" >
									<?php echo CHECKMARK_GREEN; ?>
									<?php echo gettext("Log in"); ?>
								</button>
								<button type="reset" value="<?php echo gettext("Reset"); ?>" >
									<?php echo CROSS_MARK_RED; ?>
									<?php echo gettext("Reset"); ?>
								</button>
							</div>
							<br class="clearall">
						</fieldset>
					</form>

					<?php
					if ($hint) {
						echo '<p>' . $hint . '</p>';
					}
					if ($showUserField && OFFSET_PATH != 2) {
						if (getOption('challenge_foil_enabled')) {
							?>
							<p class="logon_link">
								<a onclick="window.location = '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.php?logon_step=challenge&amp;ref=' + $('#user').val();" >
									<?php echo gettext('I forgot my <strong>User ID</strong>/<strong>Password</strong>'); ?>
								</a>
							</p>
							<?php
						} else {
							if ($star) {
								?>
								<p class="logon_link">
									<a onclick="window.location = '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.php?logon_step=captcha&amp;ref=' + $('#user').val();" >
										<?php echo gettext('I forgot my <strong>User ID</strong>/<strong>Password</strong>'); ?>
									</a>
								</p>
								<?php
							}
						}
					}
					break;
				case 'captcha':
					$captcha = $_zp_captcha->getCaptcha(NULL);
					?>
					<script type="text/javascript">
						function toggleSubmit() {
							if ($('#user').val()) {
								$('#submitButton').removeAttr('disabled');
							} else {
								$('#submitButton').prop('disabled', 'disabled');
							}
						}
					</script>
					<form name="login" action="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.php" method="post">
						<?php if (isset($captcha['hidden'])) echo $captcha['hidden']; ?>
						<input type="hidden" name="login" value="1" />
						<input type="hidden" name="password" value="captcha" />
						<input type="hidden" name="redirect" value="<?php echo html_encode(pathurlencode($redirect)); ?>" />
						<fieldset id="logon_box">
							<fieldset><legend><?php echo gettext('User name or e-mail address'); ?></legend>
								<input class="textfield" name="user" id="user" type="text" value="<?php echo html_encode($requestor); ?>" onkeyup="toggleSubmit();"/>
							</fieldset>
							<?php if (isset($captcha['html'])) echo $captcha['html']; ?>
							<?php
							if (isset($captcha['input'])) {
								?>
								<fieldset><legend><?php echo gettext("Enter CAPTCHA"); ?></legend>
									<?php echo $captcha['input']; ?>
								</fieldset>
								<?php
							}
							?>
							<br />
							<div class="buttons">
								<button type="submit"<?php if (empty($requestor)) echo ' disabled="disabled"'; ?>  id="submitButton" value="<?php echo gettext("Request"); ?>" >
									<?php echo CHECKMARK_GREEN; ?>
									<?php echo gettext("Request password reset"); ?>
								</button>
								<button type="button" value="<?php echo gettext("Return"); ?>" onclick="window.location = '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin.php?logon_step=&amp;ref=' + $('#user').val();" >
									<?php echo BACK_ARROW_BLUE; ?>
									<?php echo gettext("Return"); ?>
								</button>
							</div>
							<br class="clearall">
						</fieldset>
					</form>
					<?php
					break;
			}
			?>
		</div>
		<?php
	}

	static function loginButton() {
		if (!zp_loggedin()) {
			?>
			<span class="button">
				<a href="<?php echo FULLWEBPATH . '/' . ZENFOLDER; ?>/zenphotoLogin.php?request=zenphoto&amp;redirect=/dev/index.php?userlog=1">
					<img src="<?php echo FULLWEBPATH . '/' . ZENFOLDER; ?>/images/login_button.png" alt="login">
				</a>
			</span>
			<?php
		}
	}

	/**
	 *
	 * Javascript for password change input handling
	 */
	static function printPasswordFormJS($all = false) {
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
		<?php
		if (OFFSET_PATH || $all) {
			?>
				function passwordStrength(id) {
					var inputa = '#pass' + id;
					var inputb = '#pass_r' + id;
					var displaym = '#match' + id;
					var displays = '#strength' + id;
					var numeric = 0;
					var special = 0;
					var upper = 0;
					var lower = 0;
					var str = $(inputa).val();
					var len = str.length;
					var strength = 0;
					for (c = 0; c < len; c++) {
						if (str[c].match(/[0-9]/)) {
							numeric++;
						} else if (str[c].match(/[^A-Za-z0-9]/)) {
							special++;
						} else if (str[c].toUpperCase() == str[c]) {
							upper++;
						} else {
							lower++;
						}
					}
					if (upper != len) {
						upper = upper * 2;
					}
					if (lower == len) {
						lower = lower * 0.75;
					}
					if (numeric != len) {
						numeric = numeric * 4;
					}
					if (special != len) {
						special = special * 5;
					}
					len = Math.max(0, (len - 6) * .35);
					strength = Math.min(30, Math.round(upper + lower + numeric + special + len));
					if (str.length == 0) {
						$(displays).css('color', 'black');
						$(displays).html('<?php echo gettext('Password'); ?>');
						$(inputa).css('background-image', 'none');
					} else {
						if (strength < 15) {
							$(displays).css('color', '#ff0000');
							$(displays).html('<?php echo gettext('password strength weak'); ?>');
						} else if (strength < 25) {
							$(displays).css('color', '#ff0000');
							$(displays).html('<?php echo gettext('password strength good'); ?>');
						} else {
							$(displays).css('color', '#008000');
							$(displays).html('<?php echo gettext('password strength strong'); ?>');
						}
						if (strength < <?php echo (int) getOption('password_strength'); ?>) {
							$(inputb).prop('disabled', true);
							$(displays).css('color', '#ff0000');
							$(displays).html('<?php echo gettext('password strength too weak'); ?>');
						} else {
							$(inputb).parent().removeClass('ui-state-disabled');
							$(inputb).prop('disabled', false);
							passwordMatch(id);
						}
						var url = 'url(<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/strengths/strength' + strength + '.png)';
						$(inputa).css('background-image', url);
						$(inputa).css('background-size', '100%');
					}
				}

				function passwordMatch(id) {
					var inputa = '#pass' + id;
					var inputb = '#pass_r' + id;
					var display = '#match' + id;
					if ($('#disclose_password' + id).prop('checked')) {
						if ($(inputa).val() === $(inputb).val()) {
							if ($(inputa).val().trim() !== '') {
								$(display).css('color', '#008000');
								$(display).html('<?php echo gettext('passwords match'); ?>');
							}
						} else {
							$(display).css('color', '#ff0000');
							$(display).html('<?php echo gettext('passwords do not match'); ?>');
						}
					}
				}

				function passwordClear(id) {
					var inputa = '#pass' + id;
					var inputb = '#pass_r' + id;
					if ($(inputa).val().trim() === '') {
						$(inputa).val('');
					}
					if ($(inputb).val().trim() === '') {
						$(inputb).val('');
					}
				}
			<?php
		}
		?>
			function togglePassword(id) {
				if ($('#pass' + id).attr('type') == 'password') {
					var oldp = $('#pass' + id);
					var newp = oldp.clone();
					newp.attr('type', 'text');
					newp.insertAfter(oldp);
					oldp.remove();
					$('.password_field_' + id).hide();
				} else {
					var oldp = $('#pass' + id);
					var newp = oldp.clone();
					newp.attr('type', 'password');
					newp.insertAfter(oldp);
					oldp.remove();
					$('.password_field_' + id).show();
				}
			}
			// ]]> -->
		</script>
		<?php
	}

	static function printPasswordForm($id = '', $pad = false, $disable = NULL, $required = false, $flag = '') {
		if ($pad) {
			$x = '          ';
		} else {
			$x = '';
		}
		?>
		<input type="hidden" name="passrequired<?php echo $id; ?>" id="passrequired-<?php echo $id; ?>" value="<?php echo (int) $required; ?>" class="inputbox"/>
		<p>
			<label for="pass<?php echo $id; ?>" id="strength<?php echo $id; ?>"><?php echo gettext("Password") . $flag; ?>
			</label>
			<label for="disclose_password<?php echo $id; ?>" class="floatright" style="padding-right: 15px;"><?php echo gettext('Show'); ?></label>
			<input class="floatright"
						 type="checkbox"
						 name="disclose_password<?php echo $id; ?>"
						 class="disclose_password"
						 id="disclose_password<?php echo $id; ?>"
						 onclick="passwordClear('<?php echo $id; ?>');
										 togglePassword('<?php echo $id; ?>');">
			<input type="password" size="<?php echo TEXT_INPUT_SIZE; ?>"
						 name="pass<?php echo $id ?>" value="<?php echo $x; ?>"
						 id="pass<?php echo $id; ?>"
						 onchange="$('#passrequired-<?php echo $id; ?>').val(1);"
						 onclick="passwordClear('<?php echo $id; ?>');"
						 onkeyup="passwordStrength('<?php echo $id; ?>');"
						 <?php echo $disable; ?> class="inputbox"/>

		</p>
		<p class="password_field password_field_<?php echo $id; ?>">
			<label for="pass_r<?php echo $id; ?>" id="match<?php echo $id; ?>"><?php echo gettext("Repeat password") . $flag; ?></label>
			<input type="password" size="<?php echo TEXT_INPUT_SIZE; ?>"
						 name="pass_r<?php echo $id ?>" value="<?php echo $x; ?>"
						 id="pass_r<?php echo $id; ?>"
						 disabled="disabled"
						 onchange="$('#passrequired-<?php echo $id; ?>').val(1);"
						 onkeydown="passwordClear('<?php echo $id; ?>');"
						 onkeyup="passwordMatch('<?php echo $id; ?>');" class="inputbox"/>
		</p>
		<?php
	}

	/** PBKDF2 Implementation (described in RFC 2898)
	 *
	 *  @param string p password
	 *  @param string s salt
	 *  @param int c iteration count (use 1000 or higher)
	 *  @param int kl derived key length
	 *  @param string a hash algorithm
	 *
	 *  @return string derived key
	 */
	static function pbkdf2($p, $s, $c = 1000, $kl = 32, $a = 'sha256') {
		$hl = strlen(hash($a, null, true)); # Hash length
		$kb = ceil($kl / $hl); # Key blocks to compute
		$dk = ''; # Derived key
		# Create key
		for ($block = 1; $block <= $kb; $block++) {
			# Initial hash for this block
			$ib = $b = hash_hmac($a, $s . pack('N', $block), $p, true);
			# Perform block iterations
			for ($i = 1; $i < $c; $i++)
			# XOR each iterate
				$ib ^= ($b = hash_hmac($a, $b, $p, true));
			$dk .= $ib; # Append iterated block
		}
		# Return derived key of correct length
		return substr($dk, 0, $kl);
	}

}

class _Administrator extends PersistentObject {

	/**
	 * This is a simple class so that we have a convienient "handle" for manipulating Administrators.
	 *
	 * NOTE: one should use the Zenphoto_Authority newAdministrator() method rather than directly instantiating
	 * an administrator object
	 *
	 */
	var $objects = NULL;
	var $master = false; //	will be set to true if this is the inherited master user
	var $msg = NULL; //	a means of storing error messages from filter processing
	var $logout_link = true; // for a zenphoto logout
	var $reset = false; // if true the user was setup by a "reset password" event
	var $passhash; // the hash algorithm used in creating the password

	/**
	 * Constructor for an Administrator
	 *
	 * @param string $user.
	 * @param int $valid used to signal kind of admin object
	 * @return Administrator
	 */

	function __construct($user, $valid, $create = true) {
		global $_zp_authority;
		$this->passhash = (int) getOption('strong_hash');
		$this->instantiate('administrators', array('user' => $user, 'valid' => $valid), NULL, false, empty($user), $create);
		if (empty($user)) {
			$this->set('id', -1);
		}
		if ($valid) {
			$rights = $this->getRights();
			$new_rights = 0;
			if ($_zp_authority->isMasterUser($user)) {
				$new_rights = ALL_RIGHTS;
				$this->master = true;
			} else {
				// make sure that the "hidden" gateway rights are set for managing objects
				if ($rights & MANAGE_ALL_ALBUM_RIGHTS) {
					$new_rights = $new_rights | ALBUM_RIGHTS;
				}
				if ($rights & MANAGE_ALL_NEWS_RIGHTS) {
					$new_rights = $new_rights | ZENPAGE_PAGES_RIGHTS;
				}
				if ($rights & MANAGE_ALL_PAGES_RIGHTS) {
					$new_rights = $new_rights | ZENPAGE_NEWS_RIGHTS;
				}
				$this->getObjects();
				foreach ($this->objects as $object) {
					switch ($object['type']) {
						case 'album':
							if ($object['edit'] & MANAGED_OBJECT_RIGHTS_EDIT) {
								$new_rights = $new_rights | ALBUM_RIGHTS;
							}
							break;
						case 'pages':
							if ($object['edit'] & MANAGED_OBJECT_RIGHTS_EDIT) {
								$new_rights = $new_rights | ZENPAGE_PAGES_RIGHTS;
							}
							break;
						case 'news':
							if ($object['edit'] & MANAGED_OBJECT_RIGHTS_EDIT) {
								$new_rights = $new_rights | ZENPAGE_NEWS_RIGHTS;
							}
							break;
					}
				}
			}
			if ($new_rights) {
				$this->setRights($rights | $new_rights);
			}
		}
	}

	/**
	 * Returns the unformatted date
	 *
	 * @return date
	 */
	function getDateTime() {
		$d = $this->get('date');
		if ($d && $d != '0000-00-00 00:00:00') {
			return $d;
		}
		return false;
	}

	/**
	 * Stores the date
	 *
	 * @param string $datetime formatted date
	 */
	function setDateTime($datetime) {
		$this->set('date', $datetime);
	}

	function getID() {
		return $this->get('id');
	}

	/**
	 * Hashes and stores the password
	 * @param $pwd
	 */
	function setPass($pwd) {
		$hash_type = getOption('strong_hash');
		$pwd = Zenphoto_Authority::passwordHash($this->getUser(), $pwd, $hash_type);
		$this->set('pass', $pwd);
		$this->set('passupdate', date('Y-m-d H:i:s'));
		$this->set('passhash', $hash_type);
		return $pwd;
	}

	/**
	 * Returns stored password hash
	 */
	function getPass() {
		return $this->get('pass');
	}

	/**
	 * Stores the user name
	 */
	function setName($admin_n) {
		$this->set('name', $admin_n);
	}

	/**
	 * Returns the user name
	 */
	function getName() {
		return $this->get('name');
	}

	/**
	 * Stores the user email
	 */
	function setEmail($admin_e) {
		$this->set('email', $admin_e);
	}

	/**
	 * Returns the user email
	 */
	function getEmail() {
		return $this->get('email');
	}

	/**
	 * Stores user rights
	 */
	function setRights($rights) {
		$this->set('rights', $rights);
	}

	/**
	 * Returns user rights
	 */
	function getRights() {
		return $this->get('rights');
	}

	/**
	 * Stores local copy of managed objects.
	 * NOTE: The database is NOT updated by this, the user object MUST be saved to
	 * cause an update
	 */
	function setObjects($objects) {
		$this->objects = $objects;
	}

	/**
	 * Returns local copy of managed objects.
	 */
	function getObjects($what = NULL, $full = NULL) {
		if (is_null($this->objects)) {
			if ($this->transient) {
				$this->objects = array();
			} else {
				$this->objects = populateManagedObjectsList(NULL, $this->getID());
			}
		}
		if (empty($what)) {
			return $this->objects;
		}
		$result = array();
		foreach ($this->objects as $object) {
			if ($object['type'] == $what) {
				if ($full) {
					$result[$object['data']] = $object;
				} else {
					$result[$object['name']] = $object['data'];
				}
			}
		}
		return $result;
	}

	/**
	 * Sets the "valid" flag. Valid is 1 for users, 0 for groups and templates
	 */
	function setValid($valid) {
		$this->set('valid', $valid);
	}

	/**
	 * Returns the valid flag
	 */
	function getValid() {
		return $this->get('valid');
	}

	/**
	 * Sets the user's group.
	 * NOTE this does NOT set rights, etc. that must be done separately
	 */
	function setGroup($group) {
		$this->set('group', $group);
	}

	/**
	 * Returns user's group
	 */
	function getGroup() {
		return $this->get('group');
	}

	/**
	 * Sets the user's user id
	 */
	function setUser($user) {
		$this->set('user', $user);
	}

	/**
	 * Returns user's user id
	 */
	function getUser() {
		return $this->get('user');
	}

	/**
	 * Sets the users quota
	 */
	function setQuota($v) {
		$this->set('quota', $v);
	}

	/**
	 * Returns the users quota
	 */
	function getQuota() {
		return $this->get('quota');
	}

	/**
	 * Returns the user's prefered language
	 */
	function getLanguage() {
		return $this->get('language');
	}

	/**
	 * Sets the user's preferec language
	 */
	function setLanguage($locale) {
		$this->set('language', $locale);
	}

	/**
	 * Uptates the database with all changes
	 */
	function save() {
		global $_zp_gallery;
		if (DEBUG_LOGIN) {
			debugLogVar("Zenphoto_Administrator->save()", $this);
		}
		$objects = $this->getObjects();
		if (is_null($this->get('date'))) {
			$this->set('date', date('Y-m-d H:i:s'));
		}
		parent::save();

		$id = $this->getID();
		if (is_array($objects)) {
			$sql = "DELETE FROM " . prefix('admin_to_object') . ' WHERE `adminid`=' . $id;
			$result = query($sql, false);
			foreach ($objects as $object) {
				$edit = MANAGED_OBJECT_MEMBER;
				if (array_key_exists('edit', $object)) {
					$edit = $object['edit'] | MANAGED_OBJECT_MEMBER;
				}
				switch ($object['type']) {
					case 'album':
						$album = newAlbum($object['data']);
						$albumid = $album->getID();
						$sql = "INSERT INTO " . prefix('admin_to_object') . " (adminid, objectid, type, edit) VALUES ($id, $albumid, 'albums', $edit)";
						$result = query($sql);
						break;
					case 'pages':
						$sql = 'SELECT * FROM ' . prefix('pages') . ' WHERE `titlelink`=' . db_quote($object['data']);
						$result = query_single_row($sql);
						if (is_array($result)) {
							$objectid = $result['id'];
							$sql = "INSERT INTO " . prefix('admin_to_object') . " (adminid, objectid, type, edit) VALUES ($id, $objectid, 'pages', $edit)";
							$result = query($sql);
						}
						break;
					case 'news':
						if ($object['data'] == '`') {
							$result = array('id' => 0);
						} else {
							$sql = 'SELECT * FROM ' . prefix('news_categories') . ' WHERE `titlelink`=' . db_quote($object['data']);
							$result = query_single_row($sql);
						}
						if (is_array($result)) {
							$objectid = $result['id'];
							$sql = "INSERT INTO " . prefix('admin_to_object') . " (adminid, objectid, type, edit) VALUES ($id, $objectid, 'news', $edit)";
							$result = query($sql);
						}
						break;
				}
			}
		}
	}

	/**
	 * Removes a user from the system
	 */
	function remove() {
		zp_apply_filter('remove_user', $this);
		$album = $this->getAlbum();
		$id = $this->getID();
		if (parent::remove()) {
			if (!empty($album)) { //	Remove users album as well
				$album->remove();
			}
			$sql = "DELETE FROM " . prefix('admin_to_object') . " WHERE `adminid`=$id";
			$result = query($sql);
		} else {
			return false;
		}
		return $result;
	}

	/**
	 * Returns the user's "prime" album. See setAlbum().
	 */
	function getAlbum() {
		$id = $this->get('prime_album');
		if (!empty($id)) {
			$sql = 'SELECT `folder` FROM ' . prefix('albums') . ' WHERE `id`=' . $id;
			$result = query_single_row($sql);
			if ($result) {
				$album = newAlbum($result['folder']);
				return $album;
			}
		}
		return false;
	}

	/**
	 * Records the "prime album" of a user. Prime albums are linked to the user and
	 * removed if the user is removed.
	 */
	function setAlbum($album) {
		if ($album) {
			$this->set('prime_album', $album->getID());
		} else {
			$this->set('prime_album', NULL);
		}
	}

	/**
	 * Data to support other credential systems integration
	 */
	function getCredentials() {
		return getSerializedArray($this->get('other_credentials'));
	}

	function setCredentials($cred) {
		$this->set('other_credentials', serialize($cred));
	}

	/**
	 * Creates a "prime" album for the user. Album name is based on the userid
	 */
	function createPrimealbum($new = true, $name = NULL) {
		//	create his album
		$t = 0;
		$ext = '';
		if (is_null($name)) {
			$filename = internalToFilesystem(str_replace(array('<', '>', ':', '"' . '/' . '\\', '|', '?', '*'), '_', seoFriendly($this->getUser())));
		} else {
			$filename = internalToFilesystem(str_replace(array('<', '>', ':', '"' . '/' . '\\', '|', '?', '*'), '_', $name));
		}
		while ($new && file_exists(ALBUM_FOLDER_SERVERPATH . $filename . $ext)) {
			$t++;
			$ext = '-' . $t;
		}
		$path = ALBUM_FOLDER_SERVERPATH . $filename . $ext;
		$albumname = filesystemToInternal($filename . $ext);
		if (@mkdir_recursive($path, FOLDER_MOD)) {
			$album = newAlbum($albumname);
			if ($title = $this->getName()) {
				$album->setTitle($title);
			}
			$album->setOwner($this->getUser());
			$album->save();
			$this->setAlbum($album);
			$this->setRights($this->getRights() | ALBUM_RIGHTS);
			if (getOption('user_album_edit_default')) {
				$subrights = MANAGED_OBJECT_RIGHTS_EDIT;
			} else {
				$subrights = 0;
			}
			if ($this->getRights() & UPLOAD_RIGHTS) {
				$subrights = $subrights | MANAGED_OBJECT_RIGHTS_UPLOAD;
			}
			$objects = $this->getObjects();
			$objects[] = array('data' => $albumname, 'name' => $albumname, 'type' => 'album', 'edit' => $subrights);
			$this->setObjects($objects);
		}
	}

	function getChallengePhraseInfo() {
		$info = $this->get('challenge_phrase');
		if ($info) {
			return getSerializedArray($info);
		} else {
			return array('challenge' => '', 'response' => '');
		}
	}

	function setChallengePhraseInfo($challenge, $response) {
		$this->set('challenge_phrase', serialize(array('challenge' => $challenge, 'response' => $response)));
	}

	/**
	 *
	 * returns the last time the user has logged on
	 */
	function getLastLogon() {
		return $this->get('lastloggedin');
	}

}
?>
