<?php
/**
 * This plugin handles <i>RSS</i> feeds:
 *
 * @author Stephen Billard (sbillard)
 * @package zpcore\plugins\rss
 */
// force UTF-8 Ø

$plugin_is_filter = 900 | FEATURE_PLUGIN | ADMIN_PLUGIN;
$plugin_description = gettext('The Zenphoto <em>RSS</em> handler.');
$plugin_notice = gettext('This plugin must be enabled to supply <em>RSS</em> feeds.') . '<br />' . gettext('<strong>Note:</strong> Theme support is required to display RSS links.');
$plugin_category = gettext('Feed');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'rss_options';

zp_register_filter('admin_utilities_buttons', 'RSS::overviewbutton');
zp_register_filter('show_change', 'RSS::clearCacheOnPublish');

$_zp_cached_feeds = array('RSS'); //    Add to this array any feed classes that need cache clearing

class rss_options {

	function __construct() {
		global $plugin_is_filter;
		if (OFFSET_PATH == 2) {
			setOptionDefault('zp_plugin_rss', $plugin_is_filter);
//	migrate old RSS options
			setOptionDefault('RSS_items', getOption('feed_items')); // options for standard images rss
			setOptionDefault('RSS_imagesize', getOption('feed_imagesize'));
			setOptionDefault('RSS_sortorder', getOption('feed_sortorder'));
			setOptionDefault('RSS_items_albums', getOption('feed_items_albums')); // options for albums rss
			setOptionDefault('RSS_imagesize_albums', getOption('feed_imagesize_albums'));
			setOptionDefault('RSS_sortorder_albums', getOption('feed_sortorder_albums'));
			setOptionDefault('RSS_enclosure', getOption('feed_enclosure'));
			setOptionDefault('RSS_mediarss', getOption('feed_mediarss'));
			setOptionDefault('RSS_cache', getOption('feed_cache'));
			setOptionDefault('RSS_cache_expire', getOption('feed_cache_expire'));
			setOptionDefault('RSS_hitcounter', getOption('feed_hitcounter'));
			setOptionDefault('RSS_title', getOption('feed_title'));
			setOptionDefault('RSS_truncate_length', getOption('zenpage_rss_length'));
			setOptionDefault('RSS_zenpage_items', getOption('zenpage_rss_items'));

			purgeOption('feed_items');
			purgeOption('feed_imagesize');
			purgeOption('feed_sortorder');
			purgeOption('feed_items_albums');
			purgeOption('feed_imagesize_albums');
			purgeOption('feed_sortorder_albums');
			purgeOption('feed_enclosure');
			purgeOption('feed_mediarss');
			purgeOption('feed_cache');
			purgeOption('feed_cache_expire');
			purgeOption('feed_hitcounter');
			purgeOption('feed_title');
			purgeOption('zenpage_rss_length');
			purgeOption('zenpage_rss_items');
			
			setOptionDefault('RSS_album_image', 1);
			setOptionDefault('RSS_comments', 1);
			setOptionDefault('RSS_articles', 1);
			setOptionDefault('RSS_pages', 1);
			setOptionDefault('RSS_article_comments', 1);
			setOptionDefault('RSS_truncate_length', '100');
			setOptionDefault('RSS_zenpage_items', '10');
			setOptionDefault('RSS_items', 10); // options for standard images rss
			setOptionDefault('RSS_imagesize', 240);
			setOptionDefault('RSS_sortorder', 'latest');
			setOptionDefault('RSS_items_albums', 10); // options for albums rss
			setOptionDefault('RSS_imagesize_albums', 240);
			setOptionDefault('RSS_sortorder_albums', 'latest');
			setOptionDefault('RSS_enclosure', '0');
			setOptionDefault('RSS_mediarss', '0');
			setOptionDefault('RSS_cache', '1');
			setOptionDefault('RSS_cache_expire', 86400);
			setOptionDefault('RSS_hitcounter', 1);
			setOptionDefault('RSS_title', 'both');

			setOptionDefault('RSS__cache_albums', 0);
			setOptionDefault('RSS__cache_images', 0);
			setOptionDefault('RSS__cache_news', 0);
			setOptionDefault('RSS__cache_pages', 0);
		}
	}

