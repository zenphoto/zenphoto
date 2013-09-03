<?php
/**
 * image processing functions
 * @package core
 *
 */
// force UTF-8 Ø
// functions-image.php - HEADERS NOT SENT YET!

/**
 * If in debug mode, prints the given error message and continues; otherwise redirects
 * to the given error message image and exits; designed for a production gallery.
 * @param $errormessage string the error message to print if $_GET['debug'] is set.
 * @param $errorimg string the filename of the error image to display for production. Defaults
 *   to 'err-imagegeneral.png'. Images should be located in /zen/images .
 */
function imageError($status_text, $errormessage, $errorimg = 'err-imagegeneral.png') {
	global $newfilename, $album, $image;
	$debug = isset($_GET['debug']);
	if ($debug) {
		echo('<strong>' . sprintf(gettext('Zenphoto Image Processing Error: %s'), $errormessage) . '</strong>'
		. '<br /><br />' . sprintf(gettext('Request URI: [ <code>%s</code> ]'), html_encode(getRequestURI()))
		. '<br />PHP_SELF: [ <code>' . sanitize($_SERVER['PHP_SELF'], 3) . '</code> ]'
		. (empty($newfilename) ? '' : '<br />' . sprintf(gettext('Cache: [<code>%s</code>]'), '/' . CACHEFOLDER . '/' . sanitize($newfilename, 3)) . ' ')
		. (empty($image) || empty($album) ? '' : ' <br />' . sprintf(gettext('Image: [<code>%s</code>]'), sanitize($album . '/' . $image, 3)) . ' <br />'));
	} else {
		if (DEBUG_IMAGE_ERR) {
			trigger_error($errormessage, E_USER_NOTICE);
		}
		header("HTTP/1.0 $status_text");
		header("Status: $status_text");
		header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/images/' . $errorimg);
	}
	exitZP();
}

/**
 * Prints debug information from the arguments to i.php.
 *
 * @param string $album alubm name
 * @param string $image image name
 * @param array $args size/crop arguments
 * @param string $imgfile the filename of the image
 */
function imageDebug($album, $image, $args, $imgfile) {
	list($size, $width, $height, $cw, $ch, $cx, $cy, $quality, $thumb, $crop) = $args;
	echo "Album: [ " . $album . " ], Image: [ " . $image . " ]<br /><br />";
	if (file_exists($imgfile)) {
		echo "Image filesize: " . filesize($imgfile);
	} else {
		echo "Image file not found.";
	}
	echo '<br /><br />';
	echo "<strong>" . gettext("Debug") . " <code>i.php</code> | " . gettext("Arguments:") . "</strong><br />\n\n"
	?>
	<ul>
		<li><?php echo gettext("size ="); ?>   <strong> <?php echo sanitize($size, 3) ?> </strong></li>
		<li><?php echo gettext("width =") ?>   <strong> <?php echo sanitize($width, 3) ?> </strong></li>
		<li><?php echo gettext("height =") ?>  <strong> <?php echo sanitize($height, 3) ?> </strong></li>
		<li><?php echo gettext("cw =") ?>      <strong> <?php echo sanitize($cw, 3) ?> </strong></li>
		<li><?php echo gettext("ch =") ?>      <strong> <?php echo sanitize($ch, 3) ?> </strong></li>
		<li><?php echo gettext("cx =") ?>      <strong> <?php echo sanitize($cx, 3) ?> </strong></li>
		<li><?php echo gettext("cy =") ?>      <strong> <?php echo sanitize($cy, 3) ?> </strong></li>
		<li><?php echo gettext("quality =") ?> <strong> <?php echo sanitize($quality, 3) ?> </strong></li>
		<li><?php echo gettext("thumb =") ?>   <strong> <?php echo sanitize($thumb, 3) ?> </strong></li>
		<li><?php echo gettext("crop =") ?>    <strong> <?php echo sanitize($crop, 3) ?> </strong></li>
	</ul>
	<?php
}

/**
 * Calculates proprotional width and height
 * Used internally by cacheImage
 *
 * Returns array containing the new width and height
 *
 * @param int $size
 * @param int $width
 * @param int $height
 * @param int $w
 * @param int $h
 * @param int $thumb
 * @param int $image_use_side
 * @param int $dim
 * @return array
 */
