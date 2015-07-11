<div class="left">

	<?php
	if (($zpfocus_showrandom) != 'none') {
		if (($zpfocus_showrandom) == 'single') {
			$sscount = 1;
		} else {
			$sscount = 5;
		}
		if ((in_context(ZP_ALBUM)) || (in_context(ZP_IMAGE))) {
			$sstype = 'album';
			$ssalbum = $_zp_current_album;
			$sstitle = gettext('Random Album Image');
		} else {
			$sstype = 'all';
			$ssalbum = null;
			$sstitle = gettext('Random Gallery Image');
		}
		?>
		<div id="random-wrap">
			<?php printRandomImages($sscount, '', $sstype, $ssalbum, 300, 300, true); ?>
			<div id="random-title">
				<?php echo $sstitle; ?>
			</div>
		</div>
	<?php } ?>

	<!-- PRINTS ZENPAGE EXTRA CONTENT FOR NEW ITEMS, IF APPLICABLE -->
	<?php if (($zenpage) && (getNewsExtraContent())) { ?>
		<div class="extracontent">
			<?php printNewsExtraContent(); ?>
		</div>
	<?php } ?>

	<!-- PRINTS ZENPAGE EXTRA CONTENT FOR PAGE ITEMS, IF APPLICABLE -->
	<?php if (($zenpage) && (getPageExtraContent())) { ?>
		<div class="extracontent">
			<?php printPageExtraContent(); ?>
		</div>
	<?php } ?>

	<!-- PRINTS GALLERY DESCRIPTION -->
	<h4 class="blockhead"><span><?php echo gettext('About'); ?></span></h4>
	<?php printGalleryDesc(); ?>

	<!-- PRINTS ZENPAGE LATEST NEWS, IF APPLICABLE -->
	<?php
	if (($zenpage) && (getLatestNews()) && ($_zp_gallery_page != 'news.php') && ($zpfocus_news)) {
		if ((($zpfocus_spotlight) == 'latest') && ($_zp_gallery_page == 'index.php')) {
			?>
			<div id="spotlight2">
				<h4 class="blockhead"><span><?php echo gettext('More News'); ?></span></h4>
				<?php printLatestNews(4, '', true, true, 150, true, null); ?>
			<?php } else { ?>
				<div id="spotlight1">
					<h4 class="blockhead"><span><?php echo gettext('Latest News'); ?></span></h4>
					<?php printLatestNews(3, '', true, true, 150, true, null); ?>
				<?php } ?>
			</div>
		<?php } ?>

		<!-- PRINTS COMMENTS IN SIDEBAR -->

		<!-- PRINTS LATEST DISQUS COMMENTS, IF PLUGIN EXISTS -->
		<?php if (function_exists('printLatestDisqus')) { ?>
			<h4 class="blockhead"><span><?php echo gettext('Latest Comments'); ?></span></h4>
			<?php
			printLatestDisqus(3, 100);
		} else {
			?>

			<!-- IF NO DISQUS, PRINTS LATEST GALLERY COMMENTS OR NEWS COMMENTS -->
			<?php if ($_zp_gallery_page != 'news.php') { ?>
				<?php if ((function_exists('printCommentForm')) && (getLatestComments(1, 'all'))) { ?>
					<h4 class="blockhead"><span><?php echo gettext('Latest Gallery Comments'); ?></span></h4>
					<?php printLatestComments(3, '100'); ?>
				<?php } ?>
			<?php } else { ?>
				<?php if (function_exists('printCommentForm')) { ?>
					<h4 class="blockhead"><span><?php echo gettext('Latest News Comments'); ?></span></h4>
					<?php printLatestComments(3, '100'); ?>
				<?php } ?>
				<?php
			}
		}
		?>

		<!-- PRINTS NEWS CATEGORIES IF IN NEWS CONTEXT -->
		<?php if ($_zp_gallery_page == 'news.php') { ?>
			<div id="newscats">
				<h4 class="blockhead"><span><?php echo gettext('News Categories'); ?></span></h4>
						<?php printAllNewsCategories(''); ?>
			</div>
		<?php } ?>

	</div>