<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<meta charset="<?php echo LOCAL_CHARSET; ?>">
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
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
						if (getOption('custom_index_page') === 'gallery') {
							?>
							<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home'); ?></a> |
							<?php
						} else {
							?>
							<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle(); ?></a> |
							<?php
						}
						printGalleryTitle();
						?>
						</a></span>  |
					<?php
					print404status(isset($album) ? $album : NULL, isset($image) ? $image : NULL, $obj);
					?>
				</div>
			</div>

		</div>

		<!-- Wrap Main Body -->
		<div id="content">
			<small>&nbsp;</small>
			<div id="main">
				<?php
				echo gettext("The Zenphoto object you are requesting cannot be found.");
				if (isset($album)) {
					echo '<br />' . sprintf(gettext('Album: %s'), html_encode($album));
				}
				if (isset($image)) {
					echo '<br />' . sprintf(gettext('Image: %s'), html_encode($image));
				}
				if (isset($obj)) {
					echo '<br />' . sprintf(gettext('Page: %s'), html_encode(substr(basename($obj), 0, -4)));
				}
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