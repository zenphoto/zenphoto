<?php

// force UTF-8 Ø

if (!defined('WEBPATH')) die(); $themeResult = getTheme($zenCSS, $themeColor, 'light');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php echo getBareGalleryTitle(); ?> | <?php echo getBareAlbumTitle();?> | <?php echo getBareImageTitle();?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
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
		<?php printRSSHeaderLink('Gallery',gettext('Gallery RSS')); ?>

</head>

<body>
<?php zp_apply_filter('theme_body_open'); ?>

<div id="main">

	<div id="gallerytitle">
		<div class="imgnav">
			<?php if (hasPrevImage()) { ?>
			<div class="imgprevious"><a href="<?php echo html_encode(getPrevImageURL());?>" title="<?php echo gettext("Previous Image"); ?>">&laquo; <?php echo gettext("prev"); ?></a></div>
			<?php } if (hasNextImage()) { ?>
			<div class="imgnext"><a href="<?php echo html_encode(getNextImageURL());?>" title="<?php echo gettext("Next Image"); ?>"><?php echo gettext("next"); ?> &raquo;</a></div>
			<?php } ?>
		</div>
		<h2><span><?php printHomeLink('', ' | '); ?><a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php gettext('Albums Index'); ?>"><?php echo getGalleryTitle();?>
			</a> | <?php printParentBreadcrumb("", " | ", " | "); printAlbumBreadcrumb("", " | "); ?>
			</span> <?php printImageTitle(true); ?>
		</h2>

	</div>

	<!-- The Image -->
	<div id="image">
		<strong>
		<?php
		$fullimage = getFullImageURL();
		if (!empty($fullimage)) {
			?>
			<a href="<?php echo html_encode($fullimage);?>" title="<?php echo getBareImageTitle();?>">
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
	if (function_exists('printUserSizeImage') && isImagePhoto()) printUserSizeSelector(); ?>
	</div>

	<div id="narrow">
		<?php printImageDesc(true); ?>
		<?php if (function_exists('printSlideShowLink')) printSlideShowLink(gettext('View Slideshow')); ?>
		<hr /><br />
		<?php
			if (getImageMetaData()) {echo "<div id=\"exif_link\"><a href=\"#\" title=\"".gettext("Image Info")."\" class=\"colorbox\">".gettext("Image Info")."</a></div>";
				echo "<div style='display:none'>"; printImageMetadata('', false); echo "</div>";
			}
		?>
		<?php printTags('links', gettext('<strong>Tags:</strong>').' ', 'taglist', ''); ?>
		<br clear="all" />

		<?php if (function_exists('printGoogleMap')) printGoogleMap(); ?>
    <?php if (function_exists('printRating')) { printRating(); }?>
		<?php if (function_exists('printShutterfly')) printShutterfly(); ?>

		<?php
		if (function_exists('printCommentForm')) {
			printCommentForm();
		}
		?>
	</div>
</div>

<div id="credit"><?php printRSSLink('Gallery','','RSS', ' | '); ?> <?php printCustomPageURL(gettext("Archive View"),"archive"); ?> |
<?php printZenphotoLink(); ?>
<?php
if (function_exists('printUserLogin_out')) {
	printUserLogin_out(" | ");
}
?>
</div>

<?php
printAdminToolbox();
zp_apply_filter('theme_body_close');
?>

</body>
</html>