	function getOptionsSupported() {
		$options = array(
				gettext('RSS feeds enabled:') => array(
						'key' => 'RSS_feed_list',
						'type' => OPTION_TYPE_CHECKBOX_ARRAY,
						'order' => 0,
						'checkboxes' => array(gettext('Gallery') => 'RSS_album_image',
								gettext('Gallery Comments') => 'RSS_comments',
								gettext('All News') => 'RSS_articles',
								gettext('All Pages') => 'RSS_pages',
								gettext('News/Page Comments') => 'RSS_article_comments'
						),
						'desc' => gettext('Check each RSS feed you wish to activate.')),
				gettext('Image feed items:') => array(
						'key' => 'RSS_items',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext("The number of new images and comments you want to appear in your site’s RSS feed")),
				gettext('Album feed items:') => array(
						'key' => 'RSS_items_albums',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext("The number of new images and comments you want to appear in your site’s RSS feed")),
				gettext('Image size') => array(
						'key' => 'RSS_imagesize',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 3,
						'desc' => gettext('Size of RSS image feed images:')),
				gettext('Album image size') => array(
						'key' => 'RSS_imagesize_albums',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 4,
						'desc' => gettext('Size of RSS album feed images :')),
				gettext('Image feed sort order:') => array(
						'key' => 'RSS_sortorder',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 6,
						'selections' => array(gettext('latest by id') => 'latest',
								gettext('latest by date') => 'latest-date',
								gettext('latest by mtime') => 'latest-mtime',
								gettext('latest by publishdate') => 'latest-publishdate'
						),
						'desc' => gettext("Choose between latest by id for the latest uploaded, latest by date for the latest uploaded fetched by date, or latest by mtime for the latest uploaded fetched by the file’ last change timestamp.")),
				gettext('Album feed sort order:') => array(
						'key' => 'RSS_sortorder_albums',
						'type' => OPTION_TYPE_SELECTOR,
						'selections' => array(gettext('latest by id') => 'latest',
								gettext('latest by date') => 'latest-date',
								gettext('latest by mtime') => 'latest-mtime',
								gettext('latest by publishdate') => 'latest-publishdate',
								gettext('latest updated') => 'latestupdated'
						),
						'order' => 7,
						'desc' => gettext('Choose between latest by id for the latest uploaded and latest updated')),
				gettext('RSS enclosure:') => array(
						'key' => 'RSS_enclosure',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 8,
						'desc' => gettext('Check if you want to enable the RSS enclosure feature which provides a direct download for full images, movies etc. from within certain RSS reader clients (only Images RSS).')),
				gettext('Media RSS:') => array(
						'key' => 'RSS_mediarss',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 9,
						'desc' => gettext('Check if media RSS support is to be enabled. This support is used by some services and programs (only Images RSS).')),
				gettext('Cache') => array(
						'key' => 'RSS_cache',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 10,
						'desc' => sprintf(gettext('Check if you want to enable static RSS feed caching. The cached file will be placed within the <em>%s</em> folder.'), STATIC_CACHE_FOLDER)),
				gettext('Cache expiration') => array(
						'key' => 'RSS_cache_expire',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 11,
						'desc' => gettext('Cache expire default is 86400 seconds (1 day = 24 hrs * 60 min * 60 sec).')),
				gettext('Hitcounter') => array(
						'key' => 'RSS_hitcounter',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 12,
						'desc' => gettext('Check if you want to store the hitcount on RSS feeds.')),
				gettext('Title') => array(
						'key' => 'RSS_title',
						'type' => OPTION_TYPE_RADIO,
						'order' => 13,
						'buttons' => array(gettext('Gallery title') => 'gallery', gettext('Website title') => 'website', gettext('Both') => 'both'),
						'desc' => gettext("Select what you want to use as the main RSS feed (channel) title. “Both” means Website title followed by Gallery title")),
				gettext('Portable RSS link') => array(
						'key' => 'RSS_portable_link',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 14,
						'desc' => gettext('If checked links generated for logged‑in users will include a token identifying the user. Use of that link when not logged‑in will give the same feed as if the user were logged‑in.')),
				
		);
		if (extensionEnabled('zenpage')) {
			$options[gettext('Feed text length')] = array(
					'key' => 'RSS_truncate_length',
					'type' => OPTION_TYPE_TEXTBOX,
					'order' => 5.5,
					'desc' => gettext("The text length of the Zenpage RSS feed items. No value for full length."));
			$options[gettext('Zenpage feed items')] = array(
					'key' => 'RSS_zenpage_items',
					'type' => OPTION_TYPE_TEXTBOX,
					'order' => 5,
					'desc' => gettext("The number of news articles you want to appear in your site’s News RSS feed."));
		}
		$list = array(
				'<em>' . gettext('Albums') . '</em>' => 'RSS_cache_albums',
				'<em>' . gettext('Images') . '</em>' => 'RSS_cache_images');
		if (extensionEnabled('zenpage')) {
			$list['<em>' . gettext('News') . '</em>'] = 'RSS_cache_news';
			$list['<em>' . gettext('Pages') . '</em>'] = 'RSS_cache_pages';
		} else {
			setOption('RSS_cache_news', 0);
			setOption('RSS_cache_pages', 0);
		}
		$options[gettext('Purge cache files')] = array(
				'key' => 'RSS_cache_items',
				'type' => OPTION_TYPE_CHECKBOX_ARRAY,
				'order' => 0,
				'checkboxes' => $list,
				'desc' => gettext('If a <em>type</em> is checked, the RSS caches for the item will be purged when the published state of an item of <em>type</em> changes.') .
				'<div class="notebox">' . gettext('<strong>NOTE:</strong> The entire cache is cleared since there is no way to ascertain if a gallery page contains dependencies on the item.') . '</div>');

		return $options;
	}

