<!DOCTYPE html>
<head>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/slideshow.css" type="text/css" />
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php echo getBareGalleryTitle(); ?> <?php echo gettext("Slideshow"); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo getOption('charset'); ?>" />

</head>
<body>
	<?php zp_apply_filter('theme_body_open'); ?>
	<div id="slideshowpage">
		<?php printSlideShow(true, true); ?>
	</div>
	<?php zp_apply_filter('theme_body_close'); ?>
</body>
</html>