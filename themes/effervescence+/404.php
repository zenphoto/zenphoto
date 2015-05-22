<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>

		<?php zp_apply_filter('theme_head'); ?>

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
						<?php
						printHomeLink('', ' | ');
						if (getOption('gallery_index')) {
							?>
							<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Main Index'); ?>"><?php printGalleryTitle(); ?></a>
							<?php
						} else {
							?>
							<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle(); ?></a>
							<?php
						}
						?>
						</a></span>  |
					<?php echo "<em>" . gettext('Page not found') . "</em>"; ?>
				</div>
			</div>

		</div>

		<!-- Wrap Main Body -->
		<div id="content">
			<small>&nbsp;</small>
			<div id="main">
				<?php
				print404status();
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