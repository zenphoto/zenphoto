<?php

/**
 *
 * Removes the watermark HTML from the DOM if the user does not have <var>ADMIN_RIGHTS</var>
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage example
 * @category package
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Disable setting watermarks if user does not have ADMIN_RIGHTS.");
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('admin_note', 'disableWatermark::customData');
zp_register_filter('plugin_tabs', 'disableWatermark::tab');

class disableWatermark {

	static function customData($tab, $subtab) {
		global $_zp_admin_tab;
		if (!zp_loggedin(ADMIN_RIGHTS) && $_zp_admin_tab == 'edit') {
			?>
			<?php

			switch ($subtab) {
				case 'imageinfo':
					?>
					<script type="text/javascript">
						// <!-- <![CDATA[
						$(window).load(function () {
							var num = $('input[name=totalimages]').val();
							for (i = 0; i < num; i++) {
								var selector = $('#image_watermark-' + i);
								var selected = $('#image_watermark-' + i + ' option:selected');
								selector.before('<input type="hidden" name="image_watermark-' + i + '" value="' + selected.val() + '" />' + selected.text());
								selector.remove();
							}
						});
						// ]]> -->
					</script>
					<?php

					break;
				case 'albuminfo':
					?>
					<script type="text/javascript">
						// <!-- <![CDATA[
						$(window).load(function () {
							var selector = $('#album_watermark');
							var selected = $('#album_watermark option:selected');
							selector.before('<input type="hidden" name="album_watermark" value="' + selected.val() + '" />' + selected.text());
							selector.remove();
							selector = $('#album_watermark_thumb');
							selected = $('#album_watermark_thumb option:selected');
							selector.before('<input type="hidden" name="album_watermark_thumb" value="' + selected.val() + '" />' + selected.text());
							selector.remove();
						});
						// ]]> -->
					</script>
					<?php

					break;
				default:
					if (isset($_GET['massedit'])) {
						?>
						<script type="text/javascript">
							// <!-- <![CDATA[
							$(window).load(function () {
								var num = $('input[name=totalalbums]').val();
								for (i = 1; i <= num; i++) {
									var selector = $('#album_watermark_' + i);
									var selected = $('#album_watermark_' + i + ' option:selected');
									selector.before('<input type="hidden" name="album_watermark_' + i + '" value="' + selected.val() + '" />' + selected.text());
									selector.remove();
									selector = $('#album_watermark_thumb_' + i);
									selected = $('#album_watermark_thumb_' + i + ' option:selected');
									selector.before('<input type="hidden" name="album_watermark_thumb_' + i + '" value="' + selected.val() + '" />' + selected.text());
									selector.remove();
								}
							});
							// ]]> -->
						</script>
						<?php

					}
					break;
			}
		}
	}

	static function tab($xlate) {
		$xlate['demo'] = gettext('demo');
		return $xlate;
	}

}
?>