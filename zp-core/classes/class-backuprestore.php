<?php

/**
 * Class for creating and restoring database backups. 
 * 
 * Note that this does not create a standalone SQL backupg but data stored as serialized arrays that the restoreBackup() method turns into SQL statements.
 * For an actual SQL update use tools like phpmyadmin or the commandline "mysqldump".
 * 
 * Basic usage:
 * 
 *	$backuprestore = new backupRestore();
 *	// The constructor does not have any parameteres. Options are set via the class property 
 *	$backuprestore->compression_handler = 'no';
 * 
 *	$backuprestore->createBackup();
 *	or
 *	$backuprestore->restoreBackup($backupfile); // file name without path and suffix
 * 
 * @since 1.6.2 Moved from inline procedural functions to separate class
 * 
 * @package zpcore\classes\admin
 */
class backupRestore {

	public $header = '__HEADER__';
	public $record_separator = ':****:';
	public $table_separator = '::';
	public $respond_counter = 1000;
	public $buffer = '';
	public $file_version = 1;
	
	/**
	 * Default "no" for no compression. This is set via createBackup() and restoreBackup() internally depending 
	 * on the compression level < 1 and bzip or gzio depending on system availability
	 * @var string
	 */
	public $compression_handler = 'no'; 
	
	/**
	 * Default 0. Any value larger larger than one enables compression
	 * @var int
	 */
	public $compression_level = false; 
	
	/**
	 * Default false. Enables autobackup mode used for running via zp crons
	 * @var bool
	 */
	public $autobackup = false; 
	public $backupfile = ''; 
	
	//
	// former normal vars
	public $missing_table = array();
	public $missing_element = array();
	public $errors = array();
	public $messages = ''; // => array with array('headline' => '', 'message' => '', 'type' => 'error etc')
	public $db_prefix = '';
	public $db_prefix_length = '';
	public $signature = '';
	public $debuglog_messages = false;

	/**
	 * Set options via properties before creating or restoring backups
	 */
	function __construct() {
		global $_zp_db;
		$this->db_prefix = $_zp_db->getPrefix();
		$this->db_prefix_length = strlen($this->db_prefix);
		$this->signature = getOption('zenphoto_install');
	}

	/**
	 * Creates a database backup
	 */
	function createBackup() {
		global $_zp_db;
		setOption('backup_compression', $this->compression_level);
		if ($this->compression_level > 0) {
			if (function_exists('bzcompress')) {
				$this->compression_handler = 'bzip2_row';
			} else {
				$this->compression_handler = 'gzip_row';
			}
		} else {
			$this->compression_handler = 'no';
		}
		$tables = $_zp_db->getTables();
		if (!empty($tables)) {
			$folder = getBackupFolder(SERVERPATH);
			$randomkey = bin2hex(random_bytes(5));
			$filename = $folder . 'backup-' . date('Y_m_d-H_i_s') . '_' . $randomkey . '.zdb';
			if (!is_dir($folder)) {
				mkdir($folder, FOLDER_MOD);
			}
			@chmod($folder, FOLDER_MOD);
			$writeresult = $handle = @fopen($filename, 'w');
			if ($handle === false) {
				$msg = sprintf(gettext('Failed to open %s for writing.'), $filename);
				echo $msg;
			} else {
				$writeresult = $this->writeheader('file_version', $this->file_version, $handle);
				$writeresult = $writeresult && $this->writeHeader('compression_handler', $this->compression_handler, $handle);
				if ($writeresult === false) {
					$msg = gettext('failed writing to backup!');
				}

				$counter = 0;
				$writeresult = true;
				foreach ($tables as $table) {
					$unprefixed_table = substr($table, strlen($this->db_prefix));
					$sql = 'SELECT * from `' . $table . '`';
					$result = $_zp_db->query($sql);
					if ($result) {
						while ($tablerow = $_zp_db->fetchAssoc($result)) {
							$this->extendExecution();
							$storestring = serialize($tablerow);
							$storestring = $this->compressRow($storestring, $this->compression_level);
							$storestring = $unprefixed_table . $this->table_separator . $storestring;
							$storestring = strlen($storestring) . ':' . $storestring;
							$writeresult = fwrite($handle, $storestring);
							if ($writeresult === false) {
								$msg = gettext('failed writing to backup!');
								break;
							}
							$counter++;
							if ($counter >= $this->respond_counter) {
								echo ' ';
								$counter = 0;
							}
						}
						$writeresult = fwrite($handle, "\n"); //Empty line ensures the last item not getting lost on restoring
						$_zp_db->freeResult($result);
					}
					if ($writeresult === false) {
						break;
					}
				}
				fclose($handle);
				@chmod($filename, 0660 & CHMOD_VALUE);
			}
		} else {
			$msg = gettext('SHOW TABLES failed!');
			$writeresult = false;
		}
		if ($writeresult) {
			if ($this->autobackup) {
				setOption('last_backup_run', time());
			}
			$this->messages = '
		<div class="messagebox fade-message">
		<h2>
		';
			if ($this->compression_level > 0) {
				$this->messages .= sprintf(gettext('backup completed using <em>%1$s(%2$s)</em> compression'), $this->compression_handler, $this->compression_level);
			} else {
				$this->messages .= gettext('backup completed');
			}
			$this->messages .= '
		</h2>
		</div>
		<?php
		';
		} else {
			if ($this->autobackup) {
				debugLog(sprintf('Autobackup failed: %s', $msg));
			}
			$this->messages = '
		<div class="errorbox fade-message">
		<h2>' . gettext("backup failed") . '</h2>
		<p>' . $msg . '</p>
		</div>
		';
		}
	}

