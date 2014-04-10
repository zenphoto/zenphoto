<?php
// force UTF-8 Ø
require_once (SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/image_album_statistics.php');
zp_register_filter('themeSwitcher_head', 'switcher_head');
zp_register_filter('themeSwitcher_Controllink', 'switcher_controllink');
zp_register_filter('load_theme_script', 'fourOhFour');

$cwd = getcwd();
chdir(dirname(__FILE__));
$persona = safe_glob('*', GLOB_ONLYDIR);
chdir($cwd);
$persona = array_diff($persona, array('images', 'contact_form'));
$personalities = array();
foreach ($persona as $personality) {
	$personalities[ucfirst(str_replace('_', ' ', $personality))] = $personality;
}

if (!OFFSET_PATH) {
	if (extensionEnabled('themeSwitcher')) {
		$personality = getOption('themeSwitcher_garland_personality');
		if (isset($_GET['themePersonality'])) {
			$new = $_GET['themePersonality'];
			if (in_array($new, $personalities)) {
				setOption('themeSwitcher_garland_personality', $new);
				$personality = $new;
			}
		}
		if ($personality) {
			setOption('garland_personality', $personality, false);
		} else {
			$personality = strtolower(getOption('garland_personality'));
		}
	} else {
		$personality = strtolower(getOption('garland_personality'));
	}
	if (!in_array($personality, $personalities)) {
		$persona = $personalities;
		$personality = array_shift($persona);
	}

	require_once(SERVERPATH . '/' . THEMEFOLDER . '/garland/' . $personality . '/functions.php');
	$_oneImagePage = $handler->onePage();
	$_zp_page_check = 'my_checkPageValidity';
}

function switcher_head($ignore) {
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		function switchPersonality() {
			personality = $('#themePersonality').val();
			window.location = '?themePersonality=' + personality;
		}
		// ]]> -->
	</script>
	<?php
	return $ignore;
}

function switcher_controllink($html) {
	global $personalities, $_zp_gallery_page;
	$personality = getOption('themeSwitcher_garland_personality');
	if (!$personality) {
		$personality = getOption('garland_personality');
	}
	?>
	<span id="themeSwitcher_garland">
		<span title="<?php echo gettext("Garland image display handling."); ?>">
			<?php echo gettext('Personality'); ?>
			<select name="themePersonality" id="themePersonality" onchange="switchPersonality();">
				<?php generateListFromArray(array($personality), $personalities, false, true); ?>
			</select>
		</span>
	</span>
	<?php
	return $html;
}

function gMapOptionsImage($map) {

}

function gMapOptionsAlbum($map) {
	global $points;
	foreach ($points as $coord) {
		addGeoCoord($map, $coord);
	}
}

function footer() {
	global $_zp_gallery_page, $_zp_current_category, $_zp_gallery;
	?>
	<div id="footer">
		<?php
		if (function_exists('printFavoritesURL') && $_zp_gallery_page != 'password.php' && $_zp_gallery_page != 'favorites.php') {
			printFavoritesURL(NULL, '', ' | ', '<br />');
		}
		if (class_exists('RSS')) {
			$prev = ' | ';
			switch ($_zp_gallery_page) {
				default:
					printRSSLink('Gallery', '', 'RSS', '');
					break;
				case 'album.php':
					printRSSLink('Album', '', 'RSS', '');
					break;
				case 'news.php':
					if (is_NewsCategory()) {
						printRSSLink('Category', '', 'RSS', '', true, null, '', NULL, $_zp_current_category->getTitlelink());
					} else {
						printRSSLink('News', '', 'RSS', '');
					}
					break;
				case 'password.php':
					$prev = '';
					break;
			}
		} else {
			$prev = '';
		}
		if ($_zp_gallery_page != 'password.php' && $_zp_gallery_page != 'archive.php') {
			printCustomPageURL(gettext('Archive View'), 'archive', '', $prev, '');
			$prev = ' | ';
		}
		?>
		<?php
		if ($_zp_gallery_page != 'contact.php' && extensionEnabled('contact_form') && ($_zp_gallery_page != 'password.php' || $_zp_gallery->isUnprotectedPage('contact'))) {
			printCustomPageURL(gettext('Contact us'), 'contact', '', $prev, '');
			$prev = ' | ';
		}
		?>
		<?php
		if ($_zp_gallery_page != 'register.php' && function_exists('printRegisterURL') && !zp_loggedin() && ($_zp_gallery_page != 'password.php' || $_zp_gallery->isUnprotectedPage('register'))) {
			printRegisterURL(gettext('Register for this site'), $prev, '');
			$prev = ' | ';
		}
		?>
		<?php @call_user_func('printUserLogin_out', $prev); ?>
		<br />
		<?php @call_user_func('mobileTheme::controlLink'); ?>
		<br />
		<?php @call_user_func('printLanguageSelector'); ?>
		<?php printZenphotoLink(); ?>
	</div>
	<?php
}

function commonNewsLoop($paged) {
	$newstypes = array('album' => gettext('album'), 'image' => gettext('image'), 'video' => gettext('video'), 'news' => gettext('news'));
	while (next_news()) {
		$newstypedisplay = gettext('news');
		if (stickyNews()) {
			$newstypedisplay .= ' <small><em>' . gettext('sticky') . '</em></small>';
		}
		?>
		<div class="newsarticle<?php if (stickyNews()) echo ' sticky'; ?>">
			<h3><?php printNewsURL(); ?><?php echo " <span class='newstype'>[" . $newstypedisplay . "]</span>"; ?></h3>
			<div class="newsarticlecredit">
				<span class="newsarticlecredit-left">
					<?php
					$count = @call_user_func('getCommentCount');
					$cat = getNewsCategories();
					printNewsDate();
					if ($count > 0) {
						echo ' | ';
						printf(gettext("Comments: %d"), $count);
					}
					?>
				</span>
				<?php
				if (!empty($cat) && !in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
					echo ' | ';
					printNewsCategories(", ", gettext("Categories: "), "newscategories");
				}
				?>
			</div> <!-- newsarticlecredit -->
			<br class="clearall" />
			<?php printCodeblock(1); ?>
			<?php printNewsContent(); ?>
			<?php printCodeblock(2); ?>
			<br class="clearall" />
		</div>
		<?php
	}
	if ($paged) {
		printNewsPageListWithNav(gettext('next »'), gettext('« prev'), true, 'pagelist', true);
	}
}

function exerpt($content, $length) {
	return shortenContent(getBare($content), $length, getOption("zenpage_textshorten_indicator"));
}

function my_checkPageValidity($request, $gallery_page, $page) {
	switch ($gallery_page) {
		case 'gallery.php':
			$gallery_page = 'index.php'; //	same as an album gallery index
			break;
		case 'index.php':
			if (!extensionEnabled('zenpage')) { // only one index page if zenpage plugin is enabled or there is a custom index page
				break;
			}
		default:
			if ($page != 1) {
				return false;
			}
		case 'news.php':
		case 'album.php':
		case 'search.php':
			break;
	}
	return checkPageValidity($request, $gallery_page, $page);
}
?>