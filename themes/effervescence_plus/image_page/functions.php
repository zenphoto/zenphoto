<?php
/**
 * Image page personality
 */
// initialization stuff
$handler = new image_page();

class image_page {

	function __construct() {

	}

	function onePage() {
		return false;
	}

	function theme_head($_zp_themeroot) {
		return false;
	}

	function theme_bodyopen($_zp_themeroot) {

	}

	function theme_content($map) {
		global $_zp_current_image, $_zp_current_album, $points;
		?>
		<!-- Image page section -->
		<div id="content">
			<div id="main">
				<div id="images">
					<?php
					$points = array();
					$firstImage = null;
					$lastImage = null;
					while (next_image()) {
						if (is_null($firstImage)) {
							$lastImage = imageNumber();
							$firstImage = $lastImage;
						} else {
							$lastImage++;
						}
						?>
						<div class="image">
							<div class="imagethumb">
								<?php
								if ($map) {
									$coord = getGeoCoord($_zp_current_image);
									if ($coord) {
										$points[] = $coord;
									}
								}
								$annotate = annotateImage();
								echo '<a href="' . html_encode(getImageLinkURL()) . '"';
								echo " title=\"" . $annotate . "\">\n";
								printImageThumb($annotate);
								echo "</a>";
								?>
							</div>
						</div>
						<?php
					}
					echo '<div class="clearage"></div>';
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
					@call_user_func('printSlideShowLink', NULL, 'text-align:center;');
					?>
				</div><!-- images -->
			<?php if (function_exists('printAddToFavorites')) printAddToFavorites($_zp_current_album); ?>
			<?php @call_user_func('printRating'); ?>
			</div> <!-- main -->
			<div class="clearage"></div>
		<?php if (isset($firstImage)) printNofM('Photo', $firstImage, $lastImage, getNumImages()); ?>
		</div> <!-- content -->
		<?php
	}

}
?>