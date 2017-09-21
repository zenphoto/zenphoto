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
require_once(dirname(__FILE__) . '/functions-common.php');

switch (PHP_MAJOR_VERSION) {
	case 5:
		switch (PHP_MINOR_VERSION) {
			case 0:
			case 1:
			case 2:
				define('ENT_FLAGS', ENT_QUOTES);
				break;
			case 3:
				define('ENT_FLAGS', ENT_QUOTES | ENT_IGNORE);
				break;
			default: // 4 and beyond
				define('ENT_FLAGS', ENT_QUOTES | ENT_SUBSTITUTE);
				break;
		}
		break;
	default: // PHP 6?
		define('ENT_FLAGS', ENT_QUOTES | ENT_SUBSTITUTE);
		break;
}

// Set error reporting.
if (TEST_RELEASE) {
	error_reporting(E_ALL | E_STRICT);
	@ini_set('display_errors', 1);
}

set_error_handler("zpErrorHandler");
set_exception_handler("zpErrorHandler");
register_shutdown_function('zpShutDownFunction');
$_configMutex = new zpMutex('cF');
$_zp_mutex = new zpMutex();

$_zp_conf_vars = array('db_software' => 'NULL', 'mysql_prefix' => '_', 'charset' => 'UTF-8', 'UTF-8' => 'utf8');
// Including the config file more than once is OK, and avoids $conf missing.
if (file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
	@eval('?>' . file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE));
	if (!isset($_zp_conf_vars['UTF-8']) || $_zp_conf_vars['UTF-8'] === true) {
		$_zp_conf_vars['UTF-8'] = 'utf8';
	}
	define('DATA_MOD', fileperms(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE) & 0777);
} else {
	define('DATA_MOD', 0777);
}
define('DATABASE_PREFIX', $_zp_conf_vars['mysql_prefix']);
define('LOCAL_CHARSET', $_zp_conf_vars['charset']);
if (!isset($_zp_conf_vars['special_pages'])) {
	$_zp_conf_vars['special_pages'] = array();
}

if (!defined('CHMOD_VALUE')) {
	define('CHMOD_VALUE', fileperms(dirname(__FILE__)) & 0666);
}
define('FOLDER_MOD', CHMOD_VALUE | 0311);
define('FILE_MOD', CHMOD_VALUE & 0666);

if (OFFSET_PATH != 2) {
	if (!file_exists(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE)) {
		_setup(11);
	} else if (!file_exists(dirname(__FILE__) . '/functions-db-' . $_zp_conf_vars['db_software'] . '.php')) {
		_setup(12);
	}
}

require_once(dirname(__FILE__) . '/lib-utf8.php');

if (!defined('FILESYSTEM_CHARSET')) {
	if (isset($_zp_conf_vars['FILESYSTEM_CHARSET']) && $_zp_conf_vars['FILESYSTEM_CHARSET'] != 'unknown') {
		define('FILESYSTEM_CHARSET', $_zp_conf_vars['FILESYSTEM_CHARSET']);
	} else {
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
			define('FILESYSTEM_CHARSET', 'ISO-8859-1');
		} else {
			define('FILESYSTEM_CHARSET', 'UTF-8');
		}
	}
}

// If the server protocol is not set, set it to the default.
if (!isset($_zp_conf_vars['server_protocol'])) {
	$_zp_conf_vars['server_protocol'] = 'http';
}

if (!defined('DATABASE_SOFTWARE') && (extension_loaded(strtolower($_zp_conf_vars['db_software'])) || $_zp_conf_vars['db_software'] == 'NULL')) {
	require_once(dirname(__FILE__) . '/functions-db-' . $_zp_conf_vars['db_software'] . '.php');
	$data = db_connect(array_intersect_key($_zp_conf_vars, array('db_software' => '', 'mysql_user' => '', 'mysql_pass' => '', 'mysql_host' => '', 'mysql_database' => '', 'mysql_prefix' => '', 'UTF-8' => '')), false);
	if ($data) {
		$software = db_software();
		define('MySQL_VERSION', $software['version']);
	}
} else {
	$data = false;
}
if (!defined('MySQL_VERSION')) {
	define('MySQL_VERSION', '0.0.0');
}

if (!$data && OFFSET_PATH != 2) {
	_setup(13);
}

primeOptions();
define('SITE_LOCALE', getOption('locale'));

$data = getOption('gallery_data');
if ($data) {
	$data = getSerializedArray($data);
} else {
	$data = array();
}
define('GALLERY_SESSION', @$data['album_session']);
define('GALLERY_SECURITY', @$data['gallery_security']);
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
	@ini_set('memory_limit', '96M');
}

// Set the internal encoding
@ini_set('default_charset', LOCAL_CHARSET);
if (function_exists('mb_internal_encoding')) {
	@mb_internal_encoding(LOCAL_CHARSET);
}

