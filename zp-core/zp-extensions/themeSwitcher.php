<?php
/**
 *
 * When this plugin is enabled, a selector will be floated at the upper left corner
 * of the browser window. This enables a visitor to select which theme he wants to use.
 *
 * Theme selection is stored in a cookie. The default duration of this cookie is 120 minutes,
 * changeable by an option.
 *
 * No theme participation is needed for this plugin. But to accomplish this independence the
 * plugin will load a small css block in the theme head. The actual styling is an option to the plugin.
 * A theme may replace this css via the <var>themeSwitcher_css</var> filter.
 *
 * Themes and plugins may use the <var>themeSwitcher_head</var> and <var>themeSwitcher_controllink</var> filters to add (or remove)
 * switcher controls. The <i><var>active()</var></i> method may be called to see if <i>themeSwitcher</i> will display
 * the control links.
 *
 * @package plugins
 * @subpackage development
 */
$plugin_is_filter = 98 | CLASS_PLUGIN;
$plugin_description = gettext('Allow a visitor to select the theme of the gallery.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'themeSwitcher';

class themeSwitcher {

	function __construct() {
		global $_zp_gallery;
		$themes = $_zp_gallery->getThemes();
		foreach ($themes as $key => $theme) {
			setOptionDefault('themeSwitcher_theme_' . $key, 1);
			$themelist[$key] = getOption('themeSwitcher_theme_' . $key);
		}
		setOptionDefault('themeSwitcher_timeout', 60 * 2);
		setOptionDefault('themeSwitcher_css', ".themeSwitcherControlLink {\n" .
						" position: fixed;\n" .
						" z-index: 10000;\n" .
						" left: 0px;\n" .
						" top: 0px;\n" .
						" border-bottom: 1px solid #444;\n" .
						" border-left: 1px solid #444;\n" .
						" color: black;\n" .
						" padding: 2px;\n" .
						" background-color: #f5f5f5;\n" .
						"}\n"
		);
		setOptionDefault('themeSwitcher_adminOnly', 1);
	}

	function getOptionsSupported() {
		global $_zp_gallery;
		$themes = $_zp_gallery->getThemes();
		$list = array();
		foreach ($themes as $key => $theme) {
			$list[$theme['name']] = 'themeSwitcher_theme_' . $key;
		}
		$options = array(gettext('Cookie duration') => array('key'	 => 'themeSwitcher_timeout', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext('The time in minutes that the theme switcher cookie lasts.')),
						gettext('Selector CSS')		 => array('key'					 => 'themeSwitcher_css', 'type'				 => OPTION_TYPE_TEXTAREA,
										'multilingual' => false,
										'desc'				 => gettext('Change this box if you wish to style the theme switcher selector for your themes.')),
						gettext('Private')				 => array('key'	 => 'themeSwitcher_adminOnly', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext('Only users with <em>Themes</em> rights will see the selector if this is checked.')),
						gettext('Theme list')			 => array('key'				 => 'themeSwitcher_list', 'type'			 => OPTION_TYPE_CHECKBOX_UL,
										'checkboxes' => $list,
										'desc'			 => gettext('These are the themes that may be selected among.'))
		);
		return $options;
	}

	function handleOption($option, $currentValue) {

	}

	/**
	 *
	 * Filter to "setupTheme" that will override the gallery theme with user selected theme
	 * @param string $theme
	 */
	static function theme($theme) {
		global $_zp_gallery;
		$new = zp_getCookie('themeSwitcher_theme');
		if ($new) {
			if (array_key_exists($new, $_zp_gallery->getThemes())) {
				$theme = $new;
			}
		}
		return $theme;
	}

	static function head($css) {
		global $_themeSwitcherThemelist;
		if (getOption('themeSwitcher_css')) {
			?>
			<style type="text/css">
			<?php echo zp_apply_filter('themeSwitcher_css', getOption('themeSwitcher_css')); ?>
			</style>
			<?php
		}
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			function switchTheme(reloc) {
				window.location = reloc.replace(/%t/, $('#themeSwitcher').val());
			}
			// ]]> -->
		</script>
		<?php
		$_themeSwitcherThemelist = zp_apply_filter('themeSwitcher_head', $_themeSwitcherThemelist);
		return $css;
	}

	/**
	 *
	 * places a selector so a user may change thems
	 * @param string $text link text
	 */
	static function controlLink($textIn = NULL) {
		global $_zp_gallery, $_themeSwitcherThemelist, $_zp_gallery_page;
		if (self::active()) {
			$themes = array();
			foreach ($_zp_gallery->getThemes() as $theme => $details) {
				if ($_themeSwitcherThemelist[$theme]) {
					if (getPlugin($_zp_gallery_page, $theme)) {
						$themes[$details['name']] = $theme;
					}
				}
			}
			$text = $textIn;
			if (empty($text)) {
				$text = gettext('Theme');
			}
			$reloc = pathurlencode(trim(preg_replace('~themeSwitcher=.*?&~', '', getRequestURI() . '&'), '?&'));
			if (strpos($reloc, '?')) {
				$reloc .= '&themeSwitcher=%t';
			} else {
				$reloc .= '?themeSwitcher=%t';
			}
			$theme = $_zp_gallery->getCurrentTheme();
			?>
			<span class="themeSwitcherControlLink">
				<span title="<?php echo gettext("Themes will not show in this list if selecting them would result in a 'not found' error."); ?>">
					<?php echo $text; ?>
					<select name="themeSwitcher" id="themeSwitcher" onchange="switchTheme('<?php echo html_encode($reloc); ?>')">
						<?php generateListFromArray(array($theme), $themes, false, true); ?>
					</select>
				</span>
				<?php zp_apply_filter('themeSwitcher_Controllink', $theme); ?>
			</span>
			<?php
		}
		return $textIn;
	}

	static function active() {
		global $_showNotLoggedin_real_auth;
		if (isset($_showNotLoggedin_real_auth)) {
			$loggedin = $_showNotLoggedin_real_auth->getRights();
		} else {
			$loggedin = zp_loggedin();
		}
		return !getOption('themeSwitcher_adminOnly') || $loggedin & (ADMIN_RIGHTS | THEMES_RIGHTS);
	}

}

$_themeSwitcherThemelist = array();
foreach ($_zp_gallery->getThemes() as $__key => $__theme) {
	$_themeSwitcherThemelist[$__key] = getOption('themeSwitcher_theme_' . $__key);
}
unset($__key);
unset($__theme);
if (isset($_GET['themeSwitcher'])) {
	zp_setCookie('themeSwitcher_theme', sanitize($_GET['themeSwitcher']), getOption('themeSwitcher_timeout') * 60);
}

if (zp_getCookie('themeSwitcher_theme')) {
	zp_register_filter('setupTheme', 'themeSwitcher::theme');
}
zp_register_filter('theme_head', 'themeSwitcher::head');
zp_register_filter('theme_body_open', 'themeSwitcher::controlLink');
?>