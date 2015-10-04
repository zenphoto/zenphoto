<?php include ("inc-header.php"); ?>

<div id="breadcrumbs">
	<?php
	// if ($zpmas_infscroll) $_zp_current_search->page = '1';
	?>
	<a href="<?php echo $zpmas_homelink; ?>" title="<?php echo gettext("Gallery Index"); ?>"><?php echo gettext("Gallery Index"); ?></a> &raquo; <?php printParentBreadcrumb('', ' » ', ' » '); ?> <?php printAlbumTitle(true); ?>
</div>
<div id="wrapper">
	<div id="sidebar">
		<div id="sidebar-inner">
			<div id="sidebar-padding">
				<div class="image-nav">
					<?php
					if ($_zp_current_album->getParent()) {
						$linklabel = gettext('subalbum');
						$parent = $_zp_current_album->getParent();
						$totalalbums = $parent->getNumAlbums();
					} else {
						$linklabel = gettext('album');
						$totalalbums = $_zp_gallery->getNumAlbums();
					}
					?>
					<div class="image-prev">
						<?php
						$albumnav = getPrevAlbum();
						if (!is_null($albumnav)) {
							?>
							<a href="<?php echo getPrevAlbumURL(); ?>" title="<?php echo html_encode($albumnav->getTitle()); ?>"><?php echo '&larr; ' . gettext('prev') . ' ' . $linklabel; ?></a>
						<?php } ?>
					</div>
					<div class="image-next">
						<?php
						$albumnav = getNextAlbum();
						if (!is_null($albumnav)) {
							?>
							<a href="<?php echo getNextAlbumURL(); ?>" title="<?php echo html_encode($albumnav->getTitle()); ?>"><?php echo gettext('next') . ' ' . $linklabel . ' &rarr;'; ?></a>
						<?php } ?>
					</div>
					<span title="<?php echo $linklabel . ' ' . gettext('number') . '/' . gettext('total') . ' ' . $linklabel; ?>"><?php echo albumNumber() . '/' . $totalalbums; ?></span>
				</div>
				<div class="sidebar-divide">
					<h2><?php printAlbumTitle(true); ?></h2>
					<ul class="album-info">
						<?php
						$singletag = getTags();
						$tagstring = implode(', ', $singletag);
						?>
						<li class="counts smallthumbs">
							<?php
							if (getNumAlbums() > 0) {
								echo getNumAlbums() . ' ' . gettext('subalbums');
							}
							?>
							<?php
							if (getNumImages() > 0) {
								echo getNumImages() . ' ' . gettext('images');
							}
							?>
						</li>
						<?php if ((strlen(getAlbumDate()) > 0) || (zp_loggedin())) { ?><li class="date"><?php printAlbumDate(''); ?></li><?php } ?>
						<?php if ((strlen(getAlbumDesc()) > 0) || (zp_loggedin())) { ?><li class="desc"><?php printAlbumDesc(); ?></li><?php } ?>
<?php if ((strlen($tagstring) > 0) || (zp_loggedin())) { ?><li class="tags"><?php printTags('links', ' ', 'taglist', ', '); ?></li><?php } ?>
					</ul>
				</div>

					<?php if (getNumImages() > 0) { ?>
					<div id="slideshowlink" class="sidebar-divide">
						<?php if ($useGslideshow) { ?>
							<div class="gslideshowlink"><?php printSlideShowLink(gettext('Slideshow')); ?></div>
						<?php } else { ?>

							<?php
							$x = 0;
							while (next_image(true)):
								if ($x >= 1) {
									$show = 'style="display:none;"';
								} else {
									$show = '';
								}
								?>
								<?php if (!isImageVideo()) { ?>
									<a rel="slideshow"<?php echo $show; ?> href="<?php
									if ($zpmas_cbtarget) {
										echo htmlspecialchars(getDefaultSizedImage());
									} else {
										echo htmlspecialchars(getUnprotectedImageURL());
									}
									?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php echo gettext('Play Slideshow'); ?></a>
										 <?php
										 $x = $x + 1;
									 } else {
										 $x = $x;
									 }
									 ?>
							<?php endwhile; ?>

					<?php } ?>
					</div>
				<?php } ?>
