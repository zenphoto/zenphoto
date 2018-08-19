<div id="images" class="row">
	<?php
	while (next_image()):
		?>
		<div class="col-lg-3 col-md-4 col-xs-6" style="height:<?php
		echo html_encode(getOption('thumb_size') + 55);
		echo 'px';
		?>" itemscope itemtype="http://schema.org/ImageObject">
			<div class="thumbnail"  itemprop="thumbnail">
				<?php if (isImagePhoto()) { ?>
					<a href="<?php echo html_encode(getDefaultSizedImage()); ?>" rel="lightbox-<?php echo $_zp_current_album->getID(); ?>" title="<?php printBareImageTitle(); ?>"><?php printImageThumb(getBareImageTitle()); ?></a>
					<?php
				} else {
					?>
					<a href="<?php echo html_encode(getImageURL()); ?>"><?php printImageThumb(getBareImageTitle()); ?></a>
					<?php
				}
				if ($_zp_gallery_page == 'favorites.php') {
					printAddToFavorites($_zp_current_image, '', gettext('Remove'));
				}
				?>
				<div class="caption">
					<a href="<?php echo html_encode(getImageURL()); ?>"><span itemprop="name"><?php printBareImageTitle(); ?></span></a>
				</div>
			</div>
		</div>
	<?php endwhile; ?>
</div>
