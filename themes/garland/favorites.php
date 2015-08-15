<?php
if (!defined('WEBPATH'))
	die();
if (class_exists('favorites')) {
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta charset="<?php echo LOCAL_CHARSET; ?>">
			<?php zp_apply_filter('theme_head'); ?>
			<?php printHeadTitle(); ?>
			<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/zen.css" type="text/css" />
			<?php if (class_exists('RSS')) printRSSHeaderLink('Album', getAlbumTitle()); ?>
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
									<a href="<?php echo html_encode(getSiteHomeURL()); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a>
								</h1>
								<span id="galleryDescription"><?php printGalleryDesc(); ?></span>
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
											<?php printHomeLink('', ' » ');
											printGalleryIndexURL(' » '); printParentBreadcrumb("", " » ", " » "); echo html_encode(getAlbumTitle()); ?>
										</h2>
										<?php printAlbumDesc(); ?>
										<?php printCodeblock(1); ?>
										<div id="albums">
											<?php
											while (next_album()) {
												?>
												<div class="album">
													<a class="albumthumb" href="<?php echo getAlbumURL(); ?>" title="<?php printf(gettext('View album:  %s'), getBareAlbumTitle()); ?>">
														<?php printCustomAlbumThumbImage(getAlbumTitle(), 85, NULL, NULL, 85, 85); ?>
													</a>
													<div class="albumdesc">
														<h3>
															<a href="<?php echo getAlbumURL(); ?>" title="<?php printf(gettext('View album:  %s'), getBareAlbumTitle()); ?>">
																<?php printAlbumTitle(); ?>
															</a>
														</h3>
														<br />
														<small><?php printAlbumDate(); ?></small>
														<?php printAddToFavorites($_zp_current_album, '', gettext('Remove')); ?>
													</div>
													<p style="clear: both;"></p>
												</div>
												<?php
											}
											?>
										</div>
										<p style="clear: both; "></p>

										<!-- Image page section -->
										<div id="images">
											<?php
											while (next_image()) {
												?>
												<div class="image">
													<div class="imagethumb">
														<a href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo getBareImageTitle(); ?>"><?php printImageThumb(getImageTitle()); ?></a>
														<?php printAddToFavorites($_zp_current_image, '', gettext('Remove')); ?>
													</div>
												</div>
												<?php
											}
											?>
										</div>
										<br class="clearall" />
										<?php @call_user_func('printSlideShowLink'); ?>
										<?php
										if ((getNumAlbums() != 0) || !$_oneImagePage) {
											printPageListWithNav(gettext("« prev"), gettext("next »"), $_oneImagePage);
										}
										?>
										<?php
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
									<div id="nextalbum" class="slides">
										<a href="<?php echo html_encode(getNextAlbumURL()); ?>" title="<?php echo gettext('Next album'); ?>"><?php echo gettext('Next album »'); ?><br /><img src="<?php echo html_encode(pathurlencode($nextalbum->getThumb())); ?>" /></a>
									</div>
									<br />
									<?php
								}
								if ($prevalbum) {
									?>
									<div id="prevalbum" class="slides">
										<a href="<?php echo html_encode(getPrevAlbumURL()); ?>" title="<?php echo gettext('Prev Album'); ?>"><?php echo gettext('« Prev Album'); ?><br /><img src="<?php echo html_encode(pathurlencode($prevalbum->getThumb())); ?>" /></a>
									</div>
									<?php
								}
							}
							?>
						</div><!-- right sidebar -->
					</div><!-- sidebar -->
				</div><!-- container -->
				<span class="clear"></span>
			</div><!-- wrapper -->
			<?php
			zp_apply_filter('theme_body_close');
			?>
		</body>
	</html>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>