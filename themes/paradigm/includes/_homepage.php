<?php if (getOption('homepage_slideshow')) { ?>
	<div id="background-slideshow" class="background">
		<div class="container<?php
		if (getOption('full_width')) {
			echo '-fluid';
		}
		?>">
			<div id="paradigm-carousel" class="carousel slide" data-ride="carousel">
				<div class="carousel-inner" role="listbox">
					<?php
					$images = getImageStatistic(getOption('carousel_number'), getOption('carousel_type'), getOption('carousel_album'), true);
					$firstpict = array_shift($images);
					echo '<div class="item active" style="background-image:url(' . html_encode(pathurlencode($firstpict->getCustomImage(null, null, null, null, null, null, null, true))) . ')">';
					echo '<div class="carousel-caption">';
					echo '<p>' . $firstpict->getTitle() . '</p>';
					echo '</div>';
					echo '</div>';

					foreach ($images as $image) {
						echo '<div class="item" style="background-image:url(' . html_encode(pathurlencode($image->getCustomImage(null, null, null, null, null, null, null, true))) . ')">';
						echo '<div class="carousel-caption">';
						echo '<p>' . $image->getTitle() . '</p>';
						echo '</div>';
						echo '</div>';
					}
					?>
				</div><!-- Controls -->
				<a class="left carousel-control" href="#paradigm-carousel" role="button" data-slide="prev"><span class="sr-only">Previous</span></a>
				<a class="right carousel-control" href="#paradigm-carousel" role="button" data-slide="next"><span class="sr-only">Next</span></a>
			</div>
		</div>
	</div>
	<?php } ?>
<div id="background-main" class="background">
	<div class="container<?php
	if (getOption('full_width')) {
		echo '-fluid';
	}
	?>">
		<div class="row">
			<div class="col-sm-12">
				<h1 itemprop="name"><?php printGalleryTitle(); ?></h1>
	<?php printGalleryDesc(); ?>
			</div>
		</div>
	</div>
<?php if (class_exists("CMS") && getNumNews(true) && (getOption('homepage_blog'))) { ?>
		<div class="container<?php
	if (getOption('full_width')) {
		echo '-fluid';
	}
	?>">
			<div class="row">
				<div class="col-sm-12">
					<h2><?php echo gettext("Blog"); ?></h2>
					<div class="row">
							<?php
							// news article loop
							$cnt = 0;
							while (next_news() && $cnt < 2):;
								?>
							<div class="col-sm-6">
								<h3><?php printNewsURL(); ?></h3>
							<?php
							printNewsContent(250);
							echo "<hr />";
							?>
							</div>
		<?php
		$cnt++;
	endwhile;
	?>
					</div>
				</div>
			</div>
		</div>
				<?php } ?>
	<div class="container<?php
				if (getOption('full_width')) {
					echo '-fluid';
				}
				?>">
		<div class="row">
			<div class="col-sm-12">
				<?php if (getOption('homepage_content') == 'latest') { ?>
					<h2><?php echo gettext("Latest images"); ?></h2>
					<?php
					if (function_exists('getImageStatistic')) {
						printLatestImages_zb(12, "", true, false, false, "", false, NULL, NULL, NULL, "", true);
					} else {
						echo '<div class="alert alert-warning" role="alert">Please enable the image_album_statistics plugin to display latest images</div>';
					}
					?>
				<?php } ?>
				<?php if (getOption('homepage_content') == 'random') { ?>
					<h2><?php echo gettext("Random images"); ?></h2>
						<?php
						if (function_exists('printRandomImages')) {
							printRandomImages_zb(12, "", "all", "", NULL, NULL, NULL, true);
						}
						?>
<?php } ?>
<?php if ((getOption('homepage_content') == 'albums') || (getOption('homepage_content') == '')) { ?>
					<h2><?php echo gettext("Albums"); ?></h2>
					<div class="row">
						<?php while (next_album()): ?>
							<div class="media col-lg-4 col-sm-6" style="height:<?php echo html_encode(getOption('thumb_size') + 20);
							echo 'px';
							?>">
								<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printBareAlbumTitle(); ?>" class="pull-right"><?php printAlbumThumbImage(getBareAlbumTitle(), "media-object"); ?></a>
								<div class="media-body">
									<h3 class="media-heading"><a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printBareAlbumTitle(); ?>"><?php printAlbumTitle(); ?></a></h3>
									<p><?php echo shortenContent(getAlbumDesc(), 200, '...'); ?></p>
								</div>
							</div>
	<?php endwhile; ?>
					</div>
<?php } ?>
			</div>
		</div>
	</div>
</div>




