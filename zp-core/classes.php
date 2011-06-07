<?php
/**
 * root object class
 * @package classes
 */

// force UTF-8 Ø

// classes.php - HEADERS STILL NOT SENT! Do not output text from this file.

/*******************************************************************************
 *******************************************************************************
 * Persistent Object Class *****************************************************
 *
 * Parent ABSTRACT class of all persistent objects. This class should not be
 * instantiated, only used for subclasses. This cannot be enforced, but please
 * follow it!
 *
 * Documentation/Instructions:
 * A child class should run the follwing in its constructor:
 *
 * $new = parent::PersistentObject('tablename',
 *   array('uniquestring'=>$value, 'uniqueid'=>$uniqueid));
 *
 * where 'tablename' is the name of the database table to use for this object
 * type, and array('uniquestring'=>$value, ...) defines a unique set of columns
 * (keys) and their current values which uniquely identifies a single record in
 * that database table for this object. The return value of the constructor
 * (stored in $new in the above example) will be (=== TRUE) if a new record was
 * created, and (=== FALSE) if an existing record was updated. This can then be
 * used to set() default values for NEW objects and save() them.
 *
 * Note: This is a persistable model that does not save automatically. You MUST
 * call $this->save(); explicitly to persist the data in child classes.
 *
 *******************************************************************************
 ******************************************************************************/

// The query cache
$_zp_object_cache = array();
$_zp_object_update_cache = array();


// ABSTRACT
class PersistentObject {

	var $data = NULL;
	var $updates = NULL;
	var $loaded = false;
	var $table;
	var $unique_set = NULL;
	var $cache_by;
	var $id;
	var $use_cache = false;
	var $transient;
	var $tempdata = NULL;

	/**
	 *
	 * Prime instantiator for Zenphoto objects
	 * @param $tablename	The name of the database table
	 * @param $unique_set	An array of unique fields
	 * @param $cache_by
	 * @param $use_cache
	 * @param $is_transient	Set true to prevent database insertion
	 * @param $allowCreate Set true to allow a new object to be made.
	 */
	function PersistentObject($tablename, $unique_set, $cache_by=NULL, $use_cache=true, $is_transient=false, $allowCreate=true) {
		// Initialize the variables.
		// Load the data into the data array using $this->load()
		$this->data = array();
		$this->tempdata = array();
		$this->updates = array();
		$this->loaded = false;
		$this->table = $tablename;
		$this->unique_set = $unique_set;
		$this->cache_by = $cache_by;
		$this->use_cache = $use_cache;
		$this->transient = $is_transient;
		$result = $this->load($allowCreate);
		return $result;
	}


	/**
	* Caches the current set of objects defined by a variable key $cache_by.
	* Uses a global array to store the results of a single database query,
	* where subsequent requests for the object look for data.
	* @return a reference to the array location where this class' cache is stored
	*   indexed by the field $cache_by.
	*/
	function cache($entry=NULL) {
		global $_zp_object_cache;
		if (is_null($this->cache_by)) return false;
		$classname = get_class($this);
		if (!isset($_zp_object_cache[$classname])) {
			$_zp_object_cache[$classname] = array();
		}
		$cache_set = array_diff_assoc($this->unique_set, array($this->cache_by => $this->unique_set[$this->cache_by]));

		// This must be done here; the references do not work returned by a function.
		$cache_location = &$_zp_object_cache[$classname];
		foreach($cache_set as $key => $value) {
			if (!isset($cache_location[$value])) {
				$cache_location[$value] = array();
			}
			$cache_location = &$cache_location[$value];
		}
		// Exit if this object set is already cached.
		if (!empty($cache_location)) {
			return $cache_location;
		}

		if (!is_null($entry)) {
			$key = $entry[$this->cache_by];
			$cache_location[$key] = $entry;
		} else {
			$sql = 'SELECT * FROM ' . prefix($this->table) . getWhereClause($cache_set);
			$result = query($sql);
			if ($result && db_num_rows($result) == 0) return false;

			while ($row = db_fetch_assoc($result)) {
				$key = $row[$this->cache_by];
				$cache_location[$key] = $row;
			}
		}
		return $cache_location;
	}


