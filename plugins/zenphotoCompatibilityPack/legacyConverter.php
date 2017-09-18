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
		'new ZenpagePage' => 'newPage',
		'new ZenpageNews' => 'newArticle',
		'new ZenpageCategory' => 'newCategory',
		'\$_zp_zenpage' => '$_zp_CMS',
		'ZP_NEWS_ENABLED' => 'TRUE/* TODO:replaced ZP_NEWS_ENABLED */',
		'ZP_PAGES_ENABLED' => 'TRUE/* TODO:replaced ZP_PAGES_ENABLED */',
		'getAllTagsCount\(.*?\)' => 'getAllTagsUnique(NULL, 1, true)',
		'printHeadTitle\(.*?\);?' => '/* TODO:replaced printHeadTitle() */',
		'getSiteHomeURL\(.*?\)' => 'getGalleryIndexURL() /* TODO:replaced getSiteHomeURL() */',
		'printSiteHomeURL\(.*?\);?' => '/* TODO:replaced printSiteHomeURL() */',
		'getNextPrevNews\([\'"](.*)[\'"]\)' => 'get$1News() /* TODO:replaced getNextPrevNews(\'$1\') */',
		'zenpagePublish\((.*)\,(.*)\)' => '$1->setShow($2) /* TODO:replaced zenpagePublish() */',
		'getImageCustomData\(\)' => '($_zp_current_image)?$_zp_current_image->get("custom_data"):NULL /* TODO: Use customFieldExtender to define unique fields */',
		'printImageCategoryCustomData\(\)' => 'echo ($_zp_current_image)?$_zp_current_image->get("custom_data"):NULL /* TODO: Use customFieldExtender to define unique fields */',
		'getAlbumCustomData\(\)' => '($_zp_current_album)?$_zp_current_album->get("custom_data"):NULL /* TODO: Use customFieldExtender to define unique fields */',
		'printAlbumCategoryCustomData\(\)' => 'echo ($_zp_current_album)?$_zp_current_album->get("custom_data"):NULL /* TODO: Use customFieldExtender to define unique fields */',
		'getPageCustomData\(\)' => '($_zp_current_page)?$_zp_current_page->get("custom_data"):NULL /* TODO: Use customFieldExtender to define unique fields */',
		'printPageCategoryCustomData\(\)' => 'echo ($_zp_current_page)?$_zp_current_page->get("custom_data"):NULL /* TODO: Use customFieldExtender to define unique fields */',
		'getNewsCustomData\(\)' => '($_zp_current_article)?$_zp_current_article->get("custom_data"):NULL /* TODO: Use customFieldExtender to define unique fields */',
		'printNewsCustomData\(\)' => 'echo ($_zp_current_article)?$_zp_current_article->get("custom_data"):NULL /* TODO: Use customFieldExtender to define unique fields */',
		'getNewsCategoryCustomData\(\)' => '($_zp_current_category)?$_zp_current_category->get("custom_data"):NULL /* TODO: Use customFieldExtender to define unique fields */',
		'printNewsCategoryCustomData\(\)' => 'echo ($_zp_current_category)?$_zp_current_category->get("custom_data"):NULL /* TODO: Use customFieldExtender to define unique fields */',
		'class_exists\([\'"]Zenpage[\'"]\)' => 'class_exists("CMS")',
		'\$_zp_current_zenpage_news' => '$_zp_current_article',
		'\$_zp_current_zenpage_page' => '$_zp_current_page',
		'->getFullImage\(' => '->getFullImageURL(',
		'tinymce4_' => 'tinymce_',
		'(setOptionDefault\([\'"]colorbox_.*[\'"],.*\);?)' => '$1 /* TODO:replace with a call to colorbox::registerScripts(); */',
		'getSubtabs' => 'getCurrentTab	/* TODO:replaced printSubtabs. Remove this if you do not use the return value */',
		'printSubtabs' => 'getCurrentTab	/* TODO:replaced printSubtabs. Remove this if you do not use the return value */'
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

