<?php include ("inc-header.php"); ?>
<div class="wrapper contrast top">
	<div class="container">
		<div class="sixteen columns">
			<?php include ("inc-search.php"); ?>
			<?php
			$numimages = getNumImages();
			$numalbums = getNumAlbums();
			$total = $numimages + $numalbums;
			if ($zenpage && !isArchive()) {
				$numpages = getNumPages();
				if ($zpskel_usenews) {
					$numnews = getNumNews();
				} else {
					$numnews = 0;
				}
				$total = $total + $numnews + $numpages;
			} else {
				$numpages = $numnews = 0;
			}
			if ($total == 0) {
				$_zp_current_search->clearSearchWords();
			}
			?>
			<h5>
				<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Gallery'); ?>"><?php echo gettext("Gallery"); ?></a>
			</h5>
			<h1><?php echo gettext("Search"); ?></h1>
			<?php
			$searchwords = getSearchWords();
			$searchdate = getSearchDate();
			if (!empty($searchdate)) {
				if (!empty($searchwords)) {
					$searchwords .= ": ";
				}
				$searchwords .= $searchdate;
			}
			if ($total > 0) {
				?>
				<p><?php printf(ngettext('%1$u Hit for <em>%2$s</em>', '%1$u Hits for <em>%2$s</em>', $total), $total, html_encode($searchwords)); ?></p>
			<?php } else { ?>
				<p><?php echo gettext("Sorry, no matches found. Try refining your search."); ?></p>
			<?php } ?>
		</div>
	</div>
</div>
<div class="wrapper">
	<div class="container">
		<?php if ($_zp_page == 1) { //test of zenpage searches  ?>
			<div class="eight columns">
				<?php
				if ($numpages > 0) {
					$number_to_show = 5;
					$c = 0;
					?>
					<h5><?php printf(gettext('Pages (%s)'), $numpages); ?> <small><?php printZDSearchShowMoreLink("pages", $number_to_show); ?></small></h5>
					<ul class="searchresults">
						<?php
						while (next_page()) {
							$c++;
							?>
							<li<?php printZDToggleClass('pages', $c, $number_to_show); ?>>
								<h6><?php printPageTitlelink(); ?></h6>
								<p class="zenpageexcerpt"><?php echo html_encodeTagged(shortenContent(getPageContent(), 80, getOption("zenpage_textshorten_indicator"))); ?></p>
							</li>
						<?php } ?>
					</ul>
					<hr />
				<?php } ?>
			</div>
			<div class="eight columns">
				<?php
				if (($numnews > 0) && ($zpskel_usenews)) {
					$number_to_show = 5;
					$c = 0;
					?>
					<h5><?php printf(gettext('Articles (%s)'), $numnews); ?> <small><?php printZDSearchShowMoreLink("news", $number_to_show); ?></small></h5>
					<ul class="searchresults">
						<?php
						while (next_news()) {
							$c++;
							?>
							<li<?php printZDToggleClass('news', $c, $number_to_show); ?>>
								<h6><?php printNewsURL(); ?></h6>
								<p class="zenpageexcerpt"><?php echo html_encodeTagged(shortenContent(getNewsContent(), 80, getOption("zenpage_textshorten_indicator"))); ?></p>
							</li>
						<?php } ?>
					</ul>
					<hr />
				<?php } ?>
			</div>
		<?php } ?>
		<div class="sixteen columns">
			<h5>
				<?php
				if (getOption('search_no_albums')) {
					if (!getOption('search_no_images') && ($numpages + $numnews) > 0) {
						printf(gettext('Images (%s)'), $numimages);
					}
				} else {
					if (getOption('search_no_images')) {
						if (($numpages + $numnews) > 0) {
							printf(gettext('Albums (%s)'), $numalbums);
						}
					} else {
						printf(gettext('Albums (%1$s) &amp; Images (%2$s)'), $numalbums, $numimages);
					}
				}
				?>
			</h5>
		</div>
		<?php if (getNumAlbums() != 0) { ?>
			<?php
			$c = 0;
			while (next_album()):
				?>
				<div class="one-third column album">
					<h6><?php echo html_encode(getBareAlbumTitle()); ?></h6>
					<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo html_encode(getBareAlbumTitle()); ?>">
						<?php printCustomAlbumThumbImage(getBareAlbumTitle(), null, 420, 200, 420, 200, null, null, 'remove-attributes'); ?>
					</a>
					<div class="album-meta">
						<ul class="taglist">
							<li class="meta-date"><?php printAlbumDate(""); ?></li>
							<li class="meta-contents">
								<?php
								if ((getNumAlbums() > 0) && (getNumImages() > 0)) {
									$divider = '- ';
								} else {
									$divider = '';
								}
								?>
								<?php if (getNumAlbums() > 0) echo getNumAlbums() . ' ' . gettext("subalbums"); ?>
								<?php echo $divider; ?>
								<?php if (getNumImages() > 0) echo getNumImages() . ' ' . gettext("images"); ?>
							</li>
						</ul>
					</div>
					<p class="albumdesc"><?php echo shortenContent(getAlbumDesc(), 80, '...'); ?></p>
					<hr />
				</div>
				<?php
				$c++;
				if ($c == 3) {
					echo '<br class="clear" />';
					$c = 0;
				} endwhile;
			?>
		<?php } ?>
		<?php if (getNumImages() > 0) { ?>
			<!-- Start Images -->
			<?php
			echo '<br class="clear" />';
			$c = 0;
			if ($zpskel_thumbsize == 'small') {
				$colclass = 'two';
				$breakcount = 8;
				$imagesize = 190;
			} else {
				$colclass = 'four';
				$breakcount = 4;
				$imagesize = 220;
			}
			while (next_image()):
				?>
				<div class="<?php echo $colclass; ?> columns image imagegrid">
					<a href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>">
						<?php
						if ($thumbcrop) {
							printCustomSizedImage(getBareImageTitle(), null, $imagesize, $imagesize, $imagesize, $imagesize, null, null, 'remove-attributes', null, true);
						} else {
							printCustomSizedImage(getBareImageTitle(), $imagesize, null, null, null, null, null, null, 'remove-attributes', null, true);
						}
						?>
					</a>
				</div>
				<?php
				$c++;
				$mobilebreak = $c % 2;
				if ($c == $breakcount) {
					echo '<br class="clear clearforboth" />';
					$c = 0;
				} else if ($mobilebreak == 0) {
					echo '<br class="clear clearformobile" />';
				} endwhile;
			?>
		<?php } ?>

		<div class="sixteen columns">
			<?php if ((hasNextPage()) || (hasPrevPage())) printPageListWithNav('«', '»', false, true, 'pagination', null, true, 5); ?>
			<?php if ($numimages > 0) printPPSlideShowLink(gettext('Slideshow')); ?>
		</div>
	</div>
</div>

<?php include ("inc-bottom.php"); ?>
<?php include ("inc-footer.php"); ?>