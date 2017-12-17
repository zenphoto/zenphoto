<?php
/**
 *
 * This module helps you to keep track of your Zenphoto visitors through the Piwik platform.
 * It places the <i>Piwik JavaScript tracking scripts</i> at the bottom of your webpages using the <i>theme_body_close</i>
 * filter. It also supports tracking for users with JavaScript disabled.
 * 
 * If you do not want particular pages to be tracked you should pass an array containing <var>"piwik_tag"</var> as the
 * <i>exclude</i> parameter to the theme page body close filter application. e.g.
 * <code>zp_apply_filter('theme_body_close',array("piwik_tag"));</code>
 * 
 * Additionally it provides content macro [PIWIK_OPTOUT] that embeds a facility for visitors to optout of tracking as required by the law of several countries.
 * Place this on your privacy statement page.
 * 
 * You can also add Piwik widget iFrame code to view your statistics via a Zenphoto backend utility.
 *
 * Please visit the Piwik site for the piwik software and installation instructions.
 *
 * <hr>
 *
 * Quoted from [piwik.org](http://piwik.org).
 *
 *  Piwik is a downloadable, open source (GPL licensed) real time web analytics software program.
 *  It provides you with detailed reports on your website visitors:
 *  the search engines and keywords they used, the language they speak, your popular pages... and so much more.
 *
 *  Piwik aims to be an open source alternative to Google Analytics.
 *
 * @package plugins
 * @subpackage piwik
 */
$plugin_is_filter = 9 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext('A plugin to insert your Piwik JavaScript tracking code into your theme pages.');
$plugin_author = "Stephen Billard (sbillard), Malte Müller (acrylian)";
$plugin_category = gettext('Statistics');

$option_interface = 'piwikStats';

if (!getOption('piwik_admintracking') || !zp_loggedin(ADMIN_RIGHTS)) {
	zp_register_filter('theme_body_close', 'piwikStats::script');
}
if (getOption('piwik_widgets_code')) {
	zp_register_filter('admin_utilities_buttons', 'piwikStats::button');
}
zp_register_filter('content_macro', 'piwikStats::macro');

class piwikStats {

	function __construct() {
		
	}

	function getOptionsSupported() {
		return array(
				gettext('Piwik url') => array(
						'key' => 'piwik_url',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 0,
						'desc' => gettext('Enter your Piwik installation URL including protocol (e.g. <code>https://domain.com</code>).')),
				gettext('site id') => array(
						'key' => 'piwik_id',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext('Enter the site id assigned by Piwik.')),
				gettext('Enable Admin tracking') => array(
						'key' => 'piwik_admintracking',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 2,
						'desc' => gettext('Controls if you want Piwik to track users with <code>Admin</code> rights.')),
				gettext('Main domain for subdomain tracking') => array(
						'key' => 'piwik_sitedomain',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'multilingual' => false,
						'desc' => gettext('Enter your site domain name if you also like to track all subdomains of it. Enter like <code>domain.com</code>.')),
				gettext('Widgets: Embed code') => array(
						'key' => 'piwik_widgets_code',
						'type' => OPTION_TYPE_TEXTAREA,
						'order' => 1,
						'multilingual' => false,
						'desc' => gettext('Enter widget iframe code if you like to embed statistics to your Zenphoto backend. You can view it via a utility button afterwards. Visit the widget area on your Piwik install for more info.'))
				);
	}

	static function script($exclude = NULL) {
		if (empty($exclude) || !in_array('piwik_tag', $exclude)) {
			$piwik_url = getOption('piwik_url');
			$piwik_id = getOption('piwik_id');
			$piwik_sitedomain = trim(getOption('piwik_sitedomain'));
			?>
			<!-- Piwik -->
			<script type="text/javascript">
				var _paq = _paq || [];
			<?php if ($piwik_sitedomain) { ?>
					_paq.push(["setDocumentTitle", document.domain + "/" + document.title]);
					_paq.push(["setCookieDomain", "*.<?php echo $piwik_sitedomain; ?>"]);
			<?php } ?>
				_paq.push(['trackPageView']);
				_paq.push(['enableLinkTracking']);
				(function () {
					var u = "//<?php echo str_replace(array('http://', 'https://'), '', $piwik_url); ?>/";
					_paq.push(['setTrackerUrl', u + 'piwik.php']);
					_paq.push(['setSiteId', <?php echo $piwik_id; ?>]);
					var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
					g.type = 'text/javascript';
					g.defer = true;
					g.async = true;
					g.src = u + 'piwik.js';
					s.parentNode.insertBefore(g, s);
				})();
			</script>
			<noscript><p><img src="<?php echo $piwik_url ?>/piwik.php?idsite=<?php echo $piwik_id ?>&rec=1" style="border:0" alt="" /></p></noscript>
			<!-- End Piwik Tag -->
			<?php
		}
		return $exclude;
	}

	static function button($buttons) {
		$buttons[] = array(
				'category' => gettext('Info'),
				'enable' => true,
				'button_text' => gettext('Piwik statistics'),
				'formname' => 'piwik_button',
				'action' => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/piwik/piwik_tab.php',
				'icon' => WEBPATH . '/' . ZENFOLDER . '/images/bar_graph.png',
				'title' => gettext('View Piwik statisics of your site'),
				'alt' => '',
				'hidden' => '',
				'rights' => ADMIN_RIGHTS
		);
		return $buttons;
	}

	/**
	 * Gets the iframe for the optout cookie required by privacy laws of several countries.
	 * @return string
	 */
	static function getOptOutiFrame() {
		$userlocale = substr(getUserLocale(), 0, 2);
		$piwik_url = getOption('piwik_url');
		$src = $piwik_url . '/index.php?module=CoreAdminHome&action=optOut&language=' . $userlocale;
		return '<iframe style="border: 0; height: 200px; width: 100%;" src="' . $src . '"></iframe>';
	}

	static function macro($macros) {
		$macros['PIWIK_OPTOUT'] = array(
				'class' => 'function',
				'params' => array(),
				'value' => 'piwikStats::getOptOutiFrame',
				'owner' => 'piwikStats',
				'desc' => gettext('Inserts the iframe with the opt-out cookie code as entered on the related plugin option.')
		);
		return $macros;
	}

}
?>