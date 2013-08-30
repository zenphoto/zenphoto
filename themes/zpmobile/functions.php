<?php
//	Required plugins:
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/image_album_statistics.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/print_album_menu.php');

/**
 * Prints the scripts needed for the header
 */
function jqm_loadScripts() {
	global $_zp_themeroot;
	?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/jquerymobile/jquery.mobile-1.3.2.min.css" />
	<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/jquerymobile/jquery.mobile-1.3.2.min.js"></script>
	<?php
	printZDSearchToggleJS();
}

/**
 * Prints the rss links for use in the sidebar/bottom nav
 */
function jqm_printRSSlinks() {
	global $_zp_gallery_page, $_zp_themeroot;
	?>
	<h3><?php echo gettext('RSS'); ?></h3>
	<ul>
		<?php
		// these links must change to ones with rel="external" so they are actually loaded via jquerymobile!
		if (extensionEnabled('zenpage')) {
			?>
			<li class="rsslink"><a href="<?php echo html_encode(getRSSLink('News')); ?>" rel="external" data-ajax="false"><?php echo gettext('News'); ?></a></li>
			<li class="rsslink"><a href="<?php echo html_encode(getRSSLink('NewsWithImages')); ?>" rel="external" data-ajax="false"><?php echo gettext('News and Gallery'); ?></a></li>
			<?php
		}
		?>
		<li class="rsslink"><a href="<?php echo html_encode(getRSSLink('Gallery')); ?>" rel="external" data-ajax="false"><?php echo gettext('Gallery'); ?></a></li>
		<?php
		if ($_zp_gallery_page == 'album.php') {
			?>
			<li class="rsslink"><a href="<?php echo html_encode(getRSSLink('Album')); ?>" rel="external" data-ajax="false"><?php echo gettext('Album'); ?></a></li>
			<?php
		}
		?>
	</ul>
	<?php
}

/**
 * Prints the image/subalbum count for the album loop
 */
function jqm_printMainHeaderNav() {
	global $_zp_gallery_page, $_zp_zenpage, $_zp_current_album, $_zp_themeroot;
	?>
	<div data-role="header" data-position="inline" data-theme="a">
		<h1><?php printGalleryTitle(); ?></h1>
		<a href="<?php echo WEBPATH; ?>/" data-icon="home" data-iconpos="notext"><?php echo gettext('Home'); ?></a>
		<?php if (getOption('Allow_search')) { ?>
			<a href="<?php echo getCustomPageURL('search'); ?>" data-icon="search" data-iconpos="notext"><?php echo gettext('Search'); ?></a>
		<?php } ?>
		<div data-role="navbar">
			<ul>
				<li><a href="<?php echo getGalleryIndexURL(); ?>"><?php echo gettext('Gallery'); ?></a></li>
				<?php if (extensionEnabled('zenpage')) { ?>
					<li><a href="<?php echo getNewsIndexURL(); ?>"><?php echo gettext('News'); ?></a></li>
					<li><a href="<?php echo $_zp_zenpage->getPagesLinkPath(''); ?>"><?php echo gettext('Pages'); ?></a></li>
				<?php } ?>
				<li><a href="<?php echo getCustomPageURL('archive'); ?>"><?php echo gettext('Archive'); ?></a></li>
			</ul>
		</div><!-- /navbar -->
	</div><!-- /header -->
	<?php
}

/**
 * Prints the footer
 */
function jqm_printFooterNav() {
	global $_zp_gallery_page, $_zp_current_album;
	?>
	<div id="footer">
		<?php
		@call_user_func('printLanguageSelector', "langselector");
		?>
		<ul>
			<li><?php echo gettext('Powered by'); ?> <a href="http://www.zenphoto.org">Zenphoto</a> and <a href="http://jquerymobile.com">jQueryMobile</a></li>
			<li><?php echo gettext('zpMobile theme by'); ?> <a href="http://www.maltem.de">Malte Müller</a></li>
			<?php
			if (zp_loggedin()) {
				$protocol = SERVER_PROTOCOL;
				if ($protocol == 'https_admin') {
					$protocol = 'https';
				}
				?>
				<li><a rel="external" href="<?php echo html_encode($protocol . '://' . $_SERVER['HTTP_HOST'] . WEBPATH . '/' . ZENFOLDER); ?>"><?php echo gettext('Admin'); ?></a></li>
				<?php
			}
			?>
			<?php
			if (function_exists('printFavoritesLink')) {
				?>
				<li><?php printFavoritesLink(); ?></li><?php
			}
			?>
			<li><?php @call_user_func('mobileTheme::controlLink'); ?></li>
		</ul>
		<!-- /navbar -->
	</div><!-- footer -->
	<?php
}

/**
 * Prints the categories of current article as a unordered html list WITHOUT links
 *
 * @param string $separator A separator to be shown between the category names if you choose to style the list inline
 */
function jqm_printNewsCategories($separator = '', $class = '') {
	$categories = getNewsCategories();
	$catcount = count($categories);
	if ($catcount != 0) {
		if (is_NewsType("news")) {
			echo "<ul class=\"$class\">\n";
			$count = 0;
			foreach ($categories as $cat) {
				$count++;
				$catobj = new ZenpageCategory($cat['titlelink']);
				if ($count >= $catcount) {
					$separator = "";
				}
				echo "<li>" . $catobj->getTitle() . "</li>\n";
			}
			echo "</ul>\n";
		}
	}
}

