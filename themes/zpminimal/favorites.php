<?php
if (class_exists('favorites')) {
	include ("inc-header.php");
	?>

	<div id="breadcrumbs">
		<h2><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Home'); ?>"><?php echo gettext('Home'); ?></a> &raquo; <a href="<?php echo getCustomPageURL('gallery'); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo gettext('Gallery Index'); ?></a> &raquo; <?php printParentBreadcrumb('', ' » ', ' » '); ?> <?php printAlbumTitle(true); ?></h2>
	</div>
	</div> <!-- close #header -->
	<div id="content">
		<div id="main"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
			<div id="albums-wrap">
				<?php while (next_album()): ?>
					<div class="album-maxspace">
						<a class="thumb-link" href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo getNumAlbums() . ' ' . gettext('subalbums') . ' / ' . getNumImages() . ' ' . gettext('images') . ' - ' . truncate_string(getBareAlbumDesc(), 300, '...'); ?>">
							<?php
							if ($zpmin_thumb_crop) {
								printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, $zpmin_album_thumb_size, $zpmin_album_thumb_size, $zpmin_album_thumb_size, $zpmin_album_thumb_size);
							} else {
								printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), $zpmin_album_thumb_size);
							}
							?>
							<span class="album-title"><?php echo html_encodeTagged(shortenContent(getAlbumTitle(), 25, '...')); ?></span>
						</a>
					</div>
					<?php printAddToFavorites($_zp_current_album, '', gettext('Remove')); ?>
				<?php endwhile; ?>
			</div>
			<div id="thumbs-wrap">
				<?php while (next_image()): ?>
					<div class="thumb-maxspace">
						<a class="thumb-link" href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a>
						<?php if (($zpmin_colorbox) && (!isImageVideo())) { ?>
							<div class="cblinks">
								<a class="thickbox" href="<?php echo html_encode(getUnprotectedImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/zoom.png" /></a>
								<a href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/details.png" /></a>
							</div>
							<?php printAddToFavorites($_zp_current_image, '', gettext('Remove')); ?>
						<?php } ?>
					</div>
				<?php endwhile; ?>
			</div>
			<?php if ((hasPrevPage()) || (hasNextPage())) { ?>
				<div id="pagination">
					<?php printPageListWithNav("← " . gettext("prev"), gettext("next") . " →"); ?>
				</div>
			<?php } ?>
			<?php if (function_exists('printGoogleMap')) { ?><div class="section"><?php
				printGoogleMap();
				?></div><?php } ?>
			<?php if (function_exists('printRating')) { ?><div class="section"><?php printRating(); ?></div><?php } ?>
			<?php if (function_exists('printCommentForm')) { ?><div class="section"><?php printCommentForm(); ?></div><?php } ?>
		</div>
		<div id="sidebar"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
			<div class="sidebar-divide">
				<h3><?php printAlbumTitle(true); ?></h3>
				<div class="sidebar-section"><?php printAlbumDate('', '', null, true); ?></div>
				<?php if ((getAlbumDesc()) || (zp_loggedin())) { ?><div class="sidebar-section"><?php printAlbumDesc(true); ?></div><?php } ?>
				<?php if ((getTags()) || (zp_loggedin())) { ?><div class="sidebar-section"><?php printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', ''); ?></div><?php } ?>
				<?php if (function_exists('printSlideShowLink')) { ?><div class="sidebar-section"><div class="slideshow-link"><?php printSlideShowLink(gettext('View Slideshow')); ?></div></div><?php } ?>
			</div>
			<div class="sidebar-section"><?php include ("inc-sidemenu.php"); ?></div>
		</div>
	</div>

	<?php include ("inc-footer.php"); ?>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>