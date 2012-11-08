<?php

// force UTF-8 Ø

if (!defined('WEBPATH')) die();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php printBareGalleryTitle(); if ($_zp_page>1) echo "[$_zp_page]"; ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
	<?php printRSSHeaderLink('Gallery',gettext('Gallery RSS')); ?>
</head>
<body>
<?php zp_apply_filter('theme_body_open'); ?>

<div id="main">

		<div id="header">

		<h1><?php printGalleryTitle(); ?></h1>
		<?php if (getOption('Allow_search')) {  printSearchForm("","search","",gettext("Search gallery")); } ?>
		</div>

<div id="content">

	<div id="breadcrumb">
	<h2><a href="<?php echo getGalleryIndexURL(false); ?>"><strong><?php echo gettext("Index"); ?></strong></a>
	</h2>
	</div>
	<div id="content-left">
	<?php
	if(!getOption('zp_plugin_zenpage') || !($_zp_zenpage->news_on_index = getOption("zenpage_zp_index_news"))) {
	?>
	<?php printGalleryDesc(); ?>
	<?php printPageListWithNav("« ".gettext("prev"), gettext("next")." »"); ?>
			<div id="albums">
				<?php while (next_album()): ?>
					<div class="album">
							<div class="thumb">
							<a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo gettext('View album:'); ?> <?php printBareAlbumTitle();?>"><?php printCustomAlbumThumbImage(getBareAlbumTitle(), NULL, 95, 95, 95, 95); ?></a>
							 </div>
								<div class="albumdesc">
									<h3><a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo gettext('View album:'); ?> <?php printBareAlbumTitle();?>"><?php printAlbumTitle(); ?></a></h3>
									<?php printAlbumDate(""); ?>
									<div><?php echo shortenContent(getAlbumDesc(), 45,'...'); ?></div>
								</div>
					</div>
				<?php endwhile; ?>
		</div>
		<br style="clear: both" />
		<?php printPageListWithNav("« ".gettext("prev"), gettext("next")." »"); ?>

	<?php
} else { // news article loop
	printNewsPageListWithNav(gettext('next »'), gettext('« prev'),true,'pagelist',true);
	echo "<hr />";
	while (next_news()): ;?>
	 <div class="newsarticle">
			<h3><?php printNewsTitleLink(); ?><?php echo " <span class='newstype'>[".getNewsType()."]</span>"; ?></h3>
					<div class="newsarticlecredit"><span class="newsarticlecredit-left"><?php printNewsDate();?> | <?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?></span>
	<?php
	if(is_GalleryNewsType()) {
		if(!is_NewsType("album")) {
			echo " | ".gettext("Album:")."<a href='".getNewsAlbumURL()."' title='".getBareNewsAlbumTitle()."'> ".getNewsAlbumTitle()."</a>";
		} else {
			echo "<br />";
		}
	} else {
		printNewsCategories(", ",gettext("Categories: "),"newscategories");

	}
	?>
	</div>
			<?php printNewsContent(); ?>
			<?php printCodeblock(1); ?>
			<?php printTags('links', gettext('<strong>Tags:</strong>').' ', 'taglist', ', '); ?>
			</div>
	<?php
		endwhile;
		printNewsPageListWithNav(gettext('next »'), gettext('« prev'),true,'pagelist',true);
} ?>

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
printAdminToolbox();
zp_apply_filter('theme_body_close');
?>
</body>
</html>