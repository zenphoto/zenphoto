<?php
/** 
 * Deprecated database core functions - Legacy backward compatibility wrapper for the active database handler class
 * 
 * Use the global object `$_zp_db` and its methods of the same name instead of these functions.
 * 
 * Within main object classes like album, image etc. the db handler object is generally also available via `$this->db`.
 *
 * @package zpcore\functions\deprecated
 * 
 * @deprecated 2.0 Use the global database object variable $_zp_db and the class methods instead
 * @since 1.6
 */

/**
 * Connect to the database server and select the database.
 * @param array $config the db configuration parameters
 * @param bool $errorstop set to false to omit error messages
 * @return true if successful connection
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method connect() instead.
 * @since 1.6
 */
function db_connect($config, $errorstop = true) {
	global $_zp_db, $_zp_DB_connection;
	$_zp_DB_connection = $_zp_db->connect(); // legacy global to be sure……
	//db functions load too early for the deprecated functions plugin so we notify manually like this.
	deprecationNotice(gettext('Use the global object $_zp_db and the class method connect() instead.'));
	return $_zp_db->connect();
}

/**
 * The main query function. Runs the SQL on the connection and handles errors.
 * @param string $sql sql code
 * @param bool $errorstop set to false to supress the error message
 * @return results of the sql statements
 * @since 0.6
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method of the same name instead.
 * @since 1.6
 */
function query($sql, $errorstop = true) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method of the same name instead.'));
	return $_zp_db->query($sql, $errorstop);
}

/**
 * Runs a SQL query and returns an associative array of the first row.
 * Doesn't handle multiple rows, so this should only be used for unique entries.
 * @param string $sql sql code
 * @param bool $errorstop set to false to supress the error message
 * @return results of the sql statements
 * @since 0.6
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method querySingleRow() instead.
 * @since 1.6
 */
function query_single_row($sql, $errorstop = true) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method querySingleRow() instead.'));
	return $_zp_db->querySingleRow($sql, $errorstop);
}

/**
 * Runs a SQL query and returns an array of associative arrays of every row returned.
 * @param string $sql sql code
 * @param bool $errorstop set to false to supress the error message
 * @param string $key optional array index key
 * @return results of the sql statements
 * @since 0.6
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method queryFullArray() instead.
 * @since 1.6
 */
function query_full_array($sql, $errorstop = true, $key = NULL) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method queryFullArray() instead.'));
	return $_zp_db->queryFullArray($sql, $errorstop, $key);
}

/**
 * mysqli_real_escape_string standin that insures the DB connection is passed.
 *
 * @param string $string
 * @return string
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method quote() instead.
 * @since 1.6
 */
function db_quote($string, $addquotes = true) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method quote() instead.'));
	return $_zp_db->quote($string, $addquotes);
}

/**
 * returns the insert id of the last database insert
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method insertID() instead.
 * @since 1.6
 */
function db_insert_id() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method insertID() instead.'));
	return $_zp_db->insertID();
}

/**
 * Fetch a result row as an associative array
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method fetchAssoc() instead.
 * @since 1.6
 */
function db_fetch_assoc($resource) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method fetchAssoc() instead.'));
	return $_zp_db->fetchAssoc($resource);
}

/**
 * Returns the text of the error message from previous operation
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method getError() instead.
 * @since 1.6
 */
function db_error() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method getError() instead.'));
	return $_zp_db->getError();
}

/**
 * Get number of affected rows in previous operation
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method getAffectedRows() instead.
 * @since 1.6
 */
function db_affected_rows() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method getAffectedRows() instead.'));
	return $_zp_db->getAffectedRows();
}

/**
 * Get a result row as an enumerated array
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method fetchRow() instead.
 * @since 1.6
 */
function db_fetch_row($result) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method fetchRow() instead.'));
	return $_zp_db->fetchRow($result);
}

/**
 * Get number of rows in result
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method getNumRows() instead.
 * @since 1.6
 */
function db_num_rows($result) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method getNumRows() instead.'));
	return $_zp_db->getNumRows($result);
}

/**
 * Closes the database
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method close() instead.
 * @since 1.6
 */
function db_close() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method close() instead.'));
	return $_zp_db->close();
}

/**
 * report the software of the database
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method getSoftware() instead. 
 * @since 1.6 
 */
function db_software() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method getSoftware() instead.'));
	return $_zp_db->getSoftware();
}

/**
 * create the database
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method create() instead
 * @since 1.6
 */
function db_create() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method create() instead.'));
	return $_zp_db->create();
}

/**
 * Returns user's permissions on the database
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method getPermissions() instead.
 * @since 1.6
 */
function db_permissions() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method getPermissions() instead.'));
	return $_zp_db->getPermissions();
}

/**
 * Sets the SQL session mode to empty
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method setSQLmode() instead.
 * @since 1.6
 */
function db_setSQLmode() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method setSQLmode() instead.'));
	return $_zp_db->setSQLmode();
}

/**
 * Queries the SQL session mode
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method getSQLmode() instead.
 * @since 1.6
 */
