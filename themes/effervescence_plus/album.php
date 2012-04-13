<?php

// force UTF-8 Ø

if (!defined('WEBPATH')) die();

$map = function_exists('printGoogleMap');
$themeResult = getTheme($zenCSS, $themeColor, 'kish-my father');
$personality = strtolower(getOption('Theme_personality'));
require_once(SERVERPATH.'/'.THEMEFOLDER.'/effervescence_plus/'.$personality.'/functions.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php echo getBareGalleryTitle(); ?> | <?php echo getBareAlbumTitle(); if ($_zp_page>1) echo "[$_zp_page]"; ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<?php $oneImagePage = $personality->theme_head($_zp_themeroot); ?>
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	<?php effervescence_theme_head(); ?>
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/effervescence_plus/common.css" type="text/css" />
</head>

<body onload="blurAnchors()">
<?php zp_apply_filter('theme_body_open'); ?>
<?php $personality->theme_bodyopen($_zp_themeroot); ?>

	<!-- Wrap Header -->
	<div id="header">
			<div id="gallerytitle">

			<!-- Subalbum Navigation -->
				<div class="albnav">
					<div class="albprevious">
					<?php
						$album = getPrevAlbum();
							if (is_null($album)) {
								echo '<div class="albdisabledlink">«  '.gettext('prev').'</div>';
							} else {
							echo '<a href="'.$album->getAlbumLink().
									'" title="' . html_encode($album->getTitle()) . '">« '.gettext('prev').'</a>';
							}
						?>
					</div> <!-- albprevious -->
					<div class="albnext">
						<?php
							$album = getNextAlbum();
							if (is_null($album)) {
									echo '<div class="albdisabledlink">'.gettext('next').' »</div>';
							} else {
								echo '<a href="'.$album->getAlbumLink().
										'" title="' . html_encode($album->getTitle()) . '">'.gettext('next').' »</a>';
							}
						?>
					</div><!-- albnext -->
					<?php
					if (getOption('Allow_search')) {
						$album_list = array('albums'=>array($_zp_current_album->name),'pages'=>'0', 'news'=>'0');
						printSearchForm(NULL, 'search', $_zp_themeroot.'/images/search.png', gettext('Search within album'), NULL, NULL, $album_list);
					}
					?>
				</div> <!-- header -->

			<!-- Logo -->
				<div id="logo">
					<?php
					printLogo();
					?>
				</div>
			</div> <!-- gallerytitle -->

		<!-- Crumb Trail Navigation -->
		<div id="wrapnav">
			<div id="navbar">
				<span><?php printHomeLink('', ' | '); ?>
				<?php
				if (getOption('custom_index_page') === 'gallery') {
					?>
					<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home');?></a> |
					<?php
				}
				?>
				<a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo getGalleryTitle();?></a> |
				<?php printParentBreadcrumb(); ?></span>
				<?php printAlbumTitle(true);?>
			</div>
		</div> <!-- wrapnav -->

		<!-- Random Image -->
		<?php
		if (isAlbumPage()) {
			printHeadingImage(getRandomImagesAlbum(NULL, getThemeOption('effervescence_daily_album_image')));
		}
		?>
	</div> <!-- header -->

	<!-- Wrap Subalbums -->
	<div id="subcontent">
		<div id="submain">

			<!-- Album Description -->
			<div id="description">
				<?php
				printAlbumDesc(true);
				?>
			</div>

			<!-- SubAlbum List -->

				<?php
				$firstAlbum = null;
				$lastAlbum = null;
				while (next_album()){
					if (is_null($firstAlbum)) {
						$lastAlbum = albumNumber();
						$firstAlbum = $lastAlbum;
						?>
						<ul id="albums">
						<?php
					} else {
						$lastAlbum++;
					}
					?>
					<li>
						<?php $annotate = annotateAlbum(); ?>
						<div class="imagethumb">
							<a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo html_encode($annotate) ?>">
							<?php printCustomAlbumThumbImage($annotate, null, 180, null, 180, 80); ?></a>
						</div>
						<h4>
							<a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo html_encode($annotate) ?>">
								<?php printAlbumTitle(); ?>
							</a>
						</h4>
					</li>
					<?php
				}
				if (!is_null($firstAlbum)) {
					?>
					</ul>
					<?php
				}
				?>

			<div class="clearage"></div>
			<?php
			printNofM('Album', $firstAlbum, $lastAlbum, getNumAlbums());
			?>
		</div> <!-- submain -->

		<!-- Wrap Main Body -->
		<?php
		if (getNumImages() > 0){  /* Only print if we have images. */
			$personality->theme_content($map);
		} else { /* no images to display */
			if (getNumAlbums() == 0){
			?>
				<div id="main3">
					<div id="main2">
					<br />
					<p align="center"><?php echo gettext('Album is empty'); ?></p>
					</div>
				</div> <!-- main3 -->
				<?php
			} else {
				?>
				<div id="main">
					<?php @call_user_func('printRating'); ?>
				</div>
				<?php
			}
		}
		?>

<!-- Page Numbers -->
		<div id="pagenumbers">
		<?php
		if ((getNumAlbums() != 0) || !$oneImagePage){
			printPageListWithNav("« " .gettext('prev'), gettext('next')." »", $oneImagePage);
		}
		?>
		</div> <!-- pagenumbers -->
	<?php commonComment(); ?>
</div> <!-- subcontent -->

<!-- Footer -->
<br style="clear:all" />

<?php
printFooter();
zp_apply_filter('theme_body_close');
?>

</body>
</html>
