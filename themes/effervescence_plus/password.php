<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="<?php echo LOCAL_CHARSET; ?>">
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
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
					<?php printHomeLink('', ' | '); printGalleryIndexURL(' | ', getGalleryTitle()); echo gettext('A password is required for the page you requested'); ?>
				</div>
			</div>

		</div>

		<!-- Wrap Main Body -->
		<div id="content">
			<small>&nbsp;</small>
			<div id="main">
				<?php printPasswordForm('', true, false); ?>
			</div>
		</div>

		<?php
		printFooter(false);
		zp_apply_filter('theme_body_close');
		?>

	</body>
</html>