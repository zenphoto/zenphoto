<?php
/**
 * Provides extensions to the image utilities to crop images.
 *
 * Places an image crop button in the image utilities box of the images tab.
 *
 * You can either apply the crop to the original image or you can copy a link to the cropped
 * image and paste it elsewhere in your theme (or other web pages.)
 * <b>Note:</b> There is no <i>undo</i> once a crop is applied to the original image.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage media
 */
if (isset($_REQUEST['performcrop'])) {
	if (!defined('OFFSET_PATH'))
		define('OFFSET_PATH', 3);
	require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');
	require_once(dirname(dirname(__FILE__)) . '/functions-image.php');
	admin_securityChecks(ALBUM_RIGHTS, $return = currentRelativeURL());
} else {
	zp_register_filter('admin_toolbox_image', 'crop_image::toolbox');
	zp_register_filter('edit_image_utilities', 'crop_image::edit', 99999); // we want this one to come right after the crop thumbnail button
	$plugin_is_filter = defaultExtension(5 | ADMIN_PLUGIN);
	$plugin_description = gettext("An image cropping tool.");
	$plugin_author = "Stephen Billard (sbillard)";
	return;
}

class crop_image {

	static function toolbox($albumname, $imagename) {
		$album = newAlbum($albumname);
		if ($album->isMyItem(ALBUM_RIGHTS)) {
			$image = newimage($album, $imagename);
			if (isImagePhoto($image)) {
				?>
				<li>
					<a href="<?php echo WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/crop_image.php?a=<?php echo pathurlencode($albumname); ?>
						 &amp;i=<?php echo urlencode($imagename); ?>&amp;performcrop=frontend "><?php echo gettext("Crop image"); ?></a>
				</li>
				<?php
			}
		}
	}

	static function edit($output, $image, $prefix, $subpage, $tagsort, $singleimage) {
		if (isImagePhoto($image)) {
			if (is_array($image->filename)) {
				$albumname = dirname($image->filename['source']);
				$imagename = basename($image->filename['source']);
			} else {
				$albumname = $image->albumlink;
				$imagename = $image->filename;
			}
			if ($singleimage)
				$singleimage = '&amp;singleimage=' . $singleimage;
			$output .=
							'<div class="button buttons tooltip" title="' . gettext('Permanently crop the actual image.') . '">' . "\n" .
							'<a href="' . WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/crop_image.php?a=' . pathurlencode($albumname) . "\n" .
							'&amp;i=' . urlencode($imagename) . '&amp;performcrop=backend&amp;subpage=' . $subpage . $singleimage . '&amp;tagsort=' . html_encode($tagsort) . '">' . "\n" .
							'<img src="images/shape_handles.png" alt="" />' . gettext("Crop image") . '</a>' . "\n" .
							'<br class="clearall">' .
							'</div>' . "\n";
		}
		return $output;
	}

}

$albumname = sanitize_path($_REQUEST['a']);
$imagename = sanitize($_REQUEST['i']);
$album = newAlbum($albumname);
if (!$album->isMyItem(ALBUM_RIGHTS)) { // prevent nefarious access to this page.
	if (!zp_apply_filter('admin_managed_albums_access', false, $return)) {
		header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . $return);
		exitZP();
	}
}
if (isset($_REQUEST['singleimage'])) {
	$singleimage = sanitize($_REQUEST['singleimage']);
} else {
	$singleimage = '';
}

// get what image side is being used for resizing
$use_side = getOption('image_use_side');
// get full width and height
$albumobj = newAlbum($albumname);
$imageobj = newImage($albumobj, $imagename);

if (isImagePhoto($imageobj)) {
	$imgpath = $imageobj->localpath;
	$imagepart = basename($imgpath);
	$timg = zp_imageGet($imgpath);
	$width = $imageobj->getWidth();
	$height = $imageobj->getHeight();
} else {
	die(gettext('attempt to crop an object which is not an image.'));
}

