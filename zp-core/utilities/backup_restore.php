<?php
/**
 * Backup and restore of the ZenPhoto database tables
 *
 * This plugin provides a means to make backups of your ZenPhoto database and
 * at a later time restore the database to the contents of one of these backups.
 *
 * @package admin
 */

if (!defined('OFFSET_PATH')) define('OFFSET_PATH', 3);
define('HEADER', '__HEADER__');
define('RECORD_SEPARATOR', ':****:');
define('TABLE_SEPARATOR', '::');
define('RESPOND_COUNTER', 1000);
chdir(dirname(dirname(__FILE__)));

require_once(dirname(dirname(__FILE__)).'/admin-functions.php');
require_once(dirname(dirname(__FILE__)).'/admin-globals.php');
require_once(dirname(dirname(__FILE__)).'/template-functions.php');

$button_text = gettext('Backup/Restore');
$button_hint = gettext('Backup and restore your gallery database.');
$button_icon = 'images/folder.png';
$button_rights = ADMIN_RIGHTS;

admin_securityChecks(NULL, currentRelativeURL(__FILE__));

if (isset($_REQUEST['backup']) || isset($_REQUEST['restore'])) {
	XSRFDefender('backup');
}

global $handle, $buffer, $counter, $file_version, $compression_handler; // so this script can run from a function
$buffer = '';

function fillbuffer($handle) {
	global $buffer;
	$record = fread($handle, 8192);
	if ($record === false || empty($record)) {
		return false;
	}
	$buffer .= $record;
	return true;
}
function getrow($handle) {
	global $buffer, $counter, $file_version;
	if ($file_version == 0 || substr($buffer, 0, strlen(HEADER)) == HEADER) {
		$end = strpos($buffer, RECORD_SEPARATOR);
		while ($end === false) {
			if ($end = fillbuffer($handle)) {
				$end = strpos($buffer, RECORD_SEPARATOR);
			} else {
				return false;
			}
		}
		$result = substr($buffer, 0, $end);
		$buffer = substr($buffer, $end+strlen(RECORD_SEPARATOR));
	} else {
		$i = strpos($buffer, ':');
		if ($i === false) {
			fillbuffer($handle);
			$i = strpos($buffer, ':');
		}
		$end = substr($buffer, 0, $i)+$i+1;
		while ($end >= strlen($buffer)) {
			if (!fillbuffer($handle)) return false;
		}
		$result = substr($buffer, $i+1, $end-$i-1);
		$buffer = substr($buffer, $end);
	}
	return $result;
}

function compress($str, $lvl) {
	global $compression_handler;
	switch ($compression_handler) {
		case 'no':
			return $str;
		case 'bzip2':
			return bzcompress($str, $lvl);
		default:
			return gzcompress($str, $lvl);
	}
}

function decompress($str) {
	global $compression_handler;
	switch ($compression_handler) {
		case 'no':
			return $str;
		case 'bzip2':
			return bzdecompress($str);
		default:
			return gzuncompress($str);
	}
}

function writeHeader($type, $value) {
	global $handle;
	return fwrite($handle, HEADER.$type.'='.$value.RECORD_SEPARATOR);
}

$gallery = new Gallery();

printAdminHeader(gettext('utilities'),gettext('backup'));
echo '</head>';
?>

