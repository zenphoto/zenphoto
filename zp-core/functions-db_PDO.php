<?php

/**
 * database core functions for PDO implementations
 *
 * @author Stephen Billard (sbillard)
 *
 * @package core
 */
// force UTF-8 Ø

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
	if ($_zp_DB_connection) {
		try {
			$_zp_DB_last_result = $_zp_DB_connection->query($sql);
		} catch (PDOException $e) {
			$_zp_DB_last_result = false;
		}
	}
	if (!$_zp_DB_last_result && $errorstop) {
		dbErrorReport($sql);
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
 * PDO real_escape_string standin that insures the DB connection is passed.
 *
 * @param string $string
 * @return string
 */
function db_escape($string) {
	global $_zp_DB_connection;
	if ($_zp_DB_connection) {
		return trim($_zp_DB_connection->quote($string), "'" . '"');
	} else {
		return addslashes($string);
	}
}

/*
 * returns the insert id of the last database insert
 */

function db_insert_id() {
	global $_zp_DB_connection;
	return $_zp_DB_connection->lastInsertId();
}

/*
 * Fetch a result row as an associative array
 */

function db_fetch_assoc($resource) {
	if (is_object($resource)) {
		return $resource->fetch(PDO::FETCH_ASSOC);
	}
	return false;
}

/*
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

/*
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

/*
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

?>
