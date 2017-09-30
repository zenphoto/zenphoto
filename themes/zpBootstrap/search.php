<?php include('inc_header.php'); ?>

	<!-- .container main -->
		<!-- .page-header -->
			<!-- .header -->
				<h3><?php echo gettext('Search'); ?></h3>
			</div><!-- .header -->
		</div><!-- /.page-header -->

		<div class="page-header row">
			<div class="col-xs-offset-1 col-xs-10 col-sm-offset-2 col-sm-8 col-md-offset-3 col-md-6">
				<?php printSearchForm(); ?>
			</div>
		</div>

		<div class="search-wrap">
			<?php
			$numimages = getNumImages();
			$numalbums = getNumAlbums();
			$total_gallery = $numimages + $numalbums;

			if ($_zenpage_enabled && !isArchive()) {
				$numnews = getNumNews();
				$numpages = getNumPages();
			} else {
				$numnews = $numpages = 0;
			}
			$total = $total_gallery + $numnews + $numpages;

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
			if (getOption('search_no_albums')) {		//test of images search
				if ($numimages > 0) {
					echo '<h4 class="margin-top-double margin-bottom-double"><strong>'; printf(gettext('Images (%s)'), $numimages); echo '</strong></h4>';
				}
			} else {
				if (getOption('search_no_images')) {	//test of albums search
					if ($numalbums > 0) {
						echo '<h4 class="margin-top-double margin-bottom-double"><strong>'; printf(gettext('Albums (%s)'), $numalbums); echo '</strong></h4>';
					}
				} else {
					if ($total_gallery > 0) {					//test of albums and images search
						echo '<h4 class="margin-top-double margin-bottom-double"><strong>'; printf(gettext('Albums (%1$s) &amp; Images (%2$s)'), $numalbums, $numimages); echo '</strong></h4>';
					}
				}
			}

			if (extensionEnabled('slideshow')) {
			?>
			<ul class="pager pull-right margin-top-reset hidden-phone"> <!--hidden-phone -->
				<li>
					<?php printSlideShowLink(gettext('Slideshow')); ?>
				</li>
			</ul>
			<?php
			}

			printPageListWithNav('«', '»', false, true, 'pagination pagination-sm', NULL, true, 7);

			if (getNumAlbums() > 0) {
				include('inc_print_album_thumb.php');
			}
			if (getNumImages() > 0) {
				include('inc_print_image_thumb.php');
			}

			printPageListWithNav('«', '»', false, true, 'pagination pagination-sm margin-top-reset', NULL, true, 7);

			if ($_zp_page == 1) {						//test of zenpage searches
				if ($numnews > 0) { ?>
					<h4 class="margin-top-double margin-bottom-double"><strong><?php printf(gettext('Articles (%s)'), $numnews); ?></strong></h4>
					<?php while (next_news()) { ?>
						<div class="list-post clearfix">
							<h4 class="post-title"><?php printNewsURL(); ?></h4>
							<div class="post-content clearfix">
								<?php echo shortenContent(getBare(getNewsContent()), 200, getOption("zenpage_textshorten_indicator")); ?>
							</div>
						</div>
					<?php
					}
				}

				if ($numpages > 0) { ?>
					<h4 class="margin-top-double margin-bottom-double"><strong><?php printf(gettext('Pages (%s)'), $numpages); ?></strong></h4>
					<?php while (next_page()) { ?>
						<div class="list-post clearfix">
							<h4 class="post-title"><?php printPageURL(); ?></h4>
							<div class="post-content clearfix">
								<?php echo shortenContent(getBare(getPageContent()), 200, getOption("zenpage_textshorten_indicator")); ?>
							</div>
						</div>
					<?php
					}
				}
			}
			?>
		</div><!-- /.search-wrap -->

	</div><!-- /.container main -->

<?php include('inc_footer.php'); ?>