<?php
/**
 * Utility to create simple text watermarks
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage example
 * @category package
 */
$plugin_description = gettext("Creates text based watermarks.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_URL = FULLWEBPATH . '//plugins/text_watermark/text_watermark.htm';
$option_interface = 'text_watermark';

/**
 * Plugin option handling class
 *
 */
class text_watermark {

	function __construct() {
		if (OFFSET_PATH == 2) {
			$fonts = zp_getFonts();
			$fon = array_shift($fonts);
			setOptionDefault('text_watermark_color', '#000000');
			setOptionDefault('text_watermark_font', $fon);
			setOptionDefault('text_watermark_text', 'Sample Text');
		}
	}

	function getOptionsSupported() {
		return array(gettext('Text') => array('key' => 'text_watermark_text', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext('Text for the watermark.')),
				gettext('Color') => array('key' => 'text_watermark_color', 'type' => OPTION_TYPE_COLOR_PICKER,
						'order' => 0,
						'desc' => gettext("Text color.")),
				gettext('Font') => array('key' => 'text_watermark_font', 'type' => OPTION_TYPE_SELECTOR,
						'order' => 1,
						'selections' => zp_getFonts(),
						'desc' => gettext('Watermark font.')),
				'' => array('key' => 'text_watermark_save', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 3,
						'desc' => gettext("Enter the text you wish for the watermark and choose the text color."))
		);
	}

	function handleOption($key, $cv) {
		$imageurl = getOption('text_watermark_text');
		if (!empty($imageurl)) {
			$imageurl = '<img src="' . FULLWEBPATH . '//plugins/text_watermark/createwatermark.php' .
							'?text_watermark_text=' . $imageurl .
							'&amp;text_watermark_font=' . rawurlencode(getOption('text_watermark_font')) .
							'&amp;text_watermark_color=' . rawurlencode(getOption('text_watermark_color')) .
							'&amp;transient" alt="" />';
		}
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			window.addEventListener('load', function () {

				$('#__text_watermark_font').change(function () {
					updatewm();
				});

				$('#__text_watermark_color').change(function () {
					updatewm();
				});

				$('#__text_watermark_text').change(function () {
					updatewm();
				});


			}, false);

			$('form.dirtylistening').removeClass('dirtylistening');	//	we have nothing needed to be saved

			function imgsrc() {

				var imgsrc = '<?php echo FULLWEBPATH; ?>/plugins/text_watermark/createwatermark.php'
								+ '?text_watermark_text=' + encodeURIComponent($('#__text_watermark_text').val())
								+ '&amp;text_watermark_font=' + encodeURIComponent($('#__text_watermark_font').val())
								+ '&amp;text_watermark_color=' + encodeURIComponent($('#__text_watermark_color').val());
				return imgsrc;
			}

			function updatewm() {
				$('#text_watermark_image_loc').html('<img src="' + imgsrc() + '&amp;transient" alt="" />');
			}
			function createwm() {
				$.ajax({
					cache: false,
					type: 'GET',
					url: imgsrc()
				});
				alert('<?php echo gettext('watermark created'); ?>');
			}
			// ]]> -->
		</script>

		<p class="buttons">
			<button type="button" title="<?php echo gettext('Create'); ?>" onclick="createwm();">
				<strong>
					<?php echo gettext('Create'); ?>
				</strong>
			</button>
			<span id="text_watermark_image_loc"><?php echo $imageurl ?></span>
		</p>
		<?php
	}

}
?>