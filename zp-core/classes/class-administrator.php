<?php

/**
 * This is a simple class so that we have a convienient "handle" for manipulating Administrators.
 *
 * NOTE: one should use the Authority::newAdministrator() method rather than directly instantiating
 * an administrator object
 * @package zpcore\classes\authorization
 */
class Administrator extends PersistentObject {

	public $objects = NULL;
	public $master = false; //	will be set to true if this is the inherited master user
	public $msg = NULL; //	a means of storing error messages from filter processing
	public $logout_link = true; // for a Zenphoto logout
	public $reset = false; // if true the user was setup by a "reset password" event
	public $passhash; // the hash algorithm used in creating the password

	/**
	 * Constructor for an Administrator
	 *
	 * @param string $user.
	 * @param int $valid used to signal kind of admin object
	 * @return Administrator
	 */
	function __construct($user, $valid) {
		global $_zp_authority;
		$this->passhash = (int) getOption('strong_hash');
		$this->instantiate('administrators', array('user' => $user, 'valid' => $valid), NULL, false, empty($user));
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
							if ($object['edit'] && MANAGED_OBJECT_RIGHTS_EDIT) {
								$new_rights = $new_rights | ALBUM_RIGHTS;
							}
							break;
						case 'pages':
							$new_rights = $new_rights | ZENPAGE_PAGES_RIGHTS;
							break;
						case 'news':
							$new_rights = $new_rights | ZENPAGE_NEWS_RIGHTS;
							break;
					}
				}
			}
			if($this->getGroup()) {
				$this->preservePrimeAlbum();
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
		return $this->get('date');
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
		$pwd = Authority::passwordHash($this->getUser(), $pwd, $hash_type);
		$this->set('pass', $pwd);
		$this->set('passupdate', date('Y-m-d H:i:s'));
		$this->set('passhash', $hash_type);
		return $this->get('pass');
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
	 * Gets the full name if set 
	 * 
	 * @since 1.5.8
	 * 
	 * @param string $user User id 
	 * @return string
	 */
	static function getNameByUser($user) {
		$admin = Authority::getAnAdmin(array('`user`=' => $user, '`valid`=' => 1));
		if (is_object($admin) && $admin->getName()) {
			return $admin->getName();
		}
		return $user;
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
	 * Returns local copy of managed objects.
	 */
	function setObjects($objects) {
		$this->objects = $objects;
	}

	/**
	 * Saves local copy of managed objects.
	 * NOTE: The database is NOT updated by this, the user object MUST be saved to
	 * cause an update
	 */
	function getObjects($what = NULL) {
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
				$result[get_language_string($object['name'])] = $object['data'];
			}
		}
		return $result;
	}

	/**
	 * Stores custom data
	 */
	function setCustomData($custom_data) {
		$this->set('custom_data', $custom_data);
	}

	/**
	 * Returns custom data
	 */
	function getCustomData() {
		return $this->get('custom_data');
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
	 * 
	 * @param bool $checkupdates Default false. If true the internal $updates property is checked for actual changes so unnecessary saving is skipped. Applies to already existing objects only.
	 */
	function save($checkupdates = false) {
		global $_zp_gallery, $_zp_db;
		if (DEBUG_LOGIN) {
			debugLogVar("Administrator->save()", $this);
		}
		$objects = $this->getObjects();
		if (is_null($this->get('date'))) {
			$this->set('date', date('Y-m-d H:i:s'));
		}
		parent::save($checkupdates);
		$id = $this->getID();
		if (is_array($objects)) {
			$sql = "DELETE FROM " . $_zp_db->prefix('admin_to_object') . ' WHERE `adminid`=' . $id;
			$result = $_zp_db->query($sql, false);
			foreach ($objects as $object) {
				if (array_key_exists('edit', $object)) {
					$edit = $object['edit'] | 32767 & ~(MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_UPLOAD | MANAGED_OBJECT_RIGHTS_VIEW);
				} else {
					$edit = 32767;
				}
				switch ($object['type']) {
					case 'album':
						$album = AlbumBase::newAlbum($object['data']);
						$albumid = $album->getID();
						$sql = "INSERT INTO " . $_zp_db->prefix('admin_to_object') . " (adminid, objectid, type, edit) VALUES ($id, $albumid, 'albums', $edit)";
						$result = $_zp_db->query($sql);
						break;
					case 'pages':
						$sql = 'SELECT * FROM ' . $_zp_db->prefix('pages') . ' WHERE `titlelink`=' . $_zp_db->quote($object['data']);
						$result = $_zp_db->querySingleRow($sql);
						if (is_array($result)) {
							$objectid = $result['id'];
							$sql = "INSERT INTO " . $_zp_db->prefix('admin_to_object') . " (adminid, objectid, type, edit) VALUES ($id, $objectid, 'pages', $edit)";
							$result = $_zp_db->query($sql);
						}
						break;
					case 'news':
						$sql = 'SELECT * FROM ' . $_zp_db->prefix('news_categories') . ' WHERE `titlelink`=' . $_zp_db->quote($object['data']);
						$result = $_zp_db->querySingleRow($sql);
						if (is_array($result)) {
							$objectid = $result['id'];
							$sql = "INSERT INTO " . $_zp_db->prefix('admin_to_object') . " (adminid, objectid, type, edit) VALUES ($id, $objectid, 'news', $edit)";
							$result = $_zp_db->query($sql);
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
		global $_zp_db;
		zp_apply_filter('remove_user', $this);
		$album = $this->getAlbum();
		$id = $this->getID();
		if (parent::remove()) {
			if (!empty($album)) { //	Remove users album as well
				$album->remove();
			}
			$sql = "DELETE FROM " . $_zp_db->prefix('admin_to_object') . " WHERE `adminid`=$id";
			$result = $_zp_db->query($sql);
		} else {
			return false;
		}
		return $result;
	}

	/**
	 * Returns the user's "prime" album. See setAlbum().
	 */
	function getAlbum() {
		global $_zp_db;
		$id = $this->get('prime_album');
		if (!empty($id)) {
			$sql = 'SELECT `folder` FROM ' . $_zp_db->prefix('albums') . ' WHERE `id`=' . $id;
			$result = $_zp_db->querySingleRow($sql);
			if ($result) {
				$album = AlbumBase::newAlbum($result['folder']);
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
			$album = AlbumBase::newAlbum($albumname);
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
	
	/**
	 * Returns the last time the user visited the site being loggedin
	 * 
	 * @since 1.5.8
	 * @return strig
	 */
	function getLastVisit() {
		return $this->get('lastvisit');
	}
	
	/**
	 * Sets the last time the user visited the site being loggedin
	 * 
	 * @since 1.5.8
	 */
	function setLastVisit($datetime = '') {
		if(empty($datetime)) {
			$datetime = date('Y-m-d H:i:s');
		}
		$this->set('lastvisit', $datetime);
	}
	
	/**
	 * Updates the last visit date if enabled on the options and the time frame defined has passed.
	 * @since 1.5.8
	 */
	function updateLastVisit() {
		if (getOption('admin_lastvisit')) {
			$lastvisit = strtotime(strval($this->getLastVisit()));
			if ($lastvisit) {
				$lastvisit_timeframe = getOption('admin_lastvisit_timeframe');
				if (empty($lastvisit_timeframe)) {
					$lastvisit_timeframe = 600;
				}
				if (empty($lastvisit) || (time() - $lastvisit) > $lastvisit_timeframe) {
					$this->setLastVisit();
					$this->save();
				}
			}
		}
	}

	/**
	 * Preserves the user's prime album as managed album even if he is in a group the album is actually set as managed
	 */
	function preservePrimeAlbum() {
		$primeAlbum = $this->getAlbum();
		if (is_object($primeAlbum)) {
			$primealbum_name = $primeAlbum->name;
			$objects = $this->getObjects();
			$primealbum_managed = false;
			foreach ($objects as $key => $val) {
				if ($val['type'] == 'album' && $val['name'] == $primealbum_name) {
					$primealbum_managed = true;
					break;
				}
			}
			if (!$primealbum_managed) {
				$objects[] = array(
						'data' => $primealbum_name,
						'name' => $primealbum_name,
						'type' => 'album',
						'edit' => 32765
				);
			}
			$this->setObjects($objects);
		}
	}

}

/**
 * 
 * @package zpcore\classes\deprecated
 * @deprecated 2.0 - Use the class Administrator instead
 */
class Zenphoto_Administrator extends Administrator {
	
	function __construct($user, $valid) {
		parent::__construct($user, $valid);
		deprecationNotice(gettext('Use the Administrator class instead'));
	}
	
}