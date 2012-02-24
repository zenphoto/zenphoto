<?php
if (!defined('WEBPATH')) die();
$themeResult = getTheme($zenCSS, $themeColor, 'kish-my father');
// force UTF-8 Ø
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php echo getBareGalleryTitle(); ?> | <?php echo gettext('Archive'); if ($_zp_page>1) echo "[$_zp_page]"; ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<?php effervescence_theme_head(); ?>
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/effervescence_plus/common.css" type="text/css" />
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
				<a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo getGalleryTitle();?></a></span>  | <?php echo gettext('Archive View'); ?>
			</div>
		</div> <!-- wrapnav -->

		<!-- Random Image -->
		<?php printHeadingImage(getRandomImages(getThemeOption('effervescence_daily_album_image'))); ?>
	</div> <!-- header -->

	<!-- Wrap Main Body -->
	<div id="content">

		<small>&nbsp;</small>
		<div id="main2">
		<?php
		if ($zenpage = getOption('zp_plugin_zenpage')) {
		?>
			<div id="content-left">
			<?php
		}
		?>
			<!-- Date List -->
			<div id="archive">
				<p><?php echo gettext('Images By Date'); ?></p>
				<?php printAllDates('archive', 'year', 'month', 'desc'); ?>
					<?php
					if(function_exists("printNewsArchive")) {
						?>
						<p><?php echo(gettext('News archive')); ?></p><?php printNewsArchive("archive");	?>
						<?php
					}
					?>
			</div>
			<div id="tag_cloud"><p><?php echo gettext('Popular Tags'); ?></p><?php printAllTagsAs('cloud', 'tags'); ?></div>
			<br style="clear:both" />
			<?php
			if ($zenpage = getOption('zp_plugin_zenpage')) {
				?>
				</div><!-- content left-->
				<div id="sidebar">
				<?php include("sidebar.php"); ?>
				</div><!-- sidebar -->
				<?php
			}
			?>
		<br style="clear:both" />
		</div> <!-- main2 -->

	</div> <!-- content -->

<?php
printFooter();
zp_apply_filter('theme_body_close');
?>

</body>
</html>