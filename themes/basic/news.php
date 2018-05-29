<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
if (class_exists('CMS')) {
	?>
	<!DOCTYPE html>
	<html>
		<head>

			<?php zp_apply_filter('theme_head'); ?>
			<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
			<link rel="stylesheet" href="<?php echo pathurlencode(dirname(dirname($zenCSS))); ?>/common.css" type="text/css" />


			<?php if (class_exists('RSS')) printRSSHeaderLink("Pages", "Zenpage news", ""); ?>
		</head>

		<body>
			<?php zp_apply_filter('theme_body_open'); ?>

			<div id="main">
				<div id="header">
					<div id="gallerytitle">
						<?php
						if (getOption('Allow_search')) {
							printSearchForm('');
						}
						?>
						<h2>
							<?php printHomeLink('', ' | '); ?>
							<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>"><?php printGalleryTitle(); ?></a>
							<?php
							if (is_NewsArticle()) {
								echo ' | ';
								printCustomPageURL(NEWS_LABEL, 'news');
							} else {
								echo ' | ' . NEWS_LABEL;
							}
							printZenpageItemsBreadcrumb(" | ", "");
							printCurrentNewsCategory(" | ");
							printNewsTitle(" | ");
							printCurrentNewsArchive(" | ");
							?>
						</h2>
					</div>

				</div>

				<?php
				if (is_NewsArticle()) { // single news article
					?>
					<?php if ($prev = getPrevNewsURL()) { ?><div class="singlenews_prev"><?php printPrevNewsLink(); ?></div><?php } ?>
					<?php if ($next = getNextNewsURL()) { ?><div class="singlenews_next"><?php printNextNewsLink(); ?></div><?php } ?>
					<?php if ($prev || $next) { ?><br class="clearall"><?php } ?>
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
								printNewsCategories(", ", gettext("Categories: "), "newscategories");
							}
							?>
						</span>
						<br />
						<?php printCodeblock(1); ?>
						<?php printNewsContent(); ?>
						<?php printCodeblock(2); ?>
					</div>
					<?php
					@call_user_func('printCommentForm');
				} else { // news article loop
					while (next_news()) {
						$newstypedisplay = NEWS_LABEL;
						if (stickyNews()) {
							$newstypedisplay .= ' <small><em>' . gettext('sticky') . '</em></small>';
						}
						?>
						<div class="newsarticle<?php if (stickyNews()) echo ' sticky'; ?>">
							<h3><?php printNewsURL(); ?><?php echo " <span class='newstype'>[" . $newstypedisplay . "]</span>"; ?></h3>
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
									?>
								</span>
								<?php
								if (!empty($cat)) {
									echo ' | ';
									printNewsCategories(", ", gettext("Categories: "), "newscategories");
								}
								?>
							</div> <!-- newsarticlecredit -->
							<br clear="all">
							<?php printCodeblock(1); ?>
							<?php printNewsContent(); ?>
							<?php printCodeblock(2); ?>
							<br class="clearall">
						</div>
						<?php
					}
					printNewsPageListWithNav(gettext('next »'), gettext('« prev'), true, 'pagelist', true);
				}
				?>

			</div>
			<div id="credit">
				<?php
				if (function_exists('printFavoritesURL')) {
					printFavoritesURL(NULL, '', ' | ', '<br />');
				}
				?>
				<?php if (class_exists('RSS')) printRSSLink('Gallery', '', 'RSS', ' | '); ?>
				<?php printCustomPageURL(gettext("Archive View"), "archive"); ?> | <?php printSoftwareLink(); ?>
				<?php @call_user_func('printUserLogin_out', " | "); ?>
			</div>
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