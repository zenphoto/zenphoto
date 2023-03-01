<?php
/**
 * A plugin to add a cookie notify dialog to comply with the EU cookie law and Google's requirement for Google Ads and more
 * https://www.cookiechoices.org
 *
 * Adapted of https://cookieconsent.osano.com/
 * 
 * Note that to actually use the opt-in and out-out complicance modes your theme may require customisation. 
 * As the plugin does not clear or block scripts by itself. It is not possible to savely delete third party cookies.
 * 
 * It also does not block cookies Zenphoto sets itself as these are not privacy related and require to work properly. 
 * Learn more about Zenphotp's cookies on: https://zenphoto.org/news/cookies/
 * 
 * But you can use this plugin to only execute scripts on consent by adding the JS calls to block or allow the scripts option 
 * so they cannot set or use their cookies unless allowed to run
 * 
 * @author Malte Müller (acrylian), Fred Sondaar (fretzl), Vincent Bourganel (vincent3569)
 * @license GPL v3 or later
 * @package zpcore\plugins\cookieconsent
 */
$plugin_is_filter = 5 | THEME_PLUGIN;
$plugin_description = gettext("A plugin to add a cookie notify dialog to comply with the EU cookie law and Google's request regarding usages of Google Adwords, Analytics and more. Based on <a href='https://cookieconsent.osano.com/'>https://cookieconsent.osano.com/</a>");
$plugin_author = "Malte Müller (acrylian), Fred Sondaar (fretzl), Vincent Bourganel (vincent3569)";
$plugin_notice = gettext('Note: This plugin cannot block or delete cookies by itself without proper configuration that also may require customisations to your site.');
$option_interface = 'cookieConsent';
$plugin_category = gettext('Misc');

