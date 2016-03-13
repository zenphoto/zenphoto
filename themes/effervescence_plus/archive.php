<?php
if (!defined('WEBPATH'))
	die();
// force UTF-8 Ø
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="<?php echo LOCAL_CHARSET; ?>">
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
	</head>

	<body onload="blurAnchors()">
		<?php zp_apply_filter('theme_body_open'); ?>

		<!-- Wrap Header -->
		<div id="header">
			<div id="gallerytitle">

				<!-- Logo -->
				<div id="logo">
					<?php printLogo(); ?>
				</div>
			</div> <!-- gallerytitle -->

			<!-- Crumb Trail Navigation -->
			<div id="wrapnav">
				<div id="navbar">
					<span><?php printHomeLink('', ' | '); printGalleryIndexURL(' | ', getGalleryTitle()); ?></span>  | <?php echo gettext('Archive View'); ?>
				</div>
			</div> <!-- wrapnav -->

			<!-- Random Image -->
			<?php printHeadingImage(getRandomImages(getThemeOption('effervescence_daily_album_image'))); ?>
		</div> <!-- header -->

		<!-- Wrap Main Body -->
		<div id="content">

			<small>&nbsp;</small>
			<div id="main2">
				<?php
				if ($zenpage = extensionEnabled('zenpage')) {
					?>
					<div id="content-left">
						<?php
					}
					?>
					<!-- Date List -->
					<div id="archive">
						<p><?php echo gettext('Images By Date'); ?></p>
						<?php printAllDates('archive', 'year', 'month', 'desc'); ?>
						<?php
						if (function_exists("printNewsArchive")) {
							?>
							<p><?php echo(gettext('News archive')); ?></p><?php printNewsArchive("archive"); ?>
							<?php
						}
						?>
					</div>
					<div id="tag_cloud"><p><?php echo gettext('Popular Tags'); ?></p><?php printAllTagsAs('cloud', 'tags'); ?></div>
					<br style="clear:both" />
					<?php
					if ($zenpage) {
						?>
					</div><!-- content left-->
					<div id="sidebar">
						<?php include("sidebar.php"); ?>
					</div><!-- sidebar -->
					<?php
				}
				?>
				<br style="clear:both" />
			</div> <!-- main2 -->

		</div> <!-- content -->

		<?php
		printFooter();
		zp_apply_filter('theme_body_close');
		?>

	</body>
</html>