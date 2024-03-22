<?php
// force UTF-8 Ø
/**
 * Database base class if no supported database is configured in PHP
 *
 * @package zpcore\classes\database
 * 
 * @since 1.6 - reworked as class
 */
class dbBase {
	
	public $connection = null;
	public $last_result;
	
	public $mysql_host = '';
	public $mysql_database = '';
	public $mysql_prefix = '';
	public $mysql_user = ''; 
	public $mysql_pass = '';
	public $mysql_port = 3306;
	public $mysql_socket = null;
	public $use_utf8 = false;
	public $config_valid = false;
	
	/**
	 * Connect to the database server and select the database.
	 * @param array $config the db configuration parameters
	 * @param bool $errorstop set to false to omit error messages
	 * @return true if successful connection
	 */
	function __construct($config, $errorstop = true) {
		$this->setConfig($config);
		$this->connection = NULL;
		if ($errorstop) {
			zp_error(gettext('MySQL Error: Zenphoto could not instantiate a connection.'));
		}
	}
	
	/**
	 * Checks and sets properties for the database credentials
	 * 
	 * @param array $config The config array containing database credentials
	 */
	function setConfig($config) {
		if (isset($config['UTF-8']) && $config['UTF-8']) {
			$this->use_utf8 = true;
		}
		$config_valid = false;
		
		// required credentials
		if (isset($config['mysql_host']) && !empty($config['mysql_host'])) {
			$this->mysql_host = $config['mysql_host'];
			$config_valid += 1;
		}
		if (isset($config['mysql_database']) && !empty($config['mysql_database'])) {
			$this->mysql_database = $config['mysql_database'];
			$config_valid += 1;
		}
		if (isset($config['mysql_user']) && !empty($config['mysql_user'])) {
			$this->mysql_user = $config['mysql_user'];
			$config_valid += 1;
		}
		if (isset($config['mysql_pass']) && !empty($config['mysql_pass'])) {
			$this->mysql_pass = str_replace('$', '\$', $config['mysql_pass']);
			$config_valid += 1;
		}
		if ($config_valid == 4) {
			$this->config_valid = true;
		}
		
		// not strictly required credentials
		if (isset($config['mysql_port']) && (!empty($config['mysql_port']) && is_int($config['mysql_port']))) {
			$this->mysql_port = $config['mysql_port'];
		}
		if (isset($config['mysql_socket']) && !empty($config['mysql_socket'])) {
			$this->mysql_socket = $config['mysql_socket'];
		}
		if (isset($config['mysql_prefix']) && !empty($config['mysql_prefix'])) {
			$this->mysql_prefix = $config['mysql_prefix'];
		}
	}
	
	/**
	 * @deprecated 2.0 - Use the class property $connection instead
	 * @return object|false
	 */
	function connect() {
		deprecationNotice(gettext('Use the class property $connection instead'));
		return $this->connection;
	}
	
