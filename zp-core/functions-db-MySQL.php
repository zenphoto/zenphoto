<?php
/**
 * Standard support for MySQL databases
 *
 * database core functions
 * @package core
 */

// force UTF-8 Ø

define('DATABASE_SOFTWARE','MySql');

/**
 * Connect to the database server and select the database.
 * @param bool $noerrmsg set to false to omit error messages
 *@return true if successful connection
 *@since 0.6
	*/
function db_connect($errorstop=true) {
	global $_zp_DB_connection, $_zp_conf_vars;
	$db = $_zp_conf_vars['mysql_database'];
	if (!function_exists('mysql_connect')) {
		if ($errorstop) {
			zp_error(gettext('MySQL Error: The PHP MySQL extensions have not been installed correctly. Please ask your administrator to add MySQL support to your PHP installation.'));
		}
		return false;
	}
	if (!is_array($_zp_conf_vars)) {
		if ($errorstop) {
			zp_error(gettext('The <code>$_zp_conf_vars</code> variable is not an array. Zenphoto has not been instantiated correctly.'));
		}
		return false;
	}
	$_zp_DB_connection = @mysql_connect($_zp_conf_vars['mysql_host'], $_zp_conf_vars['mysql_user'], $_zp_conf_vars['mysql_pass']);
	if (!$_zp_DB_connection) {
		if ($errorstop) {
			zp_error(sprintf(gettext('MySQL Error: Zenphoto received the error <em>%s</em> when connecting to the database server.'),mysql_error()));
		}
		return false;
	}

	if (!@mysql_select_db($db)) {
		if ($errorstop) {
			zp_error(sprintf(gettext('MySQL Error: The database is connected, but MySQL returned the error <em>%1$s</em> when Zenphoto tried to select the database %2$s.'),mysql_error(),$db));
		}
		return false;
	}
	if (array_key_exists('UTF-8', $_zp_conf_vars) && $_zp_conf_vars['UTF-8']) {
		mysql_query("SET NAMES 'utf8'");
	}
	// set the sql_mode to relaxed (if possible)
	@mysql_query('SET SESSION sql_mode="";');
	return $_zp_DB_connection;
}


/**
 * The main query function. Runs the SQL on the connection and handles errors.
 * @param string $sql sql code
 * @param bool $noerrmsg set to false to supress the error message
 * @return results of the sql statements
 * @since 0.6
 */
function query($sql, $errorstop=true) {
	global $_zp_DB_connection, $_zp_conf_vars;
	if (is_null($_zp_DB_connection)) {
		db_connect();
	}
	// Changed this to mysql_query - *never* call query functions recursively...
	$result = mysql_query($sql, $_zp_DB_connection);
	if (!$result) {
		if($errorstop) {
			$sql = html_encode($sql);
			zp_error(sprintf(gettext('MySQL Query ( <em>%1$s</em> ) failed. MySQL returned the error <em>%2$s</em>' ),$sql,mysql_error()));
		}
		return false;
	}
	return $result;
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
	if ($result) {
		return mysql_fetch_assoc($result);
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
	if ($result) {
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
 * get result data
 */
function db_result($result, $row, $field=0) {
	return mysql_result($result, $row, $field);
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
	return mysql_error();
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
	if ($result) {
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
	$rslt = mysql_close($_zp_DB_connection);
	$_zp_DB_connection = NULL;
	return $rslt;
}

/*
 * report the software of the database
 */
function db_software() {
	$dbversion = trim(@mysql_get_server_info());
	$i = strpos($dbversion, "-");
	if ($i !== false) {
		$dbversion = substr($dbversion, 0, $i);
	}
	return array('application'=>'MySQL','required'=>'4.1','desired'=>'5.0','version'=>$dbversion);
}

/**
 * create the database
 */
function db_create() {
	$sql = 'CREATE DATABASE IF NOT EXISTS '.'`'.$_zp_conf_vars['mysql_database'].'`'.$collation;
	return query($sql, false);
}

/**
 * Returns user's permissions on the database
 */
function db_permissions() {
	global $_zp_conf_vars;
	$dbversion = db_software();
	$dbversion = $dbversion['version'];
	if (versioncheck('4.2.1', '4.2.1', $dbversion)) {
		$sql = "SHOW GRANTS FOR CURRENT_USER;";
	} else {
		$sql = "SHOW GRANTS FOR " . $_zp_conf_vars['mysql_user'].";";
	}
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
	$software = db_software();
	if (substr(trim($software['version']), 0, 1) > '4') {
		$collation = ' CHARACTER SET utf8 COLLATE utf8_unicode_ci';
	} else {
		$collation = '';
	}
	return $collation;
}

function db_create_table(&$sql) {
	return query($sql,false);
}

function db_table_update(&$sql) {
	return query($sql,false);
}

function db_show($what,$aux='') {
	global $_zp_conf_vars;
	switch ($what) {
		case 'tables':
			$sql = "SHOW TABLES FROM `".$_zp_conf_vars['mysql_database']."` LIKE '".$_zp_conf_vars['mysql_prefix']."%'";
			return query($sql, false);
		case 'columns':
			$sql = 'SHOW FULL COLUMNS FROM `'.$_zp_conf_vars['mysql_prefix'].$aux.'`';
			return query($sql, true);
		case 'variables':
			$sql = "SHOW VARIABLES LIKE '$aux'";
			return query_full_array($sql);
	}
}

function db_list_fields($table,$raw=false) {
	$result = db_show('columns',$table);
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
	global $_zp_conf_vars;
	$sql = 'TRUNCATE '.$_zp_conf_vars['mysql_prefix'].$table;
	return query($sql, false);
}

?>
