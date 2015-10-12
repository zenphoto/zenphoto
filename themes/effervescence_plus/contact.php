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
						<?php
						printLogo();
						?>
					</div> <!-- logo -->
				</div> <!-- gallerytitle -->

				<!-- Crumb Trail Navigation -->

				<div id="wrapnav">
					<div id="navbar">
						<span><?php printHomeLink('', ' | '); printGalleryIndexURL(' | ', getGalleryTitle());
							?></span> |
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
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>