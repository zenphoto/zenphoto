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
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" />
	<?php jqm_loadScripts(); ?>
</head>

<body>
<?php zp_apply_filter('theme_body_open'); ?>

<div data-role="page" id="mainpage">

<?php jqm_printMainHeaderNav(); ?>

<div data-role="content">	
	<div class="content-primary">
		<h2><a href="<?php echo getGalleryIndexURL(); ?>">Index</a>» <strong><?php echo gettext("Object not found"); ?></strong></h2>

		<div id="content-error">
		<div class="errorbox">
 		<?php
		echo gettext("The Zenphoto object you are requesting cannot be found.");
		if (isset($album)) {
			echo '<br />'.gettext("Album").': '.sanitize($album);
		}
		if (isset($image)) {
			echo '<br />'.gettext("Image").': '.sanitize($image);
		}
		if (isset($obj)) {
			echo '<br />'.gettext("Theme page").': '.substr(basename($obj),0,-4);
		}
 		?>
		</div>
		</div>
	
	</div>
		 <div class="content-secondary">
			<?php jqm_printMenusLinks(); ?>
 		</div>
</div><!-- /content -->
<?php jqm_printBacktoTopLink(); ?>
<?php jqm_printFooterNav(); ?>
</div><!-- /page -->

<?php zp_apply_filter('theme_body_close');
?>
</body>
</html>
