<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
zp_apply_filter('theme_file_top');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
<?php include("inc-head.php"); ?>
	</head>
	<body>
<?php include("inc-header.php"); ?>
		<div id="content-error">
			<div class="errorbox">
				<?php
				echo gettext("The object you are requesting cannot be found.");
				if (isset($album)) {
					echo '<br />' . gettext("Album") . ': ' . html_encode($album);
				}
				if (isset($image)) {
					echo '<br />' . gettext("Image") . ': ' . html_encode($image);
				}
				if (isset($obj)) {
					echo '<br />' . gettext("Theme page") . ': ' . html_encode(substr(basename($obj), 0, -4));
				}
				?>
			</div>
		</div>
<?php include("inc-footer.php"); ?>
	</body>
</html>
<?php
zp_apply_filter('theme_file_end');
?>
