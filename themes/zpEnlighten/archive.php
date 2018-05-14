<?php
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<head>
	<?php printZDRoundedCornerJS(); ?>
	<?php zp_apply_filter('theme_head'); ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
	<?php printRSSHeaderLink('Gallery', gettext('Gallery')); ?>
</head>

<body>
	<?php zp_apply_filter('theme_body_open'); ?>

	<div id="main">

		<?php include("header.php"); ?>


		<div id="breadcrumb">
			<h2>
				<?php if (extensionEnabled('zenpage')) { ?>
					<a href="<?php echo getGalleryIndexURL(); ?>" title="<?php gettext('Index'); ?>"><?php echo gettext("Index"); ?></a>»
				<?php } ?>
				<a href="<?php echo htmlspecialchars(getCustomPageURl('gallery')); ?>" title="<?php echo gettext('Gallery'); ?>"><?php echo gettext("Gallery") . " » "; ?></a>
				<strong><?php echo gettext("Archive View"); ?></strong>
			</h2>
		</div>

		<div id="content">
			<div id="content-left">
				<div id="archive">
					<h3><?php echo gettext('Gallery'); ?></h3>
					<?php printAllDates(); ?>
					<br />
					<?php if (extensionEnabled('zenpage') && getNumNews(true)) { ?>
						<h3><?php echo NEWS_LABEL; ?></h3>
						<?php printNewsArchive("archive"); ?>
						<br />
					<?php } ?>

					<h3><?php echo gettext('Popular Tags'); ?></h3>
					<div id="tag_cloud">

						<?php printAllTagsAs('cloud', 'tags'); ?>
					</div>

				</div>
			</div><!-- content left-->



			<div id="sidebar">
				<?php include("sidebar.php"); ?>
			</div><!-- sidebar -->

			<div id="footer">
				<?php include("footer.php"); ?>
			</div>
		</div><!-- content -->

	</div><!-- main -->
	<?php zp_apply_filter('theme_body_close'); ?>
</body>
</html>