<?php

// force UTF-8 Ø

if (!defined('WEBPATH') || !class_exists('Zenpage')) die();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php if(!isset($ishomepage)) { echo getBarePageTitle(); } ?> | <?php echo getBareGalleryTitle(); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
	<?php printZenpageRSSHeaderLink("News","", "Zenpage news", ""); ?>
	<?php zp_apply_filter('theme_head'); ?>
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
	<h2><a href="<?php echo getGalleryIndexURL(false); ?>"><?php echo gettext("Index"); ?></a><?php if(!isset($ishomepage)) { printZenpageItemsBreadcrumb(" » ",""); } ?><strong><?php if(!isset($ishomepage)) { printPageTitle(" » "); } ?></strong>
	</h2>
	</div>
<div id="content-left">
<h2><?php printPageTitle(); ?></h2>
<?php
printPageContent();
printCodeblock(1);
if(getTags()) { echo gettext('<strong>Tags:</strong>'); } printTags('links', '', 'taglist', ', ');
?>
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
printAdminToolbox();
zp_apply_filter('theme_body_close');
?>
</body>
</html>