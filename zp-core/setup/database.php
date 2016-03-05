<?php

/*
 * compares current database to the release database template and makes
 * updates as needed
 *
 * @author Stephen Billard
 * @Copyright 2016 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 */

$database_name = db_name();
$prefix = trim(prefix(), '`');
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
$database = $orphans = array();
$i = 0;
foreach ($tables as $table) {
	$table = substr($table, strlen($prefix));

	$tablecols = db_list_fields($table);
	foreach ($tablecols as $key => $datum) {
		//remove don't care fields
		unset($datum['Collation']);
		unset($datum['Key']);
		unset($datum['Extra']);
		unset($datum['Privileges']);
		$database[$table]['fields'][$datum['Field']] = $datum;
	}

	$indices = array();
	$sql = 'SHOW KEYS FROM ' . prefix($table);
	$result = query_full_array($sql);
	foreach ($result as $index) {
		if ($index['Key_name'] !== 'PRIMARY') {
			$indices[$index['Key_name']][] = $index;
		}
	}
	foreach ($indices as $keyname => $index) {
		if (count($index) > 1) {
			$column = array();
			foreach ($index as $element) {
				$column[] = "`" . $element['Column_name'] . "`";
			}
			$index = array_shift($index);
			$index['Column_name'] = implode(',', $column);
		} else {
			$index = array_shift($index);
			$index['Column_name'] = "`" . $index['Column_name'] . "`";
		}
		unset($index['Table']);
		unset($index['Seq_in_index']);
		unset($index['Collation']);
		unset($index['Cardinality']);
		unset($index['Comment']);

		switch ($keyname) {
			case 'valid':
				if ($table == 'administrators' && $index['Column_name'] === '`valid`,`user`') {
					$index['Index_comment'] = 'zp20';
				}
				break;
			case 'filename':
				if ($table == 'images' && $index['Column_name'] === '`filename`,`albumid`') {
					$index['Index_comment'] = 'zp20';
				}
				break;
		}

		$database[$table]['keys'][$keyname] = $index;
	}
}

