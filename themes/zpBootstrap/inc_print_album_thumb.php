			<div class="album-wrap">
				<ul class="thumbnails">
					<?php while (next_album()) { ?>
					<li class="span4 album-thumb">
						<a class="thumbnail" rel="tooltip" href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:') . '&nbsp;' . html_encode(getBareAlbumTitle())/* . '<br>' . html_encode(getBareAlbumDesc())*/; ?>">
							<?php printCustomAlbumThumbImage(getBareAlbumTitle(), NULL, getOption('personnal_thumb_width'), getOption('personnal_thumb_height'), getOption('personnal_thumb_width'), getOption('personnal_thumb_height'), NULL, NULL, 'remove-attributes'); ?>
						</a>
						<h4>
							<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:') . html_encode(getBareAlbumTitle()); ?>">
								<?php printAlbumTitle(); ?>
							</a>
						</h4>
					</li>
					<?php } ?>
				</ul>
				<script type="text/javascript">
					$('.thumbnail').tooltip({
						placement: 'bottom'
					});
				</script>
			</div>