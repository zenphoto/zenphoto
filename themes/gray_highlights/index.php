<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/css/reset.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/css/text.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/css/1200_15_col.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/css/theme.css" type="text/css" media="screen" />
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		<div class="container_15">
			<div id="header" class="grid_15">
				<?php
				if (function_exists('printLanguageSelector')) {
					echo '<div class="languages grid_5">';
					printLanguageSelector(true);
					echo '</div>';
				}
				?>
<?php printLoginZone(); ?>
				<h1><?php echo html_encode(getBareGalleryTitle()); ?></h1>
			</div>
			<div class="clear"></div>
			<div id="menu">
				<div id="m_search" class="grid_8">
				<?php printSearchForm(''); ?>
				</div>
<?php printMenu(); ?>
			</div>
			<div class="clear"></div>
			<div id="content">
				<ul class="gallery">
							<?php while (next_album()): ?>
						<li class="grid_5">
							<a href="<?php echo getAlbumURL(); ?>" title="<?php echo html_encodeTagged(getAnnotatedAlbumTitle()) ?>">
								<?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), NULL, 376, 140, 376, 140, NULL, null, NULL, NULL); ?>
							</a>
							<span class="title">
	<?php echo html_encodeTagged(getAnnotatedAlbumTitle()) ?>
								<span class="italic">
									[<?php printf(ngettext('%u image', '%u images', getNumImages()), getNumImages()); ?>]
								</span><br />
						<?php echo getAlbumDate("(%d/%m/%Y)"); ?>
							</span>
						</li>
				<?php endwhile; ?>
				</ul>
				<div class="clear"></div>
				<?php printPageListWithNav("« " . gettext("prev"), gettext("next") . " »", false, true, 'pagelist', null, true, 5); ?>
			</div>
			<div id="footer" class="grid_15">
		<?php printFooter(); ?>
			</div>
		</div>
<?php zp_apply_filter('theme_body_close'); ?>
	</body>
</html>
