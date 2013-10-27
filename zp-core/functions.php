<?php
/**
 * basic functions used by zenphoto
 *
 * @package core
 *
 */
// force UTF-8 Ø

global $_zp_current_context_stack, $_zp_HTML_cache;

if (!function_exists("json_encode")) {
	// load the drop-in replacement library
	require_once(dirname(__FILE__) . '/lib-json.php');
}

require_once(dirname(__FILE__) . '/functions-basic.php');
require_once(dirname(__FILE__) . '/functions-filter.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/lib-kses.php');

$_zp_captcha = new _zp_captcha(); // this will be overridden by the plugin if enabled.
$_zp_HTML_cache = new _zp_HTML_cache(); // this will be overridden by the plugin if enabled.
//setup session before checking for logon cookie
require_once(dirname(__FILE__) . '/functions-i18n.php');

if (GALLERY_SESSION) {
	zp_session_start();
}

define('ZENPHOTO_LOCALE', setMainDomain());
define('SITE_LOCALE', getOptionFromDB('locale'));

require_once(dirname(__FILE__) . '/load_objectClasses.php');

$_zp_current_context_stack = array();

$_zp_albumthumb_selector = array(array('field' => '', 'direction' => '', 'desc' => 'random'),
				array('field' => 'id', 'direction' => 'DESC', 'desc' => gettext('most recent')),
				array('field' => 'mtime', 'direction' => '', 'desc' => gettext('oldest')),
				array('field' => 'title', 'direction' => '', 'desc' => gettext('first alphabetically')),
				array('field' => 'hitcounter', 'direction' => 'DESC', 'desc' => gettext('most viewed'))
);

$_zp_missing_album = new AlbumBase(gettext('missing'), false);
$_zp_missing_image = new Transientimage($_zp_missing_album, SERVERPATH . '/' . ZENFOLDER . '/images/err-imagenotfound.png');

/**
 * parses the allowed HTML tags for use by htmLawed
 *
 * @param string &$source by name, contains the string with the tag options
 * @return array the allowed_tags array.
 * @since 1.1.3
 * */
function parseAllowedTags(&$source) {
	$source = trim($source);
	if (substr($source, 0, 1) != "(") {
		return false;
	}
	$source = substr($source, 1); //strip off the open paren
	$a = array();
	while ((strlen($source) > 1) && (substr($source, 0, 1) != ")")) {
		$i = strpos($source, '=>');
		if ($i === false) {
			return false;
		}
		$tag = trim(substr($source, 0, $i));
		$source = trim(substr($source, $i + 2));
		if (substr($source, 0, 1) != "(") {
			return false;
		}
		$x = parseAllowedTags($source);
		if ($x === false) {
			return false;
		}
		$a[$tag] = $x;
	}
	if (substr($source, 0, 1) != ')') {
		return false;
	}
	$source = trim(substr($source, 1)); //strip the close paren
	return $a;
}

/**
 * Search for a thumbnail for the image
 *
 * @param $localpath local path of the image
 * @return string
 */
function checkObjectsThumb($localpath) {
	global $_zp_supported_images;
	$image = stripSuffix($localpath);
	$candidates = safe_glob($image . '.*');
	foreach ($candidates as $file) {
		$ext = substr($file, strrpos($file, '.') + 1);
		if (in_array(strtolower($ext), $_zp_supported_images)) {
			return basename($image . '.' . $ext);
		}
	}
	return NULL;
}

/**
 * Returns a truncated string
 *
 * @param string $string souirce string
 * @param int $length how long it should be
 * @param string $elipsis the text to tack on indicating shortening
 * @return string
 */
function truncate_string($string, $length, $elipsis = '...') {
	if (mb_strlen($string) > $length) {
		$string = mb_substr($string, 0, $length);
		$pos = mb_strrpos(strtr($string, array('~' => ' ', '!' => ' ', '@' => ' ', '#' => ' ', '$' => ' ', '%' => ' ', '^' => ' ', '&' => ' ', '*' => ' ', '(' => ' ', ')' => ' ', '+' => ' ', '=' => ' ', '-' => ' ', '{' => ' ', '}' => ' ', '[' => ' ', ']' => ' ', '|' => ' ', ':' => ' ', ';' => ' ', '<' => ' ', '>' => ' ', '.' => ' ', '?' => ' ', '/' => ' ', '\\', '\\' => ' ', "'" => ' ', "`" => ' ', '"' => ' ')), ' ');
		if ($pos === FALSE) {
			$string .= $elipsis;
		} else {
			$string = mb_substr($string, 0, $pos) . $elipsis;
		}
	}
	return $string;
}

/**
 *
 * fixes unbalanced HTML tags. Used by shortenContent when PHP tidy is not present
 * @param string $html
 * @return string
 */
function cleanHTML($html) {

	preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
	$openedtags = $result[1];

	preg_match_all('#</([a-z]+)>#iU', $html, $result);
	$closedtags = $result[1];

	$len_opened = count($openedtags);

	if (count($closedtags) == $len_opened) {
		return $html;
	}

	$openedtags = array_reverse($openedtags);
	for ($i = 0; $i < $len_opened; $i++) {
		if (!in_array($openedtags[$i], $closedtags)) {
			$html .= '</' . $openedtags[$i] . '>';
		} else {
			unset($closedtags[array_search($openedtags[$i], $closedtags)]);
		}
	}

	return $html;
}

/**
 * Returns truncated html formatted content
 *
 * @param string $articlecontent the source string
 * @param int $shorten new size
 * @param string $shortenindicator
 * @param bool $forceindicator set to true to include the indicator no matter what
 * @return string
 */
function shortenContent($articlecontent, $shorten, $shortenindicator, $forceindicator = false) {
	global $_user_tags;
	if ($shorten && ($forceindicator || (mb_strlen($articlecontent) > $shorten))) {
		$allowed_tags = getAllowedTags('allowed_tags');
		$short = mb_substr($articlecontent, 0, $shorten);
		$short2 = kses($short . '</p>', $allowed_tags);
		if (($l2 = mb_strlen($short2)) < $shorten) {
			$c = 0;
			$l1 = $shorten;
			$delta = $shorten - $l2;
			while ($l2 < $shorten && $c++ < 5) {
				$open = mb_strrpos($short, '<');
				if ($open > mb_strrpos($short, '>')) {
					$l1 = mb_strpos($articlecontent, '>', $l1 + 1) + $delta;
				} else {
					$l1 = $l1 + $delta;
				}
				$short = mb_substr($articlecontent, 0, $l1);
				preg_match_all('/(<p>)/', $short, $open);
				preg_match_all('/(<\/p>)/', $short, $close);
				if (count($open) > count($close))
					$short .= '</p>';
				$short2 = kses($short, $allowed_tags);
				$l2 = mb_strlen($short2);
			}
			$shorten = $l1;
		}
		$short = truncate_string($articlecontent, $shorten, '');
		if ($short != $articlecontent) { //	we actually did remove some stuff
			// drop open tag strings
			$open = mb_strrpos($short, '<');
			if ($open > mb_strrpos($short, '>')) {
				$short = mb_substr($short, 0, $open);
			}
			if (class_exists('tidy')) {
				$tidy = new tidy();
				$tidy->parseString($short . $shortenindicator, array('show-body-only' => true), 'utf8');
				$tidy->cleanRepair();
				$short = trim($tidy);
			} else {
				$short = trim(cleanHTML($short . $shortenindicator));
			}
		}
		return $short;
	}
	return $articlecontent;
}

/**
 * Returns the oldest ancestor of an alubm;
 *
 * @param string $album an album object
 * @return object
 */
function getUrAlbum($album) {
	if (!is_object($album))
		return NULL;
	while (true) {
		$parent = $album->getParent();
		if (is_null($parent)) {
			return $album;
		}
		$album = $parent;
	}
}

/**
 * Returns a sort field part for querying
 * Note: $sorttype may be a comma separated list of field names. If so,
 *       these are peckmarked and returned otherwise unchanged.
 *
 * @param string $sorttype the 'Display" name of the sort
 * @param string $default the default if $sorttype is empty
 * @param string $table the database table being used.
 * @return string
 */
function lookupSortKey($sorttype, $default, $table) {
	global $_zp_fieldLists;
	switch (strtolower($sorttype)) {
		case 'random':
			return 'RAND()';
		case "manual":
			return '`sort_order`';
		case "filename":
			switch ($table) {
				case 'images':
					return '`filename`';
				case 'albums':
					return '`folder`';
			}
		default:
			if (empty($sorttype)) {
				return '`' . $default . '`';
			}
			if (substr($sorttype, 0) == '(') {
				return $sorttype;
			}
			if (is_array($_zp_fieldLists) && isset($_zp_fieldLists[$table])) {
				$dbfields = $_zp_fieldLists[$table];
			} else {
				$result = db_list_fields($table);
				$dbfields = array();
				if ($result) {
					foreach ($result as $row) {
						$dbfields[strtolower($row['Field'])] = $row['Field'];
					}
				}
				$_zp_fieldLists[$table] = $dbfields;
			}
			$sorttype = strtolower($sorttype);
			$list = explode(',', $sorttype);
			foreach ($list as $key => $field) {
				if (array_key_exists($field, $dbfields)) {
					$list[$key] = '`' . trim($dbfields[$field]) . '`';
				}
			}
			return implode(',', $list);
	}
}

/**
 * Returns a formated date for output
 *
 * @param string $format the "strftime" format string
 * @param date $dt the date to be output
 * @return string
 */
function zpFormattedDate($format, $dt) {
	global $_zp_UTF8;
	$fdate = strftime($format, $dt);
	$charset = 'ISO-8859-1';
	$outputset = LOCAL_CHARSET;
	if (function_exists('mb_internal_encoding')) {
		if (($charset = mb_internal_encoding()) == $outputset) {
			return $fdate;
		}
	}
	return $_zp_UTF8->convert($fdate, $charset, $outputset);
}

/**
 * Simple SQL timestamp formatting function.
 *
 * @param string $format formatting template
 * @param int $mytimestamp timestamp
 * @return string
 */
function myts_date($format, $mytimestamp) {
	$timezoneadjust = getOption('time_offset');

	$month = substr($mytimestamp, 4, 2);
	$day = substr($mytimestamp, 6, 2);
	$year = substr($mytimestamp, 0, 4);

	$hour = substr($mytimestamp, 8, 2);
	$min = substr($mytimestamp, 10, 2);
	$sec = substr($mytimestamp, 12, 2);

	$epoch = mktime($hour + $timezoneadjust, $min, $sec, $month, $day, $year);
	$date = zpFormattedDate($format, $epoch);
	return $date;
}

/**
 * Determines if the input is an e-mail address. Adapted from WordPress.
 * Name changed to avoid conflicts in WP integrations.
 *
 * @param string $input_email email address?
 * @return bool
 */
function is_valid_email_zp($input_email) {
	$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
	if (strstr($input_email, '@') && strstr($input_email, '.')) {
		if (preg_match($chars, $input_email)) {
			return true;
		}
	}
	return false;
}

/**
 * Send an mail to the mailing list. We also attempt to intercept any form injection
 * attacks by slime ball spammers. Returns error message if send failure.
 *
 * @param string $subject  The subject of the email.
 * @param string $message  The message contents of the email.
 * @param array $email_list a list of email addresses to send to
 * @param array $cc_addresses a list of addresses to send copies to.
 * @param array $bcc_addresses a list of addresses to send blind copies to.
 * @param string $replyTo reply-to address
 *
 * @return string
 *
 * @author Todd Papaioannou (lucky@luckyspin.org)
 * @since  1.0.0
 */
