<?php

// force UTF-8 Ø
if (!defined('WEBPATH') || !class_exists('Zenpage')) die();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php printBareGalleryTitle(); ?> | <?php echo gettext('Pages'); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/effervescence_plus/common.css" type="text/css" />
	<?php effervescence_theme_head(); ?>
	<?php printZenpageRSSHeaderLink("Pages","", "Zenpage news", ""); ?>
</head>

<body onload="blurAnchors()">
<?php zp_apply_filter('theme_body_open'); ?>

	<!-- Wrap Header -->
	<div id="header">
		<div id="gallerytitle">

		<!-- Logo -->
			<div id="logo">
			<?php printLogo(); ?>
			</div>
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
				<a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle();?></a></span>
								<?php printZenpageItemsBreadcrumb(" | ", ""); ?><?php printPageTitle(" | "); ?>
			</div>
		</div> <!-- wrapnav -->

		<!-- Random Image -->
		<?php printHeadingImage(getRandomImages(getThemeOption('effervescence_daily_album_image'))); ?>
	</div> <!-- header -->

	<!-- Wrap Main Body -->
	<div id="content">

		<small>&nbsp;</small>
		<div id="main2">
			<div id="content-left">
	<h2><?php printPageTitle(); ?></h2>
	<div id="pagetext">
	<?php printCodeblock(1); ?>
	<?php printPageContent(); ?>
	<?php printCodeblock(2); ?>
	</div>

	<?php
	@call_user_func('printRating');
	commonComment();
	?>

			</div><!-- content left-->
			<div id="sidebar">
			<?php include("sidebar.php"); ?>
			</div><!-- sidebar -->
			<br style="clear:both" />
		</div> <!-- main2 -->

	</div> <!-- content -->

<?php
printFooter();
zp_apply_filter('theme_body_close');
?>

</body>
</html>