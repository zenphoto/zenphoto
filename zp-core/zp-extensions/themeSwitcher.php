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
 *
 * Themes and plugins may use the <var>themeSwitcher_head</var> and <var>themeSwitcher_controllink</var> filters to add (or remove)
 * switcher controls. The <i><var>active()</var></i> method may be called to see if <i>themeSwitcher</i> will display
 * the control links.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage development
 */
$plugin_is_filter = 500 | CLASS_PLUGIN;
$plugin_description = gettext('Allow a visitor to select the theme of the gallery.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'themeSwitcher';

class themeSwitcher {

	function __construct() {
		global $_zp_gallery;
		if (OFFSET_PATH == 2) {
			$themes = $_zp_gallery->getThemes();
			foreach ($themes as $key => $theme) {
				setOptionDefault('themeSwitcher_theme_' . $key, 1);
				$themelist[$key] = getOption('themeSwitcher_theme_' . $key);
			}
			setOptionDefault('themeSwitcher_timeout', 60 * 2);
			setOptionDefault('themeSwitcher_adminOnly', 1);
		}
	}

	function getOptionsSupported() {
		global $_zp_gallery;
		$themes = $_zp_gallery->getThemes();
		$list = array();
		foreach ($themes as $key => $theme) {
			$list[$theme['name']] = 'themeSwitcher_theme_' . $key;
		}
		$options = array(gettext('Cookie duration') => array('key' => 'themeSwitcher_timeout', 'type' => OPTION_TYPE_NUMBER,
						'desc' => gettext('The time in minutes that the theme switcher cookie lasts.')),
				gettext('Private') => array('key' => 'themeSwitcher_adminOnly', 'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Only users with <em>Themes</em> rights will see the selector if this is checked.')),
				gettext('Theme list') => array('key' => 'themeSwitcher_list', 'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => $list,
						'desc' => gettext('These are the themes that may be selected among.'))
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
		$css = getPlugin('themeSwitcher/themeSwitcher.css', true, true);
		?>
		<link type="text/css" rel="stylesheet" href="<?php echo pathurlencode($css); ?>" />
		<script type="text/javascript">
			// <!-- <![CDATA[
			function switchTheme(reloc) {
				window.location = reloc.replace(/%t/, encodeURIComponent($('#themeSwitcher').val()));
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
				if (in_array($details['name'], $themes)) {
					$themes[$theme] = $details['name'] . ' v' . $details['version'];
				} else {
					$themes[$theme] = $details['name'];
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
			$icon = zp_apply_filter('iconColor', getPlugin('themeSwitcher/menu.png', true, true));
			?>
			<div class="themeSwitcherMenuMain themeSwitcherMenu themeSwitcherControl">
				<img src="<?php echo $icon; ?>" onclick="$('.themeSwitcherControl').toggle();" title="<?php echo gettext('Switch themes'); ?>" />
			</div>
			<div class="themeSwitcherControlLink themeSwitcherControl" style="display:none;">
				<div class="themeSwitcherMenu">
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/themeSwitcher/menu.png" onclick="$('.themeSwitcherControl').toggle();" title="<?php echo gettext('Close'); ?>" />
				</div>
				<?php echo $text; ?>
				<select name="themeSwitcher" id="themeSwitcher" onchange="switchTheme('<?php echo html_encode($reloc); ?>')" title="<?php echo gettext("Themes will be disabled in this list if selecting them would result in a “not found” error."); ?>">
					<?php
					foreach ($themes as $key => $item) {
						echo '<option value="' . html_encode($key) . '"';
						if ($key == $theme) {
							echo ' selected="selected"';
						} else if (!getPlugin($_zp_gallery_page, $key)) {
							echo ' disabled="disabled"';
						}
						echo '>' . $item . "</option>" . "\n";
					}
					?>
					<?php //generateListFromArray(array($theme), $themes, false, true); ?>
				</select>
				<?php zp_apply_filter('themeSwitcher_Controllink', $theme); ?>
			</div>
			<?php
		}
		return $textIn;
	}

	static function active() {
		global $_showNotLoggedin_real_auth;
		if (is_object($_showNotLoggedin_real_auth)) {
			$loggedin = $_showNotLoggedin_real_auth->getRights();
		} else {
			$loggedin = zp_loggedin();
		}
		return !getOption('themeSwitcher_adminOnly') || $loggedin & (ADMIN_RIGHTS | THEMES_RIGHTS);
	}

}

$_themeSwitcherThemelist = array();
foreach ($_zp_gallery->getThemes() as $__key => $__theme) {
	$set = getOption('themeSwitcher_theme_' . $__key);
	if (is_null($set)) //newly arrived theme?
		$set = 1;
	$_themeSwitcherThemelist[$__key] = $set;
}
unset($__key);
unset($__theme);
if (isset($_GET['themeSwitcher'])) {
	zp_setCookie('themeSwitcher_theme', sanitize($_GET['themeSwitcher']), getOption('themeSwitcher_timeout') * 60);
}

if (zp_getCookie('themeSwitcher_theme')) {
	zp_register_filter('setupTheme', 'themeSwitcher::theme');
}
zp_register_filter('theme_head', 'themeSwitcher::head', 999);
zp_register_filter('theme_body_open', 'themeSwitcher::controlLink');
?>