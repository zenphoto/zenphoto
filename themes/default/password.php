<?php
// force UTF-8 Ø

if (!defined('WEBPATH')) die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
		<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/default/common.css" type="text/css" />
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
			if (!zp_loggedin() && function_exists('printRegistrationForm') && $_zp_gallery->isUnprotectedPage('register')) {
				echo '<p>';
				printCustomPageURL(gettext('Register for this site'), 'register', '', '<br />');
				echo '</p>';
			}
			?>
			<?php printZenphotoLink(); ?>
		</div>
		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>
