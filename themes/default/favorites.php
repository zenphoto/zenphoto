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
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		<div id="main">
			<div id="gallerytitle">
				<h2>
					<span>
						<?php printHomeLink('', ' | '); ?>
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle(); ?></a> |
						<?php printParentBreadcrumb(); ?>
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
								<a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumThumbImage(getAnnotatedAlbumTitle()); ?></a>
							</div>
							<div class="albumdesc">
								<h3><a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumTitle(); ?></a></h3>
								<small><?php printAlbumDate(""); ?></small>
								<div><?php printAlbumDesc(); ?></div>
								<?php printAddToFavorites($_zp_current_album, '',gettext('Remove')); ?>
							</div>
							<p style="clear: both; "></p>
						</div>
					<?php endwhile; ?>
				</div>
				<div id="images">
					<?php
					while (next_image()) {
					?>
						<div class="image">
							<div class="imagethumb">
								<a href="<?php echo html_encode(getImageLinkURL()); ?>" title="<?php printBareImageTitle(); ?>">
									<?php printImageThumb(getAnnotatedImageTitle()); ?>
								</a>
								<?php printAddToFavorites($_zp_current_image, '',gettext('Remove')); ?>
							</div>
						</div>
					<?php
					}
					?>
				</div>
				<?php @call_user_func('printSlideShowLink'); ?>
				<?php printPageListWithNav("« " . gettext("prev"), gettext("next") . " »"); ?>
			</div>
		</div>
		<div id="credit">
			<?php printZenphotoLink(); ?>
			<?php @call_user_func('printUserLogin_out'," | ");	?>
		</div>
		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>