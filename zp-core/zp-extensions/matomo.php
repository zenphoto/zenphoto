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
 * @author Stephen Billard (sbillard), Malte Müller (acrylian), Vincent Bourganel (vincent3569)
 * @package zpcore\plugins\matomo
 */
$plugin_is_filter = 9 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext('A plugin to insert your Matomo (formerly Piwik) JavaScript tracking code into your theme pages.');
$plugin_author = "Stephen Billard (sbillard), Malte Müller (acrylian), Vincent Bourganel (vincent3569)";
$plugin_category = gettext('Statistics');

$option_interface = 'matomoStats';

if (getOption('matomo_admintracking') || !zp_loggedin(ADMIN_RIGHTS)) {
	zp_register_filter('theme_body_close', 'matomoStats::script');
}
if (getOption('matomo_widgets_code')) {
	zp_register_filter('admin_utilities_buttons', 'matomoStats::button');
}
zp_register_filter('content_macro', 'matomoStats::macro');

class matomoStats {

	function __construct() {
		if (getOption('piwik_url')) {
			setOption('matomo_url', getOption('piwik_url'));
			purgeOption('piwik_url');
		}
		if (getOption('piwik_id')) {
			setOption('matomo_id', getOption('piwik_id'));
			purgeOption('piwik_id');
		}
		if (getOption('piwik_admintracking')) {
			setOption('matomo_admintracking', getOption('piwik_admintracking'));
			purgeOption('piwik_admintracking');
		}
		if (getOption('piwik_sitedomain')) {
			setOption('matomo_sitedomain', getOption('piwik_sitedomain'));
			purgeOption('piwik_sitedomain');
		}

		if (getOption('piwik_widgets_code')) {
			setOption('matomo_widgets_code', getOption('piwik_widgets_code'));
			purgeOption('piwik_widgets_code');
		}
		setOptionDefault('matomo_disablecookies', 0);
		setOptionDefault('matomo_requireconsent', 'no-consent');
		setOptionDefault('matomo_contenttracking', 'disabled');
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
						'desc' => gettext('Enter your Matomo installation URL including protocol (e.g. <code>https://domain.com</code>).')),
				gettext('Site id') => array(
						'key' => 'matomo_id',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext('Enter the site id assigned by Matomo.')),
				gettext('Enable Admin tracking') => array(
						'key' => 'matomo_admintracking',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 2,
						'desc' => gettext('Controls if you want Matomo to track users with <code>Admin</code> rights.')),
				gettext('Content tracking') => array(
						'key' => 'matomo_contenttracking',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => array(
								gettext('Track all content') => 'all-content',
								gettext('Track visible content only') => 'visible-content',
								gettext('Disable content tracking') => 'disabled'
						),
						'order' => 3,
						'desc' => gettext('Controls if you want Matomo to track content interaction (e.g. link clicks). Your theme/plugins/site will require specific HTML markup for this to work. Read more about it on <a href="https://developer.matomo.org/guides/content-tracking">Content tracking</a>.')),
				gettext('Main domain for subdomain tracking') => array(
						'key' => 'matomo_sitedomain',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 4,
						'multilingual' => false,
						'desc' => gettext('Enter your site domain name if you also like to track all subdomains of it. Enter like <code>domain.com</code>.')),
				gettext('Widgets: Embed code') => array(
						'key' => 'matomo_widgets_code',
						'type' => OPTION_TYPE_TEXTAREA,
						'order' => 5,
						'multilingual' => false,
						'desc' => gettext('Enter widget iframe code if you like to embed statistics to your Zenphoto backend. You can view it via a utility button afterwards. Visit the widget area on your Matomo install for more info.')),
				gettext('Language to track') => array(
								'order' => 6,
								'key' => 'matomo_language_tracking',
								'type' => OPTION_TYPE_SELECTOR,
								'null_selection' => 'HTTP_Accept_Language',
								'selections' => $langs,
								'desc'=> gettext('Select in which language you want to track page titles. If none, the visitor language is used. '
												. 'If you choose a single language it avoids tracking multiple title per page. '
												. 'Note: It is rather not recommend to use this for SEO reasons as each language version of a page does count as separate content.')),
				gettext('Disable cookies') => array(
						'key' => 'matomo_disablecookies',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 7,
						'desc' => gettext('Enable this so Matomo does not use cookies to track visitors (less accurate tracking).')),
				gettext('Require consent') => array(
						'key' => 'matomo_requireconsent',
						'type' => OPTION_TYPE_RADIO,
						'buttons' => array(
								gettext('No consent required') => 'no-consent',
								gettext('Consent required') => 'consent-required',
								gettext('Consent required and remember consent*') => 'consent-required-remembered'
						),
						'order' => 8,
						'desc' => gettext('Enable this so Matomo will ask users for consent about tracking statistics. *requires cookies.'))
		);
	}

	/**
	 * Adds the Matomo statistic script
	 */
	static function script($exclude = NULL) {
		if (empty($exclude) || (!in_array('matomo_tag', $exclude))) {
			$url = getOption('matomo_url');
			$url = strval($url);
			$id = getOption('matomo_id');
			$sitedomain = trim(strval(getOption('matomo_sitedomain')));
			$requireconsent = getOption('matomo_requireconsent');
			switch($requireconsent) {
				case 'no-consent':
					$requireconsent_js = '';
					break;
				default:
				case 'consent-required':
					$requireconsent_js = "_paq.push(['requireCookieConsent']);";
					$requireconsent_js .= "_paq.push(['requireConsent']);";
					break;
				
				case 'consent-required-remembered':
					$requireconsent_js = "_paq.push(['requireCookieConsent']);";
					$requireconsent_js .= "\n_paq.push(['rememberConsentGiven']);";
					break;
			}
			?>
			<!-- Matomo -->
			<script>
				var _paq = _paq || [];
				_paq.push(["setDocumentTitle", '<?php echo matomoStats::printDocumentTitle(); ?>']);	
				<?php if ($sitedomain) { ?>
					_paq.push(["setCookieDomain", "*.<?php echo $sitedomain; ?>"]);
				<?php } 
				echo $requireconsent_js; 
				if(getOption('matomo_disablecookies')) { ?>
					_paq.push(['disableCookies']);
				<?php } ?>
				_paq.push(['trackPageView']);
				_paq.push(['enableLinkTracking']);
				<?php 
				switch(getOption('matomo_contenttracking')) {
					case 'all-content':
						?>
						_paq.push(['trackAllContentImpressions']);
						<?php
						break;
					case 'visible-content':
						?>
						_paq.push(['trackVisibleContentImpressions']);
						<?php
						break;
				}
				?>
				(function () {
					var u = "//<?php echo str_replace(array('http://', 'https://'), '', $url); ?>/";
					_paq.push(['setTrackerUrl', u + 'matomo.php']);
					_paq.push(['setSiteId', '<?php echo $id; ?>']);
					var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
					g.type = 'text/javascript';
					g.defer = true;
					g.async = true;
					g.src = u + 'matomo.js';
					s.parentNode.insertBefore(g, s);
				})();
			</script>
			<noscript><p><img src="<?php echo $url ?>/matomo.php?idsite=<?php echo $id ?>&rec=1" style="border:0" alt="" /></p></noscript>
			<!-- End Matomo Tag -->
			<?php
		}
		return $exclude;
	}

	static function button($buttons) {
		$buttons[] = array(
				'category' => gettext('Info'),
				'enable' => true,
				'button_text' => gettext('Matomo statistics'),
				'formname' => 'matomo_button',
				'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/matomo/matomo_tab.php',
				'icon' => FULLWEBPATH . '/' . ZENFOLDER . '/images/bar_graph.png',
				'title' => gettext('View Matomo statisics of your site'),
				'alt' => '',
				'hidden' => '',
				'rights' => ADMIN_RIGHTS
		);
		return $buttons;
	}

	/**
	 * Gets the iframe for the optout cookie required by privacy laws of several countries.
	 * @since 1.6.1
	 * @return string
	 */
	static function getOptOutForm() {
		$userlocale = substr(getUserLocale(), 0, 2);
		$url = strval(getOption('matomo_url'));
		if (!empty($url)) {
			$src = $url . '/index.php?module=CoreAdminHome&action=optOutJS&divId=matomo-opt-out&language=' . $userlocale . '&showIntro=1';
			$html = '<div id="matomo-opt-out"></div>';
			$html .= '<script src="' . $src . '"></script>';
			return $html;
		}
	}

	/**
	 * @deprecated 2.0 - Use matomoStats::getOptOutForm()
	 */
	static function getOptOutiFrame() {
		return matomoStats::getOptOutForm();
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
				'value' => 'matomoStats::getOptOutForm',
				'owner' => 'matomoStats',
				'desc' => gettext('Inserts the the opt-out cookie code as entered on the related plugin option.')
		);
		return $macros;
	}
	
	/**
	 * Gets the document title of the current page to track. Gets the title in a single language only if the option for single_language_tracking is set
	 * @global string $_zp_current_locale
	 */
	static function printDocumentTitle() {
		global $_zp_current_locale;
		$original_locale = null;
		$locale_to_track = getOption('matomo_language_tracking');
		if ($locale_to_track != $_zp_current_locale && $locale_to_track != null) {
			$original_locale = getOption('locale');
			setOption('locale', $locale_to_track, false);
			setupCurrentLocale($locale_to_track);
		}
		echo getHeadTitle();
		if ($original_locale != null) {
			setOption('locale', $original_locale, false);
			setupCurrentLocale($original_locale);
		}
	}
}