// load graphics libraries in priority order
// once a library has concented to load, all others will
// abdicate.
$_zp_graphics_optionhandlers = array();
$try = array('lib-GD.php', 'lib-NoGraphics.php');
if (getOption('use_imagick')) {
	array_unshift($try, 'lib-Imagick.php');
}
while (!function_exists('zp_graphicsLibInfo')) {
	require_once(dirname(__FILE__) . '/' . array_shift($try));
}
$_zp_cachefileSuffix = zp_graphicsLibInfo();


define('GRAPHICS_LIBRARY', $_zp_cachefileSuffix['Library']);
unset($_zp_cachefileSuffix['Library']);
unset($_zp_cachefileSuffix['Library_desc']);
$_zp_supported_images = $_zp_images_classes = array();
foreach ($_zp_cachefileSuffix as $key => $type) {
	if ($type) {
		$_zp_images_classes[$_zp_supported_images[] = strtolower($key)] = 'Image';
	}
}

require_once(dirname(__FILE__) . '/lib-encryption.php');

//NOTE: SERVER_PROTOCOL is the option PROTOCOL is what should be used in links!!!!
define('SERVER_PROTOCOL', getOption('server_protocol'));
switch (SERVER_PROTOCOL) {
	case 'https':
		define('PROTOCOL', 'https');
		break;
	default:
		if (secureServer()) {
			define('PROTOCOL', 'https');
		} else {
			define('PROTOCOL', 'http');
		}
		break;
}

if (!defined('COOKIE_PESISTENCE')) {
	$persistence = getOption('cookie_persistence');
	if (!$persistence)
		$persistence = 5184000;
	define('COOKIE_PESISTENCE', $persistence);
	unset($persistence);
}
if ($c = getOption('zenphoto_cookie_path')) {
	define('COOKIE_PATH', $c);
} else {
	define('COOKIE_PATH', WEBPATH);
}

define('SAFE_MODE', preg_match('#(1|ON)#i', ini_get('safe_mode')));
define('FULLHOSTPATH', PROTOCOL . "://" . $_SERVER['HTTP_HOST']);
define('FULLWEBPATH', FULLHOSTPATH . WEBPATH);
define('SAFE_MODE_ALBUM_SEP', '__');
define('SERVERCACHE', SERVERPATH . '/' . CACHEFOLDER);
define('MOD_REWRITE', getOption('mod_rewrite'));

define('DEBUG_LOG_SIZE', getOption('debug_log_size'));

define('ALBUM_FOLDER_WEBPATH', getAlbumFolder(WEBPATH));
define('ALBUM_FOLDER_SERVERPATH', getAlbumFolder(SERVERPATH));
define('ALBUM_FOLDER_EMPTY', getAlbumFolder(''));

define('IMAGE_WATERMARK', getOption('fullimage_watermark'));
define('FULLIMAGE_WATERMARK', getOption('fullsizeimage_watermark'));
define('THUMB_WATERMARK', getOption('Image_watermark'));
define('OPEN_IMAGE_CACHE', !getOption('protected_image_cache'));
define('IMAGE_CACHE_SUFFIX', getOption('image_cache_suffix'));

define('DATE_FORMAT', getOption('date_format'));

define('IM_SUFFIX', getOption('mod_rewrite_image_suffix'));
define('UNIQUE_IMAGE', getOption('unique_image_prefix') && IM_SUFFIX && MOD_REWRITE);
define('UTF8_IMAGE_URI', getOption('UTF8_image_URI'));
define('MEMBERS_ONLY_COMMENTS', getOption('comment_form_members_only'));

define('HASH_SEED', getOption('extra_auth_hash_text'));
define('IP_TIED_COOKIES', getOption('IP_tied_cookies'));

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

