<?php include('inc_header.php'); ?>

	<!-- .container -->
		<!-- .page-header -->
			<!-- .header -->
				<h3><?php printGalleryTitle(); ?></h3>
			</div><!-- .header -->
		</div><!-- /.page-header -->

		<div class="row slide">
			<?php
			$album_filename = getOption('zpB_homepage_album_filename');
			if (empty($album_filename)) {
				$option = 'all';
			} else {
				$option = 'album';
			}

			$number = getOption('zpB_homepage_random_pictures');
			if (empty($number)) {
				$number = 5;
			}

			$slides = zpB_getRandomImages($number, $option, $album_filename);
			?>
			<div class="col-sm-offset-1 col-sm-10">
				<div class="flexslider">
				<?php if (!empty($slides)) { ?>
					<ul class="slides">
						<?php foreach($slides as $slide) {
							makeImageCurrent($slide);
							?>
							<li>
								<a href="<?php echo html_encode(getCustomPageURL('gallery')); ?>" title="<?php html_encode(gettext('Gallery')); ?>">
									<?php printCustomSizedImage(gettext('Gallery'), NULL, 800, 400, 800, 400, NULL, NULL, 'remove-attributes img-responsive'); ?>
								</a>
							</li>
						<?php } ?>
					</ul>
				<?php } else { ?>
					<img src="http://via.placeholder.com/800x400?text=<?php echo gettext('Slideshow'); ?> (800 x 400)">
				<?php } ?>
				</div>
			</div>
		</div>

		<div class="row site-description">
			<?php
			if (($_zenpage_enabled) && (getNumNews() > 0)) {
				$col_sd = 'col-sm-offset-1 col-sm-6';
			} else {
				$col_sd = 'col-sm-offset-2 col-sm-8';
			}
			?>
			<div class="<?php echo $col_sd; ?>">
				<h3><?php echo gettext('Home'); ?></h3>
				<div><?php printGalleryDesc(); ?></div>
			</div>
			<?php if (($_zenpage_enabled) && (getNumNews() > 0)) { ?>
			<div class="col-sm-5">
				<h3><?php echo gettext_th('Latest news', 'zpBootstrap'); ?></h3>
				<?php printLatestNews( 1, '', true, true, 200, false); ?>
			</div>
			<?php } ?>
		</div>

	</div><!-- /.container main -->

<?php include('inc_footer.php'); ?>