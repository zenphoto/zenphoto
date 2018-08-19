<?php include('inc_header.php'); ?>

	<!-- .container main -->
		<!-- .page-header -->
			<!-- .header -->
				<h3><?php printGalleryTitle(); ?></h3>
			</div><!-- .header -->
		</div><!-- /.page-header -->

		<div class="breadcrumb">
			<h4>
				<?php printGalleryIndexURL(' » ', getGalleryTitle(), false); ?><?php printParentBreadcrumb('', ' » ', ' » '); ?><?php printAlbumTitle(); ?>
			</h4>
		</div>

		<div class="page-header margin-bottom-reset">
			<?php printAlbumDesc(true); ?>
		</div>

		<!-- TO DO : à revoir -->
		<?php if (extensionEnabled('slideshow')) { ?>
		<ul class="pager pull-right hidden-phone margin-bottom-reset"> <!--hidden-phone -->
			<li>
				<?php printSlideShowLink(gettext('Slideshow')); ?>
			</li>
		</ul>
		<?php } ?>

		<?php printPageListWithNav('«', '»', false, true, 'pagination pagination-sm', NULL, true, 7); ?>

		<?php if (isAlbumPage()) { ?>
			<?php include('inc_print_album_thumb.php'); ?>
		<?php } ?>

		<?php if (isImagePage()) { ?>
			<?php include('inc_print_image_thumb.php'); ?>
		<?php } ?>

		<?php printPageListWithNav('«', '»', false, true, 'pagination pagination-sm', NULL, true, 7); ?>

		<?php if ((zp_loggedin()) && (extensionEnabled('favoritesHandler'))) { ?>
			<div class="favorites panel-group" role="tablist">
				<?php printAddToFavorites($_zp_current_album); ?>
			</div>
		<?php } ?>

		<?php if(extensionEnabled('GoogleMap')) {
			// theme doesnot support colorbox option for googlemap plugin
			if (getOption('gmap_display') == 'colorbox') { ?>
				<div class="alert alert-danger">theme doesn't support colorbox option for googlemap plugin</div>
			<?php }
			// display map only if they are geodata
			if ((getOption('gmap_display') == 'hide') || (getOption('gmap_display') == 'show')) {
				$hasAlbumGeodata = false;
				$album = $_zp_current_album;
				$images = $album->getImages();

				foreach ($images as $an_image) {
					$image = newImage($album, $an_image);
					$exif = $image->getMetaData();
					if ((!empty($exif['EXIFGPSLatitude'])) && (!empty($exif['EXIFGPSLongitude']))) {
						$hasAlbumGeodata = true; // at least one image has geodata
					}
				}

				if ($hasAlbumGeodata == true) {
					if (getOption('gmap_display') == 'hide') {
						$gmap_display = 'gmap_hide';
					} else if (getOption('gmap_display') == 'show') {
						$gmap_display = 'gmap_show';
					}
					?>
					<div id="gmap_accordion" class="panel-group" role="tablist">
						<div class="panel panel-default">
							<div id="gmap_heading" class="panel-heading" role="tab">
								<h4 class="panel-title">
									<a id="<?php echo $gmap_display; ?>" data-toggle="collapse" data-parent="#gmap_accordion" href="#gmap_collapse_data">
										<span class="glyphicon glyphicon-map-marker"></span>&nbsp;<?php echo gettext('Google Map'); ?>
									</a>
								</h4>
							</div>
						</div>
						<?php printGoogleMap('', 'gmap_collapse'); ?>
						<script type="text/javascript">
						//<![CDATA[
							;$('#gmap_collapse_data').on('show.bs.collapse', function () {
								$('.hidden_map').removeClass('hidden_map');
							})
						//]]>
						</script>
					</div>
					<?php
				}
			}
		}
		?>

		<?php if (extensionEnabled('comment_form')) { ?>
			<?php include('inc_print_comment.php'); ?>
		<?php } ?>

	</div><!-- /.container main -->

<?php include('inc_footer.php'); ?>