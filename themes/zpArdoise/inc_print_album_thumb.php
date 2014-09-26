<div id="album-wrap" class="clearfix">
	<ul>
		<?php
		define('ARD_ALBUM_COLUMNS', getOption('albums_per_row'));
		$col = 1;
		while (next_album()) {
			$lastcol = '';
			if ($col == ARD_ALBUM_COLUMNS) {
				$lastcol = ' class="lastcol"';
				$col = 0;
			}
			?>
			<li<?php echo $lastcol; ?>>
				<a class="album-thumb" href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:') . getBareAlbumTitle(); ?>">
					<?php printCustomAlbumThumbImage(getBareAlbumTitle(), NULL, getOption('personnal_thumb_width'), getOption('personnal_thumb_height'), getOption('personnal_thumb_width'), getOption('personnal_thumb_height')); ?>
				</a>
				<h4>
					<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo getBareAlbumTitle(); ?>"><?php printAlbumTitle(); ?>
					</a>
				</h4>
			</li>
			<?php
			if ($lastcol)
				echo '<br style="clear:left">';
			$col++;
			?>
			<?php
		}
		?>
	</ul>
</div>