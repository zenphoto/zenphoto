<?php

// force UTF-8 Ø
if (!defined('WEBPATH')) die();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php printBareGalleryTitle(); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<?php zp_apply_filter('theme_head'); ?>
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/effervescence_plus/common.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/slideshow.css" type="text/css" />
	<?php printSlideShowJS(); ?>
</head>
<body>
<?php zp_apply_filter('theme_body_open'); ?>
	<!-- Wrap Everything -->
	<div id="main4">
		<div id="main2">

			<!-- Wrap Header -->
			<div id="galleryheader">
				<div id="gallerytitle">
					<div id="logo2">
					<?php printLogo(); ?>
					</div>
				</div> <!-- gallery title -->

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
						<a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>">
						<?php printGalleryTitle();?></a> |
						<?php
						if (is_null($_zp_current_album)) {
							$search = new SearchEngine();
							$params = trim(zp_getCookie('zenphoto_search_params'));
							$search->setSearchParams($params);
							$images = $search->getImages(0);
							$searchwords = $search->getSearchWords();
							$searchdate = $search->getSearchDate();
							$searchfields = $search->getSearchFields(true);
							$page = $search->page;
							$returnpath = getSearchURL($searchwords, $searchdate, $searchfields, $page);
							echo '<a href='.html_encode($returnpath).'><em>'.gettext('Search').'</em></a> | ';
						} else {
							printParentBreadcrumb();
							printAlbumBreadcrumb("", " | ");
						}
						?> </span>
						Slideshow
					</div> <!-- navbar -->
				</div> <!-- wrapnav -->
			</div> <!-- galleryheader -->
		</div> <!-- main2 -->
		<div id="content">
			<div id="main">
				<div id="slideshowpage">
					<?php printSlideShow(false,true); ?>
				</div>
			</div> <!-- main -->
		</div> <!-- content -->
	</div> <!-- main4 -->
	<br style="clear:all" />
	<!-- Footer -->
	<div class="footlinks">
		<?php
		printThemeInfo();
		?>
		<?php printZenphotoLink(); ?>
	</div> <!-- footlinks -->
	<?php
	printFooter();
	zp_apply_filter('theme_body_close');
	?>
</body>
</html>