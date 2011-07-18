<?php
/**
 * basic functions used by zenphoto i.php
 * Keep this file to the minimum to allow the largest available memory for processing images!
 * Headers not sent yet!
 * @package functions
 *
 */

// force UTF-8 Ø
require_once(dirname(__FILE__).'/global-definitions.php');
if(!function_exists("gettext")) {
	require_once(dirname(__FILE__).'/lib-gettext/gettext.inc');
}

global $_zp_conf_vars;

// Contexts (Bitwise and combinable)
define("ZP_INDEX",   1);
define("ZP_ALBUM",   2);
define("ZP_IMAGE",   4);
define("ZP_COMMENT", 8);
define("ZP_SEARCH", 16);
define("ZP_SEARCH_LINKED", 32);
define("ZP_ALBUM_LINKED", 64);
define('ZP_IMAGE_LINKED', 128);
define('ZP_ZENPAGE_NEWS_ARTICLE', 256);
define('ZP_ZENPAGE_NEWS_CATEGORY', 512);
define('ZP_ZENPAGE_NEWS_DATE', 1024);
define('ZP_ZENPAGE_PAGE', 2048);
define('ZP_ZENPAGE_SINGLE', 4096);

// Set error reporting.
if (defined("RELEASE")) {
	error_reporting(E_ALL ^E_NOTICE);
} else {
	if (version_compare(PHP_VERSION,'5.0.0') == 1) {
		error_reporting(E_ALL | E_STRICT);
	} else {
		error_reporting(E_ALL);
	}
}
$_zp_error = false;

require_once(dirname(__FILE__).'/lib-utf8.php');

if (!file_exists(dirname(dirname(__FILE__)) . '/' . DATA_FOLDER . "/zp-config.php")) {
	if (file_exists(dirname(dirname(__FILE__)).'/'.ZENFOLDER.'/setup.php')) {
		$dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
		if (substr($dir, -1) == '/') $dir = substr($dir, 0, -1);
		$location = "http://". $_SERVER['HTTP_HOST']. $dir . "/" . ZENFOLDER . "/setup.php";
		header("Location: $location" );
	} else {
		die('Zenphoto needs to run setup but the setup scripts missing. Please reinstall the setup scripts.');
	}
}
// Including zp-config.php more than once is OK, and avoids $conf missing.
require(dirname(dirname(__FILE__)).'/'.DATA_FOLDER.'/zp-config.php');

if (!defined('FILESYSTEM_CHARSET')) {
	if (isset($_zp_conf_vars['FILESYSTEM_CHARSET']) && $_zp_conf_vars['FILESYSTEM_CHARSET']!='unknown') {
		define('FILESYSTEM_CHARSET',$_zp_conf_vars['FILESYSTEM_CHARSET']);
	} else {
		define('FILESYSTEM_CHARSET', 'ISO-8859-1');
	}
}
if (!defined('CHMOD_VALUE')) { define('CHMOD_VALUE', 0777); }
if (!defined('OFFSET_PATH')) { define('OFFSET_PATH', 0); }
if (!defined('COOKIE_PESISTENCE')) { define('COOKIE_PESISTENCE', 5184000); }

// If the server protocol is not set, set it to the default (obscure zp-config.php change).
if (!isset($_zp_conf_vars['server_protocol'])) $_zp_conf_vars['server_protocol'] = 'http';

$_zp_imagick_present = false;
require_once(dirname(__FILE__).'/functions-db-'.(isset($_zp_conf_vars['db_software'])?$_zp_conf_vars['db_software']:'MySQL').'.php');
db_connect();
$_charset = getOption('charset');
if (!$_charset) {
	$_charset = 'UTF-8';
}

define ('GALLERY_DATA', getOption('gallery_data'));
if (GALLERY_DATA) {
	$data = unserialize(GALLERY_DATA);
} else {
	$data = array();
}
if (isset($data['album_session'])) {
	define('GALLERY_SESSION',$data['album_session']);
} else {
	define('GALLERY_SESSION',getOption('album_session'));
}
if (isset($data['gallery_security']))	{
	define('GALLERY_SECURITY',$data['gallery_security']);
} else {
	define('GALLERY_SECURITY',getOption('gallery_security'));
}
unset($data);

define('LOCAL_CHARSET',$_charset);

unset($_charset);
// insure a correct timezone
if (function_exists('date_default_timezone_set')) {
	$level = error_reporting(0);
	$_zp_server_timezone = date_default_timezone_get();
	date_default_timezone_set($_zp_server_timezone);
	@ini_set('date.timezone', $_zp_server_timezone);
	error_reporting($level);
}
// Set the memory limit higher just in case -- suppress errors if user doesn't have control.
// 100663296 bytes = 96M
if (ini_get('memory_limit') && parse_size(ini_get('memory_limit')) < 100663296) {
	@ini_set('memory_limit','96M');
}
// Set the internal encoding
if (function_exists('mb_internal_encoding')) {
	if (mb_internal_encoding() != LOCAL_CHARSET) {
		@mb_internal_encoding(LOCAL_CHARSET);
	}
}

// load graphics libraries in priority order
// once a library has concented to load, all others will
// abdicate.
$_zp_graphics_optionhandlers = array();
if (version_compare(PHP_VERSION, '5.0.0') == 1) {
	require_once(dirname(__FILE__).'/lib-Imagick.php');
}
require_once(dirname(__FILE__).'/lib-GD.php');

