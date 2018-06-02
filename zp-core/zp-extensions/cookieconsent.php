<?php
/**
 * A plugin to add a cookie notify dialog to comply with the EU cookie law and Google's requirement for Google Ads and more
 * https://www.cookiechoices.org
 *
 * Adapted of https://cookieconsent.insites.com
 *
 * @author Malte Müller (acrylian), Fred Sondaar (fretzl), Vincent Bourganel (vincent3569)
 * @license GPL v3 or later
 * @package plugins
 * @subpackage cookieconsent
 */
$plugin_is_filter = 5 | THEME_PLUGIN;
$plugin_description = gettext("A plugin to add a cookie notify dialog to comply with the EU cookie law and Google's request regarding usages of Google Adwords, Analytics and more");
$plugin_author = "Malte Müller (acrylian), Fred Sondaar (fretzl), Vincent Bourganel (vincent3569)";
$option_interface = 'cookieConsent';
$plugin_category = gettext('Misc');

if (!isset($_COOKIE['cookieconsent_status'])) {
	zp_register_filter('theme_head', 'cookieConsent::getCSS');
	zp_register_filter('theme_head', 'cookieConsent::getJS');
}	
class cookieConsent {

	function __construct() {
		setOptionDefault('zpcookieconsent_expirydays', 365);
		setOptionDefault('zpcookieconsent_theme', 'block');
		setOptionDefault('zpcookieconsent_position', 'bottom');
		setOptionDefault('zpcookieconsent_colorpopup', '#000');
		setOptionDefault('zpcookieconsent_colorbutton', '#f1d600');
	}

	function getOptionsSupported() {
		$options = array(
				gettext('Button: Agree') => array(
						'key' => 'zpcookieconsent_buttonagree',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'multilingual' => 1,
						'desc' => gettext('Text used for the dismiss button. Leave empty to use the default text.')),
				gettext('Button: Learn more') => array(
						'key' => 'zpcookieconsent_buttonlearnmore',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'multilingual' => 1,
						'desc' => gettext('Text used for the learn more info button. Leave empty to use the default text.')),
				gettext('Button: Learn more - URL') => array(
						'key' => 'zpcookieconsent_buttonlearnmorelink',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 3,
						'desc' => gettext('URL to your cookie policy / privacy info page.')),
				gettext('Message') => array(
						'key' => 'zpcookieconsent_message',
						'type' => OPTION_TYPE_TEXTAREA,
						'order' => 4,
						'multilingual' => 1,
						'desc' => gettext('The message shown by the plugin. Leave empty to use the default text.')),
				gettext('Domain') => array(
						'key' => 'zpcookieconsent_domain',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 5,
						'desc' => gettext('The domain for the consent cookie that Cookie Consent uses, to remember that users have consented to cookies. Useful if your website uses multiple subdomains, e.g. if your script is hosted at <code>www.example.com</code> you might override this to <code>example.com</code>, thereby allowing the same consent cookie to be read by subdomains like <code>foo.example.com</code>.')),
				gettext('Expire') => array(
						'key' => 'zpcookieconsent_expirydays',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 6,
						'desc' => gettext('The number of days Cookie Consent should store the user’s consent information for. Use -1 for no expiry.')),
				gettext('Theme') => array(
						'key' => 'zpcookieconsent_theme',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 7,
						'selections' => array(
								'block' => 'block',
								'edgeless' => 'edgeless',
								'classic' => 'classic',
								gettext('custom') => 'custom'
						),
						'desc' => gettext('These are the included default themes. The chosen theme is added to the popup container as a CSS class in the form of .cc-style-THEME_NAME. Users can create their own themes.')),
				gettext('Position') => array(
						'key' => 'zpcookieconsent_position',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 7,
						'selections' => array(
								gettext('Top') => 'top',
								gettext('Top left') => 'top-left',
								gettext('Top right') => 'top-right',
								gettext('Bottom') => 'bottom',
								gettext('Bottom left') => 'bottom-left',
								gettext('Bottom right') => 'bottom-right',
						),
						'desc' => gettext('Choose the position of the popup. Top and Bottom = banner, Top left/right, Bottom left/right = floating')),
				gettext('Dismiss on Scroll') => array(
						'key' => 'zpcookieconsent_dismissonscroll',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 9,
						'desc' => gettext('Check to dismiss when users scroll a page [other than <em>Learn more</em> page].')),
				gettext('Color - Popup') => array(
						'key' => 'zpcookieconsent_colorpopup',
						'type' => OPTION_TYPE_COLOR_PICKER,
						'order' => 10,
						'desc' => gettext('Choose the color of the popup background.')),
				gettext('Color - Button') => array(
						'key' => 'zpcookieconsent_colorbutton',
						'type' => OPTION_TYPE_COLOR_PICKER,
						'order' => 11,
						'desc' => gettext('Choose the color of the button.'))
				
		);
		return $options;
	}

