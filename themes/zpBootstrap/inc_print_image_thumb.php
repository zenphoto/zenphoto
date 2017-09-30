		<div class="row image-wrap margin-bottom-double">
			<?php while (next_image()) { ?>
				<?php $fullimage = getFullImageURL(); ?>
				<?php if (!empty($fullimage)) { ?>
					<div class="col-xs-6 col-sm-3 image-thumb">
						<a class="thumb swipebox" href="<?php echo html_encode(pathurlencode($fullimage)); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>" data-fancybox="fancybox" data-caption="<?php echo getBareImageTitle(); ?>">
							<?php printImageThumb(getBareImageTitle(), 'remove-attributes img-responsive'); ?>
							<?php /*printCustomSizedImage(getBareImageTitle(), NULL, getOption('zpB_image_thumb_size'),getOption('zpB_image_thumb_size'), getOption('zpB_image_thumb_size'), getOption('zpB_image_thumb_size'), NULL, NULL, 'remove-attributes img-responsive', NULL, true); */ ?>
							</a>
						<a href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>">
							<h5><?php printBareImageTitle(); ?></h5>
						</a>
					</div>
				<?php } ?>
			<?php } ?>
		</div>