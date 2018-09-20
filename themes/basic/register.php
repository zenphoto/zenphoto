<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
if (function_exists('printRegistrationForm')) {
	?>
	<!DOCTYPE html>
	<html>
		<head>

			<?php zp_apply_filter('theme_head'); ?>

			<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
			<link rel="stylesheet" href="<?php echo pathurlencode(dirname(dirname($zenCSS))); ?>/common.css" type="text/css" />
		</head>
		<body>
			<?php zp_apply_filter('theme_body_open'); ?>
			<div id="main">
				<div id="gallerytitle">
					<h2>
						<?php printHomeLink('', ' | '); ?>
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo gettext('Gallery Index'); ?></a> |
						<em><?php echo gettext('Register') ?></em>
					</h2>
				</div>
				<h2><?php echo gettext('User Registration') ?></h2>
				<?php printRegistrationForm(); ?>
			</div>
			<?php
			@call_user_func('printUserLogin_out', "");
			@call_user_func('printLanguageSelector');
			?>
			<div id="credit">
				<?php printSoftwareLink(); ?>
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