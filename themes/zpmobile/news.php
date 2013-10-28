<?php
// force UTF-8 Ø
if (!defined('WEBPATH') || !class_exists('Zenpage')) die();
?>
<!DOCTYPE html>
<html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<?php printHeadTitle(); ?>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" />
	<?php jqm_loadScripts(); ?>
</head>

<body>
<?php zp_apply_filter('theme_body_open'); ?>


<div data-role="page" id="mainpage">

	<?php jqm_printMainHeaderNav(); ?>

<div data-role="content">
	<div class="content-primary">
		<h2 class="breadcrumb"><?php printNewsIndexURL(); ?><strong><?php printZenpageItemsBreadcrumb(' ',''); printCurrentNewsCategory(" "); ?><?php printNewsTitle(" "); printCurrentNewsArchive(" | "); ?></strong></h2>
		<?php
// single news article
if(is_NewsArticle()) {
	?>
  <?php
  printNewsContent();
  printCodeblock(1);
  ?>
  <br class="clearall" /><br />
  <?php printNewsCategories(', ',gettext('Categories: '),'catlist'); ?>
   <?php printTags('links', gettext('<strong>Tags:</strong>').' ', 'catlist', ', '); ?>
  	<?php
	if (function_exists('printCommentForm')) {
	  printCommentForm();
	}	?>
	<br class="clearall" />
	<?php if(getPrevNewsURL()) { $prevnews = getPrevNewsURL(); ?><a class="imgprevious" href="<?php echo html_encode($prevnews['link']); ?>" data-role="button" data-icon="arrow-l" data-iconpos="left" data-inline="true"><?php echo gettext("prev"); ?></a><?php } ?>
  <?php if(getNextNewsURL()) { $nextnews = getNextNewsURL();?><a class="imgnext" href="<?php echo html_encode($nextnews['link']); ?>" data-role="button" data-icon="arrow-r" data-iconpos="right" data-inline="true"><?php echo gettext("next"); ?></a><?php } ?>
	<?php if(getPrevNewsURL() || getNextNewsURL()) { ?><?php } ?>


<?php
 } else {
	 printNewsPageListWithNav(gettext('next »'), gettext('« prev'),true,'pagelist',true,7);
	 ?>
	 	<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
	 <?php
		 while (next_news()): ?>
			<li>
			<a href="<?php echo html_encode(jqm_getNewsLink()); ?>" title="<?php printBareNewsTitle(); ?>">
			<?php printNewsTitle(); ?> <small>(<?php printNewsDate();?>)</small>
			<div class="albumdesc"><?php echo shortenContent(strip_tags(getNewsContent()), 57,'(...)',false); ?></div>
			<?php jqm_printCombiNewsThumb(); ?>
    	</a>
    </li>
	<?php
  endwhile;
  ?>
  </ul>
	<?php printNewsPageListWithNav(gettext('next »'), gettext('« prev'),true,'pagelist',true,7);
 } ?>

 </div>
 <div class="content-secondary">
	<?php jqm_printMenusLinks(); ?>
 </div>
</div><!-- /content -->
<?php jqm_printBacktoTopLink(); ?>
<?php jqm_printFooterNav(); ?>
</div><!-- /page -->

<?php zp_apply_filter('theme_body_close');
?>
</body>
</html>
