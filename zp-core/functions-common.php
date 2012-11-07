<?php
/**
 * functions common to both the Zenphoto core and setup's basic environment
 *
 * @package core
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
function zpErrorHandler($errno, $errstr='', $errfile='', $errline='') {
	// if error has been supressed with an @
	if (error_reporting() == 0) {
		return;
	}
	// check if function has been called by an exception
	if(func_num_args() == 5) {
		// called by trigger_error()
		list($errno, $errstr, $errfile, $errline) = func_get_args();
	} else {
		// caught exception
		$exc = func_get_arg(0);
		$errno = $exc->getCode();
		$errstr = $exc->getMessage();
		$errfile = $exc->getFile();
		$errline = $exc->getLine();
	}

	$errorType = array (E_ERROR           	=> gettext('ERROR'),
			E_WARNING      			=> gettext('WARNING'),
			E_NOTICE        		=> gettext('NOTICE'),
			E_USER_ERROR  			=> gettext('USER ERROR'),
			E_USER_WARNING			=> gettext('USER WARNING'),
			E_USER_NOTICE 			=> gettext('USER NOTICE'),
			E_STRICT     				=> gettext('STRICT NOTICE')
	);

	// create error message

	if (array_key_exists($errno, $errorType)) {
		$err = $errorType[$errno];
	} else {
		$err = gettext("EXCEPTION ($errno)");
		$errno = E_ERROR;
	}
	$msg = sprintf(gettext('%1$s: %2$s in %3$s on line %4$s'),$err,$errstr,$errfile,$errline);
	debugLogBacktrace($msg, 1);
	// what to do
	switch ($errno) {
		default:
			@ini_set('display_errors', 1);
		case E_NOTICE:
		case E_USER_NOTICE:
		case E_WARNING:
		case E_USER_WARNING:
			return false;
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
	if (get_magic_quotes_gpc()) $filename = stripslashes(trim($filename));
	$filename = strip_tags(str_replace('\\', '/', $filename));
	$filename = preg_replace(array('/x00/','/\/\/+/','/\/\.\./','/\/\./','/:/','/</','/>/','/\?/','/\*/','/\"/','/\|/','/\/+$/','/^\/+/'), '', $filename);
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
function sanitize($input_string, $sanitize_level=3) {
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
		return strip_tags($input_string);
	}
}

/** returns a sanitized string for the sanitize function
 * @param string $input_string
 * @param string $sanitize_level
 * @return string the sanitized string.
 */
function sanitize_string($input, $sanitize_level) {
	// Strip slashes if get_magic_quotes_gpc is enabled.
	if (is_string($input)) {
		if (get_magic_quotes_gpc()) {
			$input = stripslashes($input);
		}
		switch($sanitize_level) {
			case 0:
				return str_replace(chr(0), " ", $input);
			case 1:
				// Text formatting sanititation.
				return ksesProcess($input, getAllowedTags('allowed_tags'));
			case 2:
				// Strips non-style tags.
				return ksesProcess($input, getAllowedTags('style_tags'));
			case 3:
				// Full sanitation.  Strips all code.
				return ksesProcess($input, array());
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
		$set .= ' `' . $var . '`=';
		if (is_null($value)) {
			$set .= 'NULL';
		} else {
			$set .= db_quote($value).',';
		}
	}
	return substr($set,0,-1);
}

/*
 * returns the connected database name
*/
function db_name() {
	global $_zp_conf_vars;
	return $_zp_conf_vars['mysql_database'];
}

function db_count($table, $clause=NULL, $field="*") {
	$sql = 'SELECT COUNT('.$field.') FROM '.prefix($table).' '.$clause;
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
 * @param bool $fatal set true to fail the script
 */
function zp_error($message, $fatal=E_USER_ERROR) {
	trigger_error($message, $fatal);
}

function html_decode($string) {
	return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
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
 * @param string $str string to be encoded
 * @param bool $allowScript set to false to prevent pass-through of script tags.
 * @return string
 */
function html_encodeTagged($str, $allowScript=true) {
	$tags = array();
	//html comments
	preg_match_all('|<!--.*-->|ixs', $str, $matches);
	foreach (array_unique($matches[0]) as $key=>$tag) {
		$tags['%'.$key.'$j'] = $tag;
		$str = str_replace($tag, '%'.$key.'$j', $str);
	}
	//javascript
	if ($allowScript) {
		preg_match_all('!<script.*>.*</script>!ixs', $str, $matches);
		foreach (array_unique($matches[0]) as $key=>$tag) {
			$tags['%'.$key.'$j'] = $tag;
			$str = str_replace($tag, '%'.$key.'$j', $str);
		}
	} else {
		$str = preg_replace('|<a(.*)href(.*)=(.*)javascript|ixs', '%$x', $str);
		$tags['%$x'] = '&lt;a href=<strike>javascript</strike>';
		$str = preg_replace('|<(.*)onclick|ixs', '%$c', $str);
		$tags['%$c'] = '&lt;<strike>onclick</strike>';
	}
	// markup
	preg_match_all("/<\/?\w+((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>/i", $str, $matches);
	foreach (array_unique($matches[0]) as $key=>$tag) {
		$tags['%'.$key.'$s'] = $tag;
		$str = str_replace($tag, '%'.$key.'$s', $str);
	}
	//entities
	preg_match_all('/(&[a-z#]+;)/', $str, $matches);
	foreach (array_unique($matches[0]) as $key=>$entity) {
		$tags['%'.$key.'$e'] = $entity;
		$str = str_replace($entity, '%'.$key.'$e', $str);
	}
	$str = strtr(htmlspecialchars($str, ENT_FLAGS, LOCAL_CHARSET),$tags);
	return $str;
}

/**
 * Makes directory recursively, returns TRUE if exists or was created sucessfuly.
 * Note: PHP5 includes a recursive parameter to mkdir, but PHP4 does not, so this
 *   is required to target PHP4.
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
function debugLogBacktrace($message, $omit=0) {
	$output = trim($message)."\n";
	// Get a backtrace.
	$bt = debug_backtrace();
	while ($omit>=0) {
		array_shift($bt); // Get rid of debug_backtrace, callers in the backtrace.
		$omit--;
	}
	$prefix = '  ';
	$line = '';
	$caller = '';
	foreach($bt as $b) {
		$caller = (isset($b['class']) ? $b['class'] : '')	. (isset($b['type']) ? $b['type'] : '')	. $b['function'];
		if (!empty($line)) { // skip first output to match up functions with line where they are used.
			$prefix .= '  ';
			$output .= 'from '.$caller.' ('.$line.")\n".$prefix;
		} else {
			$output .= '  '.$caller." called ";
		}
		$date = false;
		if (isset($b['file']) && isset($b['line'])) {
			$line = basename($b['file'])	. ' [' . $b['line'] . "]";
		} else {
			$line = 'unknown';
		}
	}
	if (!empty($line)) {
		$output .= 'from '.$line;
	}
	debugLog($output);
}

/**
 * Records a Var to the debug log
 *
 * @param string $message message to insert in log
 * @param mixed $var the variable to record
 */
function debugLogVar($message, $var) {
	ob_start();
	var_dump($var);
	$str = ob_get_contents();
	ob_end_clean();
	debugLog(trim($message).' '.html_decode(strip_tags($str)));
}

?>