function propSizes($size, $width, $height, $w, $h, $thumb, $image_use_side, $dim) {
	$hprop = round(($h / $w) * $dim);
	$wprop = round(($w / $h) * $dim);
	if ($size) {
		if ((($thumb || ($image_use_side == 'longest')) && $h > $w) || ($image_use_side == 'height') || ($image_use_side == 'shortest' && $h < $w)) {
			$newh = $dim; // height is the size and width is proportional
			$neww = $wprop;
		} else {
			$neww = $dim; // width is the size and height is proportional
			$newh = $hprop;
		}
	} else { // length and/or width is set, size is NULL (Thumbs work the same as image in this case)
		if ($height) {
			$newh = $height; // height is supplied, use it
		} else {
			$newh = $hprop; // height not supplied, use the proprotional
		}
		if ($width) {
			$neww = $width; // width is supplied, use it
		} else {
			$neww = $wprop; // width is not supplied, use the proportional
		}
	}
	if (DEBUG_IMAGE)
		debugLog("propSizes(\$size=$size, \$width=$width, \$height=$height, \$w=$w, \$h=$h, \$thumb=$thumb, \$image_use_side=$image_use_side, \$dim=$dim):: \$wprop=$wprop; \$hprop=$hprop; \$neww=$neww; \$newh=$newh");
	return array($neww, $newh);
}

/**
 * iptc_make_tag() function by Thies C. Arntzen
 * @param $rec
 * @param $data
 * @param $value
 */
function iptc_make_tag($rec, $data, $value) {
	$length = strlen($value);
	$retval = chr(0x1C) . chr($rec) . chr($data);
	if ($length < 0x8000) {
		$retval .= chr($length >> 8) . chr($length & 0xFF);
	} else {
		$retval .= chr(0x80) . chr(0x04) . chr(($length >> 24) & 0xFF) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
	}
	return $retval . $value;
}

/**
 * Creates the cache folder version of the image, including watermarking
 *
 * @param string $newfilename the name of the file when it is in the cache
 * @param string $imgfile the image name
 * @param array $args the cropping arguments
 * @param bool $allow_watermark set to true if image may be watermarked
 * @param string $theme the current theme
 * @param string $album the album containing the image
 */
