<?php

// The query cache
// force UTF-8 Ø
$_zp_object_cache = array();
define('OBJECT_CACHE_DEPTH', 150); //	how many objects to hold for each object class

/**
 * Persistent Object Class 
 *
 * Parent ABSTRACT class of all persistent objects. This class should not be
 * instantiated, only used for subclasses. This cannot be enforced, but please
 * follow it!
 *
 * A child class should run the follwing in its constructor:
 *
 * $new = $this->instantiate('tablename',
 *   array('uniquestring'=>$value, 'uniqueid'=>$uniqueid));
 *
 * where 'tablename' is the name of the database table to use for this object
 * type, and array('uniquestring'=>$value, ...) defines a unique set of columns
 * (keys) and their current values which uniquely identifies a single record in
 * that database table for this object.
 *
 * Note: This is a persistable model that does not save automatically. You MUST
 * call $this->save(); explicitly to persist the data in child classes. 
 * 
 * @package core
 * @subpackage classes\objects
 */
class PersistentObject {

	var $loaded = false;
	var $exists = false;
	var $table;
	var $transient;
	protected $id = 0;
	private $unique_set = NULL;
	private $cache_by;
	private $use_cache = false;
	private $tempdata = NULL;
	private $data = NULL;
	private $updates = NULL;

	/**
	 *
	 * @deprecated 1.6
	 * @since 1.4.6
	 */
	function __construct($tablename, $unique_set, $cache_by = NULL, $use_cache = true, $is_transient = false, $allowCreate = true) {
		return instantiate($tablename, $unique_set, $cache_by, $use_cache, $is_transient, $allowCreate);
	}

	/**
	  }
	 *
	 * Prime instantiator for Zenphoto objects
	 * @param $tablename	The name of the database table
	 * @param $unique_set	An array of unique fields
	 * @param $cache_by
	 * @param $use_cache
	 * @param $is_transient	Set true to prevent database insertion
	 * @param $allowCreate Set true to allow a new object to be made.
	 * @return bool will be true if the unique_set does not already exist
	 */
	function instantiate($tablename, $unique_set, $cache_by = NULL, $use_cache = true, $is_transient = false, $allowCreate = true) {
		global $_zp_object_cache;
		//	insure a cache entry
		$classname = get_class($this);
		if (!isset($_zp_object_cache[$classname])) {
			$_zp_object_cache[$classname] = array();
		}
		// Initialize the variables.
		// Load the data into the data array using $this->load()
		$this->data = $this->tempdata = $this->updates = array();
		$this->loaded = false;
		$this->table = $tablename;
		$this->unique_set = $unique_set;
		if (is_null($cache_by)) {
			$this->cache_by = serialize($unique_set);
		} else {
			$this->cache_by = $this->unique_set[$cache_by];
		}
		$this->use_cache = $use_cache;
		$this->transient = $is_transient;
		return $this->load($allowCreate);
	}

	/**
	 *
	 * check the cache for presence of the entry and return it if found
	 * @param $entry
	 */
	private function getFromCache() {
		global $_zp_object_cache;
		if (isset($_zp_object_cache[$c = get_class($this)]) && isset($_zp_object_cache[$c][$this->cache_by])) {
			return $_zp_object_cache[$c][$this->cache_by];
		}
		return NULL;
	}

	/**
	 *
	 * add the entry to the cache
	 * @param $entry
	 */
	private function addToCache($entry) {
		global $_zp_object_cache;
		if ($entry) {
			if (count($_zp_object_cache[$classname = get_class($this)]) >= OBJECT_CACHE_DEPTH) {
				array_shift($_zp_object_cache[$classname]); //	discard the oldest
			}
			$_zp_object_cache[$classname][$this->cache_by] = $entry;
		}
	}

