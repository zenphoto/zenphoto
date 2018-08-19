<?php include('inc_header.php'); ?>

	<!-- .container -->
		<!-- .page-header -->
			<!-- .header -->
				<h3><?php printGalleryTitle(); ?></h3>
			</div><!-- .header -->
		</div><!-- /.page-header -->

		<div class="breadcrumb">
			<h4>
				<?php printGalleryIndexURL(' » ', getGalleryTitle(), false); ?><?php printParentBreadcrumb('', ' » ', ' » '); ?><?php printAlbumBreadcrumb('', ' » '); ?><?php printBareImageTitle(); ?>
			</h4>
		</div>

		<nav class="nav_photo">
			<ul class="pager">
			<?php if (hasPrevImage()) { ?>
				<li><a href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext('Previous Image'); ?>">&larr; <?php echo gettext('prev'); ?></a></li>
			<?php } else { ?>
				<li class="disabled"><a href="#">&larr; <?php echo gettext('prev'); ?></a></li>
			<?php } ?>

			<!-- TO DO : à revoir -->
			<?php if (($isMobile) && (extensionEnabled('slideshow'))) { ?>
					<?php printSlideShowLink(gettext('Slideshow')); ?>
			<?php } ?>

			<?php if (hasNextImage()) { ?>
				<li><a href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext('Next Image'); ?>"><?php echo gettext('next'); ?> &rarr;</a></li>
			<?php } else { ?>
				<li class="disabled"><a href="#"><?php echo gettext('next'); ?> &rarr;</a></li>
			<?php } ?>
			</ul>
		</nav>

		<?php printDefaultSizedImage(getBareImageTitle(), 'remove-attributes img-responsive center-block'); ?>

		<div class="photo-description row">
			<div class="col-sm-offset-2 col-sm-8">
				<h4>
					<?php printBareImageTitle(); ?>
					<?php if ((getOption('zpB_show_exif')) && (getImageMetaData())) { ?>
						<a href="#" data-toggle="modal" data-target="#exif_data"><span class="glyphicon glyphicon-info-sign"></span></a>
					<?php } ?>
				</h4>
			</div>
			<div class="col-sm-offset-2 col-sm-8">
				<?php printImageDesc(true); ?>
			</div>

			<?php if ((getOption('zpB_show_exif')) && (getImageMetaData())) { ?>
			<div id="exif_data" class="modal" tabindex="-1" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-body">
							<?php printImageMetadata(NULL, false); ?>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo gettext('Close'); ?></button>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>

			<?php if ((getOption('zpB_show_tags')) && (getTags())) { ?>
			<div class="col-sm-offset-2 col-sm-8">
				<?php printTags('links', NULL, 'nav nav-pills', NULL); ?>
			</div>
			<?php } ?>
		</div>

		<?php if ((zp_loggedin()) && (extensionEnabled('favoritesHandler'))) { ?>
		<div class="row">
			<div class="col-sm-offset-2 col-sm-8 photo-infos favorites">
				<?php printAddToFavorites($_zp_current_image); ?>
			</div>
		</div>
		<?php } ?>

		<?php if (extensionEnabled('rating')) { ?>
		<div class="row">
			<div class="col-sm-offset-2 col-sm-8 photo-infos rating">
				<?php printRating(); ?>
			</div>
		</div>
		<?php } ?>

		<?php if (extensionEnabled('comment_form')) { ?>
			<?php include('inc_print_comment.php'); ?>
		<?php } ?>

	</div><!-- /.container main -->

<?php include('inc_footer.php'); ?>