function zp_mail($subject, $message, $email_list = NULL, $cc_addresses = NULL, $bcc_addresses = NULL, $replyTo = NULL) {
	global $_zp_authority, $_zp_gallery, $_zp_UTF8;
	$result = '';
	if ($replyTo) {
		$t = $replyTo;
		if (!is_valid_email_zp($m = array_shift($t))) {
			if (empty($result)) {
				$result = gettext('Mail send failed.');
			}
			$result .= sprintf(gettext('Invalid "reply-to" mail address %s.'), $m);
		}
	}
	if (is_null($email_list)) {
		$email_list = $_zp_authority->getAdminEmail();
	} else {
		foreach ($email_list as $key => $email) {
			if (!is_valid_email_zp($email)) {
				unset($email_list[$key]);
				if (empty($result)) {
					$result = gettext('Mail send failed.');
				}
				$result .= ' ' . sprintf(gettext('Invalid "to" mail address %s.'), $email);
			}
		}
	}
	if (is_null($cc_addresses)) {
		$cc_addresses = array();
	} else {
		if (empty($email_list) && !empty($cc_addresses)) {
			if (empty($result)) {
				$result = gettext('Mail send failed.');
			}
			$result .= ' ' . gettext('"cc" list provided without "to" address list.');
			return $result;
		}
		foreach ($cc_addresses as $key => $email) {
			if (!is_valid_email_zp($email)) {
				unset($cc_addresses[$key]);
				if (empty($result)) {
					$result = gettext('Mail send failed.');
				}
				$result = ' ' . sprintf(gettext('Invalid "cc" mail address %s.'), $email);
			}
		}
	}
	if (is_null($bcc_addresses)) {
		$bcc_addresses = array();
	} else {
		foreach ($bcc_addresses as $key => $email) {
			if (!is_valid_email_zp($email)) {
				unset($bcc_addresses[$key]);
				if (empty($result)) {
					$result = gettext('Mail send failed. ');
				}
				$result = ' ' . sprintf(gettext('Invalid "bcc" mail address %s.'), $email);
			}
		}
	}
	if (count($email_list) + count($bcc_addresses) > 0) {
		if (zp_has_filter('sendmail')) {

			$from_mail = getOption('site_email');
			$from_name = get_language_string(getOption('site_email_name'));

			// Convert to UTF-8
			if (LOCAL_CHARSET != 'UTF-8') {
				$subject = $_zp_UTF8->convert($subject, LOCAL_CHARSET);
				$message = $_zp_UTF8->convert($message, LOCAL_CHARSET);
			}

			// Send the mail
			if (count($email_list) > 0) {
				$result = zp_apply_filter('sendmail', '', $email_list, $subject, $message, $from_mail, $from_name, $cc_addresses, $replyTo); // will be true if all mailers succeeded
			}
			if (count($bcc_addresses) > 0) {
				foreach ($bcc_addresses as $bcc) {
					$result = zp_apply_filter('sendmail', '', array($bcc), $subject, $message, $from_mail, $from_name, array(), $replyTo); // will be true if all mailers succeeded
				}
			}
		} else {
			$result = gettext('Mail send failed. There is no mail handler configured.');
		}
	} else {
		if (empty($result)) {
			$result = gettext('Mail send failed.');
		}
		$result .= ' ' . gettext('No "to" address list provided.');
	}
	return $result;
}

/**
 * Sorts the results of a DB search by the current locale string for $field
 *
 * @param array $dbresult the result of the DB query
 * @param string $field the field name to sort on
 * @param bool $descending the direction of the sort
 * @return array the sorted result
 */
function sortByMultilingual($dbresult, $field, $descending) {
	$temp = array();
	foreach ($dbresult as $key => $row) {
		$temp[$key] = get_language_string($row[$field]);
	}
	natcasesort($temp);
	$result = array();
	foreach ($temp as $key => $title) {
		if ($descending) {
			array_unshift($result, $dbresult[$key]);
		} else {
			$result[] = $dbresult[$key];
		}
	}
	return $result;
}

/**
 * Checks to see access is allowed to an album
 * Returns true if access is allowed.
 * There is no password dialog--you must have already had authorization via a cookie.
 *
 * @param string $album album object or name of the album
 * @param string &$hint becomes populated with the password hint.
 * @return bool
 */
function checkAlbumPassword($album, &$hint = NULL) {
	global $_zp_pre_authorization, $_zp_gallery;
	if (is_object($album)) {
		$albumname = $album->name;
	} else {
		$album = newAlbum($albumname = $album, true, true);
	}
	if (isset($_zp_pre_authorization[$albumname])) {
		return $_zp_pre_authorization[$albumname];
	}
	$hash = $album->getPassword();
	if (empty($hash)) {
		$album = $album->getParent();
		while (!is_null($album)) {
			$hash = $album->getPassword();
			$authType = "zp_album_auth_" . $album->getID();
			$saved_auth = zp_getCookie($authType);

			if (!empty($hash)) {
				if ($saved_auth == $hash) {
					$_zp_pre_authorization[$albumname] = $authType;
					return $authType;
				} else {
					$hint = $album->getPasswordHint();
					return false;
				}
			}
			$album = $album->getParent();
		}
		// revert all tlhe way to the gallery
		$hash = $_zp_gallery->getPassword();
		$authType = 'zp_gallery_auth';
		$saved_auth = zp_getCookie($authType);
		if (empty($hash)) {
			$authType = 'zp_public_access';
		} else {
			if ($saved_auth != $hash) {
				$hint = $_zp_gallery->getPasswordHint();
				return false;
			}
		}
	} else {
		$authType = "zp_album_auth_" . $album->getID();
		$saved_auth = zp_getCookie($authType);
		if ($saved_auth != $hash) {
			$hint = $album->getPasswordHint();
			return false;
		}
	}
	$_zp_pre_authorization[$albumname] = $authType;
	return $authType;
}

/**
 * Returns a consolidated list of plugins
 * The array structure is key=plugin name, value=plugin path
 *
 * @param string $pattern File system wildcard matching pattern to limit the search
 * @param string $folder subfolder within the plugin folders to search
 * @param bool $stripsuffix set to true to remove the suffix from the key name in the array
 * @return array
 */
function getPluginFiles($pattern, $folder = '', $stripsuffix = true) {
	if (!empty($folder) && substr($folder, -1) != '/')
		$folder .= '/';
	$list = array();
	$curdir = getcwd();
	$basepath = SERVERPATH . "/" . USER_PLUGIN_FOLDER . '/' . $folder;
	if (is_dir($basepath)) {
		chdir($basepath);
		$filelist = safe_glob($pattern);
		foreach ($filelist as $file) {
			$key = filesystemToInternal($file);
			if ($stripsuffix) {
				$key = stripSuffix($key);
			}
			$list[$key] = $basepath . $file;
		}
	}
	$basepath = SERVERPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/' . $folder;
	if (file_exists($basepath)) {
		chdir($basepath);
		$filelist = safe_glob($pattern);
		foreach ($filelist as $file) {
			$key = filesystemToInternal($file);
			if ($stripsuffix) {
				$key = stripSuffix($key);
			}
			$list[$key] = $basepath . $file;
		}
	}
	chdir($curdir);
	return $list;
}

/**
 * Returns the fully qualified file name of the plugin file.
 *
 * Note: order of selection is:
 * 	1-theme folder file (if $inTheme is set)
 *  2-user plugin folder file
 *  3-zp-extensions file
 * first file found is used
 *
 * @param string $plugin is the name of the plugin file, typically something.php
 * @param bool $inTheme tells where to find the plugin.
 *   true means look in the current theme
 *   false means look in the zp-core/plugins folder.
 * @param bool $webpath return a WEBPATH rather than a SERVERPATH
 *
 * @return string
 */
function getPlugin($plugin, $inTheme = false, $webpath = false) {
	$pluginFile = NULL;
	if ($inTheme === true) {
		$inTheme = getCurrentTheme();
	}
	if ($inTheme) {
		$pluginFile = '/' . THEMEFOLDER . '/' . internalToFilesystem($inTheme . '/' . $plugin);
		if (!file_exists(SERVERPATH . $pluginFile)) {
			$pluginFile = false;
		}
	}
	if (!$pluginFile) {
		$pluginFile = '/' . USER_PLUGIN_FOLDER . '/' . internalToFilesystem($plugin);
		if (!file_exists(SERVERPATH . $pluginFile)) {
			$pluginFile = '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/' . internalToFilesystem($plugin);
			if (!file_exists(SERVERPATH . $pluginFile)) {
				$pluginFile = false;
			}
		}
	}
	if ($pluginFile) {
		if ($webpath) {
			return WEBPATH . filesystemToInternal($pluginFile);
		} else {
			return SERVERPATH . $pluginFile;
		}
	}
	return false;
}

/**
 * Returns an array of the currently enabled plugins
 *
 * @return array
 */
function getEnabledPlugins() {
	global $_EnabledPlugins;
	if (is_array($_EnabledPlugins)) {
		return $_EnabledPlugins;
	}
	$_EnabledPlugins = array();
	$sortlist = getPluginFiles('*.php');
	foreach ($sortlist as $extension => $path) {
		$opt = 'zp_plugin_' . $extension;
		if ($option = getOption($opt)) {
			$_EnabledPlugins[$extension] = array('priority' => $option, 'path' => $path);
		}
	}
	$_EnabledPlugins = sortMultiArray($_EnabledPlugins, 'priority', true);
	return $_EnabledPlugins;
}

/**
 * Returns if a plugin is enabled
 * @param string $extension
 * @return bool
 */
function extensionEnabled($extension) {
	return getOption('zp_plugin_' . $extension);
}

/**
 * Enables a plugin
 * @param string $extension
 * @param int $priority
 * @param bool $persistent
 */
function enableExtension($extension, $priority, $persistent = true) {
	setOption('zp_plugin_' . $extension, $priority, $persistent);
}

/**
 * Gets an array of comments for the current admin
 *
 * @param int $number how many comments desired
 * @return array
 */
