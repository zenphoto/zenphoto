<?php
/**
 * Image page personality
 */
// initialization stuff
$personality = new image_page();

class image_page {
	function __construct() {

	}

	function theme_head($_zp_themeroot) {
		return false;
	}

	function theme_bodyopen($_zp_themeroot) {

	}

	function theme_content($map) {
		global $_zp_current_image, $points;
		?>
		<!-- Image page section -->
		<div id="images">
			<?php
			$points = array();
			while (next_image()){
				if ($map) {
					$coord = getGeoCoord($_zp_current_image);
					if ($coord) {
						$coord['desc'] = '<p align=center>'.$coord['desc'].'</p>';
						$points[] = $coord;
					}
				}
				?>
				<div class="image">
					<div class="imagethumb"><a href="<?php echo html_encode(getImageLinkURL());?>" title="<?php echo sanitize(getImageTitle()); ?>"><?php printImageThumb(getImageTitle()); ?></a></div>
				</div>
				<?php
			}
			?>
		</div>
		<br clear="all">
		<?php
		@call_user_func('printSlideShowLink');
	}
}

?>