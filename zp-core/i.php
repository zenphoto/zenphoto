<?php
/**
 * i.php: Zenphoto image processor
 * All *uncached* image requests go through this file
 * (As of 1.0.8 images are requested directly from the cache if they exist)
 *******************************************************************************
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
 *******************************************************************************
 * @package core
 */

// force UTF-8 Ø


define('OFFSET_PATH', 2);
require_once(dirname(__FILE__).'/functions-basic.php');
require_once(dirname(__FILE__).'/functions-image.php');

$debug = isset($_GET['debug']);

// Check for minimum parameters.
if (!isset($_GET['a']) || !isset($_GET['i'])) {
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
$adminrequest = isset($_GET['admin']);
if (getOption('secure_image_processor')) {
	require_once(dirname(__FILE__).'/functions.php');
	$albumobj = new Album(NULL, filesystemToInternal($album));
	if (!$albumobj->checkAccess()) {
		imageError('403 Forbidden', gettext("Forbidden"));
	}
}

// Extract the image parameters from the input variables
// This validates the input as well.
$args = array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
if (isset($_GET['s'])) { //0
	if (is_numeric($_GET['s'])) {
		$args[0] = min(abs($_GET['s']), MAX_SIZE);
	} else {
		$args[0] = sanitize($_GET['s']);
	}
}
if (isset($_GET['w'])) {  //1
	$args[1] = min(abs($_GET['w']), MAX_SIZE);
}
if (isset($_GET['h'])) { //2
	$args[2] = min(abs($_GET['h']), MAX_SIZE);
}
if (isset($_GET['cw'])) { //3
	$args[3] = sanitize($_GET['cw']);
}
if (isset($_GET['ch'])) { //4
	$args[4] = sanitize($_GET['ch']);
}
if (isset($_GET['cx'])) { //5
	$args[5] = sanitize($_GET['cx']);
}
if (isset($_GET['cy'])) { //6
	$args[6] = sanitize($_GET['cy']);
}
if (isset($_GET['q'])) { //7
	$args[7] = sanitize($_GET['q']);
}
if (isset($_GET['thumb'])) { // 8
	$args[10] = 1;
}
if (isset($_GET['c'])) {// 9
	$args[9] = sanitize($_GET['c']);
}
if (isset($_GET['t'])) { //10
	$args[10] = sanitize($_GET['t']);
}
if (isset($_GET['wmk']) && !$adminrequest) { //11
	$args[11] = sanitize($_GET['wmk']);
}
$args [12] = $adminrequest; //12

if (isset($_GET['effects'])) {	//13
	$args[13] = sanitize($_GET['effects']);
}


if ( !isset($_GET['s']) && !isset($_GET['w']) && !isset($_GET['h'])) {
	// No image parameters specified
	if (getOption('album_folder_class') !== 'external') {
		header("Location: " . getAlbumFolder(FULLWEBPATH) . pathurlencode(filesystemToInternal($album)) . "/" . rawurlencode(filesystemToInternal($image)));
		return;
	}
	// external album, Web server cannot serve original image. Force resize to as big as we can do
	$args[0] = MAX_SIZE;
}
$args = getImageParameters($args,filesystemToInternal($album));
list($size, $width, $height, $cw, $ch, $cx, $cy, $quality, $thumb, $crop, $thumbstandin, $passedWM, $adminrequest, $effects) = $args;
if (DEBUG_IMAGE) debugLog("i.php($ralbum, $rimage): \$size=$size, \$width=$width, \$height=$height, \$cw=$cw, \$ch=$ch, \$cx=$cx, \$cy=$cy, \$quality=$quality, \$thumb=$thumb, \$crop=$crop, \$thumbstandin=$thumbstandin, \$passedWM=$passedWM, \$adminrequest=$adminrequest, \$effects=$effects");
$allowWatermark = !$thumb && !$adminrequest;

// Construct the filename to save the cached image.
$newfilename = getImageCacheFilename(filesystemToInternal($album), filesystemToInternal($image), $args);
$newfile = SERVERCACHE . $newfilename;
if (trim($album)=='') {
	$imgfile = ALBUM_FOLDER_SERVERPATH . $image;
} else {
	$imgfile = ALBUM_FOLDER_SERVERPATH . $album.'/'.$image;
}

if ($debug) imageDebug($album, $image, $args, $imgfile);


/** Check for possible problems ***********
 ******************************************/
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
	$imgfile = $rimage; // undo the sanitize
	// then check to see if it is a transient image
	$i = strpos($imgfile, '_{');
	if ($i !== false) {
		$j = strpos($imgfile, '}_');
		$source = substr($imgfile, $i+2, $j-$i-2);
		$imgfile = substr($imgfile, $j+1);
		$i = strpos($imgfile, '_{');
		if ($i !== false) {
			$j = strpos($imgfile, '}_');
			$source2 = '/'.str_replace('_-_', '/', substr($imgfile, $i+2, $j-$i-2));
			$imgfile = substr($imgfile, $j+2);
		} else {
			$source2 = '';
		}
		switch($source) {
			case ZENFOLDER:
			case USER_PLUGIN_FOLDER:
				break;
			default:
			$source = THEMEFOLDER.'/'.$source;
			break;
		}
		$args[3] = $args[4] = 0;
		$args[5] = 1;    // full crops for these default images
		$args[9] = NULL;
		if (DEBUG_IMAGE) debugLog("Transient image:$source$source2/$imgfile=>$newfile");
		$imgfile = SERVERPATH .'/'. $source.$source2 . "/" . $imgfile;
	}
	if (!file_exists($imgfile)) {
		header("HTTP/1.0 404 Not Found");
		header("Status: 404 Not Found");
		imageError('404 Not Found', gettext("Image not found; file does not exist."), 'err-imagenotfound.png');
	}
}

// Make the directories for the albums in the cache, recursively.
// Skip this for safe_mode, where we can't write to directories we create!
if (!SAFE_MODE) {
	$albumdirs = getAlbumArray($album, true);
	foreach($albumdirs as $dir) {
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
		if (DEBUG_IMAGE) debugLog("Cache file valid");
	}
}

if ($process) { // If the file hasn't been cached yet, create it.
	// setup standard image options from the album theme if it exists
	if (!cacheImage($newfilename, $imgfile, $args, $allowWatermark, $theme, $album)) {
		imageError('404 Not Found', gettext('Image processing resulted in a fatal error.'));
	}
	$fmt = filemtime($newfile);
}
$protocol = FULLWEBPATH;
$path = $protocol . '/'.CACHEFOLDER . pathurlencode(imgSrcURI($newfilename));

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
			imageError(405, 'Method Not Allowed', gettext("Method Not Allowed"));
	}
	if (OPEN_IMAGE_CACHE) {
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $fmt).' GMT');
		header('Content-Type: image/'.$suffix);
		header('Location: ' . $path, true, 301);
	} else {
		$fp = fopen($newfile, 'rb');
		// send the right headers
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s').' GMT');
		header("Content-Type: image/$suffix");
		header("Content-Length: " . filesize($newfile));
		// dump the picture and stop the script
		fpassthru($fp);
		fclose($fp);
	}
	exitZP();
} else {
	echo "\n<p>Image: <img src=\"" . $path ."\" /></p>";
}

?>
