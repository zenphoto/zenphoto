<?php
/*
 * Loads the lazysizes <a href="https://github.com/aFarkas/lazysizes" rel="nooopener" target="_blank">lazysizes</a>) script for primarily image lazyloading 
 * for front and/or backend additionally as a fallback to the native browser lazy loading using loading="lazy". 
 * 
 * Native PHP DOM and multibyte extensions required.
 * 
 * The plugin attaches itself automatically to standard print* image template functions via filters and requires no theme changes. It 
 * also attaches to the general adminthumb functon used on the backend.
 * It additionally adds a no-script fallback and it supports native lazyloading in very modern browsers by adding the loading="lazy". 
 * 
 * Alternatively you can use the following method to directly filter any HTML to enable lazyloading on all images found.
 * 
 * # Example for manual usage
 * 
 * Create some HTML for example with an image without lazyloading
 * 
 *     $html = '<p><img src="someimagejpg" alt=""></p>';
 *     $lazyload = new lazyload($html);
 * 		 $lazyload->elements_to_filter = array('image'); // we only want to filter images in this case
 *     $lazyload->filterHTMLElements();
 *     echo $lazyload->getFilteredHTML(); 
 * 
 * 		 output should be like this:
 * 
 * 		 <p>
 * 		   <noscript>
 * 			   <img src="someimagejpg" alt=""> 
 * 			 </noscript>
 * 			 <img data-src="someimagejpg" class="lazyload" loading="lazy" alt="">
 *     </p>
 * 
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage lazyload
 */

$plugin_is_filter = 800 | THEME_PLUGIN | ADMIN_PLUGIN;
$plugin_description = gettext('Provides lazyloading for theme and backend using standard template image functions using <a href="https://github.com/aFarkas/lazysizes" rel="nooopener" target="_blank">lazysizes</a>');
$plugin_author = 'Malte Müller (acrylian)';
$plugin_disable = (!class_exists('DOMDocument') && !function_exists('mb_convert_encoding')) ? gettext('The native PHP extensions DOM/DOMDocument and multibyte are required.') : false;
$plugin_category = gettext('Media');
$option_interface = 'lazyloadOptions';

if (getOption('lazyload_galleryimages') || getOption('lazyload_gallerytext') || getOption('lazyload_zenpage')) {
	zp_register_filter('theme_head', 'lazyload::getJS');
}

if (getOption('lazyload_galleryimages')) {
	zp_register_filter('standard_image_html', 'lazyload::filterHTMLImages');
	zp_register_filter('custom_image_html', 'lazyload::filterHTMLImages');
	zp_register_filter('standard_image_thumb_html', 'lazyload::filterHTMLImages');
	zp_register_filter('standard_album_thumb_html', 'lazyload::filterHTMLImages');
	zp_register_filter('custom_album_thumb_html', 'lazyload::filterHTMLImages');
}

if (getOption('lazyload_backend')) {
	zp_register_filter('admin_head', 'lazyload::getJS');
	zp_register_filter('adminthumb_html', 'lazyload::filterHTMLImages');
}

class lazyloadOptions {

	function __construct() {
		setOptionDefault('lazyload_galleryimages', true);
		setOptionDefault('lazyload_backend', true);
	}

	function getOptionsSupported() {
		$options = array(
				gettext('Theme: Gallery images') => array(
						'key' => 'lazyload_galleryimages',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext("Attaches JS lazyloading to all images on your theme or plugins that are using the standard print* image template functions.")
				),
				gettext('Backend images') => array(
						'key' => 'lazyload_backend',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext("Attaches JS lazyloading to images on the backend using the necessary standard adminthumb functions.")
				)
		);
		return $options;
	}

}

/**
 * Filter HTML to add JS lazyloading using the script lazysizes
 * 
 * Native PHP DOM and multibyte extensions required
 *
 * @author Malte Müller (acrylian)
 * @since ZenphootCMS 3.0
 * 
 * @package plugins
 * @subpackage media
 */
class lazyload {

	/**
	 * $tores the DOM object for filtering
	 * @var obj
	 */
	public $dom = null;

	/**
	 * Stores the unmodified HTML to be filtered as a fallback in case the system's PHP is not compatible
	 * @var string
	 */
	public $html = null;

	/**
	 * Stores the result of PHP compatibility check of the constructor
	 * It also is set to false if there is no valid html passed
	 * @var bool 
	 */
	private $compatible = false;

	/**
	 * Is set to true if the string passed to the constructor is not empty
	 * @var bool 
	 */
	private $valid = false;

	/**
	 * The class used for the lazyloading JS fallback
	 * @var string
	 */
	public static $lazyloadclass = 'lazyload';

	/**
	 * The elements to filter. Default is all with array('image', 'iframe', 'audio', 'video')
	 * Modify this property before filtering if you don't want to filter all
	 * @var array
	 */
	public $elements_to_filter = array('image', 'iframe', 'audio', 'video');

