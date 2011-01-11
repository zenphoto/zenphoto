<?php

/**
 * database core functions for PDO::MySQL
 *
 * Note: This Database support script is redundant to functions-db-MySQL.php.
 * It is a prototype for the Zenphoto database abstraction. It is left in the
 * package just in case someone finds it useful.
 *
 * @package core
 */

// force UTF-8 Ø

define('DATABASE_SOFTWARE','PDO::MySql');

/**
 * Connect to the database server and select the database.
 * @param bool $errorstop set to false to omit error messages
 * @return true if successful connection
	*/
function db_connect($errorstop=true) {
	global $_zp_DB_connection, $_zp_DB_last_result, $_zp_conf_vars;
	$_zp_DB_last_result = NULL;
	$db = $_zp_conf_vars['mysql_database'];
	if (!is_array($_zp_conf_vars)) {
		if ($errorstop) {
			zp_error(gettext('The <code>$_zp_conf_vars</code> variable is not an array. Zenphoto has not been instantiated correctly.'));
		}
		return false;
	}
	try {
		$hostname = $_zp_conf_vars['mysql_host'];
		$username = $_zp_conf_vars['mysql_user'];
		$password = $_zp_conf_vars['mysql_pass'];
		$_zp_DB_connection = new PDO("mysql:host=$hostname;dbname=$db", $username, $password);
	} catch(PDOException $e) {
		$_zp_DB_last_result = $e;
		if ( $errorstop) {
			zp_error(sprintf(gettext('MySql Error: Zenphoto received the error <em>%s</em> when connecting to the database server.'),$e->getMessage()));
		}
		return false;
	}
	if (array_key_exists('UTF-8', $_zp_conf_vars) && $_zp_conf_vars['UTF-8']) {
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
	return array('application'=>DATABASE_SOFTWARE,'required'=>'5.0','desired'=>'5.1','version'=>$dbversion);
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
	$sql = "SHOW GRANTS FOR " . $_zp_conf_vars['mysql_user'].";";
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
	global $_zp_conf_vars;
	switch ($what) {
		case 'tables':
			$sql = "SHOW TABLES FROM `".$_zp_conf_vars['mysql_database']."` LIKE '".$_zp_conf_vars['mysql_prefix']."%'";
			return query($sql, false);
		case 'columns':
			$sql = 'SHOW FULL COLUMNS FROM `'.$_zp_conf_vars['mysql_prefix'].$aux.'`';
			return query($sql, false);
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

require_once('functions-PDO.php');
?>
