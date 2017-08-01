<?php
if (!defined('WEBPATH'))
	die();
if (class_exists('favorites')) {
	?>
	<!DOCTYPE html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<?php printZDRoundedCornerJS(); ?>
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
		<?php printRSSHeaderLink('Album', getAlbumTitle()); ?>
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>

		<div id="main">

			<?php include("header.php"); ?>
			<div id="content">

				<div id="breadcrumb">
					<h2>
						<?php if (extensionEnabled('zenpage')) { ?>
							<a href="<?php echo getGalleryIndexURL(); ?>" title="<?php gettext('Index'); ?>"><?php echo gettext("Index"); ?></a>»
						<?php } ?>
						<a href="<?php echo htmlspecialchars(getCustomPageURl('gallery')); ?>" title="<?php echo gettext('Gallery'); ?>"><?php echo gettext("Gallery") . " » "; ?></a>
						<?php printParentBreadcrumb(" » ", " » ", " » "); ?><strong><?php printAlbumTitle(true); ?></strong></h2>
				</div>

				<div id="content-left">
					<?php
					$gd = getAlbumDesc();
					if (!empty($gd)) {
						?><div class="gallerydesc"><?php printAlbumDesc(true); ?></div><?php } ?>
					<div id="albums">
						<?php $u = 0; ?>
						<?php while (next_album()): $u++ ?>
							<div class="album" <?php
							if ($u % 2 == 0) {
								echo 'style="margin-left: 8px;"';
							}
							?> >
								<div class="thumb">
									<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printBareAlbumTitle(); ?>"><?php printCustomAlbumThumbImage(getBareAlbumTitle(), NULL, 255, 75, 255, 75); ?></a>
								</div>
								<div class="albumdesc">
									<h3>
										<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo html_encode(getBareAlbumTitle()); ?>"><?php printAlbumTitle(); ?></a>
									</h3>
									<h3 class="date">
										<?php printAddToFavorites($_zp_current_album, '', gettext('Remove')); ?>
									</h3>
								<!-- p><?php echo html_encodeTagged(shortenContent(getAlbumDesc(), 45)); ?></p --></h3>
								</div>
								<p style="clear: both; "></p>
							</div>
						<?php endwhile; ?>

						<?php while ($u % 2 != 0) : $u++; ?>
							<div class="album" style="margin-left: 8px;">
								<div class="thumb">
									<a><img style="width: 255px; height: 75px;  border: 1px #efefef solid;" src="<?= $_zp_themeroot ?>/images/trans.png" /></a>
								</div>
								<div class="albumdesc">
									<h3 style="color: transparent;">No album</h3>
									<h3 class="date" style="color: transparent;">No Date</h3>
								</div>
							</div>
						<?php endwhile ?>
					</div>

					<div id="images">
						<?php $u = 0; ?>
						<?php while (next_image()): $u++; ?>
							<div class="image">
								<div class="imagethumb">
									<a href="<?php echo htmlspecialchars(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php printImageThumb(getBareImageTitle()); ?></a>
									<?php printAddToFavorites($_zp_current_image, '', gettext('Remove')); ?>
								</div>
							</div>
						<?php endwhile; ?>
						<?php while ($u % 5 != 0) : $u++; ?>
							<div class="image">
								<div class="imagethumb">
									<a><img style="width:<?php echo getOption('thumb_size'); ?>px; height:<?php echo getOption('thumb_size'); ?>px;  outline: 1px #efefef solid;" src="<?= $_zp_themeroot ?>/images/trans.png" /></a>
									<input type = "submit" class = "button buttons" value = "      " title = ""/>
								</div>
							</div>
						<?php endwhile ?>
					</div>
					<p style="clear: both; "></p>
					<?php printPageListWithNav("« " . gettext("prev"), gettext("next") . " »"); ?>
					<?php printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', ', '); ?>
					<br style="clear:both;" /><br />
					<?php
					if (function_exists('printSlideShowLink')) {
						echo '<span id="slideshowlink">';
						printSlideShowLink(gettext('View Slideshow'));
						echo '</span>';
					}
					?>
					<br style="clear:both;" />
					<?php
					if (function_exists('printRating')) {
						printRating();
					}
					?>
					<?php
					if (function_exists('printCommentForm')) {
						?>
						<div id="comments">
							<?php printCommentForm(); ?>
						</div>
						<?php
					}
					?>


				</div><!-- content left-->



				<div id="sidebar">
					<?php include("sidebar.php"); ?>
				</div><!-- sidebar -->


				<div id="footer">
					<?php include("footer.php"); ?>
				</div>

			</div><!-- content -->

		</div><!-- main -->
		<?php zp_apply_filter('theme_body_close'); ?>
	</body>
	</html>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>