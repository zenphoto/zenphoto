<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
	?>
	<!DOCTYPE html>
	<html<?php printLangAttribute(); ?>>
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
								printCurrentPageAppendix();
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
							if (function_exists('printSizedFeaturedImage')) {
								printSizedFeaturedImage(null,'', null, 580, 580, null, null, null, null, 'featuredimage_singlenews', null, false, null, true);
							}
							?>
							<?php
							printNewsContent();
							printCodeblock(1);
							?>
							<?php printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', ', '); ?>
							<br style="clear:both;" /><br />
							<?php callUserFunction('printRating'); ?>
							<?php
							// COMMENTS TEST
							callUserFunction('printCommentForm');
						} else {
							if (function_exists('printSizedFeaturedImage') && is_NewsCategory()) { // category featured image
								printSizedFeaturedImage($_zp_current_category,'', null, 580, 580, null, null, null, null, 'featuredimage_singlecategory', null, false, null, true);
							}
							printNewsPageListWithNav(gettext('next »'), gettext('« prev'), true, 'pagelist', true);
							echo "<hr />";
// news article loop
							while (next_news()):
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
									if (function_exists('printSizedFeaturedImage')) {
										printSizedFeaturedImage(null,'', null, 95, 95, 95, 95, null, null, 'featuredimage_newslist', null, true, null);
									}
									?>
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
						if(class_exists('ScriptlessSocialSharing')) {
							ScriptlessSocialSharing::printButtons();
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