if (function_exists('zp_graphicsLibInfo')) {
	$_zp_supported_images = zp_graphicsLibInfo();
	define('GRAPHICS_LIBRARY',$_zp_supported_images['Library']);
	unset($_zp_supported_images['Library']);
	unset($_zp_supported_images['Library_desc']);
	foreach ($_zp_supported_images as $key=>$type) {
		unset($_zp_supported_images[$key]);
		if ($type) $_zp_supported_images[strtolower($key)] = true;
	}
	$_zp_supported_images = array_keys($_zp_supported_images);
} else {
	$_zp_supported_images = array();
}

require_once(dirname(__FILE__).'/lib-encryption.php');

switch (OFFSET_PATH) {
	case 0:	// starts from the root index.php
		$const_webpath = dirname($_SERVER['SCRIPT_NAME']);
		break;
	case 1:	// starts from the zp-core folder
	case 2:	// things which do not need admin tabs (setup, image processor scripts, etc.)
		$const_webpath = dirname(dirname($_SERVER['SCRIPT_NAME']));
		break;
	case 3: // starts from the plugins folder
		$const_webpath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
		break;
	case 4: // starts from within a folder within the plugins folder
		$const_webpath = dirname(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))));
		break;
	case 5: // $const_webpath provided by the "loading function"
		break;
}
$const_webpath = str_replace("\\", '/', $const_webpath);
if ($const_webpath == '/') $const_webpath = '';
if (!defined('WEBPATH')) { define('WEBPATH', $const_webpath); }
unset($const_webpath);
if (!defined('SERVERPATH')) define('SERVERPATH', str_replace("\\", '/', dirname(dirname(__FILE__))));
define('SERVER_PROTOCOL', getOption('server_protocol'));
switch (SERVER_PROTOCOL) {
	case 'https':
	define('PROTOCOL', 'https');
	break;
	default:
	if(secureServer()) {
		define('PROTOCOL', 'https');
	} else {
		define('PROTOCOL', 'http');
	}
	break;
}
define('FULLWEBPATH', PROTOCOL."://" . $_SERVER['HTTP_HOST'] . WEBPATH);
define('SAFE_MODE_ALBUM_SEP', '__');
define('SERVERCACHE', SERVERPATH . '/'.CACHEFOLDER);
define('MOD_REWRITE', getOption('mod_rewrite'));
define('ALBUM_FOLDER_WEBPATH', getAlbumFolder(WEBPATH));
define('ALBUM_FOLDER_SERVERPATH', getAlbumFolder(SERVERPATH));
define('ALBUM_FOLDER_EMPTY',getAlbumFolder(''));

define('IMAGE_WATERMARK',getOption('fullimage_watermark'));
define('FULLIMAGE_WATERMARK',getOption('fullsizeimage_watermark'));
define('THUMB_WATERMARK',getOption('Image_watermark'));

define('DATE_FORMAT',getOption('date_format'));

define('IM_SUFFIX',getOption('mod_rewrite_image_suffix'));
define('UTF8_IMAGE_URI',getOption('UTF8_image_URI'));
define('MEMBERS_ONLY_COMMENTS',getOption('comment_form_members_only'));

define('HASH_SEED', getOption('extra_auth_hash_text'));
define('IP_TIED_COOKIES', getOption('IP_tied_cookies'));
// Set the version number.
$_zp_conf_vars['version'] = ZENPHOTO_VERSION;

/**
 * Decodes HTML Special Characters.
 *
 * @param string $text
 * @param string $quote_style
 * @return string
 */

function zp_html_decode($string, $quote_style = ENT_QUOTES) {
	$translation_table = get_html_translation_table(HTML_SPECIALCHARS, $quote_style);
	$translation_table["'"] = '&#039;';
	return (strtr($string, array_flip($translation_table)));
}


/**
 * encodes a pre-sanitized string to be used in an HTML text-only field (value, alt, title, etc.)
 *
 * @param string $this_string
 * @return string
 */
function html_encode($this_string) {
	return htmlspecialchars($this_string, ENT_QUOTES, LOCAL_CHARSET);
}

/**
 * encodes a pre-sanitized string to be used in a Javascript alert box
 *
 * @param string $this_string
 * @return string
 */
function js_encode($this_string) {
	global $_zp_UTF8;
	$this_string = preg_replace("/\r?\n/", "\\n", $this_string);
	$this_string = $_zp_UTF8->encode_javascript($this_string);
	return $this_string;
}

/**
 * Get a option stored in the database.
 * This function reads the options only once, in order to improve performance.
 * @param string $key the name of the option.
 * @param bool $db set to true to force retrieval from the database.
 */
function getOption($key, $db=false) {
	global $_zp_conf_vars, $_zp_options, $_zp_optionDB_hasownerid;
	if (is_null($_zp_options)) {
		$sql = "SELECT `name`, `value` FROM ".prefix('options').' WHERE `ownerid`=0';
		$optionlist = query_full_array($sql, false);
		if ($optionlist == false) { // might be old, un-migrated option table during setup--retry without the `ownerid`.
			$sql = "SELECT `name`, `value` FROM ".prefix('options');
			$optionlist = query_full_array($sql, false);
		}
		if ($optionlist !== false) {
			$_zp_options = array();
			foreach($optionlist as $option) {
				$_zp_options[$option['name']] = $option['value'];
			}
		}
	} else {
		if ($db) {
			$sql = "SELECT `value` FROM ".prefix('options')." WHERE `name`=".db_quote($key)." AND `ownerid`=0";
			$optionlist = query_single_row($sql);
			return $optionlist['value'];
		}
	}
	if (is_array($_zp_options) && array_key_exists($key, $_zp_options)) {
		return $_zp_options[$key];
	} else {
		if (array_key_exists($key, $_zp_conf_vars)) {
			return $_zp_conf_vars[$key];
		}
	}
	return NULL;
}