	/**
	 * @param string $html The HTML to filter
	 */
	function __construct($html) {
		if (!empty($html)) {
			$this->html = $html;
			$this->valid = true;
			if (class_exists('DOMDocument') && function_exists('mb_convert_encoding')) {
				$this->compatible = true;
			}
			if ($this->compatible) {
				$this->dom = new DOMDocument();
				$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'); 
				$this->dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
			}
		}
	}

	/**
	 * Filters all elements found and if necessary modifies the src for all element, for images srcset, 
	 * sizes attributes specifially, adds the lazyloading class and the native browser 
	 * lazyloading loading="lazy" attribute for  native browser lazyloading and a noscript fallback
	 * 
	 * Note this does not return any value as it modifies the $dom property of the class by reference
	 * You have to call $html = $this->getFilteredHTML() to get the actual modified HTML
	 */
	function filterHTMLElements() {
		if ($this->compatible && $this->valid) {
			$elements = array();
			foreach ($this->elements_to_filter as $element_to_filter) {
				if ($element_to_filter == 'image') {
					$elements = $this->dom->getElementsByTagName('img');
				}
				if ($element_to_filter == 'iframe') {
					$elements = $this->dom->getElementsByTagName('iframe');
				}
				if ($element_to_filter == 'audio') {
					$elements = $this->dom->getElementsByTagName('audio');
				}
				if ($element_to_filter == 'video') {
					$elements = $this->dom->getElementsByTagName('video');
				}
				foreach ($elements as $element) {

					// add native browser lazyloading support for images and iframes
					if ($element_to_filter == 'image' || $element_to_filter == 'iframe') {
						if (!$element->hasAttribute('loading')) {
							$element->setAttribute('loading', 'lazy');
						}
					}
					// Clone the node for later noscript usage before adding the JS extras
					$clone = $element->cloneNode();

					// If attributes have the data- prefix already the element is already modified and ready
					if ($element->hasAttribute('data-src')) {
						continue;
					}

					// Images of noscript elements do not need filtering
					if (isset($element->parentNode->tagName) && $element->parentNode->tagName == 'noscript') {
						continue;
					}

					// src -> data-src
					$src = $element->getAttribute('src');
					$element->removeAttribute('src');
					$element->setAttribute('data-src', $src);

					if ($element_to_filter == 'image') {
						//srcset -> data-srcset
						if ($element->hasAttribute('srcset')) {
							$srcset = $element->getAttribute('srcset');
							$element->removeAttribute('srcset');
							$element->setAttribute('data-srcset', $srcset);
						}

						//sizes -> data-sizes
						if ($element->hasAttribute('sizes')) {
							$sizes = $element->getAttribute('sizes');
							$element->removeAttribute('sizes');
							$element->setAttribute('data-sizes', $sizes);
						}
					}

					// Add the lazysizes class if needed
					if ($element->hasAttribute('class')) {
						$class = $element->getAttribute('class');
						$element->setAttribute('class', $class . ' ' . lazyload::$lazyloadclass);
					} else {
						$element->setAttribute('class', 'lazyload');
					}

					// Create the <noscript> element with our clone from above
					$no_script = $this->dom->createElement('noscript');
					$no_script->appendChild($clone);

					$element->parentNode->insertBefore($no_script, $element);
				}
			}
		}
	}

	/**
	 * Returns the filtered HTML after filterHTMLElements() has been applied
	 * @return string
	 */
	function getFilteredHTML() {
		if ($this->compatible && $this->valid) {
			return $this->dom->saveHTML();
		}
		return $this->html;
	}

	/**
	 * Wrapper for filter hook usage to modify image or iframe elements to support lazyloading
	 * 
	 * @param string $html HTML element or several elements to filter
	 * @return string
	 */
	static function filterHTML($html) {
		$lazyload = new lazyload($html);
		if ($lazyload->compatible && $lazyload->valid) {
			$lazyload->filterHTMLElements();
			return $lazyload->getFilteredHTML();
		}
		return $html;
	}

	/**
	 * Wrapper of filterHTML() for filter hook usage to filter images only
	 * @param type $html
	 * @return string
	 */
	static function filterHTMLImages($html) {
		$lazyload = new lazyload($html);
		$lazyload->elements_to_filter = array('image');
		if ($lazyload->compatible && $lazyload->valid) {
			$lazyload->filterHTMLElements();
			return $lazyload->getFilteredHTML();
		}
		return $html;
	}

	/**
	 * Gets the JS to include and also if enabled the default config
	 */
	static function getJS() {
		?>
		<script src="<?php echo FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/lazyload/lazysizes.min.js"></script>
		<script src="<?php echo FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/lazyload/ls.native-loading.min.js"></script>
		<script src="<?php echo FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/lazyload/ls.unveilhooks.min.js"></script>
		<?php
	}

}
