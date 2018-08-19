<?php 
if (!extensionEnabled('favoritesHandler')) die();
include('inc_header.php');
?>

	<!-- .container main -->
		<!-- .page-header -->
			<!-- .header -->
				<h3><?php printGalleryTitle(); ?></h3>
			</div><!-- .header -->
		</div><!-- /.page-header -->

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

		<div class="page-header bottom-margin-reset">
			<p><?php printAlbumDesc(true); ?></p>
		</div>

		<!-- TO DO : à revoir -->
		<?php if (extensionEnabled('slideshow')) { ?>
		<ul class="pager pull-right hidden-phone"> <!--hidden-phone -->
			<li>
				<?php printSlideShowLink(gettext('Slideshow')); ?>
			</li>
		</ul>
		<?php } ?>

		<?php printPageListWithNav('«', '»', false, true, 'pagination pagination-sm', NULL, true, 7); ?>

		<?php include('inc_print_album_thumb.php'); ?>

		<?php include('inc_print_image_thumb.php'); ?>

		<?php printPageListWithNav('«', '»', false, true, 'pagination pagination-sm top-margin-reset', NULL, true, 7); ?>

	</div><!-- /.container main -->

<?php include ('inc_footer.php'); ?>