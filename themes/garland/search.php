<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php
		zp_apply_filter('theme_head');
		?>
		<?php printHeadTitle(); ?>
		<?php $handler->theme_head($_zp_themeroot); ?>
		<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/zen.css" type="text/css" />
		<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', gettext('Gallery RSS')); ?>
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
	<body class="sidebars">
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
		<div id="navigation"></div>
		<div id="wrapper">
			<div id="container">
				<div id="header">
					<div id="logo-floater">
						<div>
							<h1 class="title"><a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a></h1>
						</div>
					</div>
				</div>
				<!-- header -->
				<div class="sidebar">
					<div id="leftsidebar">
						<?php include("sidebar.php"); ?>
					</div>
				</div>
				<div id="center">
					<div id="squeeze">
						<div class="right-corner">
							<div class="left-corner">
								<!-- begin content -->
								<div class="main section" id="main">
									<h2 id="gallerytitle">
										<?php printHomeLink('', ' » '); ?>
										<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a> » <?php printSearchBreadcrumb(' » '); ?>
									</h2>

									<?php
									if ($total > 0) {
										?>
										<p>
											<?php
											printf(ngettext('%1$u Hit for <em>%2$s</em>', '%1$u Hits for <em>%2$s</em>', $total), $total, html_encode($searchwords));
											?>
										</p>
										<?php
									} else {
										echo "<p>" . gettext('Sorry, no matches for your search.') . "</p>";
										$_zp_current_search->setSearchParams('words=');
									}
									?>
									<?php
									if ($zenpage && $_zp_page == 1) { //test of zenpage searches
										define('TRUNCATE_LENGTH', 80);
										define('SHOW_ITEMS', 5);
										?>
										<div id="garland_search">
											<?php
											if ($numpages > 0) {
												?>
												<div id="garland_searchhead_pages">
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
												<div class="garland_searchtext">
													<ul>
														<?php
														$c = 0;
														while (next_page()) {
															$c++;
															?>
															<li<?php if ($c > SHOW_ITEMS) echo ' class="pages_extrashow" style="display:none;"'; ?>>
																<?php printPageTitleLink(); ?>
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
												<div id="garland_searchhead_news">
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
												<div class="garland_searchtext">
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
										}
										if ($total > 0 && ($numpages + $numnews) > 0) {
											?>
											<br />
											<div id="garland_searchhead_gallery">
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
									<div id="albums">
										<?php
										while (next_album()) {
											?>
											<div class="album">
												<a class="albumthumb" href="<?php echo getAlbumLinkURL(); ?>" title="<?php printf(gettext('View album:  %s'), sanitize(getAlbumTitle())); ?>">
													<?php printCustomAlbumThumbImage(getAlbumTitle(), 85, NULL, NULL, 85, 85); ?>
												</a>
												<div class="albumdesc">
													<h3>
														<a href="<?php echo getAlbumLinkURL(); ?>" title="<?php printf(gettext('View album:  %s'), sanitize(getAlbumTitle())); ?>">
															<?php printAlbumTitle(); ?>
														</a>
													</h3>
													<br />
													<small><?php printAlbumDate(); ?></small>
												</div>
												<p style="clear: both;"></p>
											</div>
											<?php
										}
										?>
									</div>
									<p style="clear: both; "></p>
									<?php $handler->theme_content(NULL); ?>
									<?php
									if ((getNumAlbums() != 0) || !$_oneImagePage) {
										printPageListWithNav(gettext("« prev"), gettext("next »"), $_oneImagePage);
									}
									footer();
									?>
									<p style="clear: both;"></p>
								</div>
								<!-- end content -->
								<span class="clear"></span>
							</div>
						</div>
					</div>
					<div class="sidebar">
						<div id="rightsidebar">
						</div>
					</div>
					<span class="clear"></span>
				</div>
				<?php
				zp_apply_filter('theme_body_close');
				?>
				</body>
				</html>
