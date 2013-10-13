<?php
define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . '/admin-globals.php');
require_once(dirname(__FILE__) . '/functions-image.php');

admin_securityChecks(ALBUM_RIGHTS, $return = currentRelativeURL());

$albumname = sanitize_path($_REQUEST['a']);
$imagename = sanitize_path($_REQUEST['i']);

$albumobj = newAlbum($albumname);
if (!$albumobj->isMyItem(ALBUM_RIGHTS)) { // prevent nefarious access to this page.
	if (!zp_apply_filter('admin_managed_albums_access', false, $return)) {
		header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . $return);
		exitZP();
	}
}

// get what image side is being used for resizing
$use_side = getOption('image_use_side');
// get full width and height
$imageobj = newImage($albumobj, $imagename);
$currentthumbimage = $imageobj->getThumb();
setOption('image_use_side', 'longest', false);
$cropwidth = getOption("thumb_crop_width");
$cropheight = getOption("thumb_crop_height");
$imagepart = $imagename;


if (isImagePhoto($imageobj)) {
	$width = $imageobj->getWidth();
	$height = $imageobj->getHeight();
} else {
	$imgpath = $imageobj->getThumbImageFile();
	$imagepart = basename($imgpath);
	$timg = zp_imageGet($imgpath);
	$width = zp_imageWidth($timg);
	$height = zp_imageHeight($timg);
}
if (getOption('thumb_crop')) {
	$thumbcropwidth = $cropwidth;
	$thumbcropheight = $cropheight;
} else {
	if (isImagePhoto($imageobj)) {
		$thumbcropwidth = $imageobj->getWidth();
		$thumbcropheight = $imageobj->getHeight();
	} else {
		$imgpath = $imageobj->getThumbImageFile();
		$imagepart = basename($imgpath);
		$thumbcropwidth = zp_imageWidth($timg);
		$thumbcropheight = zp_imageHeight($timg);
	}
	$tsize = getOption('thumb_size');
	$max = max($thumbcropwidth, $thumbcropheight);
	$thumbcropwidth = $thumbcropwidth * ($tsize / $max);
	$thumbcropheight = $thumbcropheight * ($tsize / $max);
}

// get appropriate $sizedwidth and $sizedheight
switch ($use_side) {
	case 'longest':
		$size = min(400, $width, $height);
		if ($width >= $height) {
			$sr = $size / $width;
			$sizedwidth = $size;
			$sizedheight = round($height / $width * $size);
		} else {
			$sr = $size / $height;
			$sizedwidth = Round($width / $height * $size);
			$sizedheight = $size;
		}
		break;
	case 'shortest':
		$size = min(400, $width, $height);
		if ($width < $height) {
			$sr = $size / $width;
			$sizedwidth = $size;
			$sizedheight = round($height / $width * $size);
		} else {
			$sr = $size / $height;
			$sizedwidth = Round($width / $height * $size);
			$sizedheight = $size;
		}
		break;
	case 'width':
		$size = $width;
		$sr = 1;
		$sizedwidth = $size;
		$sizedheight = round($height / $width * $size);
		break;
	case 'height':
		$size = $height;
		$sr = 1;
		$sizedwidth = Round($width / $height * $size);
		$sizedheight = $size;
		break;
}
$args = array($size, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL);
$imageurl = getImageProcessorURI($args, $albumname, $imagepart);

$iY = round($imageobj->get('thumbY') * $sr);
$cr = max($cropwidth, $cropheight) / getOption('thumb_size');
$si = min($sizedwidth, $sizedheight);
$oW = round($si * $cr);
$oH = round($si * $cr);
$oX = round(($sizedwidth - $oW) / 2);
$oY = round(($sizedheight - $oH) / 2);
if (!is_null($iY)) {
	$iX = round($imageobj->get('thumbX') * $sr);
	$iW = round($imageobj->get('thumbW') * $sr);
	$iH = round($imageobj->get('thumbH') * $sr);
} else {
	$iW = $oW;
	$iH = $oH;
	$iX = $oX;
	$iY = $oY;
}

