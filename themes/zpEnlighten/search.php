<?php
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<head>
	<?php printZDRoundedCornerJS(); ?>
	<?php zp_apply_filter('theme_head'); ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
	<?php printRSSHeaderLink('Gallery', gettext('Gallery RSS')); ?>
	<?php printZDSearchToggleJS(); ?>
</head>
<body>
	<?php zp_apply_filter('theme_body_open'); ?>
	<div id="main">
		<?php include("header.php"); ?>

		<div id="breadcrumb">
			<?php
			$numimages = getNumImages();
			$numalbums = getNumAlbums();
			$total = $numimages + $numalbums;
			$zenpage = getOption('zp_plugin_zenpage');
			if ($zenpage && !isArchive()) {
				$numpages = getNumPages();
				$numnews = getNumNews();
				$total = $total + $numnews + $numpages;
			} else {
				$numpages = $numnews = 0;
			}
			?>
			<h2>
				<?php if (extensionEnabled('zenpage')) { ?>
					<a href="<?php echo getGalleryIndexURL(); ?>" title="<?php gettext('Index'); ?>"><?php echo gettext("Index") . " » "; ?></a>
				<?php } else { ?>
					<a href="<?php echo htmlspecialchars(getCustomPageURl('gallery')); ?>" title="<?php echo gettext('Gallery'); ?>"><?php echo gettext("Gallery") . " » "; ?></a>
				<?php } ?>
				<?php echo gettext("Search"); ?> » <strong><?php echo getSearchWords(); ?></strong> (<?php echo $total; ?> results)
			</h2>
		</div>

		<div id="content">
			<div id="content-left">
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

					<?php
				}
				if ($_zp_page == 1) { //test of zenpage searches
					if ($numpages > 0) {
						$number_to_show = 5;
						$c = 0;
						?>
						<hr />
						<h3 class="searchheader"><?php printf(gettext('Pages (%s)'), $numpages); ?> <small><?php printZDSearchShowMoreLink("pages", $number_to_show); ?></small></h3>
						<ul class="searchresults">
							<?php
							while (next_page()) {
								$c++;
								?>
								<li<?php printZDToggleClass('pages', $c, $number_to_show); ?>>
									<h4><?php printPageTitlelink(); ?></h4>
									<p class="zenpageexcerpt"><?php echo html_encodeTagged(shortenContent(getPageContent(), 80, getOption("zenpage_textshorten_indicator"))); ?></p>
								</li>
								<?php
							}
							?>
						</ul>
						<?php
					}
					if ($numnews > 0) {
						$number_to_show = 3;
						$c = 0;
						$art = 'article';
						if ($numnews > 1)
							$art .= 's'
							?>

						<h3 class="searchheader"><?php printf(gettext('%s ' . $art . ' found'), $numnews); ?> </h3>
						<div style="text-align: right; margin-right: 20px;" class="moreresults"><small><?php printZDSearchShowMoreLink("news", $number_to_show); ?></small></div>
						<ul class="searchresults news">
							<?php
							while (next_news()) {
								$c++;
								?>
								<li<?php printZDToggleClass('news', $c, $number_to_show); ?>>
									<h4><?php printNewsURL(); ?></h4>
									<p class="zenpageexcerpt"><?php echo html_encodeTagged(shortenContent(getNewsContent(), 80, getOption("zenpage_textshorten_indicator"))); ?></p>
								</li>
								<?php
							}
							?>
						</ul>
						<?php
					}
				}
				?>
				<h3 class="searchheader imgresults">
					<?php
					$alb = 'album';
					$imgs = 'image';
					if ($numalbums > 1)
						$alb .= 's';
					if ($numimages > 1)
						$imgs .= 's';
					if (getOption('search_no_albums')) {
						if (!getOption('search_no_images') && ($numpages + $numnews) > 0) {
							printf(gettext('%s ' . $imgs . ' found'), $numimages);
						}
					} else {
						if (getOption('search_no_images')) {
							if (($numpages + $numnews) > 0) {
								printf(gettext('%s ' . $alb . ' found'), $numalbums);
							}
						} else {
							printf(gettext('%1$s ' . $alb . ' and %2$s ' . $imgs . ' found'), $numalbums, $numimages);
						}
					}
					?>
				</h3>
				<?php if (getNumAlbums() != 0) { ?>
					<div id="albums">
						<?php while (next_album()): ?>
							<div class="album">
								<div class="thumb">
									<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo html_encode(getBareAlbumTitle()); ?>"><?php printCustomAlbumThumbImage(getBareAlbumTitle(), NULL, 255, 75, 255, 75); ?></a>
								</div>
								<div class="albumdesc">
									<h3><a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo html_encode(getBareAlbumTitle()); ?>"><?php printAlbumTitle(); ?></a></h3>
									<h3 class="date"><?php printAlbumDate(""); ?></h3>
								<!-- p><?php echo html_encodeTagged(shortenContent(getAlbumDesc(), 45)); ?></p --></h3>
								</div>
								<p style="clear: both; "></p>
							</div>
						<?php endwhile; ?>

					</div>
				<?php } ?>
				<?php if (getNumImages() > 0) { ?>
					<div id="images">
						<?php while (next_image()): ?>
							<div class="image">
								<div class="imagethumb"><a href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php printImageThumb(getBareImageTitle()); ?></a></div>
							</div>
						<?php endwhile; ?>
					</div>
					<br clear="all" />
				<?php } ?>
				<?php
				if (function_exists('printSlideShowLink')) {
					echo '<div id="slideshowlink" class="search">';
					printSlideShowLink(gettext('View Slideshow'));
					echo '</div>';
				}
				if ($total == 0) {
					echo "<p>" . gettext("Sorry, no matches found. Try refining your search.") . "</p>";
				}

				printPageListWithNav("« " . gettext("prev"), gettext("next") . " »");
				?>

			</div><!-- content left-->



			<div id="sidebar">
				<?php include("sidebar.php"); ?>
			</div><!-- sidebar -->



			<div id="footer">
				<?php include("footer.php"); ?>
			</div>
		</div><!-- content -->

	</div><!-- main -->
	<?php zp_apply_filter('theme_body_close'); ?>
</body>
</html>