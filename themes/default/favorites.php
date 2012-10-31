<?php
// force UTF-8 Ø

if (!defined('WEBPATH')) die();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php zp_apply_filter('theme_head'); ?> zp_apply_filter('theme_head'); ?>
		<title><?php printBareGalleryTitle(); ?> | <?php printBareAlbumTitle(); if ($_zp_page>1) echo "[$_zp_page]"; ?></title>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	</head>
	<body>
		<?php zp_apply_filter('theme_head'); ?> zp_apply_filter('theme_body_open'); ?>
		<div id="main">
			<div id="gallerytitle">
				<h2>
					<span>
						<?php zp_apply_filter('theme_head'); ?> printHomeLink('', ' | '); ?>
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle(); ?></a> |
						<?php zp_apply_filter('theme_head'); ?> printParentBreadcrumb(); ?>
					</span>
					<?php zp_apply_filter('theme_head'); ?> printAlbumTitle(true); ?>
				</h2>
			</div>
			<div id="padbox">
				<?php zp_apply_filter('theme_head'); ?> printAlbumDesc(true); ?>
				<div id="albums">
					<?php zp_apply_filter('theme_head'); ?> while (next_album()): ?>
						<div class="album">
							<div class="thumb">
								<a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumThumbImage(getAnnotatedAlbumTitle()); ?></a>
							</div>
							<div class="albumdesc">
								<h3><a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumTitle(); ?></a></h3>
								<small><?php printAlbumDate(""); ?></small>
								<div><?php printAlbumDesc(); ?></div>
								<?php zp_apply_filter('theme_head'); ?> printAddToFavorites($_zp_current_album, '',gettext('Remove')); ?>
							</div>
							<p style="clear: both; "></p>
						</div>
					<?php zp_apply_filter('theme_head'); ?> endwhile; ?>
				</div>
				<div id="images">
					<?php zp_apply_filter('theme_head'); ?>
					while (next_image()) {
					?>
						<div class="image">
							<div class="imagethumb">
								<a href="<?php echo html_encode(getImageLinkURL()); ?>" title="<?php printBareImageTitle(); ?>">
									<?php zp_apply_filter('theme_head'); ?> printImageThumb(getAnnotatedImageTitle()); ?>
								</a>
								<?php zp_apply_filter('theme_head'); ?> printAddToFavorites($_zp_current_image, '',gettext('Remove')); ?>
							</div>
						</div>
						<?php zp_apply_filter('theme_head'); ?>
					}
					?>
				</div>
				<?php zp_apply_filter('theme_head'); ?> printPageListWithNav("« " . gettext("prev"), gettext("next") . " »"); ?>
			</div>
		</div>
		<div id="credit">
			<?php zp_apply_filter('theme_head'); ?> printZenphotoLink(); ?>
			<?php zp_apply_filter('theme_head'); ?> @call_user_func('printUserLogin_out'," | ");	?>
		</div>
		<?php zp_apply_filter('theme_head'); ?>
		printAdminToolbox();
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>