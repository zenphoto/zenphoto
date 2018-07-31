<?php
/**
 * Displays a zoomable image based on {@link http://jacklmoore.com/zoom/ jQuery Zoom} by Jack Moore.
 *
 * To place a zoomable image on your web page call the <code>printZoomImage()</code> function.
 * The viewer may then <eM>zoom in</em> on the image using mouse or touch/press actions. There are four behaviors
 * for the zooming. See the plugin options descriptions for details.
 *
 * Zooming is done using the fullsized image. If there is no significant difference
 * between the sizes of the displayed image and the fullsized image the zoom is
 * ineffective.
 *
 * The optional parameters to <code>printZoomImage()</code> are:
 * <dl>
 * <dt><em>size</em></dt>
 * <dd>size of dispayed image (defaults to the <code>Image size</code> option)</dd>
 * <dt><em>type</em></dt>
 * <dd>zoom activation thpe (defaults to the plugin's <code>Default type</code> option)</dd>
 * <dt><em>magnify</em></dt>
 * <dd>magnification of the fullsized image displayed during the zoom (defaults to <code>1</code>)</dd>
 * <dt><em>target</em></dt>
 * <dd>CSS <em>id</em> of the DOM element where zoomed image will be displayed (defaults to displaying over the zoomable image)</dd>
 * <dt><em>image</em></dt>
 * <dd>image to display (defaults to <code>$_zp_current_image</code>)</dd>
 * <dt><em>id</em></dt>
 * <dd>CSS <em>id</em> for the zoomable image</dd>
 * <dt><em>class</em></dt>
 * <dd>CSS <em>class</em> for the zoomable image</em></dd>
 * <dt><em>alt</em></dt>
 * <dd>CSS <em>alt</em> for the zoomable image</dd>
 * <dt><em>title</em></dt>
 * <dd>CSS <em>title</em> for the zoomable image</dd>
 * </dl>
 *
 * <strong>Note:</strong> <code>printCustomSizedImage()</code> is used to display the image.
 *
 *
 * @author Stephen Billard (sbillard)
 * @Copyright 2018 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20 and derivatives}
 *
 * @package plugins/zoomImage
 * @pluginCategory media
 */
$plugin_description = gettext("Provides a function to display an image that can be zoomed by the viewer.");

$option_interface = 'zoomImage';

require_once(SERVERPATH . '/' . ZENFOLDER . '/functions-image.php');

zp_register_filter('theme_head', 'zoomImage::head');
zp_register_filter('theme_body_close', 'zoomImage::body_close');

class zoomImage {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('zoomImage_type', 'hover');
		}
	}

	function getOptionsSupported() {
		$options = array(
				gettext('Default type') => array('key' => 'zoomImage_type', 'type' => OPTION_TYPE_RADIO,
						'buttons' => array(gettext('Hover') => 'hover', gettext('Grab') => 'grab', gettext('Click') => 'click', gettext('Toggle') => 'toggle'),
						'desc' => gettext('Select the default for how the viewer activates zooming the image.') . '<br />' . gettext('<code>Hover</code>: Mouse-over enlarges the area under the cursor.') . '<br />' . gettext('<code>Grab</code>: Mouse press enlarges the image and "grabs" it for zoom focus.') . '<br />' . gettext('<code>Click</code>: Similar to hover, but is activated by a mouse click.' . '<br />' . gettext('<code>Toggle</code>: Enlarges the clicked area.'))
				)
		);
		return $options;
	}

	static function head($html) {
		?>
		<link rel="stylesheet" href="<?php echo getPlugin('zoomImage/zoom.css', true, true) ?>" type="text/css" />
		<script src='<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/zoomImage/jquery.zoom.min.js'></script>
		<?php
	}

	static function body_close($html) {
		global $_zoomImage_id_list;
		?>
		<script type="text/javascript">
		<?php
		if (!empty($_zoomImage_id_list)) {
			foreach ($_zoomImage_id_list as $id => $param) {
				?>
					$('#<?php echo $id; ?>').zoom(<?php echo $param; ?>);
				<?php
			}
		}
		?>
		</script>
		<?php
	}

}

/**
 * Prints a zoomable image
 *
 * @global type $_zp_current_image
 * @global type $_zoomImage_ID
 * @param int $size size to print the image
 * @param string $type zoom activation type
 * @param float $magnify magnification of the fullsized image
 * @param string $target ID of DOM element where zoomed image should be displayed
 * @param object $image
 * @param string $id
 * @param string $class
 * @param string $alt
 * @param string $title
 */
function printZoomImage($size = NULL, $type = NULL, $magnify = NULL, $target = NULL, $image = NULL, $id = NULL, $class = NULL, $alt = NULL, $title = NULL) {
	global $_zp_current_image, $_zoomImage_ID, $_zoomImage_id_list;
	if (is_null($image)) {
		$image = $_zp_current_image;
	}
	if (is_null($size)) {
		$size = getOption('image_size');
	}
	if (is_null($type)) {
		$type = getOption('zoomImage_type');
	}
	$disposal = getOption('protect_full_image');
	if ($disposal == 'Download') {
		//we need the actual image
		$disposal = 'Protected view';
	}

	$zid = 'zoomImageID_' . ++$_zoomImage_ID;
	$link = getFullImageURL($image);
	$params = array("url: '" . $link . "'");
	if ($type != 'hover') {
		$params[] = "on: '" . $type . "'";
	}
	if ($magnify) {
		$params[] = "magnify: " . $magnify;
	}
	if ($target) {
		$params[] = "target: $('#" . $target . "').get(0)";
	}

	$_zoomImage_id_list[$zid] = '{' . implode(', ', $params) . '}';
	?>
	<span class="zoom zoom<?php echo ucfirst($type); ?>" id="<?php echo $zid; ?>">
		<?php printCustomSizedImage($alt, $size, NULL, NULL, NULL, NULL, NULL, NULL, $class, $id, false, NULL, $title); ?>
	</span>
	<?php
}
