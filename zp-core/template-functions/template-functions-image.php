<?php
/**
 * Image related template functions
 * 
 * @since 1.7 moved to separate file from template-functions.php
 * 
 * @package zpcore\functions\template
 */

/**
 * Sets the image passed as the current image
 *
 * @param object $image the image to become current
 */
function makeImageCurrent($image) {
	if (!is_object($image))
		return;
	global $_zp_current_album, $_zp_current_image;
	$_zp_current_image = $image;
	$_zp_current_album = $_zp_current_image->getAlbum();
	set_context(ZP_INDEX | ZP_ALBUM | ZP_IMAGE);
}

/**
 * Returns the raw title of the current image.
 *
 * @return string
 */
function getImageTitle() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return $_zp_current_image->getTitle();
}

/**
 * Returns a text-only title of the current image.
 *
 * @return string
 */
function getBareImageTitle() {
	return getBare(getImageTitle());
}

/**
 * Returns the image title taged with not visible annotation.
 *
 * @return string
 */
function getAnnotatedImageTitle() {
	global $_zp_current_image;
	$title = getBareImageTitle();
	if (!$_zp_current_image->isPublished()) {
		$title .= "\n" . gettext('The image is marked un-published.');
	}
	return $title;
}

function printAnnotatedImageTitle() {
	echo html_encode(getAnnotatedImageTitle());
}

/**
 * Prints title of the current image
 *
 * @author Ozh
 */
function printImageTitle() {
	echo html_encodeTagged(getImageTitle());
}

function printBareImageTitle() {
	echo html_encode(getBareImageTitle());
}

/**
 * Returns the 'n' of n of m images
 *
 * @return int
 */
function imageNumber() {
	global $_zp_current_image, $_zp_current_search, $_zp_current_album;
	$name = $_zp_current_image->getName();
	if (in_context(ZP_SEARCH) || (in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED))) {
		$folder = $_zp_current_image->imagefolder;
		$images = $_zp_current_search->getImages();
		$c = 0;
		foreach ($images as $image) {
			$c++;
			if ($name == $image['filename'] && $folder == $image['folder']) {
				return $c;
			}
		}
	} else {
		return $_zp_current_image->getIndex() + 1;
	}
	return false;
}

/**
 * Returns the image date of the current image in yyyy-mm-dd hh:mm:ss format.
 * Pass it a date format string for custom formatting
 *
 * @param string $format A datetime format, if using localized dates an ICU dateformat
 * @return string
 */
function getImageDate($format = null) {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	$d = $_zp_current_image->getDateTime();
	if (empty($d) || ($d == '0000-00-00 00:00:00')) {
		return false;
	}
	if (is_null($format)) {
		return $d;
	}
	return zpFormattedDate($format, strtotime($d));
}

/**
 * Prints the date of the current album
 *
 * @param string $before Insert here the text to be printed before the date.
 * @param string $format A datetime format, if using localized dates an ICU dateformat
 */
function printImageDate($before = '', $format = null) {
	global $_zp_current_image;
	if (is_null($format)) {
		$format = DATETIME_DISPLAYFORMAT;
	}
	$date = getImageDate($format);
	if ($date) {
		if ($before) {
			$date = '<span class="beforetext">' . $before . '</span>' . $date;
		}
	}
	echo html_encodeTagged($date);
}

// IPTC fields
/**
 * Returns the Location field of the current image
 *
 * @return string
 */
function getImageLocation() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return $_zp_current_image->getLocation();
}

/**
 * Returns the City field of the current image
 *
 * @return string
 */
function getImageCity() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return $_zp_current_image->getCity();
}

/**
 * Returns the State field of the current image
 *
 * @return string
 */
function getImageState() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return $_zp_current_image->getState();
}

/**
 * Returns the Country field of the current image
 *
 * @return string
 */
function getImageCountry() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return $_zp_current_image->getCountry();
}

/**
 * Returns the raw description of the current image.
 * new lines are replaced with <br /> tags
 *
 * @return string
 */
