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
require_once(dirname(__FILE__).'/functions-common.php');

if(!function_exists("gettext")) {
	require_once(dirname(__FILE__).'/lib-gettext/gettext.inc');
}

$_zp_mutex = new Mutex();

/**
* OFFSET_PATH definitions:
* 		0		root scripts (e.g. the root index.php)
* 		1		zp-core scripts
* 		2		setup scripts
* 		3		plugin scripts
* 		4		scripts in the theme folders
*/

global $_zp_conf_vars;
$const_webpath = str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']));
$const_serverpath = str_replace('\\','/',dirname($_SERVER['SCRIPT_FILENAME']));
/**
 * see if we are executing out of any of the known script folders. If so we know how to adjust the paths
 * if not we presume the script is in the root of the installation. If it is not the script better have set
 * the SERVERPATH and WEBPATH defines to the correct values
 */
if (!preg_match('~(.*)/('.ZENFOLDER.')~',$const_webpath, $matches)) {
	preg_match('~(.*)/('.USER_PLUGIN_FOLDER.'|'.THEMEFOLDER.')~',$const_webpath, $matches);
}
if ($matches) {
	$const_webpath = $matches[1];
	$const_serverpath = substr($const_serverpath,0,strrpos($const_serverpath,'/'.$matches[2]));
	if (!defined('OFFSET_PATH')) {
		switch ($matches[2]) {
			case ZENFOLDER:
				define('OFFSET_PATH', 1);
				break;
			case USER_PLUGIN_FOLDER:
				define('OFFSET_PATH', 3);
				break;
			case THEMEFOLDER:
				define('OFFSET_PATH', 4);
				break;
		}
	}
	unset($matches);
} else {
	if (!defined('OFFSET_PATH')) {
		define('OFFSET_PATH', 0);
	}
}
if ($const_webpath == '/' || $const_webpath == '.') {
	$const_webpath = '';
}

if (defined('SERVERPATH')) {
	$const_serverpath = SERVERPATH;
}


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

switch (PHP_MAJOR_VERSION) {
	case 5:
		switch (PHP_MINOR_VERSION) {
			case 0:
			case 1:
			case 2:
				define ('ENT_FLAGS',ENT_QUOTES);
				break;
			case 3:
				define ('ENT_FLAGS',ENT_QUOTES|ENT_IGNORE);
				break;
			default:	// 4 and beyond
				define ('ENT_FLAGS',ENT_QUOTES|ENT_SUBSTITUTE);
				break;
		}
		break;
	default:	// PHP 6?
		define ('ENT_FLAGS',ENT_QUOTES|ENT_SUBSTITUTE);
		break;
}

// Set error reporting.
if (TEST_RELEASE) {
	error_reporting(E_ALL | E_STRICT);
	@ini_set('display_errors', 1);
}
set_error_handler("zpErrorHandler");
set_exception_handler("zpErrorHandler");
if (OFFSET_PATH != 2 && !file_exists($const_serverpath.'/'.DATA_FOLDER."/zenphoto.cfg")) {
	require_once(dirname(__FILE__).'/reconfigure.php');
	reconfigureAction(true);
}
// Including the config file more than once is OK, and avoids $conf missing.
eval(file_get_contents($const_serverpath.'/'.DATA_FOLDER.'/zenphoto.cfg'));

if (!defined('WEBPATH')) {
	define('WEBPATH', $const_webpath);
}
unset($const_webpath);

if (!defined('SERVERPATH')) {
	define('SERVERPATH', $const_serverpath);
}
unset($const_serverpath);

if (OFFSET_PATH != 2 && empty($_zp_conf_vars['mysql_database'])) {
	require_once(dirname(__FILE__).'/reconfigure.php');
	reconfigureAction(true);
}

require_once(dirname(__FILE__).'/lib-utf8.php');

