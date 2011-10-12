<?php
if (!defined('WEBPATH')) die();
$map = function_exists('printGoogleMap');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php printGalleryTitle(); ?> | <?php echo html_encode(getAlbumTitle()); ?></title>
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
					<h1 class="title">
						<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo sanitize(getGalleryTitle());?></a>
					</h1>
				</div>
			</div>
		</div><!-- header -->
	<div class="sidebar">
		 <div id="leftsidebar">
		<?php include("sidebar.php"); ?>
		</div>
	</div>
	<div id="center">
		<div id="squeeze">
			<div class="right-corner">
				<div class="left-corner"><!-- begin content -->
					<div class="main section" id="main">
						<h2 id="gallerytitle">
							<?php printHomeLink('',' &raquo; '); ?>
							<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo getGalleryTitle();?></a> &raquo; <?php printParentBreadcrumb("", " &raquo; ", " &raquo; "); ?><?php echo html_encode(getAlbumTitle()); ?>
						</h2>
						<?php printAlbumDesc(); ?>
						<?php printCodeblock(1); ?>
						<div id="albums">
							<?php
							while (next_album()) {
								?>
								<div class="album">
									<a class="albumthumb" href="<?php echo getAlbumLinkURL();?>" title="<?php printf (gettext('View album:  %s'),sanitize(getAlbumTitle())); ?>">
										<?php printCustomAlbumThumbImage(getAlbumTitle(),85,NULL,NULL,85,85); ?>
									</a>
									<div class="albumdesc">
										<h3>
											<a href="<?php echo getAlbumLinkURL();?>" title="<?php printf (gettext('View album:  %s'),sanitize(getAlbumTitle())); ?>">
												<?php printAlbumTitle(); ?>
											</a>
										</h3>
										<br />
										<small><?php printAlbumDate(); ?></small>
									</div>
								<p style="clear: both;"></p>
								</div>
								<?php
							}
							?>
							</div>
							<p style="clear: both; "></p>
							<div id="images">
								<?php
								$points = array();
								while (next_image()){
									if ($map) {
	 									$coord = getGeoCoord($_zp_current_image);
	 									if ($coord) {
	 										$coord['desc'] = '<p align=center>'.$coord['desc'].'</p>';
	 										$points[] = $coord;
	 									}
									}
									?>
									<div class="image">
										<div class="imagethumb"><a href="<?php echo html_encode(getImageLinkURL());?>" title="<?php echo sanitize(getImageTitle()); ?>"><?php printImageThumb(getImageTitle()); ?></a></div>
									</div>
									<?php
								}
								?>
							</div>

							<?php printPageListWithNav(gettext("&laquo; prev"), gettext("next &raquo;")); ?>
							<?php if (function_exists('printSlideShowLink')) printSlideShowLink(gettext('View Slideshow')); ?>
							<?php if (function_exists('printRating')) { printRating(); }?>
							<?php
							if (function_exists('printCommentForm')) {
								printCommentForm();
							}
							printCodeblock(2);
							footer();
							?>
					 </div>
					<p style="clear: both;"></p>
					</div>	<!-- end content -->
					<span class="clear"></span>
				</div>
			</div>
		</div>
		<div class="sidebar">
			<div id="rightsidebar">
				<?php
				$nextalbum = getNextAlbum();
				$prevalbum = getPrevAlbum();
				if ($nextalbum || $prevalbum) {
					?>
					<h2><?php echo gettext('Album Navigation'); ?></h2>
					<?php
					if ($nextalbum) {
						?>
						<div id="next" class="slides">
						<a href="<?php echo html_encode(getNextAlbumURL()); ?>" title="<?php echo gettext('Next album'); ?>"><?php echo gettext('Next album &raquo;'); ?><br /><img src="<?php echo html_encode($nextalbum->getAlbumThumb()); ?>" /></a>
						</div>
						<br />
					<?php
					}
					if ($prevalbum) {
						?>
						<div id="prev" class="slides">
						<a href="<?php echo html_encode(getPrevAlbumURL());?>" title="<?php echo gettext('Prev Album'); ?>"><?php echo gettext('&laquo; Prev Album'); ?><br /><img src="<?php echo html_encode($prevalbum->getAlbumThumb()); ?>" /></a>
						</div>
						<?php
					}
				}
				?>
				<?php printTags('links', gettext('Tags: '), NULL, ''); ?>
				<?php
				if (!empty($points) && $map) {
					setOption('gmap_display', 'colorbox', false);
					?>
					<div id="map_link">
						<?php
						printGoogleMap(NULL, NULL, NULL, 'album_page', 'gMapOptionsAlbum');
						?>
					</div>
					<br clear="all" />
					<?php
				}
				?>
				<?php
				if (function_exists('printLatestImages')) {
					?>
					<h2><?php printf(gettext('Latest Images for %s'),$_zp_current_album->getTitle()); ?></h2>
					<?php
					printLatestImages(5, $_zp_current_album->name);
				}
				?>
			</div><!-- right sidebar -->
		</div><!-- sidebar -->
	</div><!-- container -->
	<span class="clear"></span>
</div><!-- wrapper -->
<?php
printAdminToolbox();
zp_apply_filter('theme_body_close');
?>
</body>
</html>
