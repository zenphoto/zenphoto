<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
if (function_exists('printContactForm')) {
	$enableRightClickOpen = "true";

	$backgroundImagePath = "";
// End of config
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<?php zp_apply_filter('theme_head'); ?>
			<?php printHeadTitle(); ?>
			<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		</head>

		<body onload="blurAnchors()">
			<?php zp_apply_filter('theme_body_open'); ?>

			<!-- Wrap Header -->
			<div id="header">
				<div id="gallerytitle">

					<!-- Logo -->
					<div id="logo">
						<?php
						printLogo();
						?>
					</div> <!-- logo -->
				</div> <!-- gallerytitle -->

				<!-- Crumb Trail Navigation -->

				<div id="wrapnav">
					<div id="navbar">
						<span><?php printHomeLink('', ' | '); ?>
							<?php
							if (getOption('custom_index_page') === 'gallery') {
								?>
								<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home'); ?></a> |
								<?php
							}
							?>
							<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>">
								<?php printGalleryTitle(); ?></a></span> |
								<?php
						echo "<em>" . gettext('Contact') . "</em>";
						?>
					</div>
				</div> <!-- wrapnav -->

			</div> <!-- header -->

			<!-- Wrap Subalbums -->
			<div id="subcontent">
				<div id="submain">
					<h3><?php echo gettext('Contact us.') ?></h3>

					<?php printContactForm(); ?>
				</div>
			</div>


			<!-- Footer -->
			<div class="footlinks">

				<?php printThemeInfo(); ?>
				<?php printZenphotoLink(); ?>

			</div> <!-- footerlinks -->


			<?php
			zp_apply_filter('theme_body_close');
			?>

		</body>
	</html>
	<?php
} else {
	include(dirname(__FILE__) . '/404.php');
}
?>