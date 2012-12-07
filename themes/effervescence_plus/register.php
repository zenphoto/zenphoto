<?php

// force UTF-8 Ø

if (!defined('WEBPATH') || !function_exists('printRegistrationForm')) die();

$enableRightClickOpen="true";

$backgroundImagePath="";
// End of config

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php printBareGalleryTitle(); ?> | <?php echo gettext("Register"); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	<?php effervescence_theme_head(); ?>
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/effervescence_plus/common.css" type="text/css" />
	<script type="text/javascript" src="<?php echo  $_zp_themeroot ?>/scripts/swfobject.js"></script>
</head>

<body onload="blurAnchors()">
<?php zp_apply_filter('theme_body_open'); ?>

<!-- Wrap Header -->
<div id="header">
	<div id="gallerytitle">

<!-- Logo -->
	<div id="logo">
	<?php printLogo(); ?>
	</div> <!-- logo -->
</div> <!-- gallerytitle -->

<!-- Crumb Trail Navigation -->

<div id="wrapnav">
	<div id="navbar">
		<span><?php printHomeLink('', ' | '); ?>
			<?php
			if (getOption('custom_index_page') === 'gallery') {
			?>
			<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home');?></a> |
			<?php
			}
			?>
		<a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>">
		<?php printGalleryTitle();	?></a></span> |
		<?php
			echo "<em>".gettext('Register')."</em>";
		?>
	</div>
</div> <!-- wrapnav -->

</div> <!-- header -->

<!-- Wrap Subalbums -->
<div id="subcontent">
	<div id="submain">

		<h2><?php echo gettext('User Registration') ?></h2>
		<?php  printRegistrationForm();  ?>
	</div>
</div>


<!-- Footer -->
<div class="footlinks">

<?php printThemeInfo(); ?>
<?php printZenphotoLink(); ?>

</div> <!-- footerlinks -->


<?php
printAdminToolbox();
zp_apply_filter('theme_body_close');
?>

</body>
</html>