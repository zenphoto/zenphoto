<?php if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<head>
<?php zp_apply_filter('theme_head'); ?>
	<title><?php echo getBareGalleryTitle(); ?> | <?php echo gettext("Password required"); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo getOption('charset'); ?>" />
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
</head>

<body>
<?php zp_apply_filter('theme_body_open'); ?>

	<div id="main">

<?php include("header.php"); ?>

		<div id="content">
			<div id="breadcrumb">
				<h2>
					<?php if (extensionEnabled('zenpage')) { ?>
						<a href="<?php echo getGalleryIndexURL(); ?>" title="<?php gettext('Index'); ?>"><?php echo gettext("Index"); ?></a>»
<?php } ?>
					<a href="<?php echo htmlspecialchars(getCustomPageURl('gallery')); ?>" title="<?php echo gettext('Gallery'); ?>"><?php echo gettext("Gallery") . " » "; ?></a>
					<strong><?php echo gettext("A password is required for the page you requested"); ?></strong>
				</h2>
			</div>

			<div id="content-error">

				<div class="errorbox">
<?php printPasswordForm($hint, $show); ?>
				</div>

				<?php
				if (!zp_loggedin() && function_exists('printRegistrationForm') && getOption('gallery_page_unprotected_register')) {
					printCustomPageURL(gettext('Register for this site'), 'register', '', '<br />');
					echo '<br />';
				}
				?>
			</div>


			<div id="footer">
<?php include("footer.php"); ?>
			</div>



		</div><!-- content -->

	</div><!-- main -->
<?php zp_apply_filter('theme_body_close'); ?>
</body>
</html>