function getImageDesc() {
	if (!in_context(ZP_IMAGE)) {
		return false;
	}
	global $_zp_current_image;
	if (!$_zp_current_image->checkAccess()) {
		return '<p>' . gettext('<em>This image is protected.</em>') . '</p>';
	}
	return $_zp_current_image->getDesc();
}

/**
 * Returns a text-only description of the current image.
 *
 * @return string
 */
function getBareImageDesc() {
	return getBare(getImageDesc());
}

/**
 * Prints the description of the current image.
 * Converts and displays line breaks set in the admin field as <br />.
 *
 */
function printImageDesc() {
	echo html_encodeTagged(getImageDesc());
}

function printBareImageDesc() {
	echo html_encode(getBareImageDesc());
}

/**
 * A composit for getting image data
 *
 * @param string $field which field you want
 * @return string
 */
function getImageData($field) {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return get_language_string($_zp_current_image->get($field));
}

/**
 * Returns the custom_data field of the current image
 *
 * @return string
 */
function getImageCustomData() {
	Global $_zp_current_image;
	return $_zp_current_image->getCustomData();
}

/**
 * Prints the custom_data field of the current image.
 * Converts and displays line breaks set in the admin field as <br />.
 *
 * @return string
 */
function printImageCustomData() {
	$data = getImageCustomData();
	$data = str_replace("\r\n", "\n", $data);
	$data = str_replace("\n", "<br />", $data);
	echo $data;
}

/**
 * Prints arbitrary data from the image object
 *
 * @param string $field the field name of the data desired
 * @param string $label text to label the field.
 * @author Ozh
 */
function printImageData($field, $label = '') {
  global $_zp_current_image;
  $text = getImageData($field);
  if (!empty($text)) {
    echo html_encodeTagged($label . $text);
  }
}

/**
 * Returns the file size of the full original image
 * 
 * @since 1.5.2
 * 
 * @global obj $_zp_current_image
 * @return int
 */
function getFullImageFilesize() {
	global $_zp_current_image;
	$filesize = $_zp_current_image->getFilesize();
	if($filesize) {
		return byteConvert($filesize);
	}
}

/**
 * True if there is a next image
 *
 * @return bool
 */
function hasNextImage() {
  global $_zp_current_image;
  if (is_null($_zp_current_image))
    return false;
  return $_zp_current_image->getNextImage();
}

/**
 * True if there is a previous image
 *
 * @return bool
 */
function hasPrevImage() {
  global $_zp_current_image;
  if (is_null($_zp_current_image))
    return false;
  return $_zp_current_image->getPrevImage();
}

/**
 * Returns the url of the next image.
 *
 * @return string
 */
function getNextImageURL() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_album, $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	$nextimg = $_zp_current_image->getNextImage();
	return $nextimg->getLink();
}

/**
 * Returns the url of the previous image.
 *
 * @return string
 */
function getPrevImageURL() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_album, $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	$previmg = $_zp_current_image->getPrevImage();
	return $previmg->getLink();
}

/**
 * Returns the thumbnail of the previous image.
 *
 * @return string
 */
function getPrevImageThumb() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	$img = $_zp_current_image->getPrevImage();
	return $img->getThumb();
}

/**
 * Returns the thumbnail of the next image.
 *
 * @return string
 */
function getNextImageThumb() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	$img = $_zp_current_image->getNextImage();
	return $img->getThumb();
}

/**
 * Returns the url of the current image.
 *
 * @return string
 */
function getImageURL() {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	return $_zp_current_image->getLink();
}

/**
 * Prints the link to the current  image.
 *
 * @param string $text text for the link
 * @param string $title title tag for the link
 * @param string $class optional style class for the link
 * @param string $id optional style id for the link
 */
function printImageURL($text, $title, $class = NULL, $id = NULL) {
	printLinkHTML(getImageURL(), $text, $title, $class, $id);
}

/**
 * Returns the Metadata infromation from the current image
 *
 * @param $image optional image object
 * @param string $displayonly set to true to return only the items selected for display
 * @return array
 */
