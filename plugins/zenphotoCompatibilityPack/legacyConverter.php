<?php
/*
 * utility to convert legacy zenphoto themes/plugins to ZenPhoto20 syntax.
 *
 * @author Stephen Billard
 * @Copyright 2015 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage development
 * @category package
 */
// force UTF-8 Ø


define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . "/zp-core/admin-globals.php");
admin_securityChecks(THEMES_RIGHTS, currentRelativeURL());

$legacyReplacements = array(
				'new ZenpagePage'										 => 'newPage',
				'new ZenpageNews'										 => 'newArticle',
				'new ZenpageCategory'								 => 'newCategory',
				'\$_zp_zenpage'											 => '$_zp_CMS',
				'ZP_NEWS_ENABLED'										 => 'TRUE/*TODO:replaced ZP_NEWS_ENABLED */',
				'ZP_PAGES_ENABLED'									 => 'TRUE/*TODO:replaced ZP_PAGES_ENABLED */',
				'getAllTagsCount\(.*\);'						 => 'getAllTagsUnique(NULL, 1, true);',
				'printHeadTitle\(.*\);'							 => '/*TODO:replaced printHeadTitle(); */',
				'getSiteHomeURL\(.*\);'							 => '/*TODO:replaced getSiteHomeURL(); */',
				'printSiteHomeURL\(.*\);'						 => '/*TODO:replaced printSiteHomeURL(); */',
				'class_exists\([\'"]Zenpage[\'"]\)'	 => 'class_exists("CMS")',
				'\$_zp_current_zenpage_article'			 => '$_zp_current_article',
				'\$_zp_current_zenpage_page'				 => '$_zp_current_page'
);

/**
 *
 * enumerates the files in folder(s)
 * @param $folder
 */
function getResidentFiles($folder) {
	global $_zp_resident_files;
	$localfiles = array();
	$localfolders = array();
	if (file_exists($folder)) {
		$dirs = scandir($folder);
		foreach ($dirs as $file) {
			if ($file{0} != '.') {
				$file = str_replace('\\', '/', $file);
				$key = $folder . '/' . $file;
				if (is_dir($folder . '/' . $file)) {
					$localfolders = array_merge($localfolders, getResidentFiles($folder . '/' . $file));
				} else {
					if (getSuffix($key) == 'php') {
						$localfiles[] = $key;
					}
				}
			}
		}
	}
	return array_merge($localfiles, $localfolders);
}

if (isset($_GET['action'])) {
	$files = array();
	if (isset($_POST['themes'])) {
		foreach ($_POST['themes'] as $theme) {
			$themeFiles = getResidentFiles(SERVERPATH . '/' . THEMEFOLDER . '/' . $theme);
			$files = array_merge($files, $themeFiles);
		}
	}
	if (isset($_POST['plugins'])) {
		foreach ($_POST['plugins'] as $plugin) {
			$pluginFiles = getResidentFiles(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/' . $plugin);
			$pluginFiles[] = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/' . $plugin . '.php';
			$files = array_merge($files, $pluginFiles);
		}
	}
	$counter = 0;
	foreach ($files as $file) {
		$counter++;
		$source = $body = file_get_contents($file);
		foreach ($legacyReplacements as $match => $replace) {
			$body = preg_replace('~' . $match . '~im', $replace, $body);
		}
		if ($source != $body) {
			file_put_contents($file, $body);
		}
	}
}

printAdminHeader('development', gettext('legacyConverter'));
echo "\n</head>";
echo "\n<body>";
printLogoAndLinks();
echo "\n" . '<div id="main">';
printTabs();
echo "\n" . '<div id="content">';
printSubtabs();
?>
<div class="tabbox">
	<h1><?php echo gettext('Convert legacy Zenphoto themes and plugins'); ?></h1>

	<?php
	if (isset($counter)) {
		?>
		<div class="messagebox fade-message">
			<h2><?php printf(ngettext('%s file updated', '%s files updated', $counter), $counter); ?></h2>
		</div>
		<?php
	}
	$themes = $plugins = array();
	foreach ($_zp_gallery->getThemes() as $theme => $data) {
		if (!protectedTheme($theme, true)) {
			$themes[] = $theme;
		}
	}
	$paths = getPluginFiles('*.php');
	foreach ($paths as $plugin => $path) {
		if (strpos($path, USER_PLUGIN_FOLDER) !== false) {
			$p = file_get_contents($path);
			$i = strpos($p, '* @category');
			$foreign = true;
			if (($key = $i) !== false) {
				$key = strtolower(trim(substr($p, $i + 11, strpos($p, "\n", $i) - $i - 11)));
				if ($key == 'package' || $key == 'zenphoto20tools') {
					$foreign = false;
				}
			}
			if ($foreign) {
				$plugins[] = stripSuffix(basename($path));
			}
		}
	}
	?>
	<form class="dirtylistening" id="form_convert" action="?action=process" method="post" >
		<ul class="page-list">
			<?php
			XSRFToken('saveoptions');
			if (!empty($themes)) {
				?>
				<li>
					<ul>
						<?php
						echo gettext('Themes');
						foreach ($themes as $theme) {
							?>
							<li><label><input type="checkbox" name="themes[]" value="<?php echo html_encode($theme); ?>" ><?php echo html_encode($theme); ?></label></li>
							<?php
						}
						?>
					</ul>
				</li>
				<br />
				<?php
			}
			if (!empty($plugins)) {
				?>
				<li>
					<ul>
						<?php
						echo gettext('Plugins');
						foreach ($plugins as $plugin) {
							?>
							<li><label><input type="checkbox" name="plugins[]" value="<?php echo html_encode($plugin); ?>" ><?php echo html_encode($plugin); ?></label></li>
							<?php
						}
						?>
					</ul>
				</li>
				<?php
			}
			?>
		</ul>

		<p class="buttons">
			<button type="submit" >
				<img	src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="" />
				<strong><?php echo gettext("Apply"); ?></strong>
			</button>
			<button type="reset">
				<img	src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png" alt="" />
				<strong><?php echo gettext("Reset"); ?></strong>
			</button>

		</p>
	</form>
</div>
<?php
echo "\n" . '</div>'; //content
echo "\n" . '</div>'; //main

printAdminFooter();
echo "\n</body>";
echo "\n</html>";
?>