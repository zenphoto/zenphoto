<?php

/**
 * handles the watermarking and protecting of the full image link
 * @package core
 */
// force UTF-8 Ø
if (!defined('OFFSET_PATH'))
	define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . "/functions.php");
require_once(dirname(__FILE__) . "/functions-image.php");

$disposal = getOption('protect_full_image');
if ($disposal == 'No access') { // illegal use of the script!
	imageError('403 Forbidden', gettext("Forbidden"));
} else {
	if (isset($_GET['dsp'])) {
		$disposal = sanitize($_GET['dsp']);
	}
}
// Check for minimum parameters.
if (!isset($_GET['a']) || !isset($_GET['i'])) {
	imageError('404 Not Found', gettext("Too few arguments! Image not found."), 'err-imagenotfound.png');
}

list($album8, $image8) = rewrite_get_album_image('a', 'i');
$album = internalToFilesystem($album8);
$image = internalToFilesystem($image8);

/* Prevent hotlinking to the full image from other domains. */
if (getOption('hotlink_protection') && isset($_SERVER['HTTP_REFERER'])) {
	preg_match('|(.*)//([^/]*)|', $_SERVER['HTTP_REFERER'], $matches);
	if (preg_replace('/^www\./', '', strtolower($_SERVER['SERVER_NAME'])) != preg_replace('/^www\./', '', strtolower($matches[2]))) {
		/* It seems they are directly requesting the full image. */
		header('Location: ' . FULLWEBPATH . '/index.php?album=' . $album8 . '&image=' . $image8);
		exitZP();
	}
}

$albumobj = newAlbum($album8);
$imageobj = newImage($albumobj, $image8);
$args = getImageArgs($_GET);
$args[0] = 'FULL';
$adminrequest = $args[12];

if ($forbidden = getOption('image_processor_flooding_protection') && (!isset($_GET['check']) || $_GET['check'] != sha1(HASH_SEED . serialize($args)))) {
	// maybe it was from the tinyZenpage javascript which does not know better!
	zp_session_start();
	$forbidden = !isset($_SESSION['adminRequest']) || $_SESSION['adminRequest'] != @$_COOKIE['zp_user_auth'];
}

$args[0] = 'FULL';

$hash = getOption('protected_image_password');
if (($hash || !$albumobj->checkAccess()) && !zp_loggedin(VIEW_FULLIMAGE_RIGHTS)) {
	//	handle password form if posted
	zp_handle_password('zp_image_auth', getOption('protected_image_password'), getOption('protected_image_user'));
	//check for passwords
	$authType = 'zp_image_auth';
	$hint = get_language_string(getOption('protected_image_hint'));
	$show = getOption('protected_image_user');
	if (empty($hash)) { // check for album password
		$hash = $albumobj->getPassword();
		$authType = "zp_album_auth_" . $albumobj->getID();
		$hint = $albumobj->getPasswordHint();
		$show = $albumobj->getUser();
		if (empty($hash)) {
			$albumobj = $albumobj->getParent();
			while (!is_null($albumobj)) {
				$hash = $albumobj->getPassword();
				$authType = "zp_album_auth_" . $albumobj->getID();
				$hint = $albumobj->getPasswordHint();
				$show = $albumobj->getUser();
				if (!empty($hash)) {
					break;
				}
				$albumobj = $albumobj->getParent();
			}
		}
	}
	if (empty($hash)) { // check for gallery password
		$hash = $_zp_gallery->getPassword();
		$authType = 'zp_gallery_auth';
		$hint = $_zp_gallery->getPasswordHint();
		$show = $_zp_gallery->getUser();
	}

	if (empty($hash) || (!empty($hash) && zp_getCookie($authType) != $hash)) {
		require_once(dirname(__FILE__) . "/template-functions.php");
		require_once(SERVERPATH . "/" . ZENFOLDER . '/functions-controller.php');
		zp_load_gallery();
		$theme = setupTheme($albumobj);
		$custom = $_zp_themeroot . '/functions.php';
		if (file_exists($custom)) {
			require_once($custom);
		}
		$_zp_gallery_page = 'password.php';
		$_zp_script = $_zp_themeroot . '/password.php';
		if (!file_exists(internalToFilesystem($_zp_script))) {
			$_zp_script = SERVERPATH . '/' . ZENFOLDER . '/password.php';
		}
		header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
		header("HTTP/1.0 302 Found");
		header("Status: 302 Found");
		header('Last-Modified: ' . ZP_LAST_MODIFIED);
		include(internalToFilesystem($_zp_script));
		exposeZenPhotoInformations($_zp_script, array(), $theme);
		exitZP();
	}
}

$image_path = $imageobj->localpath;
$suffix = getSuffix($image_path);

switch ($suffix) {
	case 'bmp':
		$suffix = 'wbmp';
		break;
	case 'jpg':
		$suffix = 'jpeg';
		break;
	case 'png':
	case 'gif':
	case 'jpeg':
		break;
	default:
		if ($disposal == 'Download') {
			require_once(dirname(__FILE__) . '/lib-MimeTypes.php');
			$mimetype = getMimeString($suffix);
			header('Content-Disposition: attachment; filename="' . $image . '"'); // enable this to make the image a download
			$fp = fopen($image_path, 'rb');
			// send the right headers
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header("Content-Type: $mimetype");
			header("Content-Length: " . filesize($image_path));
			// dump the picture and stop the script
			fpassthru($fp);
			fclose($fp);
		} else {
			header('Location: ' . $imageobj->getFullImageURL(), true, 301);
		}
		exitZP();
}
if ($force_cache = getOption('cache_full_image')) {
	$cache_file = getImageCacheFilename($album, $image, $args);
	$cache_path = SERVERCACHE . $cache_file;
	mkdir_recursive(dirname($cache_path), FOLDER_MOD);
} else {
	$cache_file = $album . "/" . stripSuffix($image) . '_FULL.' . $suffix;
	$cache_path = NULL;
}

