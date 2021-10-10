<?php

/**
 * functions common to both the Zenphoto core and setup's basic environment
 *
 * @package core
 * @subpackage functions\functions-common
 */

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
	// check if function has been called by an exception
	if (is_object($errno)) {
		$error = $errno;
		$errorclass = get_class($error);
		switch ($errorclass) {
			default:
			case 'Exception':
				$errno = $error->getCode();
				break;
			case 'Error';
				$errno = 1; //always fatal error - getCode() does not work here
				break;
		}
		$errstr = $error->getMessage();
		$errfile = $error->getFile();
		$errline = $error->getLine();
	} else if (func_num_args() == 5) { // 5th parameter is deprecated
		// called by trigger_error()
		list($errno, $errstr, $errfile, $errline) = func_get_args();
	}
	// if error has been supressed with an @
	if (!(error_reporting() & $errno) && !in_array($errno, array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE))) {
		return;
	}
	$errorType = array(
			E_ERROR => gettext('ERROR'),
			E_WARNING => gettext('WARNING'),
			E_PARSE => gettext('PARSE ERROR'),
			E_NOTICE => gettext('NOTICE'),
			E_CORE_ERROR => gettext('CORE ERROR'),
			E_CORE_WARNING => gettext('CORE WARNING'),
			E_COMPILE_ERROR => gettext('COMPILE ERROR'),
			E_COMPILE_WARNING => gettext('COMPILE WARNING'),
			E_USER_ERROR => gettext('USER ERROR'),
			E_USER_WARNING => gettext('USER WARNING'),
			E_USER_NOTICE => gettext('USER NOTICE'),
			E_STRICT => gettext('STRICT NOTICE'),
			E_RECOVERABLE_ERROR => gettext('RECOVERABLE ERROR'),
			E_DEPRECATED => gettext('DEPRECATED'),
			E_USER_DEPRECATED => gettext('USER DEPRECATED NOTICE')
	);
	// create error message
	if (array_key_exists($errno, $errorType)) {
		$err = $errorType[$errno];
	} else {
		$err = gettext("EXCEPTION ($errno)");
		$errno = E_ERROR;
	}
	$msg = sprintf(gettext('%1$s: %2$s in %3$s on line %4$s'), $err, $errstr, $errfile, $errline);
	debugLogBacktrace($msg, 1);
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
 * Converts a Zenphoto Internal filename string to one compatible with the file system
 *
 * @param string $filename the file name to convert
 * @return string
 */
function internalToFilesystem($filename) {
	global $_zp_UTF8;
	return $_zp_UTF8->convert($filename, LOCAL_CHARSET, FILESYSTEM_CHARSET);
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
	$filename = preg_replace(array('/[[:cntrl:]]/', '/\/\/+/', '/\/\.\./', '/\/\./', '/:/', '/</', '/>/', '/\?/', '/\*/', '/\"/', '/\|/', '/\/+$/', '/^\/+/'), '', $filename);
	$filename = preg_replace(array('/\/+$/', '/^\/+/'), '', $filename);
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
 * null-bytes, and optionally use KSES
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
		return getBare($input_string);
	}
}

/**
 * Cleans tags and some content.
 * @param type $content
 * @return type
 */
