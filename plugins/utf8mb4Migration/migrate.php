<?php

/*
 * This plugin is a migration tool to move TEXT and LONGTEXT database fields to
 * utf8mb4 encoding and collation
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage development
 * @category package
 *
 * Copyright 2017 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 */

// force UTF-8 Ø

define("OFFSET_PATH", 3);
require_once(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME']))) . "/zp-core/admin-globals.php");

admin_securityChecks(ADMIN_RIGHTS, $return = currentRelativeURL());

XSRFdefender('utf8mb4Migration');

require_once(SERVERPATH . '/' . ZENFOLDER . '/setup/setup-functions.php');

$prefix = strlen(trim(prefix(), '`'));
$resource = db_show('tables');
if ($resource) {
	$result = array();
	while ($row = db_fetch_assoc($resource)) {
		$result[] = $row;
	}
	db_free_result($resource);
} else {
	$result = false;
}
$tables = array();
if (is_array($result)) {
	foreach ($result as $row) {
		$tables[] = array_shift($row);
	}
}


$sql = 'ALTER DATABASE ' . db_name() . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;';
query($sql);

foreach ($tables as $table) {
	$table = substr($table, $prefix);
	$tablecols = db_list_fields($table);
	foreach ($tablecols as $key => $datum) {
		$dbType = strtoupper($datum['Type']);
		if ($dbType == 'TEXT' || $dbType == 'LONGTEXT') {
			$sql = "ALTER TABLE " . prefix($table) . " CHANGE `" . $datum['Field'] . "` `" . $datum['Field'] . "` " . $dbType . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
			if ($datum['Null'] === 'NO')
				$sql .= " NOT NULL";
			if (!empty($datum['Default']) || $datum['Default'] === '0' || $datum['Null'] !== 'NO') {
				if (is_null($datum['Default'])) {
					if ($datum['Null'] !== 'NO') {
						$sql .= " DEFAULT NULL";
					}
				} else {
					$sql .= " DEFAULT '" . $datum['Default'] . "'";
				}
			}
			if (!empty($datum['Comment'])) {
				$sql .= " COMMENT '" . $datum['Comment'] . "'";
			}
			query($sql);
		}
	}
}
$_configMutex->lock();
$zp_cfg = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
$zp_cfg = updateConfigItem('UTF-8', 'utf8mb4', $zp_cfg);
storeConfig($zp_cfg);
$_configMutex->unlock();


header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&msg=' . gettext('utf8mb4 migration completed.'));
exitZP();
