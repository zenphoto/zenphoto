<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html<?php printLangAttribute(); ?>>
	<head>
		<meta charset="<?php echo LOCAL_CHARSET; ?>">
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
		<link rel="stylesheet" href="<?php echo pathurlencode(dirname(dirname($zenCSS))); ?>/common.css" type="text/css" />
		<?php if (class_exists('RSS')) printRSSHeaderLink('Album', getAlbumTitle()); ?>
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
						<?php printHomeLink('', ' | '); printGalleryIndexURL(' | ', getGalleryTitle()); printParentBreadcrumb(); ?>
					</span>
					<?php printAlbumTitle(); printCurrentPageAppendix(); ?>
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
							</div>

						</div>
					<?php endwhile; ?>
				</div>
				<br class="clearfloat">
				<div id="images">
					<?php while (next_image()): ?>
						<div class="image">
							<div class="imagethumb">
								<a href="<?php echo html_encode(getImageURL()); ?>" title="<?php printBareImageTitle(); ?>">
									<?php printImageThumb(getAnnotatedImageTitle()); ?>
								</a>
							</div>
						</div>
					<?php endwhile; ?>
				</div>
				<br class="clearfloat">
				<?php
					printPageListWithNav("« " . gettext("prev"), gettext("next") . " »");
					if (function_exists('printAddToFavorites')) printAddToFavorites($_zp_current_album);
					printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', '');
					callUserFunction('openStreetMap::printOpenStreetMap');
					callUserFunction('printGoogleMap');
					callUserFunction('printSlideShowLink');
					callUserFunction('printRating');
					callUserFunction('printCommentForm');
				?>
			</div>
		</div>
		<?php include 'inc-footer.php'; ?>
	</body>
</html>