<?php
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
</head>
<body>
	<?php zp_apply_filter('theme_body_open'); ?>

	<div id="main">

		<?php include("header.php"); ?>

		<div id="content">

			<div id="breadcrumb">
				<h2>
					<?php if (extensionEnabled('zenpage')) { ?>
						<a href="<?php echo getGalleryIndexURL(); ?>" title="<?php gettext('Index'); ?>"><?php echo gettext("Index"); ?></a>»
					<?php } ?>
					<a href="<?php echo htmlspecialchars(getCustomPageURl('gallery')); ?>" title="<?php echo gettext('Gallery'); ?>"><?php echo gettext("Gallery"); ?></a>
				</h2>
			</div>

			<div id="content-left">
				<h1><?php echo gettext('User Registration') ?></h1>
				<?php printRegistrationForm(); ?>
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