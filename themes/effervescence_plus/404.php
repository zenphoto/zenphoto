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
	</head>

	<body>
		<?php zp_apply_filter('theme_body_open'); ?>

		<!-- Wrap Header -->
		<div id="header">
			<div id="gallerytitle">

				<!-- Logo -->
				<div id="logo">
					<?php printLogo(); ?>
				</div>
			</div>

			<!-- Crumb Trail Navigation -->
			<div id="wrapnav">
				<div id="navbar">
					<span>
						<?php printHomeLink('', ' | '); printGalleryIndexURL(' | ', getGalleryTitle()); ?>
					</span>  |
					<?php echo "<em>" . gettext('Page not found') . "</em>"; ?>
				</div>
			</div>

		</div>

		<!-- Wrap Main Body -->
		<div id="content">
			<small>&nbsp;</small>
			<div id="main">
				<?php
				print404status(isset($album) ? $album : NULL, isset($image) ? $image : NULL, $obj);
				?>
			</div>
		</div>

		<!-- Footer -->
		<div class="footlinks">
			<small><?php printThemeInfo(); ?></small>
			<?php printZenphotoLink(); ?>
			<br />
		</div>

		<?php
		zp_apply_filter('theme_body_close');
		?>

	</body>
</html>