	/**
	* Set a variable in this object. Does not persist to the database until
	* save() is called. So, IMPORTANT: Call save() after set() to persist.
	* If the requested variable is not in the database, sets it in temp storage,
	* which won't be persisted to the database.
	*/
	function set($var, $value) {
		if (empty($var)) return false;
		if ($this->loaded && !array_key_exists($var, $this->data)) {
			$this->tempdata[$var] = $value;
		} else {
			$this->updates[$var] = $value;
		}
		return true;
	}


	/**
	* Sets default values for new objects using the set() method.
	* Should do nothing in the base class; subclasses should override.
	*/
	function setDefaults() {
		return;
	}

	/**
	* Change one or more values of the unique set assigned to this record.
	* Checks if the record already exists first, if so returns false.
	* If successful returns true and changes $this->unique_set
	* A call to move is instant, it does not require a save() following it.
	*/
	function move($new_unique_set) {
		// Check if we have a row
		$result = query('SELECT * FROM ' . prefix($this->table) .	getWhereClause($new_unique_set) . ' LIMIT 1;');
		if ($result && db_num_rows($result) == 0) {	//	we should not find an entry for the new unique set!
			if (!zp_apply_filter('move_object', true, $this, $new_unique_set)) {
				return false;
			}
			$sql = 'UPDATE ' . prefix($this->table)	. getSetClause($new_unique_set) . ' '	. getWhereClause($this->unique_set);
			$result = query($sql);
			if ($result && db_affected_rows() == 1) {	//	and the update should have effected just one record
				$this->unique_set = $new_unique_set;
				return true;
			}
		}
		return false;
	}

	/**
	 * Copy this record to another unique set. Checks if the record exists there
	 * first, if so returns false. If successful returns true. No changes are made
	 * to this object and no other objects are created, just the database entry.
	 * A call to copy is instant, it does not require a save() following it.
	 */
	function copy($new_unique_set) {
		// Check if we have a row
		$result = query('SELECT * FROM ' . prefix($this->table) .	getWhereClause($new_unique_set) . ' LIMIT 1;');
		if ($result && db_num_rows($result) == 0) {
			if (!zp_apply_filter('copy_object', true, $this, $new_unique_set)) {
				return false;
			}
			// Note: It's important for $new_unique_set to come last, as its values should override.
			$insert_data = array_merge($this->data, $this->updates, $this->tempdata, $new_unique_set);
			unset($insert_data['id']);
			if (empty($insert_data)) { return true; }
			$sql = 'INSERT INTO ' . prefix($this->table) . ' (';
			$i = 0;
			foreach(array_keys($insert_data) as $col) {
				if ($i > 0) $sql .= ", ";
				$sql .= "`$col`";
				$i++;
			}
			$sql .= ') VALUES (';
			$i = 0;
			foreach(array_values($insert_data) as $value) {
				if ($i > 0) $sql .= ', ';
				if (is_null($value)) {
					$sql .= 'NULL';
				} else {
					$sql .=  db_quote($value);
				}
				$i++;
			}
			$sql .= ');';
			$success = query($sql);
			if ($success && db_affected_rows() == 1) {
				return zp_apply_filter('copy_object', db_insert_id(), $this);
			}
		}
		return false;
	}

/**
 * Deletes object from the database
 *
 * @return bool
 */
	function remove() {
		if (!zp_apply_filter('remove_object', true, $this)) {
			return false;
		}
		$id = $this->id;
		if (empty($id)) {
			$id = ' is NULL';	//	allow delete of bad item!
		} else {
			$id = '='.$id;
		}
		$sql = 'DELETE FROM '.prefix($this->table).' WHERE `id`'.$id;
		$this->loaded = false;
		$this->transient = true;
		return query($sql);
	}

	/**
	 * Returns the id
	 *
	 * @return string
	 */
	function getID() {
		return $this->get('id');
	}


	/**
	* Get the value of a variable. If $current is false, return the value
	* as of the last save of this object.
	*/
	function get($var, $current=true) {
		if ($current && isset($this->updates[$var])) {
			return $this->updates[$var];
		} else if (isset($this->data[$var])) {
			return $this->data[$var];
		} else if (isset($this->tempdata[$var])) {
			return $this->tempdata[$var];
		} else {
			return null;
		}
	}