	/**
	 * Logs an database error to the php error_log in case the ZPCMS own debuglog functions are not yet available
	 * 
	 * @since 1.6
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
			$sql = 'CREATE DATABASE IF NOT EXISTS ' . '`' . $this->mysql_database . '`' . $this->getCollationSetClause();
			return $this->query($sql, false);
		}
		return false;
	}

	/**
	 * Returns user's permissions on the database
	 */
	function getPermissions() {
		if ($this->connection) {
			$sql = "SHOW GRANTS FOR " . $this->mysql_user . ";";
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
					$sql = "SHOW TABLES FROM `" . $this->mysql_database . "` LIKE '" . $this->likeEscape($this->mysql_prefix) . "%'";
					return $this->query($sql, false);
				case 'columns':
					$sql = 'SHOW FULL COLUMNS FROM `' . $this->mysql_prefix . $aux . '`';
					return $this->query($sql, true);
				case 'variables':
					$sql = "SHOW VARIABLES LIKE '$aux'";
					return $this->queryFullArray($sql);
				case 'index':
					$sql = "SHOW INDEX FROM `" . $this->mysql_database . '`.' . $aux;
					return $this->queryFullArray($sql);
			}
		}
		return false;
	}

	/**
	 * Returns an array with the tables names of the database
	 * 
	 * @since 1.6
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
	 * @since 1.6
	 * @param string $prefix Null is default to use the prefix set in the current connection. 
	 * 												In setup environment here pass the mysql prefix set in $_zp_conf_vars for further checks
	 * @return array
	 */
	function getExpectedTables($prefix = null) {
		if (!is_null($prefix)) {
			$mysql_prefix = $prefix;
		} else {
			$mysql_prefix = $this->mysql_prefix;
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
	 * @since 1.6
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
	 * Checks if a table has no content and/or does not exist
	 * @since 1.6
	 * 
	 * @param string $table Table name without the prefix
	 * @return boolean
	 */
	function isEmptyTable($table) {
		if ($this->connection) {
			if (!$this->hasTable($table)) {
				return true;
			}
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
	 * @since 1.6 
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
	 * @deprecated 2.0 - Use the method getFields() instead
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
			$sql = 'TRUNCATE ' . $this->mysql_prefix . $table;
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
	 * @since 1.6
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
	 * @since 1.6
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
	 * Gets an array of database infos
	 * 
	 * Uses the show method but always returns an array instead of false.
	 * 
	 * @since 1.6
	 * 
	 * @param string $what 'charsets' (db/server charsets used), 'collations' (db/server collations used)
	 * @return array
	 */
	function getDBInfo($what) {
		if ($this->connection) {
			$result = array();
			switch ($what) {
				default:
				case 'charsets':
					$result = $this->show('variables', 'character_set%');
					break;
				case 'collations':
					$result = $this->show('variables', 'collation%');
					break;
			}
			if (is_array($result)) {
				return $result;
			}
		}
		return array();
	}
	
	/**
	 * Checks if the database support utf8mb4 or ut8mb4_520 encodings
	 * 
	 * Adapted from WordPress' wpdp::has_cap() 
	 * 
	 * @since 1.6
	 *
	 * @param string $which 'utf8mb4' or 'utf8mb4_520' (default) or 'general' to check for any
	 * @return boolean
	 */
	function hasUtf8mb4Support($which = 'utf8mb4_520') {
		$db_version = $this->getVersion();
		if ($db_version) { // if not set no db functions available
			switch ($which) {
				case 'utf8mb4':
					if ($this->isMariaDB() && version_compare($db_version, '5.5.0', '<')) {
						return false;
					} else if (version_compare($db_version, '5.5.3', '<')) {
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
				case 'general':
					return ($this->hasUtf8mb4Support('utf8mb4') || $this->hasUtf8mb4Support('utf8mb4_520'));
			}
		}
		return false;
	}

	/**
	 * Helper method that returns it a charset matches a general charset category.
	 * 
	 * @param string $charset Charset to check against
	 * @param string $check_charset 'utf8', 'utf8mb4' or "any" for any utf8*
	 * @return array
	 */
	static function isUf8CharsetType($charset = '', $check_charset = 'any') {
		switch ($check_charset) {
			default:
			case 'any':
				$charsets = array('utf8', 'utf8mb4', 'utf8mb3');
				break;
			case 'utf8';
				$charsets = array('utf8');
				break;
			case 'utf8mb4':
				$charsets = array('utf8mb4');
				break;
		}
		if (in_array($charset, $charsets)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Checks if the database character set and the collation are using UTF8
	 * 
	 * @since 1.6
	 * @param $what 'database' or "server"
	 * @param string $check_charset 'utf8', 'utf8mb4' or "any" for any utf8*
	 * @return boolean
	 */
	function isUtf8System($what = 'database', $check_charset = 'any') {
		if ($this->connection) {
			$charsets = $this->getDBInfo('charsets');
			$collations = $this->getDBInfo('collations');
			$checkinfo = array_merge($charsets, $collations);
			foreach ($checkinfo as $val) {
				if (!in_array($val['Variable_name'], array('character_sets_dir', 'character_set_filesystem'))) {
					if (($what == 'database' && stristr($val['Variable_name'], '_database') !== false) || ($what == 'server' && stristr($val['Variable_name'], '_database') === false)) {
						if (stristr($val['Value'], '_') !== false) {
							list( $charset ) = explode('_', $val['Value']);
						} else {
							$charset = $val['Value'];
						}
						$charset = strtolower($charset);
						if (!dbbase::isUf8CharsetType($charset, $check_charset)) {
							return false;
						}
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Converts ab utf8 database to utf8mb4
	 * 
	 * @param bool $force_conversion default false so only tables that have no non utf8* columns are converted
	 */
	function convertDatabaseToUtf8mb4($force_conversion = false) {
		if (($force_conversion || $this->isUtf8System('database', 'any')) && $this->hasUtf8mb4Support('general')) {
			$collation = 'utf8mb4_unicode_ci';
			if ($this->hasUtf8mb4Support('utf8mb4_520')) {
				$collation = 'utf8mb4_unicode_520_ci';
			}
			//return $this->query('ALTER DATABASE '. $this->mysql_database .' CHARACTER SET = utf8mb4 COLLATE = ' . $collation);
		}
		return false; 
	}

	/**
	 * Checks if all columns of a table are utf8
	 * 
	 * Partly adapted from WordPress' maybe_convert_table_to_utf8mb4()
	 * 
	 * @since 1.6
	 * 
	 * @param string $table Tablename including prefix
	 * @param string $check_charset 'utf8', 'utf8mb4' or "any" for any utf8*
	 * @return boolean
	 */
	function isTableWithUtf8Fields($table, $check_charset = 'any') {
		if ($this->connection) {
			$columns = $this->getFieldsNotUtf8($table, $check_charset);
			if ($columns === false) {
				return false;
			}
			if (empty($columns)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Gets all columns of a table are not utf8.
	 * 
	 * Returns false if no columns could be fetched, otherwise an array even if empty
	 * 
	 * @since 1.6
	 * 
	 * @param string $table Tablename including prefix
	 * @param string $check_charset 'utf8', 'utf8mb4' or "any" for any utf8*
	 * @return boolean|array
	 */
	function getFieldsNotUtf8($table, $check_charset = 'any') {
		if ($this->connection) {
			$non_utf8_fields = array();
			$columns = $this->getFields(substr($table, strlen($this->mysql_prefix)));
			if (!$columns) {
				return false;
			}
			foreach ($columns as $column) {
				if ($column['Collation']) {
					list( $charset ) = explode('_', $column['Collation']);
					$charset = strtolower($charset);
					//echo $charset .' vs ' . $check_utf8 . '<br>';
					if (!dbbase::isUf8CharsetType($charset, $check_charset)) {
						$non_utf8_fields[] = $column['Field'];
					}
				}
			}
			return $non_utf8_fields;
		}
		return array();
	}

	/**
	 * Checks if a table itself uses an utf8 collation
	 * 
	 * Partly adapted from WordPress' maybe_convert_table_to_utf8mb4()
	 * 
	 * @since 1.6
	 * 
	 * @param string $table Tablename including prefix
	 * @param string $check_charset 'utf8', 'utf8mb4' or "any" for any utf8*
	 * @param boolean $fieldcheck Default true Table counts as utf8 if all text fields use some utf8 collation, otherwise only the table itself
	 * @return boolean
	 */
	function isUTF8Table($table, $check_charset = 'any', $fieldcheck = true) {
		if ($this->connection) {
			$table_details = $this->querySingleRow('SHOW TABLE STATUS LIKE ' . $this->quote($table));
			if (!$table_details) {
				return false;
			}
			if ($fieldcheck && !$this->isTableWithUtf8Fields($table, $check_charset)) {
				return false;
			}
			list( $table_charset ) = explode('_', $table_details['Collation']);
			$charset = strtolower($table_charset);
			if (dbbase::isUf8CharsetType($charset, $check_charset)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Converts a utf8 table to utf8mb4 collation
	 * 
	 * Partly adapted from WordPress' maybe_convert_table_to_utf8mb4()
	 * 
	 * @since 1.6
	 * 
	 * @param string $table Table name including prefix
	 * @param bool $force_conversion default false so only tables that have no non utf8* columns are converted
	 * @return boolean
	 */
	function convertTableToUtf8mb4($table, $force_conversion = false) {
		if ($this->connection) {
			if ($this->isUTF8Table($table, 'utf8mb4')) {
				return true;
			} else if (($this->isUTF8Table($table, 'any') || $force_conversion) && $this->hasUtf8mb4Support('general')) {
				$collation = 'utf8mb4_unicode_ci';
				if ($this->hasUtf8mb4Support('utf8mb4_520')) {
					$collation = 'utf8mb4_unicode_520_ci';
				}
				// convert table
				$table_converted = $this->query('ALTER TABLE `' . $table . '` CONVERT TO CHARACTER SET utf8mb4 COLLATE ' . $collation);
				if ($table_converted) {
					$this->query('REPAIR TABLE `' . $table . '`');
					$this->query('OPTIMIZE TABLE `' . $table . '`');
					return true;
				} else {
					debuglog(sprintf(gettext('The table %1$s could not be converted to %2$s collation'), $table, $collation));
					return false;
				}
			} else {
				debuglog(sprintf(gettext('The table %s could not be converted to utf8mb4 collation because it is not using any utf8_* collation'), $table));
			}
		}
		return false;
	}
	
	/**
	 * Returns the REGEX word boundary characters
	 * - For MariaDB and MySQL < 8: Spencer lib variant [[:<:]] and [[:>:]]
	 * - For MySQL 8+: ICU variant \\b
	 * 
	 * @since 1.6.3
	 * 
	 * @return array
	 */
	function getRegexWordBoundaryChars() {
		$boundaries = array(
				'open' => '[[:<:]]',
				'close' => '[[:>:]]'
		);
		$version = $this->getVersion();
		if (!$this->isMariaDB() && version_compare($version, '8.0.0', '>=')) {
			$boundaries = array(
					'open' => '\\b',
					'close' => '\\b'
			);
		}
		return $boundaries;
	}

}