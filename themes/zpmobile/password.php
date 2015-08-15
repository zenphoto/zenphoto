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
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" />
<?php jqm_loadScripts(); ?>
	</head>

	<body>
<?php zp_apply_filter('theme_body_open'); ?>

		<div data-role="page" id="mainpage">

<?php jqm_printMainHeaderNav(); ?>

			<div class="ui-content" role="main">
				<div class="content-primary">
					<h2><a href="<?php echo getGalleryIndexURL(); ?>">Index</a> » <strong><strong><?php echo gettext("A password is required for the page you requested"); ?></strong></strong></h2>

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

				</div>

			</div><!-- /content -->
			<?php jqm_printBacktoTopLink(); ?>
<?php jqm_printFooterNav(); ?>
		</div><!-- /page -->

		<?php zp_apply_filter('theme_body_close');
		?>
	</body>
</html>
