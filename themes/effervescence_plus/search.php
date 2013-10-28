<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();

$thumbnailColumns = "3";
$thumbnailRows = "6";
$navPosition = "left";

$enableRightClickOpen = "true";

$backgroundImagePath = "";
// End of config
?>
<!DOCTYPE html>
<html>
	<head>
		<?php
		zp_apply_filter('theme_head');
		?>
		<?php printHeadTitle(); ?>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<?php $handler->theme_head($_zp_themeroot); ?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			function toggleExtraElements(category, show) {
				if (show) {
					jQuery('.' + category + '_showless').show();
					jQuery('.' + category + '_showmore').hide();
					jQuery('.' + category + '_extrashow').show();
				} else {
					jQuery('.' + category + '_showless').hide();
					jQuery('.' + category + '_showmore').show();
					jQuery('.' + category + '_extrashow').hide();
				}
			}
			// ]]> -->
		</script>
	</head>

	<body onload="blurAnchors()">
		<?php
		zp_apply_filter('theme_body_open');
		$numimages = getNumImages();
		$numalbums = getNumAlbums();
		$total = $numimages + $numalbums;
		$zenpage = extensionEnabled('zenpage');
		if ($zenpage && !isArchive()) {
			$numpages = getNumPages();
			$numnews = getNumNews();
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
		if (!$total) {
			$_zp_current_search->clearSearchWords();
		}
		?>

		<!-- Wrap Header -->
		<div id="header">
			<div id="gallerytitle">

				<!-- Logo -->
				<div id="logo">
					<?php
					$albumlist = NULL;
					if (getOption('Allow_search')) {
						$categorylist = $_zp_current_search->getCategoryList();
						if (is_array($categorylist)) {
							$catlist = array('news'	 => $categorylist, 'albums' => '0', 'images' => '0', 'pages'	 => '0');
							printSearchForm(NULL, 'search', $_zp_themeroot . '/images/search.png', gettext('Search within category'), NULL, NULL, $catlist);
						} else {
							$albumlist = $_zp_current_search->getAlbumList();
							if (is_array($albumlist)) {
								$album_list = array('albums' => $albumlist, 'pages'	 => '0', 'news'	 => '0');
								printSearchForm(NULL, 'search', $_zp_themeroot . '/images/search.png', gettext('Search within album'), NULL, NULL, $album_list);
							} else {
								printSearchForm(NULL, 'search', $_zp_themeroot . '/images/search.png', gettext('Search gallery'));
							}
						}
					}
					printLogo();
					?>
				</div> <!-- logo -->
			</div> <!-- gallerytitle -->

			<!-- Crumb Trail Navigation -->

			<div id="wrapnav">
				<div id="navbar">
					<span><?php printHomeLink('', ' | '); ?>
						<?php
						if (getOption('custom_index_page') === 'gallery') {
							?>
							<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home'); ?></a> |
							<?php
						}
						?>
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>">
							<?php printGalleryTitle(); ?></a></span> |
					<?php
					if (is_array($albumlist)) {
						echo "<em>" . sprintf(ngettext('Search album: %s', 'Search albums: %s', count($albumlist)), implode(',', $albumlist)) . "</em>";
					} else {
						if (is_array($categorylist)) {
							echo "<em>" . sprintf(ngettext('Search category: %s', 'Search categories: %s', count($categorylist)), implode(',', $categorylist)) . "</em>";
						} else {
							printSearchBreadcrumb();
						}
					}
					?>
				</div>
			</div> <!-- wrapnav -->

		</div> <!-- header -->

		<!-- Wrap Subalbums -->
		<div id="subcontent">
			<div id="submain">

				<div id="description">
					<h2>
					</h2>
					<?php
					if ($total > 0) {
						printf(ngettext('%1$u Hit for <em>%2$s</em>', '%1$u Hits for <em>%2$s</em>', $total), $total, html_encode($searchwords));
					}
					if ($zenpage && $_zp_page == 1) { //test of zenpage searches
						define('TRUNCATE_LENGTH', 80);
						define('SHOW_ITEMS', 5);
						?>
						<div id="efsearch">
							<?php
							if ($numpages > 0) {
								?>
								<div id="efsearchhead_pages">
									<h3><?php printf(gettext('Pages (%s)'), $numpages); ?></h3>
									<?php
									if ($numpages > SHOW_ITEMS) {
										?>
										<p class="pages_showmore"><a href="javascript:toggleExtraElements('pages',true);"><?php echo gettext('Show more results'); ?></a></p>
										<p class="pages_showless" style="display:none;"><a href="javascript:toggleExtraElements('pages',false);"><?php echo gettext('Show fewer results'); ?></a></p>
										<?php
									}
									?>
								</div>
								<div class="efsearchtext">
									<ul>
										<?php
										$c = 0;
										while (next_page()) {
											$c++;
											?>
											<li<?php if ($c > SHOW_ITEMS) echo ' class="pages_extrashow" style="display:none;"'; ?>>
												<?php print printPageTitleLink(); ?>
												<p style="text-indent:1em;"><?php echo exerpt($_zp_current_zenpage_page->getContent(), TRUNCATE_LENGTH); ?></p>
											</li>
											<?php
										}
										?>
									</ul>
								</div>
								<?php
							}
							if ($numnews > 0) {
								if ($numpages > 0)
									echo '<br />';
								?>
								<div id="efsearchhead_news">
									<h3><?php printf(gettext('Articles (%s)'), $numnews); ?></h3>
									<?php
									if ($numnews > SHOW_ITEMS) {
										?>
										<p class="news_showmore"><a href="javascript:toggleExtraElements('news',true);"><?php echo gettext('Show more results'); ?></a></p>
										<p class="news_showless" style="display:none;"><a href="javascript:toggleExtraElements('news',false);"><?php echo gettext('Show fewer results'); ?></a></p>
										<?php
									}
									?>
								</div>
								<div class="efsearchtext">
									<ul>
										<?php
										$c = 0;
										while (next_news()) {
											$c++;
											?>
											<li<?php if ($c > SHOW_ITEMS) echo ' class="news_extrashow" style="display:none;"'; ?>>
												<?php printNewsTitleLink(); ?>
												<p style="text-indent:1em;"><?php echo exerpt($_zp_current_zenpage_news->getContent(), TRUNCATE_LENGTH); ?></p>
											</li>
											<?php
										}
										?>
									</ul>
								</div>
								<?php
							}
							if ($total > 0 && ($numpages + $numnews) > 0) {
								?>
								<br />
								<div id="efsearchhead_gallery">
									<h3>
										<?php
										if (getOption('search_no_albums')) {
											if (!getOption('search_no_images')) {
												printf(gettext('Images (%s)'), $numimages);
											}
										} else {
											if (getOption('search_no_images')) {
												printf(gettext('Albums (%s)'), $numalbums);
											} else {
												printf(gettext('Albums (%1$s) &amp; Images (%2$s)'), $numalbums, $numimages);
											}
										}
										?>
									</h3>
								</div>
								<?php
							}
							?>
						</div>
						<?php
					}
					?>
				</div>

				<!-- Album List -->
				<?php
				$firstAlbum = null;
				$lastAlbum = null;
				while (next_album()) {
					if (is_null($firstAlbum)) {
						$lastAlbum = albumNumber();
						$firstAlbum = $lastAlbum;
						?>
						<ul id="albums">
							<?php
						} else {
							$lastAlbum++;
						}
						?>
						<li>
							<?php $annotate = annotateAlbum(); ?>
							<div class="imagethumb">
								<a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo $annotate; ?>">
									<?php printCustomAlbumThumbImage($annotate, null, ALBUM_THMB_WIDTH, null, ALBUM_THMB_WIDTH, ALBUM_THUMB_HEIGHT); ?></a>
							</div>
							<h4><a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo $annotate; ?>"><?php printAlbumTitle(); ?></a></h4></li>
						<?php
					}
					if (!is_null($firstAlbum)) {
						?>
					</ul>
					<?php
				}
				?>
				<div class="clearage"></div>
				<?php printNofM('Album', $firstAlbum, $lastAlbum, getNumAlbums()); ?>
			</div>

			<!-- Wrap Main Body -->
			<?php
			if ($numimages > 0) { /* Only print if we have images. */
				$handler->theme_content(NULL);
			}

			if ($total == 0) {
				?>
				<div id="main3">
					<div id="main2">
						<br />
						<p align="center">
							<?php
							if (empty($searchwords)) {
								echo gettext('Enter your search criteria.');
							} else {
								printf(gettext('Sorry, no matches for <em>%s</em>. Try refining your search.'), html_encode($searchwords));
							}
							?>
						</p>
					</div>
				</div> <!-- main3 -->
				<?php
			}
			?>

			<!-- Page Numbers -->

			<div id="pagenumbers">
				<?php
				if ((getNumAlbums() != 0) || !$_oneImagePage) {
					printPageListWithNav("« " . gettext('prev'), gettext('next') . " »", $_oneImagePage);
				}
				?>
			</div> <!-- pagenumbers -->
		</div> <!-- subcontent -->

		<!-- Footer -->
		<br style="clear:all" />
		<?php
		printFooter();
		zp_apply_filter('theme_body_close');
		?>

	</body>
</html>