	function handleOption($option, $currentValue) {
		
	}

	function handleOptionSave() {
		if (isset($_POST['saverssoptions'])) {
			setOption('RSS_items', sanitize($_POST['RSS_items'], 3));
			setOption('RSS_imagesize', sanitize($_POST['RSS_imagesize'], 3));
			setOption('RSS_sortorder', sanitize($_POST['RSS_sortorder'], 3));
			setOption('RSS_items_albums', sanitize($_POST['RSS_items_albums'], 3));
			setOption('RSS_imagesize_albums', sanitize($_POST['RSS_imagesize_albums'], 3));
			setOption('RSS_sortorder_albums', sanitize($_POST['RSS_sortorder_albums'], 3));
			setOption('RSS_title', sanitize($_POST['RSS_title'], 3));
			setOption('RSS_cache_expire', sanitize($_POST['RSS_cache_expire'], 3));
			setOption('RSS_enclosure', (int) isset($_POST['RSS_enclosure']));
			setOption('RSS_mediarss', (int) isset($_POST['RSS_mediarss']));
			setOption('RSS_cache', (int) isset($_POST['RSS_cache']));
			setOption('RSS_album_image', (int) isset($_POST['RSS_album_image']));
			setOption('RSS_comments', (int) isset($_POST['RSS_comments']));
			setOption('RSS_articles', (int) isset($_POST['RSS_articles']));
			setOption('RSS_pages', (int) isset($_POST['RSS_pages']));
			setOption('RSS_article_comments', (int) isset($_POST['RSS_article_comments']));
			setOption('RSS_hitcounter', (int) isset($_POST['RSS_hitcounter']));
			setOption('RSS_portable_link', (int) isset($_POST['RSS_portable_link']));
			$returntab = "&tab=rss";
		}
	}

}

/**
 * Prints a RSS link for if (class_exists('RSS')) printRSSLink() and if (class_exists('RSS')) printRSSHeaderLink()
 *
 * @param string $option type of RSS: "Gallery" feed for latest images of the whole gallery
 * 																		"Album" for latest images only of the album it is called from
 * 																		"Collection" for latest images of the album it is called from and all of its subalbums
 * 																		"Comments-gallery" for all comments of all albums and images
 * 																		"Comments-image" for latest comments of only the image it is called from
 * 																		"Comments-album" for latest comments of only the album it is called from
 * 																		"AlbumsRSS" for latest albums
 * 																		"AlbumsRSScollection" only for latest subalbums with the album it is called from
 * 															or
 * 																		"News" feed for all news articles
 * 																		"Category" for only the news articles of the category that is currently selected
 * 																		"NewsWithImages" for all news articles and latest images
 * 																		"Comments-zenpage" for all comments of all news articles and pages
 * 																		"Comments-news" for comments of only the news article it is called from
 * 																		"Comments-page" for comments of only the page it is called from
 * 																		"Comments-all" for comments from all albums, images, news articels and pages
 * 																		"Pages" feed for all pages
 * @param string $lang optional to display a feed link for a specific language. Enter the locale like "de_DE" (the locale must be installed on your Zenphoto to work of course). If empty the locale set in the admin option or the language selector (getOption('locale') is used.
 * @param string $addl provided additional data for feeds (e.g. album object for album feeds, $categorylink for zenpage categories
 */
