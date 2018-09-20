<?php
// force UTF-8 Ø
if (!defined('WEBPATH')) die();
zp_apply_filter('theme_file_top')
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php 
		include("inc-head.php");
		printZDSearchToggleJS(); 
		?>
	</head>
	<body>
		<?php 
		include("inc-header.php");
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
		$searchwords = getSearchWords();
		$searchdate = getSearchDate();
		if (!empty($searchdate)) {
			if (!empty($searchwords)) {
				$searchwords .= ": ";
			}
			$searchwords .= $searchdate;
		}
		$stageWidth = getOption('zenfluid_stagewidth');
		$stageStyle = ($stageWidth > 0) ? 'style="max-width: ' . $stageWidth . 'px"' : '';
		$thumbstageStyle = (getOption('zenfluid_stagethumb')) ? $stageStyle : ''; 
		?>
		<div class="stage" <?php echo $stageStyle;?>>
			<?php 
			if ($total > 0 ) { 
				?>
				<div class="title border colour">
					<?php printf(ngettext('%1$u Hit for <em>%2$s</em>','%1$u Hits for <em>%2$s</em>',$total), $total, html_encode($searchwords)); ?>
				</div>
				<?php 
			}
			if ($_zp_page == 1) { //test of zenpage searches
				if ($numpages > 0) {
					$number_to_show = 1;
					$c = 0; 
					?>
					<div class="title border colour">
						<?php printf(gettext('Pages (%s)'), $numpages);?>
						<span id="searchshowmore"><?php printZDSearchShowMoreLink("pages", $number_to_show); ?></span>
						<ul class="searchresults">
							<?php 
							while (next_page()) {
								$c++; 
								?>
								<li<?php printZDToggleClass('pages', $c, $number_to_show); ?>>
									<?php 
									printPageURL();
									echo "<p>" . shortenContent(strip_tags(getPageContent()), 150, getOption("zenpage_textshorten_indicator")) . "</p>"; 
									?>
								</li>
								<?php 
							} 
							?>
						</ul>
					</div>
					<?php 
				}
				if ($numnews > 0) {
					$number_to_show = 1;
					$c = 0; 
					?>
					<div class="title border colour" style="font-size: 18px;">
						<?php printf(gettext('Articles (%s)'), $numnews); ?>
						<span id="searchshowmore"><?php printZDSearchShowMoreLink("news", $number_to_show); ?></span>
						<ul class="searchresults">
							<?php 
							while (next_news()) {
								$c++; 
								?>
								<li<?php printZDToggleClass('news', $c, $number_to_show); ?>>
									<?php 
									printNewsURL();
									echo "<p>" . shortenContent(strip_tags(getNewsContent()), 150, getOption("zenpage_textshorten_indicator")) . "</p>"; 
									?>
								</li>
								<?php 
							} 
							?>
						</ul>
					</div>
					<?php 
				}
			}
			if ($numalbums > 0) { 
				?>
				<div class="title border colour">
					<?php printf(gettext('Albums (%s)'), $numalbums); ?>
				</div>
				<div class="thumbstage" <?php echo $thumbstageStyle;?>>
					<?php 
					while (next_album()) {
						?>
						<div class="thumbs border colour">
							<a href="<?php echo html_encode(getAlbumURL());?>" title="<?php echo gettext('View: '); printBareAlbumTitle();?>">
								<div class="thumbimage">
									<?php printAlbumThumbImage(getBareAlbumTitle(),"border"); ?>
									<div class="thumbtitle">
										<?php 
										$numItems = getNumImages() + getNumAlbums();
										printAlbumTitle(); echo ' (' . $numItems . ')';
										echo "<p>" . shortenContent(strip_tags(getAlbumDesc()), 150, getOption("zenpage_textshorten_indicator")) . "</p>"; 
										?>
									</div>
								</div>
							</a>
						</div>
						<?php 
					} 
					?>
				</div>
				<?php 
			} 
			?>
			<div class="clearing"></div>
			<?php 
			if ($numimages > 0) { 
				?>
				<div class="title border colour">
					<?php printf(gettext('Images and Videos (%s)'), $numimages); ?>
				</div>
				<div class="thumbstage" <?php echo $thumbstageStyle;?>>
					<?php 
					while (next_image()) {
						?>
						<div class="thumbs border">
							<a href="<?php echo html_encode(getImageURL());?>" title="<?php echo gettext('View: '); printBareImageTitle();?>">
								<div class="thumbimage">
									<?php 
									printImageThumb(getBareImageTitle(),"border");
									if (isImageVideo()) { 
										?>
										<img class="videoplay" src="<?php echo $_zp_themeroot; ?>/images/videoplay.png">
										<?php 
									} 
									?>
									<div class="thumbtitle">
										<?php 
										printImageTitle();
										echo "<p>" . shortenContent(strip_tags(getImageDesc()), 150, getOption("zenpage_textshorten_indicator")) . "</p>"; 
										?>
									</div>
								</div>
							</a>
						</div>
						<?php 
					}
					?>
				</div>
				<?php 
			}
			?>
			<div class="clearing"></div>
			<div class="albumbuttons">
				<?php 
				if (hasPrevPage() || hasNextPage()) { 
					?>
					<div class="button border colour">
						<?php printPageListWithNav("Prev ", " Next", false, true, 'taglist', NULL, true); ?>
					</div>
					<?php 
				} 
				?>
			</div>
			<?php 
			if ($total == 0) { 
				?>
				<div class="title border colour">
					<?php echo gettext("Sorry, no matches found. Try refining your search."); ?>
				</div>
				<?php 
			} 
			?>
		</div>
		<?php include("inc-footer.php");?>
	</body>
</html>
<?php
zp_apply_filter('theme_file_end')
?>