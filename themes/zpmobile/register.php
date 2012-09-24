<?php
// force UTF-8 Ø
if (!defined('WEBPATH') || !function_exists('printRegistrationForm')) die();
?>
<!DOCTYPE html>
<html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php echo getBareGalleryTitle(); ?> | <?php echo gettext("Object not found"); ?></title>
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
		<h2><?php echo gettext('User Registration') ?></h2>

		<?php  printRegistrationForm();  ?>

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
