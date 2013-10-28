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
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
</head>
<body>
<?php zp_apply_filter('theme_body_open'); ?>

<div id="main">

		<div id="header">
		<h1><?php printGalleryTitle();?></h1>
			<?php if (getOption('Allow_search')) {
				$album_list = array('albums'=>array($_zp_current_album->name),'pages'=>'0', 'news'=>'0');
				printSearchForm(NULL, 'search', NULL, gettext('Search album'), NULL, NULL, $album_list);
			} ?>
		</div>

<div id="content">

	<div id="breadcrumb">
<h2><a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Index'); ?>"><?php echo gettext("Index"); ?></a> » <?php printAlbumTitle();?></strong></h2>
</div>

	<div id="content-left">
	<div><?php printAlbumDesc(); ?></div>


<?php printPageListWithNav("« ".gettext("prev"), gettext("next")." »"); ?>
			<div id="albums">
			<?php while (next_album()): ?>
			<div class="album">
				<div class="thumb">
					<a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo gettext('View album:'); ?> <?php getBareAlbumTitle();?>"><?php printCustomAlbumThumbImage(getBareAlbumTitle(), NULL, 95, 95, 95, 95); ?></a>
				</div>
				<div class="albumdesc">
					<h3><a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo gettext('View album:'); ?> <?php printBareAlbumTitle();?>"><?php printAlbumTitle(); ?></a></h3>
						<?php printAlbumDate(""); ?>
					<div><?php echo shortenContent(getAlbumDesc(), 45,'...'); ?></div>
					<br />
					<?php printAddToFavorites($_zp_current_album, '',gettext('Remove')); ?>
				</div>
				<p style="clear: both; "></p>
			</div>
			<?php endwhile; ?>
		</div>

			<div id="images">
			<?php while (next_image()): ?>
			<div class="image">
				<div class="imagethumb"><a href="<?php echo html_encode(getImageLinkURL());?>" title="<?php printBareImageTitle();?>"><?php printImageThumb(getBareImageTitle()); ?></a>
				<?php printAddToFavorites($_zp_current_image, '',gettext('Remove')); ?>
				</div>
			</div>
			<?php endwhile; ?>

		</div>
				<p style="clear: both; "></p>
				<?php @call_user_func('printSlideShowLink'); ?>
		<?php printPageListWithNav("« ".gettext("prev"), gettext("next")." »"); ?>
		<?php printTags('links', gettext('<strong>Tags:</strong>').' ', 'taglist', ', '); ?>
		<br style="clear:both;" /><br />
	<?php @call_user_func('printRating'); ?>
	<?php @call_user_func('printCommentForm'); ?>

	</div><!-- content left-->



	<div id="sidebar">
		<?php include("sidebar.php"); ?>
	</div><!-- sidebar -->



	<div id="footer">
	<?php include("footer.php"); ?>
	</div>

</div><!-- content -->

</div><!-- main -->
<?php
zp_apply_filter('theme_body_close');
?>
</body>
</html>