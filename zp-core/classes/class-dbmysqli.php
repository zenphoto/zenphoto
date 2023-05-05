<?php
// force UTF-8 Ø
/**
 * Database core class for the MySQLi library
 *
 * @package zpcore\classes\database
 * 
 * @since 1.6 - reworked as class
 */
class dbMySQLi extends dbBase {

	/**
	 * Connect to the database server and select the database.
	 * @param array $config the db configuration parameters
	 * @param bool $errorstop set to false to omit error messages
	 * @return true if successful connection
	 */
	function __construct($config, $errorstop = true) {
		$this->setConfig($config);
		if ($this->config_valid) {
			$socket = null;
			if (!empty($this->mysql_socket)) {
				$socket = $this->mysql_socket;
			}
			try {
				$this->connection = new mysqli($this->mysql_host, $this->mysql_user, $this->mysql_pass, $this->mysql_database, $this->mysql_port, $socket);
			} catch (Exception $e) {
				dbbase::logConnectionError($e->getMessage(), $errorstop);
				if (is_object($this->connection) && $this->connection->connect_error) {
					$error_msg = sprintf(gettext('MySql Error: Zenphoto received the error %s when connecting to the database server.'), $this->connection->connect_error) ;
					dbbase::logConnectionError($error_msg, $errorstop);
				}
				$this->connection = null;
			}
		}
		if ($this->connection) {
			if ($this->use_utf8) {
				if ($this->hasUtf8mb4Support('general')) {
					$charset = 'utf8mb4';
				} else {
					$charset = 'utf8';
				}
				$this->connection->set_charset($charset);
			}
			// set the sql_mode to relaxed (if possible)
			@$this->connection->query('SET SESSION sql_mode="";');
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
		if ($this->connection) {
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
				$sql = str_replace('`' . $this->mysql_prefix, '`[' . gettext('prefix') . ']', $sql);
				$sql = str_replace($this->mysql_database, '[' . gettext('DB') . ']', $sql);
				trigger_error(sprintf(gettext('%1$s Error: ( %2$s ) failed. %1$s returned the error %3$s'), DATABASE_SOFTWARE, $sql, $this->getError()), E_USER_ERROR);
			}
			return $last_result;
		}
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
		if ($this->connection && !is_null($string)) {
			$escaped = $this->connection->real_escape_string($string);
			if ($addquotes) {
				return "'" . $escaped . "'";
			} else {
				return $escaped;
			}
		}
		return $string;
	}

	/*
	 * returns the insert id of the last database insert
	 */
	function insertID() {
		if ($this->connection) {
			return $this->connection->insert_id;
		}
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
		if ($this->connection) {
			return mysqli_error($this->connection);
		}
		if (!$msg = mysqli_connect_error()) {
			$msg = sprintf(gettext('%s not connected'), DATABASE_SOFTWARE);
		}
		return $msg;
	}

	/*
	 * Get number of affected rows in previous operation
	 */
	function getAffectedRows() {
		if ($this->connection) {
			return $this->connection->affected_rows;
		}
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
		return mysqli_get_client_info();
	}

	
}