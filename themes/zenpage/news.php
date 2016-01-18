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
			<?php printHeadTitle(); ?>
			<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
			<?php if (class_exists('RSS')) printRSSHeaderLink("News", "Zenpage news", ""); ?>
			<?php zp_apply_filter('theme_head'); ?>
		</head>

		<body>
			<?php zp_apply_filter('theme_body_open'); ?>

			<div id="main">

				<div id="header">
					<h1><?php printGalleryTitle(); ?></h1>
					<?php
					if (getOption('Allow_search')) {
						printSearchForm("", "search", "", gettext("Search"));
					}
					?>
				</div>

				<div id="content">

					<div id="breadcrumb">
						<h2>
							<?php
								printGalleryIndexURL(' » ');
								printZenpageItemsBreadcrumb(' » ', '');
								printCurrentNewsCategory(" » ");
								printNewsTitle(" » ");
								printCurrentNewsArchive(" » ");
								?>
						</h2>
					</div>

					<div id="content-left">


						<?php
// single news article
						if (is_NewsArticle()) {
							if (getPrevNewsURL()) { ?><div class="singlenews_prev"><?php printPrevNewsLink(); ?></div><?php }
       if (getNextNewsURL()) { ?><div class="singlenews_next"><?php printNextNewsLink(); ?></div><?php }
       if (getPrevNewsURL() OR getNextNewsURL()) { ?><br style="clear:both" /><?php }
       ?>
							<h3><?php printNewsTitle(); ?></h3>
							<div class="newsarticlecredit"><span class="newsarticlecredit-left"><?php printNewsDate(); ?> | <?php
									if (function_exists('getCommentCount')) {
										echo gettext("Comments:");
										?> <?php echo getCommentCount(); ?> |<?php } ?> </span> <?php printNewsCategories(", ", gettext("Categories: "), "newscategories"); ?></div>
							<?php
							printNewsContent();
							printCodeblock(1);
							?>
							<?php printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', ', '); ?>
							<br style="clear:both;" /><br />
							<?php @call_user_func('printRating'); ?>
							<?php
							// COMMENTS TEST
							@call_user_func('printCommentForm');
						} else {
							printNewsPageListWithNav(gettext('next »'), gettext('« prev'), true, 'pagelist', true);
							echo "<hr />";
// news article loop
							while (next_news()):;
								?>
								<div class="newsarticle">
									<h3><?php printNewsURL(); ?></h3>
									<div class="newsarticlecredit">
										<span class="newsarticlecredit-left">
											<?php
											printNewsDate();
											if (function_exists('getCommentCount')) {
												?>
												|
												<?php
												echo gettext("Comments:");
												?>
												<?php
												echo getCommentCount();
											}
											?></span>
										<?php
										echo ' | ';
										printNewsCategories(", ", gettext("Categories: "), "newscategories");
										?>
									</div>
									<?php
         printNewsContent();
         printCodeblock(1);
									if (getTags()) {
										echo gettext('<strong>Tags:</strong>');
									} printTags('links', '', 'taglist', ', ');
									?>
									<br style="clear:both;" /><br />
								</div>
								<?php
							endwhile;
							printNewsPageListWithNav(gettext('next »'), gettext('« prev'), true, 'pagelist', true);
						}
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
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>