function getImageMetaData($image = NULL, $displayonly = true) {
	global $_zp_current_image, $_zp_exifvars;
	if (is_null($image)) {
		$image = $_zp_current_image;
	}
	if (is_null($image) || !$image->hasMetaData()) {
		return false;
	}
	$data = $image->getMetaData($displayonly);
	if (count($data) > 0) {
		return $data;
	}
	return false;
}

/**
 * Prints the Metadata data of the current image
 *
 * @param string $title title tag for the class
 * @param bool $toggle set to true to get a javascript toggle on the display of the data
 * @param string $id style class id
 * @param string $class style class
 * @author Ozh
 */
function printImageMetadata($title = NULL, $toggle = true, $id = 'imagemetadata', $class = null, $span = NULL) {
	global $_zp_exifvars, $_zp_current_image;
	if (false === ($exif = getImageMetaData($_zp_current_image, true))) {
		return;
	}
	if (is_null($title)) {
		$title = gettext('Image Info');
	}
	if ($class) {
		$class = ' class="' . $class . '"';
	}
	if (!$span) {
		$span = 'exif_link';
	}
	$dataid = $id . '_data';
	if ($id) {
		$id = ' id="' . $id . '"';
	}
	$style = '';
	if ($toggle) {
	 if(filter::hasFilter('theme_head', 'colorbox::css')) {
	 	$modal_class = ' colorbox';
	 	?>
	 	<script>
		$(document).ready(function () {
			$(".colorbox").colorbox({
				inline: true,
				href: "#imagemetadata",
				close: '<?php echo gettext("close"); ?>'
			});
		});
		</script>
		<?php
	 } else {
		 $modal_class = '';
			// we only need this eventhanlder if there is no colorbox! 
			?> 
 			<script> 
			$(document).ready(function () {
 				$(".metadata_toggle").click(function(event) { 
 					event.preventDefault(); $("#<?php echo $dataid; ?>").toggle(); 
 				}); 
			});
 			</script> 
 			<?php
		}
		$style = ' style="display:none"';
		?>
		<span id="<?php echo $span; ?>" class="metadata_title">
			<a href="#" class="metadata_toggle<?php echo $modal_class; ?>" title="<?php echo $title; ?>"><?php echo $title; ?></a>
		</span>
		<?php
	} 
	?>
	<div id="<?php echo $dataid; ?>"<?php echo $style; ?>>
		<div<?php echo $id . $class; ?>>
			<table>
				<?php
				foreach ($exif as $field => $value) {
					$label = $_zp_exifvars[$field][2];
					echo '<tr><td class="label">' . $label . ':</td><td class="value">';
					printImageMetadataValue($_zp_exifvars[$field][6], $value, $field);
					echo "</td></tr>\n";
				}
				?>
			</table>
		</div>
	</div>
	<?php
}

/**
 * Returns an array with the height & width
 *
 * @param int $size size
 * @param int $width width
 * @param int $height height
 * @param int $cw crop width
 * @param int $ch crop height
 * @param int $cx crop x axis
 * @param int $cy crop y axis
 * @param obj $image The image object for which the size is desired. NULL means the current image
 * @param string $type "image" (sizedimage) (default), "thumb" (thumbnail) required for using option settings for uncropped images
 * @return array
 */
function getSizeCustomImage($size = null, $width = NULL, $height = NULL, $cw = NULL, $ch = NULL, $cx = NULL, $cy = NULL, $image = NULL, $type = 'image') {
  global $_zp_current_image;
  if (is_null($image)) {
    $image = $_zp_current_image;
	}
  if (is_null($image)) {
    return false;
	}
  return $image->getSizeCustomImage($size, $width, $height, $cw, $ch, $cx, $cy, $type);
}

/**
 * Returns an array [width, height] of the default-sized image.
 *
 * @param int $size override the 'image_zize' option
 * @param $image object the image for which the size is desired. NULL means the current image
 *
 * @return array
 */
