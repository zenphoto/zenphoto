<?php include('inc_header.php'); ?>

	<!-- wrap -->
		<!-- container -->
			<!-- header -->
				<h3><?php printGalleryTitle(); ?></h3>
			</div> <!-- /header -->

			<div class="breadcrumb">
				<h4>
					<?php if (getOption('zpB_homepage')) { ?>
						<?php printCustomPageURL(getGalleryTitle(), 'gallery'); ?>
					<?php } else { ?>
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a>
					<?php } ?>
				</h4>
			</div>

			<?php printPageListWithNav('«', '»', false, true, 'pagination', NULL, true, 7); ?>

			<?php if (!getOption('zpB_homepage')) { ?>
			<div class="page-header bottom-margin-reset">
				<p><?php printGalleryDesc(); ?></p>
			</div>
			<?php } ?>

			<?php include('inc_print_album_thumb.php'); ?>

			<?php printPageListWithNav('«', '»', false, true, 'pagination', NULL, true, 7); ?>

<?php include('inc_footer.php'); ?>