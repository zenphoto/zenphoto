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
</head>

<body>
<?php zp_apply_filter('theme_body_open'); ?>

	<!-- Wrap Header -->
	<div id="header">
		<div id="gallerytitle">

		<!-- Logo -->
			<div id="logo">
			<?php printLogo(); ?>
			</div>
		</div>

		<!-- Crumb Trail Navigation -->
		<div id="wrapnav">
			<div id="navbar">
				<?php printHomeLink('', ' | '); ?>
			<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Index'); ?>"><?php printGalleryTitle();?></a></span>  |
				<?php echo gettext('A password is required for the page you requested'); ?>
			</div>
		</div>

	</div>

	<!-- Wrap Main Body -->
	<div id="content">
		<small>&nbsp;</small>
		<div id="main">
		<?php printPasswordForm($hint, $show, false); ?>
		</div>
	</div>

<?php
printFooter(false);
zp_apply_filter('theme_body_close');
?>

</body>
</html>