function fetchComments($number) {
	if ($number) {
		$limit = " LIMIT $number";
	} else {
		$limit = '';
	}

	$comments = array();
	if (zp_loggedin(ADMIN_RIGHTS | COMMENT_RIGHTS)) {
		if (zp_loggedin(ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) {
			$sql = "SELECT *, (date + 0) AS date FROM " . prefix('comments') . " ORDER BY id DESC$limit";
			$comments = query_full_array($sql);
		} else {
			$albumlist = getManagedAlbumList();
			$albumIDs = array();
			foreach ($albumlist as $albumname) {
				$subalbums = getAllSubAlbumIDs($albumname);
				foreach ($subalbums as $ID) {
					$albumIDs[] = $ID['id'];
				}
			}
			if (count($albumIDs) > 0) {
				$sql = "SELECT  *, (`date` + 0) AS date FROM " . prefix('comments') . " WHERE ";

				$sql .= " (`type`='albums' AND (";
				$i = 0;
				foreach ($albumIDs as $ID) {
					if ($i > 0) {
						$sql .= " OR ";
					}
					$sql .= "(" . prefix('comments') . ".ownerid=$ID)";
					$i++;
				}
				$sql .= ")) ";
				$sql .= " ORDER BY id DESC$limit";
				$albumcomments = query($sql);
				if ($albumcomments) {
					while ($comment = db_fetch_assoc($albumcomments)) {
						$comments[$comment['id']] = $comment;
					}
					db_free_result($albumcomments);
				}
				$sql = "SELECT *, " . prefix('comments') . ".id as id, " .
								prefix('comments') . ".name as name, (" . prefix('comments') . ".date + 0) AS date, " .
								prefix('images') . ".`albumid` as albumid," .
								prefix('images') . ".`id` as imageid" .
								" FROM " . prefix('comments') . "," . prefix('images') . " WHERE ";

				$sql .= "(`type` IN (" . zp_image_types("'") . ") AND (";
				$i = 0;
				foreach ($albumIDs as $ID) {
					if ($i > 0) {
						$sql .= " OR ";
					}
					$sql .= "(" . prefix('comments') . ".ownerid=" . prefix('images') . ".id AND " . prefix('images') . ".albumid=$ID)";
					$i++;
				}
				$sql .= "))";
				$sql .= " ORDER BY " . prefix('images') . ".`id` DESC$limit";
				$imagecomments = query($sql);
				if ($imagecomments) {
					while ($comment = db_fetch_assoc($imagecomments)) {
						$comments[$comment['id']] = $comment;
					}
					db_free_result($imagecomments);
				}
				krsort($comments);
				if ($number) {
					if ($number < count($comments)) {
						$comments = array_slice($comments, 0, $number);
					}
				}
			}
		}
	}
	return $comments;
}

/**
 * Populates and returns the $_zp_admin_album_list array
 * @return array
 */
function getManagedAlbumList() {
	global $_zp_admin_album_list, $_zp_current_admin_obj;
	$_zp_admin_album_list = array();
	if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
		$sql = "SELECT `folder` FROM " . prefix('albums') . ' WHERE `parentid` IS NULL';
		$albums = query($sql);
		if ($albums) {
			while ($album = db_fetch_assoc($albums)) {
				$_zp_admin_album_list[$album['folder']] = 32767;
			}
			db_free_result($albums);
		}
	} else {
		if ($_zp_current_admin_obj) {
			$_zp_admin_album_list = array();
			$objects = $_zp_current_admin_obj->getObjects();
			foreach ($objects as $object) {
				if ($object['type'] == 'album') {
					$_zp_admin_album_list[$object['data']] = $object['edit'];
				}
			}
		}
	}
	return array_keys($_zp_admin_album_list);
}

/**
 * Returns a list of album names managed by $id
 *
 * @param string $type which kind of object
 * @param int $id admin ID
 * @param bool $rights set true for album sub-rights
 * @return array
 */
function populateManagedObjectsList($type, $id, $rights = false) {
	if ($id <= 0) {
		return array();
	}
	$cv = array();
	if (empty($type) || substr($type, 0, 5) == 'album') {
		$sql = "SELECT " . prefix('albums') . ".`folder`," . prefix('albums') . ".`title`," . prefix('admin_to_object') . ".`edit` FROM " . prefix('albums') . ", " .
						prefix('admin_to_object') . " WHERE " . prefix('admin_to_object') . ".adminid=" . $id .
						" AND " . prefix('albums') . ".id=" . prefix('admin_to_object') . ".objectid AND " . prefix('admin_to_object') . ".type LIKE 'album%'";
		$currentvalues = query($sql, false);
		if ($currentvalues) {
			while ($albumitem = db_fetch_assoc($currentvalues)) {
				$folder = $albumitem['folder'];
				$name = get_language_string($albumitem['title']);
				if ($type && !$rights) {
					$cv[$name] = $folder;
				} else {
					$cv[] = array('data' => $folder, 'name' => $name, 'type' => 'album', 'edit' => $albumitem['edit'] + 0);
				}
			}
			db_free_result($currentvalues);
		}
	}
	if (empty($type) || $type == 'pages') {
		$sql = 'SELECT ' . prefix('pages') . '.`title`,' . prefix('pages') . '.`titlelink` FROM ' . prefix('pages') . ', ' .
						prefix('admin_to_object') . " WHERE " . prefix('admin_to_object') . ".adminid=" . $id .
						" AND " . prefix('pages') . ".id=" . prefix('admin_to_object') . ".objectid AND " . prefix('admin_to_object') . ".type='pages'";
		$currentvalues = query($sql, false);
		if ($currentvalues) {
			while ($item = db_fetch_assoc($currentvalues)) {
				if ($type) {
					$cv[get_language_string($item['title'])] = $item['titlelink'];
				} else {
					$cv[] = array('data' => $item['titlelink'], 'name' => $item['title'], 'type' => 'pages');
				}
			}
			db_free_result($currentvalues);
		}
	}
	if (empty($type) || $type == 'news') {
		$sql = 'SELECT ' . prefix('news_categories') . '.`titlelink`,' . prefix('news_categories') . '.`title` FROM ' . prefix('news_categories') . ', ' .
						prefix('admin_to_object') . " WHERE " . prefix('admin_to_object') . ".adminid=" . $id .
						" AND " . prefix('news_categories') . ".id=" . prefix('admin_to_object') . ".objectid AND " . prefix('admin_to_object') . ".type='news'";
		$currentvalues = query($sql, false);
		if ($currentvalues) {
			while ($item = db_fetch_assoc($currentvalues)) {
				if ($type) {
					$cv[get_language_string($item['title'])] = $item['titlelink'];
				} else {
					$cv[] = array('data' => $item['titlelink'], 'name' => $item['title'], 'type' => 'news');
				}
			}
			db_free_result($currentvalues);
		}
	}
	return $cv;
}

/**
 * Returns  an array of album ids whose parent is the folder
 * @param string $albumfolder folder name if you want a album different >>from the current album
 * @return array
 */
function getAllSubAlbumIDs($albumfolder = '') {
	global $_zp_current_album;
	if (empty($albumfolder)) {
		if (isset($_zp_current_album)) {
			$albumfolder = $_zp_current_album->getFolder();
		} else {
			return null;
		}
	}
	$query = "SELECT `id`,`folder`, `show` FROM " . prefix('albums') . " WHERE `folder` LIKE " . db_quote(db_LIKE_escape($albumfolder) . '%');
	$subIDs = query_full_array($query);
	return $subIDs;
}

/**
 * recovers search parameters from stored cookie, clears the cookie
 *
 * @param string $what the page type
 * @param string $album Name of the album
 * @param string $image Name of the image
 */
function handleSearchParms($what, $album = NULL, $image = NULL) {
	global $_zp_current_search, $zp_request, $_zp_last_album, $_zp_current_album,
	$_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_gallery, $_zp_loggedin;
	$_zp_last_album = zp_getCookie('zenphoto_last_album');
	if (is_object($zp_request) && get_class($zp_request) == 'SearchEngine') { //	we are are on a search
		return $zp_request->getAlbumList();
	}
	$params = zp_getCookie('zenphoto_search_params');
	if (!empty($params)) {
		$context = get_context();
		$_zp_current_search = new SearchEngine();
		$_zp_current_search->setSearchParams($params);
		// check to see if we are still "in the search context"
		if (!is_null($image)) {
			$dynamic_album = $_zp_current_search->getDynamicAlbum();
			if ($_zp_current_search->getImageIndex($album->name, $image->filename) !== false) {
				if ($dynamic_album) {
					$_zp_current_album = $dynamic_album;
				}
				$context = $context | ZP_SEARCH_LINKED | ZP_IMAGE_LINKED;
			}
		}
		if (!is_null($album)) {
			$albumname = $album->name;
			zp_setCookie('zenphoto_last_album', $albumname);
			if (hasDynamicAlbumSuffix($albumname)) {
				$albumname = stripSuffix($albumname); // strip off the .alb as it will not be reflected in the search path
			}
			//	see if the album is within the search context. NB for these purposes we need to look at all albums!
			$save_logon = $_zp_loggedin;
			$_zp_loggedin = $_zp_loggedin | VIEW_ALL_RIGHTS;
			$search_album_list = $_zp_current_search->getAlbums(0);
			$_zp_loggedin = $save_logon;
			foreach ($search_album_list as $searchalbum) {
				if (strpos($albumname, $searchalbum) !== false) {
					$context = $context | ZP_SEARCH_LINKED | ZP_ALBUM_LINKED;
					break;
				}
			}
		} else {
			zp_clearCookie('zenphoto_last_album');
		}
		if (!is_null($_zp_current_zenpage_page)) {
			$pages = $_zp_current_search->getPages();
			if (!empty($pages)) {
				$tltlelink = $_zp_current_zenpage_page->getTitlelink();
				foreach ($pages as $apage) {
					if ($apage == $tltlelink) {
						$context = $context | ZP_SEARCH_LINKED;
						break;
					}
				}
			}
		}
		if (!is_null($_zp_current_zenpage_news)) {
			$news = $_zp_current_search->getArticles(0, NULL, true);
			if (!empty($news)) {
				$tltlelink = $_zp_current_zenpage_news->getTitlelink();
				foreach ($news as $anews) {
					if ($anews['titlelink'] == $tltlelink) {
						$context = $context | ZP_SEARCH_LINKED;
						break;
					}
				}
			}
		}
		if (($context & ZP_SEARCH_LINKED)) {
			set_context($context);
		} else { // not an object in the current search path
			$_zp_current_search = null;
			rem_context(ZP_SEARCH);
			if (!isset($_REQUEST['preserve_serch_params'])) {
				zp_clearCookie("zenphoto_search_params");
			}
		}
	}
}

/**
 *
 * checks if the item has expired
 * @param array $row database row of the object
 */
function checkPublishDates($row) {
	if ($row['show']) {
		if (isset($row['expiredate']) && $row['expiredate'] && $row['expiredate'] != '0000-00-00 00:00:00') {
			if ($row['expiredate'] <= date('Y-m-d H:i:s')) {
				return 1;
			}
		}
		if (isset($row['publishdate']) && $row['publishdate'] && $row['publishdate'] != '0000-00-00 00:00:00') {
			if ($row['publishdate'] >= date('Y-m-d H:i:s')) {
				return 2;
			}
		}
		return null;
	}
}

/**
 * Returns the number of album thumbs that go on a gallery page
 *
 * @return int
 */
function galleryAlbumsPerPage() {
	return max(1, getOption('albums_per_page'));
}

/**
 * Returns the theme folder
 * If there is an album theme, loads the theme options.
 *
 * @param object $album album object if override desired
 *
 * @return string
 */
function setupTheme($album = NULL) {
	global $_zp_gallery, $_zp_current_album, $_zp_current_search, $_zp_themeroot;
	$albumtheme = '';
	if (is_null($album)) {
		if (in_context(ZP_SEARCH_LINKED)) {
			if (!$album = $_zp_current_search->getDynamicAlbum()) {
				$album = $_zp_current_album;
			}
		} else {
			$album = $_zp_current_album;
		}
	}
	$theme = $_zp_gallery->getCurrentTheme();
	$id = 0;
	if (!is_null($album)) {
		$parent = getUrAlbum($album);
		$albumtheme = $parent->getAlbumTheme();
		if (!empty($albumtheme)) {
			$theme = $albumtheme;
			$id = $parent->getID();
		}
	}
	$theme = zp_apply_filter('setupTheme', $theme);
	$_zp_gallery->setCurrentTheme($theme);
	$themeindex = getPlugin('index.php', $theme);
	if (empty($theme) || empty($themeindex)) {
		header('Last-Modified: ' . ZP_LAST_MODIFIED);
		header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
		?>
		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			</head>
			<body>
				<strong><?php printf(gettext('Zenphoto found no theme scripts. Please check the <em>%s</em> folder of your installation.'), THEMEFOLDER); ?></strong>
			</body>
		</html>
		<?php
		exitZP();
	} else {
		loadLocalOptions($id, $theme);
		$_zp_themeroot = WEBPATH . "/" . THEMEFOLDER . "/$theme";
	}
	return $theme;
}