	/**
	 * Set a variable in this object. Does not persist to the database until
	 * save() is called. So, IMPORTANT: Call save() after set() to persist.
	 * If the requested variable is not in the database, sets it in temp storage,
	 * which won't be persisted to the database.
	 */
	function set($var, $value) {
		if (empty($var))
			return false;
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
	protected function setDefaults() {
		
	}

	/**
	 * Change one or more values of the unique set assigned to this record.
	 * Checks if the record already exists first, if so returns false.
	 * If successful returns true and changes $this->unique_set
	 * A call to move is instant, it does not require a save() following it.
	 */
	function move($new_unique_set) {
		// Check if we have a row
		$result = query_single_row('SELECT * FROM ' . prefix($this->table) . getWhereClause($new_unique_set) . ' LIMIT 1;');
		if (!$result || $result['id'] == $this->id) { //	we should not find an entry for the new unique set!
			if (!zp_apply_filter('move_object', true, $this, $new_unique_set)) {
				return false;
			}
			$sql = 'UPDATE ' . prefix($this->table) . getSetClause($new_unique_set) . ' ' . getWhereClause($this->unique_set);
			$result = query($sql);
			if ($result && db_affected_rows() == 1) { //	and the update should have effected just one record
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
		$result = query('SELECT * FROM ' . prefix($this->table) . getWhereClause($new_unique_set) . ' LIMIT 1;');

		if ($result && db_num_rows($result) == 0) {
			if (!zp_apply_filter('copy_object', true, $this, $new_unique_set)) {
				return false;
			}
			// Note: It's important for $new_unique_set to come last, as its values should override.
			$insert_data = array_merge($this->data, $this->updates, $this->tempdata, $new_unique_set);
			unset($insert_data['id']);
			unset($insert_data['hitcounter']); //	start fresh on new copy
			if (empty($insert_data)) {
				return true;
			}
			$sql = 'INSERT INTO ' . prefix($this->table) . ' (';
			$i = 0;
			foreach (array_keys($insert_data) as $col) {
				if ($i > 0)
					$sql .= ", ";
				$sql .= "`$col`";
				$i++;
			}
			$sql .= ') VALUES (';
			$i = 0;
			foreach (array_values($insert_data) as $value) {
				if ($i > 0)
					$sql .= ', ';
				if (is_null($value)) {
					$sql .= 'NULL';
				} else {
					$sql .= db_quote($value);
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
			$id = ' is NULL'; //	allow delete of bad item!
		} else {
			$id = '=' . $id;
		}
		$sql = 'DELETE FROM ' . prefix($this->table) . ' WHERE `id`' . $id;
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
		return $this->id;
	}

	/**
	 *
	 * returns the database record of the object
	 * @return array
	 */
	function getData() {
		$this->save();
		return $this->data;
	}

	/**
	 * Get the value of a variable. If $current is false, return the value
	 * as of the last save of this object.
	 */
	function get($var, $current = true) {
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
	private function load($allowCreate) {
		$new = $entry = null;
		// Set up the SQL query in case we need it...
		$sql = 'SELECT * FROM ' . prefix($this->table) . getWhereClause($this->unique_set) . ' LIMIT 1;';
		// But first, try the cache.
		if ($this->use_cache) {
			$entry = $this->getFromCache();
		}
		// Check the database if: 1) not using cache, or 2) didn't get a hit.
		if (empty($entry)) {
			$entry = query_single_row($sql, false);
			// Save this entry into the cache so we get a hit next time.
			if ($entry)
				$this->addToCache($entry);
		}

		// If we don't have an entry yet, this is a new record. Create it.
		if (empty($entry)) {
			if ($this->transient) { // no don't save it in the DB!
				$entry = array_merge($this->unique_set, $this->updates, $this->tempdata);
				$entry['id'] = 0;
			} else if (!$allowCreate) {
				return NULL; // does not exist and we are not allowed to create it
			} else {
				$new = true;
				$this->save();
				$entry = query_single_row($sql);
				// If we still don't have an entry, something went wrong...
				if (!$entry)
					return null;
				// Save this new entry into the cache so we get a hit next time.
				$this->addToCache($entry);
			}
		}
		$this->data = $entry;
		$this->id = (int) $entry['id'];
		$this->loaded = true;
		return $new;
	}

	/**
	 * Save the updates made to this object since the last update. Returns
	 * true if successful, false if not.
	 * @param bool $checkupdates Default false. If true the internal $updates property is checked for actual changes so unnecessary saving is skipped. Applies to already existing objects only.
	 */
	function save($checkupdates = false) {
		if ($this->transient)
			return false; // If this object isn't supposed to be persisted, don't save it.
		if (!$this->unique_set) { // If we don't have a unique set, then this is incorrect. Don't attempt to save.
			zp_error('empty $this->unique set is empty');
			return false;
		}
		if (!$this->id) {
			$this->setDefaults();
			// Create a new object and set the id from the one returned.
			$insert_data = array_merge($this->unique_set, $this->updates, $this->tempdata);
			if (empty($insert_data)) {
				return true;
			}
			$i = 0;
			$cols = $vals = '';
			foreach ($insert_data as $col => $value) {
				if ($i > 0)
					$cols .= ", ";
				$cols .= "`$col`";
				if ($i > 0)
					$vals .= ", ";
				if (is_null($value)) {
					$vals .= "NULL";
				} else {
					$vals .= db_quote($value);
				}
				$i++;
			}
			$sql = 'INSERT INTO ' . prefix($this->table) . ' (' . $cols . ') VALUES (' . $vals . ')';
			$success = query($sql);
			if (!$success || db_affected_rows() != 1) {
				return false;
			}
			foreach ($insert_data as $key => $value) { // copy over any changes
				$this->data[$key] = $value;
			}
			$this->data['id'] = $this->id = (int) db_insert_id(); // so 'get' will retrieve it!
			$this->loaded = true;
			$this->updates = null;
			$this->tempdata = array(); 
		} else {
			if ($checkupdates) {
				$this->checkChanges();
			} 
			// Save the existing object (updates only) based on the existing id.
			if (empty($this->updates)) {
				return true;
			} else {
				if($checkupdates) {
					$this->setLastChange();
					if (!isset($this->updates['lastchangeuser'])) {
						$this->setLastChangeUser('');
					}
				}
				$sql = 'UPDATE ' . prefix($this->table) . ' SET';
				$i = 0;
				foreach ($this->updates as $col => $value) {
					if ($i > 0)
						$sql .= ",";
					if (is_null($value)) {
						$sql .= " `$col` = NULL";
					} else {
						$sql .= " `$col` = " . db_quote($value);
					}
					$this->data[$col] = $value;
					$i++;
				}
				$sql .= ' WHERE id=' . $this->id . ';';
				$success = query($sql);
				if (!$success || db_affected_rows() != 1) {
					return false;
				}
				foreach ($this->updates as $key => $value) {
					$this->data[$key] = $value;
				}
				$this->updates = array();
			}
		}
		zp_apply_filter('save_object', true, $this);
		$this->addToCache($this->data);
		return true;
	}

	/**
	 *
	 * "Magic" function to return a string identifying the object when it is treated as a string
	 * @return string
	 */
	public function __toString() {
		return $this->table . " (" . $this->id . ")";
	}

	/**
	 * Returns the last change user
	 * 
	 * @return string
	 */
	function getLastChange() {
		return $this->get("lastchange");
	}

	/**
	 * stores the current date in the format 'Y-m-d H:i:s' as the last change date
	 */
	function setLastChange() {
		$this->set('lastchange', date('Y-m-d H:i:s'));
	}

	/**
	 * Returns the last change user
	 *
	 * @since ZenphotoCMS 1.5.2
	 * @return string
	 */
	function getLastChangeUser() {
		return $this->get("lastchangeuser");
	}

	/**
	 * stores the last change user
	 * 
	 * @since ZenphotoCMS 1.5.2
	 */
	function setLastchangeUser($a) {
		$this->set("lastchangeuser", $a);
	}

	/**
	 * By default the object is saved if there are any updates within the property `$updates` no matter if these are actual changes to existing data.
	 * This checks that and internally updates the `$updates` property with the actual changes obky so you optionally can skip unnecessary object saves.
	 * 
	 * Standard object fields `lastchange` and `lastchangeuser` are exclude because `lastchange` always changes and both make no sense 
	 * if there is no actual content change at all.
	 * 
	 * This can be used before calling the save() method or enabled within the save() method optionally
	 * 
	 * @see save()
	 * 
	 * @param bool $update True (default) to also update the $updates property with changes found or clear it. False to only check for changes.
	 * 
	 * @since ZenphotoCMS 1.5.2
	 * 
	 * @return boolean
	 */
	function checkChanges($update = true) {
		$changes = array();
		$excluded = array('lastchange', 'lastchangeuser');
		if (!empty($this->updates)) {
			foreach ($this->updates as $key => $val) {
				if (!in_array($key, $excluded) && $val != $this->data[$key]) {
					$changes[$key] = $val;
				}
			}
			if (empty($changes)) {
				if ($update) {
					$this->updates = array();
				}
				return false;
			} else {
				if ($update) {
					//Make sure we add these back!
					foreach($excluded as $exlude) {
						if(isset($this->updates[$exlude])) {
							$changes[$exlude] = $this->updates[$exlude];
						}
					}
					$this->updates = $changes;
				}
				return true;
			}
		}
	}

}