	/**
	* Load the data array from the database, using the unique id set to get the unique record.
	*
	* @param bool $allowCreate set to true to enable new object creation.
	* @return false if the record already exists, true if a new record was created.
	*/
	function load($allowCreate) {
		$new = false;
		$entry = null;
		// Set up the SQL query in case we need it...
		$sql = 'SELECT * FROM ' . prefix($this->table) . getWhereClause($this->unique_set) . ' LIMIT 1;';
		// But first, try the cache.
		if ($this->use_cache) {
			$reporting = error_reporting(0);
			$cache_location = &$this->cache();
			$entry = &$cache_location[$this->unique_set[$this->cache_by]];
			error_reporting($reporting);
		}
		// Re-check the database if: 1) not using cache, or 2) didn't get a hit.
		if (empty($entry)) {
			$entry = query_single_row($sql,false);
		}

		// If we don't have an entry yet, this is a new record. Create it.
		if (empty($entry)) {
			if ($this->transient) { // no don't save it in the DB!
				$entry = array_merge($this->unique_set, $this->updates, $this->tempdata);
				$entry['id'] = '';
			} else if (!$allowCreate) {
				return NULL;	// does not exist and we are not allowed to create it
			} else {
				$new = true;
				$this->save();
				$entry = query_single_row($sql);
				// If we still don't have an entry, something went wrong...
				if (!$entry) return null;
				// Then save this new entry into the cache so we get a hit next time.
				$this->cache($entry);
			}
		}
		$this->data = $entry;
		$this->id = $entry['id'];
		$this->loaded = true;
		return $new;
	}

	/**
	* Save the updates made to this object since the last update. Returns
	* true if successful, false if not.
	*/
	function save() {
		if (!$this->unique_set) { // If we don't have a unique set, then this is incorrect. Don't attempt to save.
			zp_error('empty $this->unique set is empty');
			return;
		}
		if ($this->transient) return; // If this object isn't supposed to be persisted, don't save it.
		if ($this->id == null) {
			$this->setDefaults();
			// Create a new object and set the id from the one returned.
			$insert_data = array_merge($this->unique_set, $this->updates, $this->tempdata);
			if (empty($insert_data)) { return true; }
			$i = 0;
			$cols = $vals = '';
			foreach($insert_data as $col=>$value) {
				if ($i > 0) $cols .= ", ";
				$cols .= "`$col`";
				if ($i > 0) $vals .= ", ";
				if (is_null($value)) {
					$vals .= "NULL";
				} else {
					$vals .= db_quote($value);
				}
				$i++;
			}
			$sql = 'INSERT INTO ' . prefix($this->table).' ('.$cols.') VALUES ('.$vals.')';
			$success = query($sql);
			if (!$success || db_affected_rows() != 1) { return false; }
			foreach ($insert_data as $key=>$value) { // copy over any changes
				$this->data[$key] = $value;
			}
			$this->id = db_insert_id();
			$this->data['id'] = $this->id; // so 'get' will retrieve it!
			$this->loaded = true;
			$this->updates = array();
			$this->tempdata = array();

		} else {
			// Save the existing object (updates only) based on the existing id.
			if (empty($this->updates)) {
				return true;
			} else {
				$sql = 'UPDATE ' . prefix($this->table) . ' SET';
				$i = 0;
				foreach ($this->updates as $col => $value) {
					if ($i > 0) $sql .= ",";
					if (is_null($value)) {
						$sql .= " `$col` = NULL";
					} else {
						$sql .= " `$col` = ". db_quote($value);
					}
					$this->data[$col] = $value;
					$i++;
				}
				$sql .= ' WHERE id=' . $this->id . ';';
				$success = query($sql);
				if (!$success || db_affected_rows() != 1) { return false; }
				foreach ($this->updates as $key=>$value) {
					$this->data[$key] = $value;
				}
				$this->updates = array();
			}
		}
		zp_apply_filter('save_object', true, $this);
		return true;
	}

}

//////////////////////////////////////////////////////////////////////////////////////////////////////
/**
 * The basic ThemeObject class. Extends PersistentObject, is extended by various Theme related objects.
 * Provides some basic methods that all use.
 */

class ThemeObject extends PersistentObject {

	var $comments = NULL;		//Contains an array of the comments of the object
	var $commentcount;			//Contains the number of comments
	var $manage_rights = ADMIN_RIGHTS;
	var $manage_some_rights = ADMIN_RIGHTS;
	var $view_rights = VIEW_ALL_RIGHTS;

	/**
	 * Class instantiator
	 */
	function ThemeObject() {
		// no action required
	}