function getRSSLink($option, $lang = NULL, $addl = NULL) {
	global $_zp_current_album, $_zp_current_image, $_zp_current_admin_obj, $_zp_current_category;
	if (empty($lang)) {
		$lang = getLanguageText(getOption('locale'));
	}
	$link = NULL;
	switch (strtolower($option)) {
		case 'gallery':
			if (getOption('RSS_album_image')) {
				$link = array('rss' => 'gallery');
			}
			break;
		case 'album':
			if (getOption('RSS_album_image')) {
				if (is_object($addl)) {
					$album = $addl;
				} else {
					$album = $_zp_current_album;
				}
				$link = array('rss' => 'gallery', 'albumname' => $album->getName());
				break;
			}
		case 'collection':
			if (getOption('RSS_album_image')) {
				if (is_object($addl)) {
					$album = $addl;
				} else {
					$album = $_zp_current_album;
				}
				$link = array('rss' => 'gallery', 'folder' => $album->getName());
			}
			break;
		case 'comments-gallery':
			if (getOption('RSS_comments')) {
				$link = array('rss' => 'comments', 'type' => 'gallery');
			}
			break;
		case 'comments-image':
			if (getOption('RSS_comments')) {
				$link = array('rss' => 'comments', 'id' => (string) $_zp_current_image->getID(), 'type' => 'image');
			}
			break;
		case 'comments-album':
			if (getOption('RSS_comments')) {
				$link = array('rss' => 'comments', 'id' => (string) $_zp_current_album->getID(), 'type' => 'album');
			}
			break;
		case 'albumsrss':
			if (getOption('RSS_album_image')) {
				$link = array('rss' => 'gallery', 'albumsmode' => '');
			}
			break;
		case 'albumsrsscollection':
			if (getOption('RSS_album_image')) {
				$link = array('rss' => 'gallery', 'folder' => $_zp_current_album->getName(), 'albumsmode' => '');
			}
			break;
		case 'pages':
			if (getOption('RSS_pages')) {
				$link = array('rss' => 'pages');
			}
			break;
		case 'news':
			if (getOption('RSS_articles')) {
				$link = array('rss' => 'news');
			}
			break;
		case 'category':
			if (getOption('RSS_articles')) {
				if (empty($addl) && !is_null($_zp_current_category)) {
					$addl = $_zp_current_category->getName();
				}
				if (empty($addl)) {
					$link = array('rss' => 'news');
				} else {
					$link = array('rss' => 'news', 'category' => $addl);
				}
			}
			break;
		case 'newswithimages':
			if (getOption('RSS_articles')) {
				$link = array('rss' => 'news', 'withimages' => '');
			}
			break;
		case 'comments-zenpage':
			if (getOption('RSS_article_comments')) {
				$link = array('rss' => 'comments', 'type' => 'zenpage');
			}
			break;
		case 'comments-news':
			if (getOption('RSS_article_comments')) {
				$link = array('rss' => 'comments', 'id' => (string) getNewsID(), 'type' => 'news');
			}
			break;
		case 'comments-page':
			if (getOption('RSS_article_comments')) {
				$link = array('rss' => 'comments', 'id' => (string) getPageID(), 'type' => 'page');
			}
			break;
		case 'comments-all':
			if (getOption('RSS_article_comments') && getOption('RSS_comments')) {
				$link = array('rss' => 'comments', 'type' => 'allcomments');
			}
			break;
	}
	if (is_array($link)) {
		$link['lang'] = $lang;
		if (zp_loggedin() && getOption('RSS_portable_link')) {
			$link['user'] = (string) $_zp_current_admin_obj->getID();
			$link['token'] = RSS::generateToken($link);
		}
		$uri = WEBPATH . '/index.php?' . str_replace('=&', '&', http_build_query($link));
		return $uri;
	}
	return NULL;
}

/**
 * Prints an RSS link
 *
 * @param string $option type of RSS: See getRSSLink for details
 * @param string $prev text to before before the link
 * @param string $linktext title of the link
 * @param string $next text to appear after the link
 * @param bool $printIcon print an RSS icon beside it? if true, the icon is zp-core/images/rss.png
 * @param string $class css class
 * @param string $lang optional to display a feed link for a specific language. Enter the locale like "de_DE" (the locale must be installed on your Zenphoto to work of course). If empty the locale set in the admin option or the language selector (getOption('locale') is used.
 * @param string $addl provided additional data for feeds (e.g. album object for album feeds, $categorylink for zenpage categories
 */
function printRSSLink($option, $prev, $linktext, $next, $printIcon = true, $class = null, $lang = '', $addl = NULL) {
	if ($printIcon) {
		$icon = ' <img src="' . FULLWEBPATH . '/' . ZENFOLDER . '/images/rss.png" alt="RSS Feed" />';
	} else {
		$icon = '';
	}
	if (!is_null($class)) {
		$class = 'class="' . $class . '"';
	}
	if (empty($lang)) {
		$lang = getLanguageText(getOption("locale"));
	}
	echo $prev . "<a $class href=\"" . html_encode(getRSSLink($option, $lang, $addl)) . "\" title=\"" . html_encode($linktext) . "\" rel=\"nofollow\">" . $linktext . "$icon</a>" . $next;
}

/**
 * Prints the RSS link for use in the HTML HEAD
 *
 * @param string $option type of RSS: "Gallery" feed for latest images of the whole gallery
 * 																		"Album" for latest images only of the album it is called from
 * 																		"Collection" for latest images of the album it is called from and all of its subalbums
 * 																		"Comments" for all comments of all albums and images
 * 																		"Comments-image" for latest comments of only the image it is called from
 * 																		"Comments-album" for latest comments of only the album it is called from
 * 																		"AlbumsRSS" for latest albums
 * 																		"AlbumsRSScollection" only for latest subalbums with the album it is called from
 * @param string $linktext title of the link
 * @param string $lang optional to display a feed link for a specific language. Enter the locale like "de_DE" (the locale must be installed on your Zenphoto to work of course). If empty the locale set in the admin option or the language selector (getOption('locale') is used.
 * @param string $addl provided additional data for feeds (e.g. album object for album feeds, $categorylink for zenpage categories
 *
 */