<?php include ("inc-copy.php"); ?>
			</div>
		</div>
	</div>

	<div id="mason">
<?php while (next_album()): ?>
			<div class="box <?php echo $zpmas_col_album; ?> album">
				<h3><?php echo getAlbumTitle(); ?></h3>
				<div class="image-block" style="width:<?php echo $zpmas_album_size_w; ?>px;height:<?php echo $zpmas_album_size_h; ?>px;">
					<a class="thumb-link" href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo html_encodeTagged(getAnnotatedAlbumTitle()) ?>">
	<?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, $zpmas_album_size_w, $zpmas_album_size_h, $zpmas_album_size_w, $zpmas_album_size_h); ?>
					</a>
				</div>
				<?php
				$singletag = getTags();
				$tagstring = implode(', ', $singletag);
				?>
				<ul class="album-info">
					<li class="counts <?php if (($zpmas_thumbsize == 'small') && (getNumAlbums() > 0)) echo 'smallthumbs'; ?>">
						<?php
						if (getNumAlbums() > 0) {
							echo getNumAlbums() . ' ' . gettext('subalbums');
						}
						?>
						<?php
						if (getNumImages() > 0) {
							echo getNumImages() . ' ' . gettext('images');
						}
						?>
					</li>
					<?php if (strlen(getAlbumDate()) > 0) { ?><li class="date"><?php printAlbumDate(''); ?></li><?php } ?>
	<?php if (strlen(getAlbumDesc()) > 0) { ?><li class="desc"><?php echo html_encodeTagged(shortenContent(getAlbumDesc(), 150, '...')); ?></li><?php } ?>
			<?php if (strlen($tagstring) > 0) { ?><li class="tags"><?php printTags('links', ' ', 'taglist', ', '); ?></li><?php } ?>
				</ul>
			</div>
			<?php endwhile; ?>
			<?php while (next_image()): ?>
			<div class="box <?php echo $zpmas_col_image; ?>">
	<?php if ($zpmas_imagetitle) echo '<h3>' . getImageTitle() . '</h3>'; ?>
				<div class="image-block" style="width:<?php echo $zpmas_image_size; ?>px;height:<?php echo $zpmas_image_size; ?>px;">
					<div class="back">
						<a class="thumb-link" href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php printCustomSizedImage(getAnnotatedImageTitle(), null, $zpmas_image_size, $zpmas_image_size, $zpmas_image_size, $zpmas_image_size, null, null, null, null, true); ?></a>
					</div>
						<?php if (!isImageVideo()) { ?>
						<div class="overlay">
							<a class="zpmas-cb" href="<?php
							if ($zpmas_cbtarget) {
								echo htmlspecialchars(getDefaultSizedImage());
							} else {
								echo htmlspecialchars(getUnprotectedImageURL());
							}
							?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/full-screen<?php if ($zpmas_css == 'dark') echo "-inv"; ?>.png" alt="<?php echo gettext('Preview'); ?>" /></a>
						</div>
			<?php } ?>
				</div>
			</div>
	<?php endwhile; ?>
	</div>
		<?php if ($zpmas_infscroll) { ?>
		<div id="page_nav">
		<?php if (getNextPageURL()) { ?><a href="<?php echo getNextPageURL(); ?>">Next Page</a> <?php } ?>
		</div>
		<?php
	} else {
		if ((hasPrevPage()) || (hasNextPage())) {
			?>
			<div id="pagination">
			<?php printPageListWithNav("« " . gettext("prev"), gettext("next") . " »"); ?>
			</div>
			<?php } ?>
		<?php } ?>
	<div id="page">
		<?php printCodeblock(); ?>
		<?php if (function_exists('printGoogleMap')) { ?><div class="post"><?php printGoogleMap(); ?></div><?php } ?>
		<?php
		if (function_exists('printAddToFavorites'))
			printAddToFavorites($_zp_current_album);
		?>
<?php if (function_exists('printRating')) { ?><div class="post"><?php printRating(); ?></div><?php } ?>
<?php if (function_exists('printCommentForm')) { ?><div class="post"><?php printCommentForm(); ?></div><?php } ?>
	</div>
</div>

<?php include ("inc-footer.php"); ?>