/**
 * Registers a plugin as handler for a file extension
 *
 * @param string $suffix the file extension
 * @param string $objectName the name of the object that handles this extension
 */
function addPluginType($suffix, $objectName) {
	global $_zp_extra_filetypes;
	$_zp_extra_filetypes[strtolower($suffix)] = $objectName;
}

/**
 * Returns an array of unique tag names
 *
 * @return array
 */
function getAllTagsUnique() {
	global $_zp_unique_tags;
	if (!is_null($_zp_unique_tags))
		return $_zp_unique_tags; // cache them.
	$_zp_unique_tags = array();
	$sql = "SELECT DISTINCT `name` FROM " . prefix('tags') . ' ORDER BY `name`';
	$unique_tags = query($sql);
	if ($unique_tags) {
		while ($tagrow = db_fetch_assoc($unique_tags)) {
			$_zp_unique_tags[] = $tagrow['name'];
		}
		db_free_result($unique_tags);
	}
	return $_zp_unique_tags;
}

/**
 * Returns an array indexed by 'tag' with the element value the count of the tag
 *
 * @return array
 */
function getAllTagsCount() {
	global $_zp_count_tags;
	if (!is_null($_zp_count_tags))
		return $_zp_count_tags;
	$_zp_count_tags = array();
	$sql = "SELECT DISTINCT tags.name, tags.id, (SELECT COUNT(*) FROM " . prefix('obj_to_tag') . " as object WHERE object.tagid = tags.id) AS count FROM " . prefix('tags') . " as tags ORDER BY `name`";
	$tagresult = query($sql);
	if ($tagresult) {
		while ($tag = db_fetch_assoc($tagresult)) {
			$_zp_count_tags[$tag['name']] = $tag['count'];
		}
		db_free_result($tagresult);
	}
	return $_zp_count_tags;
}

/**
 * Stores tags for an object
 *
 * @param array $tags the tag values
 * @param int $id the record id of the album/image
 * @param string $tbl database table of the object
 */
function storeTags($tags, $id, $tbl) {
	if ($id) {
		$tagsLC = array();
		foreach ($tags as $key => $tag) {
			$tag = trim($tag);
			if (!empty($tag)) {
				$lc_tag = mb_strtolower($tag);
				if (!in_array($lc_tag, $tagsLC)) {
					$tagsLC[$tag] = $lc_tag;
				}
			}
		}
		$sql = "SELECT `id`, `tagid` from " . prefix('obj_to_tag') . " WHERE `objectid`='" . $id . "' AND `type`='" . $tbl . "'";
		$result = query($sql);
		$existing = array();
		if ($result) {
			while ($row = db_fetch_assoc($result)) {
				$dbtag = query_single_row("SELECT `name` FROM " . prefix('tags') . " WHERE `id`='" . $row['tagid'] . "'");
				$existingLC = mb_strtolower($dbtag['name']);
				if (in_array($existingLC, $tagsLC)) { // tag already set no action needed
					$existing[] = $existingLC;
				} else { // tag no longer set, remove it
					query("DELETE FROM " . prefix('obj_to_tag') . " WHERE `id`='" . $row['id'] . "'");
				}
			}
			db_free_result($result);
		}
		$tags = array_diff($tagsLC, $existing); // new tags for the object
		foreach ($tags as $key => $tag) {
			$dbtag = query_single_row("SELECT `id` FROM " . prefix('tags') . " WHERE `name`=" . db_quote($key));
			if (!is_array($dbtag)) { // tag does not exist
				query("INSERT INTO " . prefix('tags') . " (name) VALUES (" . db_quote($key) . ")", false);
				$dbtag = array('id' => db_insert_id());
			}
			query("INSERT INTO " . prefix('obj_to_tag') . "(`objectid`, `tagid`, `type`) VALUES (" . $id . "," . $dbtag['id'] . ",'" . $tbl . "')");
		}
	}
}

/**
 * Retrieves the tags for an object
 * Returns them in an array
 *
 * @param int $id the record id of the album/image
 * @param string $tbl 'albums' or 'images', etc.
 * @return unknown
 */
function readTags($id, $tbl) {
	$tags = array();
	$result = query("SELECT `tagid` FROM " . prefix('obj_to_tag') . " WHERE `type`='" . $tbl . "' AND `objectid`='" . $id . "'");
	if ($result) {
		while ($row = db_fetch_assoc($result)) {
			$dbtag = query_single_row("SELECT `name` FROM" . prefix('tags') . " WHERE `id`='" . $row['tagid'] . "'");
			if ($dbtag) {
				$tags[] = $dbtag['name'];
			}
		}
		db_free_result($result);
	}
	natcasesort($tags);
	return $tags;
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
	foreach ($list as $key => $item) {
		echo '<option value="' . html_encode($item) . '"';
		if (in_array($item, $currentValue)) {
			echo ' selected="selected"';
		}
		if ($localize)
			$display = $key;
		else
			$display = $item;
		echo '>' . $display . "</option>" . "\n";
	}
}

/**
 * Generates a selection list from files found on disk
 *
 * @param strig $currentValue the current value of the selector
 * @param string $root directory path to search
 * @param string $suffix suffix to select for
 * @param bool $descending set true to get a reverse order sort
 */
function generateListFromFiles($currentValue, $root, $suffix, $descending = false) {
	if (is_dir($root)) {
		$curdir = getcwd();
		chdir($root);
		$filelist = safe_glob('*' . $suffix);
		$list = array();
		foreach ($filelist as $file) {
			$file = str_replace($suffix, '', $file);
			$list[] = filesystemToInternal($file);
		}
		generateListFromArray(array($currentValue), $list, $descending, false);
		chdir($curdir);
	}
}

/**
 * @param string $url The link URL
 * @param string $text The text to go with the link
 * @param string $title Text for the title tag
 * @param string $class optional class
 * @param string $id optional id
 */
function getLink($url, $text, $title = NULL, $class = NULL, $id = NULL) {
	return "<a href=\"" . html_encode($url) . "\"" .
					(($title) ? " title=\"" . html_encode(strip_tags($title)) . "\"" : "") .
					(($class) ? " class=\"$class\"" : "") .
					(($id) ? " id=\"$id\"" : "") . ">" .
					html_encode($text) . "</a>";
}

/**
 * General link printing function
 * @param string $url The link URL
 * @param string $text The text to go with the link
 * @param string $title Text for the title tag
 * @param string $class optional class
 * @param string $id optional id
 */
function printLink($url, $text, $title = NULL, $class = NULL, $id = NULL) {
	echo getlink($url, $text, $title, $class, $id);
}

/**
 * shuffles an array maintaining the keys
 *
 * @param array $array
 * @return boolean
 */
function shuffle_assoc(&$array) {
	$keys = array_keys($array);
	shuffle($keys);
	foreach ($keys as $key) {
		$new[$key] = $array[$key];
	}
	$array = $new;
	return true;
}

/**
 * sorts the found albums (images) by the required key(s)
 *
 * NB: this sort is sensitive to the key(s) chosen and makes
 * the appropriate sorts based on same. Some multi-key sorts
 * will not make any sense and will give unexpected results.
 * Most notably any that contain the keys "title" or "desc"
 * as these require multi-lingual sorts.
 *
 * @param array $results
 * @param string $sortkey
 * @param string $order
 */
function sortByKey($results, $sortkey, $order) {
	$sortkey = str_replace('`', '', $sortkey);
	switch ($sortkey) {
		case 'title':
		case 'desc':
			return sortByMultilingual($results, $sortkey, $order);
		case 'RAND()':
			shuffle($results);
			return $results;
		default:
			if (preg_match('`[\/\(\)\*\+\-!\^\%\<\>\=\&\|]`', $sortkey)) {
				return $results; //	We cannot deal with expressions
			}
	}
	$indicies = explode(',', $sortkey);
	foreach ($indicies as $key => $index) {
		$indicies[$key] = trim($index);
	}
	$results = sortMultiArray($results, $indicies, $order);
	return $results;
}

/**
 * multidimensional array column sort
 *
 * @param array $array The multidimensional array to be sorted
 * @param mixed $index Which key(s) should be sorted by
 * @param string $order true for descending sorts
 * @param bool $natsort If natural order should be used
 * @param bool $case_sensitive If the sort should be case sensitive
 * @return array
 *
 * @author redoc (http://codingforums.com/showthread.php?t=71904)
 */
function sortMultiArray($array, $index, $descending = false, $natsort = true, $case_sensitive = false, $preservekeys = false, $remove_criteria = array()) {
	if (is_array($array) && count($array) > 0) {
		if (is_array($index)) {
			$indicies = $index;
		} else {
			$indicies = array($index);
		}
		if ($descending) {
			$separator = '~~';
		} else {
			$separator = '  ';
		}
		foreach ($array as $key => $row) {
			$temp[$key] = '';
			foreach ($indicies as $index) {
				if (is_array($row) && array_key_exists($index, $row)) {
					$temp[$key] .= $row[$index] . $separator;
					if (in_array($index, $remove_criteria)) {
						unset($array[$key][$index]);
					}
				}
			}
			$temp[$key] .= $key;
		}
		if ($natsort) {
			if ($case_sensitive) {
				natsort($temp);
			} else {
				natcasesort($temp);
			}
			if ($descending) {
				$temp = array_reverse($temp, TRUE);
			}
		} else {
			if ($descending) {
				arsort($temp);
			} else {
				asort($temp);
			}
		}

		foreach (array_keys($temp) as $key) {
			if (!$preservekeys && is_numeric($key)) {
				$sorted[] = $array[$key];
			} else {
				$sorted[$key] = $array[$key];
			}
		}
		return $sorted;
	}
	return $array;
}

/**
 * Returns a list of album IDs that the current viewer is not allowed to see
 *
 * @return array
 */
function getNotViewableAlbums() {
	global $_zp_not_viewable_album_list, $_zp_gallery;
	if (zp_loggedin(ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS))
		return array(); //admins can see all
	$hint = '';
	if (is_null($_zp_not_viewable_album_list)) {
		$sql = 'SELECT `folder`, `id`, `password`, `show` FROM ' . prefix('albums') . ' WHERE `show`=0 OR `password`!=""';
		$result = query($sql);
		if ($result) {
			$_zp_not_viewable_album_list = array();
			while ($row = db_fetch_assoc($result)) {
				if (checkAlbumPassword($row['folder'])) {
					$album = newAlbum($row['folder']);
					if (!($row['show'] || $album->isMyItem(LIST_RIGHTS))) {
						$_zp_not_viewable_album_list[] = $row['id'];
					}
				} else {
					$_zp_not_viewable_album_list[] = $row['id'];
				}
			}
			db_free_result($result);
		}
	}
	return $_zp_not_viewable_album_list;
}

/**
 * Checks to see if a URL is valid
 *
 * @param string $url the URL being checked
 * @return bool
 */
