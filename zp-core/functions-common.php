<?php
/**
 * functions common to both the core and setup's basic environment
 *
 * @author Stephen Billard (sbillard)
 *
 * @package core
 */

/**
 * Common error reporting for query errors
 * @param type $sql
 */
function dbErrorReport($sql) {
	zp_error(sprintf(gettext('%1$s Error: ( %2$s ) failed. %1$s returned the error %3$s'), DATABASE_SOFTWARE, $sql, db_error()), E_USER_ERROR);
}

/**
 * Returns a properly quoted string for DB queries
 * @param type $string
 * @return type
 */
function db_quote($string) {
	return "'" . db_escape($string) . "'";
}

/**
 * Returns the viewer's IP address
 * Deals with transparent proxies
 *
 * @return string
 */
function getUserIP() {
	$pattern = '~^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$~';
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = sanitize($_SERVER['HTTP_X_FORWARDED_FOR']);
		if (preg_match($pattern, $ip)) {
			return $ip;
		}
	}
	$ip = sanitize($_SERVER['REMOTE_ADDR']);
	if (preg_match($pattern, $ip)) {
		return $ip;
	}
	return NULL;
}

/**
 * triggers an error
 *
 * @param string $message
 * @param bool $fatal set true to fail the script
 */
function zp_error($message, $fatal = E_USER_ERROR) {
	trigger_error($message, $fatal);
}

/**
 *
 * Traps errors and insures thy are logged.
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param string $errline
 * @return void|boolean
 */
function zpErrorHandler($errno, $errstr = '', $errfile = '', $errline = '') {
	global $_zp_current_admin_obj, $_index_theme;
	// check if function has been called by an exception
	if (func_num_args() == 5) {
		list($errno, $errstr, $errfile, $errline) = func_get_args();
	} else {
		// caught exception
		$exc = func_get_arg(0);
		$errno = $exc->getCode();
		$errstr = $exc->getMessage();
		$errfile = $exc->getFile();
		$errline = $exc->getLine();
	}

	// if error has been supressed with an @
	if (error_reporting() == 0 && !in_array($errno, array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE))) {
		return;
	}
	$errorType = array(E_ERROR				 => gettext('ERROR'),
					E_WARNING			 => gettext('WARNING'),
					E_NOTICE			 => gettext('NOTICE'),
					E_USER_ERROR	 => gettext('USER ERROR'),
					E_USER_WARNING => gettext('USER WARNING'),
					E_USER_NOTICE	 => gettext('USER NOTICE'),
					E_STRICT			 => gettext('STRICT NOTICE')
	);

	// create error message

	if (array_key_exists($errno, $errorType)) {
		$err = $errorType[$errno];
	} else {
		$err = gettext("EXCEPTION ($errno)");
		$errno = E_ERROR;
	}


	$msg = sprintf(gettext('%1$s: "%2$s" in %3$s on line %4$s'), $err, $errstr, $errfile, $errline);
	if (array_key_exists('REQUEST_URI', $_SERVER)) {
		$uri = sanitize($_SERVER['REQUEST_URI']);
		preg_match('|^(http[s]*\://[a-zA-Z0-9\-\.]+/?)*(.*)$|xis', $uri, $matches);
		$uri = $matches[2];
		if (!empty($matches[1])) {
			$uri = '/' . $uri;
		}
	} else {
		$uri = sanitize(@$_SERVER['SCRIPT_NAME']);
	}
	if ($uri) {
		$uri = "\n URI:" . urldecode(str_replace('\\', '/', $uri));
	}
	$uri .= "\n IP:" . getUserIP();
	if (is_object($_zp_current_admin_obj)) {
		$uri .= "\n " . gettext('user') . ':' . $_zp_current_admin_obj->getUser();
	}
	if ($_index_theme) {
		$uri .= "\n " . gettext('theme') . ':' . $_index_theme;
	}
	debugLogBacktrace($msg . $uri, 1);

	if (!ini_get('display_errors') && ($errno == E_ERROR || $errno = E_USER_ERROR)) {
		// out of curtesy show the error message on the WEB page since there will likely be a blank page otherwise
		?>
		<div style="padding: 10px 15px 10px 15px;	background-color: #FDD;	border-width: 1px 1px 2px 1px;	border-style: solid;	border-color: #FAA;	margin-bottom: 10px;	font-size: 100%;">
			<?php echo html_encode($msg); ?>
		</div>
		<?php
	}
	return false;
}

