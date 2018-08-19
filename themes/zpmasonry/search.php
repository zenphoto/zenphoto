<?php include ("inc-header.php"); ?>
<?php
$numimages = getNumImages();
$numalbums = getNumAlbums();
$total = $numimages + $numalbums;
if ($zenpage && !isArchive()) {
	$numpages = getNumPages();
	if ($numpages > $zpmas_zpsearchcount)
		$numpages = $zpmas_zpsearchcount;
	if ($zpmas_usenews) {
		$numnews = getNumNews();
	} else {
		$numnews = 0;
	} if ($numnews > $zpmas_zpsearchcount)
		$numnews = $zpmas_zpsearchcount;
	$total = $total + $numnews + $numpages;
} else {
	$numpages = $numnews = 0;
}
$searchwords = getSearchWords();
$searchdate = getSearchDate();
if (!empty($searchdate)) {
	if (!empty($searchwords)) {
		$searchwords .= ": ";
	}
	$searchwords .= $searchdate;
}
?>
<div id="breadcrumbs">
	<a href="<?php echo $zpmas_homelink; ?>" title="<?php echo gettext("Gallery Index"); ?>"><?php echo gettext("Gallery Index"); ?></a> &raquo; <?php echo gettext("Search"); ?> &rarr; <?php echo html_encode($searchwords) . ' (' . $total . ' ' . gettext('items') . ')'; ?>
</div>
<div id="wrapper">
	<div id="sidebar">
		<div id="sidebar-inner">
			<div id="sidebar-padding">
				<div id="tag_cloud" class="sidebar-divide">
					<h3><?php echo gettext('Popular Tags'); ?></h3>
					<?php printAllTagsAs('cloud', 'tags'); ?>
				</div>
				<?php if ($numimages > 0) { ?>
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
		<?php
		$c = 0;
		while (next_album()): $c++;
			?>
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
			<?php while (next_image()): $c++; ?>
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

		<?php
		if ($_zp_page == 1) { //test of zenpage searches
			if ($numpages > 0) {
				$zpc = 0;
				while (($zpc < $zpmas_zpsearchcount) && (next_page())) {
					$zpc++;
					$c++;
					?>
					<div class="news-truncate <?php echo $zpmas_col_album; ?> box">
						<h2><?php printPageTitlelink(); ?></h2>
						<p><?php echo html_encodeTagged(shortenContent(getPageContent(), 200, getOption("zenpage_textshorten_indicator"))); ?></p>
					</div>
					<?php
				}
			}
			if ($numnews > 0) {
				$zpc = 0;
				while (($zpc < $zpmas_zpsearchcount) && (next_news())) {
					$zpc++;
					$c++;
					?>
					<div class="news-truncate <?php echo $zpmas_col_album; ?> box">
						<h2><?php printNewsURL(); ?></h2>
						<div class="newsarticlecredit">
							<span><?php printNewsDate(); ?></span><span><?php printNewsCategories(", ", gettext("Categories: "), "taglist"); ?></span><?php if (function_exists('printCommentForm')) { ?><span><?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?></span><?php } ?>
						</div>
						<p><?php echo html_encodeTagged(shortenContent(getNewsContent(), 200, getOption("zenpage_textshorten_indicator"))); ?></p>
					</div>
					<?php
				}
			}
		}
		?>
		<?php
		if ($c == 0) {
			echo "<h3>" . gettext("Sorry, no matches found.") . "</h3>";
		}
		?>
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
<?php if (function_exists('printGoogleMap')) { ?><div class="post"><?php printGoogleMap(); ?></div><?php } ?>
	</div>
</div>

<?php include ("inc-footer.php"); ?>
