<?php
// force UTF-8 Ø
/**
 * Database base class if no supported database is configured in PHP
 *
 * @package core
 * @subpackage classes\database
 * 
 * @since ZenphotoCMS 1.6 - reworked as class
 */
class dbBase {
	
	public $connection;
	public $details;
	public $last_result;

	/**
	 * Connect to the database server and select the database.
	 * @param array $config the db configuration parameters
	 * @param bool $errorstop set to false to omit error messages
	 * @return true if successful connection
	 */
	function __construct($config, $errorstop = true) {
		$this->details = unserialize(DB_NOT_CONNECTED);
		$this->connection = NULL;
		if ($errorstop) {
			zp_error(gettext('MySQL Error: Zenphoto could not instantiate a connection.'));
		}
		return false;
	}
	
	/**
	 * @deprecated ZenphotoCMS 2.0 - Use the class property $connection instead
	 * @return object|false
	 */
	function connect() {
		deprecationNotice(gettext('Use the class property $connection instead'));
		return $this->connection;
	}
	
	/**
	 * Logs an database error to the php error_log in case the ZPCMS own debuglog functions are not yet available
	 * 
	 * @since ZenphotoCMS 1.6
	 * 
	 * @param string $error_msg
	 * @param bool $errorstop
	 */
	static function logConnectionError($error_msg, $errorstop = false) {
		if (function_exists('debugLogBacktrace')) { // not available if setup problems
			debugLogBacktrace($error_msg);
		} else if (class_exists('setup')) {
			setup::Log($error_msg, true);
		}
		if ($errorstop) {
			if (function_exists('zp_error')) {
				zp_error($error_msg);
			} else {
				trigger_error($error_msg, E_USER_ERROR);
			}
		}
	}

	/**
	 * The main query function. Runs the SQL on the connection and handles errors.
	 * @param string $sql sql code
	 * @param bool $errorstop set to false to supress the error message
	 * @return results of the sql statements
	 * @since 0.6
	 */
	function query($sql, $errorstop = true) {
		return false;
	}

	/**
	 * Runs a SQL query and returns an associative array of the first row.
	 * Doesn't handle multiple rows, so this should only be used for unique entries.
	 * @param string $sql sql code
	 * @param bool $errorstop set to false to supress the error message
	 * @return results of the sql statements
	 * @since 0.6
	 */
	function querySingleRow($sql, $errorstop = true) {
		return false;
	}

	/**
	 * Runs a SQL query and returns an array of associative arrays of every row returned.
	 * @param string $sql sql code
	 * @param bool $errorstop set to false to supress the error message
	 * @param string $key optional array index key
	 * @return results of the sql statements
	 * @since 0.6
	 */
	function queryFullArray($sql, $errorstop = true, $key = NULL) {
		return false;
	}

	/**
	 * mysqli_real_escape_string standin that insures the DB connection is passed.
	 *
	 * @param string $string
	 * @return string
	 */
	function quote($string, $addquotes = true) {
		if ($addquotes) {
			return "'" . addslashes($string) . "'";
		} else {
			return addslashes($string);
		}
	}

	/**
	 * returns the insert id of the last database insert
	 */
	function insertId() {
		return 0;
	}

	/**
	 * Fetch a result row as an associative array
	 */
	function fetchAssoc($resource) {
		return false;
	}

	/**
	 * Returns the text of the error message from previous operation
	 */
	function getError() {
		return gettext('No supported databases');
	}

	/**
	 * Get number of affected rows in previous operation
	 */
	function getAffectedRows() {
		return 0;
	}

	/**
	 * Get a result row as an enumerated array
	 */
	function fetchRow($result) {
		return false;
	}

	/**
	 * Get number of rows in result
	 */
	function getNumRows($result) {
		return 0;
	}

	/**
	 * Closes the database
	 */
	function close() {
		$this->connection = NULL;
		return true;
	}

	/**
	 * report the software of the database
	 */
	function getSoftware() {
		return array(
				'application' => DATABASE_SOFTWARE, 
				'required' => DATABASE_MIN_VERSION, 
				'desired' => DATABASE_DESIRED_VERSION, 
				'required_mariadb' => DATABASE_MARIADB_MIN_VERSION, 
				'desired_mariadb' => DATABASE_MARIADB_DESIRED_VERSION, 
				'type' => $this->getType(), 
				'version' => $this->getVersion());
	}

