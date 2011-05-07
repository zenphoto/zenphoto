<?php
/**
 * These are the functions that setup needs before the database can be accessed (so it can't include
 * functions.php because that will cause a database connect error.)
 * @package setup
 */

// force UTF-8 Ø


require_once(dirname(dirname(__FILE__)).'/global-definitions.php');
require_once(dirname(dirname(__FILE__)).'/lib-htmlawed.php');

$const_webpath = dirname(dirname($_SERVER['SCRIPT_NAME']));
$const_webpath = str_replace("\\", '/', $const_webpath);
if ($const_webpath == '/') $const_webpath = '';
if (!defined('WEBPATH')) { define('WEBPATH', $const_webpath); }
if (!defined('SERVERPATH')) { define('SERVERPATH', dirname(dirname(dirname(__FILE__)))); }
define('LOCAL_CHARSET','UTF-8');
define('FILESYSTEM_CHARSET', 'ISO-8859-1');
define('ADMIN_RIGHTS',1);
define('PROTOCOL', 'http');

$_zp_imagick_present = false;

function zp_getCookie($name) {
	if (isset($_SESSION[$name])) { return $_SESSION[$name]; }
	if (isset($_COOKIE[$name])) { return $_COOKIE[$name]; }
	return false;
}

function zp_setCookie($name, $value, $time=0, $path='/') {
	setcookie($name, $value, $time, $path);
	if ($time < 0) {
		unset($_SESSION[$name]);
		unset($_COOKIE[$name]);
	} else {
		$_SESSION[$name] = $value;
		$_COOKIE[$name] = $value;
	}
}

$_options = array();
function getOption($key) {
	global $_options;
	if (isset($_options[$key])) return $_options[$key];
	return NULL;
}

function setOption($key, $value, $persistent=true) {
	global $_options;
	$_options[$key] = $value;
}

function setOptionDefault($key, $value) {
	global $_options;
	$_options[$key] = $value;
}

function sanitize($input_string, $sanitize_level=3) {
	if (is_array($input_string)) {
		foreach ($input_string as $output_key => $output_value) {
			$output_string[$output_key] = sanitize_string($output_value, $sanitize_level);
		}
		unset($output_key, $output_value);
	} else {
		$output_string = sanitize_string($input_string, $sanitize_level);
	}
	return $output_string;
}

function sanitize_string($input_string, $sanitize_level) {
	if (get_magic_quotes_gpc()) $input_string = stripslashes($input_string);
	if ($sanitize_level === 0) {
		$input_string = str_replace(chr(0), " ", $input_string);
	} else if ($sanitize_level === 1) {
		$allowed_tags = "(".getOption('allowed_tags').")";
		$allowed = parseAllowedTags($allowed_tags);
		if ($allowed === false) { $allowed = array(); }
		$input_string = kses($input_string, $allowed);
	} else if ($sanitize_level === 2) {
		$allowed = array();
		$input_string = kses($input_string, $allowed);
	// Full sanitation.  Strips all code.
	} else if ($sanitize_level === 3) {
		$allowed_tags = array();
		$input_string = kses($input_string, $allowed_tags);
	}
	return $input_string;
}

function printAdminFooter() {
	echo "<div id=\"footer\">";
	echo "\n  <a href=\"http://www.zenphoto.org\" title=\"".gettext('A simpler web album')."\">zen<strong>photo</strong></a>";
	echo " | <a href=\"http://www.zenphoto.org/support/\" title=\"".gettext('Forum').'">'.gettext('Forum')."</a> | <a href=\"http://www.zenphoto.org/trac/\" title=\"Trac\">Trac</a> | <a href=\"changelog.html\" title=\"".gettext('View Change log')."\">".gettext('Change log')."</a>\n</div>";
}

function debugLog($message, $reset=false) {
	if ($reset) { $mode = 'w'; } else { $mode = 'a'; }
	$path = SERVERPATH . '/' . DATA_FOLDER . '/debug_log.txt';
	$f = fopen($path, $mode);
	fwrite($f, $message . "\n");
	fclose($f);
	clearstatcache();
	chmod($path, 0600);

}

function debugLogArray($name, $source, $indent=0, $trail='') {
	if (is_array($source)) {
		$msg = str_repeat(' ', $indent)."$name => ( ";
		if (count($source) > 0) {
			foreach ($source as $key => $val) {
				if (strlen($msg) > 72) {
					debugLog($msg);
					$msg = str_repeat(' ', $indent);
				}
				if (is_array($val)) {
					if (!empty($msg)) debugLog($msg);
					debugLogArray($key, $val, $indent+5, ',');
					$msg = '';
				} else {
					$msg .= $key . " => " . $val. ', ';
				}
			}

			$msg = substr($msg, 0, strrpos($msg, ',')) . " )".$trail;
		} else {
			$msg .= ")";
		}
		debugLog($msg);
	} else {
		debugLog($name.' parameter is not an array.');
	}
}

function debugLogBacktrace($message) {
	debugLog("Backtrace: $message");
	// Get a backtrace.
	$bt = debug_backtrace();
	array_shift($bt); // Get rid of debug_backtrace in the backtrace.
	$prefix = '';
	$line = '';
	$caller = '';
	foreach($bt as $b) {
		$caller = (isset($b['class']) ? $b['class'] : '')	. (isset($b['type']) ? $b['type'] : '')	. $b['function'];
		if (!empty($line)) { // skip first output to match up functions with line where they are used.

			$msg = $prefix . ' from ';
			debugLog($msg.$caller.' ('.$line.')');
			$prefix .= '  ';
		} else {
			debugLog($caller.' called');
		}
		$line = basename($b['file'])	. ' [' . $b['line'] . "]";
	}
	if (!empty($line)) {
		debugLog($prefix.' from '.$line);
	}
}

