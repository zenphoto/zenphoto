<?php
/**
 * Loads Colorbox JS and CSS scripts for selected theme page scripts.
 *
 * Note that this plugin does not attach Colorbox to any element because there are so many different options and usages.
 * You need to do this in your theme yourself. Visit the {@link http://www.jacklmoore.com/colorbox/ colorbox} site for information.
 *
 * The plugin has built in support for 5 example Colorbox themes shown below:
 *
 * 		<img src="%WEBPATH%/%ZENFOLDER%/%PLUGIN_FOLDER%/colorbox_js/themes/example1.jpg" />
 * 		<img src="%WEBPATH%/%ZENFOLDER%/%PLUGIN_FOLDER%/colorbox_js/themes/example2.jpg" />
 * 		<img src="%WEBPATH%/%ZENFOLDER%/%PLUGIN_FOLDER%/colorbox_js/themes/example3.jpg" />
 * 		<img src="%WEBPATH%/%ZENFOLDER%/%PLUGIN_FOLDER%/colorbox_js/themes/example4.jpg" />
 * 		<img src="%WEBPATH%/%ZENFOLDER%/%PLUGIN_FOLDER%/colorbox_js/themes/example5.jpg" />
 *
 * If you select <i>custom (within theme)</i> on the plugin option for Colorbox you need to place a folder
 * <i>colorbox</i> containing a <i>colorbox.css</i> file and a folder <i>images</i> within the current theme
 * to use a custom Colorbox theme.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage media
 */
$plugin_is_filter = 800 | THEME_PLUGIN;
$plugin_description = gettext('Loads Colorbox JS and CSS scripts for selected theme page scripts.');
$plugin_notice = gettext('Note that this plugin does not attach Colorbox to any element. You need to do this on your theme yourself.');
$plugin_author = 'Stephen Billard (sbillard)';
$option_interface = 'colorbox';

if (OFFSET_PATH) {
	zp_register_filter('admin_head', 'colorbox::css');
} else {
	if (in_array(stripSuffix($_zp_gallery_page), getSerializedArray(getOption('colorbox_' . $_zp_gallery->getCurrentTheme() . '_scripts')))) {
		zp_register_filter('theme_head', 'colorbox::css');
	}
}

class colorbox {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('colorbox_theme', 'example1');