/**
 * Stores an option value.
 *
 * @param string $key name of the option.
 * @param mixed $value new value of the option.
 * @param bool $persistent set to false if the option is stored in memory only
 * otherwise it is preserved in the database
 */
function setOption($key, $value, $persistent=true) {
	global $_zp_conf_vars, $_zp_options;
	if ($persistent) {
		$result = query_single_row("SELECT `value` FROM ".prefix('options')." WHERE `name`='".$key."' AND `ownerid`=0", false);
		if (is_array($result) && array_key_exists('value', $result)) { // option already exists.
			if (is_null($value)) {
				$sql = "UPDATE " . prefix('options') . " SET `value`=NULL WHERE `name`=" . db_quote($key) ." AND `ownerid`=0";
			} else {
				$sql = "UPDATE " . prefix('options') . " SET `value`=" . db_quote($value) . " WHERE `name`=" . db_quote($key) ." AND `ownerid`=0";
			}
			$result = query($sql, false);
		} else {
			if (is_null($value)) {
				$sql = "INSERT INTO " . prefix('options') . " (`name`, `value`, `ownerid`) VALUES (" . db_quote($key) . ",NULL, 0)";
			} else {
				$sql = "INSERT INTO " . prefix('options') . " (`name`, `value`, `ownerid`) VALUES (" . db_quote($key) . "," . db_quote($value) . ", 0)";
			}
			$result = query($sql, false);
		}
	} else {
		$result = true;
	}
	if ($result) {
		$_zp_options[$key] = $value;
		return true;
	} else {
		return false;
	}
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
	$bt = debug_backtrace();
	$b = array_shift($bt);
	$creator = str_replace(str_replace("\\", '/', dirname(dirname(__FILE__))).'/', '', str_replace('\\', '/', $b['file']));
	$sql = 'SELECT * FROM '.prefix('options').' WHERE `name`='.db_quote($key).' AND `ownerid`=0';
	$result = query_single_row($sql, false);
	if ($result) {
		if (empty($result['creator'])) {
			$sql = 'UPDATE '.prefix('options').' SET `creator`='.db_quote($creator).' WHERE `name`='.db_quote($key).' AND `ownerid`=0';
			query($sql, false);
		}
	} else {
		if (is_null($default)) {
			$sql = "INSERT INTO " . prefix('options') . " (`name`, `value`, `ownerid`, `creator`) VALUES (" . db_quote($key) . ", NULL,
							0, ".db_quote($creator).");";
		} else {
			$sql = "INSERT INTO " . prefix('options') . " (`name`, `value`, `ownerid`, `creator`) VALUES (" . db_quote($key) . ", ".
			db_quote($default) . ", 0, ".db_quote($creator).");";
		}
		query($sql, false);
		$_zp_options[$key] = $default;
	}
}

function purgeOption($key) {
	global $_zp_options;
	unset($_zp_options[$key]);
	$sql = 'DELETE FROM '.prefix('options').' WHERE `name`='.db_quote($key);
	query($sql, false);
}

/**
 * Retuns the option array
 *
 * @return array
 */
function getOptionList() {
	global $_zp_options;
	if (NULL == $_zp_options) { getOption('nil'); } // pre-load from the database
	return $_zp_options;
}

// Set up assertions for debugging.
assert_options(ASSERT_ACTIVE, 0);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_QUIET_EVAL, 1);
/**
 * Emits an assertion error
 *
 * @param string $file the script file
 * @param string $line the line of the assertion
 * @param string $code the error message
 */
function assert_handler($file, $line, $code) {
	dmesg(gettext("ERROR: Assertion failed in")." [$file:$line]: $code");
}
// Set up assertion callback
assert_options(ASSERT_CALLBACK, 'assert_handler');

/**
 * Returns true if the file has the dynamic album suffix
 *
 * @param string $path
 * @return bool
 */
function hasDynamicAlbumSuffix($path) {
	return strtolower(substr(strrchr($path, "."), 1)) == 'alb';
}


/**
 * rewrite_get_album_image - Fix special characters in the album and image names if mod_rewrite is on:
 * This is redundant and hacky; we need to either make the rewriting completely internal,
 * or fix the bugs in mod_rewrite. The former is probably a good idea.
 *
 *  Old explanation:
 *    rewrite_get_album_image() parses the album and image from the requested URL
 *    if mod_rewrite is on, and replaces the query variables with corrected ones.
 *    This is because of bugs in mod_rewrite that disallow certain characters.
 *
 * @param string $albumvar "$_GET" parameter for the album
 * @param string $imagevar "$_GET" parameter for the image
 */