function printRSSHeaderLink($option, $linktext, $lang = '', $addl = NULL) {
	echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode(getBare($linktext)) . "\" href=\"" .
	PROTOCOL . '://' . html_encode($_SERVER["HTTP_HOST"]) . html_encode(getRSSLink($option, $lang, $addl)) . "\" />\n";
}

require_once(SERVERPATH . '/' . ZENFOLDER . '/classes/class-feed.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/classes/class-mimetypes.php');

class RSS extends feed {

	protected $feed = 'RSS';
	public $feeditems = array();

	/**
	 * Creates a feed object from the URL parameters fetched only
	 *
	 */
	function __construct($options = NULL) {
		global $_zp_gallery, $_zp_current_admin_obj, $_zp_loggedin;
		if (empty($options))
			self::feed404();

		$this->feedtype = $options['rss'];
		parent::__construct($options);

		if (isset($options['token'])) {
//	The link camed from a logged in user, see if it is valid
			$link = $options;
			unset($link['token']);
			$token = RSS::generateToken($link);
			if ($token == $options['token']) {
				$adminobj = Authority::getAnAdmin(array('`id`=' => (int) $link['user']));
				if ($adminobj) {
					$_zp_current_admin_obj = $adminobj;
					$_zp_loggedin = $_zp_current_admin_obj->getRights();
				}
			}
		}
// general feed setup
		$channeltitlemode = getOption('RSS_title');

//channeltitle general
		switch ($channeltitlemode) {
			case 'gallery':
				$this->channel_title = $_zp_gallery->getBareTitle($this->locale);
				break;
			case 'website':
				$this->channel_title = getBare($_zp_gallery->getParentSiteTitle($this->locale));
				break;
			case 'both':
				$website_title = $_zp_gallery->getParentSiteTitle($this->locale);
				$this->channel_title = $_zp_gallery->getBareTitle($this->locale);
				if (!empty($website_title)) {
					$this->channel_title = $website_title . ' - ' . $this->channel_title;
				}
				break;
		}

// individual feedtype setup
		switch ($this->feedtype) {
			case 'gallery':
				if (!getOption('RSS_album_image')) {
					self::feed404();
				}
				$albumname = $this->getChannelTitleExtra();
				if ($this->albumfolder) {
					$alb = AlbumBase::newAlbum($this->albumfolder, true, true);
					if ($alb->exists) {
						$albumtitle = $alb->getTitle();
						$albumname = ' - ' . html_encode($albumtitle) . $this->getChannelTitleExtra();
					} else {
						self::feed404();
					}
				} else {
					$albumtitle = '';
				}
				$this->channel_title = html_encode($this->channel_title . ' ' . getBare($albumname));
				require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/image_album_statistics.php');
				break;

			case 'news': //Zenpage News RSS
				if (!getOption('RSS_articles')) {
					self::feed404();
				}
				$titleappendix = gettext(' (Latest news)');

				switch ($this->newsoption) {
					case 'withalbums':
					case 'withalbums_mtime':
					case 'withalbums_publishdate':
					case 'withalbums_latestupdated':
						$titleappendix = gettext(' (Latest news and albums)');
						break;
					case 'withimages':
					case 'withimages_mtime':
					case 'withimages_publishdate':
						$titleappendix = gettext(' (Latest news and images)');
						break;
					default:
						switch ($this->sortorder) {
							case 'popular':
								$titleappendix = gettext(' (Most popular news)');
								break;
							case 'mostrated':
								$titleappendix = gettext(' (Most rated news)');
								break;
							case 'toprated':
								$titleappendix = gettext(' (Top rated news)');
								break;
							case 'random':
								$titleappendix = gettext(' (Random news)');
								break;
						}
						break;
				}
				$cattitle = "";
				if ($this->cattitle) {
					$cattitle = " - " . $this->cattitle;
				}
				$this->channel_title = html_encode($this->channel_title . $cattitle . $titleappendix);
				$this->itemnumber = getOption("RSS_zenpage_items"); // # of Items displayed on the feed
				require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/image_album_statistics.php');
				require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/zenpage-template-functions.php');

				break;
			case 'pages': //Zenpage News RSS
				if (!getOption('RSS_pages')) {
					self::feed404();
				}
				switch ($this->sortorder) {
					case 'popular':
						$titleappendix = gettext(' (Most popular pages)');
						break;
					case 'mostrated':
						$titleappendix = gettext(' (Most rated pages)');
						break;
					case 'toprated':
						$titleappendix = gettext(' (Top rated pages)');
						break;
					case 'random':
						$titleappendix = gettext(' (Random pages)');
						break;
					default:
						$titleappendix = gettext(' (Latest pages)');
						break;
				}
				$this->channel_title = html_encode($this->channel_title . $titleappendix);
				require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/zenpage-template-functions.php');
				break;

			case 'comments': //Comments RSS
				if (!getOption('RSS_comments')) {
					self::feed404();
				}
				if ($this->id) {
					switch ($this->commentfeedtype) {
						case 'album':
							$table = 'albums';
							break;
						case 'image':
							$table = 'images';
							break;
						case 'news':
							$table = 'news';
							break;
						case 'page':
							$table = 'pages';
							break;
						default:
							self::feed404();
							break;
					}
					$this->itemobj = getItemByID($table, $this->id);
					if ($this->itemobj) {
						$title = ' - ' . $this->itemobj->getTitle();
					} else {
						self::feed404();
					}
				} else {
					$this->itemobj = NULL;
					$title = NULL;
				}
				$this->channel_title = html_encode($this->channel_title . $title . gettext(' (latest comments)'));
				if (extensionEnabled('zenpage')) {
					require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/zenpage-template-functions.php');
				}
				break;
			case 'null': //we just want the class instantiated
				return;
		}
		$this->feeditems = $this->getitems();
	}

