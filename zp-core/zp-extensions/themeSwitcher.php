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
 *
 * @package plugins
 */

$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext('Allow a visitor to select the theme of the gallery.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'themeSwitcher';


class themeSwitcher {

	function __construct() {
		setOptionDefault('themeSwitcher_timeout', 60*2);
		setOptionDefault('themeSwitcher_css',
						".themeSwitcherControlLink {\n".
						" position: fixed;\n".
						" z-index: 10000;\n".
						" left: 0px;\n".
						" top: 0px;\n".
						" border-bottom: 1px solid #444;\n".
						" border-left: 1px solid #444;\n".
						" color: black;\n".
						" padding: 2px;\n".
						" background-color: #f5f5f5;\n".
						"}\n"
		);
	}

	function getOptionsSupported() {
		$options = array(	gettext('Cookie duration') => array('key' => 'themeSwitcher_timeout', 'type' => OPTION_TYPE_TEXTBOX,
																				'desc' => gettext('The time in minutes that the theme switcher cookie lasts.')),
											gettext('Selector CSS') => array('key' => 'themeSwitcher_css', 'type' => OPTION_TYPE_TEXTAREA,
																				'desc' => gettext('Check this box if you wish to style the theme switcher selector in your themes.'))
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

	static function css() {
		?>
		<style type="text/css">
			<?php echo getOption('themeSwitcher_css'); ?>
		</style>
		<?php
	}

	/**
	 *
	 * places a selector so a user may change thems
	 * @param string $text link text
	 */
	static function controlLink($text=NULL) {
		global $_zp_gallery;
		$themes = array();
		foreach ($_zp_gallery->getThemes() as $theme=>$details) {
			$themes[$details['name']] = $theme;
		}
		if (empty($text)) {
			$text = gettext('Theme');
		}
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			function switchTheme() {
				theme = $('#themeSwitcher').val();
				window.location = '?themeSwitcher='+theme;
			}
			// ]]> -->
		</script>
		<span class="themeSwitcherControlLink">
			<?php echo $text; ?>
			<select name="themeSwitcher" id="themeSwitcher" onchange="switchTheme()">
				<?php generateListFromArray(array($_zp_gallery->getCurrentTheme()), $themes, false, true); ?>
			</select>
		</span>
		<?php

	}

}
if (isset($_GET['themeSwitcher'])) {
	zp_setCookie('themeSwitcher_theme', sanitize($_GET['themeSwitcher']),getOption('themeSwitcher_timeout')*60);
}

if (zp_getCookie('themeSwitcher_theme')) {
	zp_register_filter('setupTheme', 'themeSwitcher::theme');
}
if (getOption('themeSwitcher_css')) {
	zp_register_filter('theme_head', 'themeSwitcher::css');
}
zp_register_filter('theme_body_open', 'themeSwitcher::controlLink');

?>