/**
 * Converts a file system filename to UTF-8 for zenphoto internal storage
 *
 * @param string $filename the file name to convert
 * @return string
 */
function filesystemToInternal($filename) {
	global $_zp_UTF8;
	return str_replace('\\', '/', $_zp_UTF8->convert($filename, FILESYSTEM_CHARSET, LOCAL_CHARSET));
}

/**
 * Converts an Internal filename string to one compatible with the file system
 *
 * @param string $filename the file name to convert
 * @return string
 */
function internalToFilesystem($filename) {
	global $_zp_UTF8;
	return $_zp_UTF8->convert($filename, LOCAL_CHARSET, FILESYSTEM_CHARSET);
}

/**
 * Returns the suffix of a file name
 *
 * @param string $filename
 * @return string
 */
function getSuffix($filename) {
	return strtolower(substr(strrchr($filename, "."), 1));
}

/**
 * returns a file name sans the suffix
 *
 * @param unknown_type $filename
 * @return unknown
 */
function stripSuffix($filename) {
	return str_replace(strrchr($filename, "."), '', $filename);
}

/**
 * Takes user input meant to be used within a path to a file or folder and
 * removes anything that could be insecure or malicious, or result in duplicate
 * representations for the same physical file.
 *
 * This function is used primarily for album names.
 * NOTE: The initial and trailing slashes are removed!!!
 *
 * Returns the sanitized path
 *
 * @param string $filename is the path text to filter.
 * @return string
 */
function sanitize_path($filename) {
	if (get_magic_quotes_gpc())
		$filename = stripslashes(trim($filename));
	$filename = strip_tags(str_replace('\\', '/', $filename));
	$filename = preg_replace(array('/x00/', '/\/\/+/', '/\/\.\./', '/\/\./', '/:/', '/</', '/>/', '/\?/', '/\*/', '/\"/', '/\|/', '/\/+$/', '/^\/+/'), '', $filename);
	return $filename;
}

/**
 * Checks if the input is numeric, rounds if so, otherwise returns false.
 *
 * @param mixed $num the number to be sanitized
 * @return int
 */
function sanitize_numeric($num) {
	if (is_numeric($num)) {
		return round($num);
	} else {
		return false;
	}
}

/**
 * removes script tags
 *
 * @param string $text
 * @return string
 */
function sanitize_script($text) {
	return preg_replace('!<script.*>.*</script>!ixs', '', $text);
}

/** Make strings generally clean.  Takes an input string and cleans out
 * null-bytes, slashes (if magic_quotes_gpc is on), and optionally use KSES
 * library to prevent XSS attacks and other malicious user input.
 * @param string $input_string is a string that needs cleaning.
 * @param string $sanitize_level is a number between 0 and 3 that describes the
 * type of sanitizing to perform on $input_string.
 *   0 - Basic sanitation. Only strips null bytes. Not recommended for submitted form data.
 *   1 - User specified. (User defined code is allowed. Used for descriptions and comments.)
 *   2 - Text style/formatting. (Text style codes allowed. Used for titles.)
 *   3 - Full sanitation. (Default. No code allowed. Used for text only fields)
 * @return string the sanitized string.
 */
function sanitize($input_string, $sanitize_level = 3) {
	if (is_array($input_string)) {
		$output_string = array();
		foreach ($input_string as $output_key => $output_value) {
			$output_string[$output_key] = sanitize($output_value, $sanitize_level);
		}
	} else {
		$output_string = sanitize_string($input_string, $sanitize_level);
	}
	return $output_string;
}

