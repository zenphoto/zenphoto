<?php include('inc_header.php'); ?>

<!-- wrap -->
<!-- container -->
<!-- header -->
<h3><?php printGalleryTitle(); ?></h3>
</div> <!-- / header -->

<div class="breadcrumb">
	<h4>
		<?php if (getOption('zpB_homepage')) { ?>
			<?php printCustomPageURL(getGalleryTitle(), 'gallery'); ?>
		<?php } else { ?>
			<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a>
		<?php } ?>&raquo;
		<?php printParentBreadcrumb('', ' » ', ' » '); ?>
		<?php printAlbumTitle(); ?>
	</h4>
</div>

<?php if (extensionEnabled('slideshow')) { ?>
	<ul class="pager hidden-phone pull-right"> <!--hidden-phone -->
		<li>
			<?php printSlideShowLink(gettext('Slideshow')); ?>
		</li>
	</ul>
<?php } ?>

<?php printPageListWithNav('«', '»', false, true, 'pagination', NULL, true, 7); ?>

<div class="page-header bottom-margin-reset">
	<p><?php printAlbumDesc(true); ?></p>
</div>

<?php include('inc_print_album_thumb.php'); ?>

<?php include('inc_print_image_thumb.php'); ?>

<?php printPageListWithNav('«', '»', false, true, 'pagination', NULL, true, 7); ?>

<?php if ((zp_loggedin()) && (extensionEnabled('favoritesHandler'))) { ?>
	<div class="favorites"><?php printAddToFavorites($_zp_current_album); ?></div>
<?php } ?>

<?php
if (extensionEnabled('GoogleMap')) {
	// theme doesnot support colorbox option for googlemap plugin
	// display map only if they are geodata
	if ((getOption('gmap_display') == 'hide') || (getOption('gmap_display') == 'show')) {
		$hasAlbumGeodata = false;
		$album = $_zp_current_album;
		$images = $album->getImages();

		foreach ($images as $an_image) {
			$image = newImage($album, $an_image);
			$exif = $image->getMetaData();
			$geo = $image->get('GPSLatitude') && $image->get('GPSLongitude');
			if ($geo) {
				$hasAlbumGeodata = true; // at least one image has geodata
			}
		}

		if ($hasAlbumGeodata == true) {
			if (getOption('gmap_display') == 'hide') {
				$gmap_display = 'zB_hide';
			} else if (getOption('gmap_display') == 'show') {
				$gmap_display = 'zB_show';
			}
			?>
			<div class="accordion" id="gmap_accordion">
				<div class="accordion-heading" id="<?php echo $gmap_display; ?>">
					<a class="accordion-toggle" data-toggle="collapse" data-parent="#gmap_accordion" href="#zpB_googlemap_data" title="<?php echo gettext('Display or hide the Google Map.'); ?>">
						<i class="icon-map-marker"></i><?php echo gettext('Google Map'); ?>
					</a>
					<?php printGoogleMap(NULL, 'googlemap'); ?>
					<script type="text/javascript">
						jQuery(document).ready(function ($) {
							$('#zpB_googlemap_data').collapse(
											'<?php echo $gmap_display; ?>'
											);
						});
					</script>
				</div>
			</div>
			<?php
		}
	}
}
?>

<?php if (extensionEnabled('comment_form')) { ?>
	<?php include('inc_print_comment.php'); ?>
<?php } ?>

<?php include('inc_footer.php'); ?>