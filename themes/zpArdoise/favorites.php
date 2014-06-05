<?php 
if (extensionEnabled('favoritesHandler')) {
	include ('inc_header.php');
?>

		<div id="headline" class="clearfix">
			<h3><?php printHomeLink('', ' » '); ?>
			<?php if (gettext(getOption('zenpage_homepage')) == gettext('none')) { ?>
				<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a>
			<?php } else { ?>
				<?php printCustomPageURL(getGalleryTitle(), 'gallery'); ?>
			<?php } ?>
			&raquo;&nbsp;<?php printAlbumTitle(); ?></h3>
			<div class="headline-text"><?php printAlbumDesc(); ?></div>
		</div>

		<?php if (function_exists('printSlideShowLink')) { ?>
		<div class="control-nav">
			<div class="control-slide">
				<?php printSlideShowLink(gettext('Slideshow')); ?>
			</div>
		</div>
		<?php } ?>

		<div>
			<div class="pagination-nogal clearfix">
				<?php printPageListWithNav(' « ', ' » ', false, true, 'clearfix', NULL, true, 7); ?>
			</div>

			<?php
			if (getNumAlbums() > 0) {
				include('inc_print_album_thumb.php');
			}
			if (getNumImages() > 0) {
				include('inc_print_image_thumb.php');
			}
			?>

			<div class="pagination-nogal clearfix">
				<?php printPageListWithNav(' « ', ' » ', false, true, 'clearfix', NULL, true, 7); ?>
			</div>

		</div>

<?php
	include('inc_footer.php');

} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
} ?>