function getSizeDefaultImage($size = NULL, $image = NULL) {
	global $_zp_current_image;
	if (is_null($image)) {
		$image = $_zp_current_image;
	}
	if (is_null($image)) {
		return false;
	}
	if (is_null($size)) {
		$size = getOption('image_size');
	}
	return $image->getSizeCustomImage($size, NULL, NULL, NULL, NULL, NULL, NULL);
}

/**
 * Returns an array [width, height] of the original image.
 *
 * @param $image object the image for which the size is desired. NULL means the current image
 *
 * @return array
 */
function getSizeFullImage($image = NULL) {
	global $_zp_current_image;
	if (is_null($image))
		$image = $_zp_current_image;
	if (is_null($image))
		return false;
	return array($image->getWidth(), $image->getHeight());
}

/**
 * The width of the default-sized image (in printDefaultSizedImage)
 *
 * @param $image object the image for which the size is desired. NULL means the current image
 *
 * @return int
 */
function getDefaultWidth($size = NULL, $image = NULL) {
	$size_a = getSizeDefaultImage($size, $image);
	return $size_a[0];
}

/**
 * Returns the height of the default-sized image (in printDefaultSizedImage)
 *
 * @param $image object the image for which the size is desired. NULL means the current image
 *
 * @return int
 */
function getDefaultHeight($size = NULL, $image = NULL) {
	$size_a = getSizeDefaultImage($size, $image);
	return $size_a[1];
}

/**
 * Returns the width of the original image
 *
 * @param $image object the image for which the size is desired. NULL means the current image
 *
 * @return int
 */
function getFullWidth($image = NULL) {
	global $_zp_current_image;
	if (is_null($image))
		$image = $_zp_current_image;
	if (is_null($image))
		return false;
	return $image->getWidth();
}

/**
 * Returns the height of the original image
 *
 * @param $image object the image for which the size is desired. NULL means the current image
 *
 * @return int
 */
function getFullHeight($image = NULL) {
	global $_zp_current_image;
	if (is_null($image))
		$image = $_zp_current_image;
	if (is_null($image))
		return false;
	return $image->getHeight();
}

/**
 * Returns true if the image is landscape-oriented (width is greater than height) 
 * or - kept here for backwards compatibility - square (equal widht and height)
 * 
 * @param $image object the image for which the size is desired. NULL means the current image
 *
 * @return bool
 */
function isLandscape($image = NULL) {
	global $_zp_current_image;
	if (is_null($image))
		$image = $_zp_current_image;
	if (is_null($image))
		return false;
	return ($image->isLandscape() || $image->isSquare());
}

/**
 * Returns the url to the default sized image.
 *
 * @param $image object the image for which the size is desired. NULL means the current image
 *
 * @return string
 */
function getDefaultSizedImage($image = NULL) {
	global $_zp_current_image;
	if (is_null($image))
		$image = $_zp_current_image;
	if (is_null($image))
		return false;
	return $image->getSizedImage(getOption('image_size'));
}

/**
 * Show video player with video loaded or display the image.
 *
 * @param string $alt Alt text
 * @param string $class Optional style class
 * @param string $id Optional style id
 * @param string $title Optional title attribute
 * @param obj $image optional image object, null means current image
 */
function printDefaultSizedImage($alt, $class = null, $id = null, $title = null, $image = null) {
	global $_zp_current_image;
	if (is_null($image)) {
		$image = $_zp_current_image;
	}
	if (is_null($image)) {
		return false;
	}
	if (empty($title)) {
		$title = $alt;
	}
	$attr = array(
			'alt' => html_encode($alt),
			'class' => $class,
			'title' => html_encode($title),
			'id' => $id,
			'loading' => 'lazy',
			'width' => getDefaultWidth(),
			'height' => getDefaultHeight()
	);
	if (!$image->isPublished()) {
		$attr['class'] .= " not_visible";
	}
	$album = $image->getAlbum();
	$pwd = $album->getPassword();
	if (!empty($pwd)) {
		$attr['class'] .= " password_protected";
	}
	if ($image->isPhoto()) { //Print images
		$attr['src'] = html_pathurlencode(getDefaultSizedImage());
		$attr_filtered = filter::applyFilter('standard_image_attr', $attr, $image);
		$attributes = generateAttributesFromArray($attr_filtered);
		$html = '<img' . $attributes . ' />';
		$html = filter::applyFilter('standard_image_html', $html, $image);
		echo $html;
	} else { // better be a plugin class then
		echo $image->getContent();
	}
}


