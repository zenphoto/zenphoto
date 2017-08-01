<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_head.php'); ?>
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_header.php'); ?>

<div id="background-main" class="background">
	<div class="container<?php if (getOption('full_width')) {echo '-fluid';}?>">
	<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_breadcrumbs.php'); ?>
		<div id="center" class="row" itemscope itemtype="http://schema.org/WebPage">
			<div class="col-sm-9" id="main" itemprop="mainContentOfPage">

				<?php
				$zenpage = extensionEnabled('zenpage');
				$numimages = getNumImages();
				$numalbums = getNumAlbums();
				$total = $numimages + $numalbums;
				if ($zenpage && !isArchive()) {
					$numpages = getNumPages();
					$numnews = getNumNews();
					$total = $total + $numnews + $numpages;
				} else {
					$numpages = $numnews = 0;
				}
				if ($total == 0) {
					$_zp_current_search->clearSearchWords();
				}
				?>

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
					<h1>
					<?php printf(gettext('Results for'));
					echo ':&nbsp;';
					echo html_encode($searchwords); ?>
					</h1>
					<?php
				}
				if ($_zp_page == 1) { //test of zenpage searches
					if ($numpages > 0) {
						$number_to_show = 5;
						$c = 0;
						?>
						<hr />
						<h3><?php printf(gettext('Pages (%s)'), $numpages); ?> <small><?php printZDSearchShowMoreLink("pages", $number_to_show); ?></small></h3>
						<ul class="searchresults">
		<?php
		while (next_page()) {
			$c++;
			?>
								<li<?php printZDToggleClass('pages', $c, $number_to_show); ?>>
									<h4><?php printPageURL(); ?></h4>
									<p class="zenpageexcerpt"><?php echo shortenContent(getPageContent(), 80, getOption("zenpage_textshorten_indicator")); ?></p>
								</li>
							<?php
						}
						?>
						</ul>
						<?php
					}
					if ($numnews > 0) {
						$number_to_show = 5;
						$c = 0;
						?>
						<h3><?php printf(gettext('Articles (%s)'), $numnews); ?> <small><?php printZDSearchShowMoreLink("news", $number_to_show); ?></small></h3>
						<ul class="searchresults">
		<?php
		while (next_news()) {
			$c++;
			?>
								<li<?php printZDToggleClass('news', $c, $number_to_show); ?>>
									<h4><?php printNewsURL(); ?></h4>
									<p class="zenpageexcerpt"><?php echo shortenContent(getNewsContent(), 80, getOption("zenpage_textshorten_indicator")); ?></p>
								</li>
							<?php
						}
						?>
						</ul>
							<?php
						}
					}
					?>
				<h3>
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
				</h3>
					<?php if (getNumAlbums() != 0) { ?>
						<?php include("includes/_albumlist.php"); ?>
<?php } ?>
<?php if (getNumImages() > 0) { ?>
					<div id="images" class="row">
	<?php while (next_image()): ?>
									<div class="col-lg-3 col-md-4 col-sm-6" style="height:<?php echo html_encode(getOption('thumb_size')+55); ?>px">
										<div class="thumbnail" itemtype="http://schema.org/image"><a href="<?php echo html_encode(getFullImageURL()); ?>" title="<?php printBareImageTitle(); ?>" rel="lightbox-search"><?php printImageThumb(getBareImageTitle()); ?></a>
											<div class="caption">
												<p><a href="<?php echo html_encode(getImageURL()); ?>"><?php printBareImageTitle(); ?></a></p>
											</div>	
										</div>
									</div>
								<?php endwhile; ?>
							</div>
				
								<br class="clearall">
							<?php } ?>
							<?php
							if ($total == 0) {
								echo "<p>" . gettext("Sorry, no matches found. Try refining your search.") . "</p>";
							}

				printPageListWithNav("« " . gettext("prev"), gettext("next") . " »");
				?>
			</div>
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_sidebar.php'); ?>
		</div>
	</div>
</div>

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_footer.php'); ?>