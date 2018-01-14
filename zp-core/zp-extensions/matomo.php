<?php
/**
 *
 * This module helps you to keep track of your Zenphoto visitors through the Matomo platform (formerly known as Piwik).
 * It places the <i>Matomo JavaScript tracking scripts</i> at the bottom of your webpages using the <i>theme_body_close</i>
 * filter. It also supports tracking for users with JavaScript disabled.
 *
 * If you do not want particular pages to be tracked you should pass an array containing <var>"matomo_tag"</var> as the
 * <i>exclude</i> parameter to the theme page body close filter application. e.g.
 * <code>zp_apply_filter('theme_body_close',array("matomo_tag"));</code>
 *
 * Additionally it provides content macro [MATOMO_OPTOUT] that embeds a facility for visitors to optout of tracking as required by the law of several countries.
 * Place this on your privacy statement page.
 *
 * You can also add Matomo widget iFrame code to view your statistics via a Zenphoto backend utility.
 *
 * Please visit the Matomo site for the Matomo software and installation instructions.
 *
 * <hr>
 *
 * Quoted from [matomo.org](http://matomo.org).
 *
 *  Matomo is a downloadable, open source (GPL licensed) real time web analytics software program.
 *  It provides you with detailed reports on your website visitors:
 *  the search engines and keywords they used, the language they speak, your popular pages... and so much more.
 *
 *  Matomo aims to be an open source alternative to Google Analytics.
 *
 * @package plugins/matomo
 * @pluginCategory seo
 */
$plugin_is_filter = 9 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext('A plugin to insert your Matomo (formerly Piwik) JavaScript tracking code into your theme pages.');
$plugin_author = "Stephen Billard (sbillard), Malte Müller (acrylian)";

$option_interface = 'matomoStats';

if (!getOption('matomo_admintracking') || !zp_loggedin(ADMIN_RIGHTS)) {
	zp_register_filter('theme_body_close', 'matomoStats::script');
}
if (getOption('matomo_widgets_code')) {
	zp_register_filter('admin_tabs', 'matomoStats::admin_tabs');
}
zp_register_filter('content_macro', 'matomoStats::macro');

class matomoStats {

	function __construct() {
		global $testRelease;
		if (OFFSET_PATH == 2) {
			//migrate piwik plugin options
			$old = getOptionsLike('piwik_');
			foreach ($old as $key => $value) {
//				purgeOption($key);
				setOption(str_replace('piwik', 'matomo', $key), $value);
			}
			if (!empty($old)) {
				setupLog('Plugin:matomo ' . gettext('Piwik options migrated to Matomo plugin'), $testRelease);
			}
			if (extensionEnabled('piwik')) {
				enableExtension('matomo', 9 | ADMIN_PLUGIN | THEME_PLUGIN);
			}
		}
	}

	function getOptionsSupported() {
		return array(
				gettext('Matomo url') => array(
						'key' => 'matomo_url',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 0,
						'desc' => gettext('Enter your Matomo installation URL including protocol (e.g. <code>https://domain.com</code>).')),
				gettext('site id') => array(
						'key' => 'matomo_id',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext('Enter the site id assigned by Matomo.')),
				gettext('Enable Admin tracking') => array(
						'key' => 'matomo_admintracking',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 2,
						'desc' => gettext('Controls if you want Matomo to track users with <code>Admin</code> rights.')),
				gettext('Main domain for subdomain tracking') => array(
						'key' => 'matomo_sitedomain',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'multilingual' => false,
						'desc' => gettext('Enter your site domain name if you also like to track all subdomains of it. Enter like <code>domain.com</code>.')),
				gettext('Widgets: Embed code') => array(
						'key' => 'matomo_widgets_code',
						'type' => OPTION_TYPE_TEXTAREA,
						'order' => 1,
						'multilingual' => false,
						'desc' => gettext('Enter widget iframe code if you like to embed statistics to your Zenphoto backend. You can view it via a utility button afterwards. Visit the widget area on your Matomo install for more info.'))
		);
	}

	static function script($exclude = NULL) {
		if (empty($exclude) || (!in_array('matomo_tag', $exclude))) {
			$url = getOption('matomo_url');
			$id = getOption('matomo_id');
			$sitedomain = trim(getOption('matomo_sitedomain'));
			?>
			<!-- Matomo -->
			<script type="text/javascript">
				var _paq = _paq || [];
			<?php if ($sitedomain) { ?>
					_paq.push(["setDocumentTitle", document.domain + "/" + document.title]);
					_paq.push(["setCookieDomain", "*.<?php echo $sitedomain; ?>"]);
			<?php } ?>
				_paq.push(['trackPageView']);
				_paq.push(['enableLinkTracking']);
				(function () {
					var u = "//<?php echo str_replace(array('http://', 'https://'), '', $url); ?>/";
					_paq.push(['setTrackerUrl', u + 'piwik.php']);
					_paq.push(['setSiteId', <?php echo $id; ?>]);
					var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
					g.type = 'text/javascript';
					g.defer = true;
					g.async = true;
					g.src = u + 'piwik.js';
					s.parentNode.insertBefore(g, s);
				})();
			</script>
			<noscript><p><img src="<?php echo $url ?>/piwik.php?idsite=<?php echo $id ?>&rec=1" style="border:0" alt="" /></p></noscript>
			<!-- End Matomo Tag -->
			<?php
		}
		return $exclude;
	}

	static function admin_tabs($tabs) {
		if (zp_loggedin(OVERVIEW_RIGHTS)) {
			$tabs['overview']['subtabs'][gettext('Matomo statistics')] = '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/matomo/matomo_tab.php';
		}
		return $tabs;
	}

	/**
	 * Gets the iframe for the optout cookie required by privacy laws of several countries.
	 * @return string
	 */
	static function getOptOutiFrame() {
		$userlocale = substr(getUserLocale(), 0, 2);
		$url = getOption('matomo_url');
		$src = $url . '/index.php?module=CoreAdminHome&action=optOut&language=' . $userlocale;
		return '<iframe style="border: 0; height: 200px; width: 100%;" src="' . $src . '"></iframe>';
	}

	static function macro($macros) {
		$macros['MATOMO_OPTOUT'] = array(
				'class' => 'function',
				'params' => array(),
				'value' => 'matomoStats::getOptOutiFrame',
				'owner' => 'matomoStats',
				'desc' => gettext('Inserts the iframe with the opt-out cookie code as entered on the related plugin option.')
		);
		return $macros;
	}

}
?>