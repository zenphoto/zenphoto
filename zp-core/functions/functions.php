<?php
/**
 * basic functions used by zenphoto
 *
 * @package zpcore\functions\functions
 *
 */
// force UTF-8 Ø

global $_zp_current_context_stack, $_zp_html_cache;

require_once(dirname(__FILE__) . '/functions-basic.php');
require_once(dirname(__FILE__) . '/functions-filter.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/libs/functions-kses.php');
require_once SERVERPATH . '/' . ZENFOLDER . '/libs/functions-htmlawed.php';
require_once(SERVERPATH . '/' . ZENFOLDER . '/classes/class-_zp_captcha.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/classes/class-_zp_html_cache.php');

$_zp_captcha = new _zp_captcha(); // this will be overridden by the plugin if enabled.
$_zp_html_cache = new _zp_HTML_cache(); // this will be overridden by the plugin if enabled.
//setup session before checking for logon cookie
require_once(dirname(__FILE__) . '/functions-i18n.php');

if (GALLERY_SESSION) {
	zp_session_start();
}

define('ZENPHOTO_LOCALE', setMainDomain());

require_once(SERVERPATH . '/' . ZENFOLDER . '/load_objectClasses.php');

$_zp_current_context_stack = array();

$_zp_albumthumb_selector = array(array('field' => '', 'direction' => '', 'desc' => 'random'),
		array('field' => 'id', 'direction' => 'DESC', 'desc' => gettext('most recent')),
		array('field' => 'mtime', 'direction' => '', 'desc' => gettext('oldest')),
		array('field' => 'title', 'direction' => '', 'desc' => gettext('first alphabetically')),
		array('field' => 'hitcounter', 'direction' => 'DESC', 'desc' => gettext('most viewed'))
);

$_zp_missing_album = new AlbumBase(gettext('missing'), false);
$_zp_missing_image = new Transientimage($_zp_missing_album, SERVERPATH . '/' . ZENFOLDER . '/images_errors/err-imagenotfound.png');

if (extensionEnabled('zenpage')) {
	if (getOption('enabled-zenpage-items') == 'news-and-pages' || getOption('enabled-zenpage-items') == 'news') {
		define('ZP_NEWS_ENABLED', true);
	} else {
		define('ZP_NEWS_ENABLED', false);
	}
	if (getOption('enabled-zenpage-items') == 'news-and-pages' || getOption('enabled-zenpage-items') == 'pages') {
		define('ZP_PAGES_ENABLED', true);
	} else {
		define('ZP_PAGES_ENABLED', false);
	}
} else {
	define('ZP_NEWS_ENABLED', false);
	define('ZP_PAGES_ENABLED', false);
}

zp_register_filter('content_macro', 'getCookieInfoMacro');

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
		//strip forbidden tags from list
		if ($tag == 'script') {
			return 0;
		}
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
 * Fixes unbalanced HTML tags. Uses the library htmlawed or if available the native PHP extension tidy
 * 
 * @param string $html
 * @return string
 */