function rewrite_get_album_image($albumvar, $imagevar) {
	//	initialize these. If not mod_rewrite, then they are fine. If so, they may be overwritten
	$ralbum = isset($_GET[$albumvar]) ? sanitize_path($_GET[$albumvar]) : null;
	$rimage = isset($_GET[$imagevar]) ? sanitize_path($_GET[$imagevar]) : null;
	if (MOD_REWRITE) {
		$uri = urldecode(sanitize($_SERVER['REQUEST_URI'], 0));
		$path = substr($uri, strlen(WEBPATH)+1);
		$scripturi = sanitize($_SERVER['PHP_SELF'],0);
		$script = substr($scripturi,strpos($scripturi, WEBPATH.'/')+strlen(WEBPATH)+1);
		// Only extract the path when the request doesn't include the running php file (query request).
		if (strlen($path) > 0 && strpos($uri, $script) === false && isset($_GET[$albumvar])) {
			// remove query string if present
			$qspos = strpos($path, '?');
			if ($qspos !== false) {
				$path = substr($path, 0, $qspos);
			}
			// Strip off the image suffix (could interfere with the rest, needs to go anyway).
			$im_suffix = getOption('mod_rewrite_image_suffix');
			$suf_len = strlen($im_suffix);
			if ($suf_len > 0 && substr($path, -($suf_len)) == $im_suffix) {
				$path = substr($path, 0, -($suf_len));
			} else {
				$im_suffix = false;
			}
			//	remove trailing slash
			if (substr($path, -1, 1) == '/') {
				$path = substr($path, 0, -1);
			}
			$ralbum = $path;
			//strip off things discarded by the rewrite rules
			$pagepos  = strpos($path, '/page/');
			$slashpos = strrpos($path, '/');
			$imagepos = strpos($path, '/image/');
			$albumpos = strpos($path, '/album/');
			if ($imagepos !== false) {
				$ralbum = substr($path, 0, $imagepos);
				$rimage = substr($path, $slashpos+1);
			} else if ($albumpos !== false) {
				$ralbum = substr($path, 0, $albumpos);
				$rimage = substr($path, $slashpos+1);
			} else if ($pagepos !== false) {
				$ralbum = substr($path, 0, $pagepos);
				$rimage = null;
			} else if ($slashpos !== false) {
				$ralbum = substr($path, 0, $slashpos);
				$rimage = substr($path, $slashpos+1);
				//	check if it might be an album, not an album/image form
				if (!$im_suffix && (hasDynamicAlbumSuffix($rimage) || (is_dir(ALBUM_FOLDER_SERVERPATH . internalToFilesystem($ralbum . '/' . $rimage))))) {
					$ralbum = $ralbum . '/' . $rimage;
					$rimage = null;
				}
			} else {
				$ralbum = $path;
				$rimage = null;
			}
			if (empty($ralbum)) {
				if (isset($_GET[$albumvar])) unset($_GET[$albumvar]);
			} else {
				$_GET[$albumvar] = $ralbum;
			}
			if (empty($rimage)) {
				if (isset($_GET[$imagevar])) unset($_GET[$imagevar]);
			} else {
				$_GET[$imagevar] = $rimage;
			}
			return array($ralbum, $rimage);
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
	global $_zp_supported_images;
	// this function works in FILESYSTEM_CHARSET, so convert the file names
	$album = internalToFilesystem($album8);
	$suffix = getSuffix($image8);
	if (!in_array($suffix, $_zp_supported_images) || $suffix=='jpeg') {
		$suffix = 'jpg';
	}
	$image = stripSuffix(internalToFilesystem($image8));
	// Set default variable values.
	$postfix = getImageCachePostfix($args);
	if (empty($album)) {
		$albumsep = '';
	} else {
		if (ini_get('safe_mode')) {
			$albumsep = SAFE_MODE_ALBUM_SEP;
			$album = str_replace(array('/',"\\"), $albumsep, $album);
		} else {
			$albumsep = '/';
		}
	}
	if (getOption('obfuscate_cache')) {
		$result = '/' . $album . $albumsep . sha1($image. HASH_SEED . $postfix) . '.'.$suffix;
	} else {
		$result = '/' . $album . $albumsep . $image . $postfix . '.'.$suffix;
	}
	return $result;
}

define('NO_WATERMARK','!');
/**
 * Returns the watermark image to pass to i.php
 *
 * Note: this should be used for "real" images only since thumbnail handling for Video and TextObjects is special
 * and the "album" thumbnail is not appropriate for the "default" images for those
 *
 * @param $image image object in question
 * @param $use what the watermark use is
 * @return string
 */
function getWatermarkParam($image, $use) {
	$watermark_use_image = $image->getWatermark();
	if (!empty($watermark_use_image) && ($image->getWMUse() & $use)) {	//	Use the image defined watermark
		return $watermark_use_image;
	}
	$id = NULL;
	$album = $image->album;
	if ($use & (WATERMARK_FULL)) {	//	watermark for the full sized image
		$watermark_use_image = getAlbumInherited($album->name, 'watermark', $id);
		if (empty($watermark_use_image)) {
			$watermark_use_image = FULLIMAGE_WATERMARK;
		}
	} else {
		if ($use & (WATERMARK_IMAGE)) {	//	watermark for the image
			$watermark_use_image = getAlbumInherited($album->name, 'watermark', $id);
			if (empty($watermark_use_image)) {
				$watermark_use_image = IMAGE_WATERMARK;
			}
		} else {
			if ($use & WATERMARK_THUMB) {	//	watermark for the thumb
				$watermark_use_image = getAlbumInherited($album->name, 'watermark_thumb', $id);
				if (empty($watermark_use_image)) {
					$watermark_use_image = THUMB_WATERMARK;
				}
			}
		}
	}
	if (!empty($watermark_use_image)) {
		return $watermark_use_image;
	}
	return NO_WATERMARK;	//	apply no watermark
}

/**
 * Returns the crop/sizing string to postfix to a cache image
 *
 * @param array $args cropping arguments
 * @return string
 */
function getImageCachePostfix($args) {
	list($size, $width, $height, $cw, $ch, $cx, $cy, $quality, $thumb, $crop, $thumbStandin, $passedWM, $adminrequest, $effects) = $args;
	$postfix_string = ($size ? "_$size" : "") . ($width ? "_w$width" : "")
	. ($height ? "_h$height" : "") . ($cw ? "_cw$cw" : "") . ($ch ? "_ch$ch" : "")
	. (is_numeric($cx) ? "_cx$cx" : "") . (is_numeric($cy) ? "_cy$cy" : "")
	. ($thumb || $thumbStandin ? '_thumb' : '')
	. ($adminrequest ? '_admin' : '')
	. (($passedWM && $passedWM != NO_WATERMARK) ? '_'.$passedWM : '')
	. ($effects ? '_'.$effects : '');
	return $postfix_string;
}


/**
 * Validates and edits image size/cropping parameters
 *
 * @param array $args cropping arguments
 * @return array
 */
function getImageParameters($args, $album=NULL) {
	$thumb_crop = getOption('thumb_crop');
	$thumb_size = getOption('thumb_size');
	$thumb_crop_width = getOption('thumb_crop_width');
	$thumb_crop_height = getOption('thumb_crop_height');
	$thumb_quality = getOption('thumb_quality');
	$image_default_size = getOption('image_size');
	$quality = getOption('image_quality');
	// Set up the parameters
	$thumb = $crop = false;
	@list($size, $width, $height, $cw, $ch, $cx, $cy, $quality, $thumb, $crop, $thumbstandin, $WM, $adminrequest, $effects) = $args;
	$thumb = $thumbstandin;
	if ($size == 'thumb') {
		$thumb = true;
		if ($thumb_crop) {
			$cw = $thumb_crop_width;
			$ch = $thumb_crop_height;
		}
		$size = round($thumb_size);
	} else {
		if ($size == 'default') {
			$size = $image_default_size;
		} else if (empty($size) || !is_numeric($size)) {
			$size = false; // 0 isn't a valid size anyway, so this is OK.
		} else {
			$size = round($size);
		}
	}

	// Round each numeric variable, or set it to false if not a number.
	list($width, $height, $cw, $ch, $quality) =	array_map('sanitize_numeric', array($width, $height, $cw, $ch, $quality));
	if (!is_null($cx)) {
		$cx = sanitize_numeric($cx);
	}
	if (!is_null($cy)) {
		$cy = sanitize_numeric($cy);
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
	if (empty($quality)) {
		if ($thumb) {
			$quality = round($thumb_quality);
		} else {
			$quality = getOption('image_quality');
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
	$args =  array($size, $width, $height, $cw, $ch, $cx, $cy, $quality, $thumb, $crop, $thumbstandin, $WM, $adminrequest, $effects);
	return $args;
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
	$uri = WEBPATH.'/'.ZENFOLDER.'/i.php?a='.pathurlencode($album).'&i='.urlencode($image);
	if (!empty($size)) $uri .= '&s='.$size;
	if (!empty($width)) $uri .= '&w='.$width;
	if (!empty($height)) $uri .= '&h='.$height;
	if (!is_null($crop) && $crop) $uri .= '&c=1';
	if (!is_null($cw)) $uri .= '&cw='.$cw;
	if (!is_null($ch)) $uri .= '&ch='.$ch;
	if (!is_null($cx)) $uri .= '&cx='.$cx;
	if (!is_null($cy)) $uri .= '&cy='.$cy;
	if (!empty($quality)) $uri .= '&q='.$quality;
	if ($thumb || $thumbstandin) $uri .= '&t=1';
	if (!empty($passedWM)) $uri .= '&wmk='.$passedWM;
	if (!empty($adminrequest)) $uri .= '&admin';
	if (!is_null($effects)) $uri .= '&effects='.$effects;
	return $uri;
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
	if (get_magic_quotes_gpc()) $filename = stripslashes($filename);
	$filename = strip_tags(str_replace('\\', '/', $filename));
	$filename = preg_replace(array('/x00/','/^\/+/','/\/+$/','/\/\/+/','/\/\.\./','/\/\./'), '', $filename);
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
			$output_string[$output_key] = sanitize_string($output_value, $sanitize_level);
		}
	} else {
		$output_string = sanitize_string($input_string, $sanitize_level);
	}

	return $output_string;
}

/**
 *
 * Returns an array of html tags allowed
 * @param string $which either 'allowed_tags' or 'style_tags' depending on which is wanted.
 */
function getAllowedTags($which) {
	global $_user_tags, $_style_tags;
	if ($which == 'allowed_tags') {
		if (is_null($_user_tags)) {
			$user_tags = "(".getOption('allowed_tags').")";
			$allowed_tags = parseAllowedTags($user_tags);
			if ($allowed_tags === false) {  // someone has screwed with the 'allowed_tags' option row in the database, but better safe than sorry
				$allowed_tags = array();
			}
			$_user_tags = $allowed_tags;
		}
		return $_user_tags;
	} else {
		if (is_null($_style_tags)) {
			$style_tags = "(".getOption('style_tags').")";
			$allowed_tags = parseAllowedTags($style_tags);
			if ($allowed_tags === false) {  // someone has screwed with the 'style_tags' option row in the database, but better safe than sorry
				$allowed_tags = array();
			}
			$_style_tags = $allowed_tags;
		}
		return $_style_tags;
	}
}

/** returns a sanitized string for the sanitize function
 * @param string $input_string
 * @param string $sanitize_level
 * @return string the sanitized string.
 */
function sanitize_string($input_string, $sanitize_level) {
	global $_user_tags, $_style_tags;
	// Strip slashes if get_magic_quotes_gpc is enabled.
	if (get_magic_quotes_gpc()) {
		$input_string = stripslashes($input_string);
	}
	// Basic sanitation.
	if ($sanitize_level === 0) {
		return str_replace(chr(0), " ", $input_string);
	}
	// User specified sanititation.
	if (function_exists('kses')) {
		switch($sanitize_level) {
			case 1:
				$allowed_tags = getAllowedTags('allowed_tags');
				$input_string = html_entity_decode(kses($input_string, $allowed_tags));
				break;

				// Text formatting sanititation.
			case 2:
				$allowed_tags = getAllowedTags('style_tags');
				$input_string = html_entity_decode(kses($input_string, $allowed_tags));
				break;
				// Full sanitation.  Strips all code.
			case 3:
				$allowed_tags = array();
				$input_string = html_entity_decode(kses($input_string, $allowed_tags));
				break;
		}
	} else {	//	in a basic environment--allow NO HTML tags.
		$input_string = strip_tags($input_string);
	}
	return $input_string;
}

/**
 * Formats an error message
 * If DEBUG_ERROR is set, supplies the calling sequence
 *
 * @param string $message
 * @param bool $fatal set true to fail the script
 */
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
 * Returns either the rewrite path or the plain, non-mod_rewrite path
 * based on the mod_rewrite option in zp-config.php.
 * The given paths can start /with or without a slash, it doesn't matter.
 *
 * IDEA: this function could be used to specially escape items in
 * the rewrite chain, like the # character (a bug in mod_rewrite).
 *
 * This is here because it's used in both template-functions.php and in the classes.
 * @param string $rewrite is the path to return if rewrite is enabled. (eg: "/myalbum")
 * @param string $plain is the path if rewrite is disabled (eg: "/?album=myalbum")
 * @param bool $webpath true if you want the WEBPATH to be returned, false if you want to generate a partly path. A trailing "/" is always added.
 * @return string
 */
function rewrite_path($rewrite, $plain, $webpath=true) {
	$path = null;
	if (MOD_REWRITE) {
		$path = $rewrite;
	} else {
		$path = $plain;
	}
	if (substr($path, 0, 1) == "/"  && $webpath) $path = substr($path, 1);
	if($webpath) {
		return WEBPATH . "/" . $path;
	} else {
		return $path;
	}
}

/**
 * rawurlencode function that is path-safe (does not encode /)
 *
 * @param string $path URL
 * @return string
 */
function pathurlencode($path) {
	$link = explode('?', $path);
	if (count($link)==2) {	//	some kind of query link
		return implode("/", array_map("rawurlencode", explode("/", $link[0]))).'?'.html_encode($link[1]);
	}
	return implode("/", array_map("rawurlencode", explode("/", $path)));
}


/**
 * Return human readable sizes
 * From: http://aidan.dotgeek.org/lib/
 *
 * @param       int    $size        Size
 * @param       int    $unit        The maximum unit
 * @param       int    $retstring   The return string format
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.1.0
 */
function size_readable($size, $unit = null, $retstring = null)
{
	// Units
	$sizes = array('B', 'KB', 'MB', 'GB', 'TB');
	$ii = count($sizes) - 1;

	// Max unit
	$unit = array_search((string) $unit, $sizes);
	if ($unit === null || $unit === false) {
		$unit = $ii;
	}

	// Return string
	if ($retstring === null) {
		$retstring = '%01.2f %s';
	}

	// Loop
	$i = 0;
	while ($unit != $i && $size >= 1024 && $i < $ii) {
		$size /= 1024;
		$i++;
	}

	return sprintf($retstring, $size, $sizes[$i]);
}

/**
 * Returns the fully qualified path to the album folders
 *
 * @param string $root the base from whence the path dereives
 * @return sting
 */
function getAlbumFolder($root=SERVERPATH) {
	global $_zp_album_folder, $_zp_conf_vars;
	if (is_null($_zp_album_folder)) {
		if (!isset($_zp_conf_vars['external_album_folder']) || empty($_zp_conf_vars['external_album_folder'])) {
			if (!isset($_zp_conf_vars['album_folder']) || empty($_zp_conf_vars['album_folder'])) {
				$_zp_album_folder = $_zp_conf_vars['album_folder'] = '/'.ALBUMFOLDER.'/';
			} else {
				$_zp_album_folder = str_replace('\\', '/', $_zp_conf_vars['album_folder']);
			}
		} else {
			$_zp_conf_vars['album_folder_class'] = 'external';
			$_zp_album_folder =  str_replace('\\', '/', $_zp_conf_vars['external_album_folder']);
		}
		if (substr($_zp_album_folder, -1) != '/') $_zp_album_folder .= '/';
	}
	switch (@$_zp_conf_vars['album_folder_class']) {
		default:
			$_zp_conf_vars['album_folder_class'] = 'std';
		case 'std':
			$root = str_replace('\\', '/', $root);
			return $root . $_zp_album_folder;
		case 'in_webpath':
			if (WEBPATH) { 			// strip off the WEBPATH
				$root = str_replace('\\', '/', $root);
				$root = str_replace(WEBPATH, '', $root);
				if ($root == '/') {
					$root = '';
				}
			}
			return $root . $_zp_album_folder;
		case 'external':
			return $_zp_album_folder;
	}
}

function get_caller_method() {
	$traces = @debug_backtrace();
	if (isset($traces[2]))     {
		return $traces[2]['function'];
	}
	return null;
}

/**
 * Write output to the debug log
 * Use this for debugging when echo statements would come before headers are sent
 * or would create havoc in the HTML.
 * Creates (or adds to) a file named debug_log.txt which is located in the zenphoto core folder
 *
 * @param string $message the debug information
 * @param bool $reset set to true to reset the log to zero before writing the message
 */
function debugLog($message, $reset=false) {
	global $_zp_debug_written;
	$path = dirname(dirname(__FILE__)) . '/' . DATA_FOLDER . '/debug_log.txt';
	if ($reset || ($size = @filesize($path)) == 0 || $size > 5000000) {
		$f = fopen($path, 'w');
		if ($f) {
			fwrite($f, '{'.gmdate('D, d M Y H:i:s')." GMT} Zenphoto v".ZENPHOTO_VERSION.'['.ZENPHOTO_RELEASE."]\n");
		}
	} else {
		$f = fopen($path, 'a');
		if ($f) {
			if ((time()-$_zp_debug_written)>5) {
				fwrite($f, '{'.gmdate('D, d M Y H:i:s')." GMT}\n");
			}
		}
	}
	if ($f) {
		fwrite($f, "  ".$message . "\n");
		fclose($f);
		clearstatcache();
		chmod($path, 0600);
	}
	$_zp_debug_written = time();
}

/**
 * "print_r" equivalent for the debug log
 *
 * @param string $name the name (or message) to display for the array
 * @param array $source
 */
function debugLogArray($name, $source, $indent=0, $trail='') {
	if (is_array($source)) {
		$msg = str_repeat(' ', $indent)."$name => ( ";
		$c = 1;
		if (count($source) > 0) {
			foreach ($source as $key => $val) {
				if (strlen($msg) > 72) {
					debugLog($msg);
					$msg = str_repeat(' ', $indent);
				}
				if (is_array($val)) {
					if (!empty($msg)) {
						debugLog($msg);
					}
					$c++;
					if ($c<count($source)){
						$t = '';
					} else {
						$t = ',';
					}
					debugLogArray($key, $val, $indent+5, $t);
					$msg = '';
				} else {
					if (is_null($val)) {
						$msg .= $key.' => NULL, ';
					} else {
						$msg .= $key . " => " . $val. ', ';
					}
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

/**
 * Logs the calling stack
 *
 * @param string $message Message to prefix the backtrace
 */
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
		$date = false;
		if (isset($b['file']) && isset($b['line'])) {
			$line = basename($b['file'])	. ' [' . $b['line'] . "]";
		} else {
			$line = 'unknown';
		}
	}
	if (!empty($line)) {
		debugLog($prefix.' from '.$line);
	}
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
	debugLog($message);
	debugLog(html_entity_decode(strip_tags($str)));
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
		$delta = ' ('.($now - $_zp_timer).')';
	}
	$_zp_timer = microtime(true);
	debugLogBacktrace($point.' '.$now.$delta);
}

/**
 * Makes directory recursively, returns TRUE if exists or was created sucessfuly.
 * Note: PHP5 includes a recursive parameter to mkdir, but PHP4 does not, so this
 *   is required to target PHP4.
 * @param string $pathname The directory path to be created.
 * @return boolean TRUE if exists or made or FALSE on failure.
 */

function mkdir_recursive($pathname, $mode=0777) {
	if (!is_dir(dirname($pathname))) mkdir_recursive(dirname($pathname), $mode);
	return is_dir($pathname) || @mkdir($pathname, $mode & CHMOD_VALUE);
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

/** getAlbumArray - returns an array of folder names corresponding to the
 *     given album string.
 * @param string $albumstring is the path to the album as a string. Ex: album/subalbum/my-album
 * @param string $includepaths is a boolean whether or not to include the full path to the album
 *    in each item of the array. Ex: when $includepaths==false, the above array would be
 *    ['album', 'subalbum', 'my-album'], and with $includepaths==true,
 *    ['album', 'album/subalbum', 'album/subalbum/my-album']
 *  @return array
 */
function getAlbumArray($albumstring, $includepaths=false) {
	if ($includepaths) {
		$array = array($albumstring);
		while($slashpos = strrpos($albumstring, '/')) {
			$albumstring = substr($albumstring, 0, $slashpos);
			array_unshift($array, $albumstring);
		}
		return $array;
	} else {
		return explode('/', $albumstring);
	}
}

/**
 * Returns true if the file is a valid 'other' type
 *
 * @param string $filename the name of the target
 * @return bool
 */
function is_valid_other($filename) {
	global $_zp_extra_filetypes;
	$ext = strtolower(substr(strrchr($filename, "."), 1));
	return isset($_zp_extra_filetypes[$ext]);
}

/**
 * Returns true if we are running on a Windows server
 *
 * @return bool
 */
function isWin() {
	return (strtoupper (substr(PHP_OS, 0,3)) == 'WIN' ) ;
}

/**
 * Returns an img src URI encoded based on the OS of the server
 *
 * @param string $uri uri in FILESYSTEM_CHARSET encoding
 * @return string
 */
function imgSrcURI($uri) {
	if (UTF8_IMAGE_URI) return filesystemToInternal($uri);
	return $uri;
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
	return str_replace(strrchr($filename, "."),'',$filename);
}
/**
 * Returns the Require string for the appropriate script based on the PHP version
 *
 * @param string $v The version dermarkation
 * @param string $script the script name
 * @return string
 */
function PHPScript($v, $script) {
	return dirname(__FILE__).'/'.(version_compare(PHP_VERSION, $v) == 1?'PHP5':'PHP4').'_functions/'.$script;
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
	$folders = explode('/',filesystemToInternal($folder));
	$album = array_shift($folders);
	$like = ' LIKE "'.$album.'"';
	while (count($folders) > 0) {
		$album .= '/'.array_shift($folders);
		$like .= ' OR `folder` LIKE '.db_quote($album);
	}
	$sql = 'SELECT `id`, `'.$field.'` FROM '.prefix('albums').' WHERE `folder`'.$like;
	$result = query_full_array($sql);
	if (!is_array($result)) return '';
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
function themeSetup($album) {
	// we need to conserve memory in i.php so loading the classes is out of the question.
	$id = NULL;
	$theme = getAlbumInherited(filesystemToInternal($album), 'album_theme', $id);
	if (empty($theme)) {
		$galleryoptions = serialize(getOption('gallery_data'));
		$theme = $galleryoptions['current_theme'];
	}
	loadLocalOptions($id, $theme);
	return $theme;
}

/**
 * Loads option table with album/theme options
 *
 * @param int $albumid
 * @param string $theme
 */
function loadLocalOptions($albumid, $theme) {
	//raw theme options
	$sql = "SELECT `name`, `value` FROM ".prefix('options').' WHERE `theme`='.db_quote($theme).' AND `ownerid`=0';
	$optionlist = query_full_array($sql, false);
	if ($optionlist !== false) {
		foreach($optionlist as $option) {
			setOption($option['name'], $option['value'], false);
		}
	}
	if ($albumid) {
		//album-theme options
		$sql = "SELECT `name`, `value` FROM ".prefix('options').' WHERE `theme`='.db_quote($theme).' AND `ownerid`='.$albumid;
		$optionlist = query_full_array($sql, false);
		if ($optionlist !== false) {
			foreach($optionlist as $option) {
				setOption($option['name'], $option['value'], false);
			}
		}
	}
}

/**
 * Checks access for the album root
 *
 * @param bit $action what the caller wants to do
 *
 */
function accessAllAlbums($action) {
	global $_zp_admin_album_list, $_zp_loggedin;
	if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
		if (zp_loggedin($action)) return true;
	}
	if (zp_loggedin(VIEW_ALBUMS_RIGHTS) && ($action == LIST_RIGHTS)) {	// sees all
		return $_zp_loggedin;
	}
	return false;
}

/**
 * Returns the path to a watermark
 *
 * @param string $wm watermark name
 * @return string
 */
function getWatermarkPath($wm) {
	$path = SERVERPATH . '/' . ZENFOLDER . '/watermarks/' . internalToFilesystem($wm).'.png';
	if (!file_exists($path)) {
		$path = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/watermarks/' . internalToFilesystem($wm).'.png';
	}
	return $path;
}

/**
 * Checks to see if access was through a secure protocol
 *
 * @return bool
 */
function secureServer() {
	if(isset($_SERVER['HTTPS']) && strpos(strtolower($_SERVER['HTTPS']),'on')===0) {
		return true;
	}
	return false;
}

/**
 *
 * creates an unique signature for the installation
 * @return string
 */
function installSignature() {
	if (isset($_SERVER['SERVER_ADMIN'])) {
		$t1 = $_SERVER['SERVER_ADMIN'];
	} else {
		$t1 = '';
	}
	$t1 .= ZENPHOTO_RELEASE.filesize(__FILE__);
	if (isset($_SERVER['DOCUMENT_ROOT'])) {
		$t1 .= $_SERVER['DOCUMENT_ROOT'];
	}
	$t1 = sha1($t1);
	$id = "{"	.substr($t1, 0, 8).'-'
						.substr($t1, 8, 4).'-'
						.substr($t1,12, 4).'-'
						.substr($t1,16, 4).'-'
						.substr($t1,20,12)."}";
	return $id;
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

function getServerOS() {
	ob_start();
	phpinfo(INFO_GENERAL);
	$phpinfo = ob_get_contents();
	ob_end_clean();
	$i = strpos($phpinfo,'<td class="v">');
	$j = strpos($phpinfo,'</td>',$i);
	$osinfo = strtolower(substr($phpinfo, $i+14,$j-$i-14));
	$ostokens = explode(' ', $osinfo);
	$os = array_shift($ostokens);
	return $os;
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

?>