// get appropriate $sizedwidth and $sizedheight
switch ($use_side) {
	case 'longest':
		$originalSize = min($width, $height);
		$size = min(400, $width, $height);
		if ($width >= $height) {
			$sr = $size / $width;
			$sizedwidth = $size;
			$sizedheight = round($height / $width * $size);
		} else {
			$sr = $size / $height;
			$sizedwidth = round($width / $height * $size);
			$sizedheight = $size;
		}
		break;
	case 'shortest':
		$originalSize = min($width, $height);
		$size = min(400, $width, $height);
		if ($width < $height) {
			$sr = $size / $width;
			$sizedwidth = $size;
			$sizedheight = round($height / $width * $size);
		} else {
			$sr = $size / $height;
			$sizedwidth = round($width / $height * $size);
			$sizedheight = $size;
		}
		break;
	case 'width':
		$originalSize = $size = $width;
		$sr = 1;
		$sizedwidth = $size;
		$sizedheight = round($height / $width * $size);
		break;
	case 'height':
		$originalSize = $size = $height;
		$sr = 1;
		$sizedwidth = round($width / $height * $size);
		$sizedheight = $size;
		break;
}
$pasteobj = isset($_REQUEST['performcrop']) && $_REQUEST['performcrop'] == 'pasteobj';

$imageurl = getImageProcessorURI(array($size, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL), $albumname, $imagepart);
$iW = round($sizedwidth * 0.9);
$iH = round($sizedheight * 0.9);
$iX = round($sizedwidth * 0.05);
$iY = round($sizedheight * 0.05);

if (isset($_REQUEST['crop'])) {
	XSRFdefender('crop');
	$cw = $_REQUEST['w'];
	$ch = $_REQUEST['h'];
	$cx = $_REQUEST['x'];
	$cy = $_REQUEST['y'];

	$rw = $width / $sizedwidth;
	$rh = $height / $sizedheight;
	$cw = round($cw * $rw);
	$ch = round($ch * $rh);
	$cx = round($cx * $rw);
	$cy = round($cy * $rh);

//create a new image with the set cropping
	$quality = getOption('full_image_quality');
	$rotate = false;
	if (zp_imageCanRotate()) {
		$rotate = getImageRotation($imageobj);
	}
	if (DEBUG_IMAGE)
		debugLog("image_crop: crop " . basename($imgpath) . ":\$cw=$cw, \$ch=$ch, \$cx=$cx, \$cy=$cy \$rotate=$rotate");

	if ($rotate) {
		$timg = zp_rotateImage($timg, $rotate);
	}

	$newim = zp_createImage($cw, $ch);
	zp_resampleImage($newim, $timg, 0, 0, $cx, $cy, $cw, $ch, $cw, $ch, getSuffix($imagename));
	@chmod($imgpath, 0777);
	@unlink($imgpath);
	if (zp_imageOutput($newim, getSuffix($imgpath), $imgpath, $quality)) {
		if (DEBUG_IMAGE)
			debugLog('image_crop Finished:' . basename($imgpath));
	} else {
		if (DEBUG_IMAGE)
			debugLog('image_crop: failed to create ' . $imgpath);
	}
	@chmod($imgpath, FILE_MOD);
	zp_imageKill($newim);
	zp_imageKill($timg);
	Gallery::clearCache($albumname);
// update the image data
	$imageobj->set('rotation', 0);
	$imageobj->updateDimensions();
	$imageobj->set('thumbX', NULL);
	$imageobj->set('thumbY', NULL);
	$imageobj->set('thumbW', NULL);
	$imageobj->set('thumbH', NULL);
	$imageobj->save();

	if ($_REQUEST['performcrop'] == 'backend') {
		$return = FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit&album=' . pathurlencode($albumname) . '&saved&subpage=' . sanitize($_REQUEST['subpage']) . '&tagsort=' . sanitize($_REQUEST['tagsort']) . '&tab=imageinfo';
		if ($singleimage)
			$return .= '&singleimage=' . html_encode($singleimage);
	} else {
		$return = FULLWEBPATH . $imageobj->getLink();
	}

	header('Location: ' . $return);
	exitZP();
}
if (isset($_REQUEST['subpage'])) {
	$subpage = sanitize($_REQUEST['subpage']);
	$tagsort = sanitize($_REQUEST['tagsort']);
} else {
	$subpage = $tagsort = '';
}

