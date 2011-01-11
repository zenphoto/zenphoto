<?php

/*
 * EXPERIMENTAL!!!!
 *
 * NOTE: It appears that SQLite is NOT be a viable database for Zenphoto as it is somewhat limited.
 *
 * Missing features that are required:
 * 		ALTER TABLE ... used to change column names and attributes.
 * 		SET @variable:=value (used by combi-news and search)
 *
 * Note: the setup log will contain all upgrade queries which returned syntax errors. However, many of these
 * will not be relevant to the current upgrade. Only those statements for upgrades added after the database was
 * created are at issue.
 *
 * This implementation should be considered a prototype for building database abstractions for arbitrary
 * databases.
 *
 * In addition to the model here, the switch which sets up the setup-sqlform and the reporting of the
 * databases supported may also need updating if the "connection" required information is different from MySQL.
 * Perhaps also the form itself will need to be extended. In exterme cases you may also have to update the "POST"
 * processing of the sqlform.
 *
 * From the SQLite documentation "Another way to look at SQLite is this: SQLite is not designed to replace Oracle.
 * It is designed to replace fopen()." http://www.sqlite.org/whentouse.html
 *
 */

/**
 * database core functions for PDO::SQLite
 * @package core
 */

// force UTF-8 Ø

define('DATABASE_SOFTWARE','PDO::SQLite');

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
	if (empty($folder) || $folder == 'localhost') {
		$folder = dirname(dirname(__FILE__)).'/zp-data/';
	} else {
		$folder = str_replace($_zp_conf_vars['mysql_host'],'\\','/');
		if (substr($folder, -1, 1) != '/') {
			$folder .= '/';
		}
	}
	try {
		$_zp_DB_connection = new PDO('sqlite:'.$folder.$_zp_conf_vars['mysql_database']);
	} catch(PDOException $e) {
		$_zp_DB_last_result = $e;
		if ($errorstop) {
			zp_error(sprintf(gettext('SQLite Error: Zenphoto received the error <em>%s</em> when connecting to the database server.'),$e->getMessage()));
		}
		return false;
	}
	try {
		$_zp_DB_connection->query('PRAGMA encoding = "UTF-8"');
	} catch(PDOException $e) {
		if (true || $noerrmsg) zp_error(sprintf(gettext('%1$s Error: Zenphoto received the error <em>%2$s</em> from the database server.'),DATABASE_SOFTWARE,$e->getMessage()));
		return false;
	}
	return $_zp_DB_connection;
}


/*
 * report the software of the database
 */
function db_software() {
	global $_zp_DB_connection;
	$dbversion = trim($_zp_DB_connection->getAttribute(PDO::ATTR_SERVER_VERSION));
	return array('application'=>DATABASE_SOFTWARE,'required'=>'3.6.15','desired'=>'3.6.15','version'=>$dbversion);
}

/**
 * create the database
 */
function db_create() {
	return false;	//	if the connect did not do it, can't be done
}

/**
 * Returns user's permissions on the database
 */
function db_permissions() {
	return array("GRANT ALL PRIVILEGES ON *.* TO XXX");
}

/**
 * Sets the SQL session mode to empty
 */
function db_setSQLmode() {
	// not needed?
	return true;
}

/**
 * Queries the SQL session mode
 */
function db_getSQLmode() {
	//	see above
	return '';
}

function db_collation() {
	return '';
}

function db_create_table(&$sql) {
	$sql = sqlite_transformQuery($sql,'create');
	$sql = str_replace('auto_increment', 'autoincrement', $sql);
	return query($sql,false);
}

function db_table_update(&$sql) {
	$sql = sqlite_transformQuery($sql,'update');
	return query($sql,false);
}

function db_show($what, $aux='') {
	global $_zp_conf_vars;
	switch ($what) {
		case 'tables':
			$sql = "SELECT name FROM `sqlite_master` WHERE type='table' AND name LIKE '".$_zp_conf_vars['mysql_prefix']."%'";
			return query($sql, false);
		case 'columns':
			$sql = 'PRAGMA table_info("'.$_zp_conf_vars['mysql_prefix'].$aux.'")';
			$result = query($sql, false);
			return $result;
		case 'variables':
			switch ($aux) {
				case 'character_set%':
					$sql = 'PRAGMA encoding';
					$rslt = query($sql, false);
					$result = array();
					if ($rslt) {
						while ($row = db_fetch_assoc($rslt)) {
							$result[] = array('Variable_name'=>'encoding','Value'=>$row['encoding']);
						}
					}
					return $result;
				case 'collation%':
					return false;
					/*	seems no useful data here
					$sql = 'PRAGMA collation_list';
					$rslt = query($sql, false);
					$result = array();
					if ($rslt) {
						while ($row = db_fetch_assoc($rslt)) {
							$result[] = $row;
						}
					}
					return $result;
					*/
			}
			return false;
	}
}

function db_list_fields($table, $raw=false) {
	$result = db_show('columns',$table);
	if ($result) {
		$fields = array();
		while ($row = db_fetch_assoc($result)) {
			if ($raw) {
				$fields[] = $row;
			} else {
				$fields[] = array('Collation' => 'utf8_unicode_ci',
													'Field' => $row['name'],
													'Default' => $row['dflt_value'],
													'Type' => $row['type']);
			}
		}
		return $fields;
	} else {
		return false;
	}
}

/**
 * used to transform "setup" create/updates of the database schema
 * @param $sql
 */
function sqlite_transformQuery($sql, $hint) {
	//	primary key
	$sql = str_replace('`id` int(11) UNSIGNED NOT NULL auto_increment', '`id` INTEGER PRIMARY KEY', $sql);
	$i = strpos($sql,'PRIMARY KEY (`id`)');
	if ($i !==false) {
		$s1 = substr($sql, 0, $i);
		$j = strrpos($sql, ',');
		$s2 = substr($sql,$i+19);
		$sql = substr($s1,0,$j).$s2;
	}
	// handle "unsigned" integers
	$count = preg_match_all('/int\(([0-9]+)\) unsigned/i',$sql,$matches);
	for ($i=0;$i<$count;$i++) {
		$pat = $matches[0][$i];
		$size = $matches[1][$i];
		$sql = str_replace($pat, 'unsignedint('.$size.')', $sql);
	}
	// remove secondary key statements. KEY `aux` (`aux`)
	$count = preg_match_all('/key.*\(.*\)/i',$sql,$matches);
	for ($k=0;$k<$count;$k++) {
		$pat = $matches[0][$k];
		$i = strpos($sql,$pat);
		$s1 = substr($sql, 0, $i);
		$j = strrpos($s1, ',');
		$s2 = substr($sql,$i+strlen($pat));
		$sql = substr($s1,0,$j).$s2;
	}
	return $sql;
}

function db_truncate_table($table) {
	global $_zp_conf_vars;
	$sql = 'DELETE FROM '.$_zp_conf_vars['mysql_prefix'].$table;
	return query($sql, false);
}

require_once('functions-PDO.php');
?>