function cacheImage($newfilename, $imgfile, $args, $allow_watermark = false, $theme, $album) {
	global $_zp_gallery;
	try {
		@list($size, $width, $height, $cw, $ch, $cx, $cy, $quality, $thumb, $crop, $thumbstandin, $passedWM, $adminrequest, $effects) = $args;
		// Set the config variables for convenience.
		$image_use_side = getOption('image_use_side');
		$upscale = getOption('image_allow_upscale');
		$allowscale = true;
		$sharpenthumbs = getOption('thumb_sharpen');
		$sharpenimages = getOption('image_sharpen');
		$id = $im = NULL;
		$watermark_use_image = getAlbumInherited($album, 'watermark', $id);
		if (empty($watermark_use_image)) {
			$watermark_use_image = IMAGE_WATERMARK;
		}
		if (!$effects) {
			if ($thumb && getOption('thumb_gray')) {
				$effects = 'gray';
			} else if (getOption('image_gray')) {
				$effects = 'gray';
			}
		}
		$newfile = SERVERCACHE . $newfilename;
		mkdir_recursive(dirname($newfile), FOLDER_MOD);
		if (DEBUG_IMAGE)
			debugLog("cacheImage(\$imgfile=" . basename($imgfile) . ", \$newfilename=$newfilename, \$allow_watermark=$allow_watermark, \$theme=$theme) \$size=$size, \$width=$width, \$height=$height, \$cw=$cw, \$ch=$ch, \$cx=" . (is_null($cx) ? 'NULL' : $cx) . ", \$cy=" . (is_null($cy) ? 'NULL' : $cy) . ", \$quality=$quality, \$thumb=$thumb, \$crop=$crop \$image_use_side=$image_use_side; \$upscale=$upscale);");
		// Check for the source image.
		if (!file_exists($imgfile) || !is_readable($imgfile)) {
			imageError('404 Not Found', sprintf(gettext('Image %s not found or is unreadable.'), filesystemToInternal($imgfile)), 'err-imagenotfound.png');
		}
		$rotate = false;
		if (zp_imageCanRotate()) {
			$rotate = getImageRotation($imgfile);
		}
		$s = getSuffix($imgfile);
		if (function_exists('exif_thumbnail') && getOption('use_embedded_thumb') && ($s == 'jpg' || $s == 'jpeg')) {
			$im = exif_thumbnail($imgfile, $tw, $th, $tt);
			if ($im) {
				if ($size) {
					$big_enough = $tw >= $size && $th >= $size;
				} else {
					$big_enough = $tw >= $width && $th >= $height;
				}
				if ($big_enough) {
					$im = zp_imageFromString($im);
					if (DEBUG_IMAGE && $im)
						debugLog(sprintf(gettext('Using %1$ux%2$u %3$s thumbnail image.'), $tw, $th, image_type_to_mime_type($tt)));
				} else {
					$im = false;
				}
			} else {
				$im = false;
			}
		}
		if (!$im) {
			$im = zp_imageGet($imgfile);
		}
		if (!$im) {
			imageError('404 Not Found', sprintf(gettext('Image %s not renderable (imageGet).'), filesystemToInternal($imgfile)), 'err-failimage.png');
		}
		if ($rotate) {
			if (DEBUG_IMAGE)
				debugLog("cacheImage:rotate->$rotate");
			$im = zp_rotateImage($im, $rotate);
			if (!$im) {
				imageError('404 Not Found', sprintf(gettext('Image %s not rotatable.'), filesystemToInternal($imgfile)), 'err-failimage.png');
			}
		}
		$w = zp_imageWidth($im);
		$h = zp_imageHeight($im);
		// Give the sizing dimension to $dim
		$ratio_in = '';
		$ratio_out = '';
		$crop = ($crop || $cw != 0 || $ch != 0);
		if (!empty($size)) {
			$dim = $size;
			if ($crop) {
				$dim = $size;
				if (!$ch)
					$ch = $size;
				if (!$cw)
					$cw = $size;
				$width = $cw;
				$height = $ch;
				$size = false;
			} else {
				$width = $height = false;
			}
		} else if (!empty($width) && !empty($height)) {
			$ratio_in = $h / $w;
			$ratio_out = $height / $width;
			if ($ratio_in > $ratio_out) { // image is taller than desired, $height is the determining factor
				$thumb = true;
				$dim = $width;
				if (!$ch)
					$ch = $height;
			} else { // image is wider than desired, $width is the determining factor
				$dim = $height;
				if (!$cw)
					$cw = $width;
			}
		} else if (!empty($width)) {
			$dim = $width;
			$size = $height = false;
		} else if (!empty($height)) {
			$dim = $height;
			$size = $width = false;
		} else {
			// There's a problem up there somewhere...
			imageError('404 Not Found', sprintf(gettext('Unknown error processing %s! Please report to the developers at <a href="http://www.zenphoto.org/">www.zenphoto.org</a>'), filesystemToInternal($imgfile)), 'err-imagegeneral.png');
		}

		$sizes = propSizes($size, $width, $height, $w, $h, $thumb, $image_use_side, $dim);
		list($neww, $newh) = $sizes;

		if (DEBUG_IMAGE)
			debugLog("cacheImage:" . basename($imgfile) . ": \$size=$size, \$width=$width, \$height=$height, \$w=$w; \$h=$h; \$cw=$cw, " .
							"\$ch=$ch, \$cx=$cx, \$cy=$cy, \$quality=$quality, \$thumb=$thumb, \$crop=$crop, \$newh=$newh, \$neww=$neww, \$dim=$dim, " .
							"\$ratio_in=$ratio_in, \$ratio_out=$ratio_out \$upscale=$upscale \$rotate=$rotate \$effects=$effects");

		if (!$upscale && $newh >= $h && $neww >= $w) { // image is the same size or smaller than the request
			$neww = $w;
			$newh = $h;
			$allowscale = false;
			if ($crop) {
				if ($width > $neww) {
					$width = $neww;
				}
				if ($height > $newh) {
					$height = $newh;
				}
			}
			if (DEBUG_IMAGE)
				debugLog("cacheImage:no upscale " . basename($imgfile) . ":  \$newh=$newh, \$neww=$neww, \$crop=$crop, \$thumb=$thumb, \$rotate=$rotate, watermark=" . $watermark_use_image);
		}

		$watermark_image = false;
		if ($passedWM) {
			if ($passedWM != NO_WATERMARK) {
				$watermark_image = getWatermarkPath($passedWM);
				if (!file_exists($watermark_image)) {
					$watermark_image = SERVERPATH . '/' . ZENFOLDER . '/images/imageDefault.png';
				}
			}
		} else {
			if ($allow_watermark) {
				$watermark_image = $watermark_use_image;
				if ($watermark_image) {
					if ($watermark_image != NO_WATERMARK) {
						$watermark_image = getWatermarkPath($watermark_image);
						if (!file_exists($watermark_image)) {
							$watermark_image = SERVERPATH . '/' . ZENFOLDER . '/images/imageDefault.png';
						}
					}
				}
			}
		}

		// Crop the image if requested.
		if ($crop) {
			if ($cw > $ch) {
				$ir = $ch / $cw;
			} else {
				$ir = $cw / $ch;
			}
			if ($size) {
				$neww = $size;
				$newh = $ir * $size;
			} else {
				$neww = $width;
				$newh = $height;
				if ($neww > $newh) {
					if ($newh === false) {
						$newh = $ir * $neww;
					}
				} else {
					if ($neww === false) {
						$neww = $ir * $newh;
					}
				}
			}
			if (is_null($cx) && is_null($cy)) { // scale crop to max of image
				// set crop scale factor
				$cf = 1;
				if ($cw)
					$cf = min($cf, $cw / $neww);
				if ($ch)
					$cf = min($cf, $ch / $newh);
				//	set the image area of the crop (use the most image possible, rule of thirds positioning)
				if (!$cw || $w / $cw * $ch > $h) {
					$cw = round($h / $ch * $cw * $cf);
					$ch = round($h * $cf);
					$cx = round(($w - $cw) / 3);
				} else {
					$ch = round($w / $cw * $ch * $cf);
					$cw = round($w * $cf);
					$cy = round(($h - $ch) / 3);
				}
			} else { // custom crop
				if (!$cw || $cw > $w)
					$cw = $w;
				if (!$ch || $ch > $h)
					$ch = $h;
			}
			// force the crop to be within the image
			if ($cw + $cx > $w)
				$cx = $w - $cw;
			if ($cx < 0) {
				$cw = $cw + $cx;
				$cx = 0;
			}
			if ($ch + $cy > $h)
				$cy = $h - $ch;
			if ($cy < 0) {
				$ch = $ch + $cy;
				$cy = 0;
			}
			if (DEBUG_IMAGE)
				debugLog("cacheImage:crop " . basename($imgfile) . ":\$size=$size, \$width=$width, \$height=$height, \$cw=$cw, \$ch=$ch, \$cx=$cx, \$cy=$cy, \$quality=$quality, \$thumb=$thumb, \$crop=$crop, \$rotate=$rotate");
			$newim = zp_createImage($neww, $newh);
			if (!zp_resampleImage($newim, $im, 0, 0, $cx, $cy, $neww, $newh, $cw, $ch)) {
				imageError('404 Not Found', sprintf(gettext('Image %s not renderable (resample).'), filesystemToInternal($imgfile)), 'err-failimage.png');
			}
		} else {
			if ($newh >= $h && $neww >= $w && !$rotate && !$effects && !$watermark_image && (!$upscale || $newh == $h && $neww == $w)) {
				// we can just use the original!
				if (SYMLINK && @symlink($imgfile, $newfile)) {
					if (DEBUG_IMAGE)
						debugLog("cacheImage:symlink original " . basename($imgfile) . ":\$size=$size, \$width=$width, \$height=$height, \$dim=$dim, \$neww=$neww; \$newh=$newh; \$quality=$quality, \$thumb=$thumb, \$crop=$crop, \$rotate=$rotate; \$allowscale=$allowscale;");
					clearstatcache();
					return true;
				} else if (@copy($imgfile, $newfile)) {
					if (DEBUG_IMAGE)
						debugLog("cacheImage:copy original " . basename($imgfile) . ":\$size=$size, \$width=$width, \$height=$height, \$dim=$dim, \$neww=$neww; \$newh=$newh; \$quality=$quality, \$thumb=$thumb, \$crop=$crop, \$rotate=$rotate; \$allowscale=$allowscale;");
					clearstatcache();
					return true;
				}
			}
			if ($allowscale) {
				$sizes = propSizes($size, $width, $height, $w, $h, $thumb, $image_use_side, $dim);
				list($neww, $newh) = $sizes;
			}
			if (DEBUG_IMAGE)
				debugLog("cacheImage:no crop " . basename($imgfile) . ":\$size=$size, \$width=$width, \$height=$height, \$dim=$dim, \$neww=$neww; \$newh=$newh; \$quality=$quality, \$thumb=$thumb, \$crop=$crop, \$rotate=$rotate; \$allowscale=$allowscale;");
			$newim = zp_createImage($neww, $newh);
			if (!zp_resampleImage($newim, $im, 0, 0, 0, 0, $neww, $newh, $w, $h)) {
				imageError('404 Not Found', sprintf(gettext('Image %s not renderable (resample).'), filesystemToInternal($imgfile)), 'err-failimage.png');
			}
			if (($thumb && $sharpenthumbs) || (!$thumb && $sharpenimages)) {
				if (!zp_imageUnsharpMask($newim, getOption('sharpen_amount'), getOption('sharpen_radius'), getOption('sharpen_threshold'))) {
					imageError('404 Not Found', sprintf(gettext('Image %s not renderable (unsharp).'), filesystemToInternal($imgfile)), 'err-failimage.png');
				}
			}
		}

		$imgEffects = explode(',', $effects);
		if (in_array('gray', $imgEffects)) {
			zp_imageGray($newim);
		}

		if ($watermark_image) {
			$offset_h = getOption('watermark_h_offset') / 100;
			$offset_w = getOption('watermark_w_offset') / 100;
			$percent = getOption('watermark_scale') / 100;
			$watermark = zp_imageGet($watermark_image);
			if (!$watermark) {
				imageError('404 Not Found', sprintf(gettext('Watermark %s not renderable.'), $watermark_image), 'err-failimage.png');
			}
			$watermark_width = zp_imageWidth($watermark);
			$watermark_height = zp_imageHeight($watermark);
			$imw = zp_imageWidth($newim);
			$imh = zp_imageHeight($newim);
			$nw = sqrt(($imw * $imh * $percent) * ($watermark_width / $watermark_height));
			$nh = $nw * ($watermark_height / $watermark_width);
			$r = sqrt(($imw * $imh * $percent) / ($watermark_width * $watermark_height));
			if (!getOption('watermark_allow_upscale')) {
				$r = min(1, $r);
			}
			$nw = round($watermark_width * $r);
			$nh = round($watermark_height * $r);
			if (($nw != $watermark_width) || ($nh != $watermark_height)) {
				$watermark = zp_imageResizeAlpha($watermark, $nw, $nh);
				if (!$watermark) {
					imageError('404 Not Found', sprintf(gettext('Watermark %s not resizeable.'), $watermark_image), 'err-failimage.png');
				}
			}
			// Position Overlay in Bottom Right
			$dest_x = max(0, floor(($imw - $nw) * $offset_w));
			$dest_y = max(0, floor(($imh - $nh) * $offset_h));
			if (DEBUG_IMAGE)
				debugLog("Watermark:" . basename($imgfile) . ": \$offset_h=$offset_h, \$offset_w=$offset_w, \$watermark_height=$watermark_height, \$watermark_width=$watermark_width, \$imw=$imw, \$imh=$imh, \$percent=$percent, \$r=$r, \$nw=$nw, \$nh=$nh, \$dest_x=$dest_x, \$dest_y=$dest_y");
			if (!zp_copyCanvas($newim, $watermark, $dest_x, $dest_y, 0, 0, $nw, $nh)) {
				imageError('404 Not Found', sprintf(gettext('Image %s not renderable (copycanvas).'), filesystemToInternal($imgfile)), 'err-failimage.png');
			}
			zp_imageKill($watermark);
		}

		// Create the cached file (with lots of compatibility)...
		@chmod($newfile, 0666);
		if (zp_imageOutput($newim, getSuffix($newfile), $newfile, $quality)) { //	successful save of cached image
			if (getOption('ImbedIPTC') && getSuffix($newfilename) == 'jpg' && GRAPHICS_LIBRARY != 'Imagick') { // the imbed function works only with JPEG images
				global $_zp_extra_filetypes; //	because we are doing the require in a function!
				if (!$_zp_extra_filetypes)
					$_zp_extra_filetypes = array();
				require_once(dirname(__FILE__) . '/functions.php'); //	it is ok to increase memory footprint now since the image processing is complete
				$iptc = array(
								'1#090'	 => chr(0x1b) . chr(0x25) . chr(0x47), //	character set is UTF-8
								'2#115'	 => $_zp_gallery->getTitle() //	source
				);
				$iptc_data = zp_imageIPTC($imgfile);
				if ($iptc_data) {
					$iptc = array_merge(iptcparse($iptc_data), $iptc);
				}
				$imgfile = str_replace(ALBUM_FOLDER_SERVERPATH, '', $imgfile);
				$imagename = basename($imgfile);
				$albumname = dirname($imgfile);
				$image = newImage(newAlbum($albumname), $imagename);
				$copyright = $image->getCopyright();
				if (empty($copyright)) {
					$copyright = getOption('default_copyright');
				}
				if (!empty($copyright)) {
					$iptc['2#116'] = $copyright;
				}
				$credit = $image->getCredit();
				if (!empty($credit)) {
					$iptc['2#110'] = $credit;
				}
				$iptc_result = '';
				foreach ($iptc as $tag => $string) {
					$tag_parts = explode('#', $tag);
					if (is_array($string)) {
						foreach ($string as $element) {
							$iptc_result .= iptc_make_tag($tag_parts[0], $tag_parts[1], $element);
						}
					} else {
						$iptc_result .= iptc_make_tag($tag_parts[0], $tag_parts[1], $string);
					}
				}
				$content = iptcembed($iptc_result, $newfile);
				$fw = fopen($newfile, 'w');
				fwrite($fw, $content);
				fclose($fw);
				clearstatcache();
			}
			@chmod($newfile, FILE_MOD);
			if (DEBUG_IMAGE)
				debugLog('Finished:' . basename($imgfile));
		} else {
			if (DEBUG_IMAGE)
				debugLog('cacheImage: failed to create ' . $newfile);
			imageError('404 Not Found', sprintf(gettext('cacheImage: failed to create %s'), $newfile), 'err-failimage.png');
		}
		@chmod($newfile, FILE_MOD);
		zp_imageKill($newim);
		zp_imageKill($im);
	} catch (Exception $e) {
		debugLog('cacheImage(' . $newfilename . ') exception: ' . $e->getMessage());
		imageError('404 Not Found', sprintf(gettext('cacheImage(%1$s) exception: %2$s'), $newfilename, $e->getMessage()), 'err-failimage.png');
		return false;
	}
	clearstatcache();
	return true;
}

