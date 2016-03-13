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
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" />
			<?php jqm_loadScripts(); ?>
		</head>

		<body>
			<?php zp_apply_filter('theme_body_open'); ?>


			<div data-role="page" id="mainpage">

				<?php jqm_printMainHeaderNav(); ?>

				<div class="ui-content" role="main">
					<div class="content-primary">
						<h2 class="breadcrumb">
							<?php
								printZenpageItemsBreadcrumb(' ', '');
								printCurrentNewsCategory(" ");
								printNewsTitle(" ");
								printCurrentNewsArchive(" | ");
							?>
						</h2>
						<?php
// single news article
						if (is_NewsArticle()) {
							?>
							<?php
							printNewsContent();
							printCodeblock(1);
							?>
							<br class="clearall" /><br />
							<?php printNewsCategories(', ', gettext('Categories: '), 'catlist'); ?>
							<?php printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'catlist', ', '); ?>
							<?php
							if (function_exists('printCommentForm')) {
								printCommentForm();
							}
							?>
							<br class="clearall" />
							<?php
							if (getPrevNewsURL()) {
								$prevnews = getPrevNewsURL();
								?><a class="imgprevious" href="<?php echo html_encode($prevnews['link']); ?>" data-role="button" data-icon="arrow-l" data-iconpos="left" data-inline="true"><?php echo gettext("prev"); ?></a><?php } ?>
							<?php
							if (getNextNewsURL()) {
								$nextnews = getNextNewsURL();
								?><a class="imgnext" href="<?php echo html_encode($nextnews['link']); ?>" data-role="button" data-icon="arrow-r" data-iconpos="right" data-inline="true"><?php echo gettext("next"); ?></a><?php } ?>
							<?php if (getPrevNewsURL() || getNextNewsURL()) { ?><?php } ?>


								<?php
							} else {
								printNewsPageListWithNav(gettext('next »'), gettext('« prev'), true, 'pagelist', true, 7);
								?>
							<ul data-role="listview" data-inset="true" data-theme="a" class="ui-listview ui-group-theme-a">
		<?php while (next_news()): ?>
									<li>
										<a href="<?php echo html_encode(jqm_getLink()); ?>" title="<?php printBareNewsTitle(); ?>">
									<?php printNewsTitle(); ?> <small>(<?php printNewsDate(); ?>)</small>
											<div class="albumdesc"><?php echo shortenContent(getBare(getNewsContent()), 57, '(...)', false); ?></div>
										</a>
									</li>
								<?php
							endwhile;
							?>
							</ul>
		<?php
		printNewsPageListWithNav(gettext('next »'), gettext('« prev'), true, 'pagelist', true, 7);
	}
	?>

					</div>
					<div class="content-secondary">
				<?php jqm_printMenusLinks(); ?>
					</div>
				</div><!-- /content -->
			<?php jqm_printBacktoTopLink(); ?>
			<?php jqm_printFooterNav(); ?>
			</div><!-- /page -->

	<?php zp_apply_filter('theme_body_close');
	?>
		</body>
	</html>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>