if (isset($_REQUEST['crop'])) {
	XSRFdefender('thumb_crop');
	$cw = $_REQUEST['w'];
	$ch = $_REQUEST['h'];
	$cx = $_REQUEST['x'];
	$cy = $_REQUEST['y'];
	if (DEBUG_IMAGE)
		debugLog("Thumbcrop-in: \$width=$width \$height=$height \$cx=$cx \$cy=$cy \$cw=$cw \$ch=$ch");
	if (isset($_REQUEST['clear_crop']) || ($cw == 0 && $ch == 0)) {
		$cx = $cy = $cw = $ch = NULL;
	} else {

		$rw = $width / $sizedwidth;
		$rh = $height / $sizedheight;
		$cw = round($cw * $rw);
		if ($cropwidth == $cropheight) {
			$ch = $cw;
		} else {
			$ch = round($ch * $rh);
		}
		$cx = round($cx * $rw);
		$cy = round($cy * $rh);
	}
	if (DEBUG_IMAGE)
		debugLog("Thumbcrop-out: \$cx=$cx \$cy=$cy \$cw=$cw \$ch=$ch");
	$imageobj->set('thumbX', $cx);
	$imageobj->set('thumbY', $cy);
	$imageobj->set('thumbW', $cw);
	$imageobj->set('thumbH', $ch);
	$imageobj->save();

	$return = '/admin-edit.php?page=edit&album=' . html_encode(pathurlencode($albumname)) . '&saved&subpage=' . html_encode(sanitize($_REQUEST['subpage'])) . '&tagsort=' . html_encode(sanitize($_REQUEST['tagsort'])) . '&tab=imageinfo';
	header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . $return);
	exitZP();
}
$subpage = sanitize($_REQUEST['subpage']);
$tagsort = sanitize($_REQUEST['tagsort']);
printAdminHeader('edit', 'thumbcrop');
?>

<script src="js/jquery.Jcrop.js" type="text/javascript"></script>
<link rel="stylesheet" href="js/jquery.Jcrop.css" type="text/css" />
<script type="text/javascript" >
	//<!-- <![CDATA[
	var jcrop_api;
	jQuery(window).load(function() {
		initJcrop();
		function initJcrop() {
			jcrop_api = jQuery.Jcrop('#cropbox');
			jcrop_api.setOptions({
				onchange: showPreview,
				onSelect: showPreview,
				bgOpacity: .4,
				bgColor: 'black'
			});
			jcrop_api.setOptions({aspectRatio: <?php echo $cropwidth . '/' . $cropheight; ?>});
			resetBoundingBox();
		}
		;
	});

	function resetCheck() {
		if ($('#clear_crop').prop('checked')) {
			jcrop_api.setSelect([<?php echo $oX; ?>, <?php echo $oY; ?>, <?php echo $oX + $oW; ?>, <?php echo $oY + $oH; ?>]);
		}
	}

	function resetBoundingBox() {
		if ($('#clear_crop').prop('checked')) {
			jcrop_api.setSelect([<?php echo $oX; ?>, <?php echo $oY; ?>, <?php echo $oX + $oW; ?>, <?php echo $oY + $oH; ?>]);
		} else {
			jcrop_api.setSelect([<?php echo $iX; ?>, <?php echo $iY; ?>, <?php echo $iX + $iW; ?>, <?php echo $iY + $iH; ?>]);
		}
	}

	// Our simple event handler, called from onchange and onSelect
	// event handlers, as per the Jcrop invocation above
	function showPreview(coords) {
		var rx = <?php echo $cropwidth; ?> / coords.w;
		var ry = <?php echo $cropheight; ?> / coords.h;

		jQuery('#preview').css({
			width: Math.round(rx * <?php echo $sizedwidth; ?>) + 'px', // we need to calcutate the resized width and height here...
			height: Math.round(ry * <?php echo $sizedheight; ?>) + 'px',
			marginLeft: '-' + Math.round(rx * coords.x) + 'px',
			marginTop: '-' + Math.round(ry * coords.y) + 'px'
		});
		jQuery('#x').val(coords.x);
		jQuery('#y').val(coords.y);
		jQuery('#x2').val(coords.x2);
		jQuery('#y2').val(coords.y2);
		jQuery('#w').val(coords.w);
		jQuery('#h').val(coords.h);
	}

	function checkCoords() {
		return true;
	}
	;
	// ]]> -->