function db_getSQLmode() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method getSQLmode() instead.'));
	return $_zp_db->getSQLmode();
}

/**
 * @deprecated 2.0 Use the global object $_zp_db and the class method getCollation() instead.
 * @since 1.6
 */
function db_collation() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method getCollationSetClause() instead.'));
	return $_zp_db->getCollationSetClause();
}

/**
 * @deprecated 2.0 Use the global object $_zp_db and the class method createTable() instead.
 * @since 1.6
 */
function db_create_table($sql) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method createTable() instead.'));
	return $_zp_db->createTable($sql);
}

/**
 * @deprecated 2.0 Use the global object $_zp_db and the class method tableUpdate() instead.
 * @since 1.6
 */
function db_table_update($sql) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method tableUpdate() instead.'));
	return $_zp_db->tableUpdate($sql);
}

/**
 * @deprecated 2.0 Use the global object $_zp_db and the class method show() instead.
 * @since 1.6
 */
function db_show($what, $aux = '') {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method show() instead.'));
	return $_zp_db->show($what, $aux);
}

/**
 * @deprecated 2.0 Use the global object $_zp_db and the class method listFields() instead.
 * @since 1.6
 */
function db_list_fields($table) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method getFields() instead.'));
	return $_zp_db->getFields($table);
}

/**
 * @deprecated 2.0 Use the global object $_zp_db and the class method truncateTable() instead.
 * @since 1.6
 */
function db_truncate_table($table) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method truncateTable() instead.'));
	return $_zp_db->truncateTable($table);
}

/**
 * @deprecated 2.0 Use the global object $_zp_db and the class method likeEscape() instead.
 * @since 1.6
 */
function db_LIKE_escape($str) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method likeEscape() instead.'));
	return $_zp_db->likeEscape($str);
}

/**
 * @deprecated 2.0 Use the global object $_zp_db and the class method freeResult() instead.
 * @since 1.6
 */
function db_free_result($result) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method freeResult() instead.'));
	return $_zp_db->freeResult($result);
}

/**
 * Prefix a table name with a user-defined string to avoid conflicts.
 * This MUST be used in all database queries.
 * @param string $tablename name of the table
 * @return prefixed table name
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method of the same name instead.
 * @since 1.6
 */
function prefix($tablename = NULL) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method of the same name instead.'));
	return $_zp_db->prefix($tablename);
}

/**
 * Constructs a WHERE clause ("WHERE uniqueid1='uniquevalue1' AND uniqueid2='uniquevalue2' ...")
 *  from an array (map) of variables and their values which identifies a unique record
 *  in the database table.
 * @param string $unique_set what to add to the WHERE clause
 * @return contructed WHERE cleause
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method of the same name instead
 * @since 1.6
 */
function getWhereClause($unique_set) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method of the same name instead.'));
	return $_zp_db->getWhereClause($unique_set);
}

/**
 * Constructs a SET clause ("SET uniqueid1='uniquevalue1', uniqueid2='uniquevalue2' ...")
 *  from an array (map) of variables and their values which identifies a unique record
 *  in the database table. Used to 'move' records. Note: does not check anything.
 * @param string $new_unique_set what to add to the SET clause
 * @return contructed SET cleause
 * @since 0.6
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method of the same name instead
 * @since 1.6
 */
function getSetClause($new_unique_set) {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method of the same name instead.'));
	return $_zp_db->getSetClause($new_unique_set);
}

/**
 * returns the connected database name
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method getDBName() instead.
 * @since 1.6
 */
function db_name() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method getDBName() instead.'));
	return $_zp_db->getDBName();
}

/**
 * Counts entries in a db table
 * 
 * @param string $table Table name
 * @param string $clause Where clause for the count query
 * @param string $field Which fields to count (default: id)
 * @return int
 * 
 * @deprecated 2.0 Use the global object $_zp_db and the class method count() instead.
 * @since 1.6
 */
function db_count($table, $clause = NULL, $field = "id") {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method count() instead.'));
	return $_zp_db->count($table, $clause, $field);
}

/**
 * Returns the server info
 * @since 1.5.7
 * @deprecated 2.0 Use the global object $_zp_db and the class method getServerInfo() instead.
 * @return string
 */
function db_getServerInfo() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method getServerInfo() instead.'));
	return $_zp_db->getServerInfo();
}

/**
 * Returns the client info
 * @since 1.5.7
 * @deprecated 2.0 Use the global object $_zp_db and the class method getClientInfo() instead.
 * @return string
 */
function db_getClientInfo() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method getClientInfo() instead.'));
	return $_zp_db->getClientInfo();
}

/**
 * Gets the plain version number
 * 
 * @since 1.5.8
 * @deprecate ZenphotoCMS 2.0 Use the global object $_zp_db and the class method getVersion() instead.
 * @return int
 */
function db_getVersion() {
	global $_zp_db;
	deprecationNotice(gettext('Use the global object $_zp_db and the class method getVersion() instead.'));
	return $_zp_db->getVersion();
}