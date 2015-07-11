<?php
$_zp_page_check = 'my_checkPageValidity';

setOption('zp_plugin_colorbox', false, false);
$zenpage = getOption('zp_plugin_zenpage');
if ((function_exists('printGslideshow')) && (function_exists('printSlideShow'))) {
	$useGslideshow = true;
} else {
	$useGslideshow = false;
}

if (function_exists('printAddThis')) {
	$zpfocus_social = true;
} else {
	$zpfocus_social = false;
}

$zpfocus_tagline = get_language_string(getOption('zpfocus_tagline'));
if (is_null($zpfocus_tagline))
	$zpfocus_tagline = 'A ZenPhoto / ZenPage Powered Theme';
$zpfocus_allow_search = getOption('zpfocus_allow_search');
if (is_null($zpfocus_allow_search))
	$zpfocus_allow_search = true;
$zpfocus_show_archive = getOption('zpfocus_show_archive');
if (is_null($zpfocus_show_archive))
	$zpfocus_show_archive = true;
$zpfocus_use_colorbox = getOption('zpfocus_use_colorbox');
if (is_null($zpfocus_use_colorbox))
	$zpfocus_use_colorbox = true;
$zpfocus_use_colorbox_slideshow = getOption('zpfocus_use_colorbox_slideshow');
if (is_null($zpfocus_use_colorbox_slideshow))
	$zpfocus_use_colorbox_slideshow = true;
$zpfocus_homepage = getOption('zpfocus_homepage');
if (is_null($zpfocus_homepage))
	$zpfocus_homepage = 'none';
$zpfocus_spotlight = getOption('zpfocus_spotlight');
if (is_null($zpfocus_spotlight))
	$zpfocus_spotlight = 'manual';
$zpfocus_spotlight_text = get_language_string(getOption('zpfocus_spotlight_text'));
if (is_null($zpfocus_spotlight_text))
	$zpfocus_spotlight_text = '<p>This is the <span class="spotlight-span">spotlight</span> area that can be set in the theme options.  You can either enter the text manually in the options or set it to display the latest news if ZenPage is being used. If you want nothing to appear here, set the spotlight to none.</p>';
$zpfocus_show_credit = getOption('zpfocus_show_credit');
if (is_null($zpfocus_show_credit))
	$zpfocus_show_credit = false;
$zpfocus_menutype = getOption('zpfocus_menutype');
if (is_null($zpfocus_menutype))
	$zpfocus_menutype = 'dropdown';
$zpfocus_logotype = getOption('zpfocus_logotype');
if (is_null($zpfocus_logotype))
	$zpfocus_logotype = true;
$zpfocus_logofile = getOption('zpfocus_logofile');
if (is_null($zpfocus_logofile))
	$zpfocus_logofile = 'logo.jpg';
$zpfocus_showrandom = getOption('zpfocus_showrandom');
if (is_null($zpfocus_showrandom))
	$zpfocus_showrandom = 'rotator';
$zpfocus_cbtarget = getOption('zpfocus_cbtarget');
if (is_null($zpfocus_cbtarget))
	$zpfocus_cbtarget = true;
$zpfocus_cbstyle = getOption('zpfocus_cbstyle');
if (is_null($zpfocus_cbstyle))
	$zpfocus_cbstyle = 'example3';
$zpfocus_cbtransition = getOption('zpfocus_cbtransition');
if (is_null($zpfocus_cbtransition))
	$zpfocus_cbtransition = 'fade';
$zpfocus_cbssspeed = getOption('zpfocus_cbssspeed');
if (is_null($zpfocus_cbssspeed))
	$zpfocus_cbssspeed = '2500';
$zpfocus_final_link = getOption('zpfocus_final_link');
if (is_null($zpfocus_final_link))
	$zpfocus_final_link = 'nolink';
$zpfocus_rotatorcount = getOption('zpfocus_rotatorcount');
if (is_null($zpfocus_rotatorcount))
	$zpfocus_rotatorcount = '5';
$zpfocus_rotatoreffect = getOption('zpfocus_rotatoreffect');
if (is_null($zpfocus_rotatoreffect))
	$zpfocus_rotatoreffect = 'fade';
$zpfocus_rotatorspeed = getOption('zpfocus_rotatorspeed');
if (is_null($zpfocus_rotatorspeed))
	$zpfocus_rotatorspeed = '3000';
$zpfocus_news = getOption('zpfocus_news');
if (is_null($zpfocus_news))
	$zpfocus_news = true;

