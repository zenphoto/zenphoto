<?php include ("inc-header.php"); ?>
<div class="wrapper contrast top">
	<div class="container">
		<div class="sixteen columns">
			<?php include ("inc-search.php"); ?>
			<p class="headline"><?php printGalleryDesc(); ?></p>
		</div>
	</div>
</div>
<div class="wrapper">
	<div class="container">
		<?php if (((!$zpskel_disablewarning) && ($plugincount > 0)) || ($optionsnotsaved)) {
			echo '<div class="sixteen columns alert-message">' . $warning_message . $options_message . '</div>';
		} ?>
<?php $c = 0;
while (next_album()): ?>
			<div class="one-third column album">
				<h4><?php echo html_encode(getBareAlbumTitle()); ?></h4>
				<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo html_encode(getBareAlbumTitle()); ?>">
	<?php printCustomAlbumThumbImage(getBareAlbumTitle(), null, 420, 200, 420, 200, null, null, 'remove-attributes'); ?>
				</a>
				<div class="album-meta">
					<ul class="taglist">
						<li class="meta-date"><?php printAlbumDate(""); ?></li>
						<li class="meta-contents">
							<?php if ((getNumAlbums() > 0) && (getNumImages() > 0)) {
								$divider = '- ';
							} else {
								$divider = '';
							} ?>
	<?php if (getNumAlbums() > 0) echo getNumAlbums() . ' ' . gettext("subalbums"); ?>
	<?php echo $divider; ?>
			<?php if (getNumImages() > 0) echo getNumImages() . ' ' . gettext("images"); ?>
						</li>
					</ul>
				</div>
				<p class="albumdesc"><?php echo strip_tags(truncate_string(getAlbumDesc(), 80, '...')); ?></p>
				<hr />
			</div>
	<?php $c++;
	if ($c == 3) echo '<br class="clear" />';
endwhile; ?>
		<div class="sixteen columns">
<?php printPageListWithNav('« ' . gettext('prev'), gettext('next') . ' »', false, true, 'pagination'); ?>
<?php if (!empty($zpskel_social)) include ("inc-social.php"); ?>
		</div>
	</div>
</div>
<?php include ("inc-bottom.php"); ?>
<?php include ("inc-footer.php"); ?>