<body>
<?php printLogoAndLinks(); ?>
<div id="main">
<?php printTabs(); ?>
<div id="content">
<?php zp_apply_filter('admin_note','backkup', '');; ?>
<h1><?php echo (gettext('Backup and Restore your Database')); ?></h1>
<?php
$prefix = prefix();
if (isset($_REQUEST['backup']) && db_connect()) {
	$compression_level = sanitize($_REQUEST['compress'],3);
	setOption('backup_compression', $compression_level);
	if ($compression_level > 0) {
		if (function_exists('bzcompress')) {
			$compression_handler = 'bzip2';
		} else {
			$compression_handler = 'gzip';
		}
	} else {
		$compression_handler = 'no';
	}
	$tables = array();
	$result = db_show('tables');
	if ($result) {
		while ($row = db_fetch_assoc($result)) {
			$tables[] = $row;
		}
	}
	if (!empty($tables)) {
		$folder = SERVERPATH . "/" . BACKUPFOLDER;
		$filename = $folder . '/backup-' . date('Y_m_d-H_i_s').'.zdb';
		if (!is_dir($folder)) {
			mkdir ($folder, CHMOD_VALUE);
		}
		@chmod($folder, CHMOD_VALUE);
		$handle = fopen($filename, 'w');
		if ($handle === false) {
			printf(gettext('Failed to open %s for writing.'), $filename);
		} else {
			$writeresult = writeheader('file_version', 1);
			$writeresult = $writeresult && writeHeader('compression_handler',$compression_handler);
			if ($writeresult === false) {
				$msg = gettext('failed writing to backup!');
			}

			$counter = 0;
			$writeresult = true;
			foreach ($tables as $row) {
				set_time_limit(60);
				$table = array_shift($row);
				$unprefixed_table = substr($table, strlen($prefix));
				$sql = 'SELECT * from `'.$table.'`';
				$result = query($sql);
				if ($result) {
					while ($tablerow = db_fetch_assoc($result)) {
						foreach ($tablerow as $key=>$element) {
							if (!empty($element)) {
								$tablerow[$key] = compress($element, $compression_level);
							}
						}
						$storestring = $unprefixed_table.TABLE_SEPARATOR.serialize($tablerow);
						$storestring = strlen($storestring).':'.$storestring;
						$writeresult = fwrite($handle, $storestring);
						if ($writeresult === false) {
							$msg = gettext('failed writing to backup!');
							break;
						}
						$counter ++;
						if ($counter >= RESPOND_COUNTER) {
							echo ' ';
							$counter = 0;
						}
					}
				}
				if ($writeresult === false) break;
			}
			fclose($handle);
		}
	} else {
		$msg = gettext('SHOW TABLES failed!');
		$writeresult = false;
	}
	if ($writeresult) {
		?>
		<div class="messagebox fade-message">
		<h2>
		<?php
		if ($compression_level > 0) {
			printf(gettext('backup completed using <em>%1$s(%2$s)</em> compression'),$compression_handler, $compression_level);
		} else {
			echo gettext('backup completed');
		}
		?>
		</h2>
		</div>
		<?php
	} else {
		?>
		<div class="errorbox fade-message">
		<h2><?php echo gettext("backup failed"); ?></h2>
		<p><?php echo $msg; ?></p>
		</div>
		<?php
	}
} else if (isset($_REQUEST['restore']) && db_connect()) {
	$oldlibauth = $_zp_authority->getVersion();
	$success = 1;
	if (isset($_REQUEST['backupfile'])) {
		$file_version = 0;
		$compression_handler = 'gzip';
		$folder = SERVERPATH . '/' . BACKUPFOLDER .'/';
		$filename = $folder . internalToFilesystem(sanitize($_REQUEST['backupfile'], 3)).'.zdb';
		if (file_exists($filename)) {
			$handle = fopen($filename, 'r');
			if ($handle !== false) {
				$prefix = prefix();
				$resource = db_show('tables');
				if ($resource) {
					$result = array();
					while ($row = db_fetch_assoc($resource)) {
						$result[] = $row;
					}
				} else {
					$result = false;
				}

				$tables = array();
				$table_cleared = array();
				if (is_array($result)) {
					foreach($result as $row) {
						$table = array_shift($row);
						$tables[$table] = array();
						$table_cleared[$table] = false;
						$result2 = db_list_fields(str_replace($prefix, '', $table));
						if (is_array($result2)) {
							foreach ($result2 as $row) {
								$tables[$table][] = $row['Field'];
							}
						}
					}
				}
				$success = 0;
				$string = getrow($handle);
				while (substr($string, 0, strlen(HEADER)) == HEADER) {
					$string = substr($string, strlen(HEADER));
					$i = strpos($string, '=');
					$type = substr($string, 0, $i);
					$what = substr($string, $i+1);
					switch ($type) {
						case 'compression_handler':
							$compression_handler = $what;
							break;
						case 'file_version':
							$file_version = $what;
					}
					$string = getrow($handle);
				}
				$counter = 0;
				$missing_table = array();
				$missing_element = array();
				while (!empty($string) && !$success) {
					$sep = strpos($string, TABLE_SEPARATOR);
					$table = substr($string, 0, $sep);
					if (array_key_exists($prefix.$table,$tables)) {
						if (!$table_cleared[$prefix.$table]) {
							if (!db_truncate_table($table)) {
								$success = 2;
							}
							$table_cleared[$prefix.$table] = true;
							set_time_limit(60);
						}
						$row = substr($string, $sep+strlen(TABLE_SEPARATOR));
						$row = unserialize($row);
						$items = '';
						$values = '';
						foreach($row as $key=>$element) {
							if (array_search($key,$tables[$prefix.$table]) === false) {
								$missing_element[] = $table.'->'.$key;
							} else {
								if (!empty($element)) {
									$element = decompress($element);
								}
								$items .= '`'.$key.'`,';
								if (is_null($element)) {
									$values .= 'NULL,';
								} else {
									$values .= db_quote($element).',';
								}
							}
						}
						if (!empty($items)) {
							if ($table!='options' || strpos($values,'zenphoto_release')===false) {
								$items = substr($items,0,-1);
								$values = substr($values,0,-1);

								$sql = 'INSERT INTO '.prefix($table).' ('.$items.') VALUES ('.$values.')';
								if (!query($sql, false)) {
									$success = 2;
								}
							}
						}
					} else {
						$missing_table[] = $table;
					}
					$counter ++;
					if ($counter >= RESPOND_COUNTER) {
						echo ' ';
						$counter = 0;
					}
					$string = getrow($handle);
				}
			}
			fclose($handle);
		}
	}

	if (!empty($missing_table) || !empty($missing_element)) {
		?>
		<div class="warningbox">
			<h2><?php echo gettext("Restore encountered exceptions"); ?></h2>
			<?php
			if (!empty($missing_table)) {
				?>
				<p>
				<?php
				echo gettext('The following tables were not restored because the table no longer exists:');
				?>
					<ul>
					<?php
					foreach (array_unique($missing_table) as $item) {
						?>
						<li><em><?php echo $item; ?></em></li>
						<?php
					}
					?>
					</ul>
				</p>
				<?php
			}
			if (!empty($missing_element)) {
				?>
				<p>
				<?php
				echo gettext('The following fields were not restored because the field no longer exists:');
				?>
					<ul>
					<?php
					foreach (array_unique($missing_element) as $item) {
						?>
						<li><em><?php echo $item; ?></em></li>
						<?php
					}
					?>
					</ul>
				</p>
				<?php
			}
			?>
		</div>
		<?php
	} else if ($success) {
		?>
		<div class="errorbox">
			<h2><?php echo gettext("Restore failed"); ?></h2>
			<?php
			switch ($success) {
				case 1:
					echo '<p>'.gettext('No backup set found.').'</p>';
					break;
				case 2:
					echo '<p';
					printf(gettext('Query ( <em>%1$s</em> ) failed. Error: %2$s' ),$sql,db_error());
					echo '</p>';
					break;
			}
			?>
		</div>
		<?php
	} else {
		?>
		<div class="messagebox fade-message">
			<h2>
			<?php
			if ($compression_handler == 'no') {
				echo(gettext('Restore completed'));
			} else {
				printf(gettext('Restore completed using %s compression'), $compression_handler);
			}

			?>
			</h2>
		</div>
		<?php
	}
	setOption('zenphoto_release', ZENPHOTO_RELEASE); // be sure it is correct
	setOption('zenphoto_install', installSignature());
	if ($oldlibauth != $_zp_authority->getVersion()) {
		if (!$_zp_authority->migrateAuth($oldlibauth)) {
			?>
			<div class="errorbox fade-message">
			<h2><?php echo gettext('Zenphoto Rights migration failed!')?></h2>
			</div>
			<?php
		}
	}
}
if (db_connect()) {
	$compression_level = getOption('backup_compression');
	?>
	<h3><?php gettext("database connected"); ?></h3>
	<p>
		<?php printf(gettext("Database software <strong>%s</strong>"),DATABASE_SOFTWARE); ?><br />
		<?php printf(gettext("Database name <strong>%s</strong>"),db_name()); ?><br />
		<?php printf(gettext("Tables prefix <strong>%s</strong>"), prefix()); ?>
	</p>
	<br />
	<br />
	<form name="backup_gallery" action="">
		<?php XSRFToken('backup');?>
	<input type="hidden" name="backup" value="true" />
	<div class="buttons pad_button" id="dbbackup">
	<button class="tooltip" type="submit" title="<?php echo gettext("Backup the tables in your database."); ?>">
		<img src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/burst1.png" alt="" /> <?php echo gettext("Backup the Database"); ?>
	</button>
	<select name="compress">
	<?php
	for ($v=0; $v<=9; $v++) {
	?>
		<option value="<?php echo $v;?>"<?php if($compression_level == $v) echo ' selected="selected"'; ?>><?php echo $v; ?></option>
	<?php
	}
	?>
	</select> Compression level
	</div>
	<br clear="all" />
	<br clear="all" />
	</form>
	<br />
	<br />
	<?php
	$filelist = safe_glob(SERVERPATH . "/" . BACKUPFOLDER . '/*.zdb');
	if (count($filelist) <= 0) {
		echo gettext('You have not yet created a backup set.');
	} else {
	?>
		<form name="restore_gallery" action="">
		<?php XSRFToken('backup');?>
		<?php echo gettext('Select the database restore file:'); ?>
		<br />
		<select id="backupfile" name="backupfile">
		<?php	generateListFromFiles('', SERVERPATH . "/" . BACKUPFOLDER, '.zdb', true);	?>
		</select>
		<input type="hidden" name="restore" value="true" />
		<div class="buttons pad_button" id="dbrestore">
		<button class="tooltip" type="submit" title="<?php echo gettext("Restore the tables in your database from a previous backup."); ?>">
			<img src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/redo.png" alt="" /> <?php echo gettext("Restore the Database"); ?>
		</button>
		</div>
		<br clear="all" />
		<br clear="all" />
		</form>
	<?php
	}
	?>
	<?php
} else {
	echo "<h3>".gettext("database not connected")."</h3>";
	echo "<p>".gettext("Check the zp-config.php file to make sure you've got the right username, password, host, and database. If you haven't created the database yet, now would be a good time.");
}

echo	'<p>';
echo gettext('The backup facility creates database snapshots in the <code>backup</code> folder of your installation. These backups are named in according to the date and time the backup was taken. '.
							'The compression level goes from 0 (no compression) to 9 (maximum compression). Higher compression requires more processing and may not result in much space savings.');
echo '</p><p>';
echo gettext('You restore your database by selecting a backup and pressing the <em>Restore the Database</em> button.');
echo '</p><p class="notebox">'.gettext('<strong>Note:</strong> Each database table is emptied before the restore is attempted. After a successful restore the database will be in the same state as when the backup was created.');
echo '</p><p>';
echo gettext('Ideally a restore should be done only on the same version of Zenphoto on which the backup was created. If you are intending to upgrade, first do the restore on the version of Zenphoto you were running, then install the new Zenphoto. If this is not possible the restore can still be done, but if the database fields have changed between versions, data from changed fields will not be restored.');
echo '</p>'
?>
</div><!-- content -->
</div><!-- main -->
<?php printAdminFooter(); ?>
</body>
<?php echo "</html>"; ?>