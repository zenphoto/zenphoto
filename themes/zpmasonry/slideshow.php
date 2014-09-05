<?php
/**
 * Slideshow page for gslideshow
 * Also covers for core slideshow script when gslideshow is deactivated.
 */
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die(); // are we in the Zenphoto environment? if not, kill application.
?>

<?php if (function_exists('printGslideshow')) { ?>

	<!DOCTYPE html>
	<html>
		<head>
			<?php zp_apply_filter('theme_head'); ?>
			<meta name="viewport" content="width=device-width" />
		</head>
		<body>
			<?php zp_apply_filter('theme_body_open'); ?>
			<?php printGslideshow(); ?>
			<?php zp_apply_filter('theme_body_close'); ?>
		</body>
	</html>

<?php } else { ?>

	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<?php zp_apply_filter('theme_head'); ?>
			<link rel="stylesheet" href="<?php echo WEBPATH; ?>/themes/zenpage/slideshow.css" type="text/css" />
			<?php printSlideShowJS(); ?>

		</head>
		<body>
			<?php zp_apply_filter('theme_body_open'); ?>
			<div id="slideshowpage">
				<?php printSlideShow(true, true); ?>
			</div>
			<?php zp_apply_filter('theme_body_close'); ?>

		</body>
	</html>

<?php } ?>