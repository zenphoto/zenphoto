<?php

/**
 * Database core functions for the PDO::MySQL library
 *
 * Note: PHP version 5 states that the MySQL library is "Maintenance only, Long term deprecation announced."
 * It recommends using the PDO::MySQL or the MySQLi library instead.
 *
 * @package core
 * @subpackage database-handlers\functions-db-pdo-mysql
 */
// force UTF-8 Ø

define('DATABASE_SOFTWARE', 'PDO::MySQL');
Define('DATABASE_MIN_VERSION', '5.0.7');
Define('DATABASE_DESIRED_VERSION', '5.5.3');

/**
 * Connect to the database server and select the database.
 * @param array $config the db configuration parameters
 * @param bool $errorstop set to false to omit error messages
 * @return true if successful connection
 */
function db_connect($config, $errorstop = true) {
	global $_zp_DB_connection, $_zp_DB_details, $_zp_DB_last_result;
	$_zp_DB_details = unserialize(DB_NOT_CONNECTED);
	$_zp_DB_connection = $_zp_DB_last_result = NULL;
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
		if (class_exists('PDO')) {
			$_zp_DB_connection = new PDO("mysql:host=$hostname;dbname=$db$utf8;port=$port", $username, $password);
		}
	} catch (PDOException $e) {
		$_zp_DB_last_result = $e;
		if ($errorstop) {
			zp_error(sprintf(gettext('MySql Error: Zenphoto received the error %s when connecting to the database server.'), $e->getMessage()));
		}
		$_zp_DB_connection = NULL;
		return false;
	}
	$_zp_DB_details = $config;
	if ($utf8 && version_compare(PHP_VERSION, '5.5.3', '<')) {
		try {
			$_zp_DB_connection->query("SET NAMES 'utf8'");
		} catch (PDOException $e) {
			//	:(
		}
	}
	// set the sql_mode to relaxed (if possible)
	try {
		$_zp_DB_connection->query('SET SESSION sql_mode="";');
	} catch (PDOException $e) {
		//	What can we do :(
	}
	return $_zp_DB_connection;
}

/*
 * report the software of the database
 */

function db_software() {
	$dbversion = db_getServerInfo();
	preg_match('/[0-9,\.]*/', $dbversion, $matches);
	return array('application' => DATABASE_SOFTWARE, 'required' => DATABASE_MIN_VERSION, 'desired' => DATABASE_DESIRED_VERSION, 'version' => $matches[0]);
}

/**
 * create the database
 */
function db_create() {
	global $_zp_DB_details;
	$sql = 'CREATE DATABASE IF NOT EXISTS ' . '`' . $_zp_DB_details['mysql_database'] . '`' . db_collation();
	return query($sql, false);
}

/**
 * Returns user's permissions on the database
 */
