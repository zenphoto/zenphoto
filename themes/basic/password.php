<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
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
					<span>
						<?php printHomeLink('', ' | '); ?>
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php printGalleryTitle(); ?></a>
					</span> |
					<?php echo gettext("A password is required for the page you requested"); ?>
				</h2>
			</div>
			<div id="padbox">
				<?php printPasswordForm($hint, $show, false); ?>
			</div>
		</div>
		<div id="credit">
			<?php
			if (!zp_loggedin() && function_exists('printRegisterURL') && $_zp_gallery->isUnprotectedPage('register')) {
				echo '<p>';
				printRegisterURL(gettext('Register for this site'), '<br />');
				echo '</p>';
			}
			?>
			<?php printSoftwareLink(); ?>
		</div>
		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>
