<?php

// force UTF-8 Ø
if (!defined('WEBPATH')) die();
$themeResult = getTheme($zenCSS, $themeColor, 'kish-my father');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<?php
	if (getOption('effervescence_daily_album_image_effect') && getOption('custom_index_page') != 'gallery') {
		setOption('image_custom_images', getOption('effervescence_daily_album_image_effect'), false);
	}
	?>
	<title><?php echo getBareGalleryTitle(); if ($_zp_page>1) echo "[$_zp_page]"; ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/effervescence_plus/common.css" type="text/css" />
	<?php effervescence_theme_head(); ?>
	<?php printRSSHeaderLink('Gallery','Gallery RSS'); 	?>
</head>

<body onload="blurAnchors()">
<?php zp_apply_filter('theme_body_open'); ?>

	<!-- Wrap Header -->
	<div id="header">

	<!-- Logo -->
		<div id="gallerytitle">
			<div id="logo">
				<?php
				if (getOption('Allow_search')) {
					$album_list = array('albums'=>'1','pages'=>'0', 'news'=>'0');
					printSearchForm(NULL, 'search', $_zp_themeroot.'/images/search.png', gettext('Search albums'), NULL, NULL, $album_list);
				}
				printLogo();
				?>
			</div>
		</div> <!-- gallerytitle -->

	<!-- Crumb Trail Navigation -->
		<div id="wrapnav">
			<div id="navbar">
				<span><?php printHomeLink('', ' | ');?>
				<?php
				if (getOption('custom_index_page') === 'gallery') {
					?>
					<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home');?></a> |
					<?php
				}
 				printGalleryTitle();
 				?>
				</span>
			</div>
		</div> <!-- wrapnav -->
	</div> <!-- header -->
	<!-- Random Image -->
	<?php
	printHeadingImage(getRandomImages(getThemeOption('effervescence_daily_album_image')));
	?>

	<!-- Wrap Main Body -->
	<div id="content">
		<div id="main">

		<!-- Album List -->
		<ul id="albums">
			<?php
			$firstAlbum = null;
			$lastAlbum = null;
			while (next_album()){
				if (is_null($firstAlbum)) {
					$lastAlbum = albumNumber();
					$firstAlbum = $lastAlbum;
				} else {
					$lastAlbum++;
				}
			?>
			<li>
				<?php $annotate =  annotateAlbum();	?>
				<div class="imagethumb">
				<a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo $annotate; ?>">
						<?php printCustomAlbumThumbImage($annotate, null, 180, null, 180, 80); ?>
 				</a>
				</div>
				<h4><a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo $annotate; ?>"><?php printAlbumTitle(); ?></a></h4>
			</li>
			<?php } ?>
		</ul>
		<div class="clearage"></div>
		<?php printNofM('Album', $firstAlbum, $lastAlbum, getNumAlbums()); ?>

		</div> <!-- main -->
		<!-- Page Numbers -->
		<div id="pagenumbers">
			<?php printPageListWithNav("« ".gettext('prev'), gettext('next')." »"); ?>
		</div>
	</div> <!-- content -->

	<br style="clear:all" />

	<?php
	printFooter();
	zp_apply_filter('theme_body_close');
	?>

</body>
</html>