</script>
</head>
<body>
	<?php printLogoAndLinks(); ?>

	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<h1><?php echo gettext("Custom thumbnail cropping") . ": <em>" . $albumobj->name . " (" . $albumobj->getTitle() . ") /" . $imageobj->filename . " (" . $imageobj->getTitle() . ")</em>"; ?></h1>
			<p><?php echo gettext("You can change the portion of your image which is shown in thumbnails by cropping it here."); ?></p>
			<div style="display:block">
				<div style="float: left; width:<?php echo $thumbcropwidth; ?>px; text-align: center;margin-right: 18px;  margin-bottom: 10px;">
					<img src="<?php echo html_encode(pathurlencode($currentthumbimage)); ?>" style="width:<?php echo $thumbcropwidth; ?>px;height:<?php echo $thumbcropheight; ?>px; border: 4px solid gray; float: left"/>
					<?php echo gettext("current thumbnail"); ?>
				</div>

				<div style="text-align:left; float: left;">

					<div style="width: <?php echo $sizedwidth; ?>px; height: <?php echo $sizedheight; ?>px; margin-bottom: 10px; border: 4px solid gray;">
						<!-- This is the image we're attaching Jcrop to -->
						<img src="<?php echo $imageurl; ?>" id="cropbox" />
					</div>

					<!-- This is the form that our event handler fills -->
					<form name="crop" id="crop" action="?crop" onsubmit="return checkCoords();">
						<?php XSRFToken('thumb_crop'); ?>
						<input type="hidden" size="4" id="x" name="x" value="<?php echo $iX ?>" />
						<input type="hidden" size="4" id="y" name="y" value="<?php echo $iY ?>" />
						<input type="hidden" size="4" id="x2" name="x2" value="<?php echo $iX + $iW ?>" />
						<input type="hidden" size="4" id="y2" name="y2" value="<?php echo $iY + $iH ?>" />
						<input type="hidden" size="4" id="w" name="w" value="<?php echo $iW ?>" />
						<input type="hidden" size="4" id="h" name="h" value="<?php echo $iH ?>"  />
						<input type="hidden" id="cropw" name="cropw" value="<?php echo $cropwidth; ?>" />
						<input type="hidden" id="croph" name="croph" value="<?php echo $cropheight; ?>" />
						<input type="hidden" id="a" name="a" value="<?php echo html_encode($albumname); ?>" />
						<input type="hidden" id="i" name="i" value="<?php echo html_encode($imagename); ?>" />
						<input type="hidden" id="tagsort" name="tagsort" value="<?php echo html_encode($tagsort); ?>" />
						<input type="hidden" id="subpage" name="subpage" value="<?php echo html_encode($subpage); ?>" />
						<input type="hidden" id="crop" name="crop" value="crop" />
						<?php
						if (getOption('thumb_crop')) {
							?>
							<input name="clear_crop" id="clear_crop" type="checkbox" value="1"  onclick="resetCheck();" /> <?php echo gettext("Reset to the default cropping"); ?><br />
							<br />
							<p class="buttons">
								<button type="button" onclick="resetBoundingBox();" >
									<img src="images/fail.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong>
								</button>
								<button type="submit" id="submit" name="submit" value="<?php echo gettext('Apply the cropping') ?>">
									<img src="images/pass.png" alt="" />
									<strong><?php echo gettext("Apply"); ?></strong>
								</button>
								<button type="reset" value="<?php echo gettext('Back') ?>" onclick="window.location = 'admin-edit.php?page=edit&album=<?php echo html_encode(pathurlencode($albumname)); ?>&subpage=<?php echo html_encode($subpage); ?>&tagsort=<?php echo html_encode($tagsort); ?>&tab=imageinfo'">
									<img src="images/arrow_left_blue_round.png" alt="" />
									<strong><?php echo gettext("Back"); ?></strong>
								</button>
							</p><br />

							<?php
						} else {
							echo gettext('Thumbnail cropping is disabled. Enable this option for the theme if you wish cropped thumbnails.');
						}
						?>
					</form>

				</div>

				<div style="float: left; width:<?php echo $cropwidth; ?>px; text-align: center; margin-left: 10px; margin-bottom: 10px;">
					<div style="width:<?php echo $cropwidth; ?>px;height:<?php echo $cropheight; ?>px; overflow:hidden; border: 4px solid green; float: left">
						<img src="<?php echo html_encode(pathurlencode($imageurl)); ?>" id="preview" />
					</div>
					<?php echo gettext("thumbnail preview"); ?>
				</div>

				<!-- set the initial view for the preview -->
				<script type="text/javascript" >
	// <!-- <![CDATA[
	jQuery('#preview').css({
		width: '<?php echo round($cropwidth / $iW * $sizedwidth); ?>px',
		height: '<?php echo round($cropheight / $iH * $sizedheight); ?>px',
		marginLeft: '-<?php echo round($cropwidth / $iW * $iX); ?>px',
		marginTop: '-<?php echo round($cropheight / $iH * $iY); ?>px'
	});
	// ]]> -->
				</script>
				<br style="clear: both" />
			</div><!-- block -->

		</div><!-- content -->

		<?php printAdminFooter(); ?>
	</div><!-- main -->
</body>

</html>
