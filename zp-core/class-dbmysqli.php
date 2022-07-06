<?php
// force UTF-8 Ø
/**
 * Database core class for the MySQLi library
 *
 * @package core
 * @subpackage classes\database
 * 
 * @since ZenphotoCMS 1.6 - reworked as class
 */
class dbMySQLi extends dbBase {

	/**
	 * Connect to the database server and select the database.
	 * @param array $config the db configuration parameters
	 * @param bool $errorstop set to false to omit error messages
	 * @return true if successful connection
	 */
	function __construct($config, $errorstop = true) {
		$this->details = unserialize(DB_NOT_CONNECTED);
		$socket = null;
		if (isset($config['mysql_socket']) && !empty($config['mysql_socket'])) {
			$socket = $config['mysql_socket'];
		}
		if (function_exists('mysqli_connect')) {
			try {
				$this->connection = mysqli_connect($config['mysql_host'], $config['mysql_user'], $config['mysql_pass'], $config['mysql_database'], $config['mysql_port'], $socket);
			} catch (mysqli_sql_exception $e) {
				$this->connection = NULL;
				$connection_error = gettext('MySQLi Error: Zenphoto could not instantiate a connection.') . PHP_EOL;
				$connection_error .= $e->getMessage();
				debugLogBacktrace($connection_error);
				if ($errorstop) {
					zp_error($connection_error);
				} 
				return false;
			}
		}
		$this->details['mysql_host'] = $config['mysql_host'];
		$this->details['mysql_socket'] = $config['mysql_socket'];
		if (!is_object($this->connection)) {
			return false;
		}
		if (!$this->connection->select_db($config['mysql_database'])) {
			if ($errorstop) {
				zp_error(sprintf(gettext('MySQLi Error: MySQLi returned the error %1$s when Zenphoto tried to select the database %2$s.'), $this->connection->error, $config['mysql_database']));
			}
			return false;
		}
		$this->details = $config;
		if (array_key_exists('UTF-8', $config) && $config['UTF-8']) {
			$this->connection->set_charset("utf8");
		}
		// set the sql_mode to relaxed (if possible)
		@$this->connection->query('SET SESSION sql_mode="";');
		//return $this->connection;
	}

	/**
	 * The main query function. Runs the SQL on the connection and handles errors.
	 * @param string $sql sql code
	 * @param bool $errorstop set to false to supress the error message
	 * @return results of the sql statements
	 * @since 0.6
	 */
	function query($sql, $errorstop = true) {
		if (EXPLAIN_SELECTS && strpos($sql, 'SELECT') !== false) {
			$result = $this->connection->query('EXPLAIN ' . $sql);
			if ($result) {
				$explaination = array();
				while ($row = $result->fetch_assoc()) {
					$explaination[] = $row;
				}
			}
			debugLogVar("EXPLAIN $sql", $explaination);
		}
		$last_result = false;
		if (is_object($this->connection)) {
			try {
				$last_result = $this->connection->query($sql);
			} catch (mysqli_sql_exception $e) {
				$last_result = false;
			}
		}
		/* if ($result = @$this->connection->query($sql)) {
			return $result;
		} */
		if (!$last_result && $errorstop) {
			$sql = str_replace('`' . $this->details['mysql_prefix'], '`[' . gettext('prefix') . ']', $sql);
			$sql = str_replace($this->details['mysql_database'], '[' . gettext('DB') . ']', $sql);
			trigger_error(sprintf(gettext('%1$s Error: ( %2$s ) failed. %1$s returned the error %3$s'), DATABASE_SOFTWARE, $sql, $this->getError()), E_USER_ERROR);
		}
		return $last_result;
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
		if (is_object($result)) {
			$row = $result->fetch_assoc();
			mysqli_free_result($result);
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
		if (is_object($result)) {
			$allrows = array();
			if (is_null($key)) {
				while ($row = $result->fetch_assoc()) {
					$allrows[] = $row;
				}
			} else {
				while ($row = $result->fetch_assoc()) {
					$allrows[$row[$key]] = $row;
				}
			}
			mysqli_free_result($result);
			return $allrows;
		} else {
			return false;
		}
	}

	/**
	 * mysqli_real_escape_string standin that insures the DB connection is passed.
	 *
	 * @param string $string
	 * @return string
	 */
	function quote($string, $addquotes = true) {
		$escaped = $this->connection->real_escape_string($string);
		if ($addquotes) {
			return "'" . $escaped . "'";
		} else {
			return $escaped;
		}
	}

	/*
	 * returns the insert id of the last database insert
	 */
	function insertID() {
		return $this->connection->insert_id;
	}

	/*
	 * Fetch a result row as an associative array
	 */
	function fetchAssoc($resource) {
		if ($resource) {
			return $resource->fetch_assoc();
		}
		return false;
	}

	/*
	 * Returns the text of the error message from previous operation
	 */
	function getError() {
		if (is_object($this->connection)) {
			return mysqli_error($this->connection);
		}
		if (!$msg = mysqli_connect_error())
			$msg = sprintf(gettext('%s not connected'), DATABASE_SOFTWARE);
		return $msg;
	}

	/*
	 * Get number of affected rows in previous operation
	 */
	function getAffectedRows() {
		return $this->connection->affected_rows;
	}

	/*
	 * Get a result row as an enumerated array
	 */
	function fetchRow($result) {
		if (is_object($result)) {
			return $result->fetch_row();
		}
		return false;
	}

	/*
	 * Get number of rows in result
	 */
	function getNumRows($result) {
		return $result->num_rows;
	}

	/**
	 * Closes the database
	 */
	function close() {
		if ($this->connection) {
			$rslt = $this->connection->close();
		} else {
			$rslt = true;
		}
		$this->connection = NULL;
		return $rslt;
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
				return $this->query($sql, true);
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
		return mysqli_free_result($result);
	}
	
	/**
	 * Returns the client info
	 * @return string
	 */
	function getClientInfo() {
		return $this->connection->get_client_info();
	}

	
}