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


			<?php if (class_exists('RSS')) printRSSHeaderLink("Pages", "Zenpage pages", ""); ?>
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
							printZenpageItemsBreadcrumb(" | ", "");
							printPageTitle(" | ");
							?>
						</h2>
					</div>

				</div>
				<h2><?php printPageTitle(); ?></h2>
				<div id="pagetext">
					<?php printCodeblock(1); ?>
					<?php printPageContent(); ?>
					<?php printCodeblock(2); ?>
				</div>

				<?php
				@call_user_func('printRating');
				@call_user_func('printCommentForm');

				$pages = $_zp_current_page->getPages(NULL, true); // top level only
				if (!empty($pages)) {
					?>
					<br /><hr />
					<?php
					foreach ($pages as $item) {
						$pageobj = newPage($item['titlelink']);
						?>
						<a href="<?php echo $pageobj->getLink(); ?>"><?php echo html_encode($pageobj->getTitle()); ?></a>
						<?php
					}
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