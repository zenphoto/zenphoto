<?php

// force UTF-8 Ø
if (!defined('WEBPATH') || !class_exists('Zenpage')) die();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php printBareGalleryTitle(); ?> | <?php echo gettext('News'); if ($_zp_page>1) echo "[$_zp_page]"; ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/effervescence_plus/common.css" type="text/css" />
	<?php effervescence_theme_head(); ?>
	<?php printZenpageRSSHeaderLink("News","", "Zenpage news", ""); ?>
</head>

<body onload="blurAnchors()">
<?php zp_apply_filter('theme_body_open'); ?>
	<!-- Wrap Header -->
	<div id="header">
		<div id="gallerytitle">

		<!-- Logo -->
			<div id="logo">
				<?php
				if (getOption('Allow_search')) {
					if (is_NewsCategory()) {
						$catlist = array('news'=>array($_zp_current_category->getTitlelink()),'albums'=>'0','images'=>'0','pages'=>'0');
						printSearchForm(NULL, 'search', $_zp_themeroot.'/images/search.png', gettext('Search within category'), NULL, NULL, $catlist);
					} else {
						$catlist = array('news'=>'1','albums'=>'0','images'=>'0','pages'=>'0');
						printSearchForm(NULL, 'search', $_zp_themeroot.'/images/search.png', gettext('Search news'), NULL, NULL, $catlist);
					}
				}
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
				<a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle();?></a></span>
				<?php
				if (in_context(ZP_ZENPAGE_NEWS_CATEGORY) || getNewsTitle()) {
					printNewsIndexURL(gettext("News")," | ");
					printCurrentNewsCategory(" | ".gettext('Category')." - ");
					printNewsTitle(" | ");
				} else {
					echo ' | '.gettext("News");
				}
				?>
			</div>
		</div> <!-- wrapnav -->

		<!-- Random Image -->
		<?php printHeadingImage(getRandomImages(getThemeOption('effervescence_daily_album_image'))); ?>
	</div> <!-- header -->

	<!-- Wrap Main Body -->
	<div id="content">

		<small>&nbsp;</small>
		<div id="main2">
			<div id="content-left">
	<?php
	if(is_NewsArticle()) { // single news article
		?>
		<?php if(getPrevNewsURL()) { ?><div class="singlenews_prev"><?php printPrevNewsLink(); ?></div><?php } ?>
		<?php if(getPrevNewsURL()) { ?><div class="singlenews_next"><?php printNextNewsLink(); ?></div><?php } ?>
		<?php if(getPrevNewsURL() OR getPrevNewsURL()) { ?><br clear:both /><?php } ?>
		<h3><?php printNewsTitle(); ?></h3>

		<div class="newsarticlecredit">
			<span class="newsarticlecredit-left"> <?php
			$count = getCommentCount();
			$cat = getNewsCategories();
			printNewsDate();
			if ($count > 0) {
				echo ' | ';
				printf(gettext("Comments: %d"),  $count);
			}
			if (!empty($cat)) {
				echo ' | ';
			}
			?>
			</span>
			<?php
			if (!empty($cat)) {
				printNewsCategories(", ",gettext("Categories: "),"newscategories");
			}
			?>
		<?php printCodeblock(1); ?>
		<?php printNewsContent(); ?>
		 <?php printCodeblock(2); ?>
		</div>
		<?php
		@call_user_func('printRating');
		commonComment();
	} else { 	// news article loop
		commonNewsLoop(true);
	}
	?>

	</div><!-- content left-->
	<div id="sidebar">
		<?php include("sidebar.php"); ?>
	</div><!-- sidebar -->
	<br style="clear:both" />
	</div> <!-- main2 -->

</div> <!-- content -->

<?php
printFooter();
zp_apply_filter('theme_body_close');
?>

</body>
</html>