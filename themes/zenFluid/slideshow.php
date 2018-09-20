<?php
// force UTF-8 Ø
if (!defined('WEBPATH')) die();
zp_apply_filter('theme_file_top');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php include("inc-head.php");?>
		<meta name="viewport" content="width=device-width" />
	</head>
	<body>
		<?php 
		zp_remove_filter('theme_body_open', 'printLikeButtonJS');
		zp_apply_filter('theme_body_open');
		if (function_exists('printGslideshow')) {
			printGslideshow();
		} else {
			?>
			<div id="slideshowpage">
				<?php printSlideShow(true,true); ?>
			</div>
			<?php 
		}
		?>
	</body>
</html>
<?php
zp_apply_filter('theme_file_end');
?>