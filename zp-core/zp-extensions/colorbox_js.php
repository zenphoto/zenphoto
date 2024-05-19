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
  * @deprecated 2.0
 * @author Stephen Billard (sbillard)
 * @package zpcore\plugins\colorboxjs
 */
$plugin_is_filter = 800 | THEME_PLUGIN;
$plugin_description = gettext('Loads Colorbox JS and CSS scripts for selected theme page scripts.');
$plugin_notice = gettext('Note that this plugin does not attach Colorbox to any element. You need to do this on your theme yourself.');
$plugin_author = 'Stephen Billard (sbillard)';
$plugin_deprecated = true;
$plugin_category = gettext('Media');
$option_interface = 'colorbox';


global $_zp_gallery, $_zp_gallery_page;
if (OFFSET_PATH) {
	zp_register_filter('admin_head', 'colorbox::css');
} else {
	zp_register_filter('theme_head', 'colorbox::css');
}

/**
 * @deprecated 2.0
 */
class colorbox {

	/**
	 * @deprecated 2.0
	 */
	function __construct() {
		//	These are best set by the theme itself!
		foreach (getThemeFiles(array('404.php', 'themeoptions.php', 'theme_description.php', 'slideshow.php', 'functions.php', 'password.php', 'sidebar.php', 'register.php', 'contact.php')) as $theme => $scripts) {
			foreach ($scripts as $script) {
				purgeOption('colorbox_' . $theme . '_' . stripSuffix($script));
			}
		}
		setOptionDefault('colorbox_theme', 'example1');
	}
	
	/**
	 * @deprecated 2.0
	 */
	function getOptionsSupported() {
		global $_zp_gallery;
		$themes = getPluginFiles('colorbox_js/themes/*.*');
		$list = array('Custom (theme based)' => 'custom');
		foreach ($themes as $theme) {
			$theme = stripSuffix(basename($theme));
			$list[ucfirst($theme)] = $theme;
		}
		$opts = array(gettext('Colorbox theme') => array(
						'key' => 'colorbox_theme',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 0,
						'selections' => $list,
						'desc' => gettext("The Colorbox script comes with 5 example themes you can select here. If you select <em>custom (within theme)</em> you need to place a folder <em>colorbox_js</em> containing a <em>colorbox.css</em> file and a folder <em>images</em> within the current theme to override to use a custom Colorbox theme."))
		);
		return $opts;
	}

		/**
	 * @deprecated 2.0
	 */
	function handleOption($option, $currentValue) {
		
	}

	/**
	 * @deprecated 2.0
	 */
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
		<link rel="stylesheet" href="<?php echo $css; ?>" type="text/css" />
		<script src="<?php echo FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/colorbox_js/jquery.colorbox-min.js"></script>
		<script>
			/* Colorbox resize function for images*/
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