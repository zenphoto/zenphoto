<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
if (class_exists('favorites')) {
	?>
	<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_head.php'); ?>
	<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_header.php'); ?>
	<div id="background-main" class="background">
		<div class="container<?php if (getOption('full_width')) {
		echo '-fluid';
	} ?>">
	<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_breadcrumbs.php'); ?>

			<div id="center" class="row" itemscope itemtype="http://schema.org/ImageGallery">

				<section class="col-sm-9" id="main" itemprop="mainContentOfPage">


					<h1><?php printAlbumTitle(); ?></h1>
					<p class="lead"><?php printAlbumDesc(); ?></p>

					<?php include("includes/_albumlist.php"); ?>

					<?php include("includes/_imagethumbs.php"); ?>
					<?php printPageListWithNav("« " . gettext("prev"), gettext("next") . " »"); ?>

	<?php @call_user_func('printRating'); ?>
	<?php @call_user_func('printCommentForm'); ?>

				</section>

	<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_sidebar.php'); ?>
			</div>
		</div>
	</div>

	<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_footer.php'); ?>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
?>