	/**
	 * create the database
	 */
	function create() {
		if ($this->connection) {
			$sql = 'CREATE DATABASE IF NOT EXISTS ' . '`' . $this->details['mysql_database'] . '`' . $this->getCollationSetClause();
			return $this->query($sql, false);
		}
		return false;
	}

	/**
	 * Returns user's permissions on the database
	 */
	function getPermissions() {
		if ($this->connection) {
			$sql = "SHOW GRANTS FOR " . $this->details['mysql_user'] . ";";
			$result = $this->query($sql, false);
			if (!$result) {
				$result = $this->query("SHOW GRANTS;", false);
			}
			if (is_object($result)) {
				$db_results = array();
				while ($onerow = $this->fetchRow($result)) {
					$db_results[] = $onerow[0];
				}
				return $db_results;
			} else {
				return false;
			}
		}
		return false;
	}

	/**
	 * Sets the SQL session mode to empty
	 */
	function setSQLmode() {
		return $this->query('SET SESSION sql_mode=""', false);
	}

	/**
	 * Queries the SQL session mode
	 */
	function getSQLmode() {
		$result = $this->query('SELECT @@SESSION.sql_mode;', false);
		if (is_object($result)) {
			$row = $this->fetchRow($result);
			return $row[0];
		}
		return false;
	}

	/**
	 * Gets the set clause to set the collation for tables to create
	 * 
	 * @return string
	 */
	function getCollationSetClause() {
		if ($this->hasUtf8mb4Support('utf8mb4_520')) { // MySQL 5.6+ 
			return ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci';
		} else if ($this->hasUtf8mb4Support('utf8mb4')) { // MySQL 5.5.3+
			return ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
		} else {
			return ' CHARACTER SET utf8 COLLATE utf8_unicode_ci';
		}
	}

 /**
  * @param string $sql
  * @return array
  */
	function createTable(&$sql) {
		return $this->query($sql, false);
	}

	/**
	 * @param string $sql
	 * @return array
	 */
	function tableUpdate(&$sql) {
		return $this->query($sql, false);
	}

	/**
	 * Wrapper method for various SHOW queries
	 * 
	 * @param string $what "table", "columns", "variables", "index"
	 * @param string $aux
	 * @return array
	 */
	function show($what, $aux = '') {
		if ($this->connection) {
			switch ($what) {
				case 'tables':
					$sql = "SHOW TABLES FROM `" . $this->details['mysql_database'] . "` LIKE '" . $this->likeEscape($this->details['mysql_prefix']) . "%'";
					return $this->query($sql, false);
				case 'columns':
					$sql = 'SHOW FULL COLUMNS FROM `' . $this->details['mysql_prefix'] . $aux . '`';
					return $this->query($sql, true);
				case 'variables':
					$sql = "SHOW VARIABLES LIKE '$aux'";
					return $this->queryFullArray($sql);
				case 'index':
					$sql = "SHOW INDEX FROM `" . $this->details['mysql_database'] . '`.' . $aux;
					return $this->queryFullArray($sql);
			}
		}
		return false;
	}

	/**
	 * Returns an array with the tables names of the database
	 * 
	 * @since ZenphotoCMS 1.6
	 * @return array
	 */
	function getTables() {
		$tables = array();
		if ($this->connection) {
			$resource = $this->show('tables');
			if ($resource) {
				while ($row = $this->fetchAssoc($resource)) {
					$tables[] = array_shift($row);
				}
				$this->freeResult($resource);
			}
		}
		return $tables;
	}

