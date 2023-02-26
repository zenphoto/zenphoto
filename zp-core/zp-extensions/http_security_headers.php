<?php

/**
 * A Zenphoto plugin to set various security related headers.
 * 
 * See these urls for detailed info:
 * 
 * <ul>
 * <li>{@link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy}</li>
 * <li>{@link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security}</li>
 * <li>{@link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options}</li>
 * <li>{@link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options}</li>
 * <li>{@link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-XSS-Protection}</li>
 * <li>{@link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy}</li>
 * <ul>
 * 
 * @author Malte Müller (acrylian)
 * @package zpcore\plugins\httpsecurityheaders
 * 
 */
$plugin_is_filter = 9 | CLASS_PLUGIN;
$plugin_description = gettext('A plugin to enable basic usage of various security related HTTP response headers for the frontend. <p class="warningbox">Caution: Misconfiguration may break your site.</p>');
$plugin_author = 'Malte Müller (acrylian)';
$plugin_category = gettext('security');
$option_interface = 'securityheadersOptions';

zp_register_filter('theme_headers', 'securityHeaders::setHeaders');

/**
 * Options handler for http_security_headers plugin
 */
class securityheadersOptions {

	function __construct() {
		setOptionDefault('securityheaders_csp', 1);
		setOptionDefault('securityheaders_xframeoptions', 'disabled');
		setOptionDefault('securityheaders_referrerpolicy', 'disabled');
		purgeOption('securityheaders_csp_blockallmixedcontent');
	}

