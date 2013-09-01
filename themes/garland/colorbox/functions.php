<?php
/**
 * Colorbox personality
 */
// initialization stuff

if (zp_has_filter('theme_head', 'colorbox::css')) {
	$handler = new ga_colorbox();
} else {
	require_once(SERVERPATH . '/' . THEMEFOLDER . '/garland/image_page/functions.php');
}

class ga_colorbox {

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
		<div id="images">
			<?php
			$points = array();
			while (next_image()) {
				if ($map) {
					$coord = getGeoCoord($_zp_current_image);
					if ($coord) {
						$points[] = $coord;
					}
				}
				?>
				<div class="image">
					<div class="imagethumb">
						<?php
						if (isImagePhoto()) {
							// colorbox is only for real images
							$link = html_encode(getDefaultSizedImage()) . '" class="thickbox"';
						} else {
							$link = html_encode(getImageLinkURL()) . '"';
						}
						?>
						<a href="<?php echo $link; ?>" title="<?php echo sanitize(getImageTitle()); ?>">
				<?php printImageThumb(getImageTitle()); ?>
						</a></div>
				</div>
				<?php
			}
			?>
		</div>
		<br class="clearall" />
		<?php
		@call_user_func('printSlideShowLink');
	}

}
?>