/**
 * Internal "helper" function to apply the tag removal
 *
 * @param string $input_string
 * @param array $allowed_tags
 * @return string
 */
function ksesProcess($input_string, $allowed_tags) {
	if (function_exists('kses')) {
		return kses($input_string, $allowed_tags);
	} else {
		$input_string = preg_replace('~<script.*?/script>~is', '', $input_string);
		$input_string = preg_replace('~<style.*?/style>~is', '', $input_string);
		$input_string = preg_replace('~<!--.*?-->~is', '', $input_string);
		$content = strip_tags($input_string);
		$input_string = str_replace('&nbsp;', ' ', $input_string);
		$input_string = html_decode($input_string);
		return $input_string;
	}
}

/**
 * Cleans tags and some content.
 * @param type $content
 * @return type
 */
function getBare($content) {
	return ksesProcess($content, array());
}

/** returns a sanitized string for the sanitize function
 * @param string $input_string
 * @param string $sanitize_level See sanitize()
 * @return string the sanitized string.
 */
function sanitize_string($input, $sanitize_level) {
	// Strip slashes if get_magic_quotes_gpc is enabled.
	if (is_string($input)) {
		if (get_magic_quotes_gpc()) {
			$input = stripslashes($input);
		}
		$input = str_replace(chr(0), " ", $input);
		switch ($sanitize_level) {
			case 0:
				return $input;
			case 2:
				// Strips non-style tags.
				$input = sanitize_script($input);
				return ksesProcess($input, getAllowedTags('style_tags'));
			case 3:
				// Full sanitation.  Strips all code.
				return ksesProcess($input, array());
			case 1:
				// Text formatting sanititation.
				$input = sanitize_script($input);
				return ksesProcess($input, getAllowedTags('allowed_tags'));
			case 4:
			default:
				// for internal use to eliminate security injections
				return sanitize_script($input);
		}
	}
	return $input;
}

///// database helper functions

/**
 * Prefix a table name with a user-defined string to avoid conflicts.
 * This MUST be used in all database queries.
 * @param string $tablename name of the table
 * @return prefixed table name
 * @since 0.6
 */
function prefix($tablename = NULL) {
	return '`' . DATABASE_PREFIX . $tablename . '`';
}

/**
 * Constructs a WHERE clause ("WHERE uniqueid1='uniquevalue1' AND uniqueid2='uniquevalue2' ...")
 *  from an array (map) of variables and their values which identifies a unique record
 *  in the database table.
 * @param string $unique_set what to add to the WHERE clause
 * @return contructed WHERE cleause
 * @since 0.6
 */
function getWhereClause($unique_set) {
	if (empty($unique_set))
		return ' ';
	$where = ' WHERE';
	foreach ($unique_set as $var => $value) {
		$where .= ' `' . $var . '` = ' . db_quote($value) . ' AND';
	}
	return substr($where, 0, -4);
}

/**
 * Constructs a SET clause ("SET uniqueid1='uniquevalue1', uniqueid2='uniquevalue2' ...")
 *  from an array (map) of variables and their values which identifies a unique record
 *  in the database table. Used to 'move' records. Note: does not check anything.
 * @param string $new_unique_set what to add to the SET clause
 * @return contructed SET cleause
 * @since 0.6
 */
function getSetClause($new_unique_set) {
	$i = 0;
	$set = ' SET';
	foreach ($new_unique_set as $var => $value) {
		$set .= ' `' . $var . '`=';
		if (is_null($value)) {
			$set .= 'NULL';
		} else {
			$set .= db_quote($value) . ',';
		}
	}
	return substr($set, 0, -1);
}

/*
 * returns the connected database name
 */

function db_name() {
	global $_zp_conf_vars;
	return $_zp_conf_vars['mysql_database'];
}

