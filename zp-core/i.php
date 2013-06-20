<?php

/**
 * i.php: Zenphoto image processor
 * All *uncached* image requests go through this file
 * (As of 1.0.8 images are requested directly from the cache if they exist)
 * ******************************************************************************
 * URI Parameters:
 *   s  - size (logical): Based on config, makes an image of "size s."
 *   h  - height (explicit): Image will be resized to h pixels high, w is calculated.
 *   w  - width (explicit): Image will resized to w pixels wide, h is calculated.
 *   cw - crop width: crops the image to cw pixels wide.
 *   ch - crop height: crops the image to ch pixels high.
 *   cx - crop x position: the x (horizontal) position of the crop area.
 *   cy - crop y position: the y (vertical) position of the crop area.
 *   q  - JPEG quality (1-100): sets the quality of the resulting image.
 *   t  - Set for custom images if used as thumbs.
 *   wmk - the watermark image to overlay
 *   gray - grayscale the image
 *   admin - request is from the back-end
 *
 * 	 Cropping is performed on the original image before resizing is done.
 * - cx and cy are measured from the top-left corner of the image.
 * - One of s, h, or w _must_ be specified; the others are optional.
 * - If more than one of s, h, or w are specified, s takes priority, then w+h:
 * - If none of s, h, or w are specified, the original image is returned.
 * ******************************************************************************
 * @package core
 */
// force UTF-8 Ø


if (!defined('OFFSET_PATH'))
	define('OFFSET_PATH', 2);
require_once(dirname(__FILE__) . '/functions-basic.php');
require_once(dirname(__FILE__) . '/functions-image.php');

$debug = isset($_GET['debug']);

// Check for minimum parameters.
if (!isset($_GET['a']) || !isset($_GET['i'])) {
	if (TEST_RELEASE) {
		debugLogVar('i.php too few arguments _GET', $_GET);
		debugLogVar('i.php too few arguments _SERVER', $_SERVER);
	}
	imageError('404 Not Found', gettext("Too few arguments! Image not found."), 'err-imagenotfound.png');
}

// Fix special characters in the album and image names if mod_rewrite is on:
// URL looks like: "/album1/subalbum/picture.jpg"

list($ralbum, $rimage) = rewrite_get_album_image('a', 'i');
$ralbum = internalToFilesystem($ralbum);
$rimage = internalToFilesystem($rimage);
$album = sanitize_path($ralbum);
$image = sanitize_path($rimage);
$theme = themeSetup(filesystemToInternal($album)); // loads the theme based image options.
if (getOption('secure_image_processor')) {
	require_once(dirname(__FILE__) . '/functions.php');
	$albumobj = newAlbum(filesystemToInternal($album));
	if (!$albumobj->checkAccess()) {
		imageError('403 Forbidden', gettext("Forbidden(1)"));
	}
}

$args = getImageArgs($_GET);
$adminrequest = $args[12];

if ($forbidden = getOption('image_processor_flooding_protection') && (!isset($_GET['check']) || $_GET['check'] != sha1(HASH_SEED . serialize($args)))) {
	// maybe it was from the tinyZenpage javascript which does not know better!
	zp_session_start();
	$forbidden = !isset($_SESSION['adminRequest']) || $_SESSION['adminRequest'] != @$_COOKIE['zp_user_auth'];
}

if (!isset($_GET['s']) && !isset($_GET['w']) && !isset($_GET['h'])) {
	// No image parameters specified
	if (getOption('album_folder_class') !== 'external') {
		header("Location: " . getAlbumFolder(FULLWEBPATH) . pathurlencode(filesystemToInternal($album)) . "/" . rawurlencode(filesystemToInternal($image)));
		return;
	}
}

$args = getImageParameters($args, filesystemToInternal($album));
list($size, $width, $height, $cw, $ch, $cx, $cy, $quality, $thumb, $crop, $thumbstandin, $passedWM, $adminrequest, $effects) = $args;
if (DEBUG_IMAGE)
	debugLog("i.php($ralbum, $rimage): \$size=$size, \$width=$width, \$height=$height, \$cw=$cw, \$ch=$ch, \$cx=$cx, \$cy=$cy, \$quality=$quality, \$thumb=$thumb, \$crop=$crop, \$thumbstandin=$thumbstandin, \$passedWM=$passedWM, \$adminrequest=$adminrequest, \$effects=$effects");
$allowWatermark = !$thumb && !$adminrequest;

