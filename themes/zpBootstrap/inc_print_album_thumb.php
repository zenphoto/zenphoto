		<div class="row album-wrap margin-bottom-double">
			<?php while (next_album()) { ?>
			<div class="col-sm-4 album-thumb">
				<a class="thumb" href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo html_encode(getBareAlbumTitle()); ?>">
					<?php printCustomAlbumThumbImage(getBareAlbumTitle(), NULL, getOption('zpB_album_thumb_width'), getOption('zpB_album_thumb_height'), getOption('zpB_album_thumb_width'), getOption('zpB_album_thumb_height'), NULL, NULL, 'remove-attributes img-responsive'); ?>
				</a>
				<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo html_encode(getBareAlbumTitle()); ?>">
					<h5><?php printBareAlbumTitle(); ?></h5>
				</a>
			</div>
			<?php } ?>
		</div>