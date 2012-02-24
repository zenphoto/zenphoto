<?php

// force UTF-8 Ø
if (!defined('WEBPATH')) die();
$themeResult = getTheme($zenCSS, $themeColor, 'kish-my father');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<?php
	if (getOption('effervescence_daily_album_image_effect')) {
		setOption('image_custom_images', getOption('effervescence_daily_album_image_effect'), false);
	}
	?>
	<title><?php $mainsite = getMainSiteName(); echo (empty($mainsite))?gettext("Zenphoto gallery"):$mainsite; if ($_zp_page>1) echo "[$_zp_page]"; ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/effervescence_plus/common.css" type="text/css" />
	<?php effervescence_theme_head(); ?>
</head>

<body onload="blurAnchors()">
<?php zp_apply_filter('theme_body_open'); ?>

	<!-- Wrap Header -->
	<div id="header">
		<div id="gallerytitle">

		<!-- Logo -->
			<div id="logo">
				<?php
				if (getOption('Allow_search')) {
					printSearchForm(NULL,'search',$_zp_themeroot.'/images/search.png',gettext('Search gallery'));
				}
				printLogo();
				?>
			</div>
		</div> <!-- gallerytitle -->

		<!-- Crumb Trail Navigation -->
		<div id="wrapnav">
			<div id="navbar">
				<span><?php
				if ($_zp_gallery->getWebsiteURL()) printHomeLink('', ' | ');
				echo gettext('Home');
				?></span>
			</div>
		</div> <!-- wrapnav -->

	</div> <!-- header -->
		<!-- The Image -->
		<?php
		$randomImage = getRandomImages($imageofday = getThemeOption('effervescence_daily_album_image'));
		if ($randomImage) {
			makeImageCurrent($randomImage);
	 		$size = floor(getOption('image_size') * $imagereduction);
			$s = getDefaultWidth($size) + 22;
			$wide = " style=\"width:".$s."px;";
			$s = getDefaultHeight($size) + 72;
			$high = " height:".$s."px;\"";
		} else {
			$wide = " style=\"width:332px;";
			$high = " height:162px;\"";
		}
		if ($imageofday) {
			?>
			<p align="center">
			<?php echo gettext('Picture of the day'); ?>
			</p>
			<?php
		}
		?>
		<div id="image" <?php echo $wide.$high; ?>>
			<div id="pic_day">
			<?php
				if ($randomImage) {
					?>
					<a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>">
						<?php printCustomSizedImage(gettext('Visit the image gallery'), $size); ?>
					</a>
					<?php
				} else {
					echo '<img src="'.$_zp_themeroot.'/images/zen-logo.jpg" width="310" height="90" alt="'.gettext('There were no images from which to select the random heading.').'" />';
				}
				?>
			</div>
			<?php
			if (!$zenpage) {
				?>
				<p align="center">
				<a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo gettext('Visit the image gallery');?></a>
				</p>
				<?php
			}
			?>
		</div> <!-- image -->
		<br />
	<?php
	if($zenpage)  {
	?>
	<!-- Wrap Main Body -->
	<div id="content">

		<small>&nbsp;</small>
		<div id="main2">
			<div id="content-left">
			<?php commonNewsLoop(false); ?>
			</div><!-- content left-->

			<div id="sidebar">
			<?php include("sidebar.php"); ?>
			</div><!-- sidebar -->
			<br style="clear:both" />
		</div> <!-- main2 -->

	</div> <!-- content -->
	<?php
	}
	?>
<div class="aligncenter2">
<?php printGalleryDesc(); ?>
</div>

<?php
printFooter();
zp_apply_filter('theme_body_close');
?>

</body>
</html>