// Sets expanded titles (breadcrumbs) for Title meta
function getTitleBreadcrumb($before = ' ( ', $between = ' | ', $after = ' ) ') {
	global $_zp_gallery, $_zp_current_search, $_zp_current_album, $_zp_last_album;
	$titlebreadcrumb = '';
	if (in_context(ZP_SEARCH_LINKED)) {
		$dynamic_album = $_zp_current_search->getDynamicAlbum();
		if (empty($dynamic_album)) {
			$titlebreadcrumb .= $before . gettext("Search Result") . $after;
			if (is_null($_zp_current_album)) {
				return;
			} else {
				$parents = getParentAlbums();
			}
		} else {
			$album = newAlbum($dynamic_album);
			$parents = getParentAlbums($album);
			if (in_context(ZP_ALBUM_LINKED)) {
				array_push($parents, $album);
			}
		}
	} else {
		$parents = getParentAlbums();
	}
	$n = count($parents);
	if ($n > 0) {
		$titlebreadcrumb .= $before;
		$i = 0;
		foreach ($parents as $parent) {
			if ($i > 0)
				$titlebreadcrumb .= $between;
			$titlebreadcrumb .= $parent->getTitle();
			$i++;
		}
		$titlebreadcrumb .= $after;
	}
	return $titlebreadcrumb;
}

/* Prints jQuery JS to enable the toggling of search results of Zenpage  items */

function printZDSearchToggleJS() {
	?>
	<script type="text/javascript">
		function toggleExtraElements(category, show) {
			if (show) {
				jQuery('.' + category + '_showless').show();
				jQuery('.' + category + '_showmore').hide();
				jQuery('.' + category + '_extrashow').show();
			} else {
				jQuery('.' + category + '_showless').hide();
				jQuery('.' + category + '_showmore').show();
				jQuery('.' + category + '_extrashow').hide();
			}
		}
	</script>
	<?php
}

/* Prints the "Show more results link" for search results for Zenpage items */

function printZDSearchShowMoreLink($option, $number_to_show) {
	$option = strtolower($option);
	$number_to_show = sanitize_numeric($number_to_show);
	switch ($option) {
		case "news":
			$num = getNumNews();
			break;
		case "pages":
			$num = getNumPages();
			break;
	}
	if ($num > $number_to_show) {
		?>
		<a class="<?php echo $option; ?>_showmore"href="javascript:toggleExtraElements('<?php echo $option; ?>',true);"><?php echo gettext('Show more results'); ?></a>
		<a class="<?php echo $option; ?>_showless" style="display: none;"	href="javascript:toggleExtraElements('<?php echo $option; ?>',false);"><?php echo gettext('Show fewer results'); ?></a>
		<?php
	}
}

/* Adds the css class necessary for toggling of Zenpage items search results */

function printZDToggleClass($option, $c, $number_to_show) {
	$option = strtolower($option);
	$c = sanitize_numeric($c);
	$number_to_show = sanitize_numeric($number_to_show);
	if ($c > $number_to_show) {
		echo ' class="' . $option . '_extrashow" style="display:none;"';
	}
}

function printLatestNewsCustom($number = 5, $category = '', $showdate = true, $showcontent = true, $contentlength = 70, $showcat = true) {
	global $_zp_gallery, $_zp_current_article;
	$latest = getLatestNews($number, $category);
	echo "\n<div id=\"latestnews-spotlight\">\n";
	$count = "";
	foreach ($latest as $item) {
		$count++;
		$category = "";
		$categories = "";
		$obj = newArticle($item['titlelink']);
		$title = htmlspecialchars($obj->getTitle());
		$link = getNewsURL($item['titlelink']);
		$count2 = 0;
		$category = $obj->getCategories();
		foreach ($category as $cat) {
			$catobj = new Category($cat['titlelink']);
			$count2++;
			if ($count2 != 1) {
				$categories = $categories . "; ";
			}
			$categories = $categories . $catobj->getTitle();
		}
		$content = strip_tags($obj->getContent());
		$date = zpFormattedDate(getOption('date_format'), strtotime($item['date']));
		$type = 'news';
		echo "<div>";
		echo "<h3><a href=\"" . $link . "\" title=\"" . strip_tags(htmlspecialchars($title, ENT_QUOTES)) . "\">" . htmlspecialchars($title) . "</a></h3>\n";
		;
		echo "<div class=\"newsarticlecredit\">\n";
		echo "<span class=\"latestnews-date\">" . $date . "</span>\n";
		echo "<span class=\"latestnews-cats\">| Posted in " . $categories . "</span>\n";
		echo "</div>\n";
		echo "<p class=\"latestnews-desc\">" . html_encode(getContentShorten($content, $contentlength, '(...)', null, null)) . "</p>\n";
		echo "</div>\n";
		if ($count == $number) {
			break;
		}
	}
	echo "</div>\n";
}

function my_checkPageValidity($request, $gallery_page, $page) {
	switch ($gallery_page) {
		case 'gallery.php':
			$gallery_page = 'index.php'; //	same as an album gallery index
			break;
		case 'index.php':
			if (extensionEnabled('zenpage')) {
				if (checkForPage(getOption("zpfocus_homepage"))) {
					return $page == 1; // only one page if enabled.
				}
			}
			break;
		case 'news.php':
		case 'album.php':
		case 'favorites.php':
		case 'search.php':
			break;
		default:
			if ($page != 1) {
				return false;
			}
	}
	return checkPageValidity($request, $gallery_page, $page);
}
?>
