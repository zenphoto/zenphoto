	<div id="thumbs-nogal">
		<ul class="clearfix thumbs-nogal" id="no-gal-ul">
		<?php while (next_image()) { ?>
			<li class="no-gal-li">
			<?php $fullimage = getFullImageURL(); ?>
			<?php if ((getOption('use_colorbox_album')) && (!empty($fullimage))) { ?>
				<a class="thumb colorbox" href="<?php echo html_encode(pathurlencode($fullimage)); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a>
			<?php } else { ?>
				<a class="thumb" href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a>
			<?php } ?>
			</li>
		<?php } ?>
		</ul>
	</div>