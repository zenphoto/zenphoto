<?php

// force UTF-8 Ø

if (!defined('WEBPATH')) die();
$_noFlash = false;
if ((($personality = getOption('Theme_personality'))!="Simpleviewer") || !MOD_REWRITE) {
	$_noFlash = true;
} else {  // Simpleviewer initialization stuff
	if (isset($_GET['noflash'])) {
		$_noFlash = true;
		zp_setCookie("noFlash", "noFlash");
	} elseif (zp_getCookie("noFlash") != '') {
		$_noFlash = true;
	}
	// Change the Simpleviewer configuration here

	$maxImageWidth="600";
	$maxImageHeight="600";

	$preloaderColor="0xFFFFFF";
	$textColor="0xFFFFFF";
	$frameColor="0xFFFFFF";

	$frameWidth="10";
	$stagePadding="20";

	$thumbnailColumns="3";
	$thumbnailRows="5";
	$navPosition="left";

	$enableRightClickOpen="true";

	$backgroundImagePath="";
	// End of Simpeviewer config
}
$map = function_exists('printGoogleMap');
if (!isset($_GET['format']) || $_GET['format'] != 'xml') {
	$themeResult = getTheme($zenCSS, $themeColor, 'kish-my father');
	if ($_noFlash) {
		$backgroundColor = "#0";  // who cares, we won't use it
	} else {
		$backgroundColor = parseCSSDef($zenCSS);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php echo getBareGalleryTitle(); ?> | <?php echo getBareAlbumTitle();?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/effervescence_plus/common.css" type="text/css" />
	<?php
	$oneImagePage = false;
	$show = false;
	switch ($personality) {
	case 'Simpleviewer':
		echo "<script type=\"text/javascript\" src=\"$_zp_themeroot/scripts/swfobject.js\"></script>\n";
		$oneImagePage = true;
		break;
	case 'Slimbox':
		echo "<link rel=\"stylesheet\" href=\"$_zp_themeroot/slimbox.css\" type=\"text/css\" media=\"screen\" />\n";
		echo "<script type=\"text/javascript\" src=\"$_zp_themeroot/scripts/mootools.v1.11.js\"></script>\n";
		echo "<script type=\"text/javascript\" src=\"$_zp_themeroot/scripts/slimbox.js\"></script>\n";
		break;
	case 'Smoothgallery':
		echo "<link rel=\"stylesheet\" href=\"$_zp_themeroot/jd.gallery.css\" type=\"text/css\" media=\"screen\" charset=\"utf-8\" />\n";
		echo "<script src=\"$_zp_themeroot/scripts/mootools.v1.11.js\" type=\"text/javascript\"></script>\n";
		echo "<script src=\"$_zp_themeroot/scripts/jd.gallery.js\" type=\"text/javascript\"></script>\n";
		setOption('thumb_crop_width', 100, false);
		setOption('thumb_crop_height', 75, false);
		$oneImagePage = true;
		$show = getOption('Slideshow') || (isset($_GET['slideshow']));
		break;
	}
	echo "<script type=\"text/javascript\" src=\"$_zp_themeroot/scripts/bluranchors.js\"></script>\n";
	global $_zp_current_album;
?>
</head>

<body onload="blurAnchors()">
<?php zp_apply_filter('theme_body_open'); ?>
<?php if ($personality == 'Smoothgallery') { ?>
<script type="text/javascript">
	//<!-- <![CDATA[
	function startGallery() {
		var myGallery = new gallery($('smoothImages'), {
			timed: <?php ($show) ? print 'true' : print 'false'; ?>
		});
	}
	window.addEvent('domready',startGallery);
	// ]]> -->
</script>
<?php } ?>

	<!-- Wrap Header -->
	<div id="header">
			<div id="gallerytitle">

			<!-- Subalbum Navigation -->
				<div class="albnav">
					<div class="albprevious">
					<?php
						$album = getPrevAlbum();
	 						if (is_null($album)) {
								echo '<div class="albdisabledlink">&laquo;  '.gettext('prev').'</div>';
							} else {
							echo '<a href="' .
									rewrite_path("/" . pathurlencode($album->name), "/index.php?album=" . pathurlencode($album->name)) .
									'" title="' . html_encode($album->getTitle()) . '">&laquo; '.gettext('prev').'</a>';
							}
						?>
					</div> <!-- albprevious -->
					<div class="albnext">
						<?php
							$album = getNextAlbum();
							if (is_null($album)) {
									echo '<div class="albdisabledlink">'.gettext('next').' &raquo;</div>';
							} else {
								echo '<a href="' .
										rewrite_path("/" . pathurlencode($album->name), "/index.php?album=" . pathurlencode($album->name)) .
										'" title="' . html_encode($album->getTitle()) . '">'.gettext('next').' &raquo;</a>';
							}
						?>
					</div><!-- albnext -->
					<?php
					if (getOption('Allow_search')) {
						$album_list = array('albums'=>array($_zp_current_album->name),'pages'=>'0', 'news'=>'0');
						printSearchForm(NULL, 'search', $_zp_themeroot.'/images/search.png', gettext('Search within album'), NULL, NULL, $album_list);
					}
					?>
				</div> <!-- header -->

			<!-- Logo -->
				<div id="logo">
					<?php
					printLogo();
					?>
				</div>
			</div> <!-- gallerytitle -->

		<!-- Crumb Trail Navigation -->
		<div id="wrapnav">
			<div id="navbar">
				<span><?php printHomeLink('', ' | '); ?>
				<?php
			if (getOption('custom_index_page') === 'gallery') {
				?>
				<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home');?></a> |
				<?php
				}
				?>
				<a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo getGalleryTitle();?></a> |
				<?php printParentBreadcrumb(); ?></span>
				<?php printAlbumTitle(true);?>
			</div>
		</div> <!-- wrapnav -->

		<!-- Random Image -->
		<?php
		if (isAlbumPage()) {
			printHeadingImage(getRandomImagesAlbum(NULL, getThemeOption('effervescence_daily_album_image')));
		}
		?>
	</div> <!-- header -->

	<!-- Wrap Subalbums -->
	<div id="subcontent">
		<div id="submain">

			<!-- Album Description -->
			<div id="description">
				<?php
				printAlbumDesc(true);
				?>
			</div>

			<!-- SubAlbum List -->

				<?php
				$firstAlbum = null;
				$lastAlbum = null;
				while (next_album()){
					if (is_null($firstAlbum)) {
						$lastAlbum = albumNumber();
						$firstAlbum = $lastAlbum;
						?>
						<ul id="albums">
						<?php
					} else {
						$lastAlbum++;
					}
					?>
					<li>
						<?php $annotate = annotateAlbum(); ?>
						<div class="imagethumb">
							<a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo html_encode($annotate) ?>">
							<?php printCustomAlbumThumbImage($annotate, null, 180, null, 180, 80); ?></a>
						</div>
						<h4>
							<a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo html_encode($annotate) ?>">
								<?php printAlbumTitle(); ?>
							</a>
						</h4>
					</li>
					<?php
				}
				if (!is_null($firstAlbum)) {
					?>
					</ul>
					<?php
				}
				?>

			<div class="clearage"></div>
			<?php
			printNofM('Album', $firstAlbum, $lastAlbum, getNumAlbums());
			?>
		</div> <!-- submain -->

		<!-- Wrap Main Body -->
				<?php
					if (getNumImages() > 0){  /* Only print if we have images. */
						if ($_noFlash) {
			?>
		<div id="content">
 					<div id="main">
 						<div id="images">
 						<?php
 						$points = array();
						if ($personality == 'Smoothgallery') {
							if ($imagePage = isImagePage()) {
								?>
<!-- Smoothimage section -->
						<div id="smoothImages">
						<?php
						while (next_image(true, 0)){
							if (isImagePhoto()) { // Smoothgallery does not do videos
							?>
							<div class="imageElement">
								<h3><?php echo getImageTitle();?></h3>
								<p><?php echo getImageDesc();?></p>
								<a href="<?php echo html_encode(getImageLinkURL());?>" title="<?php echo getBareImageTitle();?>" class="open"></a>
								<?php printCustomSizedImage(getImageTitle(), null, 540, null, null, null, null, null, 'full'); ?>
								<?php printImageThumb(getImageTitle(), 'thumbnail'); ?>
							</div>
							<?php
							}
						}
						?>

						</div> <!-- smoothImages -->
						<?php
							if (!$show) {
								if ($imagePage) {
									$url = html_encode(getPageURL(getTotalPages(true)));
								} else {
									$url = html_encode(getPageURL(getCurrentPage()));
								}
								echo '<p align=center>';
								printLinkWithQuery($url, 'slideshow', gettext('View Slideshow'));
								echo '</p>';
								}
							}
						} else {
							$firstImage = null;
 							$lastImage = null;
 							while (next_image()){
								if (!(($personality == 'Slimbox') && !isImagePhoto())) { // Slimbox does not do video
 									if (is_null($firstImage)) {
 										$lastImage = imageNumber();
 										$firstImage = $lastImage;
 									} else {
 										$lastImage++;
 									}
 						?>
<!-- Image thumbnails or no flash -->
 									<div class="image">
	 									<div class="imagethumb">
		 									<?php
		 									if ($map) {
			 									$coord = getGeoCoord($_zp_current_image);
			 									if ($coord) {
			 										$coord['desc'] = '<p align=center>'.$coord['desc'].'</p>';
			 										$points[] = $coord;
			 									}
		 									}
		 									$annotate = annotateImage();
		 									if ($personality == 'Slimbox') {
		 										echo "<a href=\"".html_encode(getCustomImageURL(550, null))."\"";
		 										echo "rel=\"lightbox[".getAlbumTitle()."]\"\n";
		 									} else {
		 										echo '<a href="' . html_encode(getImageLinkURL()) . '"';
		 									}
		 									echo " title=\"".$annotate."\">\n";
		 									printImageThumb($annotate);
		 									echo "</a>";
		 									?>
										</div>
 									</div>
 									<?php
									}
 								}
	 							echo '<div class="clearage"></div>';
								if (!empty($points) && map) {
									function map_callback($map) {
										global $points;
										foreach ($points as $coord) {
											addGeoCoord($map, $coord);
										}
									}
									?>
									<div id="map_link">
									<?php printGoogleMap(NULL, NULL, NULL, 'album_page', 'map_callback'); ?>
									</div>
									<?php
								}
 								if (function_exists('printSlideShowLink') && ($personality != 'Smoothgallery')) {
									printSlideShowLink(gettext('View Slideshow'),'text-align:center;');
								}
						}
						?>
 					</div> <!-- images -->
 					<?php
 					if (getOption('enable_album_zipfile')) {
						echo "<p align=\"center\">";
 						printAlbumZip();
 						echo "</p>";
 					}
					if (function_exists('printRating')) {
						printRating();
					}
 					?>
 					</div> <!-- main -->
	 			<div class="clearage"></div>
 				<?php if (isset($firstImage)) printNofM('Photo', $firstImage, $lastImage, getNumImages()); ?>
 		</div> <!-- content -->
			<?php
				} else {  /* flash */
	 			if ($imagePage = isImagePage()) {
			?>
<!-- Simpleviewer section -->
			<div id="flash">
					<p align="center">
					<span style=" color=#663300"><?php echo gettext('For the best viewing experience <a href="http://www.macromedia.com/go/getflashplayer/">Get Adobe Flash.</a>'); ?></span>
					</p>
						<p align="center">
 						<?php
 						if ($imagePage) {
 							$url = html_encode(getPageURL(getTotalPages(true)));
 						} else {
 							$url = html_encode(getPageURL(getCurrentPage()));
 						}
			 printLinkWithQuery($url, 'noflash', gettext('View Gallery Without Flash'));
			 echo "</p>";
 						$flash_url = html_encode(getAlbumLinkURL());
 						if (substr($flash_url, -1, 1) == '/') {$flash_url= substr($flash_url, 0, -1);}
 						$flash_url = $flash_url . (MOD_REWRITE ? "?" : "&amp;") . "format=xml";
 						?>
 						<script type="text/javascript">
 							// <!-- <![CDATA[
							var fo = new SWFObject("<?php echo  $_zp_themeroot ?>/simpleviewer.swf", "viewer", "100%", "100%", "7", "<?php echo $backgroundColor ?>");
							fo.addVariable("preloaderColor", "<?php echo $preloaderColor ?>");
							fo.addVariable("xmlDataPath", "<?php echo $flash_url ?>");
							fo.addVariable("width", "100%");
							fo.addVariable("height", "100%");
							fo.addParam("wmode", "opaque");
							fo.write("flash");
							// ]]> -->
 						</script>
				</div> <!-- flash -->
 						<?php
	 			}
	 		} /* image loop */
	 	} else { /* no images to display */
			if (getNumAlbums() == 0){
			?>
				<div id="main3">
					<div id="main2">
					<br />
					<p align="center"><?php echo gettext('Album is empty'); ?></p>
					</div>
				</div> <!-- main3 -->
				<?php
	 		} else {
	 			?>
				<div id="main">
 					<?php
 					if (getOption('enable_album_zipfile')) {
						echo "<p align=\"center\">";
 						printAlbumZip();
 						echo "</p>";
 					}
					if (function_exists('printRating')) {
						printRating();
					}
 					?>
				</div>
				<?php
	 		}
	 	}
	 	?>

<!-- Page Numbers -->
		<div id="pagenumbers">
		<?php
		if ((getNumAlbums() != 0) || !$oneImagePage){
			printPageListWithNav("&laquo; " .gettext('prev'), gettext('next')." &raquo;", $oneImagePage);
		}
		?>
		</div> <!-- pagenumbers -->
	<?php commonComment(); ?>
</div> <!-- subcontent -->

<!-- Footer -->
<br style="clear:all" />

<?php
printFooter();
zp_apply_filter('theme_body_close');
?>

</body>
</html>
<?php
} else {
	header ('Content-Type: application/xml');

	$path = '';
	$levels = explode('/', getAlbumLinkURL());
	foreach ($levels as $v) {$path = $path . '../';}
	$path=substr($path, 0, -1);

	echo '<?xml version="1.0" encoding="UTF-8"?>
	<simpleviewerGallery title=""  maxImageWidth="'.$maxImageWidth.'" maxImageHeight="'.$maxImageHeight.
		'" textColor="'.$textColor.'" frameColor="'.$frameColor.'" frameWidth="'.$frameWidth.'" stagePadding="'.
		$stagePadding.'" thumbnailColumns="'.$thumbnailColumns.'" thumbnailRows="'.$thumbnailRows.'" navPosition="'.
		$navPosition.'" enableRightClickOpen="'.$enableRightClickOpen.'" backgroundImagePath="'.$backgroundImagePath.
		'" imagePath="'.$path.'" thumbPath="'.$path.'">';

	while (next_image(true)){
		if (isImagePhoto()) {  // simpleviewer does not do videos
?>
			<image><filename><?php echo getDefaultSizedImage();?></filename>
				<caption>
				<![CDATA[<a href="<?php echo html_encode(getImageLinkURL());?>" title="<?php echo gettext('Open In New Window'); ?>">
					<font face="Times"><u><b><em><?php echo getImageTitle() ?></font></em></b></u></a></u>
					<br /></font><?php echo getImageDesc(); ?>]]>
			</caption>
			</image>
<?php
		}
	}
	echo "</simpleviewerGallery>";
}
?>
