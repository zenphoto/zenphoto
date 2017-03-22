<?php
// force UTF-8 Ø

	if (!defined('WEBPATH'))
		die();
	if (class_exists('favorites')) {
?>
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_head.php'); ?>
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_header.php'); ?>
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_breadcrumbs.php'); ?>

<div id="center" class="row" itemscope itemtype="http://schema.org/ImageGallery">

	<div class="col-sm-3 hidden-xs"	id="sidebar">
		<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_sidebar.php'); ?>
	</div>	
	
	<div class="col-sm-9" id="main" itemprop="mainContentOfPage">
		<h1 itemprop="name">title</h1>

		<h1><?php printAlbumTitle(); ?></h1>
		<p class="lead"><?php printAlbumDesc(); ?></p>

		<?php include("includes/_albums.php"); ?>
		
		<?php printAddToFavorites($_zp_current_album, '', gettext('Remove')); ?>

		<?php include("includes/_image-thumb.php"); ?>
		<?php printPageListWithNav("« " . gettext("prev"), gettext("next") . " »"); ?>

		<?php @call_user_func('printRating'); ?>
		<?php @call_user_func('printCommentForm'); ?>
		
	</div>
	
</div>

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_footer.php'); ?>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>