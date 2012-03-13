<?php
// force UTF-8 Ø

if (!defined('WEBPATH')) die(); $themeResult = getTheme($zenCSS, $themeColor, 'light');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<title><?php echo getBareGalleryTitle(); ?> | <?php echo getBareAlbumTitle(); ?> | <?php echo getBareImageTitle(); ?></title>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
		<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/default/common.css" type="text/css" />
		<?php if (zp_has_filter('theme_head', 'colorbox::css')) { ?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				$(document).ready(function(){
					$(".colorbox").colorbox({
						inline:true,
						href:"#imagemetadata",
						close: '<?php echo gettext("close"); ?>'
					});
				});
				// ]]> -->
			</script>
		<?php } ?>
		<?php printRSSHeaderLink('Gallery', gettext('Gallery RSS')); ?>
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		<div id="main">
			<div id="gallerytitle">
				<div class="imgnav">
					<?php if (hasPrevImage()) { ?>
						<div class="imgprevious"><a href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext("Previous Image"); ?>">« <?php echo gettext("prev"); ?></a></div>
					<?php } if (hasNextImage()) { ?>
						<div class="imgnext"><a href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext("Next Image"); ?>"><?php echo gettext("next"); ?> »</a></div>
					<?php } ?>
				</div>
				<h2>
					<span>
						<?php printHomeLink('', ' | '); ?>
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php gettext('Albums Index'); ?>"><?php echo getGalleryTitle(); ?></a> |
						<?php
						printParentBreadcrumb("", " | ", " | ");
						printAlbumBreadcrumb("", " | ");
						?>
					</span>
					<?php printImageTitle(true); ?>
				</h2>
			</div>
			<!-- The Image -->
			<div id="image">
				<strong>
					<?php
					$fullimage = getFullImageURL();
					if (!empty($fullimage)) {
						?>
						<a href="<?php echo html_encode($fullimage); ?>" title="<?php echo getBareImageTitle(); ?>">
							<?php
						}
						if (function_exists('printUserSizeImage') && isImagePhoto()) {
							printUserSizeImage(getImageTitle());
						} else {
							printDefaultSizedImage(getImageTitle());
						}
						if (!empty($fullimage)) {
							?>
						</a>
						<?php
					}
					?>
				</strong>
				<?php
				if (isImagePhoto())
					@call_user_func('printUserSizeSelector');
				?>
			</div>
			<div id="narrow">
				<?php printImageDesc(true); ?>
				<hr /><br />
				<?php
				if (getImageMetaData()) {
					echo printImageMetadata(NULL, 'colorbox');
					?>
					<br clear="all" />
					<?php
				}
				printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', '');
				?>
				<br clear="all" />
				<?php @call_user_func('printSlideShowLink'); ?>
				<?php @call_user_func('printGoogleMap'); ?>
				<?php @call_user_func('printRating'); ?>
				<?php @call_user_func('printCommentForm'); ?>
			</div>
		</div>
		<div id="credit">
			<?php printRSSLink('Gallery', '', 'RSS', ' | '); ?>
			<?php printCustomPageURL(gettext("Archive View"), "archive"); ?> |
			<?php printZenphotoLink(); ?>
			<?php @call_user_func('printUserLogin_out'," | "); ?>
		</div>
		<?php
		printAdminToolbox();
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>