$process = $rotate = false;
if (zp_imageCanRotate()) {
	$rotate = getImageRotation($image_path);
	$process = $rotate;
}
$watermark_use_image = getWatermarkParam($imageobj, WATERMARK_FULL);
if ($watermark_use_image == NO_WATERMARK) {
	$watermark_use_image = '';
} else {
	$process = 2;
}

if (isset($_GET['q'])) {
	$quality = sanitize_numeric($_GET['q']);
} else {
	$quality = getOption('full_image_quality');
}

if (!($process || $force_cache)) { // no processing needed
	if (getOption('album_folder_class') != 'external' && $disposal != 'Download') { // local album system, return the image directly
		header('Content-Type: image/' . $suffix);
		if (UTF8_IMAGE_URI) {
			header("Location: " . getAlbumFolder(FULLWEBPATH) . pathurlencode($album8) . "/" . rawurlencode($image8));
		} else {
			header("Location: " . getAlbumFolder(FULLWEBPATH) . pathurlencode($album) . "/" . rawurlencode($image));
		}
		exitZP();
	} else { // the web server does not have access to the image, have to supply it
		$fp = fopen($image_path, 'rb');
		// send the right headers
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header("Content-Type: image/$suffix");
		if ($disposal == 'Download') {
			header('Content-Disposition: attachment; filename="' . $image . '"'); // enable this to make the image a download
		}
		header("Content-Length: " . filesize($image_path));
		// dump the picture and stop the script
		fpassthru($fp);
		fclose($fp);
		exitZP();
	}
}

header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header("Content-Type: image/$suffix");
if ($disposal == 'Download') {
	header('Content-Disposition: attachment; filename="' . $image . '"'); // enable this to make the image a download
}

if (is_null($cache_path) || !file_exists($cache_path)) { //process the image
	if ($forbidden) {
		imageError('403 Forbidden', gettext("Forbidden(2)"));
	}
	if ($force_cache && !$process) {
		// we can just use the original!
		if (SYMLINK && @symlink($image_path, $cache_path)) {
			if (DEBUG_IMAGE)
				debugLog("full-image:symlink original " . basename($image));
			clearstatcache();
		} else if (@copy($image_path, $cache_path)) {
			if (DEBUG_IMAGE)
				debugLog("full-image:copy original " . basename($image));
			clearstatcache();
		}
	} else {
		//	have to create the image
		$iMutex = new Mutex('i', getOption('imageProcessorConcurrency'));
		$iMutex->lock();
		$newim = zp_imageGet($image_path);
		if ($rotate) {
			$newim = zp_rotateImage($newim, $rotate);
		}
		if ($watermark_use_image) {
			$watermark_image = getWatermarkPath($watermark_use_image);
			if (!file_exists($watermark_image))
				$watermark_image = SERVERPATH . '/' . ZENFOLDER . '/images/imageDefault.png';
			$offset_h = getOption('watermark_h_offset') / 100;
			$offset_w = getOption('watermark_w_offset') / 100;
			$watermark = zp_imageGet($watermark_image);
			$watermark_width = zp_imageWidth($watermark);
			$watermark_height = zp_imageHeight($watermark);
			$imw = zp_imageWidth($newim);
			$imh = zp_imageHeight($newim);
			$percent = getOption('watermark_scale') / 100;
			$r = sqrt(($imw * $imh * $percent) / ($watermark_width * $watermark_height));
			if (!getOption('watermark_allow_upscale')) {
				$r = min(1, $r);
			}
			$nw = round($watermark_width * $r);
			$nh = round($watermark_height * $r);
			if (($nw != $watermark_width) || ($nh != $watermark_height)) {
				$watermark = zp_imageResizeAlpha($watermark, $nw, $nh);
			}
			// Position Overlay in Bottom Right
			$dest_x = max(0, floor(($imw - $nw) * $offset_w));
			$dest_y = max(0, floor(($imh - $nh) * $offset_h));
			zp_copyCanvas($newim, $watermark, $dest_x, $dest_y, 0, 0, $nw, $nh);
			zp_imageKill($watermark);
		}
		$iMutex->unlock();
		if (!zp_imageOutput($newim, $suffix, $cache_path, $quality) && DEBUG_IMAGE) {
			debugLog('full-image failed to create:' . $image);
		}
	}
}

if (!is_null($cache_path)) {
	if ($disposal == 'Download' || !OPEN_IMAGE_CACHE) {
		require_once(dirname(__FILE__) . '/lib-MimeTypes.php');
		$mimetype = getMimeString($suffix);
		$fp = fopen($cache_path, 'rb');
		// send the right headers
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header("Content-Type: $mimetype");
		header("Content-Length: " . filesize($image_path));
		// dump the picture and stop the script
		fpassthru($fp);
		fclose($fp);
	} else {
		header('Location: ' . FULLWEBPATH . '/' . CACHEFOLDER . pathurlencode(imgSrcURI($cache_file)), true, 301);
	}
	exitZP();
}
?>

