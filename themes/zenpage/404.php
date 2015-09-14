<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="<?php echo LOCAL_CHARSET; ?>">
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
	</head>

	<body>
		<?php zp_apply_filter('theme_body_open'); ?>

		<div id="main">

			<div id="header">
				<h1 class="title">
					<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a>
				</h1>
				<?php
				if (getOption('Allow_search')) {
					printSearchForm("", "search", "", gettext("Search"));
				}
				?>
			</div>

			<div id="content">
				<div id="breadcrumb">
					<h2><?php printGalleryIndexURL(); ?></h2>
				</div>

				<div id="content-error">

					<div class="errorbox">
						<?php print404status(isset($album) ? $album : NULL, isset($image) ? $image : NULL, $obj); ?>
					</div>

				</div>



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