	/**
	 * Restores a backup 
	 * 
	 * @param string $backupfile The file name without path and suffix to restore from
	 */
	function restoreBackup($backupfile) {
		global $_zp_options, $_zp_authority, $_zp_db;
		$oldlibauth = Authority::getVersion();
		$this->errors[] = array(gettext('No backup set found.'));
		if ($backupfile) {
			$this->file_version = 0;
			$this->compression_handler = 'gzip';
			$folder = getBackupFolder(SERVERPATH);
			$filename = $folder . internalToFilesystem($backupfile, 3) . '.zdb';
			if (file_exists($filename)) {
				$handle = fopen($filename, 'r');
				if ($handle !== false) {
					$alltables = $_zp_db->getTables();
					$unique = $tables = array();
					$table_cleared = array();
					if ($alltables) {
						foreach ($alltables as $table) {
							$this->extendExecution();
							$tables[$table] = array();
							$table_cleared[$table] = false;
							$result2 = $_zp_db->getFields(substr($table, $this->db_prefix_length));
							if (is_array($result2)) {
								foreach ($result2 as $row) {
									$tables[$table][] = $row['Field'];
								}
							}
							$result2 = $_zp_db->show('index', $table);
							if (is_array($result2)) {
								foreach ($result2 as $row) {
									if (is_array($row)) {
										if (array_key_exists('Non_unique', $row) && !$row['Non_unique']) {
											$unique[$table][] = $row['Column_name'];
										}
									}
								}
							}
						}
					}

					$this->errors = array();
					$string = $this->getrow($handle);
					while (substr($string, 0, strlen($this->header)) == $this->header) {
						$string = substr($string, strlen($this->header));
						$i = strpos($string, '=');
						$type = substr($string, 0, $i);
						$what = substr($string, $i + 1);
						switch ($type) {
							case 'compression_handler':
								$this->compression_handler = $what;
								break;
							case 'file_version':
								$this->file_version = $what;
						}
						$string = $this->getrow($handle);
					}
					
					$counter = 0;
					$missing_table = array();
					$missing_element = array();
					while (!empty($string) && count($this->errors) < 100) {
						$this->extendExecution();
						$sep = strpos($string, $this->table_separator);
						$table = substr($string, 0, $sep);
						if (array_key_exists($this->db_prefix . $table, $tables)) {
							if (!$table_cleared[$this->db_prefix . $table]) {
								if (!$_zp_db->truncateTable($table)) {
									$errors[] = gettext('Truncate table<br />') . $_zp_db->getError();
								}
								$table_cleared[$this->db_prefix . $table] = true; 
							}
							$row = substr($string, $sep + strlen($this->table_separator));
							$row = $this->decompress($row);
							$row = unserialize($row);

							foreach ($row as $key => $element) {
								if ($this->compression_handler == 'bzip2' || $this->compression_handler == 'gzip') {
									if (!empty($element)) {
										$element = $this->decompress($element);
									}
								}
								if (array_search($key, $tables[$this->db_prefix . $table]) === false) {
									//	Flag it if data will be lost
									$missing_element[] = $table . '->' . $key;
									unset($row[$key]);
								} else {
									if (is_null($element)) {
										$row[$key] = 'NULL';
									} else {
										$row[$key] = $_zp_db->quote($element);
									}
								}
							}
							if (!empty($row)) {
								if ($table == 'options') {
									if ($row['name'] == 'zenphoto_install') {
										break;
									}
									if ($row['theme'] == 'NULL') {
										$row['theme'] = $_zp_db->quote('');
									}
								}
								$sql = 'INSERT INTO ' . $_zp_db->prefix($table) . ' (`' . implode('`,`', array_keys($row)) . '`) VALUES (' . implode(',', $row) . ')';
								foreach ($unique[$this->db_prefix . $table] as $exclude) {
									unset($row[$exclude]);
								}
								if (count($row) > 0) {
									$sqlu = ' ON DUPLICATE KEY UPDATE ';
									foreach ($row as $key => $value) {
										$sqlu .= '`' . $key . '`=' . $value . ',';
									}
									$sqlu = substr($sqlu, 0, -1);
								} else {
									$sqlu = '';
								}
								if (!$_zp_db->query($sql . $sqlu, false)) {
									$errors[] = $sql . $sqlu . '<br />' . $_zp_db->getError();
								} 
							}
						} else {
							$missing_table[] = $table;
						}
						$counter++;
						if ($counter >= $this->respond_counter) {
							echo ' ';
							$counter = 0;
						}
						$string = $this->getrow($handle);
					}
				}
				fclose($handle);
			}
		}
		if (!empty($this->missing_table) || !empty($this->missing_element)) {
			$this->messages = '
		<div class="warningbox">
			<h2>' . gettext("Restore encountered exceptions") . '</h2>';
			if (!empty($this->missing_table)) {
				$this->messages .= '
				<p>' . gettext('The following tables were not restored because the table no longer exists:') . '
					<ul>
					';
				foreach (array_unique($this->missing_table) as $item) {
					$this->messages .= '<li><em>' . $item . '</em></li>';
				}
				$this->messages .= '
					</ul>
				</p>
				';
			}
			if (!empty($this->missing_element)) {
				$this->messages .= '
				<p>' . gettext('The following fields were not restored because the field no longer exists:') . '
					<ul>
					';

				foreach (array_unique($this->missing_element) as $item) {
					$this->messages .= '<li><em>' . $item . '</em></li>';
				}
				$this->messages .= '
					</ul>
				</p>
				';
			}
			$this->messages .= '
		</div>
		';
		} else if (count($this->errors) > 0) {
			$this->messages = '
		<div class="errorbox">
			<h2>';
			if (count($this->errors) >= 100) {
				$this->messages .= gettext('The maximum error count was exceeded and the restore aborted.');
				unset($_GET['compression_handler']);
			} else {
				$this->messages .= gettext("Restore encountered the following errors:");
			}
			$this->messages .= '</h2>
			';
			foreach ($this->errors as $msg) {
				$this->messages .= '<p>' . html_encode($msg) . '</p>';
			}
			$this->messages .= '
		</div>
		';
		} else {

			// this probably should be taken out if $this->messages… kind of $this->resume() method or so
			$this->messages = '
			<script>
				window.onload = function() {
					window.location = "' . FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/backup_restore.php?compression_handler=' . $this->compression_handler . '";
				}
			</script>
		';
		}
		$_zp_options = NULL; //invalidate any options from before the restore
		if (getOption('zenphoto_install') !== $this->signature) {
			$l1 = '<a href="' . WEBPATH . '/' . ZENFOLDER . '/setup.php">';
			$this->messages .= '<div class="notebox">
			<h2>' . sprintf(gettext('You have restored your database content from a different instance of Zenphoto. You should run %1$ssetup%2$s to insure proper migration.'), $l1, '</a>') . '</h2>
			</div>';
		}

		setOption('license_accepted', ZENPHOTO_VERSION);
		if ($oldlibauth != Authority::getVersion()) {
			if (!$_zp_authority->migrateAuth($oldlibauth)) {
				$this->messages .= '
			<div class="errorbox fade-message">
			<h2>' . gettext('Zenphoto Rights migration failed!') . '</h2>
			</div>
			';
			}
		}
		if (isset($_GET['compression_handler'])) {
			$compression_handler = sanitize($_GET['compression_handler']);
			$this->messages = '
	<div class="messagebox fade-message">
		<h2>
			';
			if ($compression_handler == 'no') {
				$this->messages .= (gettext('Restore completed'));
			} else {
				$this->messages .= sprintf(gettext('Restore completed using %s compression'), html_encode($compression_handler));
			}
			$this->messages .= '
		</h2>
	</div>
	';
		}
	}