	/**
	 * Returns the title
	 *
	 * @return string
	 */
	function getTitle() {
		return get_language_string($this->get('title'));
	}

	/**
	 * Stores the title
	 *
	 * @param string $title the title
	 */
	function setTitle($title) { $this->set('title', $title); }

	/**
	 * Returns the partent id
	 *
	 * @return string
	 */
	function getParentID() {
		return $this->get('parentid');
	}

	/**
	 * Sets the ParentID field
	 * @param $v id of the parent
	 */
	function setParentID($v) {
		$this->set('parentid',$v);
	}

	/**
	 * Returns the hitcount
	 *
	 * @return int
	 */
	function getHitcounter() {
		return $this->get('hitcounter');
	}

	/**
	 * counts visits to the object
	 */
	function countHit() {
		$hc = $this->get('hitcounter')+1;
		$this->set('hitcounter', $hc);
		$this->save();
	}

	/**
	 * Returns true published
	 *
	 * @return bool
	 */
	function getShow() {
		return $this->get('show');
	}

	/**
	 * Stores the published value
	 *
	 * @param bool $show True if the album is published
	 */
	function setShow($show) {
		$old_show = $this->get('show');
		$new_show = (int) ($show && true);
		$this->set('show', $new_show);
		if ($old_show != $new_show && $this->get('id')) {
			zp_apply_filter('show_change', $this);
		}
	}

	/**
	 * Returns the tag data
	 *
	 * @return string
	 */
	function getTags() {
		return readTags($this->id, $this->table);
	}

	/**
	 * Stores tag information
	 *
	 * @param string $tags the tag list
	 */
	function setTags($tags) {
		if (!is_array($tags)) {
			$tags = explode(',', $tags);
		}
		storeTags($tags, $this->id, $this->table);
	}

	/**
	 * Checks if an object has a tag assigned.
	 *
	 * @param string $checktag tag to check for
	 *
	 * @return bool
	 */
	function hasTag($checktag) {
		$tags = $this->getTags();
		return in_array($checktag, $tags);
	}

	/**
	 * Returns the unformatted date
	 *
	 * @return int
	 */
	function getDateTime() { return $this->get('date'); }

	/**
	 * Stores the date
	 *
	 * @param string $datetime formatted date
	 */
	function setDateTime($datetime) {
		if ($datetime) {
			$newtime = dateTimeConvert($datetime);
			if ($newtime === false) return;
			$this->set('date', $newtime);
		} else {
			$this->set('date', NULL);
		}
	}

	/**
	 * Returns the codeblocks as an serialized array
	 *
	 * @return array
	 */
	function getCodeblock() {
		return $this->get("codeblock");
	}

	/**
	 * set the codeblocks as an serialized array
	 *
	 */
	function setCodeblock($cb) {
		$this->set("codeblock",$cb);
	}

/**
	 * returns the custom data field
	 *
	 * @return string
	 */
	function getCustomData() {
		return get_language_string($this->get('custom_data'));
	}

	/**
	 * Sets the custom data field
	 *
	 * @param string $val the value to be put in custom_data
	 */
	function setCustomData($val) { $this->set('custom_data', $val); }

	/**
	 * Retuns true if comments are allowed
	 *
	 * @return bool
	 */
	function getCommentsAllowed() { return $this->get('commentson'); }

	/**
	 * Sets the comments allowed flag
	 *
	 * @param bool $commentson true if they are allowed
	 */
	function setCommentsAllowed($commentson) {
		$this->set('commentson', (int) ($commentson && true));
	}

	/**
	 * Returns an array of comments for this album
	 *
	 * @param bool $moderated if false, ignores comments marked for moderation
	 * @param bool $private if false ignores private comments
	 * @param bool $desc set to true for descending order
	 * @return array
	 */
	function getComments($moderated=false, $private=false, $desc=false) {
		$sql = "SELECT *, (date + 0) AS date FROM " . prefix("comments") .
			" WHERE `type`='".$this->table."' AND `ownerid`='" . $this->id . "'";
		if (!$moderated) {
			$sql .= " AND `inmoderation`=0";
		}
		if (!$private) {
			$sql .= " AND `private`=0";
		}
		$sql .= " ORDER BY id";
		if ($desc) {
			$sql .= ' DESC';
		}
		$comments = query_full_array($sql);
		$this->comments = $comments;
		return $this->comments;
	}

