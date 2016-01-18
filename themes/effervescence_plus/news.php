<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
if (class_exists('Zenpage') && ZP_NEWS_ENABLED) {
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta charset="<?php echo LOCAL_CHARSET; ?>">
			<?php zp_apply_filter('theme_head'); ?>
			<?php printHeadTitle(); ?>
			<?php if (class_exists('RSS')) printRSSHeaderLink("News", "Zenpage news", ""); ?>
		</head>

		<body onload="blurAnchors()">
			<?php zp_apply_filter('theme_body_open'); ?>
			<!-- Wrap Header -->
			<div id="header">
				<div id="gallerytitle">

					<!-- Logo -->
					<div id="logo">
						<?php
						if (getOption('Allow_search')) {
							printSearchForm(NULL, 'search', $_zp_themeroot . '/images/search.png', gettext('Search'));
						}
						printLogo();
						?>
					</div>
				</div> <!-- gallerytitle -->

				<!-- Crumb Trail Navigation -->
				<div id="wrapnav">
					<div id="navbar">
						<span><?php printHomeLink('', ' | '); printGalleryIndexURL(' | ', getGalleryTitle());	?></span>
						<?php
						printZenpageItemsBreadcrumb(' | ', '');
						printCurrentNewsCategory(" | ");
						printNewsTitle(" | ");
						printCurrentNewsArchive(" | ");
						?>
					</div>
				</div> <!-- wrapnav -->

				<!-- Random Image -->
				<?php printHeadingImage(getRandomImages(getThemeOption('effervescence_daily_album_image'))); ?>
			</div> <!-- header -->

			<!-- Wrap Main Body -->
			<div id="content">

				<small>&nbsp;</small>
				<div id="main2">
					<div id="content-left">
						<?php
						if (is_NewsArticle()) { // single news article
							?>
							<?php if (getPrevNewsURL()) { ?><div class="singlenews_prev"><?php printPrevNewsLink(); ?></div><?php } ?>
							<?php if (getPrevNewsURL()) { ?><div class="singlenews_next"><?php printNextNewsLink(); ?></div><?php } ?>
							<?php if (getPrevNewsURL() OR getPrevNewsURL()) { ?><br class="clearall" /><?php } ?>
							<h3><?php printNewsTitle(); ?></h3>

							<div class="newsarticlecredit">
								<span class="newsarticlecredit-left">
									<?php
									$count = @call_user_func('getCommentCount');
									$cat = getNewsCategories();
									printNewsDate();
									if ($count > 0) {
										echo ' | ';
										printf(gettext("Comments: %d"), $count);
									}
									if (!empty($cat)) {
										echo ' | ';
									}
									?>
								</span>
								<?php
								if (!empty($cat)) {
									printNewsCategories(", ", gettext("Categories: "), "newscategories");
								}
								?>
								<?php printCodeblock(1); ?>
								<?php printNewsContent(); ?>
								<?php printCodeblock(2); ?>
							</div>
							<?php
							@call_user_func('printRating');
							commonComment();
						} else { // news article loop
							commonNewsLoop(true);
						}
						?>

					</div><!-- content left-->
					<div id="sidebar">
						<?php include("sidebar.php"); ?>
					</div><!-- sidebar -->
					<br style="clear:both" />
				</div> <!-- main2 -->

			</div> <!-- content -->

			<?php
			printFooter();
			zp_apply_filter('theme_body_close');
			?>

		</body>
	</html>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>