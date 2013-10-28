<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
require_once('normalizer.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" type="text/css" media="screen, projection" href="<?php echo $_zp_themeroot ?>/css/master.css" />
		<script type="text/javascript">var blogrelurl = "<?php echo $_zp_themeroot ?>";</script>
		<?php if (zp_has_filter('theme_head', 'colorbox::css')) { ?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				$(document).ready(function() {
					$(".colorbox").colorbox({
						inline: true,
						href: "#imagemetadata",
						close: '<?php echo gettext("close"); ?>'
					});
				});
				// ]]> -->
			</script>
		<?php } ?>
		<?php
		if (class_exists('RSS'))
			printRSSHeaderLink('Gallery', gettext('Gallery RSS'));
		setOption('thumb_crop_width', 85, false);
		setOption('thumb_crop_height', 85, false);
		setOption('images_per_page', getOption('images_per_page') - 1, false);
		if (!isImagePhoto($_zp_current_image))
			echo '<style type="text/css"> #prevnext a strong {display:none;}</style>';
		if (function_exists('getCommentErrors') && getCommentErrors()) {
			$errors = 1;
			?>
			<link rel="stylesheet" type="text/css" href="<?php echo $_zp_themeroot ?>/css/comments-show.css" />
			<?php
		} else {
			$errors = 0;
			?>
			<link rel="stylesheet" type="text/css" href="<?php echo $_zp_themeroot ?>/css/comments-hide.css" />
			<?php
		}
		?>
	</head>

	<body class="photosolo">
<?php zp_apply_filter('theme_body_open'); ?>
<?php printGalleryTitle(); ?><?php if (getOption('Allow_search')) {
	printSearchForm();
} ?>

		<div id="content" class="v">

			<div id="desc">
				<h1><?php printImageTitle(); ?></h1>
				<div id="descText"><?php printImageDesc(); ?></div>
			</div>

			<?php
			$ls = isLandscape();
			setOption('image_size', 480, false);
			$w = getDefaultWidth();
			$h = getDefaultHeight();
			if ($ls) {
				$wide = '';
			} else {
				$wide = " style=\"width:" . ($w + 22) . "px;\"";
			}
			?>
			<div class="main" <?php echo $wide; ?>>
				<p id="photo">
					<strong>
<?php printCustomSizedImage(getImageTitle(), null, $ls ? 480 : null, $ls ? null : 480); ?>
					</strong>
				</p>
			</div>
			<div id="meta">
				<ul>
					<li class="count"><?php
if (($num = getNumImages()) > 1) {
	printf(gettext('%1$u of %2$u images'), imageNumber(), getNumImages());
}
?></li>
					<li class="date"><?php printImageDate(); ?></li>
					<li class="tags"><?php echo getAlbumLocation(); ?></li>
					<li class="exif">
						<?php
						if (getImageMetaData()) {
							printImageMetadata(NULL, 'colorbox');
							if (isImagePhoto())
								echo "&nbsp;/&nbsp;";
						}
						if (isImagePhoto()) {
							$fullimage = getFullImageURL();
						} else {
							$fullimage = NULL;
						}
						if (!empty($fullimage)) {
							?>
							<a href="<?php echo html_encode(pathurlencode($fullimage)); ?>" title="<?php printBareImageTitle(); ?>"><?php echo gettext('Full Size'); ?></a>
					<?php
				}
				?>
					</li>
				</ul>
			</div>

			<div class="main">
				<?php If (function_exists('printAddToFavorites')) printAddToFavorites($_zp_current_image); ?>
				<div class="rating"><?php if (function_exists('printRating')) printRating(); ?></div>
<?php @call_user_func('printGoogleMap'); ?>
<?php
if (function_exists('printCommentForm')) {
	require_once('comment.php');
}
?>

			</div>

			<div id="prevnext">
				<?php
				$img = $_zp_current_image->getPrevImage();
				if ($img) {
					if ($img->getWidth() >= $img->getHeight()) {
						$iw = 89;
						$ih = NULL;
						$cw = 89;
						$ch = 67;
					} else {
						$iw = NULL;
						$ih = 89;
						$ch = 89;
						$cw = 67;
					}
					?>
					<div id="prev"><span class="thumb"><span>
								<em style="background-image:url('<?php echo html_encode($img->getCustomImage(NULL, $iw, $ih, $cw, $ch, NULL, NULL, true)); ?>')">
									<a href="<?php echo getPrevImageURL(); ?>" accesskey="z" style="background:#fff;">
										<strong style="width:<?php echo round(($w + 20) / 2); ?>px; height:<?php echo $h + 20; ?>px;"><?php echo gettext('Previous'); ?>: </strong>Crescent</a>
								</em></span></span></div>
					<?php
				}
				$img = $_zp_current_image->getNextImage();
				if ($img) {
					if ($img->getWidth() >= $img->getHeight()) {
						$iw = 89;
						$ih = NULL;
						$cw = 89;
						$ch = 67;
					} else {
						$iw = NULL;
						$ih = 89;
						$ch = 89;
						$cw = 67;
					}
					?>
					<div id="next"><span class="thumb"><span>
								<em style="background-image:url('<?php echo html_encode($img->getCustomImage(NULL, $iw, $ih, $cw, $ch, NULL, NULL, true)); ?>')">
									<a href="<?php echo getNextImageURL(); ?>" accesskey="x" style="background:#fff;">
										<strong style="width:<?php echo round(($w + 20) / 2); ?>px; height:<?php echo $h + 20; ?>px;"><?php echo gettext('Next'); ?>: </strong>Sagamor</a>
								</em></span></span></div>
<?php } ?>
			</div>

		</div>

		<p id="path">
			<?php printHomeLink('', ' > '); ?>
			<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home'); ?></a> &gt;
			<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle(); ?></a> &gt; <?php printParentBreadcrumb("", " > ", " > ");
			printAlbumBreadcrumb("", " > ");
			echo getImageTitle(); ?>
		</p>

		<div id="footer">
			<hr />
		<?php
		if (function_exists('printFavoritesLink')) {
			printFavoritesLink();
		}
		if (function_exists('printUserLogin_out')) {
			printUserLogin_out("");
		}
		?>
			<p>
<?php echo gettext('<a href="http://stopdesign.com/templates/photos/">Photo Templates</a> from Stopdesign.'); ?>
<?php printZenphotoLink(); ?>
			</p>
		</div>
<?php
zp_apply_filter('theme_body_close');
?>
	</body>
</html>
