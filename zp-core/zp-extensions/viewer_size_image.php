<?php
/**
 * Provides a means where visitors can select the size of the image on the image page.
 *
 * The default size and list of allowed sizes may be set in the plugin options or
 * passed as a parameter to the support functions.
 *
 * The user selects a size to view from a radio button list. This size is then saved in
 * a cookie and used as the default for future image viewing.
 *
 * Sizes as used for the default size and the allowed size list are strings with the
 * The form is <var>$s=<i>size</i></var> or <var>$h=<i>heigh</i>; $w=<i>width</i>;</var>.... See printCustomSizedImage() for
 * information about how these values are used.
 *
 * If <var>$s</var> is present, the plugin will use printCustomSizedImage() to display the image. Otherwise
 * both <var>$w</var> and <var>$h</var> must be present. Then printCustomSizedImageMaxSpace() is used for
 * displaying the image.
 *
 * You must place calls on <var>printUserSizeSelector()</var> and <var>printUserSizeImage()</var> at appropriate
 * places in your theme's image.php script to activate these features.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_description = gettext("Provides a means allowing users to select the image size to view.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'viewer_size_image_options';

/**
 * Plugin option handling class
 *
 */
class viewer_size_image_options {

	function viewer_size_image_options() {
		$default = getOption('image_size');
		setOptionDefault('viewer_size_image_sizes', '$s=' . ($default - 200) . '; $s=' . ($default - 100) . '; $s=' . ($default) . '; $s=' . ($default + 100) . '; $s=' . ($default + 200) . ';');
		setOptionDefault('viewer_size_image_default', '$s=' . $default);
		setOptionDefault('viewer_size_image_radio', 2);
		if (class_exists('cacheManager')) {
			cacheManager::deleteThemeCacheSizes('viewer_size_image');
			cacheManager::addThemeCacheSize('viewer_size_image', $default - 200, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
			cacheManager::addThemeCacheSize('viewer_size_image', $default - 100, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
			cacheManager::addThemeCacheSize('viewer_size_image', $default, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
			cacheManager::addThemeCacheSize('viewer_size_image', $default + 100, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
			cacheManager::addThemeCacheSize('viewer_size_image', $default + 200, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
		}
	}

	function getOptionsSupported() {
		return array(gettext('Image sizes allowed') => array('key'					 => 'viewer_size_image_sizes', 'type'				 => OPTION_TYPE_TEXTAREA,
										'multilingual' => false,
										'desc'				 => gettext('List of sizes from which the viewer may select.<br />The form is "$s=&lt;size&gt;" or "$h=&lt;height&gt;,$w=&lt;width&gt;"....<br />See printCustomSizedImage() for details')),
						gettext('Selector')						 => array('key'			 => 'viewer_size_image_radio', 'type'		 => OPTION_TYPE_RADIO,
										'buttons'	 => array(gettext('Radio buttons') => 2, gettext('Drop-down') => 1),
										'desc'		 => gettext('Choose the kind of selector to be presented the viewer.')),
						gettext('Default size')				 => array('key'	 => 'viewer_size_image_default', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext('The initial size for the image. Format is a single instance of the sizes list.'))
		);
	}

	function handleOption($option, $currentValue) {

	}

}

if (!OFFSET_PATH) {
	$saved = @$_COOKIE['viewer_size_image_saved']; //	This cookie set by JavaScript, so not bound to the IP. cannot use zp_getCookie()
	if (empty($saved)) {
		$postdefault = trim(getOption('viewer_size_image_default'));
	} else {
		$_POST['viewer_size_image_selection'] = true; // ignore default size
		$postdefault = $saved;
	}
}

/**
 * prints the radio button image size selection list
 *
 * @param string $text text to introduce the radio button list
 * @param string $default the default (initial) for the image sizing
 * @param array $usersizes an array of sizes which may be choosen.
 */
function printUserSizeSelector($text = '', $default = NULL, $usersizes = NULL) {
	$size = $width = $height = NULL;
	getViewerImageSize($default, $size, $width, $height);
	if (!empty($size)) {
		$current = $size;
	} else {
		$current = $width . 'x' . $height;
	}
	$sizes = array();
	if (empty($text))
		$text = gettext('Select image size');
	if (is_null($usersizes)) {
		$inputs = explode(';', trim(getOption('viewer_size_image_sizes')));
		if (!empty($inputs)) {
			foreach ($inputs as $size) {
				if (!empty($size)) {
					$size = str_replace(',', ';', $size) . ';';
					$s = $w = $h = NULL;
					if (false === eval($size)) {
						trigger_error(gettext('There is a format error in your <em>viewer_size_image_sizes</em> option string.'), E_USER_NOTICE);
					}
					if (!empty($s)) {
						$key = $s;
					} else {
						$key = $w . 'x' . $h;
					}
					$sizes[$key] = array('$s' => $s, '$h' => $h, '$w' => $w);
				}
			}
		}
	} else {
		foreach ($usersizes as $key => $size) {
			if (!empty($size)) {
				$size = str_replace(',', ';', $size) . ';';
				$s = $w = $h = NULL;
				if (false === eval($size)) {
					trigger_error(gettext('There is a format error in your $usersizes string.'), E_USER_NOTICE);
				}
				if (!empty($s)) {
					$key = $s;
				} else {
					$key = $w . 'x' . $h;
				}
				$sizes[$key] = array('$s' => $s, '$h' => $h, '$w' => $w);
			}
		}
	}
	if (($cookiepath = WEBPATH) == '')
		$cookiepath = '/';
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
	<?php
	$selector = getOption('viewer_size_image_radio') == 1;
	if ($selector) {
		?>
			function switchselection() {
				var selection = $("#viewer_size_image_selection").val();
				var items = selection.split(':');
				$('#image img').attr('width', items[1]);
				$('#image img').attr('height', items[2]);
				$('#image img').attr('src', items[3]);
				document.cookie = 'viewer_size_image_saved=' + items[0] + '; expires=<?php echo date('Y-m-d H:i:s', time() + COOKIE_PESISTENCE); ?>; path=<?php echo $cookiepath ?>';
			}
		<?php
	} else { //	radio buttons
		?>
			function switchimage(obj) {
				var url = $(obj).attr('url');
				var w = $(obj).attr('im_w');
				var h = $(obj).attr('im_h');
				$('#image img').attr('width', w);
				$('#image img').attr('height', h);
				$('#image img').attr('src', url);
				document.cookie = 'viewer_size_image_saved=' + $(obj).attr('value') + '; expires=<?php echo date('Y-m-d H:i:s', time() + COOKIE_PESISTENCE); ?>; path=<?php echo $cookiepath ?>';
			}
		<?php
	}
	?>
		// ]]> -->
	</script>
	<div>
			<?php
			echo $text;
			if ($selector) {
				?>
			<select id="viewer_size_image_selection" name="viewer_size_image_selection" onchange="switchselection();" >
				<?php
			}
			foreach ($sizes as $key => $size) {
				if (empty($size['$s'])) {
					$display = sprintf(gettext('%1$s x %2$s px'), $size['$w'], $size['$h']);
					$url = getCustomImageURL(null, $size['$w'], $size['$h'], null, null, null, null, false);
					$value = '$h=' . $size['$h'] . ',$w=' . $size['$w'];
					$dims = array($size['$w'], $size['$h']);
				} else {
					$dims = getSizeCustomImage($size['$s']);
					$display = sprintf(gettext('%s px'), $size['$s']);
					$url = getCustomImageURL($size['$s'], null, null, null, null, null, null, false);
					$value = '$s=' . $size['$s'];
				}
				if ($selector) {
					$selected = '';
					if ($key == $current) {
						$selected = ' selected="selected"';
					}
					?>
					<option id="s<?php echo $key; ?>" value="<?php echo $value . ':' . implode(':', $dims) . ':' . $url; ?>"<?php echo $selected; ?> />
					<?php echo $display; ?>
					</option>
					<?php
				} else {
					$checked = "";
					if ($key == $current) {
						$checked = ' checked="checked"';
					}
					?>
					<input type="radio" name="viewer_size_image_selection" id="s<?php echo $key; ?>" url="<?php echo $url; ?>"
								 im_w="<?php echo $dims[0]; ?>" im_h="<?php echo $dims[1]; ?>"
								 value="<?php echo $value; ?>"<?php echo $checked; ?> onclick="switchimage(this);" />
					<label for="s<?php echo $key; ?>"> <?php echo $display; ?></label>
					<?php
				}
			}
			if ($selector) {
				?>
			</select>
		<?php
	}
	?>
	</div>
	<?php
}

/**
 * returns the current values for the image size or its height & width
 * This information comes form (in order of priority)
 *   1. The posting of a radio button selection
 *   2. A cookie stored from #1
 *   3. The default (either as passed, or from the plugin option.)
 *
 * The function is used internally, so the above priority determines the
 * image sizing.
 *
 * @param string $default the default (initial) value for the image sizing
 * @param int $size The size of the image (Width and Height are NULL)
 * @param int $width The width of the image (size is null)
 * @param int $height The height of the image (size is null)
 */
function getViewerImageSize($default, &$size, &$width, &$height) {
	global $postdefault;
	if (isset($_POST['viewer_size_image_selection']) || empty($default)) {
		$msg = gettext('There is a format error in user size selection');
		$validate = $postdefault;
	} else {
		$msg = gettext('There is a format error in your $default parameter');
		$validate = $default;
	}
	$size = $width = $height = NULL;
	preg_match_all('/(\$[shw])[\s]*=[\s]*([0-9]+)/', $validate, $matches);
	if ($matches) {
		foreach ($matches[0] as $key => $str) {
			switch ($matches[1][$key]) {
				case '$s':
					$size = $matches[2][$key];
					break;
				case '$w':
					$width = $matches[2][$key];
					break;
				case '$h':
					$height = $matches[2][$key];
					break;
			}
		}

		if (!empty($size)) {
			$width = $height = NULL;
		} else {
			$size = NULL;
		}
	}
	if (empty($size) && empty($width) && empty($height)) {
		trigger_error($msg, E_USER_NOTICE);
	}
}

/**
 * prints the image according to the size chosen
 *
 * @param string $alt alt text for the img src Tag
 * @param string $default the default (initial) value for the image sizing
 * @param string $class if not empty will be used as the image class tag
 * @param string $id if not empty will be used as the image id tag
 */
function printUserSizeImage($alt, $default = NULL, $class = NULL, $id = NULL) {
	$size = $width = $height = NULL;
	getViewerImageSize($default, $size, $width, $height);
	if (empty($size)) {
		printCustomSizedImageMaxSpace($alt, $width, $height, $class, $id);
	} else {
		printCustomSizedImage($alt, $size, $width, $height, NULL, NULL, NULL, NULL, $class, $id, false);
	}
}
?>