$collation = db_collation();
$template = unserialize(file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/databaseTemplate'));

//Add in the enabled image metadata fields
$metadataProviders = array(zpFunctions::exifvars(), 'class-video' => 'Video', 'xmpMetadata' => 'xmpMetadata');
foreach ($metadataProviders as $source => $handler) {
	if (is_array($handler)) {
		$enabled = true;
		$exifvars = $handler;
	} else {
		$enabled = extensionEnabled($source);
		$plugin = getPlugin($source . '.php');
		require_once($plugin);
		$exifvars = $handler::getMetadataFields();
		foreach ($exifvars as $key => $item) {
			if (!is_null($disable = getOption($key . '-disabled'))) {
				$exifvars[$key][5] = !($disable & true);
			}
			if (!is_null($display = getOption($key . '-display'))) {
				$exifvars[$key][3] = $display;
			}
		}
	}

	foreach ($exifvars as $key => $exifvar) {
		$s = $exifvar[4];
		if ($exifvar[5] && $enabled) {
			switch ($exifvar[6]) {
				case 'string':
					if ($s < 255) {
						$s = "varchar($s)";
					} else {
						$s = 'mediumtext';
					}
					break;
				case 'number':
					$s = 'varchar(52)';
					break;
				case 'time':
					$s = 'datetime';
					break;
			}
			$field = array(
							'Field'		 => $key,
							'Type'		 => $s,
							'Null'		 => 'YES',
							'Default'	 => null,
							'Comment'	 => 'optional_metadata'
			);

			$template['images']['fields'][$key] = $field;
		} else {
			if (isset($database['images']['fields'][$key])) {
				$database['images']['fields'][$key]['Comment'] = 'optional_metadata';
			}
		}
	}
}

foreach ($template as $tablename => $table) {

	$exists = array_key_exists($tablename, $database);
	if (!$exists) {
		$create = array();
		$create[] = "CREATE TABLE IF NOT EXISTS " . prefix($tablename) . " (";
		$create[] = "  `id` int(11) UNSIGNED NOT NULL auto_increment,";
	}
	foreach ($table['fields'] as $key => $field) {
		if ($key != 'id') {

			$string = "ALTER TABLE " . prefix($tablename) . " %s `" . $field['Field'] . "` " . $field['Type'];
			if ($field['Null'] === 'NO')
				$string .= " NOT NULL";
			if (!empty($field['Default']) || $field['Default'] === '0' || $field['Null'] !== 'NO') {
				if (is_null($field['Default'])) {
					if ($field['Null'] !== 'NO') {
						$string .= " DEFAULT NULL";
					}
				} else {
					$string .= " DEFAULT '" . $field['Default'] . "'";
				}
			}
			if (empty($field['Comment'])) {
				$comment = '';
			} else {
				$comment = " COMMENT '" . $field['Comment'] . "'";
			}
			$addString = sprintf($string, 'ADD COLUMN') . $comment . ';';
			$changeString = sprintf($string, "CHANGE `" . $field['Field'] . "`") . $comment . ';';

			if ($exists) {
				if (array_key_exists($key, $database[$tablename]['fields'])) {
					if ($field != $database[$tablename]['fields'][$key]) {
						setupQuery($changeString);
					}
				} else {
					setupQuery($addString);
				}
			} else {
				$x = preg_split('/%s /', $string);
				$create[] = "  " . $x[1] . $comment . ',';
			}
		}
		unset($database[$tablename]['fields'][$key]);
	}
	if ($exists) {
		//handle surplus fields
		foreach ($database[$tablename]['fields'] as $key => $field) {
			// drop fields no longer used
			if ($field['Comment'] === 'zp20' || $field['Comment'] === 'optional_metadata') {
				$dropString = "ALTER TABLE " . prefix($tablename) . " DROP `" . $field['Field'] . "`;";
				setupQuery($dropString);
			} else {
				if (strpos($field['Comment'], 'optional_') === false) {
					$orphans[] = sprintf(gettext('Setup found the field "%1$s" in the "%2$s" table. This field is not in use by ZenPhoto20.'), $key, $tablename);
				}
			}
		}
	}


	if (isset($table['keys'])) {
		foreach ($table['keys'] as $key => $index) {
			$string = "ALTER TABLE " . prefix($tablename) . ' ADD ';
			if ($index['Non_unique']) {
				$string .= "INDEX ";
				$u = "KEY";
			} else {
				$string .="UNIQUE ";
				$u = "UNIQUE `$key`";
			}

			$k = $index['Column_name'];
			if (!empty($index['Sub_part'])) {
				$k .=" (" . $index['Sub_part'] . ")";
			}
			$alterString = "$string`$key` ($k) COMMENT 'zp20';";
			if ($exists) {
				if (array_key_exists($key, $database[$tablename]['keys'])) {
					if ($index != $database[$tablename]['keys'][$key]) {
						$dropString = "ALTER TABLE " . prefix($tablename) . " DROP INDEX `" . $index['Key_name'] . "`;";
						setupQuery($dropString);
						setupQuery($alterString);
					}
				} else {
					setupQuery($alterString);
				}
			} else {
				$tableString = "  $u ($k) COMMENT 'zp20',";
				$create[] = $tableString;
			}
			unset($database[$tablename]['keys'][$key]);
		}
	}
	if (!$exists) {
		$create[] = "  PRIMARY KEY (`id`)";
		$create[] = ") $collation;";
		$create = implode("\n", $create);
		setupQuery($create);
	} else {
		//handle surplus fields
		if (array_key_exists('keys', $database[$tablename]) && !empty($database[$tablename]['keys'])) {
			foreach ($database[$tablename]['keys'] as $index) {
				$key = $index['Key_name'];
				if ($index['Index_comment'] === 'zp20') {
					$dropString = "ALTER TABLE " . prefix($tablename) . " DROP INDEX `" . $key . "`;";
					setupQuery($dropString);
				} else {
					$orpahns = sprintf(gettext('Setup found the key "%1$s" in the "%2$s" table. This index is not in use by ZenPhoto20.'), $key, $tablename);
				}
			}
		}
	}
}
foreach ($orphans as $message) {
	setupLog($message, true);
}
?>