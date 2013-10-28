<?php

// force UTF-8 Ø

if (!defined('WEBPATH') || !class_exists('Zenpage')) die();

?>
<!DOCTYPE html>
<html>
<head>
	<?php printHeadTitle(); ?>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
	<?php if (class_exists('RSS')) printRSSHeaderLink("News","Zenpage news", ""); ?>
	<?php zp_apply_filter('theme_head'); ?>
</head>

<body>
<?php zp_apply_filter('theme_body_open'); ?>

<div id="main">

	<div id="header">
			<h1><?php printGalleryTitle(); ?></h1>
			<?php
			if (getOption('Allow_search')) {
				if (is_NewsCategory()) {
					$catlist = array('news'=>array($_zp_current_category->getTitlelink()),'albums'=>'0','images'=>'0','pages'=>'0');
					printSearchForm(NULL, 'search', NULL, gettext('Search category'), NULL, NULL, $catlist);
				} else {
					$catlist = array('news'=>'1','albums'=>'0','images'=>'0','pages'=>'0');
					printSearchForm(NULL,"search","",gettext("Search news"), NULL, NULL, $catlist);
				}
			}
			?>
		</div>

<div id="content">

	<div id="breadcrumb">
	<h2>
		<a href="<?php echo getGalleryIndexURL(false); ?>"><?php echo gettext("Index"); ?></a>
		<?php printNewsIndexURL(NULL,' » '); ?><strong><?php printZenpageItemsBreadcrumb(' » ',''); printCurrentNewsCategory(" » "); ?><?php printNewsTitle(" » "); printCurrentNewsArchive(" » "); ?></strong>
	</h2>
	</div>

<div id="content-left">


<?php
// single news article
if(is_NewsArticle()) {
	?>
  <?php if(getPrevNewsURL()) { ?><div class="singlenews_prev"><?php printPrevNewsLink(); ?></div><?php } ?>
  <?php if(getNextNewsURL()) { ?><div class="singlenews_next"><?php printNextNewsLink(); ?></div><?php } ?>
  <?php if(getPrevNewsURL() OR getNextNewsURL()) { ?><br style="clear:both" /><?php } ?>
  <h3><?php printNewsTitle(); ?></h3>
  <div class="newsarticlecredit"><span class="newsarticlecredit-left"><?php printNewsDate();?> | <?php if (function_exists('getCommentCount')) { echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?> |<?php } ?> </span> <?php printNewsCategories(", ",gettext("Categories: "),"newscategories"); ?></div>
  <?php printNewsContent(); ?>
  <?php printTags('links', gettext('<strong>Tags:</strong>').' ', 'taglist', ', '); ?>
  <br style="clear:both;" /><br />
  <?php @call_user_func('printRating'); ?>
	<?php
	// COMMENTS TEST
	@call_user_func('printCommentForm');

} else {
printNewsPageListWithNav(gettext('next »'), gettext('« prev'),true,'pagelist',true);
echo "<hr />";
// news article loop
  while (next_news()): ;?>
 <div class="newsarticle">
    <h3><?php printNewsTitleLink(); ?><?php echo " <span class='newstype'>[".getNewsType()."]</span>"; ?></h3>
        <div class="newsarticlecredit">
        <span class="newsarticlecredit-left">
        	<?php
        	printNewsDate();
        	if (function_exists('getCommentCount')) {
	        	?>
	        	|
	        	<?php
	        	echo gettext("Comments:");
	        	?>
	        	<?php
	        	echo getCommentCount();
        	}
        	?></span>
<?php
if(is_GalleryNewsType()) {
	if(!is_NewsType("album")) {
		echo " | ".gettext("Album:")."<a href='".getNewsAlbumURL()."' title='".getBareNewsAlbumTitle()."'> ".getNewsAlbumTitle()."</a>";
	} else {
		echo "<br />";
	}
} else {
	echo ' | '; printNewsCategories(", ",gettext("Categories: "),"newscategories");
}
?>
</div>
    <?php printNewsContent(); ?>
    <?php printCodeblock(1); ?>
    <?php if(getTags()) { echo gettext('<strong>Tags:</strong>'); } printTags('links', '', 'taglist', ', '); ?>
    <br style="clear:both;" /><br />
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
zp_apply_filter('theme_body_close');
?>
</body>
</html>