	function getOptionsSupported() {
		
		/*
		 * Content-Security-Policy
		 */
		$options = array(
				'Content-Security-Policy Note' => array(
						'key' => 'securityheaders_csp_note1',
						'type' => OPTION_TYPE_NOTE,
						'order' => 0,
						'desc' => '<h2>Content-Security-Policy</h2>'
						. '<p>' . gettext('The Content-Security-Policy header allows you to control which resourcess browsers are allowed to load. For detailed info please see <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy" target="_blank">https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy</a>.') . '</p>'
						. '<p>' . gettext('Use the Report-Only option to test before setting directives live. Review your browser log/console for block reports.') . '</p>'
				),
				'Content-Security-Policy' => array(// set sets the default for all other *-src policies not set individually
						'key' => 'securityheaders_csp',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 2,
						'desc' => gettext('Enable or disable the Content-Security-Policy.')
				),
				'Content-Security-Policy-Report-Only' => array(// set sets the default for all other *-src policies not set individually
						'key' => 'securityheaders_csp_reportonly',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 2,
						'desc' => gettext('Set the Content-Security-Policy header to report mode but not actually block anything.')
				),
				/**
				 * Content-Security-Policy - Fetch directives
				 */
				'Content-Security-Policy: default-src' => array(// set sets the default for all other *-src policies not set individually
						'key' => 'securityheaders_csp_defaultsrc',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicyFetchSources('securityheaders_csp_defaultsrc'),
						'order' => 2,
						'desc' => '<p>' . gettext('Fallback directive for all <em>*-src</em> fetch directives.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/default-src', true)
				),
				'Content-Security-Policy: default-src - host-source' => array(
						'key' => 'securityheaders_csp_defaultsrc_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => self::getCOntentSecurityPolicyHostSourceDoc()
				),
				'Content-Security-Policy: connect-src' => array(
						'key' => 'securityheaders_csp_connectsrc',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicyFetchSources('securityheaders_csp_connectsrc'),
						'order' => 3,
						'desc' => '<p>' . gettext('Allowed sources for loading script interfaces.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/connect-src', true)
				),
				'Content-Security-Policy: connect-src - host-source' => array(
						'key' => 'securityheaders_csp_connectsrc_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 3,
						'desc' => self::getCOntentSecurityPolicyHostSourceDoc()
				),
				'Content-Security-Policy: font-src' => array(
						'key' => 'securityheaders_csp_fontsrc',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicyFetchSources('securityheaders_csp_fontsrc'),
						'order' => 4,
						'desc' => '<p>' . gettext('Allowed sources for font loading.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/font-srcy', true)
				),
				'Content-Security-Policy: font-src - host-source' => array(
						'key' => 'securityheaders_csp_fontsrc_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 4,
						'desc' => self::getCOntentSecurityPolicyHostSourceDoc()
				),
				'Content-Security-Policy: frame-src' => array(
						'key' => 'securityheaders_csp_framesrc',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicyFetchSources('securityheaders_csp_framesrc'),
						'order' => 5,
						'desc' => '<p>' . gettext('Allowed sources for frames.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-src', true)
				),
				'Content-Security-Policy: frame-src - host-source' => array(
						'key' => 'securityheaders_csp_framesrc_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 5,
						'desc' => self::getCOntentSecurityPolicyHostSourceDoc()
				),
				'Content-Security-Policy: img-src' => array(
						'key' => 'securityheaders_csp_imgsrc',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicyFetchSources('securityheaders_csp_imgsrc'),
						'order' => 6,
						'desc' => '<p>' . gettext('Allowed sources for images.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/img-src', true)
				),
				'Content-Security-Policy: img-src - host-source' => array(
						'key' => 'securityheaders_csp_imgsrc_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 6,
						'desc' => self::getCOntentSecurityPolicyHostSourceDoc()
				),
				'Content-Security-Policy: manifest-src' => array(
						'key' => 'securityheaders_csp_manifestsrc',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicyFetchSources('securityheaders_csp_manifestsrc'),
						'order' => 7,
						'desc' => '<p>' . gettext('Allowed sources for application manifest files.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/manifest-src', true)
				),
				'Content-Security-Policy: manifest-src - host-source' => array(
						'key' => 'securityheaders_csp_manifestsrc_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 7,
						'desc' => self::getCOntentSecurityPolicyHostSourceDoc()
				),
				'Content-Security-Policy: media-src' => array(//recommended to be set to "none"
						'key' => 'securityheaders_csp_mediasrc',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicyFetchSources('securityheaders_csp_mediasrc'),
						'order' => 9,
						'desc' => '<p>' . gettext('Allowed sources for video and audio.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/media-src', true)
				),
				'Content-Security-Policy: media-src - host-source' => array(
						'key' => 'securityheaders_csp_mediasrc_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 9,
						'desc' => self::getCOntentSecurityPolicyHostSourceDoc()
				),
				'Content-Security-Policy: object-src' => array(//recommended to be set to "none"
						'key' => 'securityheaders_csp_objectsrc',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicyFetchSources('securityheaders_csp_objectsrc'),
						'order' => 10,
						'desc' => '<p>' . gettext('Allowed sources for object, embed and applet usage') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/object-src', true)
				),
				'Content-Security-Policy: object-src - host-source' => array(
						'key' => 'securityheaders_csp_objectsrc_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 10,
						'desc' => self::getCOntentSecurityPolicyHostSourceDoc()
				),
				'Content-Security-Policy: script-src' => array(
						'key' => 'securityheaders_csp_scriptsrc',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicyFetchSources('securityheaders_csp_scriptsrc'),
						'order' => 11,
						'desc' => '<p>' . gettext('Allowed sources for JavaScript.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src', true)
				),
				'Content-Security-Policy: script-src - host-source' => array(
						'key' => 'securityheaders_csp_scriptsrc_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 11,
						'desc' => self::getCOntentSecurityPolicyHostSourceDoc()
				),
				'Content-Security-Policy: style-src' => array(
						'key' => 'securityheaders_csp_stylesrc',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicyFetchSources('securityheaders_csp_stylesrc'),
						'order' => 12,
						'desc' => '<p>' . gettext('Allowed sources for CSS.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/style-src', true)
				),
				'Content-Security-Policy: style-src - host-source' => array(
						'key' => 'securityheaders_csp_stylesrc_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 12,
						'desc' => self::getCOntentSecurityPolicyHostSourceDoc()
				),
				/**
				 * Content-Security-Policy- Document directives
				 */
				'Content-Security-Policy: base-uri' => array(
						'key' => 'securityheaders_csp_baseuri',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicyFetchSources('securityheaders_csp_baseuri'),
						'order' => 13,
						'desc' => '<p>' . gettext('Restrict the base URI of the document.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/base-uri')
				),
				'Content-Security-Policy: base-uri - host-source' => array(
						'key' => 'securityheaders_csp_baseuri_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 13,
						'desc' => self::getCOntentSecurityPolicyHostSourceDoc()
				),
				'Content-Security-Policy: plugin-types' => array(
						'key' => 'securityheaders_csp_plugintypes',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecuritytPolicyPluginTypes(),
						'order' => 14,
						'desc' => '<p>' . gettext('Restricts specific plugin types a browser is allowed to load (e.g. Java Applets, Flash videos etc.) if the object-src directive is set to "none".') . '</p>' . self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/plugin-types')
				),
				'Content-Security-Policy: sandbox' => array(
						'key' => 'securityheaders_csp_sandbox',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicySandboxSources(),
						'order' => 15,
						'desc' => '<p>' . gettext('Enables sandbox for the requested source.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/sandbox')
				),
				/**
				 * Content-Security-Policy - Navigation directives
				 */
				'Content-Security-Policy: form-action' => array(
						'key' => 'securityheaders_csp_formaction',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicyFetchSources('securityheaders_csp_formaction'),
						'order' => 16,
						'desc' => '<p>' . gettext('Restricts target URLs for form actions.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/form-action', true)
				),
				'Content-Security-Policy: form-action - host-source' => array(
						'key' => 'securityheaders_csp_formaction_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 16,
						'desc' => self::getCOntentSecurityPolicyHostSourceDoc()
				),
				'Content-Security-Policy: frame-ancestors' => array(
						'key' => 'securityheaders_csp_frameancestors',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => self::getContentSecurityPolicyFrameAncestorsSources(),
						'order' => 17,
						'desc' => '<p>' . gettext('Specifices parents for frame, iframe, object, embed and applet. Helps prevent clickjacking and the site being loaded within other sites.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-ancestors')
				),
				'Content-Security-Policy: frame-ancestors - host-source' => array(
						'key' => 'securityheaders_csp_frameancestors_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 17,
						'desc' => self::getCOntentSecurityPolicyHostSourceDoc()
				),
				/**
				 * Content-Security-Policy - Other directives
				 */
				'Content-Security-Policy: upgrade-insecure-requests' => array(
						'key' => 'securityheaders_csp_upgradeinsecurerequests',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 19,
						'desc' => '<p>' . gettext('Instructs the browser to treat insecure http URLs like https ones.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/upgrade-insecure-requests')
				),
				/**
				 * Strict-Transport-Security
				 */
				'Strict-Transport-Security Note' => array(
						'key' => 'securityheaders_hsts_note',
						'type' => OPTION_TYPE_NOTE,
						'order' => 26,
						'desc' => gettext('<h2>Strict-Transport-Security</h2><hr>')
				),
				'Strict-Transport-Security: max-age' => array(
						'key' => 'securityheaders_hsts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 27,
						'desc' => '<p>' . gettext('Enter the max age in seconds. Instructs the browser that the site should be accessed via https only.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security')
				),
				'Strict-Transport-Security - includeSubdomains' => array(
						'key' => 'securityheaders_hsts_includesubdomains',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 28,
						'desc' => '<p>' . gettext('Optional to include sub domains if <em>max-age</em> is set above.')
				),
				'Strict-Transport-Security - preload' => array(
						'key' => 'securityheaders_hsts_preload',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 29,
						'desc' => '<p>' . gettext('Optional if <em>max-age</em> is set above.')
				),
				/*
				 * Other partly legacy policies/headers
				 */
				'Other note' => array(
						'key' => 'securityheaders_others_note',
						'type' => OPTION_TYPE_NOTE,
						'order' => 30,
						'desc' => gettext('<h2>Other headers</h2><hr>')
				),
				'X-Frame-Options' => array(
						'key' => 'securityheaders_xframeoptions',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => self::getXframeSources(),
						'order' => 30,
						'desc' => '<p>' . gettext('Legacy header for old browsers replaced by Content-Security-Policy: frame-ancestors') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options')
				),
				'X-Frame-Options - allow-from hosts' => array(
						'key' => 'securityheaders_csp_xframeoptions_allow-from_hosts',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 30,
						'desc' => gettext('Enter one or more domains if <em>allow-from</em> is selected above.')
				),
				'X-Content-Type-Options: nosniff' => array(
						'key' => 'securityheaders_xcontentnosniff',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 31,
						'desc' => '<p>' . gettext('Opt-out for MIME type sniffing.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options')
				),
				'X-XSS-Protection' => array(
						'key' => 'securityheaders_xxssprotection',
						'type' => OPTION_TYPE_CHECKBOX_ARRAY,
						'checkboxes' => array(
								gettext('Enable') => 'securityheaders_xxssprotection_enable',
								'mode=block' . ' ' . gettext('(Optional)') => 'securityheaders_xxssprotection_modeblock'
						),
						'order' => 32,
						'desc' => '<p>' . gettext('Legacy header for old browsers to protect against cross-site-scripting attacks.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-XSS-Protection')
				),
				'Referrer-Policy' => array(
						'key' => 'securityheaders_referrerpolicy',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 33,
						'selections' => array(
								gettext('disabled') => 'disabled',
								'no-referrer' => 'no-referrer',
								'no-referrer-when-downgrade' => 'no-referrer-when-downgrade',
								'origin' => 'origin',
								'origin-when-cross-origin' => 'origin-when-cross-origin',
								'same-origin' => 'same-origin',
								'strict-origin' => 'strict-origin',
								'strict-origin-when-cross-origin' => 'strict-origin-when-cross-origin',
								'unsafe-url' => 'unsafe-url'
						),
						'desc' => '<p>' . gettext('Controls how much referrer information should be sent.') . '</p>'
						. self::getStandardDesc('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy')
				)
		);
		return $options;
	}

