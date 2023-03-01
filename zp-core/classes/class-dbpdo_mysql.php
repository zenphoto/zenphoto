<?php
// force UTF-8 Ø
/**
 * Database core class for the PDO::MySQL library
 *
 * @package zpcore\classes\database
 * 
 * @since 1.6 - reworked as class
 */
class dbPDO_MySQL extends dbBase {
	
	/**
	 * Connect to the database server and select the database.
	 * @param array $config the db configuration parameters
	 * @param bool $errorstop set to false to omit error messages
	 * @return true if successful connection
	 */
	function __construct($config, $errorstop = true) {
		$this->setConfig($config);
		$this->connection = $this->last_result = NULL;
		if ($this->config_valid) {
			if ($this->use_utf8) {
				if ($this->hasUtf8mb4Support('general')) {
					$charset = ';charset=utf8mb4';
				} else {
					$charset = ';charset=utf8';
				}
			} else {
				$charset = '';
			}
			try {
				$socket = '';
				if (!empty($this->mysql_socket)) {
					$socket = ';unix_socket=' . $this->mysql_socket;
				}
				$port = '';
				if (!empty($this->mysql_port)) {
					$port = ';port=' . $this->mysql_port;
				}
				$this->connection = new PDO('mysql:host=' . $this->mysql_host . ';dbname=' . $this->mysql_database . $charset . $port . $socket, $this->mysql_user, $this->mysql_pass);
			} catch (PDOException $e) {
				$this->last_result = $e;
				$error_msg = sprintf(gettext('MySql Error: Zenphoto received the error %s when connecting to the database server.'), $e->getMessage());
				dbbase::logConnectionError($error_msg, $errorstop);
				$this->connection = NULL;
			}
		}
		if ($this->connection) {
			// according to docs needee for PHP 5.3 and older so charset above should be sufficient
			/*if ($utf8) {
				try {
					$this->connection->query("SET NAMES 'utf8'");
				} catch (PDOException $e) {
					//	:(
				}
			} */
			// set the sql_mode to relaxed (if possible)
			try {
				$this->connection->query('SET SESSION sql_mode="";');
			} catch (PDOException $e) {
				//	What can we do :(
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
		$this->last_result = false;
		if ($this->connection) {
			try {
				$this->last_result = $this->connection->query($sql);
			} catch (PDOException $e) {
				$this->last_result = false;
			}
			if (!$this->last_result && $errorstop) {
				$sql = str_replace('`' . $this->mysql_prefix, '`[' . gettext('prefix') . ']', $sql);
				$sql = str_replace($this->mysql_database, '[' . gettext('DB') . ']', $sql);
				trigger_error(sprintf(gettext('%1$s Error: ( %2$s ) failed. %1$s returned the error %3$s'), DATABASE_SOFTWARE, $sql, $this->getError()), E_USER_ERROR);
			}
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
		if ($this->connection) {
			return $this->connection->quote($string);
		}
		return $string;
	}

	/*
	 * returns the insert id of the last database insert
	 */
	function insertID() {
		if ($this->connection) {
			return $this->connection->lastInsertId();
		}
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
		if ($this->connection) {
			return $this->connection->getAttribute(PDO::ATTR_CLIENT_VERSION);
		}
	}

}