	/**
	 * Adds comments to the album
	 * assumes data is coming straight from GET or POST
	 *
	 * Returns a comment object
	 *
	 * @param string $name Comment author name
	 * @param string $email Comment author email
	 * @param string $website Comment author website
	 * @param string $comment body of the comment
	 * @param string $code CAPTCHA code entered
	 * @param string $code_ok CAPTCHA hash expected
	 * @param string $ip the IP address of the comment poster
	 * @param bool $private set to true if the comment is for the admin only
	 * @param bool $anon set to true if the poster wishes to remain anonymous
	 * @return object
	 */
	function addComment($name, $email, $website, $comment, $code, $code_ok, $ip, $private, $anon) {
		$goodMessage = postComment($name, $email, $website, $comment, $code, $code_ok, $this, $ip, $private, $anon);
		return $goodMessage;
	}

	/**
	 * Returns the count of comments in the album. Ignores comments in moderation
	 *
	 * @return int
	 */
	function getCommentCount() {
		if (is_null($this->commentcount)) {
			if ($this->comments == null) {
				$count = query_single_row("SELECT COUNT(*) FROM " . prefix("comments") . " WHERE `type`='".$this->table."' AND `inmoderation`=0 AND `private`=0 AND `ownerid`=" . $this->id);
				$this->commentcount = array_shift($count);
			} else {
				$this->commentcount = count($this->comments);
			}
		}
		return $this->commentcount;
	}

	/**
	 * Checks basic access rights of an object
	 * @param bit $action what the caller wants to do
	 */
	function isMyItem($action) {
		if (zp_loggedin($this->manage_rights)) {
			return true;
		}
		if (zp_loggedin($this->view_rights) && ($action == LIST_RIGHTS)) {	// sees all
			return true;
		}
		if (zp_apply_filter('check_credentials', false, $this, $action)) {
			return true;
		}
		return NULL;
	}

	/**
	 * returns false (deny) if gallery is "private"
	 * @param $hint
	 * @param $show
	 */
	function checkForGuest(&$hint=NULL, &$show=NULL) {
		return !(GALLERY_SECURITY == 'private');
	}

	/**
	 *
	 * Checks if viewing of object is allowed
	 * @param unknown_type $hint
	 * @param unknown_type $show
	 */
	function checkAccess(&$hint=NULL, &$show=NULL) {
		if ($this->isMyItem($this->view_rights)) {
			return true;
		}
		return $this->checkforGuest($hint, $show);
	}

}

//////////////////////////////////////////////////////////////////////////////////////////////////////
/**
 * Root class for images and albums
 *
 */
class MediaObject extends ThemeObject {

	/**
	 * Class instantiator
	 */
	function MediaObject() {
	//	no actions required
	}

	/**
	 * Returns the description
	 *
	 * @return string
	 */
	function getDesc() {
		return get_language_string($this->get('desc'));
	}

	/**
	 * Stores the description
	 *
	 * @param string $desc description text
	 */
	function setDesc($desc) { $this->set('desc', $desc); }

	/**
	 * Returns the sort order
	 *
	 * @return string
	 */
	function getSortOrder() { return $this->get('sort_order'); }

	/**
	 * Stores the sort order
	 *
	 * @param string $sortorder image sort order
	 */
	function setSortOrder($sortorder) { $this->set('sort_order', $sortorder); }

		/**
	 * Returns the guest user
	 *
	 * @return string
	 */
	function getUser() { return $this->get('user');	}

	/**
	 * Sets the guest user
	 *
	 * @param string $user
	 */
	function setUser($user) { $this->set('user', $user);	}

	/**
	 * Returns the password
	 *
	 * @return string
	 */
	function getPassword() { return $this->get('password'); }

	/**
	 * Sets the encrypted password
	 *
	 * @param string $pwd the cleartext password
	 */
	function setPassword($pwd) {
		global $_zp_authority;
		if (empty($pwd)) {
			$this->set('password', "");
		} else {
			$this->set('password', $_zp_authority->passwordHash($this->get('user'), $pwd));
		}
	}

	/**
	 * Returns the password hint
	 *
	 * @return string
	 */
	function getPasswordHint() {
		return get_language_string($this->get('password_hint'));
	}

	/**
	 * Sets the password hint
	 *
	 * @param string $hint the hint text
	 */
	function setPasswordHint($hint) { $this->set('password_hint', $hint); }

}
?>
