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
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
</head>

<body>
<?php zp_apply_filter('theme_body_open'); ?>

<div id="main">

	<div id="header">
		<h1><?php printGalleryTitle();?></h1>
		</div>

		<div id="content">
		<div id="breadcrumb">
	<h2><a href="<?php echo getGalleryIndexURL(); ?>">Index</a> » <strong><?php echo gettext("A password is required for the page you requested"); ?></strong></h2>
	</div>

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


<div id="footer">
	<?php include("footer.php"); ?>
</div>



</div><!-- content -->

</div><!-- main -->
<?php
zp_apply_filter('theme_body_close');
?>
</body>
</html>
