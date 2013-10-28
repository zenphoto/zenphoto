<?php
// force UTF-8
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
		<link rel="stylesheet" href="<?php echo WEBPATH . '/' . THEMEFOLDER; ?>/default/common.css" type="text/css" />
<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', gettext('Gallery RSS')); ?>
	</head>
	<body>
		<?php
		zp_apply_filter('theme_body_open');
		$total = getNumImages() + getNumAlbums();
		if (!$total) {
			$_zp_current_search->clearSearchWords();
		}
		?>
		<div id="main">
			<div id="gallerytitle">
				<?php
				printSearchForm();
				?>
				<h2>
					<span>
<?php printHomeLink('', ' | '); ?>
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo ('Gallery Index'); ?>"><?php printGalleryTitle(); ?></a>
					</span> |
<?php printSearchBreadcrumb(' | '); ?>
				</h2>
			</div>
			<div id="padbox">
				<?php
				if (($total = getNumImages() + getNumAlbums()) > 0) {
					if (isset($_REQUEST['date'])) {
						$searchwords = getSearchDate();
					} else {
						$searchwords = getSearchWords();
					}
					echo '<p>' . sprintf(gettext('Total matches for <em>%1$s</em>: %2$u'), html_encode($searchwords), $total) . '</p>';
				}
				$c = 0;
				?>
				<div id="albums">
					<?php
					while (next_album()) {
						$c++;
						?>
						<div class="album">
							<div class="thumb">
								<a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumThumbImage(getAnnotatedAlbumTitle()); ?></a>
							</div>
							<div class="albumdesc">
								<h3><a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumTitle(); ?></a></h3>
								<p><?php printAlbumDesc(); ?></p>
								<small><?php printAlbumDate(gettext("Date:") . ' '); ?> </small>
							</div>
							<p style="clear: both; "></p>
						</div>
						<?php
					}
					?>
				</div>
				<div id="images">
					<?php
					while (next_image()) {
						$c++;
						?>
						<div class="image">
							<div class="imagethumb"><a href="<?php echo html_encode(getImageLinkURL()); ?>" title="<?php printBareImageTitle(); ?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a></div>
						</div>
						<?php
					}
					?>
				</div>
				<br class="clearall" />
				<?php
				@call_user_func('printSlideShowLink');
				if ($c == 0) {
					echo "<p>" . gettext("Sorry, no image matches found. Try refining your search.") . "</p>";
				}
				printPageListWithNav("« " . gettext("prev"), gettext("next") . " »");
				?>
			</div>
		</div>
		<div id="credit">
			<?php if (class_exists('RSS')) printRSSLink('Gallery', '', gettext('Gallery RSS'), ' | '); ?>
			<?php printCustomPageURL(gettext("Archive View"), "archive"); ?> |
			<?php
			if (function_exists('printFavoritesLink')) {
				printFavoritesLink();
				?> | <?php
			}
			?>
			<?php printZenphotoLink(); ?>
		<?php @call_user_func('printUserLogin_out', " | "); ?>
		</div>
		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>