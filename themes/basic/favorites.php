<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
if (class_exists('favorites')) {
	?>
	<!DOCTYPE html>
	<html<?php printLangAttribute(); ?>>
		<head>
			<meta charset="<?php echo LOCAL_CHARSET; ?>">
			<?php zp_apply_filter('theme_head'); ?>
			<?php printHeadTitle(); ?>
			<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
			<link rel="stylesheet" href="<?php echo pathurlencode(dirname(dirname($zenCSS))); ?>/common.css" type="text/css" />
		</head>
		<body>
			<?php zp_apply_filter('theme_body_open'); ?>
			<div id="main">
				<div id="gallerytitle">
					<?php
					if (getOption('Allow_search')) {
						printSearchForm();
					}
					?>
					<h2>
						<span>
							<?php printHomeLink('', ' | ');
							printGalleryIndexURL(' | ', getGalleryTitle());
							printParentBreadcrumb(); ?>
						</span>
	<?php printAlbumTitle(); ?>
					</h2>
				</div>
				<div id="padbox">
						<?php printAlbumDesc(); ?>
					<div id="albums">
	<?php while (next_album()): ?>
							<div class="album">
								<div class="thumb">
									<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumThumbImage(getAnnotatedAlbumTitle()); ?></a>
								</div>
								<div class="albumdesc">
									<h3><a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumTitle(); ?></a></h3>
									<small><?php printAlbumDate(""); ?></small>
									<div><?php printAlbumDesc(); ?></div>
							<?php printAddToFavorites($_zp_current_album, '', gettext('Remove')); ?>
								</div>
							</div>
	<?php endwhile; ?>
					</div>
					<br class="clearall">
					<div id="images">
						<?php
						while (next_image()) {
							?>
							<div class="image">
								<div class="imagethumb">
									<a href="<?php echo html_encode(getImageURL()); ?>" title="<?php printBareImageTitle(); ?>">
									<?php printImageThumb(getAnnotatedImageTitle()); ?>
									</a>
							<?php printAddToFavorites($_zp_current_image, '', gettext('Remove')); ?>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<br class="clearall">
					<?php
					callUserFunction('printSlideShowLink');
					printPageListWithNav("« " . gettext("prev"), gettext("next") . " »");
					?>
				</div>
			</div>
	<?php include 'inc-footer.php'; ?>
		</body>
	</html>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>