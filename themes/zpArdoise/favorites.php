<?php
if (extensionEnabled('favoritesHandler')) {
	include ('inc_header.php');
	?>
	<div id="headline" class="clearfix">
		<h3><?php printHomeLink('', ' » '); ?>
			<?php
			if (gettext(getOption('zenpage_homepage')) == gettext('none')) {
				?>
				<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a>
				<?php
			} else {
				printCustomPageURL(getGalleryTitle(), 'gallery');
			}
			?>
			&raquo;&nbsp;<?php printAlbumTitle(); ?></h3>
		<div class="headline-text"><?php printAlbumDesc(); ?></div>
	</div>

	<?php
	if ((function_exists('printSlideShowLink')) && (!getOption('use_galleriffic'))) {
		?>
		<div class="control-nav">
			<div class="control-slide">
				<?php printSlideShowLink(gettext('Slideshow')); ?>
			</div>
		</div>
		<?php
	}
	?>

	<?php
	if (!((getNumImages() > 0) && (getOption('use_galleriffic')))) {
		?>
		<div class="pagination-nogal">
			<?php printPageListWithNav(' « ', ' » ', false, true, 'clearfix', NULL, true, 7); ?>
		</div>
		<?php
	}
	?>

	<?php
	//by definition an "album page"
	include('inc_print_album_thumb.php');

	if (getNumImages() > 0) {
		?>
		<?php
		if (getOption('use_galleriffic')) {
			?>
			<div id="galleriffic-wrap" class="clearfix">
				<div id="gallery" class="content">
					<div id="zpArdoise_controls" class="controls"></div>
					<div class="slideshow-container">
						<div id="loading" class="loader"></div>
						<div id="zpArdoise_slideshow" class="slideshow"></div>
					</div>
					<div id="caption" class="caption-container"></div>
				</div>
				<div id="thumbs" class="navigation">
					<ul class="thumbs">
						<?php
						while (next_image(true)) {
							?>
							<li>
								<?php
								if (isImageVideo()) {
									?>
									<a class="thumb" href="<?php echo $_zp_themeroot; ?>/images/video-placeholder.jpg" title="<?php echo html_encode(getBareImageTitle()); ?>">
										<?php
									} else {
										?>
										<a class="thumb" href="<?php echo html_encode(getDefaultSizedImage()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>">
											<?php
										}
										?>
										<?php printImageThumb(getAnnotatedImageTitle()); ?></a>
									<?php $fullimage = getFullImageURL(); ?>
									<a <?php if ((getOption('use_colorbox_album')) && (!empty($fullimage))) { ?>class="colorbox"<?php } ?> href="<?php echo html_encode(pathurlencode($fullimage)); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"></a>
									<div class="caption">
										<?php
										if (getOption('show_exif')) {
											?>
											<div class="exif-infos-gal">
												<?php zpardoise_printEXIF() ?>
											</div>
											<?php
										}
										?>
										<div class="image-title">
											<a href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo gettext('Image'); ?> : <?php echo getImageTitle(); ?>"><?php printImageTitle(); ?></a>
										</div>
										<div class="image-desc">
											<?php printImageDesc(); ?>
										</div>
									</div>
							</li>
							<?php
						}
						?>
					</ul>
				</div>
			</div>

			<!-- If javascript is disabled in the users browser, the following version of the album page will display -->
			<noscript>
			<?php include('inc_print_image_thumb.php'); ?>

			<div class="pagination-nogal clearfix">
				<?php printPageListWithNav(' « ', ' » ', false, true, 'clearfix', NULL, true, 7); ?>
			</div>

			</noscript>
			<!-- End of noscript display -->

			<?php
		} else {
			include('inc_print_image_thumb.php');
		}
	}

	if (!((getNumImages() > 0) && (getOption('use_galleriffic')))) {
		?>
		<div class="pagination-nogal">
			<?php printPageListWithNav(' « ', ' » ', false, true, 'clearfix', NULL, true, 7); ?>
		</div>
		<?php
	}
	?>
	<?php
	include('inc_footer.php');
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>