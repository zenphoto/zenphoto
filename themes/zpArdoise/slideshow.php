<?php include ('inc_header.php'); ?>

<div id="image-page" class="clearfix">
	<div id="headline" class="clearfix">


		<h3><?php printGalleryIndexURL(' » ', getGalleryTitle(), false); ?><?php printParentBreadcrumb('', ' » ', ' » '); ?><?php printAlbumBreadcrumb('', ' » '); ?><?php printImageTitle(); ?></h3>

	</div>

	<div id="image" class="clr">
		<?php printSlideShow(true, true); ?>
	</div>
	<br clear='both'><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />	<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
</div>

<br clear='all'>

<?php include('inc_footer.php'); ?>