/**
 * Prints the foldout (sidebar/bottom) menu links
 */
function jqm_printMenusLinks() {
	global $_zp_gallery_page;
	?>
	<div id="collapsible-lists" data-collapsed="false">
		<?php if (extensionEnabled('zenpage')) { ?>
			<div data-role="collapsible" data-content-theme="c" data-theme="b"<?php if ($_zp_gallery_page == 'news.php') echo ' data-collapsed="false"'; ?>>
				<h3><?php echo gettext('News'); ?></h3>
				<?php printAllNewsCategories(gettext("All news"), TRUE, "", "menu-active", true, "submenu", "menu-active"); ?>
			</div>
		<?php } ?>
		<?php if (function_exists('printAlbumMenu')) { ?>
			<div data-role="collapsible" data-content-theme="c" data-theme="b"<?php if ($_zp_gallery_page == 'gallery.php' || $_zp_gallery_page == 'album.php' || $_zp_gallery_page == 'image.php') echo ' data-collapsed="false"'; ?>>
				<h3><?php echo gettext('Gallery'); ?></h3>
				<?php printAlbumMenu('list', true, '', '', '', '', 'Gallery Index', false, false, false); ?>
			</div>
		<?php } ?>
		<?php if (extensionEnabled('zenpage')) { ?>
			<div data-role="collapsible" data-content-theme="c" data-theme="b"<?php if ($_zp_gallery_page == 'pages.php') echo ' data-collapsed="false"'; ?>>
				<h3><?php echo gettext('Pages'); ?></h3>
				<?php printPageMenu("list", "", "menu-active", "submenu", "menu-active", NULL, true, true, NULL); ?>
			</div>
		<?php } ?>
		<div data-role="collapsible" data-content-theme="c" data-theme="b">
			<?php jqm_printRSSlinks(); ?>
		</div>
	</div>
	<?php
}

function jqm_printBacktoTopLink() {
	return ''; // disabled for now as the jquerymobile cache somehow always link this to the previous page...
	?>
	<a href="#mainpage" data-ajax="false" rel="external" data-role="button" data-icon="arrow-u" data-iconpos="left" data-mini="true" data-inline="true"><?php echo gettext('Back to top'); ?></a>
	<?php
}

/**
 * Prints the link to an news entry with combinews support
 */
function jqm_getNewsLink() {
	global $_zp_current_zenpage_news;
	$newstype = getNewsType();
	switch ($newstype) {
		case "image":
		case "video":
			$link = $_zp_current_zenpage_news->getImageLink();
			break;
		case "album":
			$link = $_zp_current_zenpage_news->getAlbumLink();
			break;
		default:
			$link = getNewsURL(getNewsTitleLink());
			break;
	}
	return $link;
}

/**
 * Prints the thumbnail for news in Combinews mode
 */
function jqm_printCombiNewsThumb() {
	global $_zp_current_zenpage_news;
	$newstype = getNewsType();
	switch ($newstype) {
		case "image":
		case "video":
			$thumb = '<img src="' . html_encode(pathurlencode($_zp_current_zenpage_news->getCustomImage(NULL, 80, 80, 80, 80, NULL, NULL, true, NULL))) . '" alt="' . html_encode($_zp_current_zenpage_news->getTitle()) . '" />';
			break;
		case "album":
			$obj = $_zp_current_zenpage_news->getAlbumThumbImage();
			$thumb = '<img src="' . html_encode(pathurlencode($obj->getCustomImage(NULL, 80, 80, 80, 80, NULL, NULL, true, NULL))) . '" alt="' . html_encode($_zp_current_zenpage_news->getTitle()) . '" />';
		default:
			$thumb = '';
			break;
	}
	echo $thumb;
}

/**
 * Prints the image/subalbum count for the album loop
 */
function jqm_printImageAlbumCount() {
	$numalb = getNumAlbums();
	$numimg = getNumImages();
	if ($numalb != 0) {
		printf(ngettext("%d album", "%d albums", $numalb), $numalb);
	}
	if ($numalb != 0 && $numimg != 0)
		echo ' / ';
	if ($numimg != 0) {
		printf(ngettext("%d image", "%d images", $numimg), $numimg);
	}
}

/**
 * Prints jQuery JS to enable the toggling of search results of Zenpage  items
 *
 */
function printZDSearchToggleJS() {
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
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
		// ]]> -->
	</script>
	<?php
}

/**
 * Prints the "Show more results link" for search results for Zenpage items
 *
 * @param string $option "news" or "pages"
 * @param int $number_to_show how many search results should be shown initially
 */
function printZDSearchShowMoreLink($option, $number_to_show) {
	$option = strtolower(sanitize($option));
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

/**
 * Adds the css class necessary for toggling of Zenpage items search results
 *
 * @param string $option "news" or "pages"
 * @param string $c After which result item the toggling should begin. Here to be passed from the results loop.
 */
function printZDToggleClass($option, $c, $number_to_show) {
	$option = strtolower(sanitize($option));
	$c = sanitize_numeric($c);
	$number_to_show = sanitize_numeric($number_to_show);
	if ($c > $number_to_show) {
		echo ' class="' . $option . '_extrashow" style="display:none;"';
	}
}

$_zp_page_check = 'checkPageValidity'; //	opt-in, standard behavior
?>