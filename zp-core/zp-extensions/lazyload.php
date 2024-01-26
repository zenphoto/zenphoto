<?php
/**
 * Loads the lazysizes <a href="https://github.com/aFarkas/lazysizes" rel="nooopener" target="_blank">lazysizes</a>) script for primarily image lazyloading 
 * for front and/or backend additionally as a fallback to the native browser lazy loading using loading="lazy". 
 * 
 * The plugin attaches itself automatically to standard print* image template functions via filters and requires no theme changes. It 
 * also attaches to the general adminthumb functon used on the backend.
 * It additionally adds a no-script fallback and supports native lazyloading in very modern browsers by adding the loading="lazy" if not existing. 
 * 
 * @author Malte Müller (acrylian)
 * @package zpcore\plugins\lazyload
 */

$plugin_is_filter = 800 | THEME_PLUGIN | ADMIN_PLUGIN;
$plugin_description = gettext('Provides lazyloading for theme and backend using standard template functions using <a href="https://github.com/aFarkas/lazysizes" rel="nooopener" target="_blank">lazysizes</a>');
$plugin_author = 'Malte Müller (acrylian)';
$plugin_disable = false;
$plugin_category = gettext('Media');

$option_interface = "lazyloadOptions";

//frontend
zp_register_filter('theme_head', 'lazyload::getJS');
zp_register_filter('standard_image_attr', 'lazyload::filterHTMLAttributes');
zp_register_filter('standard_image_html', 'lazyload::addNoscriptImgHTML');

zp_register_filter('custom_image_attr', 'lazyload::filterHTMLAttributes');
zp_register_filter('custom_image_html', 'lazyload::addNoscriptImgHTML');

zp_register_filter('standard_image_thumb_attr', 'lazyload::filterHTMLAttributes');
zp_register_filter('standard_image_thumb_html', 'lazyload::addNoscriptImgHTML');

zp_register_filter('standard_album_thumb_attr', 'lazyload::filterHTMLAttributes');
zp_register_filter('standard_album_thumb_html', 'lazyload::addNoscriptImgHTML');

zp_register_filter('custom_album_thumb_attr', 'lazyload::filterHTMLAttributes');
zp_register_filter('custom_album_thumb_html', 'lazyload::addNoscriptImgHTML');


//backend
zp_register_filter('admin_head', 'lazyload::getJS');
zp_register_filter('adminthumb_attr', 'lazyload::filterHTMLAttributes');
zp_register_filter('adminthumb_html', 'lazyload::addNoscriptImgHTML');

/**
 * Options for the lazyload plugin
 *
 * @author Malte Müller (acrylian)
 * 
 * @package zpcore\plugins\lazyload
 */
class lazyloadOptions {

	function __construct() {
		setOptionDefault('lazyload_nativeonly', 0);
	}

	function getOptionsSupported() {
		return array(gettext('Native lazyload') => array(
						'key' => 'lazyload_nativeonly',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 1,
						'desc' => gettext('This disables loading the lazysizes script to only use native lazyloading modern browsers support.'))
		);
	}

}

/**
 * Filter HTML to add JS lazyloading using the script lazysizes
 *
 * @author Malte Müller (acrylian)
 * 
 * @package zpcore\plugins\lazyload
 */
class lazyload {
	
	/**
	 * The class used for the lazyloading JS fallback
	 * @var string
	 */
	public static $lazyloadclass = 'lazyload';

	/**
	 * Filters image attributes
	 * 
	 * @param array $attr Array with key value pairs of attributes
	 * @return array
	 */
	static function filterHTMLAttributes($attr) {
		if (!getOption('lazyload_nativeonly')) {
			if (isset($attr['class']) && strpos($attr['class'], lazyload::$lazyloadclass) === false) {
				$attr['class'] .= ' ' . lazyload::$lazyloadclass;
			} else {
				$attr['class'] = lazyload::$lazyloadclass;
			}
			if (isset($attr['src'])) {
				$attr['data-src'] = $attr['src'];
				unset($attr['src']);
			}
			if (isset($attr['srcset'])) {
				$attr['data-srcset'] = $attr['srcset'];
				unset($attr['srcset']);
			}
			if (isset($attr['sizes'])) {
				$attr['data-sizes'] = $attr['sizes'];
				unset($attr['sizes']);
			}
		}
		if (!isset($attr['decoding'])) {
			$attr['decoding'] = 'async';
		}
		if (!isset($attr['loading'])) {
			$attr['loading'] = 'lazy';
		}
		return $attr;
	}

	/**
	 * Creates a noscript fallback image
	 * 
	 * @param string $html
	 * @return string
	 */
	static function addNoscriptImgHTML($html) {
		if (!getOption('lazyload_nativeonly')) {
			$noscriptimg = str_replace('data-src="', 'src="', $html);
			$noscriptimg = str_replace('data-srcset="', 'srcset="', $noscriptimg);
			$noscriptimg = str_replace('data-sizes="', 'sizes="', $noscriptimg);
			$noscriptimg = str_replace(lazyload::$lazyloadclass, '', $noscriptimg);
			if ($html != $noscriptimg) {
				$html = '<noscript>' . $noscriptimg . '</noscript>' . $html;
			}
		}
		return $html;
	}

	/**
	 * Gets the JS to include and also if enabled the default config
	 */
	static function getJS() {
		if(!getOption('lazyload_nativeonly')) {
			?>
			<script src="<?php echo FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/lazyload/lazysizes.min.js"></script>
			<script src="<?php echo FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/lazyload/ls.native-loading.min.js"></script>
			<script src="<?php echo FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/lazyload/ls.unveilhooks.min.js"></script>
			<?php
		}
	}

}
