<?php

// force UTF-8 Ø

if (!defined('WEBPATH')) die(); $themeResult = getTheme($zenCSS, $themeColor, 'light');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php echo getBareGalleryTitle(); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	<?php printRSSHeaderLink('Gallery',gettext('Gallery RSS')); ?>
</head>

<body>
<?php zp_apply_filter('theme_body_open'); ?>

<div id="main">

	<div id="gallerytitle">
		<?php if (getOption('Allow_search')) {  printSearchForm(''); } ?>
		<h2><?php printHomeLink('', ' | '); echo getGalleryTitle(); ?></h2>
	</div>

		<div id="padbox">
		<?php printGalleryDesc(); ?>
		<div id="albums">
			<?php while (next_album()): ?>
			<div class="album">
						<div class="thumb">
					<a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo gettext('View album:'); ?> <?php echo getAnnotatedAlbumTitle();?>"><?php printAlbumThumbImage(getAnnotatedAlbumTitle()); ?></a>
 						 </div>
						<div class="albumdesc">
					<h3><a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo gettext('View album:'); ?> <?php echo getAnnotatedAlbumTitle();?>"><?php printAlbumTitle(); ?></a></h3>
 							<small><?php printAlbumDate(""); ?></small>
					<p><?php printAlbumDesc(); ?></p>
				</div>
				<p style="clear: both; "></p>
			</div>
			<?php endwhile; ?>
		</div>
		<br clear="all" />
		<?php printPageListWithNav("&laquo; ".gettext("prev"), gettext("next")." &raquo;"); ?>
	</div>

</div>
<?php if (function_exists('printLanguageSelector')) { printLanguageSelector(); } ?>

<div id="credit">
<?php
if (function_exists('printUserLogin_out')) {
	printUserLogin_out('', ' | ');
}
?>
<?php printRSSLink('Gallery','','RSS', ' | '); ?>
<?php printCustomPageURL(gettext("Archive View"),"archive"); ?> |

<?php	if (getOption('zp_plugin_contact_form')) {
	printCustomPageURL(gettext('Contact us'), 'contact', '', '', ' | ');
}
?>

<?php
if (!zp_loggedin() && function_exists('printRegistrationForm')) {
	printCustomPageURL(gettext('Register for this site'), 'register', '', '', ' | ');
}
?>
<?php printZenphotoLink(); ?>
</div>

<?php
printAdminToolbox();
zp_apply_filter('theme_body_close');
?>

</body>
</html>
