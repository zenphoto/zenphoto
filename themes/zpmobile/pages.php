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
						<h2 class="breadcrumb"><a href="<?php echo getPagesLink(); ?>"><?php echo gettext('Pages'); ?></a> <?php
							printZenpageItemsBreadcrumb('', '  ');
							printPageTitle('');
							?></strong></h2>

						<?php
						printPageContent();
						printCodeblock(1);
						$subpages = $_zp_current_page->getPages();
						if ($subpages) {
							?>
							<ul data-role="listview" data-inset="true" data-theme="a" class="ui-listview ui-group-theme-a">
								<?php
								foreach ($subpages as $subpage) {
									$obj = new Page($subpage['titlelink']);
									?>
									<li><a href="<?php echo html_encode($obj->getLink()); ?>" title="<?php echo html_encode($obj->getTitle()); ?>"><?php echo html_encode($obj->getTitle()); ?></a></li>
									<?php
								}
								?>
							</ul>
							<?php
						}
						printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', ', ');
						?>
						<?php
						if (function_exists('printCommentForm')) {
							printCommentForm();
						}
						?>
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