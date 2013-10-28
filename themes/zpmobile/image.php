<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" />
		<?php jqm_loadScripts(); ?>
	</head>

	<body>
		<?php zp_apply_filter('theme_body_open'); ?>

		<div data-role="page" id="mainpage">

			<?php jqm_printMainHeaderNav(); ?>

			<div data-role="content">
				<div class="content-primary">

					<h2 class="breadcrumb"><a href="<?php echo getGalleryIndexURL(); ?>"><?php echo gettext('Gallery'); ?></a><?php printParentBreadcrumb('', '', ''); ?><?php printAlbumBreadcrumb('', ''); ?> <?php printImageTitle(); ?>
					</h2>
					<div class="imgnav" data-role="controlgroup" data-type="horizontal">
						<?php if (hasPrevImage()) { ?>
							<a class="imgprev" href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext("Previous Image"); ?>" data-role="button"><?php echo gettext("prev"); ?></a>
						<?php } ?>
						<?php if (hasNextImage()) { ?>
							<a class="imgnext" href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext("Next Image"); ?>" data-role="button"><?php echo gettext("next"); ?></a>
						<?php } ?>
					</div>
					<div id="image">

						<?php
						if (isImagePhoto()) {
							?>
							<img src="<?php echo html_encode(pathurlencode(getDefaultSizedImage())); ?>" alt="<?php printBareImageTitle(); ?>" style="max-width:<?php echo getDefaultWidth(); ?>px"/>
							<?php
						} else {
							printDefaultSizedImage(getImageTitle());
						}
						if (isImageVideo() && getOption('zpmobile_mediadirectlink')) {
							?>
							<p><a href="<?php echo html_encode(getUnprotectedImageURL()); ?>" title="<?php echo gettext('Direct link'); ?>" rel="external"><?php echo gettext('Direct link'); ?></a></p>
							<?php
						}
						?>
					</div>
					<?php printImageDesc(); ?>
					<?php if (getTags()) {
						?>
						<div data-role="collapsible">
							<h3><?php echo gettext('Tags:'); ?></h3>
							<?php printTags('links', '', 'taglist', ''); ?>
						</div>
						<?php }
					?>
					<?php
					if (getImageMetaData()) {
						?>
						<div data-role="collapsible">
							<h3><?php echo gettext('View meta data'); ?></h3>
							<?php printImageMetadata(NULL, ''); ?>
						</div>
						<?php
					}
					?>
					<br style="clear:both" />
					<?php If (function_exists('printAddToFavorites')) printAddToFavorites($_zp_current_image); ?>
					<?php
					if (function_exists('printRating')) {
						echo '<div id="rating">';
						printRating();
						echo '</div>';
					}
					?>
					<?php if (function_exists('printGoogleMap')) printGoogleMap(); ?>
					<?php
					if (function_exists('printCommentForm')) {
						echo '<hr />';
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