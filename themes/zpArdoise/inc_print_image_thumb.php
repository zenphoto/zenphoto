<div id="thumbs-nogal">
	<ul class="clearfix thumbs-nogal" id="no-gal-ul">
		<?php
		define('ARD_IMAGE_COLUMNS', getOption('images_per_row'));
		$col = 1;
		while (next_image()) {
			$lastcol = '';
			if ($col == ARD_IMAGE_COLUMNS) {
				?>
				<li class="no-gal-li-lastimg">
					<?php
					$col = 0;
				} else {
					?>
				<li class="no-gal-li">
					<?php
				}
				$fullimage = getFullImageURL();
				if ((getOption('use_colorbox_album')) && (!empty($fullimage))) {
					?>
					<a class="thumb colorbox" href="<?php echo html_encode(pathurlencode($fullimage)); ?>" title="<?php echo getBareImageTitle(); ?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a>
					<?php
				} else {
					?>
					<a class="thumb" href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo getBareImageTitle(); ?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a>
					<?php
				}
				?>
			</li>
			<?php
			if ($col === 0) {
				echo '<br style="clear:left">';
			}
			$col++;
		}
		?>
	</ul>
</div>