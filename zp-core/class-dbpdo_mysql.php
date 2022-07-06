<?php
// force UTF-8 Ø
/**
 * Database core class for the PDO::MySQL library
 *
 * @package core
 * @subpackage classes\database
 * 
 * @since ZenphotoCMS 1.6 - reworked as class
 */
class dbPDO_MySQL extends dbBase {
	
	/**
	 * Connect to the database server and select the database.
	 * @param array $config the db configuration parameters
	 * @param bool $errorstop set to false to omit error messages
	 * @return true if successful connection
	 */
	function __construct($config, $errorstop = true) {
		$this->details = unserialize(DB_NOT_CONNECTED);
		$this->connection = $this->last_result = NULL;
		if (array_key_exists('UTF-8', $config) && $config['UTF-8']) {
			$utf8 = ';charset=utf8';
		} else {
			$utf8 = false;
		}
		try {
			$db = $config['mysql_database'];
			$hostname = $config['mysql_host'];
			$username = $config['mysql_user'];
			$password = $config['mysql_pass'];
			$port = $config['mysql_port'];
			$socket = '';
			if (isset($config['mysql_socket']) && !empty($config['mysql_socket'])) {
				$socket = ';unix_socket=' . $config['mysql_socket'];
			}
			if (class_exists('PDO')) {
				$this->connection = new PDO('mysql:host=' . $hostname . ';dbname=' . $db . $utf8 . ';port=' . $port . $socket, $username, $password);
			}
		} catch (PDOException $e) {
			$this->last_result = $e;
			if ($errorstop) {
				zp_error(sprintf(gettext('MySql Error: Zenphoto received the error %s when connecting to the database server.'), $e->getMessage()));
			}
			$this->connection = NULL;
			return false;
		}
		$this->details = $config;
		if ($utf8 && version_compare(PHP_VERSION, '5.3.6', '<')) {
			try {
				$this->connection->query("SET NAMES 'utf8'");
			} catch (PDOException $e) {
				//	:(
			}
		}
		// set the sql_mode to relaxed (if possible)
		try {
			$this->connection->query('SET SESSION sql_mode="";');
		} catch (PDOException $e) {
			//	What can we do :(
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
		$this->last_result = false;
		try {
			$this->last_result = $this->connection->query($sql);
		} catch (PDOException $e) {
			$this->last_result = false;
		}
		if (!$this->last_result && $errorstop) {
			$sql = str_replace('`' . $this->details['mysql_prefix'], '`[' . gettext('prefix') . ']', $sql);
			$sql = str_replace($this->details['mysql_database'], '[' . gettext('DB') . ']', $sql);
			trigger_error(sprintf(gettext('%1$s Error: ( %2$s ) failed. %1$s returned the error %3$s'), DATABASE_SOFTWARE, $sql, $this->getError()), E_USER_ERROR);
		}
		return $this->last_result;
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
		$result = $this->query($sql, $errorstop);
		if ($result) {
			$row = $this->fetchAssoc($result);
			$result->closeCursor();
			return $row;
		} else {
			return false;
		}
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
		$result = $this->query($sql, $errorstop);
		if ($result) {
			$allrows = array();
			if (is_null($key)) {
				foreach ($result as $row) {
					$allrows[] = $row;
				}
			} else {
				foreach ($result as $row) {
					$allrows[$row[$key]] = $row;
				}
			}
			$result->closeCursor();
			return $allrows;
		} else {
			return false;
		}
	}

	/**
	 * sqlite_real_escape_string standin that insures the DB connection is passed.
	 *
	 * @param string $string
	 * @return string
	 */
	function quote($string, $addquote = true) {
		return $this->connection->quote($string);
	}

	/*
	 * returns the insert id of the last database insert
	 */
	function insertID() {
		return $this->connection->lastInsertId();
	}

	/*
	 * Fetch a result row as an associative array
	 */
	function fetchAssoc($resource) {
		if (is_object($resource)) {
			return $resource->fetch(PDO::FETCH_ASSOC);
		}
		return false;
	}

	/*
	 * Returns the text of the error message from previous operation
	 */
	function getError() {
		if (is_object($this->last_result)) {
			return $this->last_result->getMessage();
		} else {
			return sprintf(gettext('%s not connected'), DATABASE_SOFTWARE);
		}
	}

	/*
	 * Get number of affected rows in previous operation
	 */
	function getAffectedRows() {
		if (is_object($this->last_result)) {
			return $this->last_result->rowCount();
		} else {
			return 0;
		}
	}

	/**
	 * Get a result row as an enumerated array
	 */
	function fetchRow($result) {
		if (is_object($result)) {
			return $result->fetch(PDO::FETCH_NUM);
		}
		return false;
	}

	/**
	 * Get number of rows in result
	 */
	function getNumRows($result) {
		if (is_array($result)) {
			return count($result);
		} else {
			return $result->rowCount();
		}
	}

	/**
	 * Closes the database
	 */
	function close() {
		$this->connection = NULL;
		return true;
	}

	/**
	 * create the database
	 */
	function create() {
		$sql = 'CREATE DATABASE IF NOT EXISTS ' . '`' . $this->details['mysql_database'] . '`' . $this->getCollation();
		return $this->query($sql, false);
	}

	/**
	 * Returns user's permissions on the database
	 */
	function getPermissions() {
		$sql = "SHOW GRANTS FOR " . $this->details['mysql_user'] . ";";
		$result = $this->query($sql, false);
		if (!$result) {
			$result = $this->query("SHOW GRANTS;", false);
		}
		if ($result) {
			$db_results = array();
			while ($onerow = $this->fetchRow($result)) {
				$db_results[] = $onerow[0];
			}
			return $db_results;
		} else {
			return false;
		}
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
		if ($result) {
			$row = $this->fetchRow($result);
			return $row[0];
		}
		return false;
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
		switch ($what) {
			case 'tables':
				$sql = "SHOW TABLES FROM `" . $this->details['mysql_database'] . "` LIKE '" . $this->likeEscape($this->details['mysql_prefix']) . "%'";
				return $this->query($sql, false);
			case 'columns':
				$sql = 'SHOW FULL COLUMNS FROM `' . $this->details['mysql_prefix'] . $aux . '`';
				return $this->query($sql, false);
			case 'variables':
				$sql = "SHOW VARIABLES LIKE '$aux'";
				return $this->queryFullArray($sql);
			case 'index':
				$sql = "SHOW INDEX FROM `" . $this->details['mysql_database'] . '`.' . $aux;
				return $this->queryFullArray($sql);
		}
	}
	
	/**
	 * Returns an array with the tables names of the database
	 * 
	 * @since ZenphotoCMS 1.6
	 * @return array
	 */
	function getTables() {
		$resource = $this->show('tables');
		$tables = array();
		if ($resource) {
			while ($row = $this->fetchAssoc($resource)) {
				$tables[] = array_shift($row);
			}
			$this->freeResult($resource);
		}
		return $tables;
	}

	/**
	 * Checks if a table has content. Note: Does not check if the table actually exists!
	 * @since ZenphotoCMS 1.6
	 * 
	 * @param string $table Table name without the prefix
	 * @return boolean
	 */
	function isEmptyTable($table) {
		$not_empty = $this->query('SELECT NULL FROM ' .  $this->prefix($table) . ' LIMIT 1', true);
		if ($not_empty) {
			return false;
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
	 * Lists the columns (fields) info of a table
	 * 
	 * @deprecated ZenphotoCMS 2.0 - Use the method getFields() instead
	 * @param stringe $table 
	 * @return boolean
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
		$sql = 'TRUNCATE ' . $this->details['mysql_prefix'] . $table;
		return $this->query($sql, false);
	}
	
	/**
	 * Escapes LIKE statements
	 * @param string $str
	 * @return string
	 */
	function likeEscape($str) {
		return strtr($str, array('_' => '\\_', '%' => '\\%'));
	}
	
	/**
	 * Frees the memory assiciated with a resutl
	 * @param type $result
	 */
	function freeResult($result) {
		return $result->closeCursor();
	}
	
	/**
	 * Returns the client info
	 * @return string
	 */
	function getClientInfo() {
		return $this->connection->getAttribute(PDO::ATTR_CLIENT_VERSION);
	}

}