	/**
	 * Updates the hitcoutner for RSS in the plugin_storage db table.
	 *
	 */
	protected function hitcounter() {
		global $_zp_db;
		if (!zp_loggedin() && getOption('RSS_hitcounter')) {
			$rssuri = $this->getCacheFilename();
			$type = 'rsshitcounter';
			$checkitem = $_zp_db->querySingleRow("SELECT `data` FROM " . $_zp_db->prefix('plugin_storage') . " WHERE `aux` = " . $_zp_db->quote($rssuri) . " AND `type` = '" . $type . "'", true);
			if ($checkitem) {
				$hitcount = $checkitem['data'] + 1;
				$_zp_db->query("UPDATE " . $_zp_db->prefix('plugin_storage') . " SET `data` = " . $hitcount . " WHERE `aux` = " . $_zp_db->quote($rssuri) . " AND `type` = '" . $type . "'", true);
			} else {
				$_zp_db->query("INSERT INTO " . $_zp_db->prefix('plugin_storage') . " (`type`,`aux`,`data`) VALUES ('" . $type . "'," . $_zp_db->quote($rssuri) . ",1)", true);
			}
		}
	}
	
	/**
	 * Generates the token based on the RSS link passed for pprtable RSS usage
	 * 
	 * @param string $link
	 * @return string
	 */
	static function generateToken($link) {
		return Authority::passwordHash(serialize($link), '');
	}

