<?php
/**
 * basic functions used by zenphoto i.php
 * Keep this file to the minimum to allow the largest available memory for processing images!
 * Headers not sent yet!
 *
 * @author Stephen Billard (sbillard)
 *
 * @package functions
 *
 */
// force UTF-8 Ø
global $_zp_conf_vars;
$_zp_options = array();

require_once(dirname(__FILE__) . '/global-definitions.php');
require_once(dirname(__FILE__) . '/initialize-basic.php');

/**
 * functions common to both the core and setup's basic environment
 *
 * @author Stephen Billard (sbillard)
 *
 * @package core
 */
function loadConfiguration() {
	global $_zp_conf_vars;
	require(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
	if (!isset($_zp_conf_vars['UTF-8']) || $_zp_conf_vars['UTF-8'] === true) {
		$_zp_conf_vars['UTF-8'] = 'utf8';
	}
}

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
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return sanitize($_SERVER['HTTP_X_FORWARDED_FOR']);
	}
	return sanitize($_SERVER['REMOTE_ADDR']);
}

/**
 * Returns the viewer's IDs
 *
 * This is his username if logged in, otherwise we use getUserIP()
 *
 * @return string
 */
function getUserID() {
	global $_zp_current_admin_obj;
	if ($_zp_current_admin_obj) {
		$id = $_zp_current_admin_obj->getUser();
	} else {
		$id = getUserIP();
	}
	return $id;
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
 * Traps exceptions for logging
 *
 * @param type $ex the exception
 */
function zpExceptionHandler($ex) {
	$errno = $ex->getCode();
	$errstr = $ex->getMessage();
	$errfile = $ex->getFile();
	$errline = $ex->getLine();
	zpErrorHandler($errno, $errstr, $errfile, $errline);
	die();
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
	// if error has been supressed with an @
	if (error_reporting() == 0 && !in_array($errno, array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE))) {
		return false;
	}
	$errorType = array(
			E_ERROR => gettext('ERROR'),
			E_WARNING => gettext('WARNING'),
			E_NOTICE => gettext('NOTICE'),
			E_USER_ERROR => gettext('USER ERROR'),
			E_USER_WARNING => gettext('USER WARNING'),
			E_USER_NOTICE => gettext('USER NOTICE'),
			E_STRICT => gettext('STRICT NOTICE')
	);

	// create error message

	if (array_key_exists($errno, $errorType)) {
		$err = $errorType[$errno];
	} else {
		$err = gettext("EXCEPTION ($errno)");
		$errno = E_ERROR;
	}


	$msg = sprintf(gettext('%1$s: "%2$s" in %3$s on line %4$s'), $err, $errstr, $errfile, $errline);
	debugLogBacktrace($msg, 1);

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
 * shut-down handler, check for errors
 */
function zpShutDownFunction() {
	$error = error_get_last();
	if ($error && !in_array($error['type'], array(E_USER_ERROR, E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING, E_NOTICE, E_USER_NOTICE))) {
		$file = str_replace('\\', '/', $error['file']);
		preg_match('~(.*)/(' . USER_PLUGIN_FOLDER . '|' . PLUGIN_FOLDER . ')~', $file, $matches);
		if (isset($matches[2])) {
			$path = trim(preg_replace('~^.*' . $matches[2] . '~i', '', $file), '/');
			$path = explode('/', $path . '/');
			$extension = stripSuffix($path[0]);
			if ($extension) {
				enableExtension($extension, 0);
			}
		}
		zpErrorHandler($error['type'], $error['message'], $file, $error['line']);
	}
	if (function_exists('db_close')) {
		db_close();
	}

	exit();
}

/**
 * Converts a file system filename to UTF-8 for zenphoto internal storage
 *
 * @param string $filename the file name to convert
 * @return string
 */
function filesystemToInternal($filename) {
	global $_zp_UTF8;
	if (FILESYSTEM_CHARSET != LOCAL_CHARSET) {
		$filename = str_replace('\\', '/', $_zp_UTF8->convert($filename, FILESYSTEM_CHARSET, LOCAL_CHARSET));
	}
	return $filename;
}

/**
 * Converts an Internal filename string to one compatible with the file system
 *
 * @param string $filename the file name to convert
 * @return string
 */
function internalToFilesystem($filename) {
	global $_zp_UTF8;
	if (FILESYSTEM_CHARSET != LOCAL_CHARSET) {
		$filename = $_zp_UTF8->convert($filename, LOCAL_CHARSET, FILESYSTEM_CHARSET);
	}
	return $filename;
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
	$f = filter_var($num, FILTER_SANITIZE_NUMBER_FLOAT);
	if ($f === false) {
		return 0;
	} else {
		return round($f);
	}
}

/**
 * removes script tags
 *
 * @param string $text
 * @return string
 */
function sanitize_script($text) {
	return preg_replace('~<script.*>.*</script>~isU', '', $text);
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
		$input_string = preg_replace('~<script.*>.*</script>~isU', '', $input_string);
		$input_string = preg_replace('~<style.*>.*</style>~isU', '', $input_string);
		$input_string = preg_replace('~<!--.*-->~isU', '', $input_string);
		$content = strip_tags($input_string);
		$input_string = str_replace('&nbsp;', ' ', $input_string);
		$input_string = html_decode($input_string);
		return $input_string;
	}
}

/** returns a sanitized string for the sanitize function
 * @param string $input_string
 * @param string $sanitize_level See sanitize()
 * @return string the sanitized string.
 */
