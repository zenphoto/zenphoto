<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
if (class_exists('CMS')) {
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<?php zp_apply_filter('theme_head'); ?>



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
						<h2><?php echo gettext('Pages'); ?></h2>
						<br />
						<ul data-role="listview" data-inset="true" data-theme="a" class="ui-listview ui-group-theme-a">
							<?php printPageMenu("list-top", "", "menu-active", "submenu", "menu-active", NULL, true, false, NULL); ?>
						</ul>
					</div>
					<div class="content-secondary">
						<?php jqm_printMenusLinks(); ?>
					</div>
				</div><!-- /content -->
				<?php jqm_printBacktoTopLink(); ?>
				<?php jqm_printFooterNav(); ?>
			</div><!-- /page -->

			<?php zp_apply_filter('theme_body_close');
			?>
		</body>
	</html>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>