	function handleOption($option, $currentValue) {
		
	}

	function handleOptionSave($themename, $themealbum) {
		
	}
	
	/**
	 * Checkbox options for CSP fetch directive except for 
	 * - frame-ancestors
	 * - plugin-types
	 * - sandbox
	 * 
	 * @return type
	 */
	static function getContentSecurityPolicyFetchSources($baseoption = '') {
		return array(
				"*" => $baseoption . '_wildcard',
				"'self'" => $baseoption . '_self',
				"'none'" => $baseoption . "_none",
				"'unsafe-inline'" => $baseoption . "_unsafeinline",
				"'unsafe-eval'" => $baseoption . "_unsafeeval",
				"'strict-dynamic'" => $baseoption . "_strictdynamic",
				"http:" => $baseoption . '_http',
				"https:" => $baseoption . '_https',
				'nonce-' => $baseoption . '_nonce'
		);
	}

	/**
	 * Returns default text with basically just a link to detailed documentation.
	 * 
	 * @param string $link Link to the full documentation
	 * @param bool $csptext Show additional optional text for CSP directives
	 * @return string
	 */
	static function getStandardDesc($link, $csptext = false) {
		$desc = '';
		if ($csptext) {
			$desc .= '<p>' . gettext('<em>nonce-</em> uses the internal XSRFToken automatically. Caution: A nonce attribute with <code>getXSRFToken("security_http_headers")</code> must be present on all inline script calls as they otherwise break. Inline handlers (e.g. onclick="") will not work in any case.') . '</p>';
		}
		$desc .= '<p>' . sprintf(gettext('For detailed info please see <a href="%1$s" target="_blank">%2$s</a>.'), $link, $link) . '</p>';
		return $desc;
	}
	