// Construct the filename to save the cached image.
$newfilename = getImageCacheFilename(filesystemToInternal($album), filesystemToInternal($image), $args);
$newfile = SERVERCACHE . $newfilename;
if (trim($album) == '') {
	$imgfile = ALBUM_FOLDER_SERVERPATH . $image;
} else {
	$imgfile = ALBUM_FOLDER_SERVERPATH . $album . '/' . $image;
}

if ($debug)
	imageDebug($album, $image, $args, $imgfile);


/** Check for possible problems ***********
 * **************************************** */
// Make sure the cache directory is writable, attempt to fix. Issue a warning if not fixable.
if (!is_dir(SERVERCACHE)) {
	@mkdir(SERVERCACHE, FOLDER_MOD);
	@chmod(SERVERCACHE, FOLDER_MOD);
	if (!is_dir(SERVERCACHE))
		imageError('404 Not Found', gettext("The cache directory does not exist. Please create it and set the permissions to 0777."), 'err-cachewrite.png');
}
if (!is_writable(SERVERCACHE)) {
	@chmod(SERVERCACHE, FOLDER_MOD);
	if (!is_writable(SERVERCACHE))
		imageError('404 Not Found', gettext("The cache directory is not writable! Attempts to chmod didn't work."), 'err-cachewrite.png');
}
if (!file_exists($imgfile)) {
	if (isset($_GET['z'])) { //	flagged as a special image
		$args[3] = $args[4] = 0;
		$args[5] = 1; // full crops for these default images
		$args[9] = NULL;
		if (DEBUG_IMAGE)
			debugLog("Transient image:$rimage=>$newfile");
		$imgfile = SERVERPATH . '/' . sanitize_path($_GET['z']);
	}
	if (!file_exists($imgfile)) {
		if (DEBUG_IMAGE)
			debugLogVar('image not found', $args);
		imageError('404 Not Found', sprintf(gettext("Image not found; file %s does not exist."), filesystemToInternal($image)), 'err-imagenotfound.png');
	}
}

// Make the directories for the albums in the cache, recursively.
// Skip this for safe_mode, where we can't write to directories we create!
if (!SAFE_MODE) {
	$albumdirs = getAlbumArray($album, true);
	foreach ($albumdirs as $dir) {
		$dir = internalToFilesystem($dir);
		$dir = SERVERCACHE . '/' . $dir;
		if (!is_dir($dir)) {
			@mkdir($dir, FOLDER_MOD);
			@chmod($dir, FOLDER_MOD);
		} else if (!is_writable($dir)) {
			@chmod($dir, FOLDER_MOD);
		}
	}
}
$process = true;
// If the file exists, check its modification time and update as needed.
$fmt = filemtime($imgfile);
if (file_exists($newfile) & !$adminrequest) {
	if (filemtime($newfile) >= filemtime($imgfile)) {
		$process = false;
		if (DEBUG_IMAGE)
			debugLog("Cache file valid");
	}
}

if ($process) { // If the file hasn't been cached yet, create it.
	if ($forbidden) {
		imageError('403 Forbidden', gettext("Forbidden(2)"));
	}

	$iMutex = new Mutex('i', getOption('imageProcessorConcurrency'));
	$iMutex->lock();
	$result = cacheImage($newfilename, $imgfile, $args, $allowWatermark, $theme, $album);
	$iMutex->unlock();

	if (!$result) {
		imageError('404 Not Found', sprintf(gettext('Image processing of %s resulted in a fatal error.'), filesystemToInternal($image)));
	}
	$fmt = filemtime($newfile);
}
$protocol = FULLWEBPATH;
$path = $protocol . '/' . CACHEFOLDER . pathurlencode(imgSrcURI($newfilename));

if (!$debug) {
	// ... and redirect the browser to it.
	$suffix = getSuffix($newfilename);
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
			imageError(405, 'Method Not Allowed', sprintf(gettext("Suffix Not Allowed: %s"), filesystemToInternal(basename($newfilename))));
	}
	if (OPEN_IMAGE_CACHE) {
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $fmt) . ' GMT');
		header('Content-Type: image/' . $suffix);
		header('Location: ' . $path, true, 301);
	} else {
		$fp = fopen($newfile, 'rb');
		// send the right headers
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header("Content-Type: image/$suffix");
		header("Content-Length: " . filesize($newfile));
		// dump the picture and stop the script
		fpassthru($fp);
		fclose($fp);
	}
} else {
	echo "\n<p>Image: <img src=\"" . $path . "\" /></p>";
}
?>
