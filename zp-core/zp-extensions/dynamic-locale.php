<?php
/**
 * A plugin to allow the site viewer to select a localization.
 * This applies only to the theme pages--not Admin. Admin continues to use the
 * language option for its language.
 *
 * Only the zenphoto and theme gettext() string are localized by this facility.
 *
 * If you want to support image descriptions, etc. in multiple languages you will
 * have to enable the multi-lingual option found next to the language selector on
 * the admin gallery configuration page. Then you will have to provide appropriate
 * alternate translations for the fields you use. While there will be a place for
 * strings for all zenphoto supported languages you need supply only those you choose.
 * The others language strings will default to your local language.
 *
 * Uses cookies to store the individual selection. Sets the 'locale' option
 * to the selected language (non-persistent.)
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_description = gettext("Enable <strong>dynamic-locale</strong> to allow viewers of your site to select the language translation of their choice.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'dynamic_locale';

zp_register_filter('theme_head', 'dynamic_locale::dynamic_localeJS');

define('SUBDOMAIN_LOCALES',getOption('dynamic_locale_subdomain'));
define('SEO_LOCALES',function_exists('filterLocale_load_request'));

/**
 * prints a form for selecting a locale
 * The POST handling is by getUserLocale() called in functions.php
 *
 */
function printLanguageSelector($flags=NULL) {
	$languages = generateLanguageList();
	if (isset($_REQUEST['locale'])) {
		$locale = sanitize($_REQUEST['locale'], 0);
		if (getOption('locale') != $locale) {
			?>
			<div class="errorbox">
				<h2>
					<?php printf(gettext('<em>%s</em> is not available.'),$languages[$locale]); ?>
					<?php printf(gettext('The locale %s is not supported on your server.'), $locale); ?>
					<br />
					<?php echo gettext('See the troubleshooting guide on zenphoto.org for details.'); ?>
				</h2>
			</div>
			<?php
		}
	}
	if (is_null($flags)) {
		$flags = getOption('dynamic_locale_visual');
	}
	if ($flags) {
		?>
		<ul class="flags">
			<?php
			$_languages = generateLanguageList();

			$currentValue = getOption('locale');
			foreach ($_languages as $text=>$lang) {
				?>
				<li<?php if ($lang==$currentValue) echo ' class="currentLanguage"'; ?>>
					<?php
					if ($lang!=$currentValue) {
						if (SUBDOMAIN_LOCALES) {
							?>
							<a href="<?php echo dynamic_locale::fullHostPath().html_encode($_SERVER['REQUEST_URI']); ?>" >
							<?php
						} else if (SEO_LOCALES) {
							?>
							<a href="<?php echo html_encode(str_replace(WEBPATH, WEBPATH.'/'.substr($lang,0,2), $_SERVER['REQUEST_URI'])); ?>" >
							<?php
						} else {
							?>
							<a href="?locale=<?php echo $lang; ?>" >
							<?php
						}
					}
					$flag = getLanguageFlag($lang);
					?>
					<img src="<?php echo $flag; ?>" alt="<?php echo $text; ?>" title="<?php echo $text; ?>" />
					<?php
					if ($lang!=$currentValue) {
						?>
						</a>
						<?php
					}
					?>
				</li>
				<?php
			}
			unset($_languages);
			?>
		</ul>
		<?php
	} else {
		?>
		<form action="#" method="post">
			<input type="hidden" name="oldlocale" value="<?php echo getOption('locale'); ?>" />
			<select id="dynamic-locale" class="languageselect" name="locale" onchange="this.form.submit()">
			<?php
			$locales = generateLanguageList();
			$currentValue = getOption('locale');
			foreach($locales as $key=>$item) {
				echo '<option class="languageoption" value="' . html_encode($item) . '"';
				if ($item==$currentValue) {
					echo ' selected="selected"';
				}
				echo ' >';
				echo html_encode($key)."</option>\n";
			}
			?>
			</select>
		</form>
	<?php
	}
}

class dynamic_locale {

	function __construct() {
		setOptionDefault('dynamic_locale_visual', 0);
		setOptionDefault('dynamic_locale_subdomain', 0);
	}

	function getOptionsSupported() {
		return array(	gettext('Use flags') => array('key' => 'dynamic_locale_visual', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext('Checked produces an array of flags. Not checked produces a selector.')),
									gettext('Use subdomains') => array('key' => 'dynamic_locale_subdomain', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => '<p>'.gettext('If checked links to the alternative languages will be in the form <code><em>language</em>.domain</code> where <code><em>language</em></code> is the two letter language code, e.g. <code><em>fr</em></code> for French.').'</p><p>'.gettext('This requires that you have created the appropriate subdomains pointing to your Zenphoto installation. That is <code>fr.mydomain.com/zenphoto/</code> must point to the same location as <code>mydomain.com/zenphoto/</code>. (Some providers will automatically redirect undefined subdomains the main domain. If your provier does this, no subdomain creation is needed.)').'</p>')
								);
	}

	static function dynamic_localeJS() {
		?>
		<link type="text/css" rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER; ?>/dynamic-locale/locale.css" />
		<?php
	}

	static function fullHostPath() {
		$host = $_SERVER['HTTP_HOST'];
		$matches = explode('.',$host);
		if (validateLocale($matches[0], 'Dynamic Locale')) {
			array_shift($matches);
			$host = implode('.',$matches);
		}
		$subdomain = substr($lang,0,2);
		$host = $subdomain.'.'.$host;
		if (SERVER_PROTOCOL == 'https') {
			$host = 'https://'.$host;
		} else {
			$host = 'http://'.$host;
		}
		return $host;
	}


}

?>