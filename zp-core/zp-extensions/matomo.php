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
 * Additionally a content macro [MATOMO_OPTOUT] is provided that embeds a facility for visitors to optout of tracking as required by the law of several countries.
 * Place this on your privacy statement page.
 *
 * You can add Matomo widget iFrame code to view your statistics via a Zenphoto backend utility.
 *
 * Please visit the {@link https://matomo.org/docs/ Matomo} site for the Matomo software and installation instructions.
 *
 * <hr>
 *
 * Quoted from {@link http://matomo.org <b>matomo.org</b>}.
 *
 *  Matomo is a downloadable, open source (GPL licensed) real time web analytics software program.
 *  It provides you with detailed reports on your website visitors:
 *  the search engines and keywords they used, the language they speak, your popular pages... and so much more.
 *
 *  Matomo aims to be an open source alternative to Google Analytics.
 *
 * @author Stephen Billard (sbillard), Malte Müller (acrylian)
 *
 *
 * @package plugins/matomo
 * @pluginCategory seo
 */
$plugin_is_filter = 9 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext('A plugin to insert Matomo (formerly Piwik) JavaScript tracking code into theme pages.');

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
				purgeOption($key);
				setOption(str_replace('piwik', 'matomo', $key), $value);
			}
			if (!empty($old)) {
				setupLog('Plugin:matomo ' . gettext('Piwik options migrated to Matomo plugin'), $testRelease);
			}
			if (extensionEnabled('piwik')) {
				enableExtension('matomo', 9 | ADMIN_PLUGIN | THEME_PLUGIN);
			}
			setOptionDefault('matomo_disablecookies', 0);
		}
		setOptionDefault('matomo_requireconsent', 'no-consent');
	}

	function getOptionsSupported() {
		$langs = $langs_list = array();
		$langs_list = generateLanguageList();
		foreach ($langs_list as $text => $lang) {
			$langs[$text] = $lang;
		}
		return array(
				gettext('Matomo url') => array(
						'key' => 'matomo_url',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 0,
						'desc' => sprintf(gettext('Enter your Matomo installation URL including protocol (e.g. <code>%s</code>).'), FULLHOSTPATH)),
				gettext('Site id') => array(
						'key' => 'matomo_id',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext('Enter the id assigned to your site by Matomo.')),
				gettext('Admin tracking') => array(
						'key' => 'matomo_admintracking',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 3,
						'desc' => gettext('Check if you want Matomo to track users with <code>Admin</code> rights.')),
				gettext('Site domain') => array(
						'key' => 'matomo_sitedomain',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 4,
						'multilingual' => false,
						'desc' => sprintf(gettext('Enter your site domain name (e.g. <code>%s</code>) if you would like to track all your subdomains.'), $_SERVER['HTTP_HOST'])),
				gettext('Widgets: Embed code') => array(
						'key' => 'matomo_widgets_code',
						'type' => OPTION_TYPE_TEXTAREA,
						'order' => 5,
						'multilingual' => false,
						'desc' => gettext('Enter widget iframe code if you like to embed statistics to your Zenphoto backend. This enables MATOMO STATISTICS on the OVERVIEW fly-out menu. Visit <a href="https://developer.matomo.org/guides/widgets">Matomo guides</a> for more information.')),
				gettext('Language to track') => array(
						'order' => 6,
						'key' => 'matomo_language_tracking',
						'type' => OPTION_TYPE_SELECTOR,
						'null_selection' => 'HTTP_Accept_Language',
						'selections' => $langs,
						'desc' => gettext('Select which language you want use when you track page titles. Selecting <em>HTTP Accept Language</em> will use the visitor\'s language. Selecting a single language avoids tracking multiple titles per page. <strong>Note</strong>: Selecting a single language is not recommend for SEO reasons. Each language version of a page really is separate content.')),
				gettext('Disable cookies') => array(
						'key' => 'matomo_disablecookies',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 7,
						'desc' => gettext('Check this so Matomo does not use cookies to track visitors (less accurate tracking).')),
				gettext('User onsent') => array(
						'key' => 'matomo_requireconsent',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => array(
								gettext('Not required') => 'no-consent',
								gettext('Required') => 'consent-required',
								gettext('Required and remembered') => 'consent-required-remembered'
						),
						'order' => 8,
						'desc' => gettext('How Matomo will deal with users consent about tracking statistics. <em>Required and remembered</em> requires cookies.'))
		);
	}

	/**
	 * Adds the Matomo statistic script
	 */
	static function script($exclude = NULL) {
		if (empty($exclude) || (!in_array('matomo_tag', $exclude))) {
			$url = getOption('matomo_url');
			$id = getOption('matomo_id');
			$sitedomain = trim(getOption('matomo_sitedomain'));
			?>
			<!-- Matomo -->
			<script type="text/javascript">
				var _paq = _paq || [];
				_paq.push(["setDocumentTitle", '<?php echo matomoStats::printDocumentTitle(); ?>']);
			<?php
			if ($sitedomain) {
				switch (getOption('matomo_requireconsent')) {
					case 'no-consent':
						break;
					case 'consent-required-remembered':
						?>
							_paq.push(['rememberConsentGiven']);
						<?php
						break;
					default:
						?>
							_paq.push(['requireConsent']);
						<?php
						break;
				}
				?>
					_paq.push(["setCookieDomain", "*.<?php echo $sitedomain; ?>"]);
				<?php
			}
			if (getOption('matomo_disablecookies')) {
				?>
					_paq.push(['disableCookies']);
				<?php
			}
			?>
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

	/**
	 * The macro button for the utility page
	 * @param type $macros
	 * @return type
	 */
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

	/**
	 * Gets the document title of the current page to track. Gets the title in a single language only if the option for single_language_tracking is set
	 * @global string $_zp_current_locale
	 */
	static function printDocumentTitle() {
		global $_zp_current_locale;

		$locale_to_track = getOption('matomo_language_tracking');
		if ($locale_to_track != $_zp_current_locale && $locale_to_track != NULL) {
			$original_locale = getOption('locale');
			setOption('locale', $locale_to_track, false);
			setupCurrentLocale($locale_to_track);
		}
		echo js_encode(getHeadTitle());
		if ($original_locale != NULL) {
			setOption('locale', $original_locale, false);
			setupCurrentLocale($original_locale);
		}
	}

}
