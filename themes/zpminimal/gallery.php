<?php include ("inc-header.php"); ?>

</div> <!-- close #header -->
<div id="content">
	<div id="main"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
		<div id="albums-wrap">
			<?php while (next_album()): ?>
				<div class="album-maxspace">
					<a class="thumb-link" href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo getNumAlbums() . ' ' . gettext('subalbums') . ' / ' . getNumImages() . ' ' . gettext('images') . ' - ' . truncate_string(getBareAlbumDesc(), 300, '...'); ?>">
						<?php
						if ($zpmin_thumb_crop) {
							printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, $zpmin_album_thumb_size, $zpmin_album_thumb_size, $zpmin_album_thumb_size, $zpmin_album_thumb_size);
						} else {
							printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), $zpmin_album_thumb_size);
						}
						?>
						<span class="album-title"><?php echo html_encodeTagged(shortenContent(getAlbumTitle(), 25, '...')); ?></span>
					</a>
				</div>
			<?php endwhile; ?>
		</div>
		<?php if ((hasPrevPage()) || (hasNextPage())) { ?>
			<div id="pagination">
				<?php printPageListWithNav("← " . gettext("prev"), gettext("next") . " →"); ?>
			</div>
		<?php } ?>
	</div>
	<div id="sidebar"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
		<div class="sidebar-divide">
			<?php printGalleryDesc(true); ?>
		</div>
		<?php include ("inc-sidemenu.php"); ?>
		<?php if ($zenpage) { ?>
			<div class="latest">
				<?php printLatestNews(2); ?>
			</div>
		<?php } ?>
	</div>
</div>

<?php include ("inc-footer.php"); ?>
