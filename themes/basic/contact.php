<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
if (function_exists('printContactForm')) {
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta charset="<?php echo LOCAL_CHARSET; ?>">
			<?php zp_apply_filter('theme_head'); ?>
			<?php printHeadTitle(); ?>
			<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
			<link rel="stylesheet" href="<?php echo pathurlencode(dirname(dirname($zenCSS))); ?>/common.css" type="text/css" />
		</head>
		<body>
			<?php zp_apply_filter('theme_body_open'); ?>
			<div id="main">
				<div id="gallerytitle">
					<?php
					if (getOption('Allow_search')) {
						printSearchForm();
					}
					?>
					<h2>
						<?php printHomeLink('', ' | '); printGalleryIndexURL(' | ', getGalleryTitle()); ?>
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
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>