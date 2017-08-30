<?php

/**
 * The plugin creates:
 * <ul>
 * <li><meta> tags using general existing info like <i>gallery description</i>, <i>tags</i> or Zenpage <i>news categories</i>.</li>
 * <li>Support for <var><link rel="canonical" href="..." /></var></li>
 * <li>Open Graph tags for social sharing</li>
 * <li>Pinterest sharing tag</li>
 * </ul>
 *
 * Just enable the plugin and the meta data will be inserted into your <var><head></var> section.
 * Use the plugin's options to choose which tags you want printed.
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage seo
 */
$plugin_is_filter = 5 | THEME_PLUGIN;
$plugin_description = gettext("A plugin to print the most common HTML meta tags to the head of your site’s pages.");
$plugin_author = "Malte Müller (acrylian)";

$option_interface = 'htmlmetatags';

if (in_context(ZP_INDEX)) {
	zp_register_filter('theme_head', 'htmlmetatags::getHTMLMetaData'); // insert the meta tags into the <head></head> if on a theme page.
	if (defined('LOCALE_TYPE')) {
		define('METATAG_LOCALE_TYPE', LOCALE_TYPE);
	} else {
		define('METATAG_LOCALE_TYPE', 0);
	}
}

class htmlmetatags {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('htmlmeta_cache_control', 'no-cache');
			setOptionDefault('htmlmeta_pragma', 'no-cache');
			setOptionDefault('htmlmeta_robots', 'index');
			setOptionDefault('htmlmeta_revisit_after', '10');
			setOptionDefault('htmlmeta_expires', '43200');
			setOptionDefault('htmlmeta_tags', '');

			if (getOption('htmlmeta_og-title')) { // assume this will be set
				setOptionDefault('htmlmeta_opengraph', 1);
			}
			//remove obsolete old options
			purgeOption('htmlmeta_og-title');
			purgeOption('htmlmeta_og-image');
			purgeOption('htmlmeta_og-description');
			purgeOption('htmlmeta_og-url');
			purgeOption('htmlmeta_og-type');

			// the html meta tag selector prechecked ones
			setOptionDefault('htmlmeta_htmlmeta_tags', '1');
			setOptionDefault('htmlmeta_tags_threshold', 1);
			setOptionDefault('htmlmeta_http-equiv-cache-control', '1');
			setOptionDefault('htmlmeta_http-equiv-pragma', '1');
			setOptionDefault('htmlmeta_name=keywords', '1');
			setOptionDefault('htmlmeta_name-description', '1');
			setOptionDefault('htmlmeta_name-robot', '1');
			setOptionDefault('htmlmeta_name-publisher', '1');
			setOptionDefault('htmlmeta_name-creator', '1');
			setOptionDefault('htmlmeta_name-author', '1');
			setOptionDefault('htmlmeta_name-copyright', '1');
			setOptionDefault('htmlmeta_name-generator', '1');
			setOptionDefault('htmlmeta_name-revisit-after', '1');
			setOptionDefault('htmlmeta_name-expires', '1');
			setOptionDefault('htmlmeta_name-generator', '1');
			setOptionDefault('htmlmeta_name-date', '1');
			setOptionDefault('htmlmeta_canonical-url', '0');
			setOptionDefault('htmlmeta_sitelogo', '');
			setOptionDefault('htmlmeta_twittercard', '');
			setOptionDefault('htmlmeta_twittername', '');
			setOptionDefault('htmlmeta_ogimage_width', 1280);
			setOptionDefault('htmlmeta_ogimage_height', 900);