function sanitize_string($input, $sanitize_level) {
	if (is_string($input)) {
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
	$unique_set = array_change_key_case($unique_set, CASE_LOWER);
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

/**
 * gating functionm for all database queries
 * @param type $sql
 * @param type $errorstop
 */
function query($sql, $errorstop = true) {
	$result = function_exists('zp_apply_filter') ? zp_apply_filter('database_query', NULL, $sql) : NULL;
	if (is_null($result)) {
		return db_query($sql, $errorstop);
	}
	return $result;
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
 * Write output to the debug log
 * Use this for debugging when echo statements would come before headers are sent
 * or would create havoc in the HTML.
 * Creates (or adds to) a file named debug.log which is located in the zenphoto core folder
 *
 * @param string $message the debug information
 * @param bool $reset set to true to reset the log to zero before writing the message
 * @param string $log alternative log file
 */
function debugLog($message, $reset = false, $log = 'debug') {
	if (defined('SERVERPATH')) {
		global $_zp_mutex;
		$path = SERVERPATH . '/' . DATA_FOLDER . '/' . $log . '.log';
		$me = getmypid();
		if (is_object($_zp_mutex))
			$_zp_mutex->lock();
		if ($reset || ($size = @filesize($path)) == 0 || (defined('DEBUG_LOG_SIZE') && DEBUG_LOG_SIZE && $size > DEBUG_LOG_SIZE)) {
			if (!$reset && $size > 0) {
				switchLog('debug');
			}
			$f = fopen($path, 'w');
			if ($f) {
				if (!class_exists('zpFunctions') || zpFunctions::hasPrimaryScripts()) {
					$clone = '';
				} else {
					$clone = ' ' . gettext('clone');
				}
				fwrite($f, '{' . $me . ':' . gmdate('D, d M Y H:i:s') . " GMT} ZenPhoto20 v" . ZENPHOTO_VERSION . $clone . "\n");
			}
		} else {
			$f = fopen($path, 'a');
			if ($f) {
				fwrite($f, '{' . $me . ':' . gmdate('D, d M Y H:i:s') . " GMT}\n");
			}
		}
		if ($f) {
			fwrite($f, "  " . $message . "\n");
			fclose($f);
			clearstatcache();
			if (defined('DATA_MOD')) {
				@chmod($path, DATA_MOD);
			}
		}
		if (is_object($_zp_mutex))
			$_zp_mutex->unlock();
	}
}

/**
 * Logs the calling stack
 *
 * @param string $message Message to prefix the backtrace
 * @param int $omit count of "callers" to remove from backtrace
 * @param string $log alternative log file
 */
function debugLogBacktrace($message, $omit = 0, $log = 'debug') {
	global $_zp_current_admin_obj, $_index_theme;
	$output = trim($message) . "\n";
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
	$uri .= "\n ID `" . getUserID() . '`';
	if (is_object($_zp_current_admin_obj)) {
		$uri .= "\n " . gettext('user') . ':' . $_zp_current_admin_obj->getUser();
	}
	if ($_index_theme) {
		$uri .= "\n " . gettext('theme') . ':' . $_index_theme;
	}
	$output .= $uri . "\n";
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

	$formatting = array('<[/]*font(.*?)>', "<[/]*pre(.*?)>", '<[/]*i>', '<[/]*b>', '<[/]*small>');
	foreach ($formatting as $pattern) {
		$str = preg_replace('~' . $pattern . '~', '', $str);
	}
	$str = ksesProcess($str, array());

	debugLog(trim($message) . "\r" . html_decode($str), false, $log);
}

/**
 * Checks to see if access was through a secure protocol
 *
 * @return bool
 */
function secureServer() {
	return isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && strpos(strtolower($_SERVER['HTTPS']), 'off') === false;
}

/**
 *
 * Starts a zenphoto session (perhaps a secure one)
 */
function zp_session_start() {
	global $_zp_conf_vars;
	$result = session_id();
	if ($result) {
		return $result;
	} else {
		session_name('Session_' . str_replace('.', '_', ZENPHOTO_VERSION));
		@ini_set('session.use_strict_mode', 1);
		//	insure that the session data has a place to be saved
		if (isset($_zp_conf_vars['session_save_path'])) {
			session_save_path($_zp_conf_vars['session_save_path']);
		}
		$_session_path = session_save_path();

		if (ini_get('session.save_handler') == 'files' && !file_exists($_session_path) || !is_writable($_session_path)) {
			mkdir_recursive(SERVERPATH . '/' . DATA_FOLDER . '/PHP_sessions', (fileperms(dirname(__FILE__)) & 0666) | 0311);
			session_save_path(SERVERPATH . '/' . DATA_FOLDER . '/PHP_sessions');
		}
		$sessionCookie = session_get_cookie_params();
		session_set_cookie_params($sessionCookie['lifetime'], WEBPATH . '/', $_SERVER['HTTP_HOST'], secureServer(), true);

		$result = session_start();
		$_SESSION['version'] = ZENPHOTO_VERSION;
		return $result;
	}
}

function zp_session_destroy() {
	if ($name = session_name()) {
		$_SESSION = array();
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie($name, 'null', 1, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		} else {
			setcookie($name, 'null', 1);
		}
	}
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
 * @param int $time The time delta until the cookie expires. Set negative to clear cookie,
 * 									set to FALSE to expire at end of session
 * @param bool $security set to false to make the cookie send for any kind of connection
 */
function zp_setCookie($name, $value, $time = NULL, $security = true) {
	$secure = $security && secureServer();
	if (empty($value)) {
		$cookiev = '';
	} else {
		$cookiev = zp_cookieEncode($value);
	}
	if (is_null($t = $time)) {
		$t = time() + COOKIE_PESISTENCE;
		$tString = COOKIE_PESISTENCE;
	} else {
		if ($time === false) {
			$tString = 'FALSE';
		} else {
			$t = time() + $time;
			$tString = (int) $time;
		}
	}
	$path = getOption('zenphoto_cookie_path');
	if (empty($path)) {
		$path = WEBPATH;
	}
	if (substr($path, -1, 1) != '/') {
		$path .= '/';
	}
	if (DEBUG_LOGIN) {
		debugLog("zp_setCookie($name, $value, $tString)::path=" . $path . "; secure=" . sprintf('%u', $secure) . "; album_session=" . GALLERY_SESSION . "; SESSION=" . session_id());
	}
	if (($time < 0) || !GALLERY_SESSION) {
		setcookie($name, $cookiev, (int) $t, $path, "", $secure, true);
	}
	if ($time < 0) {
		if (session_id()) {
			unset($_SESSION[$name]);
		}
		if (isset($_COOKIE)) {
			unset($_COOKIE[$name]);
		}
	} else {
		if (session_id()) {
			$_SESSION[$name] = $value;
		}
		$_COOKIE[$name] = $cookiev;
	}
}

/**
 *
 * Clears a cookie
 * @param string $name
 */
function zp_clearCookie($name) {
	zp_setCookie($name, 'null', -368000, false);
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
	if (empty($string)) {
		return array();
	}
	if (is_string($string) && (($data = @unserialize($string)) !== FALSE || $string === 'b:0;')) {
		if (is_array($data)) {
			return $data;
		} else {
			return array($data);
		}
	}
	return array($string);
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

function primeOptions() {
	global $_zp_options;
	$_zp_options = array();

	if (function_exists('query_full_array')) { //	incase we are in primitive mode
		$sql = "SELECT `name`, `value` FROM " . prefix('options') . ' WHERE (`theme`="" OR `theme` IS NULL) AND `ownerid`=0 ORDER BY `name`';
		$rslt = query($sql);
		if ($rslt) {
			while ($option = db_fetch_assoc($rslt)) {
				$_zp_options[strtolower($option['name'])] = $option['value'];
			}
		}
	}
}

/**
 * Get a option stored in the database.
 * This function reads the options only once, in order to improve performance.
 * @param string $key the name of the option.
 */
function getOption($key) {
	global $_zp_options;
	if (isset($_zp_options[$key = strtolower($key)])) {
		return $_zp_options[$key];
	} else {
		return NULL;
	}
}

/**
 * Returns a list of options that match $pattern
 * @param string $pattern
 * @return array
 */
function getOptionsLike($pattern) {
	$result = array();

	$sql = 'SELECT `name`,`value` FROM ' . prefix('options') . ' WHERE `name` LIKE ' . db_quote(str_replace('_', '\_', rtrim($pattern, '%')) . '%') . ' ORDER BY `name`;';
	$found = query_full_array($sql, false);
	if (!empty($found)) {
		foreach ($found as $row) {
			$result[$row['name']] = $row['value'];
		}
	}

	return $result;
}

/**
 * Stores an option value.
 *
 * @param string $key name of the option.
 * @param mixed $value new value of the option.
 * @param bool $persistent set to false if the option is stored in memory only
 * otherwise it is preserved in the database
 */
function setOption($key, $value, $persistent = true) {
	global $_zp_options;
	if ($persistent) {
		list($theme, $creator) = getOptionOwner();
		if (is_null($value)) {
			$v = 'NULL';
		} else {
			if (is_bool($value)) {
				$value = (int) $value;
			}
			$v = db_quote($value);
		}
		$sql = 'INSERT INTO ' . prefix('options') . ' (`name`,`value`,`ownerid`,`theme`,`creator`) VALUES (' . db_quote($key) . ',' . $v . ',0,' . db_quote($theme) . ',' . db_quote($creator) . ')' . ' ON DUPLICATE KEY UPDATE `value`=' . $v;
		;
		$result = query($sql, false);
	} else {
		$result = true;
	}
	if ($result) {
		$_zp_options[strtolower($key)] = $value;
		return true;
	} else {
		return false;
	}
}

/**
 * returns the owner fields of an option. Typically used when the option is set
 * to its default value
 *
 * @return array
 */
function getOptionOwner() {
	$creator = NULL;
	$bt = debug_backtrace();
	$b = array_shift($bt); // this function
	$b = array_shift($bt); //the setOption... function
	//$b now has the calling file/line# of the setOption... function
	$creator = replaceScriptPath($b['file']);
	$matches = explode('/', $creator);
	if ($matches[0] == THEMEFOLDER) {
		$theme = $matches[1];
	} else {
		$theme = '';
	}
	if (isset($b['line'])) {
		$creator.='[' . $b['line'] . ']';
	}
	return array($theme, $creator);
}

/**
 * Sets the default value of an option.
 *
 * If the option has never been set it is set to the value passed
 *
 * @param string $key the option name
 * @param mixed $default the value to be used as the default
 */
function setOptionDefault($key, $default) {
	global $_zp_options;
	list($theme, $creator) = getOptionOwner();
	$sql = 'INSERT INTO ' . prefix('options') . ' (`name`, `value`, `ownerid`, `theme`, `creator`) VALUES (' . db_quote($key) . ',';
	if (is_null($default)) {
		$sql .= 'NULL';
	} else {
		if (is_bool($default)) {
			$default = (int) $default;
		}
		$sql .= db_quote($default);
	}
	$sql .= ',0,' . db_quote($theme) . ',' . db_quote($creator) . ');';
	if (query($sql, false)) {
		$_zp_options[strtolower($key)] = $default;
	} else {
		$sql = 'UPDATE ' . prefix('options') . ' SET `theme`=' . db_quote($theme) . ', `creator`=' . db_quote($creator) . ' WHERE `ownerid`=0 AND `name`=' . db_quote($key) . ' AND `theme`=' . db_quote($theme) . ';';
		query($sql, false);
	}
}

/**
 * Loads option table with album/theme options
 *
 * @param int $albumid
 * @param string $theme
 */
function loadLocalOptions($albumid, $theme) {
	global $_zp_options, $_loaded_local;
//raw theme options
	$sql = "SELECT LCASE(`name`) as name, `value` FROM " . prefix('options') . ' WHERE `theme`=' . db_quote($theme) . ' AND `ownerid`=0';
	$optionlist = query_full_array($sql, false);
	if ($optionlist !== false) {
		foreach ($optionlist as $option) {
			$_zp_options[$option['name']] = $option['value'];
		}
	}
	if ($albumid) {
//album-theme options
		$sql = "SELECT LCASE(`name`) as name, `value` FROM " . prefix('options') . ' WHERE `theme`=' . db_quote($theme) . ' AND `ownerid`=' . $albumid;
		$optionlist = query_full_array($sql, false);
		if ($optionlist !== false) {
			foreach ($optionlist as $option) {
				$_zp_options[$option['name']] = $option['value'];
			}
		}
	}
}

/**
 *
 * @global array $_zp_options
 * @param string $key
 */
function purgeOption($key) {
	global $_zp_options;
	unset($_zp_options[strtolower($key)]);
	$sql = 'DELETE FROM ' . prefix('options') . ' WHERE `name`=' . db_quote($key);
	query($sql, false);
}

/**
 * Retuns the option array
 *
 * @return array
 */
function getOptionList() {
	global $_zp_options;
	return $_zp_options;
}

/**
 * Cloned installations may be using symLinks to the "standard" ZenPhoto20 files.
 * This can cause a problem examining the "path" to the file. __FILE__ and other functions will
 * return the actual path to the file, e.g. the path to the parent installation of
 * a clone. SERVERPATH is the path to the clone installation and will not be the same
 * as the script path to the symLinked files.
 *
 * This function deals with the situation and returns the relative path in all cases
 *
 * @param string $file
 * @return string the relative path to the file
 */
function replaceScriptPath($file, $replace = '') {
	$file = str_replace('\\', '/', $file);
	return trim(preg_replace('~^(' . SERVERPATH . '|' . SCRIPTPATH . ')~i', $replace, $file), '/');
}

/**
 * Returns true if the file has the dynamic album suffix
 *
 * @param string $path
 * @return bool
 */
function hasDynamicAlbumSuffix($path) {
	global $_zp_albumHandlers;
	return array_key_exists(getSuffix($path), $_zp_albumHandlers);
}

/**
 * Handles the special cases of album/image[rewrite_suffix]
 *
 * Separates the image part from the album if it is an image reference
 * Strips off the mod_rewrite_suffix if present
 * Handles dynamic album names that do not have the .alb suffix appended
 *
 * @param string $albumvar	$_GET index for "albums"
 * @param string $imagevar	$_GET index for "images"
 */
function rewrite_get_album_image($albumvar, $imagevar) {
	global $_zp_rewritten, $_zp_albumHandlers;
	$ralbum = isset($_GET[$albumvar]) ? trim(sanitize($_GET[$albumvar]), '/') : NULL;
	$rimage = isset($_GET[$imagevar]) ? sanitize($_GET[$imagevar]) : NULL;
	//	we assume that everything is correct if rewrite rules were not applied
	if ($_zp_rewritten) {
		if (!empty($ralbum) && empty($rimage)) { //	rewrite rules never set the image part!
			if (!is_dir(internalToFilesystem(getAlbumFolder(SERVERPATH) . $ralbum))) {
				if (RW_SUFFIX && preg_match('|^(.*)' . preg_quote(RW_SUFFIX) . '$|', $ralbum, $matches)) {
					//has an RW_SUFFIX attached
					$rimage = basename($matches[1]);
					$ralbum = trim(dirname($matches[1]), '/');
				} else { //	have to figure it out
					if (Gallery::imageObjectClass($ralbum)) {
						//	it is an image request
						$rimage = basename($ralbum);
						$ralbum = trim(dirname($ralbum), '/');
					}
				}
			}
		}
		if (empty($ralbum)) {
			unset($_GET[$albumvar]);
		} else {
			$_GET[$albumvar] = $ralbum;
		}
		if (empty($rimage)) {
			unset($_GET[$imagevar]);
		} else {
			$_GET[$imagevar] = $rimage;
		}
	}
	return array($ralbum, $rimage);
}

/**
 * Returns the path of an image for uses in caching it
 * NOTE: character set if for the filesystem
 *
 * @param string $album album folder
 * @param string $image image file name
 * @param array $args cropping arguments
 * @return string
 */
function getImageCacheFilename($album8, $image8, $args) {
	global $_zp_supported_images, $_zp_cachefileSuffix;
// this function works in FILESYSTEM_CHARSET, so convert the file names
	$album = internalToFilesystem($album8);
	if (is_array($image8)) {
		$image8 = $image8['name'];
	}
	if (IMAGE_CACHE_SUFFIX) {
		$suffix = IMAGE_CACHE_SUFFIX;
	} else {
		$suffix = @$_zp_cachefileSuffix[strtoupper(getSuffix($image8))];
		if (empty($suffix)) {
			$suffix = 'jpg';
		}
	}
	if (is_array($image8)) {
		$image = internalToFilesystem($image8['name']);
	} else {
		$image = stripSuffix(internalToFilesystem($image8));
	}

// Set default variable values.
	$postfix = getImageCachePostfix($args);
	if (empty($album)) {
		$albumsep = '';
	} else {
		if (SAFE_MODE) {
			$albumsep = SAFE_MODE_ALBUM_SEP;
			$album = str_replace(array('/', "\\"), $albumsep, $album);
		} else {
			$albumsep = '/';
		}
	}
	if (getOption('obfuscate_cache')) {
		$result = '/' . $album . $albumsep . sha1($image . HASH_SEED . $postfix) . '.' . $image . $postfix . '.' . $suffix;
	} else {
		$result = '/' . $album . $albumsep . $image . $postfix . '.' . $suffix;
	}
	return $result;
}

/**
 * Returns the crop/sizing string to postfix to a cache image
 *
 * @param array $args cropping arguments
 * @return string
 */
function getImageCachePostfix($args) {
	list($size, $width, $height, $cw, $ch, $cx, $cy, $quality, $thumb, $crop, $thumbStandin, $passedWM, $adminrequest, $effects) = $args;
	$postfix_string = ($size ? "_$size" : "") .
					($width ? "_w$width" : "") .
					($height ? "_h$height" : "") .
					($cw ? "_cw$cw" : "") .
					($ch ? "_ch$ch" : "") .
					(is_numeric($cx) ? "_cx$cx" : "") .
					(is_numeric($cy) ? "_cy$cy" : "") .
					($thumb || $thumbStandin ? '_thumb' : '') .
					($adminrequest ? '_admin' : '') .
					(($passedWM && $passedWM != NO_WATERMARK) ? '_' . $passedWM : '') .
					($effects ? '_' . $effects : '');
	return $postfix_string;
}

/**
 * Validates and edits image size/cropping parameters
 *
 * @param array $args cropping arguments
 * @return array
 */
function getImageParameters($args, $album = NULL) {
	$thumb_crop = getOption('thumb_crop');
	$thumb_size = getOption('thumb_size');
	$thumb_crop_width = getOption('thumb_crop_width');
	$thumb_crop_height = getOption('thumb_crop_height');
	$image_default_size = getOption('image_size');
	$quality = getOption('image_quality');
// Set up the parameters
	$thumb = $crop = false;
	@list($size, $width, $height, $cw, $ch, $cx, $cy, $quality, $thumb, $crop, $thumbstandin, $WM, $adminrequest, $effects) = $args;
	$thumb = $thumbstandin;

	switch ($size) {
		case 'thumb':
			$thumb = true;
			if ($thumb_crop) {
				$cw = (int) $thumb_crop_width;
				$ch = (int) $thumb_crop_height;
			}
			$size = (int) round($thumb_size);
			break;
		case 'default':
			$size = $image_default_size;
			break;
		case 0:
		default:
			if (empty($size) || !is_numeric($size)) {
				$size = false; // 0 isn't a valid size anyway, so this is OK.
			} else {
				$size = (int) round($size);
			}
			break;
	}

	if (is_numeric($width)) {
		$width = (int) round($width);
	} else {
		$width = false;
	}
	if (is_numeric($height)) {
		$height = (int) round($height);
	} else {
		$height = false;
	}
	if (empty($size) && $width == $height) {
		//square image
		$size = $height;
		$width = $height = false;
	}
	if (is_numeric($cw)) {
		$cw = (int) round($cw);
	} else {
		$cw = false;
	}
	if (is_numeric($ch)) {
		$ch = (int) round($ch);
	} else {
		$ch = false;
	}
	if (is_numeric($quality)) {
		$quality = (int) round($quality);
	} else {
		$quality = false;
	}
	if (empty($quality)) {
		if ($thumb) {
			$quality = (int) round(getOption('thumb_quality'));
		} else {
			$quality = (int) round(getOption('image_quality'));
		}
	}


	if (!is_null($cx)) {
		$cx = (int) round($cx);
	}
	if (!is_null($cy)) {
		$cy = (int) round($cy);
	}

	if (!empty($cw) || !empty($ch)) {
		$crop = true;
	}
	if (is_null($effects)) {
		if ($thumb) {
			if (getOption('thumb_gray')) {
				$effects = 'gray';
			}
		} else {
			if (getOption('image_gray')) {
				$effects = 'gray';
			}
		}
	}
	if (empty($WM)) {
		if (!$thumb) {
			if (!empty($album)) {
				$WM = getAlbumInherited($album, 'watermark', $id);
			}
			if (empty($WM)) {
				$WM = IMAGE_WATERMARK;
			}
		}
	}
// Return an array of parameters used in image conversion.
	$args = array($size, $width, $height, $cw, $ch, $cx, $cy, $quality, $thumb, $crop, $thumbstandin, $WM, $adminrequest, $effects);
	return $args;
}

/**
 * gemerates the image processor protection check code
 *
 * @param array $args
 * @return string
 */
function ipProtectTag($album, $image, $args) {
	if (is_array($image)) {
		$image = $image['name'];
	}
	$tag = sha1(HASH_SEED . $album . $image . serialize($args));
	return $tag;
}

/**
 * forms the i.php parameter list for an image.
 *
 * @param array $args
 * @param string $album the album name
 * @param string $image the image name
 * @return string
 */
function getImageProcessorURI($args, $album, $image) {
	list($size, $width, $height, $cw, $ch, $cx, $cy, $quality, $thumb, $crop, $thumbstandin, $passedWM, $adminrequest, $effects) = $args;
	$args[8] = NULL; // not used by image processor
	$uri = WEBPATH . '/' . ZENFOLDER . '/i.php?a=' . $album;
	if (is_array($image)) {
		$uri .= '&i=' . $image['name'] . '&z=' . ($z = $image['source']);
	} else {
		$uri .= '&i=' . $image;
		$z = NULL;
	}
	if (empty($size)) {
		$args[0] = NULL;
	} else {
		$uri .= '&s=' . ($args[0] = (int) $size);
	}
	if ($width) {
		$uri .= '&w=' . ($args[1] = (int) $width);
	} else {
		$args[1] = NULL;
	}
	if ($height) {
		$uri .= '&h=' . ($args[2] = (int) $height);
	} else {
		$args[2] = NULL;
	}
	if (is_null($cw)) {
		$args[3] = NULL;
	} else {
		$uri .= '&cw=' . ($args[3] = (int) $cw);
	}
	if (is_null($ch)) {
		$args[4] = NULL;
	} else {
		$uri .= '&ch=' . ($args[4] = (int) $ch);
	}
	if (is_null($cx)) {
		$args[5] = NULL;
	} else {
		$uri .= '&cx=' . ($args[5] = (int) $cx);
	}
	if (is_null($cy)) {
		$args[6] = NULL;
	} else {
		$uri .= '&cy=' . ($args[6] = (int) $cy);
	}
	if ($quality) {
		$uri .= '&q=' . ($args[7] = (int) $quality);
	} else {
		$args[7] = NULL;
	}
	$args[8] = NULL;
	if ($crop) {
		$uri .= '&c=' . ($args[9] = 1);
	} else {
		$args[9] = NULL;
	}
	if ($thumb || $thumbstandin) {
		$uri .= '&t=' . ($args[10] = 1);
	} else {
		$args[10] = NULL;
	}
	if ($passedWM) {
		$uri .= '&wmk=' . $passedWM;
	} else {
		$args[11] = NULL;
	}
	if ($adminrequest) {
		$args[12] = true;
		$uri .= '&admin=1';
	} else {
		$args[12] = false;
	}
	if ($effects) {
		$uri .= '&effects=' . $effects;
	} else {
		$args[13] = NULL;
	}
	$args[14] = $z;

	$uri .= '&check=' . ipProtectTag(internalToFilesystem($album), internalToFilesystem($image), $args) . '&cached=' . rand();

	$uri = zp_apply_filter('image_processor_uri', $uri, $args, $album, $image);

	return $uri;
}

/**
 * Extract the image parameters from the input variables
 * @param array $set
 * @return array
 */
function getImageArgs($set) {
	$args = array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
	if (isset($set['s'])) { //0
		if (is_numeric($s = $set['s'])) {
			if ($s) {
				$args[0] = (int) min(abs($s), MAX_SIZE);
			}
		} else {
			$args[0] = sanitize($set['s']);
		}
	} else {
		if (!isset($set['w']) && !isset($set['h'])) {
			$args[0] = MAX_SIZE;
		}
	}
	if (isset($set['w'])) { //1
		$args[1] = (int) min(abs(sanitize_numeric($set['w'])), MAX_SIZE);
	}
	if (isset($set['h'])) { //2
		$args[2] = (int) min(abs(sanitize_numeric($set['h'])), MAX_SIZE);
	}
	if (isset($set['cw'])) { //3
		$args[3] = (int) sanitize_numeric(($set['cw']));
	}
	if (isset($set['ch'])) { //4
		$args[4] = (int) sanitize_numeric($set['ch']);
	}
	if (isset($set['cx'])) { //5
		$args[5] = (int) sanitize_numeric($set['cx']);
	}
	if (isset($set['cy'])) { //6
		$args[6] = (int) sanitize_numeric($set['cy']);
	}
	if (isset($set['q'])) { //7
		$args[7] = (int) sanitize_numeric($set['q']);
	}
	if (isset($set['c'])) {// 9
		$args[9] = (int) sanitize($set['c']);
	}
	if (isset($set['t'])) { //10
		$args[10] = (int) sanitize($set['t']);
	}
	if (isset($set['wmk']) && !isset($_GET['admin'])) { //11
		$args[11] = sanitize($set['wmk']);
	}
	$args[12] = (bool) isset($_GET['admin']); //12

	if (isset($set['effects'])) { //13
		$args[13] = sanitize($set['effects']);
	}
	if (isset($set['z'])) { //	14
		$args[14] = sanitize($set['z']);
	}

	return $args;
}

/**
 *
 * Returns an URI to the image:
 *
 * 	If the image is not cached, the uri will be to the image processor
 * 	If the image is cached then the uri will depend on the site option for
 * 	cache serving. If the site is set for open cache the uri will point to
 * 	the cached image. If the site is set for protected cache the uri will
 * 	point to the image processor (which will serve the image from the cache.)
 * 	NOTE: this latter implies added overhead for each and every image fetch!
 *
 * @param array $args
 * @param string $album the album name
 * @param string $image the image name
 * @param int $mitme mtime of the image
 * @return string
 */
function getImageURI($args, $album, $image, $mtime) {
	$cachefilename = getImageCacheFilename($album, $image, $args);
	if (OPEN_IMAGE_CACHE && file_exists(SERVERCACHE . $cachefilename)) {
		if (($cachefiletime = filemtime(SERVERCACHE . $cachefilename)) >= $mtime) {
			return WEBPATH . '/' . CACHEFOLDER . imgSrcURI($cachefilename) . '?cached=' . $cachefiletime;
		}
	}
	return getImageProcessorURI($args, $album, $image);
}

/**
 *
 * Returns an array of html tags allowed
 * @param string $which either 'allowed_tags' or 'style_tags' depending on which is wanted.
 */
function getAllowedTags($which) {
	global $_user_tags, $_style_tags, $_default_tags;
	switch ($which) {
		case 'allowed_tags':
			if (is_null($_user_tags)) {
				$user_tags = "(" . getOption('allowed_tags') . ")";
				$allowed_tags = parseAllowedTags($user_tags);
				if ($allowed_tags === false) { // someone has screwed with the 'allowed_tags' option row in the database, but better safe than sorry
					$allowed_tags = array();
				}
				$_user_tags = $allowed_tags;
			}
			return $_user_tags;
			break;
		case 'style_tags':
			if (is_null($_style_tags)) {
				$style_tags = "(" . getOption('style_tags') . ")";
				$allowed_tags = parseAllowedTags($style_tags);
				if ($allowed_tags === false) { // someone has screwed with the 'style_tags' option row in the database, but better safe than sorry
					$allowed_tags = array();
				}
				$_style_tags = $allowed_tags;
			}
			return $_style_tags;
			break;
		case 'allowed_tags_default':
			if (is_null($_default_tags)) {
				$default_tags = "(" . getOption('allowed_tags_default') . ")";
				$allowed_tags = parseAllowedTags($default_tags);
				if ($allowed_tags === false) { // someone has screwed with the 'allowed_tags' option row in the database, but better safe than sorry
					$allowed_tags = array();
				}
				$_default_tags = $allowed_tags;
			}
			return $_default_tags;
			break;
	}
	return array();
}

/**
 * parses a query string WITHOUT url decoding it!
 * @param string $str
 */
function parse_query($str) {
	$pairs = explode('&', $str);
	$params = array();
	foreach ($pairs as $pair) {
		if (strpos($pair, '=') === false) {
			$params[$pair] = NULL;
		} else {
			list($name, $value) = explode('=', $pair, 2);
			$params[$name] = $value;
		}
	}
	return $params;
}

/**
 * Builds a url from parts
 * @param array $parts
 * @return string
 */
function build_url($parts) {
	$u = '';
	if (isset($parts['scheme'])) {
		$u .= $parts['scheme'] . '://';
	}
	if (isset($parts['host'])) {
		$u .= $parts['host'];
	}
	if (isset($parts['port'])) {
		$u .= ':' . $parts['port'];
	}
	if (isset($parts['path'])) {
		if (empty($u)) {
			$u = $parts['path'];
		} else {
			$u .= '/' . ltrim($parts['path'], '/');
		}
	}
	if (isset($parts['query'])) {
		$u .= '?' . $parts['query'];
	}
	if (isset($parts['fragment '])) {
		$u .= '#' . $parts['fragment '];
	}
	return $u;
}

/**
 * UTF-8 aware parse_url() replacement.
 *
 * @return array
 */
function mb_parse_url($url) {
	$enc_url = preg_replace_callback(
					'%[^:/@?&=#]+%usD', function ($matches) {
		return urlencode($matches[0]);
	}, $url
	);

	$parts = parse_url($enc_url);

	if ($parts === false) {
		throw new \InvalidArgumentException('Malformed URL: ' . $url);
	}

	foreach ($parts as $name => $value) {
		$parts[$name] = urldecode($value);
	}

	return $parts;
}

/**
 * rawurlencode function that is path-safe (does not encode /)
 *
 * @param string $path URL
 * @return string
 */
function pathurlencode($path) {
	$parts = mb_parse_url($path);
	if (isset($parts['query'])) {
//	some kind of query link
		$pairs = parse_query($parts['query']);
		$parts['query'] = http_build_query($pairs);
	}
	if (array_key_exists('path', $parts)) {
		$parts['path'] = implode("/", array_map("rawurlencode", explode("/", $parts['path'])));
	}
	return build_url($parts);
}

/**
 * Returns the fully qualified path to the album folders
 *
 * @param string $root the base from whence the path dereives
 * @return sting
 */
function getAlbumFolder($root = SERVERPATH) {
	global $_zp_album_folder, $_zp_conf_vars;
	if (is_null($_zp_album_folder)) {
		if (!isset($_zp_conf_vars['external_album_folder']) || empty($_zp_conf_vars['external_album_folder'])) {
			if (!isset($_zp_conf_vars['album_folder']) || empty($_zp_conf_vars['album_folder'])) {
				$_zp_album_folder = $_zp_conf_vars['album_folder'] = '/' . ALBUMFOLDER . '/';
			} else {
				$_zp_album_folder = str_replace('\\', '/', $_zp_conf_vars['album_folder']);
			}
		} else {
			$_zp_conf_vars['album_folder_class'] = 'external';
			$_zp_album_folder = str_replace('\\', '/', $_zp_conf_vars['external_album_folder']);
		}
		if (substr($_zp_album_folder, -1) != '/')
			$_zp_album_folder .= '/';
	}
	$root = str_replace('\\', '/', $root);
	switch (@$_zp_conf_vars['album_folder_class']) {
		default:
			$_zp_conf_vars['album_folder_class'] = 'std';
		case 'std':
			return $root . $_zp_album_folder;
		case 'in_webpath':
			if (WEBPATH) { // strip off the WEBPATH
				$pos = strrpos($root, WEBPATH);
				if ($pos !== false) {
					$root = substr_replace($root, '', $pos, strlen(WEBPATH));
				}
				if ($root == '/') {
					$root = '';
				}
			}
			return $root . $_zp_album_folder;
		case 'external':
			return $_zp_album_folder;
	}
}

/**
 * Rolls a log over if it has grown too large.
 *
 * @param string $log
 */
function switchLog($log) {
	$dir = getcwd();
	chdir(SERVERPATH . '/' . DATA_FOLDER);
	$list = safe_glob($log . '-*.log');
	$counter = count($list) + 1;

	chdir($dir);
	@copy(SERVERPATH . '/' . DATA_FOLDER . '/' . $log . '.log', SERVERPATH . '/' . DATA_FOLDER . '/' . $log . '-' . $counter . '.log');
	if (getOption($log . '_log_mail')) {
		zp_mail(sprintf(gettext('%s log size limit exceeded'), $log), sprintf(gettext('The %1$s log has exceeded its size limit and has been renamed to %2$s.'), $log, $log . '-' . $counter . '.log'));
	}
}

/**
 * Tool to log execution times of script bits
 *
 * @param string $point location identifier
 */
function instrument($point) {
	global $_zp_timer;
	$now = microtime(true);
	if (empty($_zp_timer)) {
		$delta = '';
	} else {
		$delta = ' (' . ($now - $_zp_timer) . ')';
	}
	$_zp_timer = microtime(true);
	debugLogBacktrace($point . ' ' . $now . $delta);
}

/**
 * Parses a byte size from a size value (eg: 100M) for comparison.
 */
function parse_size($size) {
	$suffixes = array(
			'' => 1,
			'k' => 1024,
			'm' => 1048576, // 1024 * 1024
			'g' => 1073741824, // 1024 * 1024 * 1024
	);
	if (preg_match('/([0-9]+)\s*(k|m|g)?(b?(ytes?)?)/i', $size, $match)) {
		return $match[1] * $suffixes[strtolower($match[2])];
	}
}

/** getAlbumArray - returns an array of folder names corresponding to the given album string.
 * @param string $albumstring is the path to the album as a string. Ex: album/subalbum/my-album
 * @param string $includepaths is a boolean whether or not to include the full path to the album
 *    in each item of the array. Ex: when $includepaths==false, the above array would be
 *    ['album', 'subalbum', 'my-album'], and with $includepaths==true,
 *    ['album', 'album/subalbum', 'album/subalbum/my-album']
 *  @return array
 */
function getAlbumArray($albumstring, $includepaths = false) {
	if ($includepaths) {
		$array = array($albumstring);
		while ($slashpos = strrpos($albumstring, '/')) {
			$albumstring = substr($albumstring, 0, $slashpos);
			array_unshift($array, $albumstring);
		}
		return $array;
	} else {
		return explode('/', $albumstring);
	}
}

/**
 * Returns an img src URI encoded based on the OS of the server
 *
 * @param string $uri uri in FILESYSTEM_CHARSET encoding
 * @return string
 */
function imgSrcURI($uri) {
	if (UTF8_IMAGE_URI)
		return filesystemToInternal($uri);
	return $uri;
}

/**
 * returns the non-empty value of $field from the album or one of its parents
 *
 * @param string $folder the album name
 * @param string $field the desired field name
 * @param int $id will be set to the album `id` of the album which has the non-empty field
 * @return string
 */
function getAlbumInherited($folder, $field, &$id) {
	$folders = explode('/', filesystemToInternal($folder));
	$album = array_shift($folders);
	$like = ' LIKE ' . db_quote(db_LIKE_escape($album));
	while (!empty($folders)) {
		$album .= '/' . array_shift($folders);
		$like .= ' OR `folder` LIKE ' . db_quote(db_LIKE_escape($album));
	}
	$sql = 'SELECT `id`, `' . $field . '` FROM ' . prefix('albums') . ' WHERE `folder`' . $like;
	$result = query_full_array($sql);
	if (!is_array($result))
		return '';
	while (count($result) > 0) {
		$try = array_pop($result);
		if (!empty($try[$field])) {
			$id = $try['id'];
			return $try[$field];
		}
	}
	return '';
}

/**
 * primitive theme setup for image handling scripts
 *
 * we need to conserve memory so loading the classes is out of the question.
 *
 * @param string $album
 * @return string
 */
function imageThemeSetup($album) {
	// we need to conserve memory in i.php so loading the classes is out of the question.
	$id = NULL;
	$theme = getAlbumInherited(filesystemToInternal($album), 'album_theme', $id);
	if (empty($theme)) {
		$galleryoptions = getSerializedArray(getOption('gallery_data'));
		$theme = @$galleryoptions['current_theme'];
	}
	loadLocalOptions($id, $theme);
	return $theme;
}

/**
 * Returns the path to a watermark
 *
 * @param string $wm watermark name
 * @return string
 */
function getWatermarkPath($wm) {
	$path = SERVERPATH . '/' . ZENFOLDER . '/watermarks/' . internalToFilesystem($wm) . '.png';
	if (!file_exists($path)) {
		$path = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/watermarks/' . internalToFilesystem($wm) . '.png';
	}
	return $path;
}

/**
 *
 * Returns the script requesting URI.
 * 	Uses $_SERVER[REQUEST_URI] if it exists, otherwise it concocts the URI from
 * 	$_SERVER[SCRIPT_NAME] and $_SERVER[QUERY_STRING]
 *
 * @param bool $decode Set true to urldecode the uri
 * @return string
 */
function getRequestURI($decode = true) {
	if (array_key_exists('REQUEST_URI', $_SERVER)) {
		$uri = sanitize(str_replace('\\', '/', $_SERVER['REQUEST_URI']));
		preg_match('|^(http[s]*\://[a-zA-Z0-9\-\.]+/?)*(.*)$|xis', $uri, $matches);
		$uri = $matches[2];
		if (!empty($matches[1])) {
			$uri = '/' . $uri;
		}
	} else {
		$uri = sanitize(str_replace('\\', '/', @$_SERVER['SCRIPT_NAME']));
	}
	if ($decode) {
		$uri = urldecode($uri);
	}
	return $uri;
}

/**
 * Provide an alternative to glob which does not return filenames with accented charactes in them
 *
 * NOTE: this function ignores "hidden" files whose name starts with a period!
 *
 * @param string $pattern the 'pattern' for matching files
 * @param bit $flags glob 'flags'
 */
function safe_glob($pattern, $flags = 0) {
	$split = explode('/', $pattern);
	$match = '/^' . strtr(addcslashes(array_pop($split), '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i';
	$path_return = $path = implode('/', $split);
	if (empty($path)) {
		$path = '.';
	} else {
		$path_return = $path_return . '/';
	}
	if (!is_dir($path))
		return array();
	if (($dir = opendir($path)) !== false) {
		$glob = array();
		while (($file = readdir($dir)) !== false) {
			if (@preg_match($match, $file) && $file{0} != '.') {
				if (is_dir("$path/$file")) {
					if ($flags & GLOB_MARK)
						$file.='/';
					$glob[] = $path_return . $file;
				} else if (!is_dir("$path/$file") && !($flags & GLOB_ONLYDIR)) {
					$glob[] = $path_return . $file;
				}
			}
		}
		closedir($dir);
		if (!($flags & GLOB_NOSORT))
			sort($glob);
		return $glob;
	} else {
		return array();
	}
}

/**
 *
 * Check to see if the setup script needs to be run
 */
function checkInstall() {
	if (OFFSET_PATH != 2) {
		preg_match('|([^-]*)|', ZENPHOTO_VERSION, $version);
		if ($i = getOption('zenphoto_install')) {
			$install = getSerializedArray($i);
			if (isset($install['ZENPHOTO'])) {
				preg_match('|([^-]*).*\[(.*)\]|', $install['ZENPHOTO'], $matches);
				if (isset($matches[1]) && $matches[1] != $version[1]) {
					_setup(14);
				}
			}
		}
		if ($i != serialize(installSignature())) {
			_setup((int) ($i === NULL));
		}
	}
}

/**
 * registers a request to have setup run
 * @param string $whom the requestor
 * @param string $addl additional information for request message
 *
 * @author Stephen Billard
 * @Copyright 2015 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 */
function requestSetup($whom, $addl = NULL) {
	$sig = getSerializedArray(getOption('zenphoto_install'));
	$sig['REQUESTS'][$whom] = $whom;
	if (!is_null($addl)) {
		$sig['REQUESTS'][$whom] .= ' (' . $addl . ')';
	}

	setOption('zenphoto_install', serialize($sig));
}

/**
 * Force a setup to get the configuration right
 *
 * @param int $action if positive the setup is mandatory
 *
 * @author Stephen Billard
 * @Copyright 2015 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 */
function _setup($action) {
	require_once(dirname(__FILE__) . '/reconfigure.php');
	reconfigureAction($action);
}

/**
 *
 * Computes the "installation signature" of the zenphoto install
 * @return string
 */
function installSignature() {
	$folder = dirname(__FILE__);
	$testFiles = array(
			'template-functions.php' => filesize($folder . '/template-functions.php'),
			'functions-filter.php' => filesize($folder . '/functions-filter.php'),
			'lib-auth.php' => filesize($folder . '/lib-auth.php'),
			'lib-utf8.php' => filesize($folder . '/lib-utf8.php'),
			'functions.php' => filesize($folder . '/functions.php'),
			'functions-basic.php' => filesize($folder . '/functions-basic.php'),
			'functions-controller.php' => filesize($folder . '/functions-controller.php'),
			'functions-image.php' => filesize($folder . '/functions-image.php')
	);

	if (isset($_SERVER['SERVER_SOFTWARE'])) {
		$s = $_SERVER['SERVER_SOFTWARE'];
	} else {
		$s = 'software unknown';
	}
	$dbs = db_software();
	$version = ZENPHOTO_VERSION;
	$i = strpos($version, '-');
	if ($i !== false) {
		$version = substr($version, 0, $i);
	}
	return array_merge($testFiles, array(
			'SERVER_SOFTWARE' => $s,
			'ZENPHOTO' => $version,
			'FOLDER' => dirname(dirname(__FILE__)),
			'DATABASE' => $dbs['application'] . ' ' . $dbs['version']
					)
	);
}

/**
 *
 * Call when terminating a script.
 */
function exitZP() {
	exit();
}
