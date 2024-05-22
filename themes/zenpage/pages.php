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
					<h2><?php
						printGalleryIndexURL('');
						if (!isset($ishomepage)) {
							printZenpageItemsBreadcrumb(" » ", "");
						}
						?><strong><?php
							if (!isset($ishomepage)) {
								printPageTitle(" » ");
							}
							?></strong>
					</h2>
				</div>
				<div id="content-left">
					<h2><?php printPageTitle(); ?></h2>
					<?php
					if (function_exists('printSizedFeaturedImage')) {
						printSizedFeaturedImage(null,'', null, 580, 580, null, null, null, null, 'singlepage_featuredimage', null, false, null, true);
					}
					printPageContent();
					printCodeblock(1);
					if (getTags()) {
						echo gettext('<strong>Tags:</strong>');
					} printTags('links', '', 'taglist', ', ');
					?>
					<br style="clear:both;" /><br />
					<?php
					if (class_exists('ScriptlessSocialSharing')) {
						ScriptlessSocialSharing::printButtons();
					}
					callUserFunction('printRating');
					callUserFunction('printCommentForm');
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