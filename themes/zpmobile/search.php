<?php
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="<?php echo LOCAL_CHARSET; ?>">
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" />
		<?php jqm_loadScripts(); ?>
<?php printZDSearchToggleJS(); ?>
	</head>

	<body>
<?php zp_apply_filter('theme_body_open'); ?>
		<div data-role="page" id="mainpage">

<?php jqm_printMainHeaderNav(); ?>

			<div class="ui-content" role="main">
				<div class="content-primary">

					<h2><?php printSearchBreadcrumb(); ?></h2>
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
					if (getOption('Allow_search')) {
						$categorylist = $_zp_current_search->getCategoryList();
						if (is_array($categorylist)) {
							$catlist = array('news' => $categorylist, 'albums' => '0', 'images' => '0', 'pages' => '0');
							printSearchForm(NULL, 'search', NULL, gettext('Search'), NULL, NULL, $catlist);
						} else {
							$albumlist = $_zp_current_search->getAlbumList();
							if (is_array($albumlist)) {
								$album_list = array('albums' => $albumlist, 'pages' => '0', 'news' => '0');
								printSearchForm(NULL, 'search', NULL, gettext('Search'), NULL, NULL, $album_list);
							} else {
								printSearchForm("", "search", NULL, gettext("Search"));
							}
						}
					}
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
						<h3>
							<?php
							printf(ngettext('%1$u Hit for <em>%2$s</em>', '%1$u Hits for <em>%2$s</em>', $total), $total, html_encode($searchwords));
							?>
						</h3>
						<?php
					}
					if ($zenpage && $_zp_page == 1) { //test of zenpage searches
						if ($numpages > 0 && ZP_PAGES_ENABLED) {
							$number_to_show = 5;
							$c = 0;
							?>
							<hr />
							<h3><?php printf(gettext('Pages (%s)'), $numpages); ?> <small><?php printZDSearchShowMoreLink("pages", $number_to_show); ?></small></h3>
							<ul data-role="listview" data-inset="true" data-theme="c" class="ui-listview ui-group-theme-a">
								<?php
								while (next_page()) {
									$c++;
									?>
									<li<?php printZDToggleClass('pages', $c, $number_to_show); ?>>
										<h4><?php printPageURL(); ?></h4>
										<p class="zenpageexcerpt"><?php echo shortenContent(getBare(getPageContent()), 80, getOption("zenpage_textshorten_indicator")); ?></p>
									</li>
									<?php
								}
								?>
							</ul>
							<?php
						}
						if ($numnews > 0 && ZP_NEWS_ENABLED) {
							$number_to_show = 5;
							$c = 0;
							?>
							<h3><?php printf(gettext('Articles (%s)'), $numnews); ?> <small><?php printZDSearchShowMoreLink("news", $number_to_show); ?></small></h3>
							<ul data-role="listview" data-inset="true" data-theme="c" class="ui-listview ui-group-theme-a">
								<?php
								while (next_news()) {
									$c++;
									?>
									<li<?php printZDToggleClass('news', $c, $number_to_show); ?>>
										<h4><?php printNewsURL(); ?></h4>
										<p class="zenpageexcerpt"><?php echo shortenContent(getBare(getNewsContent()), 80, getOption("zenpage_textshorten_indicator")); ?></p>
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
						<ul data-role="listview" data-inset="true">
	<?php while (next_album()): ?>
							<li>
								<a href="<?php echo html_encode(getAlbumURL());?>" title="<?php echo gettext('View album:'); ?>">
									<?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, 79, 79, 79, 79, NULL, null, NULL,NULL); ?>
									<h3><?php printAlbumTitle(); ?><small> (<?php printAlbumDate(''); ?>)</small></h3>
									<div class="albumdesc"><?php echo shortenContent(getAlbumDesc(), 100,'(...)',false); ?></div>
									<small class="ui-li-aside ui-li-count"><?php jqm_printImageAlbumCount()?></small>
								</a>
							</li>
						<?php endwhile; ?>
						</ul>
					<?php } ?>
						<?php if (getNumImages() > 0) { ?>
						<div class="ui-grid-c">
	<?php
	$count = '';
	while (next_image()) {
		$count++;
				switch($count) {
					case 1:
						$imgclass = ' ui-block-a';
						break;
					case 2:
						$imgclass = ' ui-block-b';
						break;
					case 3:
						$imgclass = ' ui-block-c';
						break;
					case 4:
						$imgclass = ' ui-block-d';
						$count = ''; // reset to start with a again;
						break;
				}
	?>
				<a class="image<?php echo $imgclass; ?>" href="<?php echo html_encode(getImageURL());?>" title="<?php printBareImageTitle();?>">
					<?php printCustomSizedImage(getAnnotatedImageTitle(), NULL,230, 230, 230, 230, NULL, NULL, NULL, NULL, true, NULL); ?>
				</a>
	<?php } ?>
						</div>
						<br class="clearall" />
					<?php } ?>
					<?php
					if (function_exists('printSlideShowLink'))
						printSlideShowLink(gettext('View Slideshow'));
					if ($total == 0) {
						echo "<p>" . gettext("Sorry, no matches found. Try refining your search.") . "</p>";
					}

					printPageListWithNav("« " . gettext("prev"), gettext("next") . " »");
					?>

				</div>
				<div class="content-secondary">
<?php jqm_printMenusLinks(); ?>
				</div>
			</div><!-- /content -->
			<?php jqm_printBacktoTopLink(); ?>
		<?php jqm_printFooterNav(); ?>
		</div><!-- /page -->
<?php zp_apply_filter('theme_body_close'); ?>
	</body>
</html>