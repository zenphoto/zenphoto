<?php

/**
 * Database core functions for the PDO::MySQL library
 *
 * Note: PHP version 5 states that the MySQL library is "Maintenance only, Long term deprecation announced."
 * It recommends using the PDO::MySQL or the MySQLi library instead.
 *
 * @package core
 */

// force UTF-8 Ø

define('DATABASE_SOFTWARE','PDO::MySQL');
Define('DATABASE_MIN_VERSION','5.0.0');
Define('DATABASE_DESIRED_VERSION','5.5.0');

/**
 * Connect to the database server and select the database.
 * @param array $config the db configuration parameters
 * @param bool $errorstop set to false to omit error messages
 * @return true if successful connection
 */
function db_connect($config, $errorstop=true) {
	global $_zp_DB_connection, $_zp_DB_details, $_zp_DB_last_result;
	$_zp_DB_details = unserialize(DB_NOT_CONNECTED);
	$_zp_DB_connection = $_zp_DB_last_result = NULL;
	try {
		$db = $config['mysql_database'];
		$hostname = $config['mysql_host'];
		$username = $config['mysql_user'];
		$password = $config['mysql_pass'];
		if (class_exists('PDO')) {
			$_zp_DB_connection = new PDO("mysql:host=$hostname;dbname=$db", $username, $password);
		}
	} catch(PDOException $e) {
		$_zp_DB_last_result = $e;
		if ( $errorstop) {
			zp_error(sprintf(gettext('MySql Error: Zenphoto received the error %s when connecting to the database server.'),$e->getMessage()));
		}
		$_zp_DB_connection = NULL;
		return false;
	}
	$_zp_DB_details = $config;
	if (array_key_exists('UTF-8', $config) && $config['UTF-8']) {
		try {
			$_zp_DB_connection->query("SET NAMES 'utf8'");
		} catch (PDOException $e){
			//	:(
		}
	}
	// set the sql_mode to relaxed (if possible)
	try {
		$_zp_DB_connection->query('SET SESSION sql_mode="";');
	} catch (PDOException $e){
		//	What can we do :(
	}
	return $_zp_DB_connection;
}


/*
 * report the software of the database
 */
function db_software() {
	global $_zp_DB_connection;
	$dbversion = trim($_zp_DB_connection->getAttribute(PDO::ATTR_SERVER_VERSION));
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
			return query($sql, false);
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
	$sql = 'TRUNCATE '.$_zp_DB_details['mysql_prefix'].$table;
	return query($sql, false);
}

function db_LIKE_escape($str) {
	return strtr($str, array('_'=>'\\_','%'=>'\\%'));
}

require_once('functions-db_PDO.php');
?>
