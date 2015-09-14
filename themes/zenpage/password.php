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
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
	</head>

	<body>
		<?php zp_apply_filter('theme_body_open'); ?>

		<div id="main">

			<div id="header">
				<h1><?php printGalleryTitle(); ?></h1>
				<?php
				if (getOption('Allow_search')) {
					printSearchForm("", "search", "", gettext("Search"));
				}
				?>
			</div>

			<div id="content">
				<div id="breadcrumb">
					<h2><?php printGalleryIndexURL(' » '); echo gettext("A password is required for the page you requested"); ?></strong></h2>
				</div>

				<div id="content-error">

					<div class="errorbox">
						<?php printPasswordForm('', true, false); ?>
					</div>

					<?php
					if (!zp_loggedin() && function_exists('printRegisterURL') && $_zp_gallery->isUnprotectedPage('register')) {
						printRegisterURL(gettext('Register for this site'), '<br />');
						echo '<br />';
					}
					?>
				</div>


				<div id="footer">
					<?php include("footer.php"); ?>
				</div>



			</div><!-- content -->

		</div><!-- main -->
		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>
