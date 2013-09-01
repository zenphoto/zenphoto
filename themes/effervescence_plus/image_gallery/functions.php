<?php
/**
 * Gallery personality
 */
// initialization stuff
$handler = new imagegallery();

class imagegallery {

	function __construct() {

	}

	function onePage() {
		return true;
	}

	function theme_head($_zp_themeroot) {
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . COMMON_FOLDER; ?>/adGallery/jquery.ad-gallery.css">
		<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . COMMON_FOLDER; ?>/adGallery/jquery.ad-gallery.min.js"></script>
		<?php
	}

	function theme_bodyopen($_zp_themeroot) {
		$location = getOption('effervescence_caption_location');
		?>
		<script type="text/javascript">
			$(function() {
				var galleries = $('.ad-gallery').adGallery({
					width: 600, // Width of the image, set to false and it will read the CSS width
					height: 400, // Height of the image, set to false and it will read the CSS height
					start_at_index: 0, // Which image should be displayed at first? 0 is the first image
					description_wrapper: <?php if ($location != 'image') { ?>$('#caption')<?php } else { ?>false<?php } ?>, // Either false or a jQuery object, if you want the image descriptions
					// to be placed somewhere else than on top of the image
					animate_first_image: false, // Should first image just be displayed, or animated in?
					animation_speed: 500, // Which ever effect is used to switch images, how long should it take?
					display_next_and_prev: true, // Can you navigate by clicking on the left/right on the image?
					display_back_and_forward: true, // Are you allowed to scroll the thumb list?
					scroll_jump: 0, // If 0, it jumps the width of the container
					loader_image: '<?php echo WEBPATH . '/' . ZENFOLDER . '/' . COMMON_FOLDER; ?>/adGallery/loader.gif',
					slideshow: {
						enable: true,
						autostart: true,
						speed: 5000,
						start_label: '<?php echo gettext('Start'); ?>',
						stop_label: '<?php echo gettext('Stop'); ?>',
						stop_on_scroll: true, // Should the slideshow stop if the user scrolls the thumb list?
						countdown_prefix: '(', // Wrap around the countdown
						countdown_sufix: ')',
					},
					effect: '<?php echo getOption('effervescence_transition'); ?>', // or 'slide-vert', 'resize', 'fade', 'none' or false
					enable_keyboard_move: true, // Move to next/previous image with keyboard arrows?
					cycle: true, // If set to false, you can't go from the last image to the first, and vice versa
					// All callbacks has the AdGallery objects as 'this' reference
					callbacks: {
						// Executes right after the internal init, can be used to choose which images
						// you want to preload
						// This gets fired right after the new_image is fully visible
						afterImageVisible: function() {
							// For example, preload the next image
							var context = this;
							this.preloadImage(this.current_index + 1,
											function() {
												// This function gets executed after the image has been loaded
												context.loading(false);
											}
							);
		<?php
		if ($location == 'separate') {
			?>
								$('#caption').show();
			<?php
		}
		?>
						},
						beforeImageVisible: function(new_image, old_image) {
		<?php
		if ($location == 'separate') {
			?>
								$('#caption').hide();
			<?php
		}
		?>
						}
					}
				});
			});
		</script>

		<?php
	}

	function theme_content($map) {
		global $_zp_current_image, $_zp_current_album, $points;
		if (isImagePage()) {
			?>
			<!-- Gallery section -->
			<div id="content">
				<div id="main">
					<div id="images">
						<?php
						$points = array();
						$firstImage = null;
						$lastImage = null;
						if (getNumImages() > 0) {
							?>
							<div id="gallery" class="ad-gallery">
								<div class="ad-image-wrapper"></div>
								<div class="ad-controls"></div>
								<div class="ad-nav">
									<div class="ad-thumbs">
										<ul class="ad-thumb-list">
											<?php
											while (next_image(true)) {
												if ($map) {
													$coord = getGeoCoord($_zp_current_image);
													if ($coord) {
														$points[] = $coord;
													}
												}
												if (isImagePhoto()) {
													// does not do video
													if (is_null($firstImage)) {
														$lastImage = imageNumber();
														$firstImage = $lastImage;
													} else {
														$lastImage++;
													}
													?>
													<li>
														<a href="<?php echo html_encode(getDefaultSizedImage()); ?>">
															<img src="<?php echo html_encode(pathurlencode(getImageThumb())); ?>"
																	 class="image<?php echo $lastImage; ?>"
																	 alt="<?php echo html_encode(getImageDesc()); ?>">
														</a>
													</li>
													<?php
												}
											}
											?>
										</ul>
									</div>
								</div>
							</div>
							<?php
						}
						?>

						<div id="caption"<?php if (getOption('effervescence_caption_location') == 'none') echo ' style="display:none"' ?>>
						</div>
						<div class="clearage"></div>
						<?php
						if (!empty($points) && $map) {

							function map_callback($map) {
								global $points;
								foreach ($points as $coord) {
									addGeoCoord($map, $coord);
								}
							}
							?>
							<div id="map_link">
							<?php printGoogleMap(NULL, NULL, NULL, 'album_page', 'map_callback'); ?>
							</div>
						<?php
					}
					?>
					</div><!-- images -->
			<?php if (function_exists('printAddToFavorites')) printAddToFavorites($_zp_current_album); ?>
			<?php @call_user_func('printRating'); ?>
				</div><!-- main -->
				<div class="clearage"></div>
			</div><!-- content -->
			<?php
		}
	}

}
?>