	/**
	 * Gets the names of the tables expected to exist in a Zenphoto database
	 * 
	 * @since ZenphotoCMS 1.6
	 * @param string $prefix Null is default to use the prefix set in the current connection. 
	 * 												In setup environment here pass the mysql prefix set in $_zp_conf_vars for further checks
	 * @return array
	 */
	function getExpectedTables($prefix = null) {
		if (!is_null($prefix)) {
			$mysql_prefix = $prefix;
		} else {
			$mysql_prefix = $this->details['mysql_prefix'];
		}
		return array($mysql_prefix . 'options',
				$mysql_prefix . 'albums',
				$mysql_prefix . 'images',
				$mysql_prefix . 'comments',
				$mysql_prefix . 'administrators',
				$mysql_prefix . 'admin_to_object',
				$mysql_prefix . 'tags',
				$mysql_prefix . 'obj_to_tag',
				$mysql_prefix . 'captcha',
				$mysql_prefix . 'pages',
				$mysql_prefix . 'news2cat',
				$mysql_prefix . 'news_categories',
				$mysql_prefix . 'news',
				$mysql_prefix . 'menu',
				$mysql_prefix . 'plugin_storage',
				$mysql_prefix . 'search_cache'
		);
	}
	
	/**
	 * Checks if a table exists in the database
	 * 
	 * @since ZenphotoCMS 1.6
	 * @param string $table Table name without the prefix
	 * @return boolean
	 */
	function hasTable($table) {
		$tables = $this->getTables();
		if ($tables && in_array($this->getPrefix() . $table, $tables)) {
			return true;
		}
		return false;
	}

	/**
	 * Checks if a table has content. Note: Does not check if the table actually exists!
	 * @since ZenphotoCMS 1.6
	 * 
	 * @param string $table Table name without the prefix
	 * @return boolean
	 */
	function isEmptyTable($table) {
		if ($this->connection) {
			$not_empty = $this->query('SELECT NULL FROM ' . $this->prefix($table) . ' LIMIT 1', true);
			if ($not_empty) {
				return false;
			}
			return true;
		}
		return true;
	}

	/**
	 * Gets the detail info of all fields in a table
	 * 
	 * @since ZenphotoCMS 1.6 
	 * @param string $table Name of the table to get the fields info of
	 * @return array|false
	 */
	function getFields($table) {
		$result = $this->show('columns', $table);
		if ($result) {
			$fields = array();
			while ($row = $this->fetchAssoc($result)) {
				$fields[] = $row;
			}
			return $fields;
		} else {
			return false;
		}
	}

	/**
	 * @deprecated ZenphotoCMS 2.0 - Use the method getFields() instead
	 */
	function listFields($table) {
		deprecationNotice('Use the method getFields() instead');
		return $this->getFields($table);
	}
	
	/**
	 * Deletes the content of a table
	 * @param string $table
	 * @return mixed
	 */
	function truncateTable($table) {
		if ($this->connection) {
			$sql = 'TRUNCATE ' . $this->details['mysql_prefix'] . $table;
			return $this->query($sql, false);
		}
		return false;
	}

	/**
	 * Escapes LIKE statements
	 * @param string $str
	 * @return string
	 */
	function likeEscape($str) {
		return strtr($str, array('_' => '\\_', '%' => '\\%'));
	}

	function freeResult($result) {
		return false;
	}
	
	
	/**
	 * Prefix a table name with a user-defined string to avoid conflicts and enclosed all in backticks
	 * This MUST be used in all database queries.
	 * @param string $tablename name of the table
	 * @return prefixed table name
	 * @since 0.6
	 */
	function prefix($tablename = NULL) {
		return '`' . $this->getPrefix() . $tablename . '`';
	}
	
	/**
	 * Gets the plain database table prefix
	 * 
	 * @since ZenphotoCMS 1.6
	 * 
	 * @return string
	 */
	function getPrefix() {
		if (defined('DATABASE_PREFIX')) {
			return DATABASE_PREFIX;
		} else {
			return 'zp_'; // use default in case this constant is not set in setup primitive environments
		}
	}

	/**
	 * Constructs a WHERE clause ("WHERE uniqueid1='uniquevalue1' AND uniqueid2='uniquevalue2' ...")
	 *  from an array (map) of variables and their values which identifies a unique record
	 *  in the database table.
	 * @param string $unique_set what to add to the WHERE clause
	 * @return contructed WHERE cleause
	 * @since 0.6
	 */
	function getWhereClause($unique_set) {
		if (empty($unique_set))
			return ' ';
		$where = ' WHERE';
		foreach ($unique_set as $var => $value) {
			$where .= ' `' . $var . '` = ' . $this->quote($value) . ' AND';
		}
		return substr($where, 0, -4);
	}