function db_count($table, $clause = NULL, $field = "*") {
	$sql = 'SELECT COUNT(' . $field . ') FROM ' . prefix($table) . ' ' . $clause;
	$result = query_single_row($sql);
	if ($result) {
		return array_shift($result);
	} else {
		return 0;
	}
}

function html_decode($string) {
	$string = html_entity_decode($string, ENT_QUOTES, LOCAL_CHARSET);
	// Replace numeric entities because html_entity_decode doesn't do it for us.
	if (function_exists('mb_convert_encoding')) {
		$string = preg_replace_callback("/(&#[0-9]+;)/", function($m) {
			return mb_convert_encoding($m[1], LOCAL_CHARSET, "HTML-ENTITIES");
		}, $string);
	}
	return $string;
}

/**
 * encodes a pre-sanitized string to be used in an HTML text-only field (value, alt, title, etc.)
 *
 * @param string $this_string
 * @return string
 */
function html_encode($this_string) {
	return htmlspecialchars($this_string, ENT_FLAGS, LOCAL_CHARSET);
}

/**
 * HTML encodes the non-metatag part of the string.
 *
 * @param string $original string to be encoded
 * @param bool $allowScript set to false to prevent pass-through of script tags.
 * @return string
 */
function html_encodeTagged($original, $allowScript = true) {
	$tags = array();
	$str = $original;
	//javascript
	if ($allowScript) {
		preg_match_all('!<script.*>.*</script>!ixs', $str, $matches);
		foreach (array_unique($matches[0]) as $key => $tag) {
			$tags[2]['%' . $key . '$j'] = $tag;
			$str = str_replace($tag, '%' . $key . '$j', $str);
		}
	} else {
		$str = preg_replace('|<a(.*)href(.*)=(.*)javascript|ixs', '%$x', $str);
		$tags[2]['%$x'] = '&lt;a href=<strike>javascript</strike>';
		$str = preg_replace('|<(.*)onclick|ixs', '%$c', $str);
		$tags[2]['%$c'] = '&lt;<strike>onclick</strike>';
	}
	//strip html comments
	$str = preg_replace('~<!--.*?-->~is', '', $str);
	// markup
	preg_match_all("/<\/?\w+((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>/i", $str, $matches);
	foreach (array_unique($matches[0]) as $key => $tag) {
		$tags[2]['%' . $key . '$s'] = $tag;
		$str = str_replace($tag, '%' . $key . '$s', $str);
	}
	$str = html_decode($str);
	$str = htmlentities($str, ENT_FLAGS, LOCAL_CHARSET);
	foreach (array_reverse($tags, true) as $taglist) {
		$str = strtr($str, $taglist);
	}
	if (class_exists('tidy') && $str != $original) {
		$tidy = new tidy();
		$tidy->parseString($str, array('show-body-only' => 1, 'quote-marks' => 1, 'quote-ampersand' => 1, 'preserve-entities' => true), 'utf8');
		$tidy->cleanRepair();
		$str = $tidy->value;
	}
	return $str;
}

/**
 * Makes directory recursively, returns TRUE if exists or was created sucessfuly.
 * Note: PHP5 includes a recursive parameter to mkdir, but it apparently does not
 * 				does not traverse symlinks!
 * @param string $pathname The directory path to be created.
 * @return boolean TRUE if exists or made or FALSE on failure.
 */
function mkdir_recursive($pathname, $mode) {
	if (!is_dir(dirname($pathname))) {
		mkdir_recursive(dirname($pathname), $mode);
	}
	return is_dir($pathname) || @mkdir($pathname, $mode);
}

/**
 * Logs the calling stack
 *
 * @param string $message Message to prefix the backtrace
 * @param int $omit count of "callers" to remove from backtrace
 * @param string $log alternative log file
 */