			$found = array();
			$result = getOptionsLike('colorbox_');
			foreach ($result as $option => $value) {
				preg_match('/colorbox_(.*)_(.*)/', $option, $matches);
				if (count($matches) == 3 && $matches[2] != 'scripts') {
					if ($value) {
						$found[$matches[1]][] = $matches[2];
					}
					purgeOption('colorbox_' . $matches[1] . '_' . $matches[2]);
				}
			}
			foreach ($found as $theme => $scripts) {
				setOptionDefault('colorbox_' . $theme . '_scripts', serialize($scripts));
			}
		}
	}

	function getOptionsSupported() {
		global $_zp_gallery;
		$themes = getPluginFiles('colorbox_js/themes/*.*');
		$list = array('Custom (theme based)' => 'custom');
		foreach ($themes as $theme) {
			$theme = stripSuffix(basename($theme));
			$list[ucfirst($theme)] = $theme;
		}
		$opts = array(gettext('Colorbox theme') => array('key' => 'colorbox_theme', 'type' => OPTION_TYPE_SELECTOR,
						'order' => 0,
						'selections' => $list,
						'desc' => gettext("The Colorbox script comes with 5 example themes you can select here. If you select <em>custom (within theme)</em> you need to place a folder <em>colorbox_js</em> containing a <em>colorbox.css</em> file and a folder <em>images</em> within the current theme to override to use a custom Colorbox theme."))
		);
		$c = 10;
		$opts['note'] = array('key' => 'colorbox_note', 'type' => OPTION_TYPE_NOTE,
				'order' => $c,
				'desc' => gettext('<strong>NOTE:</strong> the plugin will automatically set the following options based on actual script page use. They may also be set by the themes themselves. It is unnecessary to set them here, but the first time used the JavaScript and CSS files may not be loaded and the colorbox not shown. Refreshing the page will then show the colorbox.')
		);
		foreach (getThemeFiles(array('404.php', 'themeoptions.php', 'theme_description.php')) as $theme => $scripts) {
			$list = array();
			foreach ($scripts as $script) {
				$list[$script] = stripSuffix($script);
			}
			$opts[$theme] = array('key' => 'colorbox_' . $theme . '_scripts', 'type' => OPTION_TYPE_CHECKBOX_ARRAYLIST,
					'order' => $c++,
					'checkboxes' => $list,
					'desc' => gettext('The scripts for which Colorbox is enabled.')
			);
		}

		return $opts;
	}

	function handleOption($option, $currentValue) {

	}

	/**
	 * Use by themes to declare which scripts should have the colorbox CSS loaded
	 *
	 * @param string $theme
	 * @param array $scripts list of the scripts
	 */
	static function registerScripts($scripts, $theme = NULL) {
		if (is_null($theme)) {
			list($theme, $creaator) = getOptionOwner();
		}
		setOptionDefault('colorbox_' . $theme . '_scripts', serialize($scripts));
	}

	/**
	 * Checks if the theme script is registered for colorbox. If not it will register the script
	 * so next time things will workl
	 *
	 * @global type $_zp_gallery
	 * @global type $_zp_gallery_page
	 * @param string $theme
	 * @param string $script
	 * @return boolean true registered
	 */
	static function scriptEnabled($theme, $script) {
		global $_zp_gallery, $_zp_gallery_page;
		$scripts = getSerializedArray(getOption('colorbox_' . $_zp_gallery->getCurrentTheme() . '_scripts'));
		if (!in_array(stripSuffix($_zp_gallery_page), $scripts)) {
			array_push($scripts, $script);
			setOption('colorbox_' . $theme . '_scripts', serialize($scripts));
			return false;
		}
		return true;
	}

	static function css() {
		global $_zp_gallery;
		$inTheme = false;
		if (OFFSET_PATH) {
			$themepath = 'colorbox_js/themes/example4/colorbox.css';
		} else {
			$theme = getOption('colorbox_theme');
			if (empty($theme)) {
				$themepath = 'colorbox_js/themes/example4/colorbox.css';
			} else {
				if ($theme == 'custom') {
					$themepath = zp_apply_filter('colorbox_themepath', 'colorbox_js/colorbox.css');
				} else {
					$themepath = 'colorbox_js/themes/' . $theme . '/colorbox.css';
				}
				$inTheme = $_zp_gallery->getCurrentTheme();
			}
		}
		$css = getPlugin($themepath, $inTheme, true);
		?>
		<link type="text/css" rel="stylesheet" href="<?php echo $css; ?>" />
		<script type="text/javascript" src="<?php echo FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/colorbox_js/jquery.colorbox-min.js"></script>
		<script type="text/javascript">
			/* Colorbox resize function for images */
			var resizeTimer;

			function resizeColorBoxImage() {
				if (resizeTimer)
					clearTimeout(resizeTimer);
				resizeTimer = setTimeout(function () {
					if (jQuery('#cboxOverlay').is(':visible')) {
						jQuery.colorbox.resize({width: '90%'});
						jQuery('#cboxLoadedContent img').css('max-width', '100%').css('height', 'auto');
					}
				}, 300)
			}
			/* Colorbox resize function for Google Maps*/
			function resizeColorBoxMap() {
				if (resizeTimer)
					clearTimeout(resizeTimer);
				resizeTimer = setTimeout(function () {
					var mapw = $(window).width() * 0.8;
					var maph = $(window).height() * 0.7;
					if (jQuery('#cboxOverlay').is(':visible')) {
						$.colorbox.resize({innerWidth: mapw, innerHeight: maph});
						$('#cboxLoadedContent iframe').contents().find('#map_canvas').css('width', '100%').css('height', maph - 20);
					}
				}, 500)
			}
			// Resize Colorbox when changing mobile device orientation
			window.addEventListener("orientationchange", function () {
				resizeColorBoxImage();
				parent.resizeColorBoxMap()
			}, false);

		</script>
		<?php
	}

}
?>