	/**
	 * Constructs a SET clause ("SET uniqueid1='uniquevalue1', uniqueid2='uniquevalue2' ...")
	 *  from an array (map) of variables and their values which identifies a unique record
	 *  in the database table. Used to 'move' records. Note: does not check anything.
	 * @param string $new_unique_set what to add to the SET clause
	 * @return contructed SET cleause
	 * @since 0.6
	 */
	function getSetClause($new_unique_set) {
		$i = 0;
		$set = ' SET';
		foreach ($new_unique_set as $var => $value) {
			$set .= ' `' . $var . '`=';
			if (is_null($value)) {
				$set .= 'NULL';
			} else {
				$set .= $this->quote($value) . ',';
			}
		}
		return substr($set, 0, -1);
	}

	/**
	 * returns the connected database name
	 */
	function getDBName() {
		global $_zp_conf_vars;
		return $_zp_conf_vars['mysql_database'];
	}

	/**
	 * Counts entries in a db table
	 * 
	 * @param string $table Table name
	 * @param string $clause Where clause for the count query
	 * @param string $field Which fields to count (default: id)
	 * @return int
	 */
	function count($table, $clause = NULL, $field = "id") {
		$sql = 'SELECT COUNT(' . $field . ') FROM ' . $this->prefix($table) . ' ' . $clause;
		$result = $this->querySingleRow($sql);
		if ($result) {
			return array_shift($result);
		} else {
			return 0;
		}
	}
	
	/**
	 * Returns the server info
	 * @return string
	 */
	function getServerInfo() {
		return $this->getVersionComplete();
	}

	/**
	 * Returns the client info
	 * @return type
	 */
	function getClientInfo() {
		return null;
	}
	
	/**
	 * Returns the database type (MySQL or MariaDB)
	 */
	function getType() {
		if ($this->connection) {
			if ($this->isMariaDB()) {
				return 'MariaDB';
			} else {
				return 'MySQL';
			}
		}
		return null;
	}

	/**
	 * Gets the plain version number
	 * 
	 * @return int
	 */
	function getVersion() {
		$software = $this->getVersionComplete();
		if ($software) {
			if ($this->isMariaDB()) {
				preg_match("/\d+(\.\d+){2,}-MariaDB/", $software, $matches);
				if ($matches) {
					return str_replace('-MariaDB', '', $matches[0]);
				}
				return $software; // just in case…
			} else {
				return $software;
			}
		}
		return '0.0.0';
	}

	/**
	 * Gets the full version including prefixes and appendixes (as on MariaDB)
	 * 
	 * @since ZenphotoCMS 1.6
	 */
	function getVersionComplete() {
		if ($this->connection) {
			$query = $this->querySingleRow('SELECT version()');
			if ($query) {
				return $query['version()'];
			}
		}
		return null;
	}

	/**
	 * Returns true if the database is MariaDB
	 * 
	 * @return boolean
	 */
	function isMariaDB() {
		$db_version = $this->getVersionComplete();
		if ($db_version && stristr($db_version, 'mariadb')) { // version includes note if mariadb
			return true;
		}
		return false;
	}

	/**
	 * Checks if the database support utf8mb4 or ut8mb4_520 encodings
	 * 
	 * Adapted from WordPress' wpdp::has_cap() 
	 *
	 * @param string $which 'utf8mb4' or 'utf8mb4_520' (default)
	 * @return boolean
	 */
	function hasUtf8mb4Support($which = 'utf8mb4_520') {
		$db_version = $this->getVersion();
		if ($db_version) { // if not set no db functions available
			switch ($which) {
				case 'utf8mb4':
					if (version_compare($db_version, '5.5.3', '<')) {
						return false;
					}
					$client_version = $this->getClientInfo();
					/*
					 * libmysql has supported utf8mb4 since 5.5.3, same as the MySQL server.
					 * mysqlnd has supported utf8mb4 since 5.0.9.
					 */
					if (strpos($client_version, 'mysqlnd') !== false) {
						$client_version = preg_replace('/^\D+([\d.]+).*/', '$1', $client_version);
						return version_compare($client_version, '5.0.9', '>=');
					} else {
						return version_compare($client_version, '5.5.3', '>=');
					}
				case 'utf8mb4_520': 
					return version_compare($db_version, '5.6', '>=');
			}
		}
		return false;
	}

}