/**
 * Returns the url to the thumbnail of the current image.
 * @param obj $image optional image object, null means current image
 * @return string
 */
function getImageThumb($image = null) {
	global $_zp_current_image;
	if (is_null($image)) {
		$image = $_zp_current_image;
	}
	if (is_null($image)) {
		return false;
	}
	return $image->getThumb();
}

/**
 * @param string $alt Alt text
 * @param string $class optional class attribute
 * @param string $id optional id attribute
 * @param string $title optional title attribute
 * @param obj $image optional image object, null means current image
 */
function printImageThumb($alt, $class = null, $id = null, $title = null, $image = null) {
	global $_zp_current_image;
	if (is_null($image)) {
		$image = $_zp_current_image;
	}
	if (is_null($image)) {
		return false;
	}
	if (empty($title)) {
		$title = $alt;
	}
	$attr = array(
			'alt' => html_encode($alt),
			'class' => $class,
			'title' => html_encode($title),
			'id' => $id,
			'loading' => 'lazy'
	);
	if (!$image->isPublished()) {
		$attr['class'] .= " not_visible";
	}
	$album = $image->getAlbum();
	$pwd = $album->getPassword();
	if (!empty($pwd)) {
		$attr['class'] .= " password_protected";
	}
	$attr['src'] = html_pathurlencode($image->getThumb());
	$sizes = $image->getSizeDefaultThumb();
	$attr['width'] = $sizes[0];
	$attr['height'] = $sizes[1];
	$attr_filtered = filter::applyFilter('standard_image_thumb_attr', $attr, $image);
	$attributes = generateAttributesFromArray($attr_filtered);
	$html = '<img' . $attributes . ' />';
	$html = filter::applyFilter('standard_image_thumb_html', $html, $image);
	echo $html;
}

/**
 * Gets the width and height of a default thumb for the <img> tag height/width
 * @global type $_zp_current_image
 * @param obj $image Image object, if NULL the current image is used
 * @return aray
 */
function getSizeDefaultThumb($image = NULL) {
	global $_zp_current_image;
	if (is_null($image)) {
		$image = $_zp_current_image;
	}
	return $image->getSizeDefaultThumb();
}

/**
 * Returns the url to original image.
 * It will return a protected image is the option "protect_full_image" is set
 *
 * @param $image optional image object
 * @return string
 */
function getFullImageURL($image = NULL) {
	global $_zp_current_image;
	if (is_null($image)) {
		$image = $_zp_current_image;
	}
	if (is_null($image)) {
		return false;
	}
	$outcome = getOption('protect_full_image');
	if ($outcome == 'no-access') {
		return NULL;
	}
	if ($outcome == 'unprotected') {
		return $image->getFullImageURL();
	} else {
		return getProtectedImageURL($image, $outcome);
	}
}

/**
 * Returns the "raw" url to the image in the albums folder
 *
 * @param $image optional image object
 * @return string
 *
 */
function getUnprotectedImageURL($image = NULL) {
	global $_zp_current_image;
	if (is_null($image)) {
		$image = $_zp_current_image;
	}
	if (!is_null($image)) {
		return $image->getFullImageURL();
	}
}

/**
 * Returns an url to the password protected/watermarked current image
 *
 * @param object $image optional image object overrides the current image
 * @param string $disposal set to override the 'protect_full_image' option. 'protected', "download", "unprotected" or "no-access"
 * @return string
 * */
