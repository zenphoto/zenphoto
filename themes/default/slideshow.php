<?php
// force UTF-8 Ø
if (!defined('WEBPATH')) die();
?>
<!DOCTYPE html>
<html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<?php printHeadTitle(); ?>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
</head>

<body>
<?php zp_apply_filter('theme_body_open'); ?>
	<div id="slideshowpage">
		<?php
		printSlideShow(true,true);
		?>
	</div>
<?php zp_apply_filter('theme_body_close'); ?>
</body>
</html>