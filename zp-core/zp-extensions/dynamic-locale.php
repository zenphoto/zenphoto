<?php
/**
 * Allow the site viewer to select a localization.
 *
 * Only the zenphoto and theme gettext() string are localized by this facility.
 *
 * If you want to support image descriptions, etc. in multiple languages you will
 * have to enable the <i>multi-lingual</i> option found next to the language selector on
 * the admin gallery configuration page. Then you will have to provide appropriate
 * alternate translations for the fields you use. While there is a field for
 * strings for all zenphoto supported languages you need supply only those you choose.
 * The others language strings will default to your local language.
 *
 * Locale selection may occur in several ways:
 * <ul>
 * 	<li>A cookie stored when the user chooses his language</li>
 * 	<li>The URI language selection provided by the <i>seo_locale</i> plugin</li>
 * 	<li>The <i>subdomain locales</i> option</li>
 * </ul>
 *
 * This plugiin applies only to the theme pages--not Admin. The <em>language cookie</i>, if set, will
 * carry over to the admin pages. As will using <i>subdomains</i>.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage seo
 */
$plugin_is_filter = 10|CLASS_PLUGIN;
$plugin_description = gettext("Allows viewers of your site to select the language translation of their choice.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'dynamic_locale';

zp_register_filter('theme_head', 'dynamic_locale::dynamic_localeJS');

if (getOption('dynamic_locale_subdomain')) {
	define('LOCALE_TYPE',2);
} else if (extensionEnabled('seo_locale') && MOD_REWRITE) {
	define('LOCALE_TYPE',1);
} else {
	define('LOCALE_TYPE',0);
}

/**
 * prints a form for selecting a locale
 * The POST handling is by getUserLocale() called in functions.php
 *
 */
function printLanguageSelector($flags=NULL) {
	$languages = generateLanguageList();
	if (isset($_REQUEST['locale'])) {
		$locale = sanitize($_REQUEST['locale']);
		if (getOption('locale') != $locale) {
			?>
			<div class="errorbox">
				<h2>
					<?php printf(gettext('<em>%s</em> is not available.'),html_encode($locale)); ?>
					<?php printf(gettext('The locale %s is not supported on your server.'), html_encode($locale)); ?>
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
			$request = parse_url(getRequestURI());
			$separator = '?';
			if (isset($request['query'])) {
				$query = explode('&', $request['query']);
				$uri['query'] = '';
				foreach ($query as $key=>$str) {
					if (preg_match('/^locale\s*=/', $str)) {
						unset($query[$key]);
					}
				}
				if (empty($query)) {
					unset($request['query']);
				} else {
					$request['query'] = implode('&', $query);
					$separator = '&';
				}

			}
			$uri = $request['path'];
			if (isset($request['query'])) $uri .= '?'.$request['query'];
			foreach ($_languages as $text=>$lang) {
				?>
				<li<?php if ($lang==$currentValue) echo ' class="currentLanguage"'; ?>>
					<?php
					if ($lang!=$currentValue) {
						switch (LOCALE_TYPE) {
							case 2:
								?>
								<a href="<?php echo dynamic_locale::fullHostPath($lang).html_encode($uri); ?>" >
								<?php
								break;
							case 1:
								?>
								<a href="<?php echo str_replace(WEBPATH, seo_locale::localePath(false, $lang), html_encode($uri)); ?>" >
								<?php
								break;
							default:
								?>
								<a href="<?php echo $uri.$separator; ?>locale=<?php echo $lang; ?>" >
								<?php
								break;
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
		global $_common_locale_type;
			$localdesc = '<p>'.gettext('If checked links to the alternative languages will be in the form <code><em>language</em>.domain</code> where <code><em>language</em></code> is the language code, e.g. <code><em>fr</em></code> for French.').'</p>';
		if (!$_common_locale_type) {
			$localdesc .= '<p>'.gettext('This requires that you have created the appropriate subdomains pointing to your Zenphoto installation. That is <code>fr.mydomain.com/zenphoto/</code> must point to the same location as <code>mydomain.com/zenphoto/</code>. (Some providers will automatically redirect undefined subdomains to the main domain. If your provider does this, no subdomain creation is needed.)').'</p>';
		}
		$options = array(	gettext('Use flags') => array('key' => 'dynamic_locale_visual', 'type' => OPTION_TYPE_CHECKBOX,
										'order'=>0,
										'desc' => gettext('Checked produces an array of flags. Not checked produces a selector.')),
									gettext('Use subdomains').'*' => array('key' => 'dynamic_locale_subdomain', 'type' => OPTION_TYPE_CHECKBOX,
										'order'=>1,
										'disabled' => $_common_locale_type,
										'desc' => $localdesc)
								);
		if ($_common_locale_type) {
			$options['note'] = array('key' => 'dynamic_locale_type', 'type' => OPTION_TYPE_NOTE,
																'order' => 2,
																'desc' => '<p class="notebox">'.$_common_locale_type.'</p>');
		} else {
			$_common_locale_type = gettext('* This option may be set via the <a href="javascript:gotoName(\'dynamic-locale\');"><em>dynamic-locale</em></a> plugin options.');
			$options['note'] = array('key' => 'dynamic_locale_type',
															'type' => OPTION_TYPE_NOTE,
															'order' => 2,
															'desc' => gettext('<p class="notebox">*<strong>Note:</strong> The setting of this option is shared with other plugins.</p>'));
		}
		return $options;
	}

	static function dynamic_localeJS() {
		?>
		<link type="text/css" rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER; ?>/dynamic-locale/locale.css" />
		<?php
	}

	static function fullHostPath($lang) {
		global $_locale_Subdomains;
		$host = $_SERVER['HTTP_HOST'];
		$matches = explode('.',$host);
		if (validateLocale($matches[0], 'Dynamic Locale')) {
			array_shift($matches);
			$host = implode('.',$matches);
		}
		if ($l = $_locale_Subdomains[$lang]) {
			$host = $l.'.'.$host;
		}
		if (SERVER_PROTOCOL == 'https') {
			$host = 'https://'.$host;
		} else {
			$host = 'http://'.$host;
		}
		return $host;
	}

}

?>
