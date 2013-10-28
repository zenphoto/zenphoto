<?php

// force UTF-8 Ø
if (!defined('WEBPATH') || !class_exists('Zenpage')) die();

?>
<!DOCTYPE html>
<html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<?php printHeadTitle(); ?>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<?php if (class_exists('RSS')) printRSSHeaderLink("Pages","Zenpage news", ""); ?>
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