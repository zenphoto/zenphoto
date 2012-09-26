<?php

// force UTF-8 Ø

if (!defined('WEBPATH') || !function_exists('printContactForm')) die();
require_once('normalizer.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php echo getBareGalleryTitle(); ?> <?php echo gettext("Contact form"); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" type="text/css" media="screen, projection" href="<?php echo $_zp_themeroot ?>/css/master.css" />
	<?php
	printRSSHeaderLink('Gallery',gettext('Gallery RSS'));
	setOption('thumb_crop_width', 85, false);
	setOption('thumb_crop_height', 85, false);
	?>
</head>

<body class="archive">
	<?php zp_apply_filter('theme_body_open'); ?>
	<?php echo getGalleryTitle(); ?>
	<div id="content">
		<h1><?php printGalleryTitle(); ?> <em><?php echo gettext('Contact'); ?></em></h1>
		<div class="galleries">
		<h2><?php echo gettext('Contact us.') ?></h2>
		<?php  printContactForm();  ?>
	</div>
</div>

<p id="path">
	<?php printHomeLink('', ' &gt; '); ?>
	<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home');?></a> &gt;
	<?php echo getGalleryTitle();?>
	&gt; <em><?php echo gettext('Contact'); ?></em>
</p>

<div id="footer">
	<p>
		<?php echo gettext('<a href="http://stopdesign.com/templates/photos/">Photo Templates</a> from Stopdesign');?>.
		<?php printZenphotoLink(); ?>
	</p>
</div>

<?php
printAdminToolbox();
zp_apply_filter('theme_body_close');
?>

</body>
</html>
