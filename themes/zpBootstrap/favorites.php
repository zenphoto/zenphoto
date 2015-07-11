<?php include ('inc_header.php'); ?>

	<!-- wrap -->
		<!-- container -->
			<!-- page-header -->
				<h3><?php printGalleryTitle(); ?></h3>
			</div> <!-- /page-header -->

			<div class="breadcrumb">
				<h4>
					<?php if (getOption('zpB_homepage')) { ?>
						<?php printCustomPageURL(getGalleryTitle(), 'gallery'); ?>
					<?php } else { ?>
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a>
					<?php } ?>&raquo;
					<?php printAlbumTitle(); ?>
				</h4>
			</div>

			<?php if (extensionEnabled('slideshow')) { ?>
			<ul class="pager hidden-phone pull-right"> <!--hidden-phone -->
				<li>
					<?php printSlideShowLink(gettext('Slideshow')); ?>
				</li>
			</ul>
			<?php } ?>

			<?php printPageListWithNav('«', '»', false, true, 'pagination', NULL, true, 7); ?>

			<div class="page-header bottom-margin-reset">
				<p><?php printAlbumDesc(true); ?></p>
			</div>

			<?php include('inc_print_album_thumb.php'); ?>

			<?php include('inc_print_image_thumb.php'); ?>

			<?php printPageListWithNav('«', '»', false, true, 'pagination', NULL, true, 7); ?>

<?php include ('inc_footer.php'); ?>