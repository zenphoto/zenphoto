<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
if (function_exists('printSlideShow')) {
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta charset="<?php echo LOCAL_CHARSET; ?>">
			<?php zp_apply_filter('theme_head'); ?>
			<?php printHeadTitle(); ?>
			<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css">
			<link rel="stylesheet" href="<?php echo pathurlencode(dirname(dirname($zenCSS))); ?>/common.css" type="text/css" />
		</head>

		<body>
			<?php zp_apply_filter('theme_body_open'); 
				switch(getOption('Theme_colors')) {
					case 'light':
					case 'sterile-light':
						$class = 'slideshow_light';
						break;
					case 'dark':
					case 'sterile-dark':
						default:
						$class = 'slideshow_dark';
						break;
				}
			?>
			<div id="slideshowpage" class="<?php echo $class; ?>">
				<?php
				printSlideShow(true, true);
				?>
			</div>
			<?php zp_apply_filter('theme_body_close'); ?>
		</body>
	</html>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>