if (!defined('FILESYSTEM_CHARSET')) {
	if (isset($_zp_conf_vars['FILESYSTEM_CHARSET']) && $_zp_conf_vars['FILESYSTEM_CHARSET']!='unknown') {
		define('FILESYSTEM_CHARSET',$_zp_conf_vars['FILESYSTEM_CHARSET']);
	} else {
		define('FILESYSTEM_CHARSET', 'ISO-8859-1');
	}
}
if (!defined('CHMOD_VALUE')) {
	define('CHMOD_VALUE', 0666);
}
define('FOLDER_MOD',CHMOD_VALUE | 0311);
define('FILE_MOD', CHMOD_VALUE & 0666);

// If the server protocol is not set, set it to the default.
if (!isset($_zp_conf_vars['server_protocol'])) $_zp_conf_vars['server_protocol'] = 'http';

require_once(dirname(__FILE__).'/functions-db-'.(isset($_zp_conf_vars['db_software'])?$_zp_conf_vars['db_software']:'MySQL').'.php');
db_connect(false);

$_charset = getOption('charset');
if (!$_charset) {
	$_charset = 'UTF-8';
}
define('LOCAL_CHARSET',$_charset);
unset($_charset);

$data = getOption('gallery_data');
if ($data) {
	$data = unserialize($data);
} else {
	$data = array();
}
define('GALLERY_SESSION',@$data['album_session']);
define('GALLERY_SECURITY',@$data['gallery_security']);
unset($data);

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
	@mb_internal_encoding(LOCAL_CHARSET);
}

// load graphics libraries in priority order
// once a library has concented to load, all others will
// abdicate.
$_zp_graphics_optionhandlers = array();
if (getOption('use_imagick')) {
	require_once(dirname(__FILE__).'/lib-Imagick.php');
}
if (!function_exists('zp_graphicsLibInfo')) {
	require_once(dirname(__FILE__).'/lib-GD.php');
}
if (function_exists('zp_graphicsLibInfo')) {
	$_zp_supported_images = zp_graphicsLibInfo();
} else {
	$_zp_supported_images = array('Library'=>gettext('none'), 'Library_desc'=>NULL);
}
define('GRAPHICS_LIBRARY',$_zp_supported_images['Library']);
unset($_zp_supported_images['Library']);
unset($_zp_supported_images['Library_desc']);
foreach ($_zp_supported_images as $key=>$type) {
	unset($_zp_supported_images[$key]);
	if ($type) $_zp_supported_images[strtolower($key)] = true;
}
$_zp_supported_images = array_keys($_zp_supported_images);

require_once(dirname(__FILE__).'/lib-encryption.php');

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

if (!defined('COOKIE_PESISTENCE')) {
	$persistence = getOption('cookie_persistence');
	if (!$persistence) $persistence = 5184000;
	define('COOKIE_PESISTENCE', $persistence);
	unset($persistence);
}

define('SAFE_MODE',preg_match('#(1|ON)#i', ini_get('safe_mode')));
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
define('OPEN_IMAGE_CACHE', !getOption('protected_image_cache'));

define('DATE_FORMAT',getOption('date_format'));

define('IM_SUFFIX',getOption('mod_rewrite_image_suffix'));
define('UTF8_IMAGE_URI',getOption('UTF8_image_URI'));
define('MEMBERS_ONLY_COMMENTS',getOption('comment_form_members_only'));

define('HASH_SEED', getOption('extra_auth_hash_text'));
define('IP_TIED_COOKIES', getOption('IP_tied_cookies'));

/**
 * Decodes HTML Special Characters.
 *
 * @param string $text
 * @return string
 */

/**
 * encodes a pre-sanitized string to be used as a Javascript parameter
 *
 * @param string $this_string
 * @return string
 */
function js_encode($this_string) {
	global $_zp_UTF8;
	$this_string = preg_replace("/\r?\n/", "\\n", $this_string);
	$this_string = utf8::encode_javascript($this_string);
	return $this_string;
}

/**
 * Get a option stored in the database.
 * This function reads the options only once, in order to improve performance.
 * @param string $key the name of the option.
 */