	/**
	 * Prints messages like success or error notices generated by creating or restoring a backup
	 */
	function printMessages() {
		echo $this->messages;
		if ($this->debuglog_messages) {
			debuglog($this->messages);
		}
	}

	/**
	 * "Extends" the PHP script execution by defining a time limit and echoing empty content
	 */
	function extendExecution() {
		@set_time_limit(30);
		echo ' ';
	}

	/**
	 * Fills the buffer on reading data from the file handle
	 * 
	 * @param resource $handle Backup file resource
	 * @return bool
	 */
	function fillBuffer($handle) {
		$record = fread($handle, 8192);
		if ($record === false || empty($record)) {
			return false;
		}
		$this->buffer .= $record;
		return true;
	}

	/**
	 * Gets a row from the file handle resource
	 * 
	 * @param resource $handle Backup file resource
	 * @return bool
	 */
	function getRow($handle) {
		if ($this->file_version == 0 || substr($this->buffer, 0, strlen($this->header)) == $this->header) {
			$end = strpos($this->buffer, $this->record_separator);
			while ($end === false) {
				if ($end = $this->fillbuffer($handle)) {
					$end = strpos($this->buffer, $this->record_separator);
				} else {
					return false;
				}
			}
			$result = substr($this->buffer, 0, $end);
			$this->buffer = substr($this->buffer, $end + strlen($this->record_separator));
		} else {
			$i = strpos($this->buffer, ':');
			if ($i === false) {
				$this->fillbuffer($handle);
				$i = strpos($this->buffer, ':');
			}
			$end = intval(substr($this->buffer, 0, $i)) + $i + 1;
			while ($end >= strlen($this->buffer)) {
				if (!$this->fillbuffer($handle))
					return false;
			}
			$result = substr($this->buffer, $i + 1, $end - $i - 1);
			$this->buffer = substr($this->buffer, $end);
		}
		return $result;
	}
	
	/**
	 * Compresses a row if a compression level is set to 1 or higher
	 * 
	 * @param sting $str Data to compress
	 * @param int $lvl Block size during compression
	 * @return type
	 */
	function compressRow($str, $lvl) {
		switch ($this->compression_handler) {
			default:
				return $str;
			case 'bzip2_row':
				return bzcompress($str, $lvl);
			case 'gzip_row':
				return gzcompress($str, $lvl);
		}
	}
	
	/**
	 * Decompresses field or row data which is set within restoreBackup()
	 * 
	 * @param sting $str Data to compress
	 * @return type
	 */
	function decompress($str) {
		switch ($this->compression_handler) {
			default:
				return $str;
			case 'bzip2':
			case 'bzip2_row':
				return bzdecompress($str);
			case 'gzip':
			case 'gzip_row':
				return gzuncompress($str);
		}
	}

	/**
	 * Writes header information to the first lines of a backup file
	 * 
	 * @param string $type Header type "file_version" or "compression_handler"
	 * @param string $value Header type value
	 * @param resource $handle Backup file resource
	 * @return int|false
	 */
	function writeHeader($type, $value, $handle) {
		return fwrite($handle, $this->header . $type . '=' . $value . $this->record_separator);
	}

}
