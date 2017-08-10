<?php
zp_register_filter('themeSwitcher_head', 'switcher_head');
zp_register_filter('iconColor', 'iconColor');
zp_register_filter('themeSwitcher_Controllink', 'switcher_controllink');
zp_register_filter('theme_head', 'css_head', 500);
enableExtension('zenpage', 0, false); //	we do not support it

$curdir = getcwd();
chdir(SERVERPATH . "/themes/" . basename(dirname(__FILE__)) . "/styles");
$filelist = safe_glob('*.css');
$themecolors = array();
foreach ($filelist as $file) {
	$themecolors[] = stripSuffix(filesystemToInternal($file));
}
chdir($curdir);

function css_head($ignore) {
	global $themecolors, $zenCSS, $themeColor, $_zp_themeroot;
	if (!$themeColor) {
		$themeColor = getOption('Theme_colors');
	}

	if ($editorConfig = getOption('tinymce_comments')) {
		if (strpos($themeColor, 'dark') !== false) {
			$editorConfig = str_replace('_dark', '', stripSuffix($editorConfig)) . '_dark.php';
			setOption('tinymce_comments', $editorConfig, false);
		}
	}

	$zenCSS = $_zp_themeroot . '/styles/' . $themeColor . '.css';
	$unzenCSS = str_replace(WEBPATH, '', $zenCSS);
	if (!file_exists(SERVERPATH . internalToFilesystem($unzenCSS))) {
		$zenCSS = $_zp_themeroot . "/styles/light.css";
	}
	return $ignore;
}

function iconColor($icon) {
	global $themeColor;
	if (!$themeColor) {
		$themeColor = getOption('Theme_colors');
	}
	if (strpos($themeColor, 'dark') !== false) {
		$icon = stripSuffix($icon) . '-white.png';
	}
	return($icon);
}

function printSoftwareLink() {
	global $themeColor;
	switch ($themeColor) {
		case 'dark':
			$logo = 'blue';
			break;
		case'light':
			$logo = 'light';
			break;
		default:
			$logo = 'sterile';
			break;
	}
	printZenphotoLink($logo);
}

function switcher_head($ignore) {
	global $personalities, $themecolors, $themeColor;
	$themeColor = zp_getCookie('themeSwitcher_color');
	if (isset($_GET['themeColor'])) {
		$new = $_GET['themeColor'];
		if (in_array($new, $themecolors)) {
			zp_setCookie('themeSwitcher_color', $new, false);
			$themeColor = $new;
		}
	}
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		function switchColors() {
			personality = $('#themeColor').val();
			window.location = '?themeColor=' + personality;
		}
		// ]]> -->
	</script>
	<?php
	return $ignore;
}

function switcher_controllink($ignore) {
	global $themecolors;
	$color = zp_getCookie('themeSwitcher_color');
	if (!$color) {
		$color = getOption('Theme_colors');
	}
	?>
	<span title="<?php echo gettext("Default theme color scheme."); ?>">
		<?php echo gettext('Theme Color'); ?>
		<select name="themeColor" id="themeColor" onchange="switchColors();">
			<?php generateListFromArray(array($color), $themecolors, false, false); ?>
		</select>
	</span>
	<?php
	return $ignore;
}

$_zp_page_check = 'checkPageValidity'; //	opt-in, standard behavior
?>