<?php
if (!defined('WEBPATH')) die();
// force UTF-8 Ø
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php printBareGalleryTitle(); ?> | <?php echo gettext('Object not found'); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/effervescence_plus/common.css" type="text/css" />
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
				<span><?php printHomeLink('', ' | '); ?>
				<?php
			if (getOption('custom_index_page') === 'gallery') {
				?>
				<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home');?></a> |
				<?php
				}
				?>
				<a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle();?></a></span>  |
				<?php echo gettext('Object not found'); ?>
			</div>
		</div>

	</div>

	<!-- Wrap Main Body -->
	<div id="content">
		<small>&nbsp;</small>
		<div id="main">
		<?php
		echo gettext("The Zenphoto object you are requesting cannot be found.");
		if (isset($album)) {
			echo '<br />'.sprintf(gettext('Album: %s'),html_encode($album));
		}
		if (isset($image)) {
			echo '<br />'.sprintf(gettext('Image: %s'),html_encode($image));
		}
		if (isset($obj)) {
			echo '<br />'.sprintf(gettext('Page: %s'),html_encode(substr(basename($obj),0,-4)));
		}
		?>
		</div>
	</div>

	<!-- Footer -->
	<div class="footlinks">
		<small><?php printThemeInfo(); ?></small>
		<?php printZenphotoLink(); ?>
		<br />
	</div>

<?php printAdminToolbox(); ?>

</body>
</html>