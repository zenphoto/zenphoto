<?php
include ("inc-header.php");

$numimages = getNumImages();
$numalbums = getNumAlbums();
$total = $numimages + $numalbums;
if ($zenpage && !isArchive()) {
	$numpages = getNumPages();
	if ($numpages > $zpmin_zpsearchcount)
		$numpages = $zpmin_zpsearchcount;
	$numnews = getNumNews();
	if ($numnews > $zpmin_zpsearchcount)
		$numnews = $zpmin_zpsearchcount;
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
	<h2><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Home'); ?>"><?php echo gettext('Home'); ?></a> &raquo; <a href="<?php echo getCustomPageURL('gallery'); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo gettext('Gallery Index'); ?></a> &raquo; <?php echo gettext("Search"); ?> &rarr; <?php echo html_encode($searchwords) . ' (' . $total . ' ' . gettext('items') . ')'; ?></h2>
</div>
</div> <!-- close #header -->
<div id="content">
	<div id="main"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
		<div id="albums-wrap">
			<?php
			$c = 0;
			while (next_album()): $c++;
				?>
				<div class="album-maxspace">
					<a class="thumb-link" href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo truncate_string(getBareAlbumDesc(), 300, '...'); ?>">
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
		<div id="thumbs-wrap">
			<?php while (next_image()): $c++; ?>
				<div class="thumb-maxspace">
					<a class="thumb-link" href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a>
					<?php if (($zpmin_colorbox) && ($cbscript) && (!isImageVideo())) { ?>
						<div class="cblinks">
							<a class="thickbox" href="<?php echo html_encode(getUnprotectedImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/zoom.png" /></a>
							<a href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/details.png" /></a>
						</div>
					<?php } ?>
				</div>
			<?php endwhile; ?>
		</div>
		<?php if ((hasPrevPage()) || (hasNextPage())) { ?>
			<div id="pagination">
				<?php printPageListWithNav("← " . gettext("prev"), gettext("next") . " →"); ?>
			</div>
		<?php } ?>
		<?php if (function_exists('printGoogleMap')) { ?><div class="section">
				?></div><?php } ?>

		<?php
		if ($_zp_page == 1) { //test of zenpage searches
			if ($numpages > 0) {
				$zpc = 0;
				while (($zpc < $zpmin_zpsearchcount) && (next_page())) {
					$zpc++;
					$c++;
					?>
					<div class="news-truncate">
						<h2><?php printPageTitlelink(); ?></h2>
						<p><?php echo html_encodeTagged(shortenContent(getPageContent(), 200, getOption("zenpage_textshorten_indicator"))); ?></p>
					</div>
					<?php
				}
			}
			if ($numnews > 0) {
				$zpc = 0;
				while (($zpc < $zpmin_zpsearchcount) && (next_news())) {
					$zpc++;
					$c++;
					?>
					<div class="news-truncate">
						<h2><?php printNewsURL(); ?></h2>
						<div class="newsarticlecredit">
							<span><?php printNewsDate(); ?> &sdot; <?php printNewsCategories(", ", gettext("Categories: "), "taglist"); ?><?php if (function_exists('printCommentForm')) { ?> &sdot; <?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?> <?php } ?></span>
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
	<div id="sidebar"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
		<div class="sidebar-divide">
			<?php printGalleryDesc(true); ?>
			<?php if (($c > 0) && (function_exists('printSlideShowLink'))) { ?><div class="sidebar-section"><div class="slideshow-link"><?php printSlideShowLink(gettext('View Slideshow')); ?></div></div><?php } ?>
		</div>
		<?php include ("inc-sidemenu.php"); ?>
	</div>
</div>

<?php include ("inc-footer.php"); ?>