function getBare($content) {
  $content = preg_replace('~<script.*?/script>~is', '', $content);
  $content = preg_replace('~<style.*?/style>~is', '', $content);
  $content = preg_replace('~<!--.*?-->~is', '', $content);
  $content = strip_tags($content);
  $content = str_replace('&nbsp;', ' ', $content);
  return $content;
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
				return getBare($input);
 
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
	if(defined('DATABASE_PREFIX')) {
		$prefix = DATABASE_PREFIX;
	} else{
		$prefix = 'zp_'; // use default in case this constant is not set in setup primitive environments
	}
	return '`' . $prefix . $tablename . '`';
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

/**
 * triggers an error
 *
 * @param string $message
 * @param int $type the PHP error type to trigger; default to E_USER_ERROR
 */
function zp_error($message, $fatal = E_USER_ERROR) {
	// Print the error message, to be convenient.
	printf(html_encode($message));
	trigger_error($message, $fatal);
}

function html_decode($string) {
	return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
}

/**
 * encodes a pre-sanitized string to be used in an HTML text-only field (value, alt, title, etc.)
 *
 * @param string $str
 * @return string
 */
function html_encode($str) {
	return htmlspecialchars($str, ENT_FLAGS, LOCAL_CHARSET);
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
	$str = htmLawed($str);
	//entities
	preg_match_all('/(&[a-z0-9#]+;)/i', $str, $matches);
	foreach (array_unique($matches[0]) as $key => $entity) {
		$tags[3]['%' . $key . '$e'] = $entity;
		$str = str_replace($entity, '%' . $key . '$e', $str);
	} 
	$str = htmlspecialchars($str, ENT_FLAGS, LOCAL_CHARSET);
	foreach (array_reverse($tags, true) as $taglist) {
		$str = strtr($str, $taglist);
	}
	if ($str != $original) {
		$str = tidyHTML($str);
	}
	return $str;
}

/**
 * Convenience wrapper of html_encode(pathurlencode($url))
 * Primarily intended for use with img src URLs
 * 
 * @since ZenphotoCMS 1.5.8
 * 
 * @param string $url
 * @return string
 */
function html_pathurlencode($url) {
	return html_encode(pathurlencode($url));
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
 */
function debugLogBacktrace($message, $omit = 0) {
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
	debugLog($output);
}

/**
 * Records a Var to the debug log
 *
 * @param string $message message to insert in log [optional]
 * @param mixed $var the variable to record
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
	ob_start();
	var_dump($var);
	$str = ob_get_contents();
	ob_end_clean();
	debugLog(trim($message) . "\r" . html_decode(getBare($str)));
}

/**
 * Returns the value of a cookie from either the cookies or from $_SESSION[]
 *
 * @param string $name the name of the cookie
 */
function zp_getCookie($name) {
  if (isset($_COOKIE[$name])) {
    $cookiev = sanitize($_COOKIE[$name]);
  } else {
    $cookiev = '';
  }
  if (DEBUG_LOGIN) {
    if (isset($_SESSION[$name])) {
      $sessionv = sanitize($_SESSION[$name]);
    } else {
      $sessionv = '';
    }
    debugLog(zp_getCookie($name) . '=::' . 'album_session=' . GALLERY_SESSION . "; SESSION[" . session_id() . "]=" . sanitize($sessionv) . ", COOKIE=" . sanitize($cookiev));
  }
  if (!empty($cookiev) && (defined('GALLERY_SESSION') && !GALLERY_SESSION)) {
    return zp_cookieEncode($cookiev);
  }
  if (isset($_SESSION[$name])) {
    return sanitize($_SESSION[$name]);
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
 * @param bool $httponly true if access to this cookie should only be allowed via http (e.g. no access to JS etc.). Requires browser support though.
 */
function zp_setCookie($name, $value, $time = NULL, $path = NULL, $secure = false, $httponly = false) {
  if (empty($value)) {
    $cookiev = '';
  } else {
    $cookiev = zp_cookieEncode(sanitize($value));
  }
  if (is_null($time)) {
    $time = COOKIE_PERSISTENCE;
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
		if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
			$options = array(
					'expires' => (time() + $time),
					'path' => $path,
					'secure' => $secure,
					'httponly' => $httponly,
					'samesite' => 'Lax'
			);
			setcookie($name, $cookiev, $options);
		} else {
			setcookie($name, $cookiev, time() + $time, $path, '', $secure, $httponly);
		}
	}
	if ($time < 0) {
    if (isset($_SESSION))
      unset($_SESSION[$name]);
    if (isset($_COOKIE))
      unset($_COOKIE[$name]);
  } else {
    $_SESSION[$name] = sanitize($value);
    $_COOKIE[$name] = sanitize($cookiev);
  }
}

/**
 * Clears a cookie
 * @param string $name The 'cookie' name
 * @param string $path The path on the server in which the cookie will be available on
 * @param bool $secure true if secure cookie
 * @param bool $httponly true if access to this cookie should only be allowed via http (e.g. no access to JS etc.). Requires browser support though.
 */
function zp_clearCookie($name, $path = NULl, $secure = false, $httponly = false) {
	zp_setCookie($name, '', -368000, $path, $secure, $httponly);
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
	if (preg_match('/^a:[0-9]+:{/', $string)) {
		$r = @unserialize($string);
		if ($r) {
			return $r;
		} else {
			return array();
		}
	} else if (strlen($string) == 0 && !is_bool($string)) {
		return array();
	} else {
		return array($string);
	}
}

?>
