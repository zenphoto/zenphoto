<?php

/**
 * The plugin creates:
 * <ul>
 * <li><meta> tags using general existing Zenphoto info like <i>gallery description</i>, <i>tags</i> or Zenpage <i>news categories</i>.</li>
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
		setOptionDefault('htmlmeta_cache_control', 'no-cache');
		setOptionDefault('htmlmeta_pragma', 'no-cache');
		setOptionDefault('htmlmeta_robots', 'index');
		setOptionDefault('htmlmeta_revisit_after', '10 Days');
		setOptionDefault('htmlmeta_expires', '43200');
		setOptionDefault('htmlmeta_tags', '');

		if(getOption('htmlmeta_og-title')) { // assume this will be set
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

	// Gettext calls are removed because some terms like "noindex" are fixed terms that should not be translated so user know what setting they make.
	function getOptionsSupported() {
		global $_common_locale_type;
		$localdesc = '<p>' . gettext('If checked links to the alternative languages will be in the form <code><em>language</em>.domain</code> where <code><em>language</em></code> is the language code, e.g. <code><em>fr</em></code> for French.') . '</p>';
		if (!$_common_locale_type) {
			$localdesc .= '<p>' . gettext('This requires that you have created the appropriate subdomains pointing to your Zenphoto installation. That is <code>fr.mydomain.com/zenphoto/</code> must point to the same location as <code>mydomain.com/zenphoto/</code>. (Some providers will automatically redirect undefined subdomains to the main domain. If your provider does this, no subdomain creation is needed.)') . '</p>';
		}
		$options = array(gettext('Cache control')				 => array('key'				 => 'htmlmeta_cache_control', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 0,
										'selections' => array('no-cache' => "no-cache", 'public' => "public", 'private' => "private", 'no-store' => "no-store"),
										'desc'			 => gettext("If the browser cache should be used.")),
						gettext('Pragma')								 => array('key'				 => 'htmlmeta_pragma', 'type'			 => OPTION_TYPE_SELECTOR,
										'selections' => array('no-cache' => "no-cache", 'cache' => "cache"),
										'desc'			 => gettext("If the pages should be allowed to be cached on proxy servers.")),
						gettext('Robots')								 => array('key'				 => 'htmlmeta_robots', 'type'			 => OPTION_TYPE_SELECTOR,
										'selections' => array('noindex' => "noindex", 'index' => "index", 'nofollow' => "nofollow", 'noindex,nofollow' => "noindex,nofollow", 'noindex,follow' => "noindex,follow", 'index,nofollow' => "index,nofollow", 'none' => "none"),
										'desc'			 => gettext("If and how robots are allowed to visit the site. Default is “index”. Note that you also should use a robot.txt file.")),
						gettext('Revisit after')				 => array('key'	 => 'htmlmeta_revisit_after', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Request the crawler to revisit the page after x days.")),
						gettext('Expires')							 => array('key'	 => 'htmlmeta_expires', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("When the page should be loaded directly from the server and not from any cache. You can either set a date/time in international date format <em>Sat, 15 Dec 2001 12:00:00 GMT (example)</em> or a number. A number then means seconds, the default value <em>43200</em> means 12 hours.")),
						gettext('Canonical URL link')		 => array('key'		 => 'htmlmeta_canonical-url', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 11,
										'desc'	 => gettext('This adds a link element to the head of each page with a <em>canonical url</em>. If the <code>seo_locale</code> plugin is enabled or <code>use subdomains</code> is checked it also generates alternate links for other languages (<code>&lt;link&nbsp;rel="alternate" hreflang="</code>...<code>" href="</code>...<code>" /&gt;</code>).')),
						gettext('Site logo')						 => array('key'	 => 'htmlmeta_sitelogo', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Enter the full url to a specific site logo image. Facebook, Google+ and others will use that as the thumb shown in link previews within posts. For image or album pages the default size album or image thumb is used automatically.")),
						gettext('Twitter name')					 => array('key'	 => 'htmlmeta_twittername', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("If you enabled Twitter card meta tags, you need to enter your Twitter user name here.")),
						gettext('Open graph image - width')							 => array('key'	 => 'htmlmeta_ogimage_width', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Max width of the open graph image used for sharing to social networks if enabled.")),
						gettext('Open graph image - height')							 => array('key'	 => 'htmlmeta_ogimage_height', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Max height of the open graph image used for sharing to social networks if enabled.")),
						gettext('HTML meta tags')				 => array('key'				 => 'htmlmeta_tags', 'type'			 => OPTION_TYPE_CHECKBOX_UL,
										"checkboxes" => array(
														"http-equiv='cache-control'"					 => "htmlmeta_http-equiv-cache-control",
														"http-equiv='pragma'"									 => "htmlmeta_http-equiv-pragma",
														"http-equiv='content-style-type'"			 => "htmlmeta_http-equiv-content-style-type",
														"name='keywords'"											 => "htmlmeta_name-keywords",
														"name='description'"									 => "htmlmeta_name-description",
														"name='page-topic'"										 => "htmlmeta_name-page-topic",
														"name='robots'"												 => "htmlmeta_name-robots",
														"name='publisher'"										 => "htmlmeta_name-publisher",
														"name='creator'"											 => "htmlmeta_name-creator",
														"name='author'"												 => "htmlmeta_name-author",
														"name='copyright'"										 => "htmlmeta_name-copyright",
														"name='rights'"												 => "htmlmeta_name-rights",
														"name='generator' ('Zenphoto')"				 => "htmlmeta_name-generator",
														"name='revisit-after'"								 => "htmlmeta_name-revisit-after",
														"name='expires'"											 => "htmlmeta_name-expires",
														"name='date'"													 => "htmlmeta_name-date",
														"property='og:title'"									 => "htmlmeta_og-title",
														"property='og:image'"									 => "htmlmeta_og-image",
														"property='og:description'"						 => "htmlmeta_og-description",
														"property='og:url'"										 => "htmlmeta_og-url",
														"property='og:type'"									 => "htmlmeta_og-type",
														"OpenGraph (og:)"											 => "htmlmeta_opengraph",
														"name='pinterest' content='nopin'"		 => "htmlmeta_name-pinterest",
														"twitter:card"												 => "htmlmeta_twittercard"
										),
										"desc"			 => gettext("Which of the HTML meta tags should be used. For info about these in detail please refer to the net.")),
						gettext('Use subdomains') . '*'	 => array('key'			 => 'dynamic_locale_subdomain', 'type'		 => OPTION_TYPE_CHECKBOX,
										'order'		 => 12,
										'disabled' => $_common_locale_type,
										'desc'		 => $localdesc)
		);
		if ($_common_locale_type) {
			$options['note'] = array('key'		 => 'html_meta_tags_locale_type', 'type'	 => OPTION_TYPE_NOTE,
							'order'	 => 13,
							'desc'	 => '<p class="notebox">' . $_common_locale_type . '</p>');
		} else {
			$_common_locale_type = gettext('* This option may be set via the <a href="javascript:gotoName(\'html_meta_tags\');"><em>html_meta_tags</em></a> plugin options.');
			$options['note'] = array('key'		 => 'html_meta_tags_locale_type',
							'type'	 => OPTION_TYPE_NOTE,
							'order'	 => 13,
							'desc'	 => gettext('<p class="notebox">*<strong>Note:</strong> The setting of this option is shared with other plugins.</p>'));
		}
		return $options;
	}

	/**
	 * Traps imageProcessorURIs for causing them to be cached.
	 * @param string $uri
	 */
	static function ipURI($uri) {
		global $htmlmetatags_need_cache;
		$htmlmetatags_need_cache[] = $uri;
	}

	/**
	 * Prints html meta data to be used in the <head> section of a page
	 *
	 */
	static function getHTMLMetaData() {
		global $_zp_gallery, $_zp_gallery_page, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news,
		$_zp_current_zenpage_page, $_zp_current_category, $_zp_authority, $_zp_conf_vars, $_myFavorites,
		$htmlmetatags_need_cache, $_zp_page;
		zp_register_filter('image_processor_uri', 'htmlmetatags::ipURI');
		$host = sanitize("http://" . $_SERVER['HTTP_HOST']);
		$url = $host . getRequestURI();

		// Convert locale shorttag to allowed html meta format
		$locale = str_replace("_", "-", getUserLocale());
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
				//$canonicalurl = $host . getGalleryIndexURL();
				$canonicalurl = $host . getPageNumURL($_zp_page);
				$type = 'website';
				break;
			case 'album.php':
				$pagetitle = getBareAlbumTitle() . " - ";
				$date = getAlbumDate();
				$desc = getBareAlbumDesc();
				$canonicalurl = $host . getPageNumURL($_zp_page);
				if (getOption('htmlmeta_og-image') || getOption('htmlmeta_twittercard')) {
					$thumbimg = $_zp_current_album->getAlbumThumbImage();
					getMaxSpaceContainer($ogimage_width, $ogimage_height, $thumbimg, false);
					$thumb = $host . html_encode(pathurlencode($thumbimg->getCustomImage(NULL, $ogimage_width, $ogimage_height, NULL, NULL, NULL, NULL, false, NULL)));
					$twittercard_type = 'summary_large_image';
				}
				break;
			case 'image.php':
				$pagetitle = getBareImageTitle() . " (" . getBareAlbumTitle() . ") - ";
				$date = getImageDate();
				$desc = getBareImageDesc();
				$canonicalurl = $host . getImageURL();
				if (getOption('htmlmeta_og-image') || getOption('htmlmeta_twittercard')) {
					$thumb = $host . html_encode(pathurlencode(getCustomSizedImageMaxSpace($ogimage_width, $ogimage_height)));
					$twittercard_type = 'summary_large_image';
				}
				break;
			case 'news.php':
				if (function_exists("is_NewsArticle")) {
					if (is_NewsArticle()) {
						$pagetitle = getBareNewsTitle() . " - ";
						$date = getNewsDate();
						$desc = trim(getBare(getNewsContent()));
						$canonicalurl = $host . $_zp_current_zenpage_news->getLink();
					} else if (is_NewsCategory()) {
						$pagetitle = $_zp_current_category->getTitlelink() . " - ";
						$date = strftime(DATE_FORMAT);
						$desc = trim(getBare($_zp_current_category->getDesc()));
						$canonicalurl = $host . $_zp_current_category->getLink();
						$type = 'category';
					} else {
						$pagetitle = gettext('News') . " - ";
						$desc = '';
						$canonicalurl = $host . getNewsIndexURL();
						$type = 'website';
					}
					if ($_zp_page != 1) {
						$canonicalurl .= '/' . $_zp_page;
					}
				}
				break;
			case 'pages.php':
				$pagetitle = getBarePageTitle() . " - ";
				$date = getPageDate();
				$desc = trim(getBare(getPageContent()));
				$canonicalurl = $host . $_zp_current_zenpage_page->getLink();
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
				$canonicalurl = $host . getCustomPageURL($custompage);
				if ($_zp_page != 1) {
					$canonicalurl .= '/'. $_zp_page;
				}
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
			$meta .= '<meta name="generator" content="Zenphoto ' . ZENPHOTO_VERSION . '">' . "\n";
		}
		if (getOption('htmlmeta_name-revisit-after')) {
			$meta .= '<meta name="revisit-after" content="' . getOption("htmlmeta_revisit_after") . '">' . "\n";
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
					foreach ($langs as $text => $lang) {
						$langcheck = zpFunctions::getLanguageText($lang, '-'); //	for hreflang we need en-US
						if ($langcheck != $locale) {
							switch (METATAG_LOCALE_TYPE) {
								case 1:
									$altlink = seo_locale::localePath(true, $lang);
									break;
								case 2:
									$altlink = dynamic_locale::fullHostPath($lang);
									break;
							}
							switch ($_zp_gallery_page) {
								case 'index.php':
									$altlink .= '/';
									break;
								case 'gallery.php':
									$altlink .= '/'. _PAGE_ . '/gallery';
									break;
								case 'album.php':
									$altlink .= '/' . html_encode($_zp_current_album->name) . '/';
									break;
								case 'image.php':
									$altlink .= '/' . html_encode($_zp_current_album->name) . '/' . html_encode($_zp_current_image->filename) . IM_SUFFIX;
									break;
								case 'news.php':
									if (function_exists("is_NewsArticle")) {
										if (is_NewsArticle()) {
											$altlink .= '/' . _NEWS_ . '/' . html_encode($_zp_current_zenpage_news->getTitlelink());
										} else if (is_NewsCategory()) {
											$altlink .= '/' . _NEWS_ . '/' . html_encode($_zp_current_category->getTitlelink());
										} else {
											$altlink .= '/' . _NEWS_;
										}
									}
									break;
								case 'pages.php':
									$altlink .= '/' . _PAGES_ . '/' . html_encode($_zp_current_zenpage_page->getTitlelink());
									break;
								case 'archive.php':
									$altlink .= '/' . _ARCHIVE_ ;
									break;
								case 'search.php':
									$altlink .= '/' . _SEARCH_ . '/';
									break;
								case 'contact.php':
									$altlink .= '/' . _CONTACT_ . '/';
									break;
								default: // for all other possible none standard custom pages
									$altlink .= '/' . _PAGE_ . '/' . html_encode($pagetitle);
									break;
							} // switch

							//append page number if needed
							switch ($_zp_gallery_page) {
								case 'index.php':
								case 'album.php':
									if ($_zp_page != 1) {
										$altlink .= _PAGE_ . '/' . $_zp_page . '/';
									}
									break;
								case 'gallery.php':
								case 'news.php':
									if ($_zp_page != 1) {
										$altlink .= '/' . $_zp_page;
									}
									break;
							}
							$meta .= '<link rel="alternate" hreflang="' . $langcheck . '" href="' . $altlink . '">' . "\n";
						} // if lang
					} // foreach
				} // if count
			} // if option




		} // if canonical
		if (!empty($htmlmetatags_need_cache)) {
			$meta .= '<script type="text/javascript">' . "\n";
			$meta .= 'var caches = ["' . implode('","', $htmlmetatags_need_cache) . '"];' . "\n";
			$meta .= '
					window.onload = function() {
						var index,value;
						for (index in caches) {
								value = caches[index];
								$.ajax({
									cache: false,
									type: "GET",
									url: value
								});
						}
					}
					';
			$meta .= '</script>' . "\n";
		}
		zp_remove_filter('image_processor_uri', 'htmlmetatags::ipURI');
		echo $meta;
	}

	/**
	 * Helper function to list tags/categories as keywords separated by comma.
	 *
	 * @param array $array the array of the tags or categories to list
	 */
	private static function getMetaKeywords() {
		global $_zp_gallery, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_current_category, $_zp_gallery_page, $_zp_zenpage;
		$words = '';
		if (is_object($_zp_current_album) OR is_object($_zp_current_image)) {
			$tags = getTags();
			$words .= htmlmetatags::getMetaAlbumAndImageTags($tags, "gallery");
		} else if ($_zp_gallery_page === "index.php") {
			$tags = array_keys(getAllTagsCount(true)); // get all if no specific item is set
			$words .= htmlmetatags::getMetaAlbumAndImageTags($tags, "gallery");
		}
		if (extensionEnabled('zenpage')) {
			if (is_NewsArticle()) {
				$tags = getNewsCategories(getNewsID());
				$words .= htmlmetatags::getMetaAlbumAndImageTags($tags, "zenpage");
				$tags = getTags();
				$words = $words . "," . htmlmetatags::getMetaAlbumAndImageTags($tags, "gallery");
			} else if (is_Pages()) {
				$tags = getTags();
				$words = htmlmetatags::getMetaAlbumAndImageTags($tags, "gallery");
			} else if (is_News()) {
				$tags = $_zp_zenpage->getAllCategories();
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
