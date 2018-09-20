<?php include("inc-header.php"); ?>
<?php include("inc-sidebar.php"); ?>

<div class="right">
	<h1 id="tagline"><?php echo gettext('Search'); ?></h1>
	<?php if ($zpfocus_logotype) { ?>
		<a style="display:block;" href="<?php echo getGalleryIndexURL(); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/<?php echo $zpfocus_logofile; ?>" alt="<?php echo html_encode(getBareGalleryTitle()); ?>" /></a>
	<?php } else { ?>
		<h2 id="logo"><a href="<?php echo html_encode(getGalleryIndexURL()); ?>"><?php echo html_encode(getBareGalleryTitle()); ?></a></h2>
	<?php } ?>

	<p class="description">
		<?php
		$x = 0;
		$c = 0; // declare variables

		$numimages = getNumImages();
		$numalbums = getNumAlbums();
		$total = $numimages + $numalbums;
		$zenpage = getOption('zp_plugin_zenpage');
		if ($zenpage && !isArchive()) {
			$numpages = getNumPages();
			if ($zpfocus_news) {
				$numnews = getNumNews();
			} else {
				$numnews = 0;
			}
			$total = $total + $numnews + $numpages;
		} else {
			$numpages = $numnews = 0;
		}
		$searchwords = getSearchWords();
		$searchdate = getSearchDate();
		if (!empty($searchdate)) {
			if (!empty($seachwords)) {
				$searchwords .= ": ";
			}
			$searchwords .= $searchdate;
		}
		if ($total > 0) {
			printf(ngettext('%1$u Hit for <em>%2$s</em>', '%1$u Hits for <em>%2$s</em>', $total), $total, html_encode($searchwords));
		}
		?>
	</p>

	<?php
	if ($_zp_page == 1) { //test of zenpage searches
		if ($numpages > 0) {
			$number_to_show = 2;
			$c = 0;
			?>
			<h4 class="blockhead"><span><?php printf(gettext('Pages (%s)'), $numpages); ?> <?php printZDSearchShowMoreLink("pages", $number_to_show); ?></span></h4>
			<ul class="zenpagesearchresults">
				<?php
				while (next_page()) {
					$c++;
					?>
					<li<?php printZDToggleClass('pages', $c, $number_to_show); ?>>
						<h4><?php printPageTitlelink(); ?></h4>
						<p class="zenpageexcerpt"><?php echo html_encodeTagged(shortenContent(getPageContent(), 250, getOption("zenpage_textshorten_indicator"))); ?></p>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
		}
		if (($numnews > 0) && ($zpfocus_news)) {
			$number_to_show = 2;
			$c = 0;
			?>
			<h4 class="blockhead"><span><?php printf(gettext('Articles (%s)'), $numnews); ?> <?php printZDSearchShowMoreLink("news", $number_to_show); ?></span></h4>
			<ul class="zenpagesearchresults">
				<?php
				while (next_news()) {
					$c++;
					?>
					<li<?php printZDToggleClass('news', $c, $number_to_show); ?>>
						<h4><?php printNewsURL(); ?></h4>
						<p class="zenpageexcerpt"><?php echo html_encodeTagged(shortenContent(getNewsContent(), 250, getOption("zenpage_textshorten_indicator"))); ?></p>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
		}
	}
	?>

	<?php if (getNumAlbums() != 0) { ?>
		<div class="subalbum-wrap">
			<h4 class="blockhead"><span><?php echo gettext('Album Search Results'); ?> (<?php echo getNumAlbums(); ?>)</span></h4>
			<ul>
				<?php
				$x = 1;
				while (next_album()): $c++;
					if ($odd = $x % 2) {
						$css = 'goleft';
					} else {
						$css = 'goright';
					}
					?>
					<li class="<?php echo $css; ?>">
						<h4><a href="<?php echo htmlspecialchars(getAlbumURL()); ?>" title="<?php echo gettext('View Album:'); ?> <?php echo html_encode(getBareAlbumTitle()); ?>"><?php echo html_encodeTagged(shortenContent(getAlbumTitle(), 25, '...')); ?></a></h4>
						<a class="thumb" href="<?php echo htmlspecialchars(getAlbumURL()); ?>" title="<?php echo gettext('View Album:'); ?> <?php echo html_encode(getBareAlbumTitle()); ?>">
							<?php
							if (isLandscape()) {
								printCustomAlbumThumbImage(getBareAlbumTitle(), null, 160, 120, 160, 120);
							} else {
								printCustomAlbumThumbImage(getBareAlbumTitle(), null, 120, 160, 120, 160);
							}
							?>
						</a>
						<span class="front-date"><?php printAlbumDate(); ?></span>
						<p class="front-desc">
							<?php echo html_encodeTagged(shortenContent(getAlbumDesc(), 175)); ?>
							<a href="<?php echo htmlspecialchars(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo html_encode(getBareAlbumTitle()); ?>">&raquo;</a>
						</p>
					</li>
					<?php
					$x = $x + 1;
				endwhile;
				?>
			</ul>
		</div>
	<?php } ?>

	<?php if (getNumImages() > 0) { ?>

		<h4 class="blockhead">
			<?php if ($useGslideshow) { ?>
				<div class="slideshowlink"><?php printSlideShowLink(gettext('Slideshow')); ?></div>
			<?php } elseif ($zpfocus_use_colorbox_slideshow) { ?>
				<?php
				$x = 0;
				while (next_image(true)):
					if ($x >= 1) {
						$css = 'noshow';
					} else {
						$css = 'slideshowlink';
					}
					?>
					<a class="<?php echo $css; ?>" rel="slideshow" href="<?php
					if ($zpfocus_cbtarget) {
						echo htmlspecialchars(getDefaultSizedImage());
					} else {
						echo htmlspecialchars(getUnprotectedImageURL());
					}
					?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php echo gettext('Play Slideshow'); ?></a>
						 <?php
						 $x++;
					 endwhile;
					 ?>
				 <?php } ?>
			<span><?php echo gettext('Image Search Results'); ?> (<?php echo getNumImages(); ?>)</span>
		</h4>
		<div class="image-wrap">
			<ul>
				<?php while (next_image()): $c++; ?>
					<?php if (isLandscape()) { ?>
						<li class="thumb-landscape">
							<div class="album-tools-landscape">
								<?php if (($zpfocus_use_colorbox) && (!isImageVideo())) { ?><a class="album-tool" rel="zoom" href="<?php
									if ($zpfocus_cbtarget) {
										echo htmlspecialchars(getDefaultSizedImage());
									} else {
										echo htmlspecialchars(getUnprotectedImageURL());
									}
									?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/search.png" alt="Zoom Image" /></a><?php } ?>
									 <?php if (function_exists('getCommentCount') && (getCommentCount()) > 0) { ?>
									<a class="album-tool" href="<?php echo htmlspecialchars(getImageURL()); ?>" title="<?php echo getCommentCount(); ?> Comments"><img src="<?php echo $_zp_themeroot; ?>/images/shout.png" alt="Comments" /></a>
								<?php } ?>
							</div>
							<a class="thumb" href="<?php echo htmlspecialchars(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>">
								<?php printCustomSizedImage(getBareImageTitle(), null, 160, 120, 160, 120, null, null, 'thumb', null, true); ?>
							</a>
						<?php } else { ?>
						<li class="thumb-portrait">
							<div class="album-tools-portrait">
								<?php if (($zpfocus_use_colorbox) && (!isImageVideo())) { ?><a class="album-tool" rel="zoom" href="<?php
									if ($zpfocus_cbtarget) {
										echo htmlspecialchars(getDefaultSizedImage());
									} else {
										echo htmlspecialchars(getUnprotectedImageURL());
									}
									?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/search.png" alt="Zoom Image" /></a><?php } ?>
									 <?php if (function_exists('getCommentCount') && (getCommentCount()) > 0) { ?>
									<a class="album-tool" href="<?php echo htmlspecialchars(getImageURL()); ?>" title="<?php echo getCommentCount(); ?> Comments"><img src="<?php echo $_zp_themeroot; ?>/images/shout.png" alt="Comments" /></a>
								<?php } ?>
							</div>
							<a class="thumb" href="<?php echo htmlspecialchars(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>">
								<?php printCustomSizedImage(getBareImageTitle(), null, 120, 160, 120, 160, null, null, 'thumb', null, true); ?>
							</a>
						<?php } ?>
					</li>
				<?php endwhile; ?>
			</ul>
		</div>
	<?php } ?>

	<?php if ((hasNextPage()) || (hasPrevPage())) { ?>
		<?php printPageListWithNav('« ' . gettext('Prev'), gettext('Next') . ' »', false, 'true', 'page-nav', '', true, '5'); ?>
	<?php } ?>

	<?php if (function_exists('printGoogleMap')) { ?>
		<div class="gmap">
			<?php
			printGoogleMap();
			?>
		</div>
	<?php } ?>

	<?php
	if ($c == 0) {
		echo "<p>" . gettext("Sorry, no matches. Try refining your search.") . "</p>";
	}
	?>
</div>

<?php include("inc-footer.php"); ?>