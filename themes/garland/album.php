<?php
if (!defined('WEBPATH')) die();
require_once (ZENFOLDER.'/'.PLUGIN_FOLDER.'/image_album_statistics.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php printGalleryTitle(); ?> | <?php echo getAlbumTitle();?></title>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/zen.css" type="text/css" />
	<?php printRSSHeaderLink('Album',getAlbumTitle()); ?>
</head>
<body class="sidebars">
<?php zp_apply_filter('theme_body_open'); ?>
<div id="navigation"></div>
<div id="wrapper">
	<div id="container">
		<div id="header">
			<div id="logo-floater">
				<div>
					<h1 class="title"><a href="<?php echo getGalleryIndexURL();?>" title="Gallery Index"><?php echo getGalleryTitle();?></a></h1>
				</div>
			</div>
		</div>
		<!-- header -->
		<div class="sidebar">
			<div id="leftsidebar">
				<?php include("sidebar.php"); ?>
			</div>
		 </div>
		<div id="center">
			<div id="squeeze">
				<div class="right-corner">
					<div class="left-corner">
						<!-- begin content -->
						<div class="main section" id="main">
								<h3 id="gallerytitle"><a href="<?php echo getGalleryIndexURL();?>" title="Gallery Index"><?php echo getGalleryTitle();?></a> &raquo; <?php printParentBreadcrumb("", " &raquo; ", " &raquo; "); ?><?php printAlbumTitle(true);?></h3>

					<?php printAlbumDesc(true); ?>


				<!-- Sub-Albums -->
					<div id="albums">
					<?php while (next_album()) { ?>
					<div class="album">
									 <div class="albumthumb">
										<a href="<?php echo getAlbumLinkURL();?>" title="View album: <?php echo getAlbumTitle();?>"><?php printCustomAlbumThumbImage(getAlbumTitle(),85,null,null,77,77); ?></a>
									</div>
						<div class="albumdesc">
							<h3><a href="<?php echo getAlbumLinkURL();?>" title="View album: <?php echo getAlbumTitle();?>"><?php printAlbumTitle(); ?></a><small><?php printAlbumDate("Date Taken: "); ?></small></h3>
							<p class="desc"><?php printAlbumDesc(); ?></p>
						</div>
						<br style="clear: both; " />
					</div>
					<?php } ?>
				</div>

								<div id="images">
					<?php
					$points = array();
					while (next_image()){
						$exif = $_zp_current_image->getMetaData();
						if(!empty($exif['EXIFGPSLatitude']) && !empty($exif['EXIFGPSLongitude'])){
							$lat = $exif['EXIFGPSLatitude'];
							$long = $exif['EXIFGPSLongitude'];
							if($exif['EXIFGPSLatitudeRef'] == 'S') {
								$lat = '-' . $lat;
							}
							if($exif['EXIFGPSLongitudeRef'] == 'W') {
								$long = '-' . $long;
							}
							$desc = $_zp_current_image->getDesc();
							$title = $_zp_current_image->getTitle();
							if (empty($desc)) {
								$desc = $title;
							}
							$points[] = array($lat, $long, $title, '<p align=center >' . $desc."</p>");
						}
						?>
					<div class="image">
						<div class="imagethumb"><a href="<?php echo getImageLinkURL();?>" title="<?php echo getImageTitle();?>"><?php printImageThumb(getImageTitle()); ?></a></div>
					</div>
					<?php
					}
					?>
				</div>

							<?php
							if (!empty($points) && function_exists('printGoogleMap')) {
								?>
								<div id="map_link">
								<?php
								printGoogleMap(NULL, NULL, NULL, 'album_page', 'gMapOptionsAlbum');
								?>
								</div>
								<?php
							}
							?>
							<?php printPageListWithNav("&laquo; prev", "next &raquo;"); ?>
							<?php if (function_exists('printSlideShowLink')) printSlideShowLink(gettext('View Slideshow')); ?>
							<?php if (function_exists('printRating')) { printRating(); }?>
							<?php
							if (function_exists('printCommentForm')) {
								printCommentForm();
							}
							footer();
							?>
					 </div>
						<div style="clear: both;"></div>
						</div>
						<!-- end content -->
						<span class="clear"></span> </div>
				</div>
			</div>
		</div>
		<div class="sidebar">
			<div id="rightsidebar">
				<h2>Album Navigation</h2>
		<?php printLink(getNextAlbumURL(), "Next Album &raquo;"); ?><br />
				<?php printLink(getPrevAlbumURL(), "Prev Album &laquo;"); ?>
				<?php if (getOption('Allow_cloud')) { echo "<br><br>"; printAllTagsAs('Cloud'); } ?>
			</div>
		</div>
		<span class="clear"></span>
	 </div>
	<!-- /container -->
</div>
<?php
printAdminToolbox();
zp_apply_filter('theme_body_close');
?>
</body>
</html>
