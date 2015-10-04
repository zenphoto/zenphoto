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
				<div id="m_bread" class="grid_8">
					<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo getGalleryTitle(); ?>"><?php echo getGalleryTitle(); ?></a>
					<?php printParentBreadcrumb('', '', ''); ?>
					<span class="current"><?php echo getAlbumTitle(); ?></span>
				</div>
				<?php printMenu(); ?>
			</div>
			<div class="clear"></div>
			<div id="content">
				<div class="desc grid_5">
					<h2 class="suffix_1"><?php echo getAlbumTitle(); ?></h2>
					<div class="date"><?php echo getAlbumDate('%d/%d/%Y'); ?></div>
					<div class="comment"><?php echo getAlbumDesc(); ?></div>
				</div>
				<div class="suffix_5">
					<ul class="gallery">
						<?php while (next_album()): ?>
							<li class="grid_5">
								<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo html_encodeTagged(getAnnotatedAlbumTitle()) ?>">
									<?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), NULL, 376, 140, 376, 140, NULL, null, NULL, NULL); ?>
								</a>
								<span class="title">
									<?php echo html_encodeTagged(getAnnotatedAlbumTitle()) ?>
									<span class="italic">
										[<?php printf(ngettext('%u image', '%u images', getNumImages()), getNumImages()); ?>]
									</span>
									<br />
									<?php echo getAlbumDate("(%d/%m/%Y)"); ?>
								</span>
							</li>
						<?php endwhile; ?>
					</ul>
					<div class="l_clear"></div>
					<ul class="album">
						<?php while (next_image()): ?>
							<li class="grid_5">
								<a href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>">
									<?php printCustomSizedImage(getAnnotatedImageTitle(), NULL, 376, 140, 376, 140, NULL, NULL, NULL, NULL, true, NULL); ?>
								</a>
								<span class="title">
									<?php echo getAnnotatedImageTitle(); ?>
								</span>

							</li>
						<?php endwhile; ?>
					</ul>
				</div>
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
