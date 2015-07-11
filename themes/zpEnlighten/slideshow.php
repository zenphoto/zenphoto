<!DOCTYPE html>
<head>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/slideshow.css" type="text/css" />
	<?php zp_apply_filter('theme_head'); ?>

</head>
<body>
	<?php zp_apply_filter('theme_body_open'); ?>
	<div id="slideshowpage">
		<?php printSlideShow(true, true); ?>
	</div>
	<?php zp_apply_filter('theme_body_close'); ?>
</body>
</html>