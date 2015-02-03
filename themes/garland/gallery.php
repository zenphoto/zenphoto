<?php
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>

		<?php zp_apply_filter('theme_head'); ?>

		<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/zen.css" type="text/css" />
		<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', gettext('Gallery RSS')); ?>
	</head>
	<body class="sidebars">
		<?php zp_apply_filter('theme_body_open'); ?>
		<div id="navigation"></div>
		<div id="wrapper">
			<div id="container">
				<div id="header">
					<div id="logo-floater">
						<div>
							<h1 class="title"><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a></h1>
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
									<h3 id="gallerytitle"><?php printHomeLink('', ' » '); ?><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a> » <?php echo gettext('Album index'); ?></h3>
									<?php printCodeblock(1); ?>
									<div id="albums">
										<?php
										setOption('albums_per_page', 12, false);
										while (next_album()) {
											?>
											<div class="album">
												<a class="albumthumb" href="<?php echo getAlbumURL(); ?>" title="<?php printf(gettext('View album:  %s'), getBareAlbumTitle()); ?>">
													<?php printCustomAlbumThumbImage(getAlbumTitle(), 85, NULL, NULL, 85, 85); ?>
												</a>
												<div class="albumdesc">
													<h3>
														<a href="<?php echo getAlbumURL(); ?>" title="<?php printf(gettext('View album:  %s'), getBareAlbumTitle()); ?>">
															<?php printBareAlbumTitle(25); ?>
														</a>
													</h3>
													<br />
													<small><?php printAlbumDate(); ?></small>
												</div>
												<p style="clear: both;"></p>
											</div>
											<?php
										}
										printPageListWithNav(gettext("« prev"), gettext("next »"));
										?>
									</div><!-- album -->
									<?php ?>
									<p style="clear: both;"></p>
									<?php printCodeblock(2); ?>
									<?php footer(); ?>
								</div><!-- main -->
								<span class="clear"></span>
							</div><!-- left corner -->
						</div><!-- right corner -->
					</div><!-- squeeze -->
				</div><!-- center -->
				<div class="sidebar">
					<div id="rightsidebar">
						<?php
						if (function_exists('printLatestImages')) {
							?>
							<h2><?php echo gettext('Latest Images'); ?></h2>
							<?php
							printLatestImages(7);
						}
						?>
					</div><!-- right sidebar -->
				</div><!-- sidebar -->
				<span class="clear"></span>
			</div><!-- container -->
		</div><!-- wrapper -->
		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>
