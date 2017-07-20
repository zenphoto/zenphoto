<?php include('inc_header.php'); ?>

<!-- wrap -->
<!-- container -->
<!-- header -->
<h3><?php printGalleryTitle(); ?> &raquo; <?php echo '<em>' . gettext('Search') . '</em>'; ?></h3>
</div> <!-- /header -->

<div class="search-wrap">
	<?php
	$numimages = getNumImages();
	$numalbums = getNumAlbums();
	$total1 = $numimages + $numalbums;

	$zenpage = getOption('zp_plugin_zenpage');
	if ($zenpage && !isArchive()) {
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

	<div class="page-header">
		<h4>
			<?php
			if ($total == 0) {
				echo gettext("Sorry, no matches found. Try refining your search.");
			} else {
				printf(ngettext('%1$u Hit for <em>%2$s</em>', '%1$u Hits for <em>%2$s</em>', $total), $total, html_encode($searchwords));
			}
			?>
		</h4>
	</div>

	<?php
	if (getOption('search_no_albums')) { //test of images search
		if ($numimages > 0) {
			echo '<ul class="nav search-item"><li><h4>';
			printf(gettext('Images (%s)'), $numimages);
			echo '</h4></li></ul>';
		}
	} else {
		if (getOption('search_no_images')) { //test of albums search
			if ($numalbums > 0) {
				echo '<ul class="nav search-item"><li><h4>';
				printf(gettext('Albums (%s)'), $numalbums);
				echo '</h4></li></ul>';
			}
		} else {
			if ($total1 > 0) {	 //test of albums and images search
				echo '<ul class="nav search-item"><li><h4>';
				printf(gettext('Albums (%1$s) &amp; Images (%2$s)'), $numalbums, $numimages);
				echo '</h4></li></ul>';
			}
		}
	}

	if (extensionEnabled('slideshow')) {
		?>
		<ul class="pager hidden-phone pull-right"> <!--hidden-phone -->
			<li>
				<?php printSlideShowLink(gettext('Slideshow')); ?>
			</li>
		</ul>
		<?php
	}

	printPageListWithNav('«', '»', false, true, 'pagination', NULL, true, 7);

	if (getNumAlbums() > 0) {
		include('inc_print_album_thumb.php');
	}
	if (getNumImages() > 0) {
		include('inc_print_image_thumb.php');
	}

	printPageListWithNav('«', '»', false, true, 'pagination', NULL, true, 7);

	if ($_zp_page == 1) { //test of zenpage searches
		if ($numnews > 0) {
			?>
			<ul class="nav top-margin search-item"><li><h4><?php printf(gettext('Articles (%s)'), $numnews); ?></h4></li></ul>
			<?php while (next_news()) { ?>
				<div class="news-truncate clearfix">
					<h3 class="search-title"><?php printNewsURL(); ?></h3>
					<div class="search-content clearfix">
						<?php echo html_encodeTagged(shortenContent(getNewsContent(), 120, getOption("zenpage_textshorten_indicator"))); ?>
					</div>
				</div>
				<?php
			}
		}

		if ($numpages > 0) {
			?>
			<ul class="nav top-margin search-item"><li><h4><?php printf(gettext('Pages (%s)'), $numpages); ?></h4></li></ul>

			<?php while (next_page()) { ?>
				<div class="news-truncate clearfix">
					<h3 class="search-title"><?php printPageTitlelink(); ?></h3>
					<div class="search-content clearfix">
						<?php echo html_encodeTagged(shortenContent(getPageContent(), 120, getOption("zenpage_textshorten_indicator"))); ?>
					</div>
				</div>
				<?php
			}
		}
	}
	?>
</div> <!-- /search-wrap -->

<?php include('inc_footer.php'); ?>