function checkIfProcessed($kind, $name) {
	$file = 'none';
	switch ($kind) {
		case 'plugin':
			$file = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/' . $name . '.php';
			break;
		case'theme':
			$file = SERVERPATH . '/' . THEMEFOLDER . '/' . $name . '/theme_description.php';
			break;
	}
	if (file_exists($file)) {
		$body = file_get_contents($file);
		return (strpos($body, '/*LegacyConverter was here*/') !== false);
	}
	return false;
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
		$source = $body = file_get_contents($file);
		if (strpos($body, '/*LegacyConverter was here*/') === false) {
			$body = preg_replace('~\<\?php~i', "<?php\n/*LegacyConverter was here*/", $body, 1);
		}

		foreach ($legacyReplacements as $match => $replace) {
			$body = preg_replace('~' . $match . '~im', $replace, $body);
		}
		$body = preg_replace('~/\* TODO:replaced .*/\* TODO:replaced(.*)\*/ \*/~', '/* TODO:replaced$1*/', $body); //in case we came here twice

		if ($source != $body) {
			file_put_contents($file, $body);
			$counter++;
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
zp_apply_filter('admin_note', 'development', '');
echo "\n" . '<div id="container">';
?>
<h1><?php echo gettext('Convert legacy Zenphoto themes and plugins'); ?></h1>

<div class="tabbox">
	<?php
	if (isset($counter)) {
		?>
		<div class="messagebox fade-message">
			<h2><?php printf(ngettext('%s file updated', '%s files updated', $counter), $counter); ?></h2>
		</div>
		<?php
	}
	$themesP = $themes = $plugins = $pluginsP = array();
	foreach ($_zp_gallery->getThemes() as $theme => $data) {
		if (!protectedTheme($theme, true)) {
			$themes[] = $theme;
			if (checkIfProcessed('theme', $theme)) {
				$themesP[] = $theme;
			}
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
				$name = stripSuffix(basename($path));
				$plugins[] = $name;
				if (checkIfProcessed('plugin', $name)) {
					$pluginsP[] = $name;
				}
			}
		}
	}
	?>
	<form class="dirtylistening" id="form_convert" action="?page=development&tab=legacyConverter&action=process" method="post" >
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
							<li>
								<label>
									<input type="checkbox" name="themes[]" value="<?php echo html_encode($theme); ?>" >
									<?php
									echo html_encode($theme);
									if (in_array($theme, $themesP)) {
										?>
										<span style="color: orangered">
											<?php echo gettext(' (already processed)'); ?>
										</span>
										<?php
									}
									?>
								</label>
							</li>
							<?php
						}
						?>
					</ul>
				</li>
				<br />
				<?php
			}
			if (!empty($plugins) || !empty($pluginsP)) {
				?>
				<li>
					<ul>
						<?php
						echo gettext('Plugins');
						foreach ($plugins as $plugin) {
							?>
							<li>
								<label>
									<input type="checkbox" name="plugins[]" value="<?php echo html_encode($plugin); ?>" >
									<?php
									echo html_encode($plugin);
									if (in_array($plugin, $pluginsP)) {
										?>
										<span style="color: orangered">
											<?php echo gettext(' (already processed)'); ?>
										</span>
										<?php
									}
									?>
								</label>
							</li>
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
				<?php echo CHECKMARK_GREEN; ?>
				<strong><?php echo gettext("Apply"); ?></strong>
			</button>
			<button type="reset">
				<?php echo CROSS_MARK_RED; ?>
				<strong><?php echo gettext("Reset"); ?></strong>
			</button>

		</p>

		<br clear="all">

	</form>
</div>
<?php
echo "\n" . '</div>'; //content
echo "\n" . '</div>'; //container
echo "\n" . '</div>'; //main

printAdminFooter();
echo "\n</body>";
echo "\n</html>";
?>