function getProtectedImageURL($image = NULL, $disposal = NULL) {
	global $_zp_current_image;
	if (is_null($disposal)) {
		$disposal = getOption('protect_full_image');
	}
	if ($disposal == 'no-access')
		return NULL;
	if (is_null($image)) {
		if (!in_context(ZP_IMAGE))
			return false;
		if (is_null($_zp_current_image))
			return false;
		$image = $_zp_current_image;
	}
	$album = $image->getAlbum();
	$watermark_use_image = getWatermarkParam($image, WATERMARK_FULL);
	if (!empty($watermark_use_image)) {
		$wmt = $watermark_use_image;
	} else {
		$wmt = false;
	}
	$args = array('FULL', NULL, NULL, NULL, NULL, NULL, NULL, (int) getOption('full_image_quality'), NULL, NULL, NULL, $wmt, false, NULL, NULL);
	$cache_file = getImageCacheFilename($album->name, $image->filename, $args);
	$cache_path = SERVERCACHE . $cache_file;
	if ($disposal != 'download' && OPEN_IMAGE_CACHE && file_exists($cache_path)) {
		return WEBPATH . '/' . CACHEFOLDER . pathurlencode(imgSrcURI($cache_file));
	} else if ($disposal == 'unprotected') {
		return getImageURI($args, $album->name, $image->filename, $image->filemtime);
	} else {
		$params = '&q=' . getOption('full_image_quality');
		if (!empty($watermark_use_image)) {
			$params .= '&wmk=' . $watermark_use_image;
		}
		if ($disposal) {
			$params .= '&dsp=' . $disposal;
		}
		$params .= '&check=' . sha1(HASH_SEED . serialize($args));
		if (is_array($image->filename)) {
			$album = dirname($image->filename['source']);
			$image = basename($image->filename['source']);
		} else {
			$album = $album->name;
			$image = $image->filename;
		}
		return WEBPATH . '/' . ZENFOLDER . '/full-image.php?a=' . $album . '&i=' . $image . $params;
	}
}

/**
 * Returns a link to the current image custom sized to $size
 *
 * @param int $size The size the image is to be
 */
function getSizedImageURL($size) {
	return getCustomImageURL($size);
}

/**
 * Returns the url to the image with the dimensions you define with this function.
 *
 * @param int $size the size of the image to have
 * @param int $width width
 * @param int $height height
 * @param int $cropw crop width
 * @param int $croph crop height
 * @param int $cropx crop part x axis
 * @param int $cropy crop part y axis
 * @param bool $thumbStandin set true to inhibit watermarking
 * @param bool $effects image effects (e.g. set gray to force to grayscale)
 * @return string
 *
 * $size, $width, and $height are used in determining the final image size.
 * At least one of these must be provided. If $size is provided, $width and
 * $height are ignored. If both $width and $height are provided, the image
 * will have those dimensions regardless of the original image height/width
 * ratio. (Yes, this means that the image may be distorted!)
 *
 * The $crop* parameters determine the portion of the original image that
 * will be incorporated into the final image.
 *
 * $cropw and $croph "sizes" are typically proportional. That is you can
 * set them to values that reflect the ratio of width to height that you
 * want for the final image. Typically you would set them to the final
 * height and width. These values will always be adjusted so that they are
 * not larger than the original image dimensions.
 *
 * The $cropx and $cropy values represent the offset of the crop from the
 * top left corner of the image. If these values are provided, the $croph
 * and $cropw parameters are treated as absolute pixels not proportions of
 * the image. If cropx and cropy are not provided, the crop will be
 * "centered" in the image.
 *
 * When $cropx and $cropy are not provided the crop is offset from the top
 * left proportionally to the ratio of the final image size and the crop
 * size.
 *
 * Some typical croppings:
 *
 * $size=200, $width=NULL, $height=NULL, $cropw=200, $croph=100,
 * $cropx=NULL, $cropy=NULL produces an image cropped to a 2x1 ratio which
 * will fit in a 200x200 pixel frame.
 *
 * $size=NULL, $width=200, $height=NULL, $cropw=200, $croph=100, $cropx=100,
 * $cropy=10 will will take a 200x100 pixel slice from (10,100) of the
 * picture and create a 200x100 image
 *
 * $size=NULL, $width=200, $height=100, $cropw=200, $croph=120, $cropx=NULL,
 * $cropy=NULL will produce a (distorted) image 200x100 pixels from a 1x0.6
 * crop of the image.
 *
 * $size=NULL, $width=200, $height=NULL, $cropw=180, $croph=120, $cropx=NULL, $cropy=NULL
 * will produce an image that is 200x133 from a 1.5x1 crop that is 5% from the left
 * and 15% from the top of the image.
 * 
  * @param int $size the size of the image to have
 * @param int $width width
 * @param int $height height
 * @param int $cropw crop width
 * @param int $croph crop height
 * @param int $cropx crop part x axis
 * @param int $cropy crop part y axis
 * @param bool $thumbStandin set true to inhibit watermarking
 * @param bool $effects image effects (e.g. set gray to force to grayscale)
 * @param obj $image optional image object, null means current image
 */