function debugLogBacktrace($message, $omit = 0, $log = 'debug') {
	$output = trim($message) . "\n";
	// Get a backtrace.
	$bt = debug_backtrace();
	while ($omit >= 0) {
		array_shift($bt); // Get rid of debug_backtrace, callers in the backtrace.
		$omit--;
	}
	$prefix = '  ';
	$line = '';
	$caller = '';
	foreach ($bt as $b) {
		$caller = (isset($b['class']) ? $b['class'] : '') . (isset($b['type']) ? $b['type'] : '') . $b['function'];
		if (!empty($line)) { // skip first output to match up functions with line where they are used.
			$prefix .= '  ';
			$output .= 'from ' . $caller . ' (' . $line . ")\n" . $prefix;
		} else {
			$output .= '  ' . $caller . " called ";
		}
		$date = false;
		if (isset($b['file']) && isset($b['line'])) {
			$line = basename($b['file']) . ' [' . $b['line'] . "]";
		} else {
			$line = 'unknown';
		}
	}
	if (!empty($line)) {
		$output .= 'from ' . $line;
	}
	debugLog($output, false, $log);
}

/**
 * Records a Var to the debug log
 *
 * @param string $message message to insert in log [optional]
 * @param mixed $var the variable to record
 * @param string $log alternative log file
 */
function debugLogVar($message) {
	$args = func_get_args();
	if (count($args) == 1) {
		$var = $message;
		$message = '';
	} else {
		$message .= ' ';
		$var = $args[1];
	}
	if (count($args) == 3) {
		$log = $args[2];
	} else {
		$log = 'debug';
	}
	ob_start();
	var_dump($var);
	$str = ob_get_contents();
	ob_end_clean();
	debugLog(trim($message) . "\r" . html_decode(getBare($str)), false, $log);
}

/**
 * Returns the value of a cookie from either the cookies or from $_SESSION[]
 *
 * @param string $name the name of the cookie
 */
function zp_getCookie($name) {
	if (isset($_COOKIE[$name])) {
		$cookiev = $_COOKIE[$name];
	} else {
		$cookiev = '';
	}
	if (DEBUG_LOGIN) {
		if (isset($_SESSION[$name])) {
			$sessionv = $_SESSION[$name];
		} else {
			$sessionv = '';
		}
		debugLog("zp_getCookie($name)::" . 'album_session=' . GALLERY_SESSION . "; SESSION[" . session_id() . "]=" . $sessionv . ", COOKIE=" . $cookiev);
	}
	if (!empty($cookiev) && (defined('GALLERY_SESSION') && !GALLERY_SESSION)) {
		return zp_cookieEncode($cookiev);
	}
	if (isset($_SESSION[$name])) {
		return $_SESSION[$name];
	}
	return NULL;
}

/**
 *
 * Encodes a cookie value tying it to the user IP
 * @param $value
 */
function zp_cookieEncode($value) {
	if (IP_TIED_COOKIES) {
		return rc4(getUserIP() . HASH_SEED, $value);
	} else {
		return $value;
	}
}

/**
 * Sets a cookie both in the browser cookies and in $_SESSION[]
 *
 * @param string $name The 'cookie' name
 * @param string $value The value to be stored
 * @param timestamp $time The time delta until the cookie expires
 * @param string $path The path on the server in which the cookie will be available on
 * @param bool $secure true if secure cookie
 */
function zp_setCookie($name, $value, $time = NULL, $path = NULL, $secure = false) {
	if (empty($value)) {
		$cookiev = '';
	} else {
		$cookiev = zp_cookieEncode($value);
	}
	if (is_null($time)) {
		$time = COOKIE_PESISTENCE;
	}
	if (is_null($path)) {
		$path = WEBPATH;
	}
	if (substr($path, -1, 1) != '/')
		$path .= '/';
	if (DEBUG_LOGIN) {
		debugLog("zp_setCookie($name, $value, $time, $path)::album_session=" . GALLERY_SESSION . "; SESSION=" . session_id());
	}
	if (($time < 0) || !GALLERY_SESSION) {
		setcookie($name, $cookiev, time() + $time, $path, "", $secure);
	}
	if ($time < 0) {
		if (isset($_SESSION))
			unset($_SESSION[$name]);
		if (isset($_COOKIE))
			unset($_COOKIE[$name]);
	} else {
		$_SESSION[$name] = $value;
		$_COOKIE[$name] = $cookiev;
	}
}