	/**
	 * Gets the feed item data in a gallery feed
	 *
	 * @param object $item Object of an image or album
	 * @return array
	 */
	protected function getItemGallery($item) {
		global $_zp_db;
		if ($this->mode == "albums") {
			$albumobj = $item;
			$totalimages = $albumobj->getNumImages();
			$itemlink = $albumobj->getLink(1, FULLWEBPATH);
			$thumb = $albumobj->getAlbumThumbImage();
			$thumburl = '<img border="0" src="' . SERVER_HTTP_HOST . html_encode(pathurlencode($thumb->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . '" alt="' . html_encode($albumobj->getTitle($this->locale)) . '" />';
			$title = $albumobj->getTitle($this->locale);
			if (true || $this->sortorder == "latestupdated") {
				$filechangedate = filectime(ALBUM_FOLDER_SERVERPATH . internalToFilesystem($albumobj->name));
				$latestimage = $_zp_db->querySingleRow("SELECT mtime FROM " . $_zp_db->prefix('images') . " WHERE albumid = " . $albumobj->getID() . " AND `show` = 1 ORDER BY id DESC");
				if ($latestimage && $this->sortorder == 'latestupdated') {
					$count = $_zp_db->count('images', "WHERE albumid = " . $albumobj->getID() . " AND mtime = " . $latestimage['mtime']);
				} else {
					$count = $totalimages;
				}
				if ($count != 0) {
					$imagenumber = sprintf(ngettext('%s (%u image)', '%s (%u images)', $count), $title, $count);
				} else {
					$imagenumber = $title;
				}
				$feeditem['desc'] = '<a title="' . $title . '" href="' . $itemlink . '">' . $thumburl . '</a>' .
								'<p>' . html_encode($imagenumber) . '</p>' . $albumobj->getDesc($this->locale) . '<br />' . sprintf(gettext("Last update: %s"), zpFormattedDate(DATETIME_DISPLAYFORMAT, $filechangedate));
			} else {
				if ($totalimages != 0) {
					$imagenumber = sprintf(ngettext('%s (%u image)', '%s (%u images)', $totalimages), $title, $totalimages);
				}
				$feeditem['desc'] = '<a title="' . html_encode($title) . '" href="' . $itemlink . '">' . $thumburl . '</a>' . $item->getDesc($this->locale) . '<br />' . sprintf(gettext("Date: %s"), zpFormattedDate(DATETIME_DISPLAYFORMAT, $item->get('mtime')));
			}
			$ext = getSuffix($thumb->localpath);
		} else {
			$ext = getSuffix($item->localpath);
			$albumobj = $item->getAlbum();
			$itemlink = $item->getLink(FULLWEBPATH);
			$fullimagelink = html_encode(pathurlencode($item->getFullImageURL(FULLWEBPATH)));
			$thumburl = '<img border="0" src="' . SERVER_HTTP_HOST . pathurlencode($item->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE)) . '" alt="' . $item->getTitle($this->locale) . '" /><br />';
			$title = $item->getTitle($this->locale);
			$datecontent = '<br />Date: ' . zpFormattedDate(DATETIME_DISPLAYFORMAT, $item->get('mtime'));
			if (in_array($ext, array('mp3', 'm4a', 'm4v', 'mp4')) AND $this->mode != "album") {
				$feeditem['desc'] = '<a title="' . html_encode($title) . ' in ' . html_encode($albumobj->getTitle($this->locale)) . '" href="' . $itemlink . '">' . $thumburl . '</a>' . $item->getDesc($this->locale) . $datecontent;
			} else {
				$feeditem['desc'] = '<a title="' . html_encode($title) . ' in ' . html_encode($albumobj->getTitle($this->locale)) . '" href="' . $itemlink . '"><img src="' . SERVER_HTTP_HOST . html_encode(pathurlencode($item->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . '" alt="' . html_encode($title) . '" /></a>' . $item->getDesc($this->locale) . $datecontent;
			}
		}
// title
		if ($this->mode != "albums") {
			$feeditem['title'] = sprintf('%1$s (%2$s)', $item->getTitle($this->locale), $albumobj->getTitle($this->locale));
		} else {
			$feeditem['title'] = $imagenumber;
		}
//link
		$feeditem['link'] = $itemlink;

// enclosure
		$feeditem['enclosure'] = '';
		if (getOption("RSS_enclosure") AND $this->mode != "albums") {
			$feeditem['enclosure'] = '<enclosure url="' . $fullimagelink . '" type="' . mimeTypes::getType($ext) . '" length="' . filesize($item->localpath) . '" />';
		}
//category
		if ($this->mode != "albums") {
			$feeditem['category'] = html_encode($albumobj->getTitle($this->locale));
		} else {
			$feeditem['category'] = html_encode($albumobj->getTitle($this->locale));
		}
//media content
		$feeditem['media_content'] = '';
		$feeditem['media_thumbnail'] = '';
		if (getOption("RSS_mediarss") AND $this->mode != "albums") {
			$feeditem['media_content'] = '<media:content url="' . $fullimagelink . '" type="image/jpeg" />';
			$feeditem['media_thumbnail'] = '<media:thumbnail url="' . $fullimagelink . '" width="' . $this->imagesize . '"	height="' . $this->imagesize . '" />';
		}
//date
		if ($this->mode != "albums") {
			$feeditem['pubdate'] = date("r", strtotime($item->getDateTime()));
		} else {
			$feeditem['pubdate'] = date("r", strtotime($albumobj->getDateTime()));
		}
		if ($this->mode == "albums") {
			return zp_apply_filter('feed_album', $feeditem, $item);
		} else {
			return zp_apply_filter('feed_image', $feeditem, $item);
		}
	}

	/**
	 * Gets the feed item data in a Zenpage news feed
	 *
	 * @param array $item Titlelink a Zenpage article or filename of an image if a combined feed
	 * @return array
	 */
	protected function getItemNews($item) {
		$categories = '';
		$feeditem['enclosure'] = '';
		$obj = new ZenpageNews($item['titlelink']);
		$title = $feeditem['title'] = get_language_string($obj->getTitle('all'), $this->locale);
		$link = $obj->getLink(FULLWEBPATH);
		$plaincategories = $obj->getCategories();
		$categories = '';
		foreach ($plaincategories as $cat) {
			$catobj = new ZenpageCategory($cat['titlelink']);
			$categories .= get_language_string($catobj->getTitle('all'), $this->locale) . ', ';
		}
		$categories = rtrim($categories, ', ');
		$feeditem['desc'] = shortenContent($obj->getContent($this->locale), getOption('RSS_truncate_length'), '...');

		if (!empty($categories)) {
			$feeditem['category'] = html_encode($categories);
			$feeditem['title'] = $title . ' (' . $categories . ')';
		}
		$feeditem['link'] = $link;
		$feeditem['media_content'] = '';
		$feeditem['media_thumbnail'] = '';
		$feeditem['pubdate'] = date("r", strtotime($item['date']));
		return zp_apply_filter('feed_news', $feeditem, $obj);
	}

	/**
	 * Prints the RSS feed xml
	 *
	 */
	public function printFeed() {
		global $_zp_gallery;
		$feeditems = $this->getitems();
		//NOTE: feeditems are complete HTML so necessarily must have been properly endoded by the server function!

		header('Content-Type: application/xml');
		$this->hitcounter();
		$this->startCache();
		echo '<?xml-stylesheet type="text/css" href="' . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/rss/rss.css" ?>' . "\n";
		?>
		<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
			<channel>
				<title><![CDATA[<?php echo $this->channel_title; ?>]]></title>
				<link><?php echo FULLWEBPATH; ?></link>
				<atom:link href="<?php echo FULLWEBPATH ?><?php echo html_encode(getRequestURI()); ?>" rel="self"	type="application/rss+xml" />
				<description><![CDATA[<?php echo getBare($_zp_gallery->getDesc($this->locale)); ?>]]></description>
				<language><?php echo $this->locale_xml; ?></language>
				<pubDate><?php echo date("r", time()); ?></pubDate>
				<lastBuildDate><?php echo date("r", time()); ?></lastBuildDate>
				<docs>http://blogs.law.harvard.edu/tech/rss</docs>
				<generator>Zenphoto RSS Generator</generator>
				
				<?php
				if (is_array($feeditems) && !empty($feeditems)) {
					foreach ($feeditems as $feeditem) {
						switch ($this->feedtype) {
							case 'gallery':
								$item = $this->getItemGallery($feeditem);
								break;
							case 'news':
								$item = $this->getItemNews($feeditem);
								break;
							case 'pages':
								$item = $this->getitemPages($feeditem, getOption('RSS_truncate_length'));
								break;
							case 'comments':
								$item = $this->getitemComments($feeditem);
								break;
							default:
								$item = $feeditem;
								break;
						}
						?>
						<item>
							<title><![CDATA[<?php echo $item['title']; ?>]]></title>
							<link><?php echo html_encode($item['link']); ?></link>
							<description><![CDATA[<?php echo $item['desc']; ?>]]></description>
							<?php
							if (!empty($item['enclosure'])) {
								echo $item['enclosure']; //prints xml as well
							}
							if (!empty($item['category'])) {
								?>
								<category><![CDATA[<?php echo $item['category']; ?>]]></category>
								<?php
							}
							if (!empty($item['media_content'])) {
								echo $item['media_content']; //prints xml as well
							}
							if (!empty($item['media_thumbnail'])) {
								echo $item['media_thumbnail']; //prints xml as well
							}
							?>
							<guid><?php echo html_encode($item['link']); ?></guid>
							<pubDate><?php echo html_encode($item['pubdate']); ?></pubDate>
						</item>
						<?php
					} // foreach
				} else {
					?>
					<item>
						<title><![CDATA[<?php echo gettext('No items available.'); ?>]]></title>
					</item>
					<?php
				}
				?>
			</channel>
		</rss>
		<?php
		$this->endCache();
	}
	
	/**
	 * Adds the utility  button for cache clearing
	 * 
	 * @since 1.6.1 moved from cacheManager
	 * 
	 * @param array $buttons
	 * @return string
	 */
	static function overviewbutton($buttons) {
		$buttons[] = array(
				'XSRFTag' => 'clear_cache',
				'category' => gettext('Cache'),
				'enable' => true,
				'button_text' => gettext('Purge RSS cache'),
				'formname' => 'purge_rss_cache',
				'action' => FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=clear_rss_cache',
				'icon' => 'images/edit-delete.png',
				'alt' => '',
				'title' => gettext('Delete all files from the RSS cache'),
				'hidden' => '<input type="hidden" name="action" value="clear_rss_cache" />',
				'rights' => ADMIN_RIGHTS
		);
		return $buttons;
	}
		/**
	 *
	 * Clears the RSS cache for items if published and this is enabled on the options
	 * 
	 * @since 1.6.1 former published() method moved from cacheManager
	 * 
	 * @param object $obj
	 */
	static function clearCacheOnPublish($obj) {
		global $_zp_cached_feeds;
		if (getOption('RSS_cache' . $obj->table)) {
			foreach ($_zp_cached_feeds as $feed) {
				$feeder = new Feed($feed);
				$feeder->clearCache();
			}
		}
		return $obj;
	}

}

function executeRSS() {
	global $_zp_gallery_page;
	if (!$_GET['rss']) {
		$_GET['rss'] = 'gallery';
	}
	$_zp_gallery_page = 'rss.php';
	$rss = new RSS(sanitize($_GET));
	$rss->printFeed();
	exitZP();
}

// RSS feed calls before anything else
if (!OFFSET_PATH && isset($_GET['rss'])) {
	zp_register_filter('load_theme_script', 'executeRSS', 9999);
}