function getCustomImageURL($size, $width = NULL, $height = NULL, $cropw = NULL, $croph = NULL, $cropx = NULL, $cropy = NULL, $thumbStandin = false, $effects = NULL, $image = null) {
	global $_zp_current_image;
	if (is_null($image)) {
		$image = $_zp_current_image;
	}
	if (is_null($image)) {
		return false;
	}
	return $image->getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin, $effects);
}

/**
 * Print normal video or custom sized images.
 * Note: a class of 'not_visible' or 'password_protected' will be added as appropriate
 *
 * Notes on cropping:
 *
 * The $crop* parameters determine the portion of the original image that will be incorporated
 * into the final image. The w and h "sizes" are typically proportional. That is you can set them to
 * values that reflect the ratio of width to height that you want for the final image. Typically
 * you would set them to the fincal height and width.
 *
 * @param string $alt Alt text for the url
 * @param int $size size
 * @param int $width width
 * @param int $height height
 * @param int $cropw crop width
 * @param int $croph crop height
 * @param int $cropx crop x axis
 * @param int $cropy crop y axis
 * @param string $class Optional style class
 * @param string $id Optional style id
 * @param bool $thumbStandin set to true to treat as thumbnail
 * @param bool $effects image effects (e.g. set gray to force grayscale)
 * @param string $title Optional title attribute
 * @param string $type "image" (sizedimage) (default), "thumb" (thumbnail) required for using option settings for uncropped images
 * @param obj $image optional image object, null means current image
 * @param bool $maxspace true for maxspace, false default
 */
function printCustomSizedImage($alt = '', $size = null, $width = NULL, $height = NULL, $cropw = NULL, $croph = NULL, $cropx = NULL, $cropy = NULL, $class = NULL, $id = NULL, $thumbStandin = false, $effects = NULL, $title = null, $type = 'image', $image = null, $maxspace = false) {
	global $_zp_current_image;
	if (is_null($image)) {
		$image = $_zp_current_image;
	}
	if (is_null($image)) {
		return false;
	}
	if ($maxspace) {
		getMaxSpaceContainer($width, $height, $image, $thumbStandin);
	}
	if (empty($title)) {
		$title = $alt;
	}
	$attr = array(
			'alt' => html_encode($alt),
			'class' => $class,
			'title' => html_encode($title),
			'id' => $id,
			'loading' => 'lazy'
	);
	if (!$image->isPublished()) {
		$attr['class'] .= " not_visible";
	}
	$album = $image->getAlbum();
	$pwd = $album->getPassword();
	if (!empty($pwd)) {
		$attr['class'] .= " password_protected";
	}
	if ($maxspace) {
		$attr['width'] = $width;
		$attr['height'] = $height;
	} else {
		$type = 'image';
		if ($thumbStandin) {
			$type = 'thumb';
		}
		$dims = getSizeCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $image, $type);
		$attr['width'] = $dims[0];
		$attr['height'] = $dims[1];
	}
	if ($image->isPhoto() || $thumbStandin) {
		if ($maxspace) {
			$attr['src'] = html_pathurlencode($image->getCustomImage(null, $width, $height, NULL, NULL, NULL, NULL, $thumbStandin, $effects));
		} else {
			$attr['src'] = html_pathurlencode($image->getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbStandin, $effects));
		}
		$attr_filtered = filter::applyFilter('custom_image_attr', $attr, $image);
		$attributes = generateAttributesFromArray($attr_filtered);
		$html = '<img ' . $attributes . ' />';
		$html = filter::applyFilter('custom_image_html', $html, $thumbStandin, $image);
		echo $html;
	} else { // better be a plugin
		echo $image->getContent($width, $height);
	}
}