	static function getCOntentSecurityPolicyHostSourceDoc() {
		return '<p>' . gettext('Define one or more domains, e.g. http://yourdomain1.com http://yourdomain2.com, to allow content from') . '</p>';
	}

	/**
	 * Returns the array for the options list for CSP frame-ancestors header
	 * @return array
	 */
	static function getContentSecurityPolicyFrameAncestorsSources() {
		return array(
				"*" => 'securityheaders_csp_frameancestors_wildcard',
				"'self'" => 'securityheaders_csp_frameancestors_self',
				"'none'" => 'securityheaders_csp_frameancestors_none',
				"http:" => 'securityheaders_csp_frameancestors_http',
				"https:" => 'securityheaders_csp_frameancestors_https'
		);
	}

	/**
	 * Returns the array for the checkbox option list for the CSP Sandbox header
	 * @return array
	 */
	static function getContentSecurityPolicySandboxSources() {
		return array(
				'allow-forms' => 'securityheaders_csp_sandbox_allowforms',
				'allow-modals' => 'securityheaders_csp_sandbox_allowmodals',
				'allow-orientation-lock' => 'securityheaders_csp_sandbox_alloworientationlock',
				'allow-pointer-lock' => 'securityheaders_csp_sandbox_allowpointerlock',
				'allow-popups' => 'securityheaders_csp_sandbox_allowpopups',
				'allow-popups-to-escape-sandbox' => 'securityheaders_csp_sandbox_allowpopupstoescapesandbox',
				'allow-presentation' => 'securityheaders_csp_sandbox_allowresentation',
				'allow-same-origin' => 'securityheaders_csp_sandbox_allowsameorigin',
				'allow-scripts' => 'securityheaders_csp_sandbox_allowscripts',
				'allow-top-navigation' => 'securityheaders_csp_sandbox_allowtopnavigation',
				'allow-top-navigation-by-user-activation' => 'securityheaders_csp_sandbox_allowtopnavigationbyuseractivation'
		);
	}

