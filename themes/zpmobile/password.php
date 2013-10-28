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
		<h2><a href="<?php echo getGalleryIndexURL(); ?>">Index</a> » <strong><strong><?php echo gettext("A password is required for the page you requested"); ?></strong></strong></h2>

		<div id="content-error">
			<div class="errorbox">
 				<?php printPasswordForm($hint, $show); ?>
			</div>
			<?php
	if (!zp_loggedin() && function_exists('printRegistrationForm') && $_zp_gallery->isUnprotectedPage('register')) {
		printCustomPageURL(gettext('Register for this site'), 'register', '', '<br />');
		echo '<br />';
	}
	?>
		</div>
	
	</div>
		 
</div><!-- /content -->
<?php jqm_printBacktoTopLink(); ?>
<?php jqm_printFooterNav(); ?>
</div><!-- /page -->

<?php zp_apply_filter('theme_body_close');
?>
</body>
</html>