/**
 * Creates the body of a select list
 *
 * @param array $currentValue list of items to be flagged as checked
 * @param array $list the elements of the select list
 * @param bool $descending set true for a reverse order sort
 */
function generateListFromArray($currentValue, $list, $descending, $localize) {
	if ($localize) {
		$list = array_flip($list);
		if ($descending) {
			arsort($list);
		} else {
			natcasesort($list);
		}
		$list = array_flip($list);
	} else {
		if ($descending) {
			rsort($list);
		} else {
			natcasesort($list);
		}
	}
	foreach($list as $key=>$item) {
		echo '<option value="' . $item . '"';
		if (in_array($item, $currentValue)) {
			echo ' selected="selected"';
		}
		if ($localize) $display = $key; else $display = $item;
		echo '>' . $display . "</option>"."\n";
	}
}

function zp_loggedin() {
	return ADMIN_RIGHTS;
}

function mkdir_recursive($pathname, $mode=0777) {
	if (!is_dir(dirname($pathname))) mkdir_recursive(dirname($pathname), $mode);
	return is_dir($pathname) || @mkdir($pathname, $mode & CHMOD_VALUE);
}

function zp_html_decode($string, $quote_style = ENT_QUOTES) {
	$translation_table = get_html_translation_table(HTML_SPECIALCHARS, $quote_style);
	$translation_table["'"] = '&#039;';
	return (strtr($string, array_flip($translation_table)));
}

function html_encode($this_string) {
	$this_string = zp_html_decode($this_string, ENT_QUOTES);
	return htmlspecialchars($this_string, ENT_QUOTES, 'UTF-8');
}

/**
 * Prefix a table name with a user-defined string to avoid conflicts.
 * This MUST be used in all database queries.
 *@param string $tablename name of the table
 *@return prefixed table name
 *@since 0.6
	*/
function prefix($tablename=NULL) {
	global $_zp_conf_vars;
	if (empty($tablename)) {
		return $_zp_conf_vars['mysql_prefix'];
	} else {
		return '`' . $_zp_conf_vars['mysql_prefix'] . $tablename . '`';
	}
}

/**
 * Constructs a WHERE clause ("WHERE uniqueid1='uniquevalue1' AND uniqueid2='uniquevalue2' ...")
 *  from an array (map) of variables and their values which identifies a unique record
 *  in the database table.
 *@param string $unique_set what to add to the WHERE clause
 *@return contructed WHERE cleause
 *@since 0.6
	*/
function getWhereClause($unique_set) {
	if (empty($unique_set)) return ' ';
	$i = 0;
	$where = ' WHERE';
	foreach($unique_set as $var => $value) {
		if ($i > 0) $where .= ' AND';
		$where .= ' `' . $var . '` = ' . db_quote($value);
		$i++;
	}
	return $where;
}

/**
 * Constructs a SET clause ("SET uniqueid1='uniquevalue1', uniqueid2='uniquevalue2' ...")
 *  from an array (map) of variables and their values which identifies a unique record
 *  in the database table. Used to 'move' records. Note: does not check anything.
 *@param string $new_unique_set what to add to the SET clause
 *@return contructed SET cleause
 *@since 0.6
	*/
function getSetClause($new_unique_set) {
	$i = 0;
	$set = ' SET';
	foreach($new_unique_set as $var => $value) {
		if ($i > 0) $set .= ', ';
		$set .= ' `' . $var . '`=' . db_quote($value);
		$i++;
	}
	return $set;
}

/*
 * returns the connected database name
 */
function db_name() {
	global $_zp_conf_vars;
	return $_zp_conf_vars['mysql_database'];
}

function zp_error($message, $fatal=true) {
	global $_zp_error;
	if (!$_zp_error) {
		?>
		<div style="padding: 15px; border: 1px solid #F99; background-color: #FFF0F0; margin: 20px; font-family: Arial, Helvetica, sans-serif; font-size: 12pt;">
			<h2 style="margin: 0px 0px 5px; color: #C30;">Zenphoto encountered an error</h2>
			<div style=" color:#000;">
				<?php echo $message; ?>
			</div>
		<?php
		if (DEBUG_ERROR) {
			// Get a backtrace.
			$bt = debug_backtrace();
			array_shift($bt); // Get rid of zp_error in the backtrace.
			$prefix = '  ';
			?>
			<p>
				<?php echo gettext('<strong>Backtrace:</strong>'); ?>
				<br />
				<pre>
					<?php
					echo "\n";
					foreach($bt as $b) {
						echo $prefix . ' -> '
						. (isset($b['class']) ? $b['class'] : '')
						. (isset($b['type']) ? $b['type'] : '')
						. $b['function']
						. (isset($b['file']) ? ' (' . basename($b['file']) : '')
						. (isset($b['line']) ? ' [' . $b['line'] . "])" : '')
						. "\n";
						$prefix .= '  ';
					}
					?>
				</pre>
			</p>
			<?php
		}
		?>
		</div>
		<?php
		if ($fatal) {
			$_zp_error = true;
			exit();
		}
	}
}

/**
 * Converts a file system filename to UTF-8 for zenphoto internal storage
 *
 * @param string $filename the file name to convert
 * @return string
 */
function filesystemToInternal($filename) {
	global $_zp_UTF8;
	return str_replace('\\', '/', $_zp_UTF8->convert($filename, FILESYSTEM_CHARSET, 'UTF-8'));
}

/**
 * Converts a Zenphoto Internal filename string to one compatible with the file system
 *
 * @param string $filename the file name to convert
 * @return string
 */
function internalToFilesystem($filename) {
	global $_zp_UTF8;
	return $_zp_UTF8->convert($filename, 'UTF-8', FILESYSTEM_CHARSET);
}

?>