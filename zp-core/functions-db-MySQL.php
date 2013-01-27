<?php
/**
 * Database core functions forthe  MySQL legacy library
 *
 * Note: PHP version 5 states that the MySQL library is "Maintenance only, Long term deprecation announced."
 * It recommends using the PDO::MySQL or the MySQLi library instead.
 *
 * @package core
 */

// force UTF-8 Ø

define('DATABASE_SOFTWARE','MySQL');
Define('DATABASE_MIN_VERSION','5.0.0');
Define('DATABASE_DESIRED_VERSION','5.5.0');

/**
 * Connect to the database server and select the database.
 * @param array $config the db configuration parameters
 * @param bool $errorstop set to false to omit error messages
 * @return true if successful connection
 */
function db_connect($config, $errorstop=true) {
	global $_zp_DB_connection, $_zp_DB_details;
	$_zp_DB_details = unserialize(DB_NOT_CONNECTED);
	if (function_exists('mysql_connect')) {
		$_zp_DB_connection = @mysql_connect($config['mysql_host'], $config['mysql_user'], $config['mysql_pass']);
	} else {
		$_zp_DB_connection = NULL;
	}
	if (!$_zp_DB_connection) {
		if ($errorstop) {
			zp_error(sprintf(gettext('MySQL Error: Zenphoto received the error %s when connecting to the database server.'),mysql_error()));
		}
		return false;
	}
	$_zp_DB_details['mysql_host'] = $config['mysql_host'];
	if (!@mysql_select_db($config['mysql_database'])) {
		if ($errorstop) {
			zp_error(sprintf(gettext('MySQL Error: MySQL returned the error %1$s when Zenphoto tried to select the database %2$s.'),mysql_error(),$config['mysql_database']));
		}
		return false;
	}
	$_zp_DB_details = $config;
	if (array_key_exists('UTF-8', $config) && $config['UTF-8']) {
		mysql_query("SET NAMES 'utf8'");
	}
	// set the sql_mode to relaxed (if possible)
	@mysql_query('SET SESSION sql_mode="";');
	return $_zp_DB_connection;
}


/**
 * The main query function. Runs the SQL on the connection and handles errors.
 * @param string $sql sql code
 * @param bool $errorstop set to false to supress the error message
 * @return results of the sql statements
 * @since 0.6
 */
