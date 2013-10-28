<?php

// force UTF-8 Ø

if (!defined('WEBPATH')) die();

?>
<!DOCTYPE html>
<html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<?php printHeadTitle(); ?>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
	<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery',gettext('Gallery RSS')); ?>
	<?php printZDSearchToggleJS(); ?>
</head>
<body>
<?php zp_apply_filter('theme_body_open'); ?>
<div id="main">
		<div id="header">
		<h1><?php printGalleryTitle(); ?></h1>
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
				$catlist = array('news'=>$categorylist,'albums'=>'0','images'=>'0','pages'=>'0');
				printSearchForm(NULL, 'search', NULL, gettext('Search category'), NULL, NULL, $catlist);
			} else {
				$albumlist = $_zp_current_search->getAlbumList();
				if (is_array($albumlist)) {
					$album_list = array('albums'=>$albumlist,'pages'=>'0', 'news'=>'0');
					printSearchForm(NULL, 'search', NULL, gettext('Search album'), NULL, NULL, $album_list);
				} else {
					printSearchForm("","search","",gettext("Search gallery"));
				}
			}
		}
		?>
		</div>

<div id="breadcrumb">
		<h2><a href="<?php echo getGalleryIndexURL(false);?>" title="<?php echo gettext('Index'); ?>"><?php echo gettext('Index'); ?> » </a><?php printSearchBreadcrumb(" » ");	?>
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
		if ($total > 0 ) {
			?>
			<h3>
			<?php
			printf(ngettext('%1$u Hit for <em>%2$s</em>','%1$u Hits for <em>%2$s</em>',$total), $total, html_encode($searchwords));
			?>
			</h3>
			<?php
		}
		if ($_zp_page == 1) { //test of zenpage searches
			if ($numpages > 0) {
				$number_to_show = 5;
				$c = 0;
				?>
				<hr />
				<h3><?php printf(gettext('Pages (%s)'),$numpages); ?> <small><?php	printZDSearchShowMoreLink("pages",$number_to_show); ?></small></h3>
					<ul class="searchresults">
					<?php
					while (next_page()) {
						$c++;
						?>
						<li<?php printZDToggleClass('pages',$c,$number_to_show); ?>>
						<h4><?php printPageTitlelink(); ?></h4>
							<p class="zenpageexcerpt"><?php echo shortenContent(strip_tags(getPageContent()),80,getOption("zenpage_textshorten_indicator")); ?></p>
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
				<h3><?php printf(gettext('Articles (%s)'),$numnews); ?> <small><?php	printZDSearchShowMoreLink("news",$number_to_show); ?></small></h3>
					<ul class="searchresults">
					<?php
					while (next_news()) {
						$c++;
						?>
						<li<?php printZDToggleClass('news',$c,$number_to_show); ?>>
						<h4><?php printNewsTitleLink(); ?></h4>
							<p class="zenpageexcerpt"><?php echo shortenContent(strip_tags(getNewsContent()),80,getOption("zenpage_textshorten_indicator")); ?></p>
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
						printf(gettext('Images (%s)'),$numimages);
					}
				} else {
					if (getOption('search_no_images')) {
						if (($numpages + $numnews) > 0) {
							printf(gettext('Albums (%s)'),$numalbums);
						}
					} else {
						printf(gettext('Albums (%1$s) &amp; Images (%2$s)'),$numalbums,$numimages);
					}
				}
			?>
			</h3>
		<?php if (getNumAlbums() != 0) { ?>
			<div id="albums">
				<?php while (next_album()): ?>
					<div class="album">
							<div class="thumb">
							<a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo gettext('View album:'); ?> <?php printBareAlbumTitle();?>"><?php printCustomAlbumThumbImage(getBareAlbumTitle(), NULL, 95, 95, 95, 95); ?></a>
 							 </div>
								<div class="albumdesc">
									<h3><a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo gettext('View album:'); ?> <?php printBareAlbumTitle();?>"><?php printAlbumTitle(); ?></a></h3>
 									<?php printAlbumDate(""); ?>
									<p><?php echo truncate_string(getAlbumDesc(), 45); ?></p>
								</div>
					</div>
				<?php endwhile; ?>
			</div>
			<?php } ?>
<?php if (getNumImages() > 0) { ?>
			<div id="images">
				<?php while (next_image()) { ?>
				<div class="image">
					<div class="imagethumb"><a href="<?php echo html_encode(getImageLinkURL());?>" title="<?php printBareImageTitle();?>"><?php printImageThumb(getBareImageTitle()); ?></a></div>
				</div>
				<?php } ?>
			</div>
		<br class="clearall" />
<?php } ?>
		<?php
		@call_user_func('printSlideShowLink');
		if ($total == 0) {
				echo "<p>".gettext("Sorry, no matches found. Try refining your search.")."</p>";
			}

			printPageListWithNav("« ".gettext("prev"),gettext("next")." »");
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
<?php
zp_apply_filter('theme_body_close');
?>
</body>
</html>