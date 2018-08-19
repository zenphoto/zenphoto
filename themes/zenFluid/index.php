<?php
// force UTF-8 Ø
if (!defined('WEBPATH')) die();
zp_apply_filter('theme_file_top');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php include("inc-head.php");?>
	</head>
	<body>
		<?php include("inc-header.php");?>
		<div class="image" <?php echo $imageStyle;?>>
			<?php
			$imageWidth = (getOption('zenfluid_stageimage')) ? $stageWidth : 0;
			echo ImageJS(0, $imageWidth);
			printHomepageImage(getOption('zenfluid_imageroot'),getOption('zenfluid_randomimage'),$titleStyle,$imageStyle);
			?>
		</div>
		<?php include("inc-footer.php");?>
	</body>
</html>
<?php
zp_apply_filter('theme_file_end');
?>