function tidyHTML($html) {
	if (class_exists('tidy')) {
		$options = array(
				'new-blocklevel-tags' => 'article aside audio bdi canvas details dialog figcaption figure footer header main nav section source summary template track video',
				'new-empty-tags' => 'command embed keygen source track wbr',
				'new-inline-tags' => 'audio command datalist embed keygen mark menuitem meter output progress source time video wbr srcset sizes',
				'show-body-only' => true,
				'indent' => true,
				'wrap' => 0
		);
		$tidy = new tidy();
		$tidy->parseString($html, $options, 'utf8');
		$tidy->cleanRepair();
		return trim($tidy);
	} else {
		return trim(htmLawed($html, array('tidy' => '2s2n')));
	}
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
	global $_zp_user_tags;
	$articlecontent = strval($articlecontent);
	if ($shorten && ($forceindicator || (mb_strlen($articlecontent) > $shorten))) {
		$allowed_tags = getAllowedTags('allowed_tags');
		$articlecontent = html_decode($articlecontent);
		//remove script to be replaced later
		$articlecontent = preg_replace('~<script.*?/script>~is', '', $articlecontent);

		//remove HTML comments
		$articlecontent = preg_replace('~<!--.*?-->~is', '', $articlecontent);
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
		if ($short != $articlecontent || $forceindicator) { //	we actually did remove some stuff
			// drop open tag strings
			$open = mb_strrpos($short, '<');
			if ($open > mb_strrpos($short, '>')) {
				$short = mb_substr($short, 0, $open);
			}
			$short = tidyHTML($short . $shortenindicator);
		}
		$articlecontent = $short;
	}
	if (isset($matches)) {
		//replace the script text
		foreach ($matches[0] as $script) {
			$articlecontent = $script . $articlecontent;
		}
	}
	return $articlecontent;
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
	global $_zp_fieldLists, $_zp_db;
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
				$result = $_zp_db->getFields($table);
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
 * Returns a formatted date for output
 *
 * @param string $format A datetime compatible format string. Leave empty to use the option value.
 *							NOTE: If $localize_date = true you need to provide an ICU dateformat string instead of a datetime format string 
 *							unless you pass a date format constant like DATETIME_DISPLAYFORMAT using one of the standard formats. 
 * @param string|int $datetime the date to be formatted. Can be a date string or a timestamp.  
 * @param boolean $localized_date Default null to use the related option setting. Set to true to use localized dates. PHP intl extension required 
 * @return string
 */
function zpFormattedDate($format = '', $datetime = '', $localized_date = null) {
	global $_zp_utf8;
	if (empty($format)) {
		$format = DATETIME_DISPLAYFORMAT;
	}
	$format_converted = convertStrftimeFormat($format);
	if ($format_converted != $format) {
		deprecationNotice(gettext('Using strftime() based date formats strings is deprecated. Use standard date() compatible formatting or a timestamp instead.'), true);
	}
	if (empty($datetime)) {
		$datetime = 'now';
	}
	if (is_null($localized_date)) {
		$localized_date = (bool) getOption('date_format_localized');
	}
	$locale_preferred = array(
			'locale_preferreddate_time',
			'locale_preferreddate_notime'
	);
	if ($localized_date && extension_loaded('intl')) { 
		$datetime_formats = getStandardDateFormats();
		$date_formats = getStandardDateFormats('date');
		$time_formats = getStandardDateFormats('time');
		if (in_array($format_converted, $locale_preferred)) {
			//special format getFormattedLocaleDate() needs to internally
			$localized_format = $format_converted;
		} else if (array_key_exists($format_converted, $datetime_formats)) {
			//one of the predefined datetime ICU formats
			$localized_format = $datetime_formats[$format_converted];
		} else if(array_key_exists($format_converted, $date_formats)) {
			// one of the predefined date ICU formats
			$localized_format = $date_formats[$format_converted];
		} else if(array_key_exists($format_converted, $time_formats)) {
			//one of the predefined time ICU format
			$localized_format = $time_formats[$format_converted];
		} else {
			//custom date we expect to be ICU format already
			$localized_format = $format_converted;
		}
		$fdate = getFormattedLocaleDate($localized_format, $datetime);
	} else {
		// no support for preferred locale dates here so use generic fallback
		if (in_array($format_converted, $locale_preferred)) { 
			$format_converted = 'Y-m-d';
		}
		$dateobj = getDatetimeObject($datetime);
		$fdate = $dateobj->format($format_converted);
	}
	$charset = 'UTF-8';
	$outputset = LOCAL_CHARSET;
	if (function_exists('mb_internal_encoding')) {
		if (($charset = mb_internal_encoding()) == $outputset) {
			return $fdate;
		}
	}
	return $_zp_utf8->convert($fdate, $charset, $outputset);
}

/**
/**
 * Returns a datetime object
 * 
 * @since 1.6.1
 * 
* @param string|int $datetime the date to be output. Can be a date string or a timestamp. If empty "now" is used
 * @return object
 */
function getDatetimeObject($datetime = '') {
	// Check if datetime string or timstamp integer (to cover if passed as a string)
	if (empty($datetime)) {
		$datetime = 'now';
	}
	if (is_string($datetime) && strtotime($datetime) !== false) {
		$date = new DateTime($datetime);
	} else {
		$timestamp = intval($datetime); // to be sure…
		$dateobj = new DateTime();
		$date = $dateobj->setTimestamp($timestamp);
		if (!$date) { // fallback for invalid timestamp
			$date = new DateTime('now');
		}
	}
	return $date;
}

/**
 * Returns an array with datetime (keys) and ICU dateformat (values) strings
 * 
 * @since 1.6.1
 * 
 * @param string $type "date" for date formats without time, "time" for time formats, "datetime" for both combined
 * 
 * @return array
 */
function getStandardDateFormats($type = "datetime") {
	$dateformats = array(
			'm/d/y' => 'MM/dd/yy', //02/25/08
			'm/d/Y' => 'MM/dd/yyyy', //02/25/2008
			'm-d-y' => 'MM-dd-yy', //02-25-08
			'm-d-Y' => 'MM-dd-yyyy', //02-25-2008
			'Y. F d.' => 'yyyy. MMMM dd.', //2008. February 25.
			'Y-m-d' => 'yyyy-MM-dd', //2008-02-25
			'd M Y' => 'dd MMM yyyy', //25 Feb 2008
			'd F Y' => 'dd MMMM yyyy', //25 February 2008
			'd. M Y' => 'dd. MMM yyyy', //25. Feb 2008
			'd. M y' => 'dd. MMM yy', //25. Feb. 08
			'd. F Y' => 'dd. MMMM yyyy', //25. February 2008
			'd.m.y' => 'dd.MM.yy', //25.02.08
			'd.m.Y' => 'dd.MM.yyyy', //25.02.2008
			'j.n.Y' => 'd.M.yyyy', //25.2.2008
			'd-m-y' => 'dd-MM-yy', //25-02-08
			'd-m-Y' => 'dd-MM-yyyy', //25-02-2008
			'd-M-y' => 'dd-MMM-yy', //25-Feb-08
			'd-M-Y' => 'dd-MMM-yyyy', //25-Feb-2008
			'M d, Y' => 'MMM dd, yyyy', //Feb 25, 2008
			'F d, Y' => 'MMMM dd, yyyy', //February 25, 2008
			'F Y' => 'MMMM yyyy', //February 2008
	);
	$timeformats = array(
			'H:i'			=> 'H:mm', //15:30 / 03:30
			'H:i:s'			=> 'H:mm:ss', //15:30:30 / 03:30:30
			'G:i'			=> 'k:mm', //15:30 / 3:30
			'G:i:s'			=> 'k:mm:ss', //15:30:30 / 3:30:30
			'g:i A'		=> 'h:mm a', //3:30 PM	
			'g:i:s A'		=> 'h:mm:ss a' //3:30:30 PM	
	);
	switch ($type) {
		case 'date':
			return $dateformats;
		case 'time':
			return $timeformats;
		case 'datetime':
			$datetime_formats = array();
			foreach($dateformats as $datetime => $icudate) {
				foreach($timeformats as $time => $icutime) {
					$datetime_formats[$datetime . ' ' . $time] = $icudate . ' ' . $icutime;
				}
			}
			return array_merge($datetime_formats, $dateformats);
	}
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
 * @since 1.0.0
 */
function zp_mail($subject, $message, $email_list = NULL, $cc_addresses = NULL, $bcc_addresses = NULL, $replyTo = NULL) {
	global $_zp_authority, $_zp_gallery, $_zp_utf8;
	$result = '';
	if ($replyTo) {
		$t = $replyTo;
		if (!isValidEmail($m = array_shift($t))) {
			if (empty($result)) {
				$result = gettext('Mail send failed.');
			}
			$result .= sprintf(gettext('Invalid “reply-to” mail address %s.'), $m);
		}
	}
	if (is_null($email_list)) {
		$email_list = $_zp_authority->getAdminEmail();
	} else {
		foreach ($email_list as $key => $email) {
			if (!isValidEmail($email)) {
				unset($email_list[$key]);
				if (empty($result)) {
					$result = gettext('Mail send failed.');
				}
				$result .= ' ' . sprintf(gettext('Invalid “to” mail address %s.'), $email);
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
			$result .= ' ' . gettext('“cc” list provided without “to” address list.');
			return $result;
		}
		foreach ($cc_addresses as $key => $email) {
			if (!isValidEmail($email)) {
				unset($cc_addresses[$key]);
				if (empty($result)) {
					$result = gettext('Mail send failed.');
				}
				$result = ' ' . sprintf(gettext('Invalid “cc” mail address %s.'), $email);
			}
		}
	}
	if (is_null($bcc_addresses)) {
		$bcc_addresses = array();
	} else {
		foreach ($bcc_addresses as $key => $email) {
			if (!isValidEmail($email)) {
				unset($bcc_addresses[$key]);
				if (empty($result)) {
					$result = gettext('Mail send failed.');
				}
				$result = ' ' . sprintf(gettext('Invalid “bcc” mail address %s.'), $email);
			}
		}
	}
	if (count($email_list) + count($bcc_addresses) > 0) {
		if (zp_has_filter('sendmail')) {

			$from_mail = getOption('site_email');
			$from_name = get_language_string(getOption('site_email_name'));

			// Convert to UTF-8
			if (LOCAL_CHARSET != 'UTF-8') {
				$subject = $_zp_utf8->convert($subject, LOCAL_CHARSET);
				$message = $_zp_utf8->convert($message, LOCAL_CHARSET);
			}

			//	we do not support rich text
			$message = preg_replace('~<p[^>]*>~', "\n", $message); // Replace the start <p> or <p attr="">
			$message = preg_replace('~</p>~', "\n", $message); // Replace the end
			$message = preg_replace('~<br[^>]*>~', "\n", $message); // Replace <br> or <br ...>
			$message = preg_replace('~<ol[^>]*>~', "", $message); // Replace the start <ol> or <ol attr="">
			$message = preg_replace('~</ol>~', "", $message); // Replace the end
			$message = preg_replace('~<ul[^>]*>~', "", $message); // Replace the start <ul> or <ul attr="">
			$message = preg_replace('~</ul>~', "", $message); // Replace the end
			$message = preg_replace('~<li[^>]*>~', ".\t", $message); // Replace the start <li> or <li attr="">
			$message = preg_replace('~</li>~', "", $message); // Replace the end
			$message = getBare($message);
			$message = preg_replace('~\n\n\n+~', "\n\n", $message);

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
		$result .= ' ' . gettext('No “to” address list provided.');
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
	sortArray($temp);
	if ($descending) {
		$temp = array_reverse($temp, true);
	}
	$result = array();
	foreach ($temp as $key => $v) {
		$result[] = $dbresult[$key];
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
	if (is_object($album)) {
		$albumobj = $album;
	} else {
		$albumobj = AlbumBase::newAlbum($album, true, true);
	}
	return $albumobj->checkForGuest($hint);
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
 * Returns the fully qualified file path of a plugin file.
 * 
 * Note: order of selection is if the file is named "something.php":
 * 
 * - 1-theme folder file (if $inTheme is set): /themes/currenthemefolder/something.php
 * - 2-user plugin folder file: /plugins/something.php
 * - 3-zp-extensions file /zp-core/zp-extensions/something.php
 * 
 * First file found is used. Returns false if no file is found.
 *
 * @param string $plugin is the name of the plugin file, typically something.php
 * @param bool $inTheme tells where to find the plugin.
 *   true means look in the current theme. This for example can be also used to load a additional custom css file for theme customizations so the theme itself does not need to be modified.
 *   false means look in the zp-core/plugins folder.
 * @param bool $webpath return a WEBPATH rather than a SERVERPATH
 *
 * @return string|false
 */
function getPlugin($plugin, $inTheme = false, $webpath = false) {
	global $_zp_gallery;
	$plugin = ltrim($plugin, './\\');
	$pluginFile = NULL;
	if ($inTheme === true) {
		$inTheme = $_zp_gallery->getCurrentTheme();
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
			if (is_string($webpath)) {
				return $webpath . filesystemToInternal($pluginFile);
			} else {
				return WEBPATH . filesystemToInternal($pluginFile);
			}
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
	global $_zp_enabled_plugins;
	if (is_array($_zp_enabled_plugins)) {
		return $_zp_enabled_plugins;
	}
	$_zp_enabled_plugins = array();
	$sortlist = getPluginFiles('*.php');
	foreach ($sortlist as $extension => $path) {
		$opt = 'zp_plugin_' . $extension;
		if ($option = getOption($opt)) {
			$_zp_enabled_plugins[$extension] = array('priority' => $option, 'path' => $path);
		}
	}
	$_zp_enabled_plugins = sortMultiArray($_zp_enabled_plugins, 'priority', true);
	return $_zp_enabled_plugins;
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
 * Disables an extension
 * @param string $extension
 * @param bool $persistent
 * 
 * @since 1.5.2
 */
function disableExtension($extension, $persistent = true) {
	setOption('zp_plugin_' . $extension, 0, $persistent);
}

/**
 * Populates and returns the $_zp_admin_album_list array
 * @return array
 */
function getManagedAlbumList() {
	global $_zp_admin_album_list, $_zp_current_admin_obj, $_zp_db;
	$_zp_admin_album_list = array();
	if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
		$sql = "SELECT `folder` FROM " . $_zp_db->prefix('albums') . ' WHERE `parentid` IS NULL';
		$albums = $_zp_db->query($sql);
		if ($albums) {
			while ($album = $_zp_db->fetchAssoc($albums)) {
				$_zp_admin_album_list[$album['folder']] = 32767;
			}
			$_zp_db->freeResult($albums);
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
	global $_zp_db;
	if ($id <= 0) {
		return array();
	}
	$cv = array();
	if (empty($type) || substr($type, 0, 5) == 'album') {
		$sql = "SELECT " . $_zp_db->prefix('albums') . ".`folder`," . $_zp_db->prefix('albums') . ".`title`," . $_zp_db->prefix('admin_to_object') . ".`edit` FROM " . $_zp_db->prefix('albums') . ", " .
						$_zp_db->prefix('admin_to_object') . " WHERE " . $_zp_db->prefix('admin_to_object') . ".adminid=" . $id .
						" AND " . $_zp_db->prefix('albums') . ".id=" . $_zp_db->prefix('admin_to_object') . ".objectid AND " . $_zp_db->prefix('admin_to_object') . ".type LIKE 'album%'";
		$currentvalues = $_zp_db->query($sql, false);
		if ($currentvalues) {
			while ($albumitem = $_zp_db->fetchAssoc($currentvalues)) {
				$folder = $albumitem['folder'];
				$name = get_language_string($albumitem['title']);
				if ($type && !$rights) {
					$cv[$name] = $folder;
				} else {
					$cv[] = array('data' => $folder, 'name' => $name, 'type' => 'album', 'edit' => $albumitem['edit'] + 0);
				}
			}
			$_zp_db->freeResult($currentvalues);
		}
	}
	if (empty($type) || $type == 'pages') {
		$sql = 'SELECT ' . $_zp_db->prefix('pages') . '.`title`,' . $_zp_db->prefix('pages') . '.`titlelink` FROM ' . $_zp_db->prefix('pages') . ', ' .
						$_zp_db->prefix('admin_to_object') . " WHERE " . $_zp_db->prefix('admin_to_object') . ".adminid=" . $id .
						" AND " . $_zp_db->prefix('pages') . ".id=" . $_zp_db->prefix('admin_to_object') . ".objectid AND " . $_zp_db->prefix('admin_to_object') . ".type='pages'";
		$currentvalues = $_zp_db->query($sql, false);
		if ($currentvalues) {
			while ($item = $_zp_db->fetchAssoc($currentvalues)) {
				if ($type) {
					$cv[get_language_string($item['title'])] = $item['titlelink'];
				} else {
					$cv[] = array('data' => $item['titlelink'], 'name' => $item['title'], 'type' => 'pages');
				}
			}
			$_zp_db->freeResult($currentvalues);
		}
	}
	if (empty($type) || $type == 'news') {
		$sql = 'SELECT ' . $_zp_db->prefix('news_categories') . '.`titlelink`,' . $_zp_db->prefix('news_categories') . '.`title` FROM ' . $_zp_db->prefix('news_categories') . ', ' .
						$_zp_db->prefix('admin_to_object') . " WHERE " . $_zp_db->prefix('admin_to_object') . ".adminid=" . $id .
						" AND " . $_zp_db->prefix('news_categories') . ".id=" . $_zp_db->prefix('admin_to_object') . ".objectid AND " . $_zp_db->prefix('admin_to_object') . ".type='news'";
		$currentvalues = $_zp_db->query($sql, false);
		if ($currentvalues) {
			while ($item = $_zp_db->fetchAssoc($currentvalues)) {
				if ($type) {
					$cv[get_language_string($item['title'])] = $item['titlelink'];
				} else {
					$cv[] = array('data' => $item['titlelink'], 'name' => $item['title'], 'type' => 'news');
				}
			}
			$_zp_db->freeResult($currentvalues);
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
	global $_zp_current_album, $_zp_db;
	if (empty($albumfolder)) {
		if (isset($_zp_current_album)) {
			$albumfolder = $_zp_current_album->getName();
		} else {
			return null;
		}
	}
	$query = "SELECT `id`,`folder`, `show` FROM " . $_zp_db->prefix('albums') . " WHERE `folder` LIKE " . $_zp_db->quote($_zp_db->likeEscape($albumfolder) . '%');
	$subIDs = $_zp_db->queryFullArray($query);
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
	global $_zp_current_search, $_zp_request, $_zp_last_album, $_zp_current_album,
	$_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_gallery, $_zp_loggedin, $_zp_gallery_page;
	$_zp_last_album = zp_getCookie('zpcms_search_lastalbum');
	if (is_object($_zp_request) && get_class($_zp_request) == 'SearchEngine') { //	we are are on a search
		zp_setCookie('zpcms_search_parent', 'searchresults');
		return $_zp_request->getAlbumList();
	}
	$params = zp_getCookie('zpcms_search_params');
	if (!empty($params)) {
		$searchparent = zp_getCookie('zpcms_search_parent');
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
			zp_setCookie('zpcms_search_lastalbum', $albumname);
			if ($_zp_gallery_page == 'album.php') {
				$searchparent = 'searchresults_album'; // so we know we are in an album search result so any of its images that are also results don't throw us out of context
			}
			if (hasDynamicAlbumSuffix($albumname) && !is_dir(ALBUM_FOLDER_SERVERPATH . $albumname)) {
				$albumname = stripSuffix($albumname); // strip off the suffix as it will not be reflected in the search path
			}
			//	see if the album is within the search context. NB for these purposes we need to look at all albums!
			$save_logon = $_zp_loggedin;
			$_zp_loggedin = $_zp_loggedin | VIEW_ALL_RIGHTS;
			$search_album_list = $_zp_current_search->getAlbums(0);
			$_zp_loggedin = $save_logon;
			foreach ($search_album_list as $searchalbum) {
				if (strpos($albumname, $searchalbum) !== false) {
					if ($searchparent == 'searchresults_album') {
						$context = $context | ZP_SEARCH_LINKED | ZP_ALBUM_LINKED;
					} else {
						$context = $context | ZP_SEARCH_LINKED | ZP_IMAGE_LINKED;
					}
					break;
				}
			}
			zp_setCookie('zpcms_search_parent', $searchparent);
		} else {
			zp_clearCookie('zpcms_search_parent');
			zp_clearCookie('zpcms_search_lastalbum');
		}
		if (!is_null($_zp_current_zenpage_page)) {
			$pages = $_zp_current_search->getPages();
			if (!empty($pages)) {
				$tltlelink = $_zp_current_zenpage_page->getName();
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
				$tltlelink = $_zp_current_zenpage_news->getName();
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
			if (!isset($_REQUEST['preserve_search_params'])) {
				zp_clearCookie("zpcms_search_params");
			}
		}
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
		$parent = $album->getUrParent();
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
		<html>
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
 * Returns an array of unique tag names
 *
 * @param bool $checkaccess Set to true if you wish to exclude tags that are assigned to items (or are not assigned at all) the visitor is not allowed to see
 * Beware that this may cause overhead on large sites. Usage of the static_html_cache plugin is strongely recommended.
 * @return array
 */
function getAllTagsUnique($checkaccess = false) {
	global $_zp_unique_tags, $_zp_unique_tags_excluded, $_zp_db;
	if (zp_loggedin(VIEW_ALL_RIGHTS)) {
		$checkaccess = false;
	}
	//need to cache all and filtered tags indiviually
	if ($checkaccess) {
		if (!is_null($_zp_unique_tags_excluded)) {
			return $_zp_unique_tags_excluded; // cache them.
		}
	} else {
		if (!is_null($_zp_unique_tags)) {
			return $_zp_unique_tags; // cache them.
		}
	}
	$all_unique_tags = array();
	$sql = "SELECT DISTINCT `name`, `id` FROM " . $_zp_db->prefix('tags') . ' ORDER BY `name`';
	$unique_tags = $_zp_db->query($sql);
	if ($unique_tags) {
		while ($tagrow = $_zp_db->fetchAssoc($unique_tags)) {
			if ($checkaccess) {
				if (getTagCountByAccess($tagrow) != 0) {
					$all_unique_tags[] = $tagrow['name'];
				}
			} else {
				$all_unique_tags[] = $tagrow['name'];
			}
		}
		$_zp_db->freeResult($unique_tags);
	}
	if ($checkaccess) {
		$_zp_unique_tags_excluded = $all_unique_tags;
		return $_zp_unique_tags_excluded;
	} else {
		$_zp_unique_tags = $all_unique_tags;
		return $_zp_unique_tags;
	}
}

/**
 * Returns an array indexed by 'tag' with the element value the count of the tag
 *
 * @param bool $exclude_unassigned Set to true if you wish to exclude tags that are not assigne to any item
 * @param bool $checkaccess Set to true if you wish to exclude tags that are assigned to items (or are not assigned at all) the visitor is not allowed to see
 * If set to true it overrides the $exclude_unassigned parameter.
 * Beware that this may cause overhead on large sites. Usage of the static_html_cache plugin is strongely recommended.
 * @return array
 */
function getAllTagsCount($exclude_unassigned = false, $checkaccess = false) {
	global $_zp_count_tags, $_zp_db;
	if (!is_null($_zp_count_tags)) {
		return $_zp_count_tags;
	}
	if (zp_loggedin(VIEW_ALL_RIGHTS)) {
		$exclude_unassigned = false;
		$checkaccess = false;
	}
	$_zp_count_tags = array();
	$sql = "SELECT DISTINCT tags.name, tags.id, (SELECT COUNT(*) FROM " . $_zp_db->prefix('obj_to_tag') . " as object WHERE object.tagid = tags.id) AS count FROM " . $_zp_db->prefix('tags') . " as tags ORDER BY `name`";
	$tagresult = $_zp_db->query($sql);
	if ($tagresult) {
		while ($tag = $_zp_db->fetchAssoc($tagresult)) {
			if ($checkaccess) {
				$count = getTagCountByAccess($tag);
				if ($count != 0) {
					$_zp_count_tags[$tag['name']] = $count;
				}
			} else {
				if ($exclude_unassigned) {
					if ($tag['count'] != 0) {
						$_zp_count_tags[$tag['name']] = $tag['count'];
					}
				} else {
					$_zp_count_tags[$tag['name']] = $tag['count'];
				}
			}
		}
		$_zp_db->freeResult($tagresult);
	}
	return $_zp_count_tags;
}

/**
 * Checks if a tag is assigned at all and if it can be viewed by the current visitor and returns the corrected count
 * Helper function used optionally within getAllTagsCount() and getAllTagsUnique()
 *
 * @global obj $_zp_zenpage
 * @param array $tag Array representing a tag containing at least its name and id
 * @return int
 */
function getTagCountByAccess($tag) {
	global $_zp_zenpage, $_zp_object_to_tags, $_zp_db;
	if (array_key_exists('count', $tag) && $tag['count'] == 0) {
		return $tag['count'];
	}
	$hidealbums = getNotViewableAlbums();
	$hideimages = getNotViewableImages();
	$hidenews = array();
	$hidepages = array();
	if (extensionEnabled('Zenpage')) {
		$hidenews = $_zp_zenpage->getNotViewableNews();
		$hidepages = $_zp_zenpage->getNotViewablePages();
	}
	//skip checks if there are no unviewable items at all
	if (empty($hidealbums) && empty($hideimages) && empty($hidenews) && empty($hidepages)) {
		if (array_key_exists('count', $tag)) {
			return $tag['count'];
		}
		return 0;
	}
	if (is_null($_zp_object_to_tags)) {
		$sql = "SELECT tagid, type, objectid FROM " . $_zp_db->prefix('obj_to_tag') . " ORDER BY tagid";
		$_zp_object_to_tags = $_zp_db->queryFullArray($sql);
	}
	$count = '';
	if ($_zp_object_to_tags) {
		foreach ($_zp_object_to_tags as $tagcheck) {
			if ($tagcheck['tagid'] == $tag['id']) {
				switch ($tagcheck['type']) {
					case 'albums':
						if (!in_array($tagcheck['objectid'], $hidealbums)) {
							$count++;
						}
						break;
					case 'images':
						if (!in_array($tagcheck['objectid'], $hideimages)) {
							$count++;
						}
						break;
					case 'news':
						if (ZP_NEWS_ENABLED) {
							if (!in_array($tagcheck['objectid'], $hidenews)) {
								$count++;
							}
						}
						break;
					case 'pages':
						if (ZP_PAGES_ENABLED) {
							if (!in_array($tagcheck['objectid'], $hidepages)) {
								$count++;
							}
						}
						break;
				}
			}
		}
	}
	if (empty($count)) {
		$count = 0;
	}
	return $count;
}

/**
 * Stores tags for an object
 *
 * @param array $tags the tag values
 * @param int $id the record id of the album/image
 * @param string $tbl database table of the object
 */
function storeTags($tags, $id, $tbl) {
	global $_zp_db;
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
		$sql = "SELECT `id`, `tagid` from " . $_zp_db->prefix('obj_to_tag') . " WHERE `objectid`='" . $id . "' AND `type`='" . $tbl . "'";
		$result = $_zp_db->query($sql);
		$existing = array();
		if ($result) {
			while ($row = $_zp_db->fetchAssoc($result)) {
				$dbtag = $_zp_db->querySingleRow("SELECT `name` FROM " . $_zp_db->prefix('tags') . " WHERE `id`='" . $row['tagid'] . "'");
				$existingLC = mb_strtolower($dbtag['name']);
				if (in_array($existingLC, $tagsLC)) { // tag already set no action needed
					$existing[] = $existingLC;
				} else { // tag no longer set, remove it
					$_zp_db->query("DELETE FROM " . $_zp_db->prefix('obj_to_tag') . " WHERE `id`='" . $row['id'] . "'");
				}
			}
			$_zp_db->freeResult($result);
		}
		$tags = array_diff($tagsLC, $existing); // new tags for the object
		foreach ($tags as $key => $tag) {
			$dbtag = $_zp_db->querySingleRow("SELECT `id` FROM " . $_zp_db->prefix('tags') . " WHERE `name`=" . $_zp_db->quote($key));
			if (!is_array($dbtag)) { // tag does not exist
				$_zp_db->query("INSERT INTO " . $_zp_db->prefix('tags') . " (name) VALUES (" . $_zp_db->quote($key) . ")", false);
				$dbtag = array('id' => $_zp_db->insertID());
			}
			$_zp_db->query("INSERT INTO " . $_zp_db->prefix('obj_to_tag') . "(`objectid`, `tagid`, `type`) VALUES (" . $id . "," . $dbtag['id'] . ",'" . $tbl . "')");
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
	global $_zp_db;
	$tags = array();
	$result = $_zp_db->query("SELECT `tagid` FROM " . $_zp_db->prefix('obj_to_tag') . " WHERE `type`='" . $tbl . "' AND `objectid`='" . $id . "'");
	if ($result) {
		while ($row = $_zp_db->fetchAssoc($result)) {
			$dbtag = $_zp_db->querySingleRow("SELECT `name` FROM" . $_zp_db->prefix('tags') . " WHERE `id`='" . $row['tagid'] . "'");
			if ($dbtag) {
				$tags[] = $dbtag['name'];
			}
		}
		$_zp_db->freeResult($result);
	}
	sortArray($tags);
	return $tags;
}

/**
 * Creates the body of a select list
 *
 * @param array $currentValue list of items to be flagged as checked
 * @param array $list the elements of the select list
 * @param bool $descending set true for a ascending order sort. Set to null to keep the array as it is passed.
 * @param bool $localize set true if the keys as description should be listed instead of the plain values
 */
function generateListFromArray($currentValue, $list, $descending, $localize) {
	if ($localize) {
		$list = array_flip($list);
		if (!is_null($descending)) {
			if ($descending) {
				sortArray($list, true); 
			} else {
				sortArray($list); 
			}
		}
		$list = array_flip($list);
	} else {
		if (!is_null($descending)) {
			if ($descending) {
				sortArray($list, true); 
			} else {
				sortArray($list); 
			}
		}
	}

	foreach ($list as $key => $item) {
		echo '<option value="' . html_encode($item) . '"';
		if (in_array($item, $currentValue)) {
			echo ' selected="selected"';
		}
		if ($localize) {
			$display = $key;
		} else {
			$display = $item;
		}
		echo '>' . $display . "</option>" . "\n";
	}
}

/**
 * Generates a selection list from files found on disk
 *
 * @param strig $currentValue the current value of the selector
 * @param string $root directory path to search
 * @param string $suffix suffix to select for
 * @param bool $descending set true to get a reverse order sort. Set to null to keep the array as it is passed.
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
 * Helper to generate attributename="attributevalue" for HTML elements based on an array 
 * consisting of key => value pairs
 * 
 * Returns a string with with prependend space to directly use with an HTML element.
 * 
 * Note: 
 * There is no check if these attributes are valid. Also values are not html_encoded  as that could 
 * break for example JS event handlers. Do this in your attribute definition array as needed.
 * Attributes with an empty value are skipped except the alt attribute or known boolean attributes (see in function definition)
 * 
 * @since 1.5.8
 * @param array $attributes key => value pairs of element attribute name and value. e.g. array('class' => 'someclass', 'id' => 'someid');
 * @param array $exclude Names of attributes to exclude (in case already set otherwise)
 * @return string
 */
function generateAttributesFromArray($attributes = array(), $exclude = array()) {
	$boolean_attr = array(
			'allowfullscreen',
			'allowpaymentrequest',
			'async',
			'autofocus',
			'autoplay',
			'checked',
			'controls',
			'default',
			'disabled',
			'formnovalidate',
			'hidden',
			'ismap',
			'itemscope',
			'loop',
			'multiple',
			'muted',
			'nomodule',
			'novalidate',
			'open',
			'playsinline',
			'readonly',
			'required',
			'reversed',
			'selected',
			'truespeed'
	);
	$attr = '';
	if (!empty($attributes) && is_array($attributes)) {
		foreach ($attributes as $key => $val) {
			if (!in_array($key, $exclude)) {
				if (empty($val)) {
					if (in_array($key, $boolean_attr)) {
						$attr .= ' ' . $key;
					} else if ($key == 'alt') {
						$attr .= ' ' . $key . '=""';
					}
				} else {
					$attr .= ' ' . $key . '="' . $val . '"';
				}
			}
		}
	}
	return $attr;
}

/**
 * @param string $url The link URL
 * @param string $text The text to go with the link
 * @param string $title Text for the title tag
 * @param string $class optional class
 * @param string $id optional id
 * @param array  $extra_attr Additional attributes as array of key => value pairs
 */
function getLinkHTML($url, $text, $title = NULL, $class = NULL, $id = NULL, $extra_attr = array()) {
	$attr = array(
			'href' => html_encode($url),
			'title' => html_encode(getBare($title)),
			'class' => $class,
			'id' => $id
	);
	$attr_final = array_merge($attr, $extra_attr);
	$attributes = generateAttributesFromArray($attr_final);
	return '<a' . $attributes . '>' . html_encode($text) . '</a>';
}

/**
 * General link printing function
 * @param string $url The link URL
 * @param string $text The text to go with the link
 * @param string $title Text for the title tag
 * @param string $class optional class
 * @param string $id optional id
 */
function printLinkHTML($url, $text, $title = NULL, $class = NULL, $id = NULL) {
	echo getLinkHTML($url, $text, $title, $class, $id);
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
	$results = sortMultiArray($results, $indicies, $order, true, false, true);
	return $results;
}

/**
 * multidimensional array column sort
 * 
 * If the system's PHP has the native intl extension and its Collator class available
 * the sorting is locale aware (true natural order) and always case sensitive if $natsort is set to true
 *
 * @param array $array The multidimensional array to be sorted
 * @param mixed $index Which key(s) should be sorted by
 * @param string $descending true for descending sortorder
 * @param bool $natsort If natural order should be used. If available sorting will be locale aware.
 * @param bool $case_sensitive If the sort should be case sensitive. Note if $natsort is true and locale aware sorting is available sorting is always case sensitive
 * @param bool $preservekeys Default false,
 * @param array $remove_criteria Array of indices to remove.
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
					$temp[$key] .= get_language_string($row[$index]) . $separator;
					if (in_array($index, $remove_criteria)) {
						unset($array[$key][$index]);
					}
				}
			}
			$temp[$key] .= $key;
		}
		sortArray($temp, $descending, $natsort, $case_sensitive);
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
 * General one dimensional array sorting function. Key/value associations are preserved.
 * 
 * If the system's PHP has the native intl extension and its Collator class available and $natsort is set to true
 * the sorting is locale sensitive (true natural order).
 * 
 * The function follows native PHP array sorting functions (natcasesort() etc.) and uses the array by reference and returns true or false on success or failure.
 * 
 * @since 1.5.8
 * 
 * @param array $array The array to sort. The array is passed by reference
 * @param string  $descending true for descending sorts (default false)
 * @param bool $natsort If natural order should be used (default true). If available sorting will be locale sensitive. 
 * @param bool $case_sensitive If the sort should be case sensitive (default false). Note if $natsort is true and locale aware sorting is available sorting is always case sensitive
 * @return boolean
 */
function sortArray(&$array, $descending = false, $natsort = true, $case_sensitive = false) {
	$success = false;
	if (is_array($array) && count($array) > 0) {
		if ($natsort) {
			if (class_exists('collator')) {
				$locale = getUserLocale();
				$collator = new Collator($locale);
				if ($case_sensitive) {
					$collator->setAttribute(Collator::CASE_FIRST, Collator::UPPER_FIRST);
				}
				$collator->setAttribute(Collator::NUMERIC_COLLATION, Collator::ON);
				$success = $collator->asort($array, Collator::SORT_STRING);
			} else {
				if ($case_sensitive) {
					$success = natsort($array);
				} else {
					$success = natcasesort($array);
				}
			}
			if ($descending) {
				$array = array_reverse($array, true);
			}
		} else {
			if ($descending) {
				$success = arsort($array);
			} else {
				$success = asort($array);
			}
		}
	}
	return $success;
}

/**
 * Returns a list of album IDs that the current viewer is not allowed to see
 *
 * @return array
 */
function getNotViewableAlbums() {
	global $_zp_not_viewable_album_list, $_zp_db;
	if (zp_loggedin(ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) {
		return array(); //admins can see all
	}
	if (is_null($_zp_not_viewable_album_list)) {
		$sql = 'SELECT `folder` FROM ' . $_zp_db->prefix('albums');
		$result = $_zp_db->query($sql);
		if ($result) {
			$_zp_not_viewable_album_list = array();
			while ($row = $_zp_db->fetchAssoc($result)) {
				$album = AlbumBase::newAlbum($row['folder']);
				if (!$album->isVisible()) {
					$_zp_not_viewable_album_list[] = $album->getID();
				}
			}
			$_zp_db->freeResult($result);
		}
	}
	return $_zp_not_viewable_album_list;
}

/**
 * Returns a list of image IDs that the current viewer is not allowed to see
 *
 * @return array
 */
function getNotViewableImages() {
	global $_zp_not_viewable_image_list, $_zp_db;
	if (zp_loggedin(ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) {
		return array(); //admins can see all
	}
	$hidealbums = getNotViewableAlbums();
	$where = '';
	if ($hidealbums) {
		$where = ' OR `albumid` IN (' . implode(',', $hidealbums) . ')';
	}
	if (is_null($_zp_not_viewable_image_list)) {
		$sql = 'SELECT DISTINCT `id` FROM ' . $_zp_db->prefix('images') . ' WHERE `show` = 0' . $where;
		$result = $_zp_db->query($sql);
		if ($result) {
			$_zp_not_viewable_image_list = array();
			while ($row = $_zp_db->fetchAssoc($result)) {
				$_zp_not_viewable_image_list[] = $row['id'];
			}
		}
	}
	return $_zp_not_viewable_image_list;
}

/**
 * Checks to see if a URL is valid
 *
 * @param string $url the URL being checked
 * @return bool
 */
function isValidURL($url) {
	if (filter_var($url, FILTER_VALIDATE_URL)) {
		return true;
	}
	/*
	 * Above does not allow the newer UTF8 internation domain names.
	 * @see Alexander Terehov https://github.com/terales/php-url-validation-example
	 */
	if (parse_url($url, PHP_URL_SCHEME) && parse_url($url, PHP_URL_HOST)) {
		return true;
	}
	return false;
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
 * Returns true if the mail address passed is valid. 
 * It uses PHP's internal `filter_var` functions to validate the syntax but not the existence.
 * 
 * @since 1.5.2
 * 
 * @param string $email An email address
 * @return boolean
 */
function isValidEmail($email) {
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return true;
	}
	return false;
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

/**
 * Removes the time zone info from date formats like "2009-05-14T13:30:29+10:00" as stored for e.g. image meta data
 * 
 * @since 1.6.3
 * 
 * @param string $date
 * @return string
 */
function removeDateTimeZone($date) {
	if (!is_int($date) && strpos($date, 'T') !== false) {
		$date = str_replace('T', ' ', substr($date, 0, 19));
	}
	return $date;
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
 * Returns the current item object (image, album and Zenpage page, article, category) of the current theme context
 * or false if no context is set or matches
 *
 * @since 1.6.3
 * @global obj $_zp_current_album
 * @global obj $_zp_current_image
 * @global obj $_zp_current_zenpage_page
 * @global obj $_zp_current_category
 * @global obj $_zp_current_zenpage_news
 * @return boolean|obj
 */
function getContextObject() {
	global $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_page, $_zp_current_category, $_zp_current_zenpage_news;
	if (in_context(ZP_IMAGE)) {
		$obj = $_zp_current_image;
	} elseif (in_context(ZP_ALBUM)) {
		$obj = $_zp_current_album;
	} elseif (in_context(ZP_ZENPAGE_PAGE)) {
		$obj = $_zp_current_zenpage_page;
	} elseif (in_context(ZP_ZENPAGE_NEWS_ARTICLE) || in_context(ZP_ZENPAGE_SINGLE)) {
		$obj = $_zp_current_zenpage_news;
	} elseif (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
		$obj = $_zp_current_category;
	}  else {
		$obj = false;
	}
	return $obj;
}

/**
 * checks password posting
 *
 * @param string $authType override of athorization type
 */
function zp_handle_password($authType = NULL, $check_auth = NULL, $check_user = NULL) {
	global $_zp_loggedin, $_zp_login_error, $_zp_current_album, $_zp_current_zenpage_page, $_zp_current_category, $_zp_current_zenpage_news, $_zp_gallery, $_zp_db;
	if (empty($authType)) { // not supplied by caller
		$check_auth = '';
		if (isset($_GET['z']) && @$_GET['p'] == 'full-image' || isset($_GET['p']) && $_GET['p'] == '*full-image') {
			$authType = 'zpcms_auth_image';
			$check_auth = getOption('protected_image_password');
			$check_user = getOption('protected_image_user');
		} else if (in_context(ZP_SEARCH)) { // search page
			$authType = 'zpcms_auth_search';
			$check_auth = getOption('search_password');
			$check_user = getOption('search_user');
		} else if (in_context(ZP_ALBUM)) { // album page
			$authType = "zpcms_auth_album_" . $_zp_current_album->getID();
			$check_auth = $_zp_current_album->getPassword();
			$check_user = $_zp_current_album->getUser();
			if (empty($check_auth)) {
				$parent = $_zp_current_album->getParent();
				while (!is_null($parent)) {
					$check_auth = $parent->getPassword();
					$check_user = $parent->getUser();
					$authType = "zpcms_auth_album_" . $parent->getID();
					if (!empty($check_auth)) {
						break;
					}
					$parent = $parent->getParent();
				}
			}
		} else if (in_context(ZP_ZENPAGE_PAGE)) {
			$authType = "zpcms_auth_page_" . $_zp_current_zenpage_page->getID();
			$check_auth = $_zp_current_zenpage_page->getPassword();
			$check_user = $_zp_current_zenpage_page->getUser();
			if (empty($check_auth)) {
				$pageobj = $_zp_current_zenpage_page;
				while (empty($check_auth)) {
					$parentID = $pageobj->getParentID();
					if ($parentID == 0)
						break;
					$sql = 'SELECT `titlelink` FROM ' . $_zp_db->prefix('pages') . ' WHERE `id`=' . $parentID;
					$result = $_zp_db->querySingleRow($sql);
					$pageobj = new ZenpagePage($result['titlelink']);
					$authType = "zpcms_auth_page_" . $pageobj->getID();
					$check_auth = $pageobj->getPassword();
					$check_user = $pageobj->getUser();
				}
			}
		} else if (in_context(ZP_ZENPAGE_NEWS_CATEGORY) || in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
			$check_auth_user = array();
			if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
				$checkcats = array($_zp_current_category);
			} else if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
				$checkcats = array();
				$cats = $_zp_current_zenpage_news->getCategories();
				foreach ($cats as $cat) {
					$checkcats[] = new ZenpageCategory($cat['titlelink']);
				}
			}
			if (!empty($checkcats)) {
				foreach ($checkcats as $obj) {
					$authType = "zpcms_auth_category_" . $obj->getID();
					$check_auth = $obj->getPassword();
					$check_user = $obj->getUser();
					if (empty($check_auth)) {
						$catobj = $obj;
						while (empty($check_auth)) {
							$parentID = $catobj->getParentID();
							if ($parentID == 0)
								break;
							$sql = 'SELECT `titlelink` FROM ' . $_zp_db->prefix('news_categories') . ' WHERE `id`=' . $parentID;
							$result = $_zp_db->querySingleRow($sql);
							$catobj = new ZenpageCategory($result['titlelink']);
							$authType = "zpcms_auth_category_" . $catobj->getID();
							$check_auth = $catobj->getPassword();
							$check_user = $catobj->getUser();
						}
					}
					if (!empty($check_auth)) {
						//collect passwords from all categories
						$check_auth_user[] = array(
								'authtype' => $authType,
								'check_auth' => $check_auth,
								'check_user' => $check_user
						);
					}
				}
			}
		}
		if (empty($check_auth)) { // anything else is controlled by the gallery credentials
			$authType = 'zpcms_auth_gallery';
			$check_auth = $_zp_gallery->getPassword();
			$check_user = $_zp_gallery->getUser();
		}
	}
	if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
		//check every category with password individually
		foreach ($check_auth_user as $check) {
			zp_handle_password_single($check['authtype'], $check['check_auth'], $check['check_user']);
		}
	} else {
		zp_handle_password_single($authType, $check_auth, $check_user);
	}
}

/**
 * Handles a passwort 
 * 
 * @param string $authType override of authorization type
 * @param string $check_auth Password
 * @param string $check_user User
 * @return bool
 */
function zp_handle_password_single($authType = NULL, $check_auth = NULL, $check_user = NULL) {
	global $_zp_login_error;
	// Handle the login form.
	if (DEBUG_LOGIN)
		debugLog("zp_handle_password: \$authType=$authType; \$check_auth=$check_auth; \$check_user=$check_user; ");

	if (isset($_POST['password']) && isset($_POST['pass'])) { // process login form
		if (isset($_POST['user'])) {
			$post_user = sanitize($_POST['user']);
		} else {
			$post_user = '';
		}
		$post_pass = $_POST['pass']; // We should not sanitize the password

		foreach (Authority::$hashList as $hash => $hi) {
			$auth = Authority::passwordHash($post_user, $post_pass, $hi);
			$success = ($auth == $check_auth) && $post_user == $check_user;
			if (DEBUG_LOGIN)
				debugLog("zp_handle_password($success): \$post_user=$post_user; \$post_pass=$post_pass; \$check_auth=$check_auth; \$auth=$auth; \$hash=$hash;");
			if ($success) {
				break;
			}
		}
		$success = zp_apply_filter('guest_login_attempt', $success, $post_user, $post_pass, $authType);
		if ($success) {
			$_zp_login_error = 0;
			// Correct auth info. Set the cookie.
			if (DEBUG_LOGIN)
				debugLog("zp_handle_password: valid credentials");
			zp_setCookie($authType, $auth);
			if (isset($_POST['redirect'])) {
				$redirect_to = sanitizeRedirect($_POST['redirect']);
				if (!empty($redirect_to)) {
					redirectURL($redirect_to);
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
	global $_zp_db;
	$sql = "SELECT `value` FROM " . $_zp_db->prefix('options') . " WHERE `name`=" . $_zp_db->quote($key) . " AND `ownerid`=0";
	$optionlist = $_zp_db->querySingleRow($sql, false);
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
	global $_zp_gallery, $_zp_db;
	if (is_null($album)) {
		$id = 0;
	} else {
		$id = $album->getID();
		$theme = $album->getAlbumTheme();
	}
	if (empty($theme)) {
		$theme = $_zp_gallery->getCurrentTheme();
	}
	$creator = THEMEFOLDER . '/' . $theme;

	$sql = 'INSERT INTO ' . $_zp_db->prefix('options') . ' (`name`,`ownerid`,`theme`,`creator`,`value`) VALUES (' . $_zp_db->quote($key) . ',0,' . $_zp_db->quote($theme) . ',' . $_zp_db->quote($creator) . ',';
	$sqlu = ' ON DUPLICATE KEY UPDATE `value`=';
	if (is_null($value)) {
		$sql .= 'NULL';
		$sqlu .= 'NULL';
	} else {
		$sql .= $_zp_db->quote($value);
		$sqlu .= $_zp_db->quote($value);
	}
	$sql .= ') ';
	if (!$default) {
		$sql .= $sqlu;
	}
	$result = $_zp_db->query($sql, false);
}

/**
 * Replaces/renames an option. If the old option exits, it creates the new option with the old option's value as the default 
 * unless the new option has already been set otherwise. Independently it always deletes the old option.
 * 
 * @param string $oldkey Old option name
 * @param string $newkey New option name
 * 
 * @since 1.5.1
 */
function replaceThemeOption($oldkey, $newkey) {
	$oldoption = getThemeOption($oldkey);
	if ($oldoption) {
		setThemeOptionDefault($newkey, $oldoption);
		purgeThemeOption($oldkey);
	}
}

/**
 * Deletes an theme option for a specific or the current theme from the database 
 * 
 * @global array $_zp_options
 * @param string $key
 * 
 * @since 1.5.1
 */
function purgeThemeOption($key, $album = NULL, $theme = NULL, $allthemes = false) {
	global $_zp_set_theme_album, $_zp_gallery, $_zp_db;
	if (is_null($album)) {
		$album = $_zp_set_theme_album;
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
	$sql = 'DELETE FROM ' . $_zp_db->prefix('options') . ' WHERE `name`=' . $_zp_db->quote($key) . ' AND `ownerid`=' . $id . ' AND `theme`=' . $_zp_db->quote($theme);
	$_zp_db->query($sql, false);
}

/**
 * Deletes a theme option for all themes present or not
 * 
 * @since 1.6
 * 
 * @global obj $_zp_db
 * @param string $key
 */
function purgeThemeOptionTotal($key) {
	global $_zp_db;
	$sql = 'DELETE FROM ' . $_zp_db->prefix('options') . ' WHERE `name`=' . $_zp_db->quote($key) . ' AND `theme`IS NOT NULL';
	$_zp_db->query($sql, false);
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
	global $_zp_set_theme_album, $_zp_gallery, $_zp_db;
	if (is_null($album)) {
		$album = $_zp_set_theme_album;
	}

	if (is_null($album) || !is_object($album)) {
		$id = 0;
	} else {
		$id = $album->getID();
		$theme = $album->getAlbumTheme();
	}
	if (empty($theme)) {
		$theme = $_zp_gallery->getCurrentTheme();
	}

	// album-theme
	$sql = "SELECT `value` FROM " . $_zp_db->prefix('options') . " WHERE `name`=" . $_zp_db->quote($option) . " AND `ownerid`=" . $id . " AND `theme`=" . $_zp_db->quote($theme);
	$db = $_zp_db->querySingleRow($sql);
	if (!$db) {
		// raw theme option
		$sql = "SELECT `value` FROM " . $_zp_db->prefix('options') . " WHERE `name`=" . $_zp_db->quote($option) . " AND `ownerid`=0 AND `theme`=" . $_zp_db->quote($theme);
		$db = $_zp_db->querySingleRow($sql);
		if (!$db) {
			// raw album option
			$sql = "SELECT `value` FROM " . $_zp_db->prefix('options') . " WHERE `name`=" . $_zp_db->quote($option) . " AND `ownerid`=" . $id . " AND `theme`=NULL";
			$db = $_zp_db->querySingleRow($sql);
			if (!$db) {
				return getOption($option);
			}
		}
	}
	return $db['value'];
}

/**
 * Returns the viewer's IP address
 * Deals with transparent proxies
 *
 * @param bool $anonymize If null (default) the backend option setting is used. Override with anonymize levels 
 * - 0 (No anonymizing)
 * - 1 (Last fourth anonymized)
 * - 2 (Last half anonymized)
 * - 3 (Last three fourths anonymized)
 * - 4 (Full anonymization, no IP stored)
 * @return string
 */
function getUserIP($anonymize = null) {
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = sanitize($_SERVER['HTTP_X_FORWARDED_FOR']);
		if (filter_var($ip, FILTER_VALIDATE_IP)) {
			return getAnonymIP($ip, $anonymize);
		}
	}
	$ip = sanitize($_SERVER['REMOTE_ADDR']);
	if (filter_var($ip, FILTER_VALIDATE_IP)) {
		return getAnonymIP($ip, $anonymize);
	}
	return NULL;
}

/**
 * Anonymizing of IP addresses
 * @param bool $anonymize If null (default) the backend option setting is used. Override with anonymize levels 
 * - 0 (No anonymizing)
 * - 1 (Last fourth anonymized)
 * - 2 (Last half anonymized)
 * - 3 (Last three fourths anonymized)
 * - 4 (Full anonymization, no IP stored)
 * 
 * @return string
 */
function getAnonymIP($ip, $anonymize = null) {
	if (is_null($anonymize)) {
		$anonymize = getOption('anonymize_ip');
	}
	$is_ipv6 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	switch ($anonymize) {
		case 0; // No anonymizing
			return $ip;
		default:
		case 1; // Last fourth anonymized
			if ($is_ipv6) {
				return preg_replace('~[0-9a-zA-Z]*:[0-9a-zA-Z]+$~', '0:0', $ip);
			} else {
				return preg_replace('~[0-9a-zA-Z]+$~', '0', $ip);
			}
		case 2: // Last half anonymized
			if ($is_ipv6) {
				return preg_replace('~[0-9a-zA-Z]*:[0-9a-zA-Z]*:[0-9a-zA-Z]*:[0-9a-zA-Z]+$~', '0:0:0:0', $ip);
			} else {
				return preg_replace('~[0-9a-zA-Z]*.[0-9a-zA-Z]+$~', '0.0', $ip);
			}
		case 3: // Last three fourths anonymized
			if ($is_ipv6) {
				return preg_replace('~[0-9a-zA-Z]*:[0-9a-zA-Z]*:[0-9a-zA-Z]*:[0-9a-zA-Z]*:[0-9a-zA-Z]*:[0-9a-zA-Z]+$~', '0:0:0:0:0:0', $ip);
			} else {
				return preg_replace('~[0-9a-zA-Z]*.[0-9a-zA-Z]*.[0-9a-zA-Z]+$~', '0.0.0', $ip);
			}
		case 4: // Full anonymization, no IP stored
			if ($is_ipv6) {
				return '0:0:0:0:0:0:0:0';
			} else {
				return '0.0.0.0';
			}
	}
}

/**
 * Strips out and/or replaces characters from the string that are not "soe" friendly
 *
 * @param string $string
 * @return string
 */
function seoFriendly($string) {
	$string = trim(preg_replace('~\s+\.\s*~', '.', $string));
	if (zp_has_filter('seoFriendly')) {
		$string = zp_apply_filter('seoFriendly', $string);
	} else { // no filter, do basic cleanup
		$string = trim($string);
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
		fname=fname.trim();
		fname=fname.replace(/\s+\.\s*/,'.');
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
		$list = explode('/', strval($album));
		if (array_shift($list) == 'cache') {
			return;
		}
		$ignore = array('/favicon.ico', '/zp-data/tést.jpg');
		$target = getRequestURI();
		foreach ($ignore as $uri) {
			if ($target == $uri)
				return;
		}
		$server = array();
		foreach (array('REQUEST_URI', 'HTTP_REFERER', 'REMOTE_ADDR', 'REDIRECT_STATUS') as $key) {
			$server[$key] = @$_SERVER[$key];
		}
		$request = $_REQUEST;
		$request['theme'] = $theme;
		if (!empty($image)) {
			$request['image'] = $image;
		}

		trigger_error(sprintf(gettext('Zenphoto processed a 404 error on %s. See the debug log for details.'), $target), E_USER_NOTICE);
		ob_start();
		var_dump($server);
		$server = preg_replace('~array\s*\(.*\)\s*~', '', html_decode(getBare(ob_get_contents())));
		ob_end_clean();
		ob_start();
		var_dump($request);
		$request['theme'] = $theme;
		if (!empty($image)) {
			$request['image'] = $image;
		}
		$request = preg_replace('~array\s*\(.*\)\s*~', '', html_decode(getBare(ob_get_contents())));
		ob_end_clean();
		debugLog("404 error details\n" . $server . $request);
	}
}

/**
 * Checks for Cross Site Request Forgeries
 * @param string $action
 */
function XSRFdefender($action) {
	$token = getXSRFToken($action);
	if (!isset($_REQUEST['XSRFToken']) || $_REQUEST['XSRFToken'] != $token) {
		zp_apply_filter('admin_XSRF_access', false, $action);
		redirectURL(FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&error&msg=' . sprintf(gettext('“%s” Cross Site Request Forgery blocked.'), $action), '302');
	}
	unset($_REQUEST['XSRFToken']);
	unset($_POST['XSRFToken']);
	unset($_GET['XSRFToken']);
}

/**
 * returns an XSRF token
 * @param string $action
 */
function getXSRFToken($action) {
	global $_zp_current_admin_obj, $_zp_db;
	$admindata = '';
	if (!is_null($_zp_current_admin_obj)) {
		$admindata = $_zp_current_admin_obj->getData();
		unset($admindata['lastvisit']);
	}
	return sha1($action . $_zp_db->prefix(ZENPHOTO_VERSION) . serialize($admindata) . session_id());
}

/**
 * Emits a "hidden" input for the XSRF token
 * @param string $action
 */
function XSRFToken($action) {
	?>
	<input type="hidden" name="XSRFToken" id="XSRFToken<?php echo $action; ?>" value="<?php echo getXSRFToken($action); ?>" />
	<?php
}

/**
 * Starts a sechedule script run
 * @param string $script The script file to load
 * @param array $params "POST" parameters
 * @param bool $inline set to true to run the task "in-line". Set false run asynchronously
 */
function cron_starter($script, $params, $offsetPath, $inline = false) {
	global $_zp_authority, $_zp_loggedin, $_zp_current_admin_obj, $_zp_html_cache;
	$admin = $_zp_authority->getMasterUser();

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
		$_zp_html_cache->abortHTMLCache();
		?>
		<script>
			$.ajax({
				type: 'POST',
				cache: false,
				data: '<?php echo $paramlist; ?>',
				url: '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/cron_runner.php'
			});
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
	global $_zp_loggedin, $_zp_current_admin_obj, $_zp_db;
	if (is_object($_zp_db) && $_zp_db->isEmptyTable('administrators')) {
		return false;
	}
	$loggedin = $_zp_loggedin & ($rights | ADMIN_RIGHTS);
	if ($loggedin && $_zp_current_admin_obj) {
		$_zp_current_admin_obj->updateLastVisit();
	}
	return $loggedin;
}

/**
 * Provides an error protected read of image EXIF/IPTC data
 *
 * @param string $path image path
 * @return array
 *
 */
function read_exif_data_protected($path) {
	if (@exif_imagetype($path) !== false) {
		if (DEBUG_EXIF) {
			debugLog("Begin read_exif_data_protected($path)");
			$start = microtime(true);
		}
		try {
			$rslt = @exif_read_data($path);
		} catch (Exception $e) {
			if (DEBUG_EXIF) {
				debugLog("read_exif_data($path) exception: " . $e->getMessage());
			}
			$rslt = array();
		}
		if (DEBUG_EXIF) {
			$time = microtime(true) - $start;
			debugLog(sprintf("End read_exif_data_protected($path) [%f]", $time));
		}
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
	global $_zp_db;
	if ($result = $_zp_db->querySingleRow('SELECT * FROM ' . $_zp_db->prefix($table) . ' WHERE id =' . (int) $id)) {
		switch ($table) {
			case 'images':
				if ($alb = getItemByID('albums', $result['albumid'])) {
					return Image::newImage($alb, $result['filename'], true);
				}
				break;
			case 'albums':
				return AlbumBase::newAlbum($result['folder'], false, true);
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
	global $_zp_db;
	$text = strval($text);
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
					$data = false;
					if (array_key_exists($key, $parms)) {
						switch (trim($type, '*')) {
							case 'int':
								if (is_numeric($parms[$key])) {
									$parameters[] = (int) $parms[$key];
								} else {
									$data = '<span class="error">' . sprintf(gettext('<em>[%1$s]</em> parameter %2$d should be a number.'), trim($macro_instance, '[]'), $key + 1) . '</span>';
									$class = 'error';
								}
								break;
							case 'string':
								if (is_string($parms[$key])) {
									$parameters[] = $parms[$key];
								} else {
									$data = '<span class="error">' . sprintf(gettext('<em>[%1$s]</em> parameter %2$d should be a string.'), trim($macro_instance, '[]'), $key + 1) . '</span>';
									$class = 'error';
								}
								break;
							case 'bool':
								switch (strtolower($parms[$key])) {
									case ("true"):
										$parameters[] = true;
										break;
									case ("false"):
										$parameters[] = false;
										break;
									default:
										$data = '<span class="error">' . sprintf(gettext('<em>[%1$s]</em> parameter %2$d should be <code>true</code> or <code>false</code>.'), trim($macro_instance, '[]'), $key + 1) . '</span>';
										$class = 'error';
										break;
								}
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
							$expression = preg_replace('/\$' . $key . '/', $_zp_db->quote($value), $expression);
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

/**
 * generates a nested list of albums for the album tab sorting
 * Returns an array of "albums" each element contains:
 * 								'name' which is the folder name
 * 								'sort_order' which is an array of the sort order set
 *
 * @param $subalbum root level album (NULL is the gallery)
 * @param $levels how far to nest
 * @param $checkalbumrights TRUE (Default) for album rights for backend usage, FALSE to skip for frontend usage
 * @param $level internal for keeping the sort order elements
 * @return array
 */
function getNestedAlbumList($subalbum, $levels, $checkalbumrights = true, $level = array()) {
	global $_zp_gallery;
	$cur = count($level);
	$levels--; // make it 0 relative to sync with $cur
	if (is_null($subalbum)) {
		$albums = $_zp_gallery->getAlbums();
	} else {
		$albums = $subalbum->getAlbums();
	}
	$list = array();
	foreach ($albums as $analbum) {
		$albumobj = AlbumBase::newAlbum($analbum);
		$accessallowed = true;
		if ($checkalbumrights) {
			$accessallowed = $albumobj->isMyItem(ALBUM_RIGHTS);
		}
		if (!is_null($subalbum) || $accessallowed) {
			$level[$cur] = sprintf('%03u', $albumobj->getSortOrder());
			$list[] = array('name' => $analbum, 'sort_order' => $level);
			if ($cur < $levels && ($albumobj->getNumAlbums()) && !$albumobj->isDynamic()) {
				$list = array_merge($list, getNestedAlbumList($albumobj, $levels + 1, $checkalbumrights, $level));
			}
		}
	}
	return $list;
}

function getImageMetaDataFieldFormatted() {
	
}

/**
 * initializes the $_zp_exifvars array display state
 *
 */
function setexifvars() {
	global $_zp_exifvars;
	/*
	 * Note: If fields are added or deleted, setup should be run or the new data won't be stored
	 * (but existing fields will still work; nothing breaks).
	 *
	 * This array should be ordered by logical associations as it will be the order that EXIF information
	 * is displayed
	 */
	$_zp_exifvars = array(
			// Database Field       		 => array('source', 'Metadata Key', 'ZP Display Text', Display?,	size (ignored!), enabled, type)
			'EXIFMake' => array('IFD0', 'Make', gettext('Camera Maker'), true, 52, true, 'string'),
			'EXIFModel' => array('IFD0', 'Model', gettext('Camera Model'), true, 52, true, 'string'),
			'EXIFDescription' => array('IFD0', 'ImageDescription', gettext('Image Title'), false, 52, true, 'string'),
			'IPTCObjectName' => array('IPTC', 'ObjectName', gettext('Object Name'), false, 256, true, 'string'),
			'IPTCImageHeadline' => array('IPTC', 'ImageHeadline', gettext('Image Headline'), false, 256, true, 'string'),
			'IPTCImageCaption' => array('IPTC', 'ImageCaption', gettext('Image Caption'), false, 2000, true, 'string'),
			'IPTCImageCaptionWriter' => array('IPTC', 'ImageCaptionWriter', gettext('Image Caption Writer'), false, 32, true, 'string'),
			'EXIFDateTime' => array('SubIFD', 'DateTime', gettext('Date and Time Taken'), true, 52, true, 'datetime'),
			'EXIFDateTimeOriginal' => array('SubIFD', 'DateTimeOriginal', gettext('Original Date and Time Taken'), true, 52, true, 'datetime'),
			'EXIFDateTimeDigitized' => array('SubIFD', 'DateTimeDigitized', gettext('Date and Time Digitized'), true, 52, true, 'datetime'),
			'IPTCDateCreated' => array('IPTC', 'DateCreated', gettext('Date Created'), false, 8, true, 'date'),
			'IPTCTimeCreated' => array('IPTC', 'TimeCreated', gettext('Time Created'), false, 11, true, 'time'),
			'IPTCDigitizeDate' => array('IPTC', 'DigitizeDate', gettext('Digital Creation Date'), false, 8, true, 'date'),
			'IPTCDigitizeTime' => array('IPTC', 'DigitizeTime', gettext('Digital Creation Time'), false, 11, true, 'time'),
			'EXIFArtist' => array('IFD0', 'Artist', gettext('Artist'), false, 52, true, 'string'),
			'IPTCImageCredit' => array('IPTC', 'ImageCredit', gettext('Image Credit'), false, 32, true, 'string'),
			'IPTCByLine' => array('IPTC', 'ByLine', gettext('Byline'), false, 32, true, 'string'),
			'IPTCByLineTitle' => array('IPTC', 'ByLineTitle', gettext('Byline Title'), false, 32, true, 'string'),
			'IPTCSource' => array('IPTC', 'Source', gettext('Image Source'), false, 32, true, 'string'),
			'IPTCContact' => array('IPTC', 'Contact', gettext('Contact'), false, 128, true, 'string'),
			'EXIFCopyright' => array('IFD0', 'Copyright', gettext('Copyright Holder'), false, 128, true, 'string'),
			'IPTCCopyright' => array('IPTC', 'Copyright', gettext('Copyright Notice'), false, 128, true, 'string'),
			'IPTCKeywords' => array('IPTC', 'Keywords', gettext('Keywords'), false, 0, true, 'string'),
			'EXIFExposureTime' => array('SubIFD', 'ExposureTime', gettext('Shutter Speed'), true, 52, true, 'string'),
			'EXIFFNumber' => array('SubIFD', 'FNumber', gettext('Aperture'), true, 52, true, 'number'),
			'EXIFISOSpeedRatings' => array('SubIFD', 'ISOSpeedRatings', gettext('ISO Sensitivity'), true, 52, true, 'number'),
			'EXIFExposureBiasValue' => array('SubIFD', 'ExposureBiasValue', gettext('Exposure Compensation'), true, 52, true, 'string'),
			'EXIFMeteringMode' => array('SubIFD', 'MeteringMode', gettext('Metering Mode'), true, 52, true, 'string'),
			'EXIFFlash' => array('SubIFD', 'Flash', gettext('Flash Fired'), true, 52, true, 'string'),
			'EXIFImageWidth' => array('SubIFD', 'ExifImageWidth', gettext('Original Width'), false, 52, true, 'number'),
			'EXIFImageHeight' => array('SubIFD', 'ExifImageHeight', gettext('Original Height'), false, 52, true, 'number'),
			'EXIFOrientation' => array('IFD0', 'Orientation', gettext('Orientation'), false, 52, true, 'string'),
			'EXIFSoftware' => array('IFD0', 'Software', gettext('Software'), false, 999, true, 'string'),
			'EXIFContrast' => array('SubIFD', 'Contrast', gettext('Contrast Setting'), false, 52, true, 'string'),
			'EXIFSharpness' => array('SubIFD', 'Sharpness', gettext('Sharpness Setting'), false, 52, true, 'string'),
			'EXIFSaturation' => array('SubIFD', 'Saturation', gettext('Saturation Setting'), false, 52, true, 'string'),
			'EXIFWhiteBalance' => array('SubIFD', 'WhiteBalance', gettext('White Balance'), false, 52, true, 'string'),
			'EXIFSubjectDistance' => array('SubIFD', 'SubjectDistance', gettext('Subject Distance'), false, 52, true, 'number'),
			'EXIFFocalLength' => array('SubIFD', 'FocalLength', gettext('Focal Length'), true, 52, true, 'number'),
			'EXIFLensType' => array('SubIFD', 'LensType', gettext('Lens Type'), false, 52, true, 'string'),
			'EXIFLensInfo' => array('SubIFD', 'LensInfo', gettext('Lens Info'), false, 52, true, 'string'),
			'EXIFFocalLengthIn35mmFilm' => array('SubIFD', 'FocalLengthIn35mmFilm', gettext('35mm Focal Length Equivalent'), false, 52, true, 'string'),
			'IPTCCity' => array('IPTC', 'City', gettext('City'), false, 32, true, 'string'),
			'IPTCSubLocation' => array('IPTC', 'SubLocation', gettext('Sub-location'), false, 32, true, 'string'),
			'IPTCState' => array('IPTC', 'State', gettext('Province/State'), false, 32, true, 'string'),
			'IPTCLocationCode' => array('IPTC', 'LocationCode', gettext('Country/Primary Location Code'), false, 3, true, 'string'),
			'IPTCLocationName' => array('IPTC', 'LocationName', gettext('Country/Primary Location Name'), false, 64, true, 'string'),
			'IPTCContentLocationCode' => array('IPTC', 'ContentLocationCode', gettext('Content Location Code'), false, 3, true, 'string'),
			'IPTCContentLocationName' => array('IPTC', 'ContentLocationName', gettext('Content Location Name'), false, 64, true, 'string'),
			'EXIFGPSLatitude' => array('GPS', 'Latitude', gettext('Latitude'), false, 52, true, 'number'),
			'EXIFGPSLatitudeRef' => array('GPS', 'Latitude Reference', gettext('Latitude Reference'), false, 52, true, 'string'),
			'EXIFGPSLongitude' => array('GPS', 'Longitude', gettext('Longitude'), false, 52, true, 'number'),
			'EXIFGPSLongitudeRef' => array('GPS', 'Longitude Reference', gettext('Longitude Reference'), false, 52, true, 'string'),
			'EXIFGPSAltitude' => array('GPS', 'Altitude', gettext('Altitude'), false, 52, true, 'number'),
			'EXIFGPSAltitudeRef' => array('GPS', 'Altitude Reference', gettext('Altitude Reference'), false, 52, true, 'string'),
			'IPTCOriginatingProgram' => array('IPTC', 'OriginatingProgram', gettext('Originating Program'), false, 32, true, 'string'),
			'IPTCProgramVersion' => array('IPTC', 'ProgramVersion', gettext('Program Version'), false, 10, true, 'string'),
			'VideoFormat' => array('VIDEO', 'fileformat', gettext('Video File Format'), false, 32, true, 'string'),
			'VideoSize' => array('VIDEO', 'filesize', gettext('Video File Size'), false, 32, true, 'number'),
			'VideoArtist' => array('VIDEO', 'artist', gettext('Video Artist'), false, 256, true, 'string'),
			'VideoTitle' => array('VIDEO', 'title', gettext('Video Title'), false, 256, true, 'string'),
			'VideoBitrate' => array('VIDEO', 'bitrate', gettext('Bitrate'), false, 32, true, 'number'),
			'VideoBitrate_mode' => array('VIDEO', 'bitrate_mode', gettext('Bitrate_Mode'), false, 32, true, 'string'),
			'VideoBits_per_sample' => array('VIDEO', 'bits_per_sample', gettext('Bits per sample'), false, 32, true, 'number'),
			'VideoCodec' => array('VIDEO', 'codec', gettext('Codec'), false, 32, true, 'string'),
			'VideoCompression_ratio' => array('VIDEO', 'compression_ratio', gettext('Compression Ratio'), false, 32, true, 'number'),
			'VideoDataformat' => array('VIDEO', 'dataformat', gettext('Video Dataformat'), false, 32, true, 'string'),
			'VideoEncoder' => array('VIDEO', 'encoder', gettext('File Encoder'), false, 10, true, 'string'),
			'VideoSamplerate' => array('VIDEO', 'Samplerate', gettext('Sample rate'), false, 32, true, 'number'),
			'VideoChannelmode' => array('VIDEO', 'channelmode', gettext('Channel mode'), false, 32, true, 'string'),
			'VideoFormat' => array('VIDEO', 'format', gettext('Format'), false, 10, true, 'string'),
			'VideoChannels' => array('VIDEO', 'channels', gettext('Channels'), false, 10, true, 'number'),
			'VideoFramerate' => array('VIDEO', 'framerate', gettext('Frame rate'), false, 32, true, 'number'),
			'VideoResolution_x' => array('VIDEO', 'resolution_x', gettext('X Resolution'), false, 32, true, 'number'),
			'VideoResolution_y' => array('VIDEO', 'resolution_y', gettext('Y Resolution'), false, 32, true, 'number'),
			'VideoAspect_ratio' => array('VIDEO', 'pixel_aspect_ratio', gettext('Aspect ratio'), false, 32, true, 'number'),
			'VideoPlaytime' => array('VIDEO', 'playtime_string', gettext('Play Time'), false, 10, true, 'number'),
			'XMPrating' => array('XMP', 'rating', gettext('XMP Rating'), false, 10, true, 'string'),
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
 * Recursively clears and removes a folder
 * @param string $path
 * @return boolean
 */
function removeDir($path, $within = false) {
	if (($dir = @opendir($path)) !== false) {
		while (($file = readdir($dir)) !== false) {
			if ($file != '.' && $file != '..') {
				if ((is_dir($path . '/' . $file))) {
					if (!removeDir($path . '/' . $file)) {
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
function tagURLs($text) {
	if (is_string($text) && preg_match('/^a:[0-9]+:{/', $text)) { //	serialized array
		$text = getSerializedArray($text);
		$serial = true;
	} else {
		$serial = false;
	}
	if (is_array($text)) {
		foreach ($text as $key => $textelement) {
			$text[$key] = tagURLs($textelement);
		}
		if ($serial) {
			$text = serialize($text);
		}
	} else if (is_string($text)) {
		$text = str_replace(WEBPATH, '{*WEBPATH*}', str_replace(FULLWEBPATH, '{*FULLWEBPATH*}', strval($text)));
	}
	return $text;
}

/**
 * reverses tagURLs()
 * @param string|array $text
 * @return string
 */
function unTagURLs($text) {
	if (is_string($text) && preg_match('/^a:[0-9]+:{/', $text)) { //	serialized array
		$text = getSerializedArray($text);
		$serial = true;
	} else {
		$serial = false;
	}
	if (is_array($text)) {
		foreach ($text as $key => $textelement) {
			$text[$key] = unTagURLs($textelement);
		}
		if ($serial) {
			$text = serialize($text);
		}
	} else if (is_string($text)) {
		$text = str_replace('{*WEBPATH*}', WEBPATH, str_replace('{*FULLWEBPATH*}', FULLWEBPATH, strval($text)));
	}
	return $text;
}

/**
 * Searches out i.php image links and replaces them with cache links if image is cached
 * @param string $text
 * @return string
 */
function updateImageProcessorLink($text) {
	if (is_string($text) && preg_match('/^a:[0-9]+:{/', $text)) { //	serialized array
		$text = getSerializedArray($text);
		$serial = true;
	} else {
		$serial = false;
	}
	if (is_array($text)) {
		foreach ($text as $key => $textelement) {
			$text[$key] = updateImageProcessorLink($textelement);
		}
		if ($serial) {
			$text = serialize($text);
		}
	} else {
		preg_match_all('|<\s*img.*?\ssrc\s*=\s*"([^"]*)?|', $text, $matches);
		foreach ($matches[1] as $key => $match) {
			preg_match('|.*i\.php\?(.*)|', $match, $imgproc);
			if ($imgproc) {
				$match = preg_split('~\&[amp;]*~', $imgproc[1]);
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
	}
	return $text;
}

function pluginDebug($extension, $priority, $start) {
	list($usec, $sec) = explode(" ", microtime());
	$end = (float) $usec + (float) $sec;
	$class = array();
	if ($priority & CLASS_PLUGIN) {
		$class[] = 'CLASS';
	}
	if ($priority & ADMIN_PLUGIN) {
		$class[] = 'ADMIN';
	}
	if ($priority & FEATURE_PLUGIN) {
		$class[] = 'FEATURE';
	}
	if ($priority & THEME_PLUGIN) {
		$class[] = 'THEME';
	}
	if (empty($class))
		$class[] = 'theme';
	debugLog(sprintf('    ' . $extension . '(%s:%u)=>%.4fs', implode('|', $class), $priority & PLUGIN_PRIORITY, $end - $start));
}

/**
 * Removes a trailing slash from a string if one exists, otherwise just returns the string
 * Used primarily within date and tag searches and news date archive results
 * 
 * @param string $string
 * @return string
 * @since 1.4.12
 */
function removeTrailingSlash($string) {
	if (substr($string, -1) == '/') {
		$length = strlen($string) - 1;
		return substr($string, 0, $length);
	}
	return $string;
}

/**
 * Returns an array the data privacy policy page and the data usage confirmation text as defined on Options > Security
 * array(
 * 	'notice' => '<The defined text>',
 * 	'url' => '<url to the define page either custom page url or Zenpage page>',
 * 	'linktext' => '<The defined text>'
 * )
 * 
 * @since 1.5
 * 
 * @return array
 */
function getDataUsageNotice() {
	$array = array('notice' => '', 'url' => '', 'linktext' => '');
	$array['linktext'] = get_language_string(getOption('dataprivacy_policy_customlinktext'));
	$array['notice'] = get_language_string(getOption('dataprivacy_policy_notice'));
	$custompage = trim(strval(getOption('dataprivacy_policy_custompage')));
	$zenpage_page = '';
	if (empty($array['notice'])) {
		$array['notice'] = gettext('By using this form you agree with the storage and handling of your data by this website.');
	}
	if (extensionEnabled('zenpage') && ZP_PAGES_ENABLED) {
		$zenpage_page = getOption('dataprivacy_policy_zenpage');
		if ($zenpage_page == 'none') {
			$zenpage_page = '';
		}
	}
	if (!empty($custompage)) {
		$array['url'] = $custompage;
	} else if (!empty($zenpage_page)) {
		$obj = new ZenpagePage($zenpage_page);
		$array['url'] = $obj->getLink();
	}
	if (empty($array['linktext'])) {
		$array['linktext'] = gettext('More info on our data privacy policy.');
	}
	return $array;
}

/**
 * Prints the data privacy policy page and the data usage confirmation text as defined on Options > Security
 * If there is no page defined it only prints the default text.
 * 
 * @since 1.5
 */
function printDataUsageNotice() {
	$data = getDataUsageNotice();
	echo $data['notice'];
	if (!empty($data['url'])) {
		printLinkHTML($data['url'], ' ' . $data['linktext'], $data['linktext'], null, null);
	}
}

/**
 * Returns an array with predefined info about general cookies set by the system and/or plugins
 * 
 * @since 1.5.8
 * 
 * @param string $section Name of the section to get: 'authentication', 'search', 'admin', 'cookie', 'various' or null (default) for the full array
 * @return array
 */
function getCookieInfoData($section = null) {
	$info = array(
			'authentication' => array(
					'sectiontitle' => gettext('Authentication'),
					'sectiondesc' => gettext('Cookies set if logging in as an admin or as one of the various guest user types.'),
					'cookies' => array(
							'zpcms_auth_user' => gettext('Stores the zenphoto user login credentials.'),
							'zpcms_auth_gallery' => gettext('Stores guest user gallery access credentias.'),
							'zpcms_auth_search' => gettext('Stores guest user search access credentials'),
							'zpcms_auth_image_itemid' => gettext('Stores guest user <em>image item</em> access credentials. <em>itemid</em> refers to the ID of the image.'),
							'zpcms_auth_album_itemid' => gettext('Stores guest user <em>album item</em> access credentials. <em>itemid</em> refers to the ID of the album.'),
							'zpcms_auth_category_itemid' => gettext('Stores guest user <em>category item</em> access credentials. <em>itemid</em> refers to the ID of the category.'),
							'zpcms_auth_page_itemid' => gettext('Stores guest user <em>page item</em> access credentials. <em>itemid</em> refers to the ID of the zenpage page.'),
							'zpcms_auth_download' => gettext('Stores guest user access used by the <em>downloadlist</em> plugin.')
					),
			),
			'search' => array(
					'sectiontitle' => gettext('Search context (frontend)'),
					'sectiondesc' => gettext('These cookies help keep the search result context while browsing results'),
					'cookies' => array(
							'zpcms_search_params' => gettext('Stores search parameters of the most recent search.'),
							'zpcms_search_lastalbum' => gettext('Stores the last album in search context.'),
							'zpcms_search_parent' => gettext('Stores the previous page within search context (either the main search results or an album result).')
					),
			),
			'admin' => array(
					'sectiontitle' => gettext('Administration'),
					'sectiondesc' => gettext('These are set on the backend to help editing.'),
					'cookies' => array(
							'zpcms_admin_gallery_nesting' => gettext('Stores the setting for the nested album list display on the backend.'),
							'zpcms_admin_subalbum_nesting' => gettext('Stores the setting for the nested subalbum list display on the backend.'),
							'zpcms_admin_imagestab_imagecount' => gettext('Stores the image count on the backend images pages.'),
							'zpcms_admin_uploadtype' => gettext('Stores the upload method on the backend.')
					),
			),
			'cookie' => array(
					'sectiontitle' => gettext('Cookie related'),
					'sectiondesc' => '',
					'cookies' => array(
							'zpcms_setup_testcookie' => gettext('Used by setup to test if cookies are operational on the installation. May store the Zenphoto version number of the last unsuccessful run.'),
							'zpcms_cookie_path' => gettext('Stores the path for cookies.')
					),
			),
			'various' => array(
					'sectiontitle' => gettext('Various'),
					'sectiondesc' => gettext('Various cookies set by plugins, themes or otherwise'),
					'cookies' => array(
							'zcms_ssl' => gettext('Stores the HTTPS/SSL setting.'),
							'zpcms_locale' => gettext('Stores the language selection set by the <em>dynamic_locale</em> plugin.'),
							'zpcms_mobiletheme' => gettext('Stores if the mobile theme is defined - used by the <em>mobileTheme</em> plugin.'),
							'zpcms_themeswitcher_theme' => gettext('Stores the current theme selected by the <em>themeSwitcher</em> plugin.'),
							'zpcms_comment' => gettext('Stores information from the comment form POST for re-populaton of the form in the <em>comment_form</em> plugin.')
					)
			)
	);
	if (is_null($section) && array_key_exists($section, $info)) {
		return $info[$section];
	} else {
		return $info;
	}
}

/**
 * Returns a definition list with predefined info about general cookies set by the system and/or plugins as a string
 * 
 * @since 1.5.8
 * 
 * @param string $section Name of the section to get: 'authentication', 'search', 'admin', 'cookie', 'various' or null (default) for the full array
 * @param string $sectionheadline Add h2 to h6 to print as the section headline, h2 default.
 * @return string
 */
function getCookieInfoHTML($section = null, $sectionheadline = 'h2') {
	$cookies = getCookieInfoData($section);
	$html = '';
	if ($cookies) {
		foreach ($cookies as $section) {
			if (!in_array($sectionheadline, array('h2', 'h3', 'h4', 'h5', 'h6'))) {
				$sectionheadline = 'h2';
			}
			$html .= '<' . $sectionheadline . '>' . $section['sectiontitle'] . '</' . $sectionheadline . '>';
			$html .= '<p>' . $section['sectiondesc'] . '</p>';
			if ($section['cookies']) {
				$html .= '<dl>';
				foreach ($section['cookies'] as $key => $val) {
					$html .= '<dt>' . $key . '</dt>';
					$html .= '<dd>' . $val . '</dd>';
				}
				$html .= '</dl>';
			}
		}
	}
	return $html;
}

/**
 * Prints a definition list with predefined info about general cookies set by the system and/or plugins
 * 
 * @since 1.5.8
 * 
 * @param string $section Name of the section to get: 'authentication', 'search', 'admin', 'cookie', 'various' or null (default) for the full array
 * @param string $sectionheadline Add h2 to h6 to print as the section headline, h2 default.
 */
function printCookieInfo($section = null, $sectionheadline = 'h2') {
	echo getCookieInfoHTML($section, $sectionheadline);
}

/**
 * Registers the content macro(s)
 * 
 * @param array $macros Passes through the array of already registered 
 * @return array
 */
function getCookieInfoMacro($macros) {
	$macros['COOKIEINFO'] = array(
			'class' => 'function',
			'params' => array('string*', 'string*'),
			'value' => 'getCookieInfoHTML',
			'owner' => 'core',
			'desc' => gettext('Set %1 to the section to get, set %2 to the h2-h6 for the headline element to use.')
	);
	return $macros;
}

/**
 * Gets a formatted metadata field value for display
 * 
 * @since 1.6.3
 * 
 * @param string $type The field type
 * @param mixed $value The field value
 * @param string $name The field name (Metadata Key)
 */
function getImageMetadataValue($type = '', $value = '', $name = '') {
	switch ($type) {
		case 'datetime':
			return zpFormattedDate(DATETIME_FORMAT, removeDateTimeZone($value));
		case 'date':
			return zpFormattedDate(DATE_FORMAT, removeDateTimeZone($value));
		case 'time':	
			return zpFormattedDate(TIME_FORMAT, removeDateTimeZone($value));
		default:
			if ($name == 'IPTCImageCaption') {
				return nl2br(html_decode($value));
			} else {
				return html_encode($value);
			}
	}
}

/**
 * Prints a formatted metadata field value
 * 
 * @since 1.6.3
 * 
 * @param string $type The field type
 * @param mixed $value The field value
 * @param string $name The field name (Metadata Key)
 */
function printImageMetadataValue($type, $value = '', $name = '') {
	echo getImageMetadataValue($type, $value, $name);
}

setexifvars();