	static function getCSS() {
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/cookieconsent/cookieconsent.min.css" />
		<?php
	}

	static function getJS() {
		$message = gettext('This website uses cookies. By continuing to browse the site, you agree to our use of cookies.');
		if (getOption('zpcookieconsent_message')) {
			$message = get_language_string(getOption('zpcookieconsent_message'));
		}
		$dismiss = gettext('Agree');
		if (getOption('zpcookieconsent_buttonagree')) {
			$dismiss = get_language_string(getOption('zpcookieconsent_buttonagree'));
		}
		$learnmore = gettext('More info');
		if (getOption('zpcookieconsent_buttonlearnmore')) {
			$learnmore = get_language_string(getOption('zpcookieconsent_buttonlearnmore'));
		}
		$link = getOption('zpcookieconsent_buttonlearnmorelink');
		$theme = 'block';
		if (getOption('zpcookieconsent_theme')) {
			$theme = getOption('zpcookieconsent_theme');
			//fix old option
			if (!in_array($theme, array('block', 'edgeless', 'classic', 'custom'))) {
				$theme = 'block';
				setOption('zpcookieconsent_theme', $theme, true);
			}
		}
		$domain = '';
		if (getOption('zpcookieconsent_domain')) {
			$domain = getOption('zpcookieconsent_domain');
		}
		$position = getOption('zpcookieconsent_position');
		$cookie_expiry = getOption('zpcookieconsent_expirydays');
		$dismiss_on_scroll = "false";
		if (getOption('zpcookieconsent_dismissonscroll') && strpos(sanitize($_SERVER['REQUEST_URI']), $link) === false) { // false in Cookie Policy Page
			$dismiss_on_scroll = 100;
		}
		$color_popup = getOption('zpcookieconsent_colorpopup');
		$color_button = getOption('zpcookieconsent_colorbutton');
		?>
		<script src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/cookieconsent/cookieconsent.min.js"></script>
		<script>
			window.addEventListener("load", function () {
				window.cookieconsent.initialise({
					"palette": {
						"popup": {
							"background": "<?php echo $color_popup; ?>"
						},
						"button": {
							"background": "<?php echo $color_button; ?>"
						}
					},
					"position": "<?php echo js_encode($position); ?>",
					"theme": "<?php echo js_encode($theme); ?>",
					"dismissOnScroll": <?php echo js_encode($dismiss_on_scroll); ?>,
					"cookie": {
						"domain": "<?php echo js_encode($domain); ?>",
						"expiryDays": <?php echo js_encode($cookie_expiry); ?>
					},
					"content": {
						"message": "<?php echo js_encode($message); ?>",
						"dismiss": "<?php echo js_encode($dismiss); ?>",
						"link": "<?php echo js_encode($learnmore); ?>",
						"href": "<?php echo html_encode($link); ?>"
					},
					onStatusChange: function(status) {
						this.element.parentNode.removeChild(this.element);
					}
				})
			});
		</script>
		<?php
	}

}