function query($sql, $errorstop=true) {
	global $_zp_DB_connection, $_zp_DB_details;
	if ($result = @mysql_query($sql, $_zp_DB_connection)) {
		return $result;
	}
	if($errorstop) {
		$sql = str_replace($_zp_DB_details['mysql_prefix'], '['.gettext('prefix').']',$sql);
		$sql = str_replace($_zp_DB_details['mysql_database'], '['.gettext('DB').']',$sql);
		trigger_error(sprintf(gettext('%1$s Error: ( %2$s ) failed. %1$s returned the error %3$s'),DATABASE_SOFTWARE,$sql,db_error()), E_USER_ERROR);
	}
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
function query_single_row($sql, $errorstop=true) {
	$result = query($sql, $errorstop);
	if (is_resource($result)) {
		$row = mysql_fetch_assoc($result);
		mysql_free_result($result);
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
function query_full_array($sql, $errorstop=true, $key=NULL) {
	$result = query($sql, $errorstop);
	if (is_resource($result)) {
		$allrows = array();
		if (is_null($key)) {
			while ($row = mysql_fetch_assoc($result)) {
				$allrows[] = $row;
			}
		} else {
			while ($row = mysql_fetch_assoc($result)) {
				$allrows[$row[$key]] = $row;
			}
		}
		mysql_free_result($result);
		return $allrows;
	} else {
		return false;
	}
}

/**
 * mysql_real_escape_string standin that insures the DB connection is passed.
 *
 * @param string $string
 * @return string
 */
function db_quote($string) {
	global $_zp_DB_connection;
	return "'".mysql_real_escape_string($string,$_zp_DB_connection)."'";
}

/*
 * returns the insert id of the last database insert
 */
function db_insert_id() {
	return mysql_insert_id();
}

/*
 * Fetch a result row as an associative array
 */
function db_fetch_assoc($resource) {
	if ($resource) {
		return mysql_fetch_assoc($resource);
	}
	return false;
}

/*
 * Returns the text of the error message from previous operation
 */
function db_error() {
	global $_zp_DB_connection;
	if (is_object($_zp_DB_connection)) {
		return mysql_error();
	}
	return sprintf(gettext('%s not connected'),DATABASE_SOFTWARE);
}

/*
 * Get number of affected rows in previous operation
 */
function db_affected_rows() {
	return mysql_affected_rows();
}

/*
 * Get a result row as an enumerated array
 */
function db_fetch_row($result) {
	if (is_resource($result)) {
		return mysql_fetch_row($result);
	}
	return false;
}

/*
 * Get number of rows in result
 */
function db_num_rows($result) {
	return mysql_num_rows($result);
}

/**
 * Closes the database
 */
function db_close() {
	global $_zp_DB_connection;
	if ($_zp_DB_connection) {
		$rslt = mysql_close($_zp_DB_connection);
	} else {
		$rslt = true;
	}
	$_zp_DB_connection = NULL;
	return $rslt;
}

/*
 * report the software of the database
 */
function db_software() {
	$dbversion = trim(@mysql_get_server_info());
	preg_match('/[0-9,\.]*/', $dbversion, $matches);
	return array('application'=>DATABASE_SOFTWARE,'required'=>DATABASE_MIN_VERSION,'desired'=>DATABASE_DESIRED_VERSION,'version'=>$matches[0]);
}

/**
 * create the database
 */
function db_create() {
	global $_zp_DB_details;
	$sql = 'CREATE DATABASE IF NOT EXISTS '.'`'.$_zp_DB_details['mysql_database'].'`'.db_collation();
	return query($sql, false);
}

/**
 * Returns user's permissions on the database
 */
function db_permissions() {
	global $_zp_DB_details;
	$sql = "SHOW GRANTS FOR " . $_zp_DB_details['mysql_user'].";";
	$result = query($sql, false);
	if (!$result) {
		$result = query("SHOW GRANTS;", false);
	}
	if (is_resource($result)) {
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
	if (is_resource($result)) {
		$row = db_fetch_row($result);
		return $row[0];
	}
	return false;
}

function db_collation() {
	$collation = ' CHARACTER SET utf8 COLLATE utf8_unicode_ci';
	return $collation;
}

function db_create_table(&$sql) {
	return query($sql,false);
}

function db_table_update(&$sql) {
	return query($sql,false);
}

function db_show($what,$aux='') {
	global $_zp_DB_details;
	switch ($what) {
		case 'tables':
			$sql = "SHOW TABLES FROM `".$_zp_DB_details['mysql_database']."` LIKE '".db_LIKE_escape($_zp_DB_details['mysql_prefix'])."%'";
			return query($sql, false);
		case 'columns':
			$sql = 'SHOW FULL COLUMNS FROM `'.$_zp_DB_details['mysql_prefix'].$aux.'`';
			return query($sql, true);
		case 'variables':
			$sql = "SHOW VARIABLES LIKE '$aux'";
			return query_full_array($sql);
		case 'index':
			$sql = "SHOW INDEX FROM `".$_zp_DB_details['mysql_database'].'`.'.$aux;
			return query_full_array($sql);
	}
}

function db_list_fields($table) {
	$result = db_show('columns',$table);
	if (is_resource($result)) {
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
	$sql = 'TRUNCATE '.$_zp_DB_details['mysql_prefix'].$table;
	return query($sql, false);
}

function db_LIKE_escape($str) {
	return strtr($str, array('_'=>'\\_','%'=>'\\%'));
}


function db_free_result($result) {
	return mysql_free_result($result);
}

?>
