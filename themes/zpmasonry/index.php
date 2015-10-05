<?php include ("inc-header.php"); ?>

<div id="breadcrumbs">
	<a href="<?php echo $zpmas_homelink; ?>" title="<?php echo gettext("Gallery Index"); ?>"><?php echo gettext("Gallery Index"); ?></a>
</div>
<div id="wrapper">
	<div id="sidebar">
		<div id="sidebar-inner">
			<div id="sidebar-padding">
				<div class="sidebar-divide">
					<h2><?php echo gettext('Welcome'); ?></h2>
					<?php printGalleryDesc(true); ?>
				</div>
				<?php if (function_exists('printAlbumMenuJump')) { ?>
					<div class="sidebar-divide">
						<?php printAlbumMenuJump('count', gettext('Gallery Index')); ?>
					</div>
				<?php } ?>
				<?php if (getOption('zp_plugin-zenpage') && getOption('zpmas_usenews')) { ?>
					<div class="latest sidebar-divide">
						<?php printLatestNews(1); ?>
					</div>
				<?php } ?>
				<?php include ("inc-copy.php"); ?>
			</div>
		</div>
	</div>
	<div id="mason">
		<?php if (($zpmas_ss) && ($_zp_page == 1)) { ?>
			<div id="cycle" class="box <?php echo $zpmas_col_ss; ?> album">
				<?php if ($zpmas_sscount > $_zp_gallery->getNumImages(2)) $zpmas_sscount = $_zp_gallery->getNumImages(2); ?>
				<?php
				if ($zpmas_albumorimage == 'image') {
					printImageStatistic($zpmas_sscount, $zpmas_functionoption, '', true, false, false, 40, '', $zpmas_ss_size_w, $zpmas_ss_size_h, true);
				} else if ($zpmas_albumorimage == 'album') {
					if ($zpmas_sscount > $_zp_gallery->getNumAlbums(false, true))
						$zpmas_sscount = $_zp_gallery->getNumAlbums(false, true);
					printAlbumStatistic($zpmas_sscount, $zpmas_functionoption, true, false, false, 40, '', $zpmas_ss_size_w, $zpmas_ss_size_h, true);
				} else {
					?>
					<ul>
						<?php
						$randomList = "";
						for ($i = 1; $i <= $zpmas_sscount; $i++) {
							$randomImage = getRandomImages();
							if (is_object($randomImage) && $randomImage->exists) {
								$imageName = $randomImage->getTitle();
								if (strpos($randomList, $imageName)) {
									$i--;
								} else {
									$randomList = $randomList . ' ' . $imageName;
									$randomImageURL = html_encode($randomImage->getLink());
									echo '<li><a href="' . $randomImageURL . '" title="' . sprintf(gettext('View image: %s'), html_encode($randomImage->getTitle())) . '">';
									$html = "<img src=\"" . html_encode($randomImage->getCustomImage(null, $zpmas_ss_size_w, $zpmas_ss_size_h, $zpmas_ss_size_w, $zpmas_ss_size_h, null, null, true)) . "\" alt=\"" . html_encode($randomImage->getTitle()) . "\" />\n";
									echo zp_apply_filter('custom_image_html', $html, false);
									echo "</a>";
									echo '<h3><a href="' . $randomImageURL . '" title="' . sprintf(gettext('View image: %s'), html_encode($randomImage->getTitle())) . '">' . html_encodeTagged($randomImage->getTitle()) . '</a></h3>';
									echo "</li>";
								}
							} else {
								echo gettext('No Images Exist...');
							}
						}
						?>
					</ul>
				<?php } ?>
				<h2 id="ss-title"><?php echo $zpmas_sstitle; ?></h2>
			</div>
		<?php } ?>

		<?php echo $zpmas_sscount; ?>

		<?php while (next_album()): ?>
			<div class="box <?php echo $zpmas_col_album; ?> album">
				<h3><?php echo getAlbumTitle(); ?></h3>
				<a class="thumb-link" href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo html_encode(getAnnotatedAlbumTitle()); ?>">
					<?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, $zpmas_album_size_w, $zpmas_album_size_h, $zpmas_album_size_w, $zpmas_album_size_h); ?>
				</a>
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
	</div>
	<?php if ($zpmas_infscroll) { ?>
		<div id="page_nav">
			<?php if (getNextPageURL()) { ?><a href="<?php echo getNextPageURL(); ?>"><?php echo gettext('Next Page'); ?></a> <?php } ?>
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
	<?php printCodeblock(); ?>
</div>

<?php include ("inc-footer.php"); ?>
