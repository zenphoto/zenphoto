	<div id="album-wrap" class="clearfix">
		<ul>
		<?php $col = 1; ?>
		<?php while (next_album()) { ?>
			<?php $lastcol = ''; ?>
			<?php if ($col == 3) {$lastcol=' class="lastcol"'; $col = 0;} ?>
			<li<?php echo $lastcol; ?>>
				<a class="album-thumb" href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo getBareAlbumTitle(); ?>"><?php printCustomAlbumThumbImage(getBareAlbumTitle(), NULL, getOption('personnal_thumb_width'), getOption('personnal_thumb_height'), getOption('personnal_thumb_width'), getOption('personnal_thumb_height')); ?></a>
				<h4><a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo getBareAlbumTitle(); ?>"><?php printAlbumTitle(); ?></a></h4>
			</li>
			<?php $col++; ?>
		<?php } ?>
		</ul>
	</div>