/**
 *
 * Clears a cookie
 * @param string $name
 * @param string $path
 * @param bool $secure true if secure cookie
 */
function zp_clearCookie($name, $path = NULl, $secure = false) {
	zp_setCookie($name, '', -368000, $path, $secure);
}

/**
 * if $string is an serialzied array it is unserialized otherwise an appropriate array is returned
 *
 * @param string $string
 *
 * @return array
 */
function getSerializedArray($string) {
	if (is_array($string)) {
		return $string;
	}
	if (is_null($string) || $string === '') {
		return array();
	}
	if (preg_match('/^a:[0-9]+:{/', $string)) {
		$r = @unserialize($string);
		if ($r) {
			return $r;
		} else {
			return array();
		}
	} else {
		return array($string);
	}
}

/**
 * Mutex class
 * @author Stephen
 *
 */
class zpMutex {

	private $locked = NULL;
	private $ignoreUseAbort = NULL;
	private $mutex = NULL;
	private $lock = NULL;

	function __construct($lock = 'zP', $concurrent = NULL, $folder = NULL) {

// if any of the construction fails, run in free mode (lock = NULL)
		if (function_exists('flock') && defined('SERVERPATH')) {
			if (is_null($folder)) {
				$folder = SERVERPATH . '/';
			}
			if ($concurrent) {
				If ($subLock = self::which_lock($lock, $concurrent, $folder)) {
					$this->lock = $folder . DATA_FOLDER . '/' . MUTEX_FOLDER . '/' . $lock . '_' . $subLock;
				}
			} else {
				$this->lock = $folder . DATA_FOLDER . '/' . MUTEX_FOLDER . '/' . $lock;
			}
		}
		return $this->lock;
	}

// returns the integer id of the lock to be obtained
// rotates locks sequentially mod $concurrent
	private static function which_lock($lock, $concurrent, $folder) {
		global $_zp_mutex;
		$counter_file = $folder . DATA_FOLDER . '/' . MUTEX_FOLDER . '/' . $lock . '_counter';
		$_zp_mutex->lock();
// increment the lock id:
		if (@file_put_contents($counter_file, $count = (((int) @file_get_contents($counter_file)) + 1) % $concurrent)) {
			$count++;
		} else {
			$count = false;
		}
		$_zp_mutex->unlock();
		return $count;
	}

	function __destruct() {
		if ($this->locked) {
			$this->unlock();
		}
	}

	public function lock() {
//if "flock" is not supported run un-serialized
//Only lock an unlocked mutex, we don't support recursive mutex'es
		if (!$this->locked && $this->lock) {
			if ($this->mutex = @fopen($this->lock, 'wb')) {
				if (flock($this->mutex, LOCK_EX)) {
					$this->locked = true;
//We are entering a critical section so we need to change the ignore_user_abort setting so that the
//script doesn't stop in the critical section.
					$this->ignoreUserAbort = ignore_user_abort(true);
				}
			}
		}
		return $this->locked;
	}

	/**
	 * 	Unlock the mutex.
	 */
	public function unlock() {
		if ($this->locked) {
//Only unlock a locked mutex.
			$this->locked = false;
			ignore_user_abort($this->ignoreUserAbort); //Restore the ignore_user_abort setting.
			flock($this->mutex, LOCK_UN);
			fclose($this->mutex);
			return true;
		}
		return false;
	}

}

if (!function_exists('hex2bin')) {

	function hex2bin($h) {
		if (!is_string($h))
			return null;
		$r = '';
		for ($a = 0; $a < strlen($h); $a+=2) {
			$r .= chr(hexdec($h{$a} . $h{($a + 1)}));
		}
		return $r;
	}

}
?>
