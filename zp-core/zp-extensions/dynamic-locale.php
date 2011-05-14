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
$plugin_version = '1.4.1';
$option_interface = 'dynamic_locale_options';

zp_register_filter('theme_head', 'dynamic_localeJS');

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
						?>
						<a href="javascript:launchScript('',['locale=<?php echo $lang; ?>']);" >
						<?php
					}
					if (file_exists(SERVERPATH.'/'.ZENFOLDER.'/locale/'.$lang.'/flag.png')) {
						$flag = WEBPATH.'/'.ZENFOLDER.'/locale/'.$lang.'/flag.png';
					} else {
						$flag = WEBPATH.'/'.ZENFOLDER.'/locale/missing_flag.png';
					}
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

function dynamic_localeJS() {
	?>
	<link type="text/css" rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER; ?>/dynamic-locale/locale.css" />
	<?php
}

class dynamic_locale_options {

	function dynamic_locale_options() {
		setOptionDefault('dynamic_locale_visual', 0);
	}

	function getOptionsSupported() {
		return array(	gettext('Use flags') => array('key' => 'dynamic_locale_visual', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext('Checked produces an array of flags. Not checked produces a selector.'))
		);
	}

}

?>