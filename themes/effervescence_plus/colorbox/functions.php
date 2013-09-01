<?php
/**
 * Colorbox personality
 */
// initialization stuff

if (zp_has_filter('theme_head', 'colorbox::css')) {
	$handler = new ef_colorbox();
} else {
	require_once(SERVERPATH . '/' . THEMEFOLDER . '/effervescence_plus/image_page/functions.php');
}

class ef_colorbox {

	function __construct() {

	}

	function onePage() {
		return false;
	}

	function theme_head($_zp_themeroot) {
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			$(document).ready(function() {
				$("a.thickbox").colorbox({
					maxWidth: "98%",
					maxHeight: "98%",
					photo: true,
					close: '<?php echo gettext("close"); ?>'
				});
			});
			// ]]> -->
		</script>
		<?php
	}

	function theme_bodyopen($_zp_themeroot) {

	}

	function theme_content($map) {
		global $_zp_current_image, $points;
		?>
		<!-- Colorbox section -->
		<div id="content">
			<div id="main">
				<div id="images">
					<?php
					$points = array();
					$firstImage = null;
					$lastImage = null;
					while (next_image()) {
						// Colorbox does not do video
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
								if (isImagePhoto()) {
									// colorbox is only for real images
									echo '<a href="' . html_encode(getDefaultSizedImage()) . '" class="thickbox"';
								} else {
									echo '<a href="' . html_encode(getImageLinkURL()) . '"';
								}
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
			<?php @call_user_func('printRating'); ?>
			</div><!-- main -->
			<div class="clearage"></div>
		<?php if (isset($firstImage)) printNofM('Photo', $firstImage, $lastImage, getNumImages()); ?>
		</div><!-- content -->
		<?php
	}

}
?>