function db_permissions() {
	global $_zp_DB_details;
	$sql = "SHOW GRANTS FOR " . $_zp_DB_details['mysql_user'] . ";";
	$result = query($sql, false);
	if (!$result) {
		$result = query("SHOW GRANTS;", false);
	}
	if ($result) {
		$db_results = array();
		while ($onerow = db_fetch_row($result)) {
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
function db_setSQLmode() {
	return query('SET SESSION sql_mode=""', false);
}

/**
 * Queries the SQL session mode
 */
function db_getSQLmode() {
	$result = query('SELECT @@SESSION.sql_mode;', false);
	if ($result) {
		$row = db_fetch_row($result);
		return $row[0];
	}
	return false;
}

function db_collation() {
	return ' CHARACTER SET utf8 COLLATE utf8_unicode_ci';
}

function db_create_table(&$sql) {
	return query($sql, false);
}

function db_table_update(&$sql) {
	return query($sql, false);
}

function db_show($what, $aux = '') {
	global $_zp_DB_details;
	switch ($what) {
		case 'tables':
			$sql = "SHOW TABLES FROM `" . $_zp_DB_details['mysql_database'] . "` LIKE '" . db_LIKE_escape($_zp_DB_details['mysql_prefix']) . "%'";
			return query($sql, false);
		case 'columns':
			$sql = 'SHOW FULL COLUMNS FROM `' . $_zp_DB_details['mysql_prefix'] . $aux . '`';
			return query($sql, false);
		case 'variables':
			$sql = "SHOW VARIABLES LIKE '$aux'";
			return query_full_array($sql);
		case 'index':
			$sql = "SHOW INDEX FROM `" . $_zp_DB_details['mysql_database'] . '`.' . $aux;
			return query_full_array($sql);
	}
}

function db_list_fields($table) {
	$result = db_show('columns', $table);
	if ($result) {
		$fields = array();
		while ($row = db_fetch_assoc($result)) {
			$fields[] = $row;
		}
		return $fields;
	} else {
		return false;
	}
}

function db_truncate_table($table) {
	global $_zp_DB_details;
	$sql = 'TRUNCATE ' . $_zp_DB_details['mysql_prefix'] . $table;
	return query($sql, false);
}

function db_LIKE_escape($str) {
	return strtr($str, array('_' => '\\_', '%' => '\\%'));
}

/**
 * The main query function. Runs the SQL on the connection and handles errors.
 * @param string $sql sql code
 * @param bool $errorstop set to false to supress the error message
 * @return results of the sql statements
 * @since 0.6
 */
function query($sql, $errorstop = true) {
	global $_zp_DB_connection, $_zp_DB_last_result, $_zp_DB_details;
	$_zp_DB_last_result = false;
	try {
		$_zp_DB_last_result = $_zp_DB_connection->query($sql);
	} catch (PDOException $e) {
		$_zp_DB_last_result = false;
	}
	if (!$_zp_DB_last_result && $errorstop) {
		$sql = str_replace('`' . $_zp_DB_details['mysql_prefix'], '`[' . gettext('prefix') . ']', $sql);
		$sql = str_replace($_zp_DB_details['mysql_database'], '[' . gettext('DB') . ']', $sql);
		trigger_error(sprintf(gettext('%1$s Error: ( %2$s ) failed. %1$s returned the error %3$s'), DATABASE_SOFTWARE, $sql, db_error()), E_USER_ERROR);
	}
	return $_zp_DB_last_result;
}

/**
 * Runs a SQL query and returns an associative array of the first row.
 * Doesn't handle multiple rows, so this should only be used for unique entries.
 * @param string $sql sql code
 * @param bool $errorstop set to false to supress the error message
 * @return results of the sql statements
 * @since 0.6
 */
function query_single_row($sql, $errorstop = true) {
	$result = query($sql, $errorstop);
	if ($result) {
		$row = db_fetch_assoc($result);
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
function query_full_array($sql, $errorstop = true, $key = NULL) {
	$result = query($sql, $errorstop);
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
function db_quote($string) {
	global $_zp_DB_connection;
	return $_zp_DB_connection->quote($string);
}

/**
 * returns the insert id of the last database insert
 */
function db_insert_id() {
	global $_zp_DB_connection;
	return $_zp_DB_connection->lastInsertId();
}

/**
 * Fetch a result row as an associative array
 */
function db_fetch_assoc($resource) {
	if (is_object($resource)) {
		return $resource->fetch(PDO::FETCH_ASSOC);
	}
	return false;
}

/**
 * Returns the text of the error message from previous operation
 */
function db_error() {
	global $_zp_DB_last_result;
	if (is_object($_zp_DB_last_result)) {
		return $_zp_DB_last_result->getMessage();
	} else {
		return sprintf(gettext('%s not connected'), DATABASE_SOFTWARE);
	}
}

/**
 * Get number of affected rows in previous operation
 */
function db_affected_rows() {
	global $_zp_DB_last_result;
	if (is_object($_zp_DB_last_result)) {
		return $_zp_DB_last_result->rowCount();
	} else {
		return 0;
	}
}

/**
 * Get a result row as an enumerated array
 */

function db_fetch_row($result) {
	if (is_object($result)) {
		return $result->fetch(PDO::FETCH_NUM);
	}
	return false;
}

/*
 * Get number of rows in result
 */

function db_num_rows($result) {
	if (is_array($result)) {
		return count($result);
	} else {
		return $result->rowCount();
	}
}

/**
 * Closes the database
 */
function db_close() {
	global $_zp_DB_connection;
	$_zp_DB_connection = NULL;
	return true;
}

function db_free_result($result) {
	return $result->closeCursor();
}

/**
 * Returns the server info
 * @return string
 */
function db_getServerInfo() {
	global $_zp_DB_connection;
	return trim($_zp_DB_connection->getAttribute(PDO::ATTR_SERVER_VERSION));
}

/**
 * Returns the client info
 * @return string
 */
function db_getClientInfo() {
	global $_zp_DB_connection;
	return $_zp_DB_connection->getAttribute(PDO::ATTR_CLIENT_VERSION);
}