if (!zp_loggedin()) {
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
		setOptionDefault('zpcookieconsent_compliancetype', 'info');
		setOptionDefault('zpcookieconsent_consentrevokable', 0);
		setOptionDefault('zpcookieconsent_layout', 'basic');
	}

	function getOptionsSupported() {
		$options = array(
				gettext('Button: Agree') => array(
						'key' => 'zpcookieconsent_buttonagree',
						'type' => OPTION_TYPE_TEXTBOX,
						'multilingual' => 1,
						'desc' => gettext('Text used for the dismiss button in info complicance. Leave empty to use the default text.')),
				gettext('Button: Allow cookies') => array(
						'key' => 'zpcookieconsent_buttonallow',
						'type' => OPTION_TYPE_TEXTBOX,
						'multilingual' => 1,
						'desc' => gettext('Text used for the button to allow cookies in opt-in and opt-out complicance. Leave empty to use the default text.')),
				gettext('Button: Decline cookies') => array(
						'key' => 'zpcookieconsent_buttondecline',
						'type' => OPTION_TYPE_TEXTBOX,
						'multilingual' => 1,
						'desc' => gettext('Text used for the button to decline cookies in opt-in and opt-out complicance. Leave empty to use the default text.')),
				gettext('Button: Learn more') => array(
						'key' => 'zpcookieconsent_buttonlearnmore',
						'type' => OPTION_TYPE_TEXTBOX,
						'multilingual' => 1,
						'desc' => gettext('Text used for the learn more info button. Leave empty to use the default text.')),
				gettext('Button: Learn more - URL') => array(
						'key' => 'zpcookieconsent_buttonlearnmorelink',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('URL to your cookie policy / privacy info page.')),
				gettext('Message') => array(
						'key' => 'zpcookieconsent_message',
						'type' => OPTION_TYPE_TEXTAREA,
						'multilingual' => 1,
						'desc' => gettext('The message shown by the plugin. Leave empty to use the default text.')),
				gettext('Cookie Policy') => array(
						'key' => 'zpcookieconsent_policy',
						'type' => OPTION_TYPE_TEXTAREA,
						'multilingual' => 1,
						'desc' => gettext('The policy headline for the message text on some layout/theme settings.')),
				gettext('Header') => array(
						'key' => 'zpcookieconsent_header',
						'type' => OPTION_TYPE_TEXTAREA,
						'multilingual' => 1,
						'desc' => gettext('The header shown on some layout/theme settings.')),
				gettext('Domain') => array(
						'key' => 'zpcookieconsent_domain',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('The domain for the consent cookie that Cookie Consent uses, to remember that users have consented to cookies. Useful if your website uses multiple subdomains, e.g. if your script is hosted at <code>www.example.com</code> you might override this to <code>example.com</code>, thereby allowing the same consent cookie to be read by subdomains like <code>foo.example.com</code>.')),
				gettext('Expire') => array(
						'key' => 'zpcookieconsent_expirydays',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('The number of days Cookie Consent should store the user’s consent information for. Use -1 for no expiry.')),
				gettext('Theme') => array(
						'key' => 'zpcookieconsent_theme',
						'type' => OPTION_TYPE_SELECTOR,
						'selections' => array(
								'block' => 'block',
								'edgeless' => 'edgeless',
								'classic' => 'classic',
								gettext('custom') => 'custom'
						),
						'desc' => gettext('These are the included default themes. The chosen theme is added to the popup container as a CSS class in the form of .cc-style-THEME_NAME. Users can create their own themes.')),
				gettext('Layout') => array(
						'key' => 'zpcookieconsent_layout',
						'type' => OPTION_TYPE_SELECTOR,
						'selections' => array(
								'basic' => 'basic',
								'basic-close' => 'basic-close',
								'basic-header' => 'basic-header'
						),
						'desc' => gettext('The layout style of the chosen theme.')),
				gettext('Position') => array(
						'key' => 'zpcookieconsent_position',
						'type' => OPTION_TYPE_SELECTOR,
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
						'desc' => gettext('Check to dismiss when users scroll a page [other than <em>Learn more</em> page].')),
				gettext('Color - Popup') => array(
						'key' => 'zpcookieconsent_colorpopup',
						'type' => OPTION_TYPE_COLOR_PICKER,
						'desc' => gettext('Choose the color of the popup background.')),
				gettext('Color - Button') => array(
						'key' => 'zpcookieconsent_colorbutton',
						'type' => OPTION_TYPE_COLOR_PICKER,
						'desc' => gettext('Choose the color of the button.')),
				gettext('Compliance type') => array(
						'key' => 'zpcookieconsent_compliancetype',
						'type' => OPTION_TYPE_SELECTOR,
						'selections' => array(
								gettext('Info: Cookies are always allowed') => 'info',
								gettext('Opt-in: Cookies are allowed after consent') => 'opt-in',
								gettext('Opt-out: Cookies are allowed unless declined') => 'opt-out'
						),
						'desc' => gettext('Choose the compliance type required for your jurisdiction. Note that your site may require modification to properly apply this to your cookies. Also see the scripts option below.')),
				gettext('Consent revokable') => array(
						'key' => 'zpcookieconsent_consentrevokable',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Check to allow revoking the consent as required in some jurisdictions.')), 
				gettext('Scripts to allow or block') => array(
						'key' => 'zpcookieconsent_scripts',
						'type' => OPTION_TYPE_TEXTAREA,
						'multilingual' => false,
						'desc' => gettext('Add privacy related executional JS code (ad trackers statistics etc.) here to allow or block opt-in/opt-out compliances (without the script wrapper). As we cannot safely delete cookies set by third party scripts, we block their execution so they can neither set nor fetch their cookies.')),
				gettext('External Scripts to allow or block') => array(
						'key' => 'zpcookieconsent_externalscripts',
						'type' => OPTION_TYPE_TEXTAREA,
						'multilingual' => false,
						'desc' => gettext('Add URLs to privacy related external JS scripts as a comma separated list (ad trackers statistics etc.) here to allow or block opt-in/opt-out compliances (without the script wrapper). As we cannot safely delete cookies set by third party scripts, we block their execution so they can neither set nor fetch their cookies.'))
		
		);
		return $options;
	}
	
	/**
	 * Gets the CSS for the cookieconsent script
	 */
	static function getCSS() {
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/cookieconsent/cookieconsent.min.css" />
		<?php
	}

	/**
	 * Gets the JS definition of the cookieconsent script based on the options
	 */
	static function getJS() {
		$message = gettext('This website uses cookies. By continuing to browse the site, you agree to our use of cookies.');
		if (getOption('zpcookieconsent_message')) {
			$message = get_language_string(getOption('zpcookieconsent_message'));
		}
		$dismiss = gettext('Agree');
		if (getOption('zpcookieconsent_buttonagree')) {
			$dismiss = get_language_string(getOption('zpcookieconsent_buttonagree'));
		}
		$allow = gettext('Allow cookies');
		if (getOption('zpcookieconsent_buttonallow')) {
			$allow = get_language_string(getOption('zpcookieconsent_buttonallow'));
		}
		$decline = gettext('Decline');
		if (getOption('zpcookieconsent_buttondecline')) {
			$decline = get_language_string(getOption('zpcookieconsent_buttondecline'));
		}
		$policy = gettext('Cookie Policy');
		if (getOption('zpcookieconsent_policy')) {
			$policy  = get_language_string(getOption('zpcookieconsent_policy'));
		}
		$header = gettext('Cookies used on the website!');
		if (getOption('zpcookieconsent_header')) {
			$header = get_language_string(getOption('zpcookieconsent_header'));
		}
		$dataprivacy_info = getDataUsageNotice();
		if (getOption('zpcookieconsent_buttonlearnmore')) {
			$learnmore = get_language_string(getOption('zpcookieconsent_buttonlearnmore'));
		} else {
			$learnmore = $dataprivacy_info['linktext'];
		}
		if (getOption('zpcookieconsent_buttonlearnmorelink')) {
			$link = getOption('zpcookieconsent_buttonlearnmorelink');
		} else {
			$link = $dataprivacy_info['url'];
		}
		$theme = 'block';
		if (getOption('zpcookieconsent_theme')) {
			$theme = getOption('zpcookieconsent_theme');
			//fix old option
			if (!in_array($theme, array('block', 'edgeless', 'classic', 'custom'))) {
				$theme = 'block';
				setOption('zpcookieconsent_theme', $theme, true);
			}
		}
		$layout = 'basic';
		if (getOption('zpcookieconsent_layout')) {
			$layout = getOption('zpcookieconsent_layout');
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
		$complicance_type = getOption('zpcookieconsent_compliancetype');
		$consentrevokable = getOption('zpcookieconsent_consentrevokable');
		if ($consentrevokable) {
			$consentrevokable = 'true';
		} else {
			$consentrevokable = 'false';
		}
		?>
		<script src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/cookieconsent/cookieconsent.min.js"></script>
		<script>
			window.addEventListener("load", function () {
				var cookieconsent_allowed = false;
				window.cookieconsent.initialise({
					palette: {
						popup: {
							background: "<?php echo $color_popup; ?>"
						},
						button: {
							background: "<?php echo $color_button; ?>"
						}
					},
					position: "<?php echo js_encode($position); ?>",
					theme: "<?php echo js_encode($theme); ?>",
					layout: "<?php echo js_encode($layout); ?>",
					dismissOnScroll: <?php echo js_encode($dismiss_on_scroll); ?>,
					type: '<?php echo $complicance_type; ?>',
					revokable: <?php echo $consentrevokable; ?>,
					cookie: {
						domain: "<?php echo js_encode($domain); ?>",
						expiryDays: <?php echo js_encode($cookie_expiry); ?>
					},
					content: {
						href: "<?php echo html_encode($link); ?>",
						header: '<?php echo js_encode($header); ?>',
						message: "<?php echo js_encode($message); ?>",
						dismiss: "<?php echo js_encode($dismiss); ?>",
						allow: '<?php echo js_encode($allow); ?>',
						deny: '<?php echo js_encode($decline); ?>',
						link: "<?php echo js_encode($learnmore); ?>",
						policy: "<?php echo js_encode($policy); ?>"
					},
					onInitialise: function (status) {
						var type = this.options.type;
						var didConsent = this.hasConsented();
						if (type == 'opt-in' && didConsent) {
							// enable cookies
							cookieconsent_allowed = true;
						}
						if (type == 'opt-out' && !didConsent) {
							// disable cookies
							cookieconsent_allowed = false;
						}
					},
					onStatusChange: function (status, chosenBefore) {
						var type = this.options.type;
						var didConsent = this.hasConsented();
						if (type == 'opt-in' && didConsent) {
							// enable cookies
							cookieconsent_allowed = true;
						}
						if (type == 'opt-out' && !didConsent) {
							// disable cookies
							cookieconsent_allowed = false;
						}
					},
					onRevokeChoice: function () {
						var type = this.options.type;
						if (type == 'opt-in') {
							// disable cookies
							cookieconsent_allowed = false;
						}
						if (type == 'opt-out') {
							// enable cookies
							cookieconsent_allowed = true;
						}
					}
				});
				if(cookieconsent_allowed) {
					<?php 
					cookieConsent::printExternalConsentJS(); 
					cookieConsent::printConsentJS(); 
					?>
				}
			});
		</script>
		<?php 
	}
	
	
	/**
	 * Checks if consent has been given depending on the compliance mode and if the cookieconsent_status cookie is set
	 * 
	 * - info: All just informational so always true
	 * - opt-in: Returns true only if the consent cookie is set to "allow"
	 * - opt-out: Returns true by default unless declined or if the consent cookie is set to "allow"
	 * 
	 * NOTE: This will not and cannot work properly while using the static_html_cache plugin unless called before the cache is fetched.
	 * 
	 * @since 1.5.8
	 * 
	 * @return boolean
	 */
	static function checkConsent() {
		$complicance = getOption('zpcookieconsent_compliancetype');
		$consent = zp_getCookie('cookieconsent_status');
		switch ($complicance) {
			case 'info':
				// just info but always allowed
				return true;
			case 'opt-in':
				// only allow by consent
				if ($consent && $consent == 'allow') {
					return true;
				} else {
					return false;
				}
			case 'opt-out':
				//Allows by default or by consent
				if (!$consent || $consent == 'allow') {
					return true;
				} else {
					return false;
				}
				break;
		}
		return false;
	}

	/**
	 * Prints the scripts added to the scripts option.
	 * These are then added to the theme_header filter automatically by the plugin
	 * 
	 * Plugins or themes can use the "cookieconsent_consentscripts" to add additional ones
	 * 
	 * @since 1.5.8
	 */
	static function printConsentJS() {
		$scripts = getOption('zpcookieconsent_scripts');
		echo zp_apply_filter('cookieconsent_consentscripts', $scripts, cookieconsent::checkConsent());
	}
	
	/**
	 * Prints JS code from the external scripts option without <script> that loads external scripts if consent has been given
	 * 
	 * Plugins or themes can use the "cookieconsent_externalconsentscripts" to add additional ones
	 * 
	 * @since 1.5.8
	 */
	static function printExternalConsentJS() {
		$option = trim(strval(getOption('zpcookieconsent_externalscripts')));
		$scripts = zp_apply_filter('cookieconsent_externalconsentscripts', $option, cookieconsent::checkConsent());
		if(!empty($scripts)) {
			$array = explode(',', $scripts);
			$externaljs = '';
			$total = count($array);
			$count = '';
			foreach($array as $url) {
				$count++;
				$externaljs .= '"' . $url . '"';
				if($count != $total) {
					$externaljs .= ',';
				}
			}
			// JS code!
			?>
				var externalconsentjs = [<?php echo $externaljs; ?>];
				$.each( externalconsentjs, function( key, value ) {
					$.getScript( value, function( data, textStatus, jqxhr ) {
						console.log( data ); // Data returned
						console.log( textStatus ); // Success
						console.log( jqxhr.status ); // 200
						console.log( "Load was performed." );
					});
				});
			<?php
		}
	}

}