function getOption($key) {
	global $_zp_conf_vars, $_zp_options;
	if (isset($_zp_options[$key])) {
		return $_zp_options[$key];
	} else {
		$v = NULL;
		if (is_null($_zp_options)) {
			// option table not yet loaded, load it (but not the theme options!)
			$sql = "SELECT `name`, `value` FROM ".prefix('options').' WHERE (`theme`="" OR `theme` IS NULL) AND `ownerid`=0';
			$optionlist = query_full_array($sql, false);
			if ($optionlist !== false) {
				$_zp_options = array();
				foreach($optionlist as $option) {
					$_zp_options[$option['name']] = $option['value'];
					if ($option['name']==$key) {
						$v = $option['value'];
					}
				}
			}
		}
	}
	return $v;
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
		$sql = 'INSERT INTO '.prefix('options').' (`name`,`ownerid`,`theme`,`value`) VALUES ('.db_quote($key).',0,"",';
		$sqlu = ' ON DUPLICATE KEY UPDATE `value`=';
		if (is_null($value)) {
			$sql .= 'NULL';
			$sqlu .= 'NULL';
		} else {
			$sql .= db_quote($value);
			$sqlu .= db_quote($value);
		}
		$sql .= ') '.$sqlu;
		$result = query($sql,false);
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
	$SERVERPATH = str_replace("\\", '/', dirname(dirname(__FILE__)));
	$creator = str_replace($SERVERPATH.'/', '', str_replace('\\', '/', $b['file']));
	$sql = 'INSERT INTO ' . prefix('options') . ' (`name`, `value`, `ownerid`, `theme`, `creator`) VALUES (' . db_quote($key) . ',';
	if (is_null($default)) {
		$sql .= 'NULL';
	} else {
		$sql .= db_quote($default);
	}
	$sql .= ',0,"",'.db_quote($creator).');';
	if (query($sql, false)) {
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
	if (NULL == $_zp_options) {
		getOption('nil'); // pre-load from the database
	}
	return $_zp_options;
}

/**
 * Returns true if the file has the dynamic album suffix
 *
 * @param string $path
 * @return bool
 */
function hasDynamicAlbumSuffix($path) {
	return getSuffix($path) == 'alb';
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
		$uri = getRequestURI();
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
			//	sanitize the path
			$ralbum = $path = sanitize_path($path);
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
				if (!$im_suffix && !(is_valid_image($rimage) || is_valid_other_type($rimage))) {
					$ralbum = $ralbum . '/' . $rimage;
					$albumpath = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($ralbum);
					if (!is_dir($albumpath)) {
						if (file_exists($albumpath.'.alb')) {
							$ralbum .= '.alb';
						}
					}
					$rimage = null;
				}
			} else {
				$albumpath = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($path);
				if (!is_dir($albumpath)) {
					if (file_exists($albumpath.'.alb')) {
						$path .= '.alb';
					}
				}
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
	$suffix = getOption('image_cache_suffix');
	if (empty($suffix)) {
		$suffix = getSuffix($image8);
	}
	if (!in_array($suffix, $_zp_supported_images) || $suffix=='jpeg') {
		$suffix = 'jpg';
	}
	$image = stripSuffix(internalToFilesystem($image8));
	// Set default variable values.
	$postfix = getImageCachePostfix($args);
	if (empty($album)) {
		$albumsep = '';
	} else {
		if (SAFE_MODE) {
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

	switch ($size) {
		case 0:
		default:
			if (empty($size) || !is_numeric($size)) {
				$size = false; // 0 isn't a valid size anyway, so this is OK.
			} else {
				$size = round($size);
			}
			break;
		case 'thumb':
			$thumb = true;
			if ($thumb_crop) {
				$cw = $thumb_crop_width;
				$ch = $thumb_crop_height;
			}
			$size = round($thumb_size);
			break;
		case 'default':
			$size = $image_default_size;
			break;
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
	$args =  array((int) $size, (int) $width, (int) $height, $cw, $ch, $cx, $cy, (int) $quality, (bool) $thumb, (bool) $crop, (bool) $thumbstandin, $WM, (bool) $adminrequest, $effects);
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
	$args[8] = NULL;	// not used by image processo
	$check = md5(HASH_SEED.implode($args));
	$uri = WEBPATH.'/'.ZENFOLDER.'/i.php?a='.pathurlencode($album).'&i='.urlencode($image);
	if (!empty($size)) $uri .= '&s='.$size;
	if (!empty($width)) $uri .= '&w='.$width;
	if (!empty($height)) $uri .= '&h='.$height;
	if (!is_null($crop) && $crop) $uri .= '&c=1';
	if ($cw) $uri .= '&cw='.$cw;
	if ($ch) $uri .= '&ch='.$ch;
	if (!is_null($cx)) $uri .= '&cx='.$cx;
	if (!is_null($cy)) $uri .= '&cy='.$cy;
	if (!empty($quality)) $uri .= '&q='.$quality;
	if ($thumb || $thumbstandin) $uri .= '&t=1';
	if (!empty($passedWM)) $uri .= '&wmk='.$passedWM;
	if (!empty($adminrequest)) $uri .= '&admin';
	if (!is_null($effects)) $uri .= '&effects='.$effects;
	$uri .= '&check='.$check;
	if (class_exists('static_html_cache')) {
		// don't cache pages that have image processor URIs
		static_html_cache::disable();
	}
	return $uri;
}

/**
 *
 * Returns an URI to the image:
 *
 * 	If the image is not cached, the uri will be to the image processor
 * 	If the image is cached then the uri will depend on the site option for
 *	cache serving. If the site is set for open cache the uri will point to
 *	the cached image. If the site is set for protected cache the uri will
 *	point to the image processor (which will serve the image from the cache.)
 *	NOTE: this latter implies added overhead for each and every image fetch!
 *
 * @param array $args
 * @param string $album the album name
 * @param string $image the image name
 * @param int $mitme mtime of the image
 * @return string
 */
function getImageURI($args, $album, $image, $mtime) {
	$cachefilename = getImageCacheFilename($album, $image, $args);
	if (OPEN_IMAGE_CACHE && file_exists(SERVERCACHE . $cachefilename) && (!$mtime || filemtime(SERVERCACHE . $cachefilename) >= $mtime)) {
		return WEBPATH . '/'.CACHEFOLDER . imgSrcURI($cachefilename);
	} else {
		return getImageProcessorURI($args,$album,$image);
	}
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

/**
 * Returns either the rewrite path or the plain, non-mod_rewrite path
 * based on the mod_rewrite option.
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
		if (class_exists('seo_locale')) {
			return seo_locale::localePath() . "/" . $path;
		} else {
			return WEBPATH . "/" . $path;
		}
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
	preg_match('|^(http[s]*\://[a-zA-Z0-9\-\.]+/?)*(.*)$|xis', $path, $matches);
	$parts = explode('?', $matches[2]);
	$link = implode("/", array_map("rawurlencode", explode("/", $parts[0])));
	if (count($parts)==2) {
		//	some kind of query link
		$link .= '?'.html_encode($parts[1]);
	}
	$link = $matches[1].$link;
	return $link;
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
	$root = str_replace('\\', '/', $root);
	switch (@$_zp_conf_vars['album_folder_class']) {
		default:
			$_zp_conf_vars['album_folder_class'] = 'std';
		case 'std':
			return $root . $_zp_album_folder;
		case 'in_webpath':
			if (WEBPATH) { 			// strip off the WEBPATH
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

/**
 * Rolls a log over if it has grown too large.
 *
 * @param string $log
 */
function switchLog($log) {
	$dir = getcwd();
	chdir(SERVERPATH . '/' . DATA_FOLDER);
	$list = safe_glob($log.'-*.log');
	if (empty($list)) {
		$counter = 1;
	} else {
		sort($list);
		$last = array_pop($list);
		preg_match('|'.$log.'-(.*).log|', $last, $matches);
		$counter = $matches[1]+1;
	}
	chdir($dir);
	@copy(SERVERPATH.'/'. DATA_FOLDER.'/'.$log.'.log',SERVERPATH.'/'. DATA_FOLDER.'/'.$log.'-'.$counter.'.log');
}

/**
 * Write output to the debug log
 * Use this for debugging when echo statements would come before headers are sent
 * or would create havoc in the HTML.
 * Creates (or adds to) a file named debug.log which is located in the zenphoto core folder
 *
 * @param string $message the debug information
 * @param bool $reset set to true to reset the log to zero before writing the message
 */
function debugLog($message, $reset=false) {
	global $_zp_mutex;
	$path = SERVERPATH . '/' . DATA_FOLDER . '/debug.log';
	$max = getOption('debug_log_size');
	$_zp_mutex->lock();
	if ($reset || ($size = @filesize($path)) == 0 || ($max && $size > $max)) {
		if ($size > 0 && !$reset) {
			switchLog('debug');
		}
		$f = fopen($path, 'w');
		if ($f) {
			if (!class_exists('zpFunctions') || zpFunctions::hasPrimaryScripts()) {
				$clone = '';
			} else {
				$clone = ' '.gettext('clone');
			}
			fwrite($f, '{'.gmdate('D, d M Y H:i:s')." GMT} Zenphoto v".ZENPHOTO_VERSION.'['.ZENPHOTO_RELEASE.']'.$clone."\n");
		}
	} else {
		$f = fopen($path, 'a');
		if ($f) {
			fwrite($f, '{'.gmdate('D, d M Y H:i:s')." GMT}\n");
		}
	}
	if ($f) {
		fwrite($f, "  ".$message . "\n");
		fclose($f);
		clearstatcache();
		if (defined('FILE_MOD')) {
			@chmod($path, FILE_MOD);
		}
	}
	$_zp_mutex->unlock();
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
 * Returns true if the file is an image
 *
 * @param string $filename the name of the target
 * @return bool
 */
function is_valid_image($filename) {
	global $_zp_supported_images;
	$ext = strtolower(substr(strrchr($filename, "."), 1));
	return in_array($ext, $_zp_supported_images);
}

/**
 * Returns true if the file is handled by a plugin object
 *
 * @param string $filename
 * @return bool
 */
function is_valid_other_type($filename) {
	global $_zp_extra_filetypes;
	$ext = strtolower(substr(strrchr($filename, "."), 1));
	if (array_key_exists($ext, $_zp_extra_filetypes)) {
		return $ext;
	} else {
		return false;
	}
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
	$like = ' LIKE '.db_quote(db_LIKE_escape($album));
	while (count($folders) > 0) {
		$album .= '/'.array_shift($folders);
		$like .= ' OR `folder` LIKE '.db_quote(db_LIKE_escape($album));
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
		$galleryoptions = unserialize(getOption('gallery_data'));
		$theme = @$galleryoptions['current_theme'];
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
	if (zp_loggedin(ALL_ALBUMS_RIGHTS) && ($action == LIST_RIGHTS)) {	// sees all
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
	return isset($_SERVER['HTTPS']) && strpos(strtolower($_SERVER['HTTPS']),'on')===0;
}

/**
 *
 * Returns the script requesting URI.
 * 	Uses $_SERVER[REQUEST_URI] if it exists, otherwise it concocts the URI from
 * 	$_SERVER[SCRIPT_NAME] and $_SERVER[QUERY_STRING]
 *
 * @return string
 */
function getRequestURI() {
	if (array_key_exists('REQUEST_URI', $_SERVER)) {
		$uri = $_SERVER['REQUEST_URI'];
		preg_match('|^(http[s]*\://[a-zA-Z0-9\-\.]+/?)*(.*)$|xis', $uri, $matches);
		$uri = $matches[2];
		if (!empty($matches[1])) {
			$uri = '/'.$uri;
		}
	} else {
		$uri = @$_SERVER['SCRIPT_NAME'];
		if (@$_SERVER['QUERY_STRING']) {
			$uri .= '?'.$_SERVER['QUERY_STRING'];
		}
	}
	return urldecode(sanitize(str_replace('\\','/',$uri),0));
}

/**
* Provide an alternative to glob which does not return filenames with accented charactes in them
*
* NOTE: this function ignores "hidden" files whose name starts with a period!
*
* @param string $pattern the 'pattern' for matching files
* @param bit $flags glob 'flags'
*/
function safe_glob($pattern, $flags=0) {
	$split=explode('/',$pattern);
	$match = '/^' . strtr(addcslashes(array_pop($split), '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i';
	$path_return = $path = implode('/',$split);
	if (empty($path)) {
		$path = '.';
	} else {
		$path_return = $path_return . '/';
	}
	if (!is_dir($path)) return array();
	if (($dir=opendir($path))!==false) {
		$glob=array();
		while(($file=readdir($dir))!==false) {
			if(@preg_match($match, $file) && $file{0}!='.') {
				if ((is_dir("$path/$file"))||(!($flags&GLOB_ONLYDIR))) {
					if ($flags&GLOB_MARK) $file.='/';
					$glob[]=$path_return.$file;
				}
			}
		}
		closedir($dir);
		if (!($flags&GLOB_NOSORT)) sort($glob);
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
	if ((!($i = getOption('zenphoto_install')) || (getOption('zenphoto_release') != ZENPHOTO_VERSION.'['.ZENPHOTO_RELEASE.']') || ((time() & 7)==0) && OFFSET_PATH!=2 && $i != serialize(installSignature()))) {
		require_once(dirname(__FILE__).'/reconfigure.php');
		reconfigureAction(false);
	}
}

/**
 *
 * Call when terminating a script.
 * Closes the database to be sure that we do not build up outstanding connections
 */
function exitZP() {
	IF (function_exists('db_close')) db_close();
	exit();
}

/**
 *
 * Computes the "installation signature" of the Zenphoto install
 * @return string
 */
function installSignature() {
	$testFiles = array(	'template-functions.php'=>filesize(SERVERPATH.'/'.ZENFOLDER.'/template-functions.php'),
											'functions-filter.php'=>filesize(SERVERPATH.'/'.ZENFOLDER.'/functions-filter.php'),
											'lib-auth.php'=>filesize(SERVERPATH.'/'.ZENFOLDER.'/lib-auth.php'),
											'lib-utf8.php'=>filesize(SERVERPATH.'/'.ZENFOLDER.'/lib-utf8.php'),
											'functions.php'=>filesize(SERVERPATH.'/'.ZENFOLDER.'/functions.php'),
											'functions-basic.php'=>filesize(SERVERPATH.'/'.ZENFOLDER.'/functions-basic.php'),
											'functions-controller.php'=>filesize(SERVERPATH.'/'.ZENFOLDER.'/functions-controller.php'),
											'functions-image.php'=>filesize(SERVERPATH.'/'.ZENFOLDER.'/functions-image.php'));

	if (isset($_SERVER['SERVER_SOFTWARE'])) {
		$s = $_SERVER['SERVER_SOFTWARE'];
	} else {
		$s = 'software unknown';
	}
	$dbs = db_software();
	return array_merge($testFiles,
											array('SERVER_SOFTWARE'=>$s,
														'ZENPHOTO'=>ZENPHOTO_VERSION.'['.ZENPHOTO_RELEASE.']',
														'FOLDER'=>dirname(SERVERPATH.'/'.ZENFOLDER),
														'DATABASE'=>$dbs['application'].' '.$dbs['version']
														)
				);
}

/**
 *
 * Starts a zenphoto session (perhaps a secure one)
 */
function zp_session_start() {
	if (session_id() == '') {
		// force session cookie to be secure when in https
		if(secureServer()) {
			$CookieInfo=session_get_cookie_params();
			session_set_cookie_params($CookieInfo['lifetime'],$CookieInfo['path'], $CookieInfo['domain'],TRUE);
		}
		session_start();
	}
}

?>