			if (class_exists('cacheManager')) {
				cacheManager::deleteThemeCacheSizes('html_meta_tags');
				cacheManager::addThemeCacheSize('html_meta_tags', NULL, getOption('htmlmeta_ogimage_width'), getOption('htmlmeta_ogimage_height'), NULL, NULL, NULL, NULL, NULL, NULL, NULL, true);
			}
		}
	}

	// Gettext calls are removed because some terms like "noindex" are fixed terms that should not be translated so user know what setting they make.
	function getOptionsSupported() {

		$options = array(gettext('Cache control') => array('key' => 'htmlmeta_cache_control', 'type' => OPTION_TYPE_SELECTOR,
						'order' => 0,
						'selections' => array('no-cache' => "no-cache", 'public' => "public", 'private' => "private", 'no-store' => "no-store"),
						'desc' => gettext("If the browser cache should be used.")),
				gettext('Pragma') => array('key' => 'htmlmeta_pragma', 'type' => OPTION_TYPE_SELECTOR,
						'selections' => array('no-cache' => "no-cache", 'cache' => "cache"),
						'desc' => gettext("If the pages should be allowed to be cached on proxy servers.")),
				gettext('Robots') => array('key' => 'htmlmeta_robots', 'type' => OPTION_TYPE_SELECTOR,
						'selections' => array('noindex' => "noindex", 'index' => "index", 'nofollow' => "nofollow", 'noindex,nofollow' => "noindex,nofollow", 'noindex,follow' => "noindex,follow", 'index,nofollow' => "index,nofollow", 'none' => "none"),
						'desc' => gettext("If and how robots are allowed to visit the site. Default is “index”. Note that you also should use a robot.txt file.")),
				gettext('Revisit after') => array('key' => 'htmlmeta_revisit_after', 'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("Request the crawler to revisit the page after x days.")),
				gettext('Expires') => array('key' => 'htmlmeta_expires', 'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("When the page should be loaded directly from the server and not from any cache. You can either set a date/time in international date format <em>Sat, 15 Dec 2001 12:00:00 GMT (example)</em> or a number. A number then means seconds, the default value <em>43200</em> means 12 hours.")),
				gettext('Canonical URL link') => array('key' => 'htmlmeta_canonical-url', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 11,
						'desc' => gettext('This adds a link element to the head of each page with a <em>canonical url</em>. If the <code>seo_locale</code> plugin is enabled or <code>use subdomains</code> is checked it also generates alternate links for other languages (<code>&lt;link&nbsp;rel="alternate" hreflang="</code>...<code>" href="</code>...<code>" /&gt;</code>).')),
				gettext('Site logo') => array('key' => 'htmlmeta_sitelogo', 'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("Enter the full url to a specific site logo image. Facebook, Google+ and others will use that as the thumb shown in link previews within posts. For image or album pages the default size album or image thumb is used automatically.")),
				gettext('Twitter name') => array('key' => 'htmlmeta_twittername', 'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("If you enabled Twitter card meta tags, you need to enter your Twitter user name here.")),
				gettext('Open graph image - width') => array('key' => 'htmlmeta_ogimage_width', 'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("Max width of the open graph image used for sharing to social networks if enabled.")),
				gettext('Open graph image - height') => array('key' => 'htmlmeta_ogimage_height', 'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("Max height of the open graph image used for sharing to social networks if enabled.")),
				gettext('HTML meta tags') => array('key' => 'htmlmeta_tags', 'type' => OPTION_TYPE_CHECKBOX_UL,
						"checkboxes" => array(
								"http-equiv='cache-control'" => "htmlmeta_http-equiv-cache-control",
								"http-equiv='pragma'" => "htmlmeta_http-equiv-pragma",
								"http-equiv='content-style-type'" => "htmlmeta_http-equiv-content-style-type",
								"name='keywords'" => "htmlmeta_name-keywords",
								"name='description'" => "htmlmeta_name-description",
								"name='page-topic'" => "htmlmeta_name-page-topic",
								"name='robots'" => "htmlmeta_name-robots",
								"name='publisher'" => "htmlmeta_name-publisher",
								"name='creator'" => "htmlmeta_name-creator",
								"name='author'" => "htmlmeta_name-author",
								"name='copyright'" => "htmlmeta_name-copyright",
								"name='rights'" => "htmlmeta_name-rights",
								"name='generator' ('ZenPhoto20')" => "htmlmeta_name-generator",
								"name='revisit-after'" => "htmlmeta_name-revisit-after",
								"name='expires'" => "htmlmeta_name-expires",
								"name='date'" => "htmlmeta_name-date",
								"OpenGraph (og:)" => "htmlmeta_opengraph",
								"name='pinterest' content='nopin'" => "htmlmeta_name-pinterest",
								"twitter:card" => "htmlmeta_twittercard"
						),
						"desc" => gettext("Which of the HTML meta tags should be used. For info about these in detail please refer to the net."))
		);

		return $options;
	}

	/**
	 * Prints html meta data to be used in the <head> section of a page
	 *
	 */
	static function getHTMLMetaData() {
		global $_zp_gallery, $_zp_page, $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_current_article,
		$_zp_current_page, $_zp_gallery_page, $_zp_current_category, $_zp_authority, $_zp_conf_vars, $_myFavorites;

		$url = FULLHOSTPATH . getRequestURI();

		// Convert locale shorttag to allowed html meta format
		$locale_ = getUserLocale();
		$locale = zpFunctions::getLanguageText($locale_, '-');
		$canonicalurl = '';
		// generate page title, get date
		$pagetitle = ""; // for gallery index setup below switch
		$date = strftime(DATE_FORMAT); // if we don't have a item date use current date
		$desc = getBareGalleryDesc();
		$thumb = '';
		if (getOption('htmlmeta_sitelogo')) {
			$thumb = getOption('htmlmeta_sitelogo');
		}
		if (getOption('htmlmeta_og-image') || getOption('htmlmeta_twittercard')) {
			$ogimage_width = getOption('htmlmeta_ogimage_width');
			$ogimage_height = getOption('htmlmeta_ogimage_height');
			if (empty($ogimage_width)) {
				$ogimage_width = 1280;
			}
			if (empty($ogimage_height)) {
				$ogimage_height = 900;
			}
			$twittercard_type = 'summary';
		}
		$type = 'article';
		switch ($_zp_gallery_page) {
			case 'index.php':
				$desc = getBareGalleryDesc();
				$canonicalurl = FULLHOSTPATH . $_zp_gallery->getLink($_zp_page);
				$type = 'website';
				break;
			case 'album.php':
			case 'favorites.php';
				$pagetitle = getBareAlbumTitle() . " - ";
				$date = getAlbumDate();
				$desc = getBareAlbumDesc();
				$canonicalurl = FULLHOSTPATH . $_zp_current_album->getLink($_zp_page);
				if (getOption('htmlmeta_og-image') || getOption('htmlmeta_twittercard')) {
					$thumbimg = $_zp_current_album->getAlbumThumbImage();
					getMaxSpaceContainer($ogimage_width, $ogimage_height, $thumbimg, false);
					$thumb = FULLHOSTPATH . html_encode(pathurlencode($thumbimg->getCustomImage(NULL, $ogimage_width, $ogimage_height, NULL, NULL, NULL, NULL, false, NULL)));
					$twittercard_type = 'summary_large_image';
				}
				break;
			case 'image.php':
				$pagetitle = getBareImageTitle() . " (" . getBareAlbumTitle() . ") - ";
				$date = getImageDate();
				$desc = getBareImageDesc();
				$canonicalurl = FULLHOSTPATH . $_zp_current_image->getLink();
				if (getOption('htmlmeta_og-image') || getOption('htmlmeta_twittercard')) {
					$thumb = FULLHOSTPATH . html_encode(pathurlencode(getCustomSizedImageMaxSpace($ogimage_width, $ogimage_height)));
					$twittercard_type = 'summary_large_image';
				}
				break;
			case 'news.php':
				if (function_exists("is_NewsArticle")) {
					if (is_NewsArticle()) {
						$pagetitle = getBareNewsTitle() . " - ";
						$date = getNewsDate();
						$desc = trim(getBare(getNewsContent()));
						$canonicalurl = FULLHOSTPATH . $_zp_current_article->getLink();
					} else if (is_NewsCategory()) {
						$pagetitle = $_zp_current_category->getTitlelink() . " - ";
						$date = strftime(DATE_FORMAT);
						$desc = trim(getBare($_zp_current_category->getDesc()));
						$canonicalurl = FULLHOSTPATH . $_zp_current_category->getLink($_zp_page);
						$type = 'category';
					} else {
						$pagetitle = gettext('News') . " - ";
						$desc = '';
						$canonicalurl = FULLHOSTPATH . getNewsPathNav($_zp_page);
						$type = 'website';
					}
				}
				break;
			case 'pages.php':
				$pagetitle = getBarePageTitle() . " - ";
				$date = getPageDate();
				$desc = trim(getBare(getPageContent()));
				$canonicalurl = FULLHOSTPATH . $_zp_current_page->getLink();
				break;
			default: // for all other possible static custom pages
				$custompage = stripSuffix($_zp_gallery_page);
				$standard = array('contact' => gettext('Contact'), 'register' => gettext('Register'), 'search' => gettext('Search'), 'archive' => gettext('Archive view'), 'password' => gettext('Password required'));
				if (is_object($_myFavorites)) {
					$standard['favorites'] = gettext('My favorites');
				}
				If (array_key_exists($custompage, $standard)) {
					$pagetitle = $standard[$custompage] . " - ";
				} else {
					$pagetitle = $custompage . " - ";
				}
				$desc = '';
				$canonicalurl = FULLHOSTPATH . getCustomPageURL($custompage);
				break;
		}
		// shorten desc to the allowed 200 characters if necesssary.
		$desc = html_encode(trim(substr(getBare($desc), 0, 160)));
		$pagetitle = $pagetitle . getBareGalleryTitle();
		// get master admin
		$admin = $_zp_authority->getMasterUser();
		$author = $admin->getName();
		$meta = '';
		if (getOption('htmlmeta_http-equiv-cache-control')) {
			$meta .= '<meta http-equiv="Cache-control" content="' . getOption("htmlmeta_cache_control") . '">' . "\n";
		}
		if (getOption('htmlmeta_http-equiv-pragma')) {
			$meta .= '<meta http-equiv="pragma" content="' . getOption("htmlmeta_pragma") . '">' . "\n";
		}
		if (getOption('htmlmeta_name-keywords')) {
			$meta .= '<meta name="keywords" content="' . htmlmetatags::getMetaKeywords() . '">' . "\n";
		}
		if (getOption('htmlmeta_name-description')) {
			$meta .= '<meta name="description" content="' . $desc . '">' . "\n";
		}
		if (getOption('htmlmeta_name-page-topic')) {
			$meta .= '<meta name="page-topic" content="' . $desc . '">' . "\n";
		}
		if (getOption('htmlmeta_name-robots')) {
			$meta .= '<meta name="robots" content="' . getOption("htmlmeta_robots") . '">' . "\n";
		}
		if (getOption('htmlmeta_name-publisher')) {
			$meta .= '<meta name="publisher" content="' . FULLWEBPATH . '">' . "\n";
		}
		if (getOption('htmlmeta_name-creator')) {
			$meta .= '<meta name="creator" content="' . FULLWEBPATH . '">' . "\n";
		}
		if (getOption('htmlmeta_name-author')) {
			$meta .= '<meta name="author" content="' . $author . '">' . "\n";
		}
		if (getOption('htmlmeta_name-copyright')) {
			$meta .= '<meta name="copyright" content=" (c) ' . FULLWEBPATH . ' - ' . $author . '">' . "\n";
		}
		if (getOption('htmlmeta_name-rights')) {
			$meta .= '<meta name="rights" content="' . $author . '">' . "\n";
		}
		if (getOption('htmlmeta_name-generator')) {
			$meta .= '<meta name="generator" content="ZenPhoto20 ' . ZENPHOTO_VERSION . '">' . "\n";
		}
		if (getOption('htmlmeta_name-revisit-after')) {
			$meta .= '<meta name="revisit-after" content="' . getOption("htmlmeta_revisit_after") . ' days">' . "\n";
		}
		if (getOption('htmlmeta_name-expires')) {
			$expires = getOption("htmlmeta_expires");
			if ($expires == (int) $expires)
				$expires = preg_replace('|\s\-\d+|', '', date('r', time() + $expires)) . ' GMT';
			$meta .= '<meta name="expires" content="' . $expires . '">' . "\n";
		}

		// OpenGraph meta
		if (getOption('htmlmeta_opengraph')) {
			$meta .= '<meta property="og:title" content="' . $pagetitle . '">' . "\n";
			if (!empty($thumb)) {
				$meta .= '<meta property="og:image" content="' . $thumb . '">' . "\n";
			}
			$meta .= '<meta property="og:description" content="' . $desc . '">' . "\n";
			$meta .= '<meta property="og:url" content="' . html_encode($url) . '">' . "\n";
			$meta .= '<meta property="og:type" content="' . $type . '">' . "\n";
		}

		// Social network extras
		if (getOption('htmlmeta_name-pinterest')) {
			$meta .= '<meta name="pinterest" content="nopin">' . "\n";
		} // dissalow users to pin images on Pinterest
		// Twitter card
		if (getOption('htmlmeta_twittercard')) {
			$twittername = getOption('htmlmeta_twittername');
			if (!empty($twittername)) {
				$meta .= '<meta name="twitter:creator" content="' . $twittername . '">' . "\n";
				$meta .= '<meta name="twitter:site" content="' . $twittername . '">' . "\n";
			}
			$meta .= '<meta name="twitter:card" content="' . $twittercard_type . '">' . "\n";
			$meta .= '<meta name="twitter:title" content="' . $pagetitle . '">' . "\n";
			$meta .= '<meta name="twitter:description" content="' . $desc . '">' . "\n";
			if (!empty($thumb)) {
				$meta .= '<meta name="twitter:image" content="' . $thumb . '">' . "\n";
			}
		}

		// Canonical url
		if (getOption('htmlmeta_canonical-url')) {
			$meta .= '<link rel="canonical" href="' . $canonicalurl . '">' . "\n";
			if (METATAG_LOCALE_TYPE) {
				$langs = generateLanguageList();
				if (count($langs) != 1) {

					if (METATAG_LOCALE_TYPE == 1) {
						$locallink = seo_locale::localePath(false, $locale_);
					} else {
						$locallink = '';
					}
					foreach ($langs as $text => $lang) {
						$langcheck = zpFunctions::getLanguageText($lang, '-'); //	for hreflang we need en-US
						if ($langcheck != $locale) {
							if (METATAG_LOCALE_TYPE == 1) {
								$altlink = seo_locale::localePath(true, $lang);
							} else {
								$altlink = dynamic_locale::fullHostPath($lang);
							}
							switch ($_zp_gallery_page) {
								case 'index.php':
									$altlink .= str_replace($locallink, '', $_zp_gallery->getLink($_zp_page));
									break;
								case 'album.php':
								case 'favorites.php';
									$altlink .= str_replace($locallink, '', $_zp_current_album->getLink($_zp_page));
									break;
								case 'image.php':
									$altlink .= str_replace($locallink, '', $_zp_current_image->getLink());
									break;
								case 'news.php':
									if (function_exists("is_NewsArticle")) {
										if (is_NewsArticle()) {
											$altlink .= str_replace($locallink, '', $_zp_current_article->getLink());
										} else if (is_NewsCategory()) {
											$altlink .= str_replace($locallink, '', $_zp_current_category->getLink($_zp_page));
										} else {
											$altlink .= getNewsPathNav($_zp_page);
										}
									}
									break;
								case 'pages.php':
									$altlink .= str_replace($locallink, '', $_zp_current_page->getLink());
									break;
								case 'archive.php':
									$altlink .= getCustomPageURL('archive');
									break;
								case 'search.php':
									$searchwords = $_zp_current_search->codifySearchString();
									$searchdate = $_zp_current_search->getSearchDate();
									$searchfields = $_zp_current_search->getSearchFields(true);
									$searchpagepath = getSearchURL($searchwords, $searchdate, $searchfields, $_zp_page, array('albums' => $_zp_current_search->getAlbumList()));
									$altlink .= $searchpagepath;
									break;
								case 'contact.php':
									$altlink .= getCustomPageURL('contact');
									break;
								default: // for all other possible none standard custom pages
									$altlink .= getCustomPageURL($pagetitle);
									break;
							} // switch
							$meta .= '<link rel="alternate" hreflang="' . $langcheck . '" href="' . html_encode($altlink) . '">' . "\n";
						} // if lang
					} // foreach
				} // if count
			} // if option
		} // if canonical

		echo $meta;
	}

	/**
	 * Helper function to list tags/categories as keywords separated by comma.
	 *
	 * @param array $array the array of the tags or categories to list
	 */
	private static function getMetaKeywords() {
		global $_zp_gallery, $_zp_current_album, $_zp_current_image, $_zp_current_article, $_zp_current_page, $_zp_current_category, $_zp_gallery_page, $_zp_CMS;
		$words = '';
		if (is_object($_zp_current_album) OR is_object($_zp_current_image)) {
			$tags = getTags();
			$words .= htmlmetatags::getMetaAlbumAndImageTags($tags, "gallery");
		} else if ($_zp_gallery_page === "index.php") {
			$tags = array_keys(getAllTagsUnique(NULL, getOption('htmlmeta_tags_threshold'))); // get all if no specific item is set
			$words .= htmlmetatags::getMetaAlbumAndImageTags($tags, "gallery");
		}
		if (extensionEnabled('zenpage')) {
			if (is_NewsArticle()) {
				$tags = getNewsCategories(getNewsID());
				$words .= htmlmetatags::getMetaAlbumAndImageTags($tags, "zenpage");
				$tags = getTags();
				$words = $words . ", " . htmlmetatags::getMetaAlbumAndImageTags($tags, "gallery");
			} else if (is_Pages()) {
				$tags = getTags();
				$words = htmlmetatags::getMetaAlbumAndImageTags($tags, "gallery");
			} else if (is_News()) {
				$tags = $_zp_CMS->getAllCategories();
				$words .= htmlmetatags::getMetaAlbumAndImageTags($tags, "zenpage");
			} else if (is_NewsCategory()) {
				$words .= $_zp_current_category->getTitle();
			}
		}
		return $words;
	}

	/**
	 * Helper function to print the album and image tags and/or the news article categorieslist within printMetaKeywords()
	 * Shortens the length to the allowed 1000 characters.
	 *
	 * @param array $tags the array of the tags to list
	 * @param string $mode "gallery" (to process tags on all) or "zenpage" (to process news categories)
	 */
	private static function getMetaAlbumAndImageTags($tags, $mode = "") {
		if (is_array($tags)) {
			$alltags = '';
			$count = "";
			$separator = ", ";
			foreach ($tags as $keyword) {
				$count++;
				if ($count >= count($tags))
					$separator = "";
				switch ($mode) {
					case "gallery":
						$alltags .= html_encode($keyword) . $separator;
						break;
					case "zenpage":
						$alltags .= html_encode($keyword["titlelink"]) . $separator;
						break;
				}
			}
		} else {
			$alltags = $tags;
		}
		return $alltags;
	}

}

?>