function primeOptions() {
	global $_zp_options;
	if (function_exists('query_full_array')) { //	incase we are in primitive mode
		$sql = "SELECT LCASE(`name`) as name, `value` FROM " . prefix('options') . ' WHERE (`theme`="" OR `theme` IS NULL) AND `ownerid`=0';
		$optionlist = query_full_array($sql, false);
		if ($optionlist !== false) {
			$_zp_options = array();
			foreach ($optionlist as $option) {
				$_zp_options[$option['name']] = $option['value'];
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
 * checks if there is a file with the prefix and one of the
 * handled suffixes. Returns the found suffix
 *
 * @param type $path SERVER path to be tested
 * @return string
 */
function isHandledAlbum($path) {
	global $_zp_albumHandlers;
	foreach (array_keys($_zp_albumHandlers) as $suffix) {
		if (file_exists($path . '.' . $suffix)) {
//	it is a handled album sans suffix
			return $suffix;
		}
	} return NULL;
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
			$path = internalToFilesystem(getAlbumFolder(SERVERPATH) . $ralbum);
			if (IM_SUFFIX) { // require the rewrite have the suffix as well
				if (preg_match('|^(.*)' . preg_quote(IM_SUFFIX) . '$|', $ralbum, $matches)) {
//has an IM_SUFFIX attached
					$rimage = basename($matches[1]);
					$ralbum = trim(dirname($matches[1]), '/');
					$path = internalToFilesystem(getAlbumFolder(SERVERPATH) . $ralbum);
				}
			} else { //	have to figure it out
				if (Gallery::imageObjectClass($ralbum)) { //	it is an image request
					$rimage = basename($ralbum);
					$ralbum = trim(dirname($ralbum), '/');
					$path = internalToFilesystem(getAlbumFolder(SERVERPATH) . $ralbum);
				}
			}
			if (!is_dir($path)) {
				if ($suffix = isHandledAlbum($path)) { //	it is a dynamic album sans suffix
					$ralbum .= '.' . $suffix;
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
 * Returns an i.php "image name" for an image not within the albums structure
 *
 * @param string $image Path to the image
 * @return string
 */
function makeSpecialImageName($image) {
	$filename = basename($image);
	$base = explode('/', replaceScriptPath(dirname($image)));
	$sourceFolder = array_shift($base);
	$sourceSubfolder = implode('/', $base);
	return array('source' => $sourceFolder . '/' . $sourceSubfolder . '/' . $filename, 'name' => $sourceFolder . '_' . basename($sourceSubfolder) . '_' . $filename);
}

define('NO_WATERMARK', '!');

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
	if (!empty($watermark_use_image) && ($image->getWMUse() & $use)) { //	Use the image defined watermark
		return $watermark_use_image;
	}
	$id = NULL;
	$album = $image->album;
	if ($use & (WATERMARK_FULL)) { //	watermark for the full sized image
		$watermark_use_image = getAlbumInherited($album->name, 'watermark', $id);
		if (empty($watermark_use_image)) {
			$watermark_use_image = FULLIMAGE_WATERMARK;
		}
	} else {
		if ($use & (WATERMARK_IMAGE)) { //	watermark for the image
			$watermark_use_image = getAlbumInherited($album->name, 'watermark', $id);
			if (empty($watermark_use_image)) {
				$watermark_use_image = IMAGE_WATERMARK;
			}
		} else {
			if ($use & WATERMARK_THUMB) { //	watermark for the thumb
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
	return NO_WATERMARK; //	apply no watermark
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

	$uri .= '&check=' . sha1(HASH_SEED . serialize($args));

	$uri = zp_apply_filter('image_processor_uri', $uri, $args, $album, $image);

	return $uri;
}

// Don't let anything get above this, to save the server from burning up...
define('MAX_SIZE', getOption('image_max_size'));

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
	if (OPEN_IMAGE_CACHE && file_exists(SERVERCACHE . $cachefilename) && (!$mtime || filemtime(SERVERCACHE . $cachefilename) >= $mtime)) {
		return WEBPATH . '/' . CACHEFOLDER . imgSrcURI($cachefilename);
	} else {
		return getImageProcessorURI($args, $album, $image);
	}
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
 * @param bool $webpath host path to be prefixed. If "false" is passed you will get a localized "WEBPATH"
 * @return string
 */
function rewrite_path($rewrite, $plain, $webpath = NULL) {
	if (is_null($webpath)) {
		if (defined('LOCALE_TYPE') && LOCALE_TYPE == 1) {
			$webpath = seo_locale::localePath();
		} else {
			$webpath = WEBPATH;
		}
	}
	if (MOD_REWRITE) {
		$path = $rewrite;
	} else {
		$path = $plain;
	}
	if ($path && $path{0} == "/") {
		$path = substr($path, 1);
	}
	return $webpath . "/" . $path;
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
 * rawurlencode function that is path-safe (does not encode /)
 *
 * @param string $path URL
 * @return string
 */
function pathurlencode($path) {
	$parts = parse_url($path);
	if (isset($parts['query'])) {
//	some kind of query link
		$pairs = parse_query($parts['query']);
		$parts['query'] = http_build_query($pairs);
	}
	if (array_key_exists('path', $parts))
		$parts['path'] = implode("/", array_map("rawurlencode", explode("/", $parts['path'])));
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

/** getAlbumArray - returns an array of folder names corresponding to the
 *     given album string.
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
 * Checks access for the album root
 *
 * @param bit $action what the caller wants to do
 *
 */
function accessAllAlbums($action) {
	global $_zp_admin_album_list, $_zp_loggedin;
	if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
		if (zp_loggedin($action))
			return true;
	}
	if (zp_loggedin(ALL_ALBUMS_RIGHTS) && ($action == LIST_RIGHTS)) { // sees all
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
 *
 * @author Stephen Billard
 * @Copyright 2015 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 */
function requestSetup($whom) {
	$sig = getSerializedArray(getOption('zenphoto_install'));
	$sig['REQUESTS'][$whom] = $whom;
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
 * Closes the database to be sure that we do not build up outstanding connections
 */
function exitZP() {
	if (version_compare(phpversion(), '7', '>=')) {
		error_clear_last(); //	it will be handled here, not on shutdown!
	}
	if (function_exists('db_close')) {
		db_close();
	}
	exit();
}

?>
