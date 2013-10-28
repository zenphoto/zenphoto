<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
if (function_exists('printContactForm')) {
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<?php zp_apply_filter('theme_head'); ?>
			<?php printHeadTitle(); ?>
			<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
			<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
			<link rel="stylesheet" href="<?php echo WEBPATH . '/' . THEMEFOLDER; ?>/default/common.css" type="text/css" />
		</head>
		<body>
			<?php zp_apply_filter('theme_body_open'); ?>
			<div id="main">
				<div id="gallerytitle">
					<h2>
						<?php printHomeLink('', ' | '); ?>
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo gettext('Gallery Index'); ?></a> |
						<em><?php echo gettext('Contact us'); ?></em>
					</h2>
				</div>
				<h3><?php echo gettext('Contact us.') ?></h3>
				<?php printContactForm(); ?>
			</div>
			<?php @call_user_func('printLanguageSelector'); ?>
			<div id="credit">
				<?php printZenphotoLink(); ?>
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