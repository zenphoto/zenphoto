<?php

/**
 * The plugin usses general existing Zenphoto info like <i>gallery description</i>, <i>tags</i> or Zenpage <i>news categories</i>.
 * It also has support for <var><link rel="canonical" href="" /></var>
 *
 * Just enable the plugin and the meta data will be inserted into your <var><head></var> section.
 * Use the plugin's options to choose which tags you want printed.
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage seo
 */
$plugin_description = gettext("A plugin to print the most common HTML meta tags to the head of your site's pages.");
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

		// the html meta tag selector prechecked ones
		setOptionDefault('htmlmeta_http-equiv-language', '1');
		setOptionDefault('htmlmeta_name-language', '1');
		setOptionDefault('htmlmeta_htmlmeta_tags', '1');
		setOptionDefault('htmlmeta_http-equiv-cache-control', '1');
		setOptionDefault('htmlmeta_http-equiv-pragma', '1');
		setOptionDefault('htmlmeta_http-equiv-content-style-type', '1');
		setOptionDefault('htmlmeta_name-title', '1');
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
										'selections' => array('no-cache' => "no-cache", 'public'	 => "public", 'private'	 => "private", 'no-store' => "no-store"),
										'desc'			 => gettext("If the browser cache should be used.")),
						gettext('Pragma')								 => array('key'				 => 'htmlmeta_pragma', 'type'			 => OPTION_TYPE_SELECTOR,
										'selections' => array('no-cache' => "no-cache", 'cache'		 => "cache"),
										'desc'			 => gettext("If the pages should be allowed to be cached on proxy servers.")),
						gettext('Robots')								 => array('key'				 => 'htmlmeta_robots', 'type'			 => OPTION_TYPE_SELECTOR,
										'selections' => array('noindex'					 => "noindex", 'index'						 => "index", 'nofollow'				 => "nofollow", 'noindex,nofollow' => "noindex,nofollow", 'noindex,follow'	 => "noindex,follow", 'index,nofollow'	 => "index,nofollow", 'none'						 => "none"),
										'desc'			 => gettext("If and how robots are allowed to visit the site. Default is 'index'. Note that you also should use a robot.txt file.")),
						gettext('Revisit after')				 => array('key'	 => 'htmlmeta_revisit_after', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Request the crawler to revisit the page after x days.")),
						gettext('Expires')							 => array('key'	 => 'htmlmeta_expires', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("When the page should be loaded directly from the server and not from any cache. You can either set a date/time in international date format <em>Sat, 15 Dec 2001 12:00:00 GMT (example)</em> or a number. A number then means seconds, the default value <em>43200</em> means 12 hours.")),
						gettext('Canonical URL link')		 => array('key'		 => 'htmlmeta_canonical-url', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 11,
										'desc'	 => gettext('This adds a link element to the head of each page with a <em>canonical url</em>. If the <code>seo_locale</code> plugin is enabled or <code>use subdomains</code> is checked it also generates alternate links for other languages (<code>&lt;link&nbsp;rel="alternate" hreflang="</code>...<code>" href="</code>...<code>" /&gt;</code>).')),
						gettext('Site logo')						 => array('key'	 => 'htmlmeta_sitelogo', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Enter the full url to a specific site logo image. Facebook, Google+ and others will use that as the thumb shown in link previews within posts. For image or album pages the default size album or image thumb is used automatically.")),
						gettext('HTML meta tags')				 => array('key'				 => 'htmlmeta_tags', 'type'			 => OPTION_TYPE_CHECKBOX_UL,
										"checkboxes" => array(
														"http-equiv='language'"								 => "htmlmeta_http-equiv-language",
														"name = 'language'"										 => "htmlmeta_name-language",
														"content-language"										 => "htmlmeta_name-content-language",
														"http-equiv='imagetoolbar' ('false')"	 => "htmlmeta_http-equiv-imagetoolbar",
														"http-equiv='cache-control'"					 => "htmlmeta_http-equiv-cache-control",
														"http-equiv='pragma'"									 => "htmlmeta_http-equiv-pragma",
														"http-equiv='content-style-type'"			 => "htmlmeta_http-equiv-content-style-type",
														"name='title'"												 => "htmlmeta_name-title",
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
														"name='DC.title'"											 => "htmlmeta_name-DC-title",
														"name='DC.keywords'"									 => "htmlmeta_name-DC-keywords",
														"name='DC.description'"								 => "htmlmeta_name-DC-description",
														"name='DC.language'"									 => "htmlmeta_name-DC-language",
														"name='DC.subject'"										 => "htmlmeta_name-DC-subject",
														"name='DC.publisher'"									 => "htmlmeta_name-DC-publisher",
														"name='DC.creator'"										 => "htmlmeta_name-DC-creator",
														"name='DC.date'"											 => "htmlmeta_name-DC-date",
														"name='DC.type'"											 => "htmlmeta_name-DC-type",
														"name='DC.format'"										 => "htmlmeta_name-DC-format",
														"name='DC.identifier'"								 => "htmlmeta_name-DC-identifier",
														"name='DC.rights'"										 => "htmlmeta_name-DC-rights",
														"name='DC.source'"										 => "htmlmeta_name-DC-source",
														"name='DC.relation'"									 => "htmlmeta_name-DC-relation",
														"name='DC.Date.created'"							 => "htmlmeta_name-DC-Date-created",
														"property='og:title'"									 => "htmlmeta_og-title",
														"property='og:image'"									 => "htmlmeta_og-image",
														"property='og:description'"						 => "htmlmeta_og-description",
														"property='og:url'"										 => "htmlmeta_og-url",
														"property='og:type'"									 => "htmlmeta_og-type",
														"name='pinterest' content='nopin'"		 => "htmlmeta_name-pinterest"
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
		global $_zp_gallery, $_zp_galley_page, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news,
		$_zp_current_zenpage_page, $_zp_gallery_page, $_zp_current_category, $_zp_authority, $_zp_conf_vars,
		$htmlmetatags_need_cache;
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
		$type = 'article';
		switch ($_zp_gallery_page) {
			case 'index.php':
				$desc = getBareGalleryDesc();
				$canonicalurl = $host . getGalleryIndexURL(false);
				$type = 'website';
				break;
			case 'album.php':
				$pagetitle = getBareAlbumTitle() . " - ";
				$date = getAlbumDate();
				$desc = getBareAlbumDesc();
				$canonicalurl = $host . getAlbumLinkURL();
				if (getOption('htmlmeta_og-image')) {
					$thumb = $host . getAlbumThumb();
				}
				break;
			case 'image.php':
				$pagetitle = getBareImageTitle() . " (" . getBareAlbumTitle() . ") - ";
				$date = getImageDate();
				$desc = getBareImageDesc();
				$canonicalurl = $host . getImageLinkURL();
				if (getOption('htmlmeta_og-image')) {
					$thumb = $host . getImageThumb();
				}
				break;
			case 'news.php':
				if (function_exists("is_NewsArticle")) {
					if (is_NewsArticle()) {
						$pagetitle = getBareNewsTitle() . " - ";
						$date = getNewsDate();
						$desc = trim(strip_tags(getNewsContent()));
						$canonicalurl = $host . getNewsURL($_zp_current_zenpage_news->getTitlelink());
					} else if (is_NewsCategory()) {
						$pagetitle = $_zp_current_category->getTitlelink() . " - ";
						$date = strftime(DATE_FORMAT);
						$desc = trim(strip_tags($_zp_current_category->getDesc()));
						$canonicalurl = $host . getNewsCategoryURL($_zp_current_category->getTitlelink());
					} else {
						$pagetitle = gettext('News') . " - ";
						$desc = '';
						$canonicalurl = $host . getNewsIndexURL();
					}
				}
				break;
			case 'pages.php':
				$pagetitle = getBarePageTitle() . " - ";
				$date = getPageDate();
				$desc = trim(strip_tags(getPageContent()));
				$canonicalurl = $host . getPageLinkURL($_zp_current_zenpage_page->getTitlelink());
				break;
			default: // for all other possible static custom pages
				$custompage = stripSuffix($_zp_gallery_page);
				$standard = array('contact'	 => gettext('Contact'), 'register' => gettext('Register'), 'search'	 => gettext('Search'), 'archive'	 => gettext('Archive view'), 'password' => gettext('Password required'));
				if (class_exists('favorites')) {
					$standard[str_replace(_PAGE_ . '/', '', favorites::getFavorites_link())] = gettext('My favorites');
				}
				If (array_key_exists($custompage, $standard)) {
					$pagetitle = $standard[$custompage] . " - ";
				} else {
					$pagetitle = $custompage . " - ";
				}
				$desc = '';
				$canonicalurl = $host . getCustomPageURL($custompage);
				break;
		}
		// shorten desc to the allowed 200 characters if necesssary.
		$desc = strip_tags($desc);
		if (strlen($desc) > 200) {
			$desc = trim(substr($desc, 0, 200));
		}
		$desc = html_encode($desc);
		$pagetitle = $pagetitle . getBareGalleryTitle();
		// get master admin
		$admin = Zenphoto_Authority::getAnAdmin(array('`user`='	 => $_zp_authority->master_user, '`valid`=' => 1));
		$author = $admin->getName();
		$meta = '';
		if (getOption('htmlmeta_http-equiv-language')) {
			$meta .= '<meta http-equiv="language" content="' . $locale . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-language')) {
			$meta .= '<meta name="language" content="' . $locale . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-content-language')) {
			$meta .= '<meta name="content-language" content="' . $locale . '" />' . "\n";
		}
		if (getOption('htmlmeta_http-equiv-imagetoolbar')) {
			$meta .= '<meta http-equiv="imagetoolbar" content="false" />' . "\n";
		}
		if (getOption('htmlmeta_http-equiv-cache-control')) {
			$meta .= '<meta http-equiv="cache-control" content="' . getOption("htmlmeta_cache_control") . '" />' . "\n";
		}
		if (getOption('htmlmeta_http-equiv-pragma')) {
			$meta .= '<meta http-equiv="pragma" content="' . getOption("htmlmeta_pragma") . '" />' . "\n";
		}
		if (getOption('htmlmeta_http-equiv-content-style-type')) {
			$meta .= '<meta http-equiv="Content-Style-Type" content="text/css" />' . "\n";
		}
		if (getOption('htmlmeta_name-title')) {
			$meta .= '<meta name="title" content="' . html_encode($pagetitle) . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-keywords')) {
			$meta .= '<meta name="keywords" content="' . htmlmetatags::getMetaKeywords() . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-description')) {
			$meta .= '<meta name="description" content="' . $desc . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-page-topic')) {
			$meta .= '<meta name="page-topic" content="' . $desc . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-robots')) {
			$meta .= '<meta name="robots" content="' . getOption("htmlmeta_robots") . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-publisher')) {
			$meta .= '<meta name="publisher" content="' . FULLWEBPATH . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-creator')) {
			$meta .= '<meta name="creator" content="' . FULLWEBPATH . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-author')) {
			$meta .= '<meta name="author" content="' . $author . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-copyright')) {
			$meta .= '<meta name="copyright" content=" (c) ' . FULLWEBPATH . ' - ' . $author . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-rights')) {
			$meta .= '<meta name="rights" content="' . $author . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-revisit-after')) {
			$meta .= '<meta name="revisit-after" content="' . getOption("htmlmeta_revisit_after") . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-expires')) {
			$meta .= '<meta name="expires" content="' . getOption("htmlmeta_expires") . '" />' . "\n";
		}

		// DC meta data
		if (getOption('htmlmeta_name-DC-title')) {
			$meta .= '<meta name="DC.title" content="' . $pagetitle . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-DC-keywords')) {
			$meta .= '<meta name="DC.keywords" content="' . htmlmetatags::getMetaKeywords() . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-DC-description')) {
			$meta .= '<meta name="DC.description" content="' . $desc . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-DC-language')) {
			$meta .= '<meta name="DC.language" content="' . $locale . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-DC-subject')) {
			$meta .= '<meta name="DC.subject" content="' . $desc . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-DC-publisher')) {
			$meta .= '<meta name="DC.publisher" content="' . FULLWEBPATH . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-DC-creator')) {
			$meta .= '<meta name="DC.creator" content="' . FULLWEBPATH . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-DC-date')) {
			$meta .= '<meta name="DC.date" content="' . $date . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-DC-type')) {
			$meta .= '<meta name="DC.type" content="Text" /> <!-- ? -->' . "\n";
		}
		if (getOption('htmlmeta_name-DC-format')) {
			$meta .= '<meta name="DC.format" content="text/html" /><!-- What else? -->' . "\n";
		}
		if (getOption('htmlmeta_name-DC-identifier')) {
			$meta .= '<meta name="DC.identifier" content="' . FULLWEBPATH . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-DC-rights')) {
			$meta .= '<meta name="DC.rights" content="' . FULLWEBPATH . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-DC-source')) {
			$meta .= '<meta name="DC.source" content="' . $url . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-DC-relation')) {
			$meta .= '<meta name="DC.relation" content="' . FULLWEBPATH . '" />' . "\n";
		}
		if (getOption('htmlmeta_name-DC-Date.created')) {
			$meta .= '<meta name="DC.Date.created" content="' . $date . '" />' . "\n";
		}

		// OpenGraph meta
		if (getOption('htmlmeta_og-title')) {
			$meta .= '<meta property="og:title" content="' . $pagetitle . '" />' . "\n";
		}
		if (getOption('htmlmeta_og-image') && !empty($thumb)) {
			$meta .= '<meta property="og:image" content="' . $thumb . '" />' . "\n";
		}
		if (getOption('htmlmeta_og-description')) {
			$meta .= '<meta property="og:description" content="' . $desc . '" />' . "\n";
		}
		if (getOption('htmlmeta_og-url')) {
			$meta .= '<meta property="og:url" content="' . html_encode($url) . '" />' . "\n";
		}
		if (getOption('htmlmeta_og-type')) {
			$meta .= '<meta property="og:type" content="' . $type . '" />' . "\n";
		}

		// Social network extras
		if (getOption('htmlmeta_name-pinterest')) {
			$meta .= '<meta name="pinterest" content="nopin" />' . "\n";
		} // dissalow users to pin images on Pinterest
		// Canonical url
		if (getOption('htmlmeta_canonical-url')) {
			$meta .= '<link rel="canonical" href="' . $canonicalurl . '" />' . "\n";
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
									break;
								case 'album.php':
									$altlink .= '/' . html_encode($_zp_current_album->name);
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
									$altlink .= '/' . $_zp_conf_vars['special_pages']['archive']['rewrite'] . '/';
									break;
								case 'search.php':
									$altlink .= '/' . $_zp_conf_vars['special_pages']['search']['rewrite'] . '/';
									break;
								case 'contact.php':
									$altlink .= '/' . _PAGE_ . '/contact';
									break;
								default: // for all other possible none standard custom pages
									$altlink .= '/' . _PAGE_ . '/' . html_encode($pagetitle);
									break;
							} // switch
							$meta .= '<link rel="alternate" hreflang="' . $langcheck . '" href="' . $altlink . '" />' . "\n";
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
			$tags = array_keys(getAllTagsCount()); // get all if no specific item is set
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
