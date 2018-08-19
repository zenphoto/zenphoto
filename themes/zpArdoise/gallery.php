<?php include ('inc_header.php'); ?>

		<div id="headline" class="clearfix">
			<h3><?php echo html_encode(getGalleryTitle()); ?></h3>
			<div class="headline-text"><?php printGalleryDesc(); ?></div>
		</div>

		<?php if (isAlbumPage()) { ?>
			<div class="pagination-nogal">
				<?php printPageListWithNav(' « ', ' » ', false, true, 'clearfix', NULL, true, 7); ?>
			</div>

			<?php include('inc_print_album_thumb.php'); ?>

			<div class="pagination-nogal">
				<?php printPageListWithNav(' « ', ' » ', false, true, 'clearfix', NULL, true, 7); ?>
			</div>
		<?php } ?>

		<?php if (getOption('show_tag')) { ?>
			<div class="headline-tags">
				<?php printAllTagsAs('cloud', 'hor-list', 'abc', false, true, 2.5, 30, 5, NULL, 1); ?>
			</div>
		<?php } ?>

		<?php if ((getOption('image_statistic') != 'none') && ((extensionEnabled('image_album_statistics')) || (getOption('image_statistic') == 'random'))) { ?>
			<?php include('inc_print_image_statistic.php'); ?>
		<?php } ?>

<?php include('inc_footer.php'); ?>