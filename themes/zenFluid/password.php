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
		<?php include ('inc-header.php'); ?>

		<div class="title border colour" <?php echo $titleStyle;?>>
			<h3><?php echo gettext('Password required'); ?></h3>
		</div>

		<div style='display: none;'>
			<?php printPasswordForm(isset($hint) ? $hint : NULL, false, true, NULL); ?>
		</div>
		<script type="text/javascript">
			//<![CDATA[
			$(document).ready(function () {
				$.colorbox({
					inline: true,
					href: "#passwordform",
					innerWidth: "400px",
					close: '<?php echo gettext("close"); ?>',
					open: true
				});
			});
			//]]>
		</script>

		<?php include('inc-footer.php'); ?>
	</body>
</html>
<?php
zp_apply_filter('theme_file_end');
?>