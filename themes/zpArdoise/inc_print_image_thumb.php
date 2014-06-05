	<div id="thumbs-nogal">
		<ul class="clearfix thumbs-nogal" id="no-gal-ul">
		<?php $col = 1; ?>
		<?php while (next_image()) { ?>
			<?php $lastcol = ''; ?>
			<?php if ($col == 5) { ?>
				<li class="no-gal-li-lastimg">
				<?php $col = 0; ?>
			<?php } else { ?>
				<li class="no-gal-li">
			<?php } ?>
			<?php $fullimage = getFullImageURL(); ?>
			<?php if ((getOption('use_colorbox_album')) && (!empty($fullimage))) { ?>
				<a class="thumb colorbox" href="<?php echo html_encode(pathurlencode($fullimage)); ?>" title="<?php echo getBareImageTitle(); ?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a>
			<?php } else { ?>
				<a class="thumb" href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo getBareImageTitle(); ?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a>
			<?php } ?>
			</li>
			<?php $col++; ?>
		<?php } ?>
		</ul>
	</div>