	/**
	 * Returns the array for the checkbox option list for X-Frame-Option header
	 * @return array
	 */
	static function getXframeSources() {
		return array(
				gettext('disabled') => 'disabled',
				'deny' => 'deny',
				'sameorigin' => 'sameorigin',
				'allow-from' => 'allow-from'
		);
	}
	
	/**
	 * Gets the formatted mimetype list for the CSP plugin-types directive
	 * @global array $mime_types
	 * @return array
	 */
	static function getContentSecuritytPolicyPluginTypes($suffix_as_key = false) {
		require_once SERVERPATH.'/'.ZENFOLDER.'/classes/class-mimetypes.php';
		$plugintypes = array();
		foreach (mimeTypes::$mime_types as $key => $val) {
			if($suffix_as_key) {
				$key_new = $key;
			} else {
				$key_new = $key . ' (' . $val . ')';
			}
			$plugintypes[$key_new ] = 'securityheader_csp_plugintypes_' . $key;
		}
		return $plugintypes;
	}

}

/**
 * Static class to generate the headers as set on the plugin options
 */
class securityHeaders {

	/**
	 * Sets all headers, to be used with the "theme_headers" filter hook
	 */
	static function setHeaders() {
		self::setContentSecurityPolicy();
		self::setStrictTransportSecurity();
		self::setXFrameOptions();
		self::setXContentTypeOptions();
		self::setXSSProtection();
		self::setReferrerPolicy();
	}

	/**
	 * Sets the Content-Security-Policy header
	 */
	static function setContentSecurityPolicy() {
		require_once SERVERPATH . '/' . ZENFOLDER . '/classes/class-mimetypes.php';
		if (getOption('securityheaders_csp')) {
			$reportonly = '';
			if (getOption('securityheaders_csp_reportonly')) {
				$reportonly = '-Report-Only';
			}
			$csp_sources = array();
			$csp_mainoptions = array(
					'default-src' => 'securityheaders_csp_defaultsrc',
					'connect-src' => 'securityheaders_csp_connectsrc',
					'font-src' => 'securityheaders_csp_fontsrc',
					'frame-src' => 'securityheaders_csp_framesrc',
					'img-src' => 'securityheaders_csp_imgsrc',
					'manifest-src' => 'securityheaders_csp_manifestsrc',
					'media-src' => 'securityheaders_csp_mediasrc',
					'object-src' => 'securityheaders_csp_objectsrc',
					'script-src' => 'securityheaders_csp_scriptsrc',
					'style-src' => 'securityheaders_csp_stylesrc',
					'form-action' => 'securityheaders_csp_formaction',
					'base-uri' => 'securityheaders_csp_baseuri'
			);
			foreach ($csp_mainoptions as $policy => $option) {
				$csp_fetch = array();
				$csp_options = securityHeadersOptions::getContentSecurityPolicyFetchSources($option);
				$check = self::getContentSecurityPolicySources($policy, $csp_options);
				if (!empty($check)) {
					$csp_fetch[] = self::getContentSecurityPolicySources($policy, $csp_options);
				}
				if (getOption($option . '_hosts')) {
					$value = trim(getOption($option . '_hosts'));
					if (!empty($value)) {
						if (empty($csp_fetch)) {
							//if above are not set the policy name is missing here otherwise…
							$csp_fetch[] = $policy . ' ' . $value;
						} else {
							$csp_fetch[] = $value;
						}
					}
				}
				if (!empty($csp_fetch)) {
					$csp_sources[] = implode(' ', $csp_fetch);
				}
			}
			
			$csp_plugintypes_options = securityheadersOptions::getContentSecuritytPolicyPluginTypes(true);
			$csp_plugintypes = array();
			foreach($csp_plugintypes_options as $key => $val) {
				$plugintype = getOption($val);
				if($plugintype) {
					$csp_plugintypes[] = mimeTypes::$mime_types[$key];
				}
			} 
			if(!empty($csp_plugintypes)) {
				$csp_sources[] = 'plugin-types ' . implode(' ', $csp_plugintypes);
			}

			$csp_sandbox = securityheadersOptions::getContentSecurityPolicySandboxSources();
			$check_sandbox = self::getContentSecurityPolicySources('sandbox', $csp_sandbox);
			if (!empty($check_sandbox)) {
				$csp_sources[] = $check_sandbox;
			}

			$csp_frameancestor_sources = array();
			$csp_frameancestors = array(
					"*" => 'securityheaders_csp_frameancestors_wildcard',
					"'self'" => 'securityheaders_csp_frameancestors_self',
					"'none'" => 'securityheaders_csp_frameancestors_none',
					"http:" => 'securityheaders_csp_frameancestors_http',
					"https:" => 'securityheaders_csp_frameancestors_https'
			);
			$check_frameancestors = self::getContentSecurityPolicySources('frame-ancestors', $csp_frameancestors);
			if (!empty($check_frameancestors)) {
				$csp_frameancestor_sources[] = $check_frameancestors;
			}
			if (getOption('securityheaders_csp_frameancestors_hosts')) {
				$value = trim(getOption('securityheaders_csp_frameancestors_hosts'));
				if (!empty($value)) {
					if (empty($csp_frameancestor_sources)) {
						//if above are not set the policy name is missing here otherwise…
						$csp_frameancestor_sources[] = 'frame-ancestors ' . $value;
					} else {
						$csp_frameancestor_sources[] = $value;
					}
				}
			}
			if (!empty($csp_frameancestor_sources)) {
				$csp_sources[] = implode(' ', $csp_frameancestor_sources);
			}

			if (getOption('securityheaders_csp_upgradeinsecurerequests')) {
				$csp_sources[] = 'upgrade-insecure-requests';
			}

			if (!empty($csp_sources)) {
				$csp_final = implode('; ', $csp_sources);
				$csp_header = 'Content-Security-Policy' . $reportonly . ': ' . $csp_final;
				header($csp_header);
			}
		}
	}