function isValidURL($url) {
	return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

/**
 * pattern match function Works with characters with diacritical marks where the PHP one does not.
 *
 * @param string $pattern pattern
 * @param string $string haystack
 * @return bool
 */
function safe_fnmatch($pattern, $string) {
	return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
}

/**
 * returns a list of comment record 'types' for "images"
 * @param string $quote quotation mark to use
 *
 * @return string
 */
function zp_image_types($quote) {
	global $_zp_extra_filetypes;
	$typelist = $quote . 'images' . $quote . ',' . $quote . '_images' . $quote . ',';
	$types = array_unique($_zp_extra_filetypes);
	foreach ($types as $type) {
		$typelist .= $quote . strtolower($type) . 's' . $quote . ',';
	}
	return substr($typelist, 0, -1);
}

/**

 * Returns video argument of the current Image.
 *
 * @param object $image optional image object
 * @return bool
 */
function isImageVideo($image = NULL) {
	if (is_null($image)) {
		if (!in_context(ZP_IMAGE))
			return false;
		global $_zp_current_image;
		$image = $_zp_current_image;
	}
	return strtolower(get_class($image)) == 'video';
}

/**
 * Returns true if the image is a standard photo type
 *
 * @param object $image optional image object
 * @return bool
 */
function isImagePhoto($image = NULL) {
	if (is_null($image)) {
		if (!in_context(ZP_IMAGE))
			return false;
		global $_zp_current_image;
		$image = $_zp_current_image;
	}
	$class = strtolower(get_class($image));
	return $class == 'image' || $class == 'transientimage';
}

/**
 * Copies a directory recursively
 * @param string $srcdir the source directory.
 * @param string $dstdir the destination directory.
 * @return the total number of files copied.
 */
function dircopy($srcdir, $dstdir) {
	$num = 0;
	if (!is_dir($dstdir))
		mkdir($dstdir);
	if ($curdir = opendir($srcdir)) {
		while ($file = readdir($curdir)) {
			if ($file != '.' && $file != '..') {
				$srcfile = $srcdir . '/' . $file;
				$dstfile = $dstdir . '/' . $file;
				if (is_file($srcfile)) {
					if (is_file($dstfile))
						$ow = filemtime($srcfile) - filemtime($dstfile);
					else
						$ow = 1;
					if ($ow > 0) {
						if (copy($srcfile, $dstfile)) {
							touch($dstfile, filemtime($srcfile));
							$num++;
						}
					}
				} else if (is_dir($srcfile)) {
					$num += dircopy($srcfile, $dstfile);
				}
			}
		}
		closedir($curdir);
	}
	return $num;
}

/**
 * Returns a byte size from a size value (eg: 100M).
 *
 * @param int $bytes
 * @return string
 */
function byteConvert($bytes) {
	if ($bytes <= 0)
		return gettext('0 Bytes');
	$convention = 1024; //[1000->10^x|1024->2^x]
	$s = array('Bytes', 'kB', 'mB', 'GB', 'TB', 'PB', 'EB', 'ZB');
	$e = floor(log($bytes, $convention));
	return round($bytes / pow($convention, $e), 2) . ' ' . $s[$e];
}

/**
 * Converts a datetime to connoical form
 *
 * @param string $datetime input date/time string
 * @param bool $raw set to true to return the timestamp otherwise you get a string
 * @return mixed
 */
function dateTimeConvert($datetime, $raw = false) {
	// Convert 'yyyy:mm:dd hh:mm:ss' to 'yyyy-mm-dd hh:mm:ss' for Windows' strtotime compatibility
	$datetime = preg_replace('/(\d{4}):(\d{2}):(\d{2})/', ' \1-\2-\3', $datetime);
	$time = strtotime($datetime);
	if ($time == -1 || $time === false)
		return false;
	if ($raw)
		return $time;
	return date('Y-m-d H:i:s', $time);
}

/* * * Context Manipulation Functions ****** */
/* * *************************************** */

/* Contexts are simply constants that tell us what variables are available to us
 * at any given time. They should be set and unset with those variables.
 */

function get_context() {
	global $_zp_current_context;
	return $_zp_current_context;
}

function set_context($context) {
	global $_zp_current_context;
	$_zp_current_context = $context;
}

function in_context($context) {
	return get_context() & $context;
}

function add_context($context) {
	set_context(get_context() | $context);
}

function rem_context($context) {
	global $_zp_current_context;
	set_context(get_context() & ~$context);
}

// Use save and restore rather than add/remove when modifying contexts.
function save_context() {
	global $_zp_current_context, $_zp_current_context_stack;
	array_push($_zp_current_context_stack, $_zp_current_context);
}

function restore_context() {
	global $_zp_current_context, $_zp_current_context_stack;
	$_zp_current_context = array_pop($_zp_current_context_stack);
}

/**
 *
 * Sanitizes a "redirect" post
 * @param string $redirectTo
 * @return string
 */
function sanitizeRedirect($redirectTo, $forceHost = false) {
	$redirect = NULL;
	if ($redirectTo && $redir = parse_url($redirectTo)) {
		if (isset($redir['scheme']) && isset($redir['host'])) {
			$redirect .= $redir['scheme'] . '://' . sanitize($redir['host']);
		} else {
			if ($forceHost) {
				$redirect .= SERVER_PROTOCOL . '://' . $_SERVER['HTTP_HOST'];
				if (WEBPATH && strpos($redirectTo, WEBPATH) === false) {
					$redirect .= WEBPATH;
				}
			}
		}
		if (isset($redir['path'])) {
			$redirect .= urldecode(sanitize($redir['path']));
		}
		if (isset($redir['query'])) {
			$redirect .= '?' . sanitize($redir['query']);
		}
		if (isset($redir['fragment'])) {
			$redirect .= '#' . sanitize($redir['fragment']);
		}
	}
	return $redirect;
}

/**
 * checks password posting
 *
 * @param string $authType override of athorization type
 */
function zp_handle_password($authType = NULL, $check_auth = NULL, $check_user = NULL) {
	global $_zp_loggedin, $_zp_login_error, $_zp_current_album, $_zp_current_zenpage_page, $_zp_gallery;
	if (empty($authType)) { // not supplied by caller
		$check_auth = '';
		if (isset($_GET['z']) && $_GET['p'] == 'full-image' || isset($_GET['p']) && $_GET['p'] == '*full-image') {
			$authType = 'zp_image_auth';
			$check_auth = getOption('protected_image_password');
			$check_user = getOption('protected_image_user');
		} else if (in_context(ZP_SEARCH)) { // search page
			$authType = 'zp_search_auth';
			$check_auth = getOption('search_password');
			$check_user = getOption('search_user');
		} else if (in_context(ZP_ALBUM)) { // album page
			$authType = "zp_album_auth_" . $_zp_current_album->getID();
			$check_auth = $_zp_current_album->getPassword();
			$check_user = $_zp_current_album->getUser();
			if (empty($check_auth)) {
				$parent = $_zp_current_album->getParent();
				while (!is_null($parent)) {
					$check_auth = $parent->getPassword();
					$check_user = $parent->getUser();
					$authType = "zp_album_auth_" . $parent->getID();
					if (!empty($check_auth)) {
						break;
					}
					$parent = $parent->getParent();
				}
			}
		} else if (in_context(ZP_ZENPAGE_PAGE)) {
			$authType = "zp_page_auth_" . $_zp_current_zenpage_page->getID();
			$check_auth = $_zp_current_zenpage_page->getPassword();
			$check_user = $_zp_current_zenpage_page->getUser();
			if (empty($check_auth)) {
				$pageobj = $_zp_current_zenpage_page;
				while (empty($check_auth)) {
					$parentID = $pageobj->getParentID();
					if ($parentID == 0)
						break;
					$sql = 'SELECT `titlelink` FROM ' . prefix('pages') . ' WHERE `id`=' . $parentID;
					$result = query_single_row($sql);
					$pageobj = new ZenpagePage($result['titlelink']);
					$authType = "zp_page_auth_" . $pageobj->getID();
					$check_auth = $pageobj->getPassword();
					$check_user = $pageobj->getUser();
				}
			}
		}
		if (empty($check_auth)) { // anything else is controlled by the gallery credentials
			$authType = 'zp_gallery_auth';
			$check_auth = $_zp_gallery->getPassword();
			$check_user = $_zp_gallery->getUser();
		}
	}
	// Handle the login form.
	if (DEBUG_LOGIN)
		debugLog("zp_handle_password: \$authType=$authType; \$check_auth=$check_auth; \$check_user=$check_user; ");
	if (isset($_POST['password']) && isset($_POST['pass'])) { // process login form
		if (isset($_POST['user'])) {
			$post_user = sanitize($_POST['user']);
		} else {
			$post_user = '';
		}
		$post_pass = sanitize($_POST['pass']);

		foreach (Zenphoto_Authority::$hashList as $hash => $hi) {
			$auth = Zenphoto_Authority::passwordHash($post_user, $post_pass, $hi);
			$success = ($auth == $check_auth) && $post_user == $check_user;
			if (DEBUG_LOGIN)
				debugLog("zp_handle_password($success): \$post_user=$post_user; \$post_pass=$post_pass; \$check_auth=$check_auth; \$auth=$auth; \$hash=$hash;");
			if ($success) {
				break;
			}
		}
		$success = zp_apply_filter('guest_login_attempt', $success, $post_user, $post_pass, $authType);
		if ($success) {
			// Correct auth info. Set the cookie.
			if (DEBUG_LOGIN)
				debugLog("zp_handle_password: valid credentials");
			zp_setCookie($authType, $auth);
			if (isset($_POST['redirect'])) {
				$redirect_to = sanitizeRedirect($_POST['redirect'], true);
				if (!empty($redirect_to)) {
					header("Location: " . $redirect_to);
					exitZP();
				}
			}
		} else {
			// Clear the cookie, just in case
			if (DEBUG_LOGIN)
				debugLog("zp_handle_password: invalid credentials");
			zp_clearCookie($authType);
			$_zp_login_error = true;
		}
		return;
	}
	if (empty($check_auth)) { //no password on record or admin logged in
		return;
	}
	if (($saved_auth = zp_getCookie($authType)) != '') {
		if ($saved_auth == $check_auth) {
			if (DEBUG_LOGIN)
				debugLog("zp_handle_password: valid cookie");
			return;
		} else {
			// Clear the cookie
			if (DEBUG_LOGIN)
				debugLog("zp_handle_password: invalid cookie");
			zp_clearCookie($authType);
		}
	}
}

/**
 *
 * Gets an option directly from the database.
 * @param string $key
 */
function getOptionFromDB($key) {
	$sql = "SELECT `value` FROM " . prefix('options') . " WHERE `name`=" . db_quote($key) . " AND `ownerid`=0";
	$optionlist = query_single_row($sql, false);
	return @$optionlist['value'];
}

/**
 * Set options local to theme and/or album
 *
 * @param string $key
 * @param string $value
 * @param object $album
 * @param string $theme default theme
 * @param bool $default set to true for setting default theme options (does not set the option if it already exists)
 */
function setThemeOption($key, $value, $album, $theme, $default = false) {
	global $_zp_gallery;
	if (is_null($album)) {
		$id = 0;
	} else {
		$id = $album->getID();
		$theme = $album->getAlbumTheme();
	}
	$creator = THEMEFOLDER . '/' . $theme;

	$sql = 'INSERT INTO ' . prefix('options') . ' (`name`,`ownerid`,`theme`,`creator`,`value`) VALUES (' . db_quote($key) . ',0,' . db_quote($theme) . ',' . db_quote($creator) . ',';
	$sqlu = ' ON DUPLICATE KEY UPDATE `value`=';
	if (is_null($value)) {
		$sql .= 'NULL';
		$sqlu .= 'NULL';
	} else {
		$sql .= db_quote($value);
		$sqlu .= db_quote($value);
	}
	$sql .= ') ';
	if (!$default) {
		$sql .= $sqlu;
	}
	$result = query($sql, false);
}

/**
 * Used to set default values for theme specific options
 *
 * @param string $key
 * @param mixed $value
 */
function setThemeOptionDefault($key, $value) {
	$bt = debug_backtrace();
	$b = array_shift($bt);
	$theme = basename(dirname($b['file']));
	setThemeOption($key, $value, NULL, $theme, true);
}

/**
 * Returns the value of a theme option
 *
 * @param string $option option key
 * @param object $album
 * @param string $theme default theme name
 * @return mixed
 */
function getThemeOption($option, $album = NULL, $theme = NULL) {
	global $_set_theme_album, $_zp_gallery;
	if (is_null($album)) {
		$album = $_set_theme_album;
	}
	if (is_null($album)) {
		$id = 0;
	} else {
		$id = $album->getID();
		$theme = $album->getAlbumTheme();
	}
	if (empty($theme)) {
		$theme = $_zp_gallery->getCurrentTheme();
	}

	// album-theme
	$sql = "SELECT `value` FROM " . prefix('options') . " WHERE `name`=" . db_quote($option) . " AND `ownerid`=" . $id . " AND `theme`=" . db_quote($theme);
	$db = query_single_row($sql);
	if (!$db) {
		// raw theme option
		$sql = "SELECT `value` FROM " . prefix('options') . " WHERE `name`=" . db_quote($option) . " AND `ownerid`=0 AND `theme`=" . db_quote($theme);
		$db = query_single_row($sql);
		if (!$db) {
			// raw album option
			$sql = "SELECT `value` FROM " . prefix('options') . " WHERE `name`=" . db_quote($option) . " AND `ownerid`=" . $id . " AND `theme`=NULL";
			$db = query_single_row($sql);
			if (!$db) {
				return getOption($option);
			}
		}
	}
	return $db['value'];
}

/**
 * Returns true if all the right conditions are set to allow comments for the $type
 *
 * @param string $type Which comments
 * @return bool
 */
function commentsAllowed($type) {
	return getOption($type) && (!MEMBERS_ONLY_COMMENTS || zp_loggedin(ADMIN_RIGHTS | POST_COMMENT_RIGHTS));
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
 * Strips out and/or replaces characters from the string that are not "soe" friendly
 *
 * @param string $string
 * @return string
 */
function seoFriendly($string) {
	if (zp_has_filter('seoFriendly')) {
		$string = zp_apply_filter('seoFriendly', $string);
	} else { // no filter, do basic cleanup
		$string = preg_replace("/\s+/", "-", $string);
		$string = preg_replace("/[^a-zA-Z0-9_.-]/", "-", $string);
		$string = str_replace(array('---', '--'), '-', $string);
	}
	return $string;
}

/**
 *
 * emit the javascript seojs() function
 */
function seoFriendlyJS() {
	if (zp_has_filter('seoFriendly_js')) {
		echo zp_apply_filter('seoFriendly_js');
	} else {
		?>
		function seoFriendlyJS(fname) {
		fname = fname.replace(/\s+/g, '-');
		fname = fname.replace(/[^a-zA-Z0-9_.-]/g, '-');
		fname = fname.replace(/--*/g, '-');
		return fname;
		}
		<?php
	}
}

/**
 * Returns true if there is an internet connection
 *
 * @param string $host optional host name to test
 *
 * @return bool
 */
function is_connected($host = 'www.zenphoto.org') {
	$err_no = $err_str = false;
	$connected = @fsockopen($host, 80, $errno, $errstr, 0.5);
	if ($connected) {
		fclose($connected);
		return true;
	}
	return false;
}

/**
 * produce debugging information on 404 errors
 * @param string $album
 * @param string $image
 * @param string $theme
 */
function debug404($album, $image, $theme) {
	if (DEBUG_404) {
		$list = explode('/', $album);
		if (array_shift($list) == 'cache') {
			return;
		}
		$ignore = array('/favicon.ico', '/zp-data/tést.jpg');
		$target = getRequestURI();
		foreach ($ignore as $uri) {
			if ($target == $uri)
				return;
		}
		trigger_error(sprintf(gettext('Zenphoto processed a 404 error on %s. See the debug log for details.'), $target), E_USER_NOTICE);
		debugLog("404 error: album=$album; image=$image; theme=$theme");
		debugLogVar('$_SERVER ', $_SERVER);
		debugLogVar('$_REQUEST ', $_REQUEST);
		debugLog('');
	}
}

/**
 * returns an XSRF token
 * @param striong $action
 */
function getXSRFToken($action) {
	global $_zp_current_admin_obj;
	return sha1($action . prefix(getUserIP()) . serialize($_zp_current_admin_obj) . session_id());
}

/**
 * Emits a "hidden" input for the XSRF token
 * @param string $action
 */
function XSRFToken($action) {
	?>
	<input type="hidden" name="XSRFToken" id="XSRFToken" value="<?php echo getXSRFToken($action); ?>" />
	<?php
}

/**
 * Starts a sechedule script run
 * @param string $script The script file to load
 * @param array $params "POST" parameters
 * @param bool $inline set to true to run the task "in-line". Set false run asynchronously
 */
function cron_starter($script, $params, $offsetPath, $inline = false) {
	global $_zp_authority, $_zp_loggedin, $_zp_current_admin_obj;
	$admin = Zenphoto_Authority::getAnAdmin(array('`user`=' => $_zp_authority->master_user, '`valid`=' => 1));

	if ($inline) {
		$_zp_current_admin_obj = $admin;
		$_zp_loggedin = $_zp_current_admin_obj->getRights();
		foreach ($params as $key => $value) {
			if ($key == 'XSRFTag') {
				$key = 'XSRFToken';
				$value = getXSRFToken($value);
			}
			$_POST[$key] = $_GET[$key] = $_REQUEST[$key] = $value;
		}
		require_once($script);
	} else {
		$auth = sha1($script . serialize($admin));
		$paramlist = 'link=' . $script;
		foreach ($params as $key => $value) {
			$paramlist .= '&' . $key . '=' . $value;
		}
		$paramlist .= '&auth=' . $auth . '&offsetPath=' . $offsetPath;
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			$.ajax({
				type: 'POST',
				cache: false,
				data: '<?php echo $paramlist; ?>',
				url: '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/cron_runner.php'
			});
			// ]]> -->
		</script>
		<?php
	}
}

/**
 *
 * Check if logged in (with specific rights)
 * Returns a true value if there is a user logged on with the required rights
 *
 * @param bit $rights rights required by the caller
 *
 * @return bool
 */
function zp_loggedin($rights = ALL_RIGHTS) {
	global $_zp_loggedin;
	return $_zp_loggedin & ($rights | ADMIN_RIGHTS);
}

/**
 *
 * Produces the # to table association array
 */
function getTableAsoc() {
	return array('1' => 'albums', '2' => 'images', '3' => 'news', '4' => 'pages', '5' => 'comments');
}

/**
 *
 * Returns a Zenphoto tiny URL to the object
 * @param $obj object
 */
function getTinyURL($obj) {
	$asoc = array_flip(getTableAsoc());
	$tiny = ($obj->getID() << 3) | $asoc[$obj->table];
	if (MOD_REWRITE) {
		if (class_exists('seo_locale')) {
			return seo_locale::localePath(true) . '/tiny/' . $tiny;
		} else {
			return FULLWEBPATH . '/tiny/' . $tiny;
		}
	} else {
		return FULLWEBPATH . '/index.php?p=' . $tiny . '&t';
	}
}

/**
 * Provides an error protected read of image EXIF/IPTC data
 *
 * @param string $path image path
 * @return array
 *
 */
function read_exif_data_protected($path) {
	if (DEBUG_EXIF) {
		debugLog("Begin read_exif_data_protected($path)");
		$start = microtime(true);
	}
	try {
		$rslt = read_exif_data_raw($path, false);
	} catch (Exception $e) {
		debugLog("read_exif_data($path) exception: " . $e->getMessage());
		$rslt = array();
	}
	if (DEBUG_EXIF) {
		$time = microtime(true) - $start;
		debugLog(sprintf("End read_exif_data_protected($path) [%f]", $time));
	}
	return $rslt;
}

/**
 *
 * fetches the path to the flag image
 * @param string $lang whose flag
 * @return string
 */
function getLanguageFlag($lang) {
	if (file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/locale/' . $lang . '/flag.png')) {
		$flag = WEBPATH . '/' . USER_PLUGIN_FOLDER . '/locale/' . $lang . '/flag.png';
	} else if (file_exists(SERVERPATH . '/' . ZENFOLDER . '/locale/' . $lang . '/flag.png')) {
		$flag = WEBPATH . '/' . ZENFOLDER . '/locale/' . $lang . '/flag.png';
	} else {
		$flag = WEBPATH . '/' . ZENFOLDER . '/locale/missing_flag.png';
	}
	return $flag;
}

/**
 * Gets an item object by id
 *
 * @param string $table database table to search
 * @param int $id id of the item to get
 * @return mixed
 */
function getItemByID($table, $id) {
	if ($result = query_single_row('SELECT * FROM ' . prefix($table) . ' WHERE id =' . $id)) {
		switch ($table) {
			case 'images':
				if ($alb = getItemByID('albums', $result['albumid'])) {
					return newImage($alb, $result['filename']);
				}
			case 'albums':
				return newAlbum($result['folder']);
			case 'news':
				return new ZenpageNews($result['titlelink']);
			case 'pages':
				return new ZenpagePage($result['titlelink']);
			case 'news_categories':
				return new ZenpageCategory($result['titlelink']);
		}
	}
	return NULL;
}

/**
 * uses down and up arrow links to show and hide sections of HTML
 *
 * @param string $content the id of the html section to be revealed
 * @param bool $visible true if the content is initially visible
 */
function reveal($content, $visible = false) {
	?>
	<span id="<?php echo $content; ?>_reveal"<?php if ($visible) echo 'style="display:none;"'; ?> class="icons">
		<a href="javascript:reveal('<?php echo $content; ?>')" title="<?php echo gettext('Click to show content'); ?>">
			<img src="../../images/arrow_down.png" alt="" class="icon-position-top4" />
		</a>
	</span>
	<span id="<?php echo $content; ?>_hide"<?php if (!$visible) echo 'style="display:none;"'; ?> class="icons">
		<a href="javascript:reveal('<?php echo $content; ?>')" title="<?php echo gettext('Click to hide content'); ?>">
			<img src="../../images/arrow_up.png" alt="" class="icon-position-top4" />
		</a>
	</span>
	<?php
}

/**
 * Deals with the [macro parameters] substitutions
 *
 * See the macroList plugin for details
 *
 * @param string $text
 * @return string
 */
function applyMacros($text) {
	$content_macros = getMacros();
	preg_match_all('/\[(\w+)(.*?)\]/i', $text, $instances);
	foreach ($instances[0] as $instance => $macro_instance) {
		$macroname = strtoupper($instances[1][$instance]);
		if (array_key_exists($macroname, $content_macros)) {
			$macro = $content_macros[$macroname];
			$p = $instances[2][$instance];
			$data = NULL;
			$class = $macro['class'];
			if ($p) {
				$p = trim(utf8::sanitize(str_replace("\xC2\xA0", ' ', strip_tags($p)))); //	remove hard spaces and invalid characters
				$p = preg_replace("~\s+=\s+(?=(?:[^\"]*+\"[^\"]*+\")*+[^\"]*+$)~", "=", $p); //	deblank assignment operator
				preg_match_all("~'[^'\"]++'|\"[^\"]++\"|[^\s]++~", $p, $l); //	parse the parameter list
				$parms = array();
				$k = 0;
				foreach ($l[0] as $s) {
					if ($s != ',') {
						$parms[$k++] = trim($s, '\'"'); //	remove any quote marks
					}
				}
			} else {
				$parms = array();
			}
			$parameters = array();
			if (!empty($macro['params'])) {
				$err = false;
				foreach ($macro['params'] as $key => $type) {
					if (array_key_exists($key, $parms)) {
						switch (trim($type, '*')) {
							case 'int':
								if (is_numeric($parms[$key])) {
									$parameters[] = (int) $parms[$key];
									continue 2;
								}
								$data = '<span class="error">' . sprintf(gettext('<em>[%1$s]</em> parameter %2$d should be a number.'), trim($macro_instance, '[]'), $key + 1) . '</span>';
								$class = 'error';
								break;
							case 'string':
								if (is_string($parms[$key])) {
									$parameters[] = $parms[$key];
									continue 2;
								}
								$data = '<span class="error">' . sprintf(gettext('<em>[%1$s]</em> parameter %2$d should be a string.'), trim($macro_instance, '[]'), $key + 1) . '</span>';
								$class = 'error';
								break;
							case 'bool':
								switch (strtolower($parms[$key])) {
									case ("true"):
										$parameters[] = true;
										continue 2;
									case ("false"):
										$parameters[] = false;
										continue 2;
								}
								$data = '<span class="error">' . sprintf(gettext('<em>[%1$s]</em> parameter %2$d should be <code>true</code> or <code>false</code>.'), trim($macro_instance, '[]'), $key + 1) . '</span>';
								$class = 'error';
								break;
							case 'array':
								$l = array_slice($parms, $key);
								$parms = array();
								foreach ($l as $key => $p) {
									$x = explode('=', $p);
									if (count($x) == 2) {
										$parms[$x[0]] = $x[1];
									} else {
										$parms[$key] = $x[0];
									}
								}
								$parameters[] = $parms;
								break;
							default:
								$data = '<span class="error">' . sprintf(gettext('<em>[%1$s]</em> parameter %2$d is incorrectly defined.'), trim($macro_instance, '[]'), $key + 1) . '</span>';
								$class = 'error';
								break;
						}
						break;
					} else {
						if (strpos($type, '*') === false) {
							$data = '<span class="error">' . sprintf(gettext('<em>[%1$s]</em> parameter %2$d is missing.'), trim($macro_instance, '[]'), $key + 1) . '</span>';
							$class = 'error';
						}
						break;
					}
				}
			} else {
				if (!empty($p)) {
					$class = 'error';
					$data = '<span class="error">' . sprintf(gettext('<em>[%1$s]</em> macro does not take parameters'), trim($macro_instance, '[]')) . '</span>';
				}
			}
			switch ($class) {
				case 'error':
					break;
				case 'function';
				case 'procedure':
					if (is_callable($macro['value'])) {
						if ($class == 'function') {
							ob_start();
							$data = call_user_func_array($macro['value'], $parameters);
							if (empty($data)) {
								$data = ob_get_contents();
							}
							ob_end_clean();
						} else {
							ob_start();
							call_user_func_array($macro['value'], $parameters);
							$data = ob_get_contents();
							ob_end_clean();
						}
						if (empty($data)) {
							$data = '<span class="error">' . sprintf(gettext('<em>[%1$s]</em> retuned no data'), trim($macro_instance, '[]')) . '</span>';
						} else {
							$data = "\n<!--Begin " . $macroname . "-->\n" . $data . "\n<!--End " . $macroname . "-->\n";
						}
					} else {
						$data = '<span class="error">' . sprintf(gettext('<em>[%1$s]</em> <code>%2$s</code> is not callable'), trim($macro_instance, '[]'), $macro['value']) . '</span>';
					}
					break;
				case 'constant':
					$data = "\n<!--Begin " . $macroname . "-->\n" . $macro['value'] . "\n<!--End " . $macroname . "-->\n";
					break;
				case 'expression':
					$expression = '$data = ' . $macro['value'];
					$parms = array_reverse($parms, true);
					preg_match_all('/\$\d+/', $macro['value'], $replacements);
					foreach ($replacements as $rkey => $v) {
						if (empty($v))
							unset($replacements[$rkey]);
					}
					if (count($parms) == count($replacements)) {

						foreach ($parms as $key => $value) {
							$key++;
							$expression = preg_replace('/\$' . $key . '/', db_quote($value), $expression);
						}
						eval($expression);
						if (!isset($data) || is_null($data)) {
							$data = '<span class="error">' . sprintf(gettext('<em>[%1$s]</em> retuned no data'), trim($macro_instance, '[]')) . '</span>';
						} else {
							$data = "\n<!--Begin " . $macroname . "-->\n" . $data . "\n<!--End " . $macroname . "-->\n";
						}
					} else {
						$data = '<span class="error">' . sprintf(ngettext('<em>[%1$s]</em> takes %2$d parameter', '<em>[%1$s]</em> takes %2$d parameters', count($replacements)), trim($macro_instance, '[]'), count($replacements)) . '</span>';
					}
					break;
			}
			$text = str_replace($macro_instance, $data, $text);
		}
	}
	return $text;
}

function getMacros() {
	global $_zp_content_macros;
	if (is_null($_zp_content_macros)) {
		$_zp_content_macros = zp_apply_filter('content_macro', array());
	}
	return $_zp_content_macros;
}

class zpFunctions {

	/**
	 *
	 * creates an SEO language prefix list
	 */
	static function LanguageSubdomains() {
		$domains = array();
		$langs = generateLanguageList();
		$domains = array();
		foreach ($langs as $value) {
			$domains[substr($value, 0, 2)][] = $value;
		}
		$langs = array();
		foreach ($domains as $simple => $full) {
			if (count($full) > 1) {
				foreach ($full as $loc) {
					$langs[$loc] = $loc;
				}
			} else {
				$langs[$full[0]] = $simple;
			}
		}
		if (isset($langs[SITE_LOCALE])) {
			$langs[SITE_LOCALE] = '';
		}
		return $langs;
	}

	/**
	 * Returns a canonical language name string for the location
	 *
	 * @param string $loc the location. If NULL use the current cookie
	 * @param string separator will be used between the major and qualifier parts, e.g. en_US
	 *
	 * @return string
	 */
	static function getLanguageText($loc = NULL, $separator = NULL) {
		global $_locale_Subdomains;
		if (is_null($loc)) {
			$text = @$_locale_Subdomains[zp_getCookie('dynamic_locale')];
		} else {
			$text = @$_locale_Subdomains[$loc];
		}
		if (!is_null($separator)) {
			$text = str_replace('_', $separator, $text);
		}
		return $text;
	}

	/**
	 * initializes the $_zp_exifvars array display state
	 *
	 */
	static function setexifvars() {
		global $_zp_exifvars;
		/*
		 * Note: If fields are added or deleted, setup should be run or the new data won't be stored
		 * (but existing fields will still work; nothing breaks).
		 *
		 * This array should be ordered by logical associations as it will be the order that EXIF information
		 * is displayed
		 */
		$_zp_exifvars = array(
						// Database Field       		 => array('source', 'Metadata Key', 'ZP Display Text', Display?	size,	enabled, type)
						'EXIFMake'									 => array('IFD0', 'Make', gettext('Camera Maker'), true, 52, true, 'string'),
						'EXIFModel'									 => array('IFD0', 'Model', gettext('Camera Model'), true, 52, true, 'string'),
						'EXIFDescription'						 => array('IFD0', 'ImageDescription', gettext('Image Title'), false, 52, true, 'string'),
						'IPTCObjectName'						 => array('IPTC', 'ObjectName', gettext('Object Name'), false, 256, true, 'string'),
						'IPTCImageHeadline'					 => array('IPTC', 'ImageHeadline', gettext('Image Headline'), false, 256, true, 'string'),
						'IPTCImageCaption'					 => array('IPTC', 'ImageCaption', gettext('Image Caption'), false, 2000, true, 'string'),
						'IPTCImageCaptionWriter'		 => array('IPTC', 'ImageCaptionWriter', gettext('Image Caption Writer'), false, 32, true, 'string'),
						'EXIFDateTime'							 => array('SubIFD', 'DateTime', gettext('Time Taken'), true, 52, true, 'time'),
						'EXIFDateTimeOriginal'			 => array('SubIFD', 'DateTimeOriginal', gettext('Original Time Taken'), true, 52, true, 'time'),
						'EXIFDateTimeDigitized'			 => array('SubIFD', 'DateTimeDigitized', gettext('Time Digitized'), true, 52, true, 'time'),
						'IPTCDateCreated'						 => array('IPTC', 'DateCreated', gettext('Date Created'), false, 8, true, 'time'),
						'IPTCTimeCreated'						 => array('IPTC', 'TimeCreated', gettext('Time Created'), false, 11, true, 'time'),
						'IPTCDigitizeDate'					 => array('IPTC', 'DigitizeDate', gettext('Digital Creation Date'), false, 8, true, 'time'),
						'IPTCDigitizeTime'					 => array('IPTC', 'DigitizeTime', gettext('Digital Creation Time'), false, 11, true, 'time'),
						'EXIFArtist'								 => array('IFD0', 'Artist', gettext('Artist'), false, 52, true, 'string'),
						'IPTCImageCredit'						 => array('IPTC', 'ImageCredit', gettext('Image Credit'), false, 32, true, 'string'),
						'IPTCByLine'								 => array('IPTC', 'ByLine', gettext('Byline'), false, 32, true, 'string'),
						'IPTCByLineTitle'						 => array('IPTC', 'ByLineTitle', gettext('Byline Title'), false, 32, true, 'string'),
						'IPTCSource'								 => array('IPTC', 'Source', gettext('Image Source'), false, 32, true, 'string'),
						'IPTCContact'								 => array('IPTC', 'Contact', gettext('Contact'), false, 128, true, 'string'),
						'EXIFCopyright'							 => array('IFD0', 'Copyright', gettext('Copyright Holder'), false, 128, true, 'string'),
						'IPTCCopyright'							 => array('IPTC', 'Copyright', gettext('Copyright Notice'), false, 128, true, 'string'),
						'IPTCKeywords'							 => array('IPTC', 'Keywords', gettext('Keywords'), false, 0, true, 'string'),
						'EXIFExposureTime'					 => array('SubIFD', 'ExposureTime', gettext('Shutter Speed'), true, 52, true, 'string'),
						'EXIFFNumber'								 => array('SubIFD', 'FNumber', gettext('Aperture'), true, 52, true, 'number'),
						'EXIFISOSpeedRatings'				 => array('SubIFD', 'ISOSpeedRatings', gettext('ISO Sensitivity'), true, 52, true, 'number'),
						'EXIFExposureBiasValue'			 => array('SubIFD', 'ExposureBiasValue', gettext('Exposure Compensation'), true, 52, true, 'string'),
						'EXIFMeteringMode'					 => array('SubIFD', 'MeteringMode', gettext('Metering Mode'), true, 52, true, 'string'),
						'EXIFFlash'									 => array('SubIFD', 'Flash', gettext('Flash Fired'), true, 52, true, 'string'),
						'EXIFImageWidth'						 => array('SubIFD', 'ExifImageWidth', gettext('Original Width'), false, 52, true, 'number'),
						'EXIFImageHeight'						 => array('SubIFD', 'ExifImageHeight', gettext('Original Height'), false, 52, true, 'number'),
						'EXIFOrientation'						 => array('IFD0', 'Orientation', gettext('Orientation'), false, 52, true, 'string'),
						'EXIFSoftware'							 => array('IFD0', 'Software', gettext('Software'), false, 999, true, 'string'),
						'EXIFContrast'							 => array('SubIFD', 'Contrast', gettext('Contrast Setting'), false, 52, true, 'string'),
						'EXIFSharpness'							 => array('SubIFD', 'Sharpness', gettext('Sharpness Setting'), false, 52, true, 'string'),
						'EXIFSaturation'						 => array('SubIFD', 'Saturation', gettext('Saturation Setting'), false, 52, true, 'string'),
						'EXIFWhiteBalance'					 => array('SubIFD', 'WhiteBalance', gettext('White Balance'), false, 52, true, 'string'),
						'EXIFSubjectDistance'				 => array('SubIFD', 'SubjectDistance', gettext('Subject Distance'), false, 52, true, 'number'),
						'EXIFFocalLength'						 => array('SubIFD', 'FocalLength', gettext('Focal Length'), true, 52, true, 'number'),
						'EXIFLensType'							 => array('SubIFD', 'LensType', gettext('Lens Type'), false, 52, true, 'string'),
						'EXIFLensInfo'							 => array('SubIFD', 'LensInfo', gettext('Lens Info'), false, 52, true, 'string'),
						'EXIFFocalLengthIn35mmFilm'	 => array('SubIFD', 'FocalLengthIn35mmFilm', gettext('35mm Focal Length Equivalent'), false, 52, true, 'string'),
						'IPTCCity'									 => array('IPTC', 'City', gettext('City'), false, 32, true, 'string'),
						'IPTCSubLocation'						 => array('IPTC', 'SubLocation', gettext('Sub-location'), false, 32, true, 'string'),
						'IPTCState'									 => array('IPTC', 'State', gettext('Province/State'), false, 32, true, 'string'),
						'IPTCLocationCode'					 => array('IPTC', 'LocationCode', gettext('Country/Primary Location Code'), false, 3, true, 'string'),
						'IPTCLocationName'					 => array('IPTC', 'LocationName', gettext('Country/Primary Location Name'), false, 64, true, 'string'),
						'IPTCContentLocationCode'		 => array('IPTC', 'ContentLocationCode', gettext('Content Location Code'), false, 3, true, 'string'),
						'IPTCContentLocationName'		 => array('IPTC', 'ContentLocationName', gettext('Content Location Name'), false, 64, true, 'string'),
						'EXIFGPSLatitude'						 => array('GPS', 'Latitude', gettext('Latitude'), false, 52, true, 'number'),
						'EXIFGPSLatitudeRef'				 => array('GPS', 'Latitude Reference', gettext('Latitude Reference'), false, 52, true, 'string'),
						'EXIFGPSLongitude'					 => array('GPS', 'Longitude', gettext('Longitude'), false, 52, true, 'number'),
						'EXIFGPSLongitudeRef'				 => array('GPS', 'Longitude Reference', gettext('Longitude Reference'), false, 52, true, 'string'),
						'EXIFGPSAltitude'						 => array('GPS', 'Altitude', gettext('Altitude'), false, 52, true, 'number'),
						'EXIFGPSAltitudeRef'				 => array('GPS', 'Altitude Reference', gettext('Altitude Reference'), false, 52, true, 'string'),
						'IPTCOriginatingProgram'		 => array('IPTC', 'OriginatingProgram', gettext('Originating Program '), false, 32, true, 'string'),
						'IPTCProgramVersion'				 => array('IPTC', 'ProgramVersion', gettext('Program Version'), false, 10, true, 'string'),
						'VideoFormat'								 => array('VIDEO', 'fileformat', gettext('Video File Format'), false, 32, true, 'string'),
						'VideoSize'									 => array('VIDEO', 'filesize', gettext('Video File Size'), false, 32, true, 'number'),
						'VideoArtist'								 => array('VIDEO', 'artist', gettext('Video Artist'), false, 256, true, 'string'),
						'VideoTitle'								 => array('VIDEO', 'title', gettext('Video Title'), false, 256, true, 'string'),
						'VideoBitrate'							 => array('VIDEO', 'bitrate', gettext('Bitrate'), false, 32, true, 'number'),
						'VideoBitrate_mode'					 => array('VIDEO', 'bitrate_mode', gettext('Bitrate_Mode'), false, 32, true, 'string'),
						'VideoBits_per_sample'			 => array('VIDEO', 'bits_per_sample', gettext('Bits per sample'), false, 32, true, 'number'),
						'VideoCodec'								 => array('VIDEO', 'codec', gettext('Codec'), false, 32, true, 'string'),
						'VideoCompression_ratio'		 => array('VIDEO', 'compression_ratio', gettext('Compression Ratio'), false, 32, true, 'number'),
						'VideoDataformat'						 => array('VIDEO', 'dataformat', gettext('Video Dataformat'), false, 32, true, 'string'),
						'VideoEncoder'							 => array('VIDEO', 'encoder', gettext('File Encoder'), false, 10, true, 'string'),
						'VideoSamplerate'						 => array('VIDEO', 'Samplerate', gettext('Sample rate'), false, 32, true, 'number'),
						'VideoChannelmode'					 => array('VIDEO', 'channelmode', gettext('Channel mode'), false, 32, true, 'string'),
						'VideoFormat'								 => array('VIDEO', 'format', gettext('Format'), false, 10, true, 'string'),
						'VideoChannels'							 => array('VIDEO', 'channels', gettext('Channels'), false, 10, true, 'number'),
						'VideoFramerate'						 => array('VIDEO', 'framerate', gettext('Frame rate'), false, 32, true, 'number'),
						'VideoResolution_x'					 => array('VIDEO', 'resolution_x', gettext('X Resolution'), false, 32, true, 'number'),
						'VideoResolution_y'					 => array('VIDEO', 'resolution_y', gettext('Y Resolution'), false, 32, true, 'number'),
						'VideoAspect_ratio'					 => array('VIDEO', 'pixel_aspect_ratio', gettext('Aspect ratio'), false, 32, true, 'number'),
						'VideoPlaytime'							 => array('VIDEO', 'playtime_string', gettext('Play Time'), false, 10, true, 'number'),
						'XMPrating'									 => array('XMP', 'rating', gettext('XMP Rating'), false, 10, true, 'string'),
		);
		foreach ($_zp_exifvars as $key => $item) {
			if (!is_null($disable = getOption($key . '-disabled'))) {
				$_zp_exifvars[$key][5] = !$disable;
			}
			$_zp_exifvars[$key][3] = getOption($key);
		}
	}

	/**
	 *
	 * Returns true if the install is not a "clone"
	 */
	static function hasPrimaryScripts() {
		if (!defined('PRIMARY_INSTALLATION')) {
			if (function_exists('readlink') && ($zen = str_replace('\\', '/', @readlink(SERVERPATH . '/' . ZENFOLDER)))) {
				// no error reading the link info
				$os = strtoupper(PHP_OS);
				$sp = SERVERPATH;
				if (substr($os, 0, 3) == 'WIN' || $os == 'DARWIN') { // canse insensitive file systems
					$sp = strtolower($sp);
					$zen = strtolower($zen);
				}
				define('PRIMARY_INSTALLATION', $sp == dirname($zen));
			} else {
				define('PRIMARY_INSTALLATION', true);
			}
		}
		return PRIMARY_INSTALLATION;
	}

	/**
	 *
	 * Recursively clears and removes a folder
	 * @param string $path
	 * @return boolean
	 */
	static function removeDir($path, $within = false) {
		if (($dir = @opendir($path)) !== false) {
			while (($file = readdir($dir)) !== false) {
				if ($file != '.' && $file != '..') {
					if ((is_dir($path . '/' . $file))) {
						if (!zpFunctions::removeDir($path . '/' . $file)) {
							return false;
						}
					} else {
						@chmod($path . $file, 0777);
						if (!@unlink($path . '/' . $file)) {
							return false;
						}
					}
				}
			}
			closedir($dir);
			if (!$within) {
				@chmod($path, 0777);
				if (!@rmdir($path)) {
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * inserts location independent WEB path tags in place of site path tags
	 * @param string $text
	 */
	static function tagURLs($text) {
		if ($serial = preg_match('/^a:[0-9]+:{/', $text)) { //	serialized array
			$text = unserialize($text);
		}
		if (is_array($text)) {
			foreach ($text as $key => $textelement) {
				$text[$key] = self::TagURLs($textelement);
			}
			if ($serial) {
				$text = serialize($text);
			}
		} else {
			$text = str_replace(WEBPATH, '{*WEBPATH*}', str_replace(FULLWEBPATH, '{*FULLWEBPATH*}', $text));
		}
		return $text;
	}

	/**
	 * reverses tagURLs()
	 * @param string $text
	 * @return string
	 */
	static function unTagURLs($text) {
		if ($serial = preg_match('/^a:[0-9]+:{/', $text)) { //	serialized array
			$text = getSerializedArray($text);
		}
		if (is_array($text)) {
			foreach ($text as $key => $textelement) {
				$text[$key] = self::unTagURLs($textelement);
			}
			if ($serial) {
				$text = serialize($text);
			}
		} else {
			$text = str_replace('{*WEBPATH*}', WEBPATH, str_replace('{*FULLWEBPATH*}', FULLWEBPATH, $text));
		}
		return $text;
	}

	/**
	 * Searches out i.php image links and replaces them with cache links if image is cached
	 * @param string $text
	 * @return string
	 */
	static function updateImageProcessorLink($text) {
		if ($serial = preg_match('/^a:[0-9]+:{/', $text)) { //	serialized array
			$text = getSerializedArray($text);
		}
		if (is_array($text)) {
			foreach ($text as $key => $textelement) {
				$text[$key] = self::updateImageProcessorLink($textelement);
			}
			if ($serial) {
				$text = serialize($text);
			}
		} else {
			preg_match_all('|\<\s*img.*?\ssrc\s*=\s*"(.*i\.php\?([^"]*)).*/\>|', $text, $matches);
			foreach ($matches[2] as $key => $match) {
				$match = explode('&amp;', $match);
				$set = array();
				foreach ($match as $v) {
					$s = explode('=', $v);
					$set[$s[0]] = $s[1];
				}
				$args = getImageArgs($set);
				$imageuri = getImageURI($args, urldecode($set['a']), urldecode($set['i']), NULL);
				if (strpos($imageuri, 'i.php') === false) {
					$text = str_replace($matches[1][$key], $imageuri, $text);
				}
			}
		}
		return $text;
	}

}

/**
 * Standins for when no captcha is enabled
 */
class _zp_captcha {

	var $name = NULL; // "captcha" name if no captcha plugin loaded

	function getCaptcha($prompt) {
		return array('input' => NULL, 'html' => '<p class="errorbox">' . gettext('No captcha handler is enabled.') . '</p>', 'hidden' => '');
	}

	function checkCaptcha($s1, $s2) {
		return false;
	}

}

/**
 * stand-in for when there is no HTML cache plugin enabled
 */
class _zp_HTML_cache {

	function disable() {

	}

	function startHTMLCache() {

	}

	function abortHTMLCache() {

	}

	function endHTMLCache() {

	}

	function clearHtmlCache() {

	}

}

zpFunctions::setexifvars();
$_locale_Subdomains = zpFunctions::LanguageSubdomains();
?>
