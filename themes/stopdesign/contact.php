<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
if (function_exists('printContactForm')) {
	require_once('normalizer.php');
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<?php zp_apply_filter('theme_head'); ?>
			<?php printHeadTitle(); ?>
			<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
			<link rel="stylesheet" type="text/css" media="screen, projection" href="<?php echo $_zp_themeroot ?>/css/master.css" />
			<?php
			if (class_exists('RSS'))
				printRSSHeaderLink('Gallery', gettext('Gallery RSS'));
			setOption('thumb_crop_width', 85, false);
			setOption('thumb_crop_height', 85, false);
			?>
		</head>

		<body class="archive">
			<?php zp_apply_filter('theme_body_open'); ?>
			<?php printGalleryTitle(); ?>
			<div id="content">
				<h1><?php printGalleryTitle(); ?> <em><?php echo gettext('Contact'); ?></em></h1>
				<div class="galleries">
					<h2><?php echo gettext('Contact us.') ?></h2>
					<?php printContactForm(); ?>
				</div>
			</div>

			<p id="path">
				<?php printHomeLink('', ' > '); ?>
				<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home'); ?></a> &gt;
				<?php printGalleryTitle(); ?>
				&gt; <em><?php echo gettext('Contact'); ?></em>
			</p>

			<div id="footer">
				<p>
					<?php echo gettext('<a href="http://stopdesign.com/templates/photos/">Photo Templates</a> from Stopdesign'); ?>.
					<?php printZenphotoLink(); ?>
				</p>
			</div>

			<?php
			zp_apply_filter('theme_body_close');
			?>

		</body>
	</html>
	<?php
} else {
	include(dirname(__FILE__) . '/404.php');
}
?>