	/**
	 * Sets the Strict-Transport-Security header
	 */
	static function setStrictTransportSecurity() {
		$hsts = getOption('securityheaders_hsts');
		if ($hsts) {
			$header = 'Strict-Transport-Security: max-age=' . $hsts;
			if (getOption('securityheaders_hsts_includesubdomains')) {
				$header .= '; includeSubDomains';
			}
			if (getOption('securityheaders_hsts_preload')) {
				$header .= '; preload';
			}
			header($header);
		}
	}

	/**
	 * Sets the X-Frame-Options header
	 */
	static function setXFrameOptions() {
		$xframeoptions = getOption('securityheaders_xframeoptions');
		if ($xframeoptions && $xframeoptions != 'disabled') {
			$allowfrom = getOption('securityheaders_csp_xframeoptions_allow-from_hosts');
			if ($xframeoptions == 'allow-from' && $allowfrom) {
				header('X-Frame-Options: allow-from ' . $allowfrom);
			} else {
				header('X-Frame-Options: ' . $xframeoptions);
			}
		}
	}

	/**
	 * Sets the X-Content-Type-Options header
	 */
	static function setXContentTypeOptions() {
		if (getOption('securityheaders_xcontentnosniff')) {
			header('X-Content-Type-Options: nosniff');
		}
	}

	/**
	 * Sets the X-XSS-Protection header
	 */
	static function setXSSProtection() {
		if (getOption('securityheaders_xxssprotection_enable')) {
			$header = 'X-XSS-Protection: 1';
			if (getOption('securityheaders_xxssprotection_modeblock')) {
				$header .= '; mode:block';
			}
			header($header);
		}
	}

	/**
	 * Sets the Referrer-Policy header
	 */
	static function setReferrerPolicy() {
		$referrerpolicy = getOption('securityheaders_referrerpolicy');
		if ($referrerpolicy && $referrerpolicy != 'disabled') {
			header('Referrer-Policy: ' . $referrerpolicy);
		}
	}

	/**
	 * Gets headers from checkbox lists type options and returns them as string setup for header usage
	 * 
	 * @param string $policyname The CSP policy name
	 * @param array $checkboxoptions The extra sources via checkbox options for the header. Key of entry is the source to set, value the option name as these are 
	 * 
	 * @return string
	 */
	static function getContentSecurityPolicySources($policyname, $checkboxoptions) {
		$policies = array();
		foreach ($checkboxoptions as $source => $option) {
			$policy = '';
			if (getOption($option)) {
				if ($source == 'nonce-') {
					$policies[] = $source . getXSRFToken('security_http_headers');
				} else {
					$policies[] = trim($source);
				}
			}
		}
		if (!empty($policies)) {
			$implode = implode(' ', $policies);
			$header = trim($policyname . ' ' . $implode);
		}
		if (!empty($header)) {
			return $header;
		}
	}

}