/* Determines the rotation of the image looking EXIF information.
 *
 * @param string $imgfile the image name
 * @return false when the image should not be rotated, or the degrees the
 *         image should be rotated otherwise.
 *
 * PHP GD do not support flips so when a flip is needed we make a
 * rotation that get close to that flip. But I don't think any camera will
 * fill a flipped value in the tag.
 */

function getImageRotation($imgfile) {
	$imgfile = substr(filesystemToInternal($imgfile), strlen(ALBUM_FOLDER_SERVERPATH));
	$result = query_single_row('SELECT EXIFOrientation FROM ' . prefix('images') . ' AS i JOIN ' . prefix('albums') . ' as a ON i.albumid = a.id WHERE ' . db_quote($imgfile) . ' = CONCAT(a.folder,"/",i.filename)');
	if (is_array($result) && array_key_exists('EXIFOrientation', $result)) {
		$splits = preg_split('/!([(0-9)])/', $result['EXIFOrientation']);
		$rotation = $splits[0];
		switch ($rotation) {
			case 1 : return false; // none
			case 2 : return false; // mirrored
			case 3 : return 180; // upside-down (not 180 but close)
			case 4 : return 180; // upside-down mirrored
			case 5 : return 270; // 90 CW mirrored (not 270 but close)
			case 6 : return 270; // 90 CCW
			case 7 : return 90; // 90 CCW mirrored (not 90 but close)
			case 8 : return 90; // 90 CW
		}
	}
	return false;
}
?>