printAdminHeader('edit', gettext('crop image'));
if ($pasteobj) {
	?>
	<style>
		body {
			text-align: center;
			background-color: white;
			background-image: none;
		}
		#pasteobj {
			margin: 10px;
		}
	</style>
	<?php
}
?>

<script src="<?php echo WEBPATH . '/' . ZENFOLDER ?>/js/jquery.Jcrop.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/htmlencoder.js"></script>
<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER ?>/js/jquery.Jcrop.css" type="text/css" />
<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/crop_image/crop_image.css" type="text/css" />
<script type="text/javascript" >
	//<!-- <![CDATA[
	var jcrop_api;
	var sizedWidth = <?php echo $sizedwidth ?>;
	var sizedHeight = <?php echo $sizedheight ?>;
	var oldSize = <?php echo $size; ?>;
	jQuery(window).load(function () {
		initJcrop();
		function initJcrop() {
			jcrop_api = jQuery.Jcrop('#cropbox');

			jcrop_api.setOptions({
				onchange: showCoords,
				onSelect: showCoords,
				bgOpacity: .4,
				bgColor: 'black'
			});
			jcrop_api.setOptions({aspectRatio: 0});
			resetBoundingBox();
		}
<?php
if ($pasteobj && isset($_REQUEST['size'])) {
	?>
			jQuery('#new_size').val(<?php echo (int) sanitize_numeric($_REQUEST['size']); ?>);
			sizeChange();
	<?php
}
?>
		jQuery('#aspect-ratio-width').keyup(aspectChange);
		jQuery('#aspect-ratio-height').keyup(aspectChange);
		jQuery('#new_size').keyup(sizeChange);
		$('#crop').removeClass('dirty');
	});

	function sizeChange() {
		var size = jQuery('#new_size').val();
		if (size > 0) {
			r = oldSize / size;
			sizedWidth = Math.round(sizedWidth * r);
			sizedHeight = Math.round(sizedHeight * r);
			oldSize = size;
			showCoords(jcrop_api.tellSelect());
		}
	}

	function watermarkChange() {
		showCoords(jcrop_api.tellSelect());
	}

	function resetButton() {
		jcrop_api.setOptions({aspectRatio: 0});
		$('#aspect-ratio-width').val('');
		$('#aspect-ratio-height').val('');
		sizedWidth = <?php echo $sizedwidth ?>;
		sizedHeight = <?php echo $sizedheight ?>;
		jQuery('#new_size').val(<?php echo $originalSize; ?>);
		resetBoundingBox();
		showCoords(jcrop_api.tellSelect());
		$('#crop').removeClass('dirty');
	}

	function aspectChange() {
		var aspectWidth = jQuery('#aspect-ratio-width').val();
		var aspectHeight = jQuery('#aspect-ratio-height').val();
		if (!aspectWidth)
			aspectWidth = aspectHeight;
		if (!aspectHeight)
			aspectHeight = aspectWidth;
		if (aspectHeight) {
			jcrop_api.setOptions({aspectRatio: aspectWidth / aspectHeight});
		} else {
			jcrop_api.setOptions({aspectRatio: 0});
		}
		showCoords(jcrop_api.tellSelect());
	}

	function swapAspect() {
		var aspectHeight = $('#aspect-ratio-width').val();
		var aspectWidth = $('#aspect-ratio-height').val();
		$('#aspect-ratio-width').val(aspectWidth);
		$('#aspect-ratio-height').val(aspectHeight);
		jcrop_api.setOptions({aspectRatio: aspectWidth / aspectHeight});
		showCoords(jcrop_api.tellSelect());
	}

	// Our simple event handler, called from onchange and onSelect
	// event handlers, as per the Jcrop invocation above
	function showCoords(c) {

		var new_width = Math.round(c.w * (<?php echo $width ?> / sizedWidth));
		var new_height = Math.round(c.h * (<?php echo $height ?> / sizedHeight));

		jQuery('#x').val(c.x);
		jQuery('#y').val(c.y);
		jQuery('#x2').val(c.x2);
		jQuery('#y2').val(c.y2);
		jQuery('#w').val(c.w);
		jQuery('#h').val(c.h);
		jQuery('#new-width').text(new_width);
		jQuery('#new-height').text(new_height);

		cw = Math.round(c.w * <?php echo $width / $sizedwidth; ?>);
		ch = Math.round(c.h * <?php echo $width / $sizedwidth; ?>);
		cx = Math.round(c.x * <?php echo $width / $sizedwidth; ?>);
		cy = Math.round(c.y * <?php echo $height / $sizedheight; ?>);
		wmk = jQuery('#watermark').val();
		if (wmk == '') {
			wmk = '!';
		}

		uri = '<?php echo WEBPATH . '/' . ZENFOLDER . '/i.php?a=' . pathurlencode($albumname) . '&i=' . urlencode($imagename); ?>' + '&w=' + new_width + '&h=' + new_height + '&cw=' + cw + '&ch=' + ch + '&cx=' + cx + '&cy=' + cy + '&wmk=' + wmk;
		jQuery('#imageURI').val(uri);
		jQuery('#imageURI').attr('size', uri.length + 10);
		$('#crop').addClass('dirty');
	}

	function resetBoundingBox() {
		jcrop_api.setSelect([<?php echo $iX; ?>, <?php echo $iY; ?>, <?php echo $iX + $iW; ?>, <?php echo $iY + $iH; ?>]);
	}

	function checkCoords() {
		return confirm('<?php echo gettext('Are you sure you want to permanently alter this image?'); ?>');
	}

	// ]]> -->
