<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>

		<?php
		zp_apply_filter('theme_head');
		if (getOption('effervescence_daily_album_image_effect')) {
			setOption('image_custom_images', getOption('effervescence_daily_album_image_effect'), false);
		}
		?>

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
						printSearchForm(NULL, 'search', $_zp_themeroot . '/images/search.png', gettext('Search gallery'));
					}
					printLogo();
					?>
				</div>
			</div> <!-- gallerytitle -->

			<!-- Crumb Trail Navigation -->
			<div id="wrapnav">
				<div id="navbar">
					<span><?php
						if ($_zp_gallery->getWebsiteURL())
							printHomeLink('', ' | ');
						printGalleryTitle();
						?></span>
				</div>
			</div> <!-- wrapnav -->

		</div> <!-- header -->
		<!-- The Image -->
		<?php
		$randomImage = getRandomImages($imageofday = getOption('effervescence_daily_album_image'));
		if ($randomImage) {
			makeImageCurrent($randomImage);
			$size = floor(getOption('image_size') * $imagereduction);
			$size_a = getSizeDefaultImage($size);
			$s = $size_a[0] + 22;
			$wide = " style=\"width:" . $s . "px;";
			$s = $size_a[1] + 72;
			$high = " height:" . $s . "px;\"";
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
		<div id="image" <?php echo $wide . $high; ?>>
			<div id="pic_day">
				<?php
				if ($randomImage) {
					?>
					<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>">
						<?php printCustomSizedImage(gettext('Visit the image gallery'), $size); ?>
					</a>
					<?php
				} else {
					echo '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/zen-logo.png" width="310" height="90" alt="' . gettext('There were no images from which to select the random heading.') . '" />';
				}
				?>
			</div>
			<?php
			if (!$zenpage) {
				?>
				<p align="center">
					<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo gettext('Visit the image gallery'); ?></a>
				</p>
				<?php
			}
			?>
		</div> <!-- image -->
		<br />
		<?php
		if ($zenpage) {
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