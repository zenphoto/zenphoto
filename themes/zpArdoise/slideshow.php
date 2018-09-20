<?php include ('inc_header.php'); ?>

<div id="image-page" class="clearfix">
	<div id="headline" class="clearfix">

		<h3><?php printGalleryIndexURL(' » ', getGalleryTitle(), false); ?><?php printParentBreadcrumb('', ' » ', ' » '); ?><?php printAlbumBreadcrumb('', ' » '); ?><?php echo gettext('Slideshow'); ?></h3>

	</div>

	<div class="slideshow-container">

		<?php printSlideShow(true, false, $albumobj = $_zp_current_album, NULL, NULL, NULL, false, false, false, true); ?>


	</div>



</div>

<?php include('inc_footer.php'); ?>