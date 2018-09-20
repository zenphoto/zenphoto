<?php include ('inc_header.php'); ?>

<div id="headline" class="clearfix">
	<h3><?php echo '<em>' . gettext('Search') . '</em>'; ?></h3>
</div>

<?php
$numimages = getNumImages();
$numalbums = getNumAlbums();
$total1 = $numimages + $numalbums;

if ($_zenpage_enabled && !isArchive()) {
	$numnews = getNumNews();
	$numpages = getNumPages();
} else {
	$numnews = $numpages = 0;
}
$total = $total1 + $numnews + $numpages;

$searchwords = getSearchWords();
$searchdate = getSearchDate();
if (!empty($searchdate)) {
	if (!empty($searchwords)) {
		$searchwords .= ": ";
	}
	$searchwords .= $searchdate;
}
?>

<div class="search-result">
	<p><?php printf(ngettext('%1$u Hit for <em>%2$s</em>', '%1$u Hits for <em>%2$s</em>', $total), $total, html_encode($searchwords)); ?></p>
	<?php if ($total == 0) { ?>
		<p><?php echo gettext("Sorry, no matches found. Try refining your search."); ?></p>
	<?php } ?>
</div>

<div>
	<?php
	if (getOption('search_no_albums')) {
		if ($numimages > 0) {
			echo'<ul class="search-item"><li>';
			printf(gettext('Images (%s)'), $numimages);
			echo'</li></ul>';
		}
	} else {
		if (getOption('search_no_images')) {
			if ($numalbums > 0) {
				echo'<ul class="search-item"><li>';
				printf(gettext('Albums (%s)'), $numalbums);
				echo'</li></ul>';
			}
		} else {
			if ($total1 > 0) {
				echo'<ul class="search-item"><li>';
				printf(gettext('Albums (%1$s) &amp; Images (%2$s)'), $numalbums, $numimages);
				echo'</li></ul>';
			}
		}
	}
	?>

	<?php if (function_exists('printSlideShowLink')) { ?>
		<div class="control-nav">
			<div class="control-slide">
			<?php printSlideShowLink(gettext('Slideshow')); ?>
			</div>
		</div>
	<?php } ?>

	<div class="pagination-nogal clearfix">
	<?php printPageListWithNav(' « ', ' » ', false, true, 'clearfix', NULL, true, 7); ?>
	</div>

	<?php
	if (getNumAlbums() > 0) {
		include('inc_print_album_thumb.php');
	}
	if (getNumImages() > 0) {
		include('inc_print_image_thumb.php');
	}
	?>

	<div class="pagination-nogal clearfix">
<?php printPageListWithNav(' « ', ' » ', false, true, 'clearfix', NULL, true, 7); ?>
	</div>

</div>

<?php if ($_zp_page == 1) { //test of zenpage searches
	if ($numnews > 0) {
		?>
		<div>
			<ul class="search-item"><li><?php printf(gettext('Articles (%s)'), $numnews); ?></li></ul>
			<?php while (next_news()) { ?>
				<div class="news-truncate clearfix">
					<h3 class="search-title"><?php printNewsURL(); ?></h3>
					<div class="search-content clearfix">
			<?php echo html_encodeTagged(shortenContent(getNewsContent(), 100, getOption("zenpage_textshorten_indicator"))); ?>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	if ($numpages > 0) {
		?>
		<div>
			<ul class="search-item"><li><?php printf(gettext('Pages (%s)'), $numpages); ?></li></ul>
		<?php while (next_page()) { ?>
				<div class="news-truncate clearfix">
					<h3 class="search-title"><?php printPageURL(); ?></h3>
					<div class="search-content clearfix">
			<?php echo html_encodeTagged(shortenContent(getPageContent(), 100, getOption("zenpage_textshorten_indicator"))); ?>
					</div>
				</div>
		<?php } ?>
		</div>
		<?php
	}
}
?>

<?php include('inc_footer.php'); ?>