</script>
</head>
<body>
	<?php
	if ($pasteobj) {
		?>

		<div id="pasteobj">
			<?php
		} else {
			printLogoAndLinks();
			?>

			<div id="main">
				<?php printTabs(); ?>
				<div id="content">
					<?php zp_apply_filter('admin_note', 'crop_image', ''); ?>
					<h1><?php echo gettext("Image cropping") . ": <em>" . $albumobj->name . " (" . $albumobj->getTitle() . ") /" . $imageobj->filename . " (" . $imageobj->getTitle() . ")</em>"; ?></h1>
					<div id="notice_div">
						<p><?php echo gettext('You can crop your image by dragging the crop handles on the image'); ?></p>
						<p id="notice" class="notebox" style="width:<?php echo $sizedwidth; ?>px" ><?php echo gettext('<strong>Note:</strong> If you save these changes they are permanent!'); ?></p>
					</div>
					<?php
				}
				?>
				<div class="tabbox">

					<div style="text-align:left; float: left;">
						<!-- This is the form that our event handler fills -->
						<form class="dirtylistening" onReset="setClean('crop');"  name="crop" id="crop" action="?crop" onsubmit="return checkCoords();">
							<?php XSRFToken('crop'); ?>

							<div style="width: <?php echo $sizedwidth; ?>px; height: <?php echo $sizedheight; ?>px; margin-bottom: 10px; border: 4px solid gray;">
								<!-- This is the image we're attaching Jcrop to -->
								<img src="<?php echo html_encode(pathurlencode($imageurl)); ?>" id="cropbox" />
								<span class="floatright">
									<?php echo sprintf(gettext('(<span id="new-width">%1$u</span> x <span id="new-height">%2$u</span> pixels)'), round($iW * ($width / $sizedwidth)), round($iH * ($height / $sizedheight)));
									?>
								</span>
							</div>
							<span class="clearall" ></span>
							<p>
								<?php echo gettext('size'); ?>
								<input type = "text" name = "new_size" id = "new_size" size = "5" value = "<?php echo $originalSize; ?>" />
							</p>
							<p>
								<?php
								printf(gettext('crop width:%1$s %2$s crop height:%3$s'), '<input type="text" id="aspect-ratio-width" name="aspect-ratio-width" value="" size="5" />', '&nbsp;<span id="aspect" ><a id="swap_button" onclick="swapAspect();" title="' . gettext('swap width and height fields') . '" > <img src="crop_image/swap.png"> </a></span>&nbsp;', '<input type="text" id="aspect-ratio-height" name="aspect-ratio-height" value="" size="5" />');
								?>
							</p>
							<?php
							if ($singleimage) {
								?>
								<input type="hidden" name="singleimage" value="<?php echo html_encode($singleimage); ?>" />
								<?php
							}
							?>
							<input type="hidden" size="4" id="x" name="x" value="<?php echo $iX ?>" />
							<input type="hidden" size="4" id="y" name="y" value="<?php echo $iY ?>" />
							<input type="hidden" size="4" id="x2" name="x2" value="<?php echo $iX + $iW ?>" />
							<input type="hidden" size="4" id="y2" name="y2" value="<?php echo $iY + $iH ?>" />
							<input type="hidden" size="4" id="w" name="w" value="<?php echo $iW ?>" />
							<input type="hidden" size="4" id="h" name="h" value="<?php echo $iH ?>"  />
							<input type="hidden" id="a" name="a" value="<?php echo html_encode($albumname); ?>" />
							<input type="hidden" id="i" name="i" value="<?php echo html_encode($imagename); ?>" />
							<input type="hidden" id="tagsort" name="tagsort" value="<?php echo html_encode($tagsort); ?>" />
							<input type="hidden" id="subpage" name="subpage" value="<?php echo html_encode($subpage); ?>" />
							<input type="hidden" id="crop" name="crop" value="crop" />
							<input type="hidden" id="performcrop" name="performcrop" value="<?php echo html_encode(sanitize($_REQUEST['performcrop'])); ?>" />
							<p>
								<?php
								echo gettext('Link image watermark');
								$watermarks = getWatermarks();
								$current = IMAGE_WATERMARK;
								?>
								<select id="watermark" name="watermark" onchange="watermarkChange();">
									<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('none'); ?></option>
									<?php
									generateListFromArray(array($current), $watermarks, false, false);
									?>
								</select>
							</p>
							<p>
								<?php
								if (!$pasteobj) {
									echo linkPickerIcon($imageobj, 'imageURI', "+'&pick[picture]=' + $('#imageURI').val().replaceAll('&', ':')");
								}
								echo linkPickerItem($imageobj, 'imageURI');
								?>
							</p>
							<p class="buttons">
								<?php
								if ($_REQUEST['performcrop'] == 'backend') {
									?>
									<button type="button" value="<?php echo gettext('Back') ?>" onclick="window.location = '../admin-edit.php?page=edit&album=<?php echo pathurlencode($albumname); ?>&subpage=<?php echo $subpage . ($singleimage) ? '&singleimage=' . html_encode($singleimage) : ''; ?>&tagsort=<?php echo html_encode($tagsort); ?>&tab=imageinfo'">
										<?php echo BACK_ARROW_BLUE; ?>
										<strong><?php echo gettext("Back"); ?></strong>
									</button>
									<?php
								} else if ($pasteobj) {
									?>
									<button type="button" value="<?php echo gettext('Back') ?>" onclick="<?php echo linkPickerPick($imageobj, 'imageURI', "+'&pick[picture]=' + $('#imageURI').val().replaceAll('&', ':')"); ?> setClean('crop');	window.history.back();">
										<?php echo BACK_ARROW_BLUE; ?>
										<strong><?php echo gettext("Done"); ?></strong>
									</button>
									<?php
								} else {
									?>
									<button type="button" value="<?php echo gettext('Back') ?>" onclick="window.location = '../../index.php?album=<?php echo pathurlencode($albumname); ?>&image=<?php echo urlencode($imagename); ?>'">
										<?php echo BACK_ARROW_BLUE; ?>
										<strong><?php echo gettext("Back"); ?></strong>
									</button>
									<?php
								}
								if (!$pasteobj) {
									?>
									<button type="submit" id="submit" name="submit" value="<?php echo gettext('Apply the cropping') ?>">
										<?php echo CHECKMARK_GREEN; ?>
										<strong><?php echo gettext("Apply"); ?></strong>
									</button>
									<?php
								}
								?>
								<button type="reset" onclick="resetButton();" >
									<?php echo CROSS_MARK_RED; ?>
									<strong><?php echo gettext("Reset"); ?></strong>
								</button>
							</p>
						</form>
					</div>

					<br class="clearall">
				</div><!-- block -->
			</div><!-- content -->
		</div><!-- main -->

		<?php
		if (!$pasteobj) {
			?>
			<?php
			printAdminFooter();
			?>
			<?php
		}
		?>
</body>

</html>
