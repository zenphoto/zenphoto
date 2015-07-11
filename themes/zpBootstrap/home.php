<?php include('inc_header.php'); ?>

<!-- wrap -->
<!-- container -->
<!-- header -->
<h3><?php printGalleryTitle(); ?></h3>
</div> <!-- /header -->

<div class="row">
	<?php if (($_zp_gallery->getNumImages(true)) > 0) { ?>
		<div class="span10 offset1">
			<div class="flexslider">
				<ul class="slides">
					<?php
					for ($i = 1; $i <= 5; $i++) {
						$randomImage = getRandomImages();
						if (is_object($randomImage) && $randomImage->exists) {
							makeImageCurrent($randomImage);
						}
						?>
						<li>
							<a href="<?php echo html_encode(getCustomPageURL('gallery')); ?>" title="<?php html_encode(gettext('Gallery')); ?>">
						<?php printCustomSizedImage(gettext('Gallery'), null, 780, 400, 780, 400); ?>
							</a>
						</li>
	<?php } ?>
				</ul>
			</div>
		</div>
<?php } else { ?>
		<div class="span10 offset1">
			<div class="flexslider">
				<img src="<?php echo $_zp_themeroot; ?>/images/placeholder.jpg">
				<p class="flex-caption center"><?php echo gettext('Slideshow'); ?></p>
			</div>
		</div>
<?php } ?>
</div>

<div class="row site-description">
	<?php
	if (($_zenpage_enabled) && (getNumNews() > 0)) {
		$col1 = 'span6 offset1';
	} else {
		$col1 = 'span8 offset2';
	}
	?>
	<div class="<?php echo $col1; ?>">
		<h3><?php echo gettext('Home'); ?></h3>
		<div><?php printGalleryDesc(); ?></div>
	</div>
		<?php if (($_zenpage_enabled) && (getNumNews() > 0)) { ?>
		<div class="span5">
			<h3><?php echo gettext('Latest news'); ?></h3>
		<?php printLatestNews(1, '', true, true, 200, false); ?>
		</div>
<?php } ?>
</div>

<?php include('inc_footer.php'); ?>