/**
 * Returns a link to a un-cropped custom sized version of the current image within the given height and width dimensions.
 * Use for sized images.
 *
 * @param int $width width
 * @param int $height height
 * @return string
 */
function getCustomSizedImageMaxSpace($width, $height) {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) {
		return false;
	}
	return $_zp_current_image->getCustomSizedImageMaxSpace($width, $height, false);
}

/**
 * Returns a link to a un-cropped custom sized version of the current image within the given height and width dimensions.
 * Use for sized thumbnails.
 *
 * @param int $width width
 * @param int $height height
 * @return string
 */
function getCustomSizedImageThumbMaxSpace($width, $height) {
	global $_zp_current_image;
	if (is_null($_zp_current_image)) {
		return false;
	}
	return $_zp_current_image->getCustomSizedImageMaxSpace($width, $height, true);
}

/**
 * Creates image thumbnails which will fit un-cropped within the width & height parameters given
 *
 * @param string $alt Alt text for the url
 * @param int $width width
 * @param int $height height
 * @param string $class Optional style class
 * @param string $id Optional style id
 * @param string $title optional title attribute
 * @param obj $image optional image object, null means current image
 */
function printCustomSizedImageThumbMaxSpace($alt = '', $width = null, $height = null, $class = NULL, $id = NULL, $title = null, $image = null) {
	global $_zp_current_image;
	if (is_null($image))
		$image = $_zp_current_image;
	if (is_null($image))
		return false;
	printCustomSizedImage($alt, NULL, $width, $height,  NULL,  NULL, NULL,  NULL, $class, $id, true, NULL, $title, 'thumb', $image, true);
}

/**
 * Print normal video or un-cropped within the given height and width dimensions. Use for sized images or thumbnails in an album.
 * Note: a class of 'not_visible' or 'password_protected' will be added as appropriate
 *
 * @param string $alt Alt text for the url
 * @param int $width width
 * @param int $height height
 * @param string $class Optional style class
 * @param string $id Optional style id
 * @param string $title optional title attribute
 * @param obj $image optional image object, null means current image
 */
function printCustomSizedImageMaxSpace($alt = '', $width = null, $height = null, $class = NULL, $id = NULL, $thumb = false, $title = null, $image = null) {
	global $_zp_current_image;
	if (is_null($image))
		$image = $_zp_current_image;
	if (is_null($image))
		return false;
	printCustomSizedImage($alt, NULL, $width, $height,  NULL,  NULL, NULL,  NULL, $class, $id, $thumb, NULL, $title, 'image', $image, true);
}

/**
 * Prints link to an image of specific size
 * @param int $size how big
 * @param string $text URL text
 * @param string $title URL title
 * @param string $class optional URL class
 * @param string $id optional URL id
 */
function printSizedImageURL($size, $text, $title, $class = NULL, $id = NULL) {
	printLinkHTML(getSizedImageURL($size), $text, $title, $class, $id);
}

/**
 * Called by ***MaxSpace functions to compute the parameters to be passed to xxCustomyyy functions.
 *
 * @param int $width maxspace width
 * @param int $height maxspace height
 * @param object $image the image in question
 * @param bool $thumb true if for a thumbnail
 */
function getMaxSpaceContainer(&$width, &$height, $image, $thumb = false) {
	$image->getMaxSpaceContainer($width, $height, $thumb);
}