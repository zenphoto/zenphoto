<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
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
					<h2 class="breadcrumb"><a href="<?php echo getGalleryIndexURL(); ?>"><?php echo gettext('Gallery'); ?></a> <?php printParentBreadcrumb('', '', ''); ?> <?php printAlbumTitle(); ?></h2>
					<?php printAlbumDesc(); ?>
					<?php if (hasPrevPage() || hasNextPage()) printPageListWithNav(gettext("prev"), gettext("next"), false, true, 'pagelist', NULL, true, 7); ?>
					<ul data-role="listview" data-inset="true">
						<?php while (next_album()): ?>
							<li>
								<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:');
						printAnnotatedAlbumTitle();
							?>">
	<?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, 79, 79, 79, 79, NULL, null, NULL, NULL); ?>
									<h3><?php printAlbumTitle(); ?><small> (<?php printAlbumDate(''); ?>)</small></h3>
									<div class="albumdesc"><?php echo html_encodeTagged(shortenContent(getAlbumDesc(), 100, '(...)', false)); ?></div>
									<small class="ui-li-aside ui-li-count"><?php jqm_printImageAlbumCount() ?></small>
								</a>
							</li>
<?php endwhile; ?>
					</ul>
					<div class="ui-grid-c">
						<?php
						$count = '';
						while (next_image()):
							$count++;
							switch ($count) {
								case 1:
									$imgclass = ' ui-block-a';
									break;
								case 2:
									$imgclass = ' ui-block-b';
									break;
								case 3:
									$imgclass = ' ui-block-c';
									break;
								case 4:
									$imgclass = ' ui-block-d';
									$count = ''; // reset to start with a again;
									break;
							}
							?>
							<a class="image<?php echo $imgclass; ?>" href="<?php echo html_encode(getImageURL()); ?>" title="<?php printBareImageTitle(); ?>">
							<?php printCustomSizedImage(getAnnotatedImageTitle(), NULL, 230, 230, 230, 230, NULL, NULL, NULL, NULL, true, NULL); ?>
							</a>
<?php endwhile; ?>
					</div>
					<br class="clearall">
					<?php
					if (hasPrevPage() || hasNextPage())
						printPageListWithNav(gettext("prev"), gettext("next"), false, true, 'pagelist', NULL, true, 7);
					if (function_exists('printSlideShowLink')) {
						echo '<span id="slideshowlink">';
						printSlideShowLink();
						echo '</span>';
					}
					if (function_exists('printAddToFavorites')) {
						echo "<br />";
						printAddToFavorites($_zp_current_album);
					}
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

<?php zp_apply_filter('theme_body_close'); ?>

	</body>
</html>