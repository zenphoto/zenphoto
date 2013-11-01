<?php
/**
 * This plugin handles <i>RSS</i> feeds:
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage feed
 */
// force UTF-8 Ø

$plugin_is_filter = 9 | FEATURE_PLUGIN | ADMIN_PLUGIN;
$plugin_description = gettext('The Zenphoto <em>RSS</em> handler.');
$plugin_notice = gettext('This plugin must be enabled to supply <em>RSS</em> feeds.') . '<br />' . gettext('<strong>Note:</strong> Theme support is required to display RSS links.');

$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'rss_options';

class rss_options {

	function __construct() {
		if (OFFSET_PATH == 2) {
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
		}
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
	}

	function getOptionsSupported() {
		$options = array(gettext('RSS feeds enabled:')			 => array('key'				 => 'RSS_feed_list', 'type'			 => OPTION_TYPE_CHECKBOX_ARRAY,
										'order'			 => 0,
										'checkboxes' => array(gettext('Gallery')						 => 'RSS_album_image',
														gettext('Comments')						 => 'RSS_comments',
														gettext('All News')						 => 'RSS_articles',
														gettext('All Pages')					 => 'RSS_pages',
														gettext('News/Page Comments')	 => 'RSS_article_comments'
										),
										'desc'			 => gettext('Check each RSS feed you wish to activate.')),
						gettext('Image feed items:')			 => array('key'		 => 'RSS_items', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1,
										'desc'	 => gettext("The number of new images and comments you want to appear in your site's RSS feed")),
						gettext('Album feed items:')			 => array('key'		 => 'RSS_items_albums', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext("The number of new images and comments you want to appear in your site's RSS feed")),
						gettext('Image size')							 => array('key'		 => 'RSS_imagesize', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 3,
										'desc'	 => gettext('Size of RSS image feed images:')),
						gettext('Album image size')				 => array('key'		 => 'RSS_imagesize_albums', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 4,
										'desc'	 => gettext('Size of RSS album feed images :')),
						gettext('Image feed sort order:')	 => array('key'				 => 'RSS_sortorder', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 6,
										'selections' => array(gettext('latest by id')					 => 'latest',
														gettext('latest by date')				 => 'latest-date',
														gettext('latest by mtime')			 => 'latest-mtime',
														gettext('latest by publishdate') => 'latest-publishdate'
										),
										'desc'			 => gettext("Choose between latest by id for the latest uploaded, latest by date for the latest uploaded fetched by date, or latest by mtime for the latest uploaded fetched by the file's last change timestamp.")),
						gettext('Album feed sort order:')	 => array('key'				 => 'RSS_sortorder_albums', 'type'			 => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('latest by id')					 => 'latest',
														gettext('latest by date')				 => 'latest-date',
														gettext('latest by mtime')			 => 'latest-mtime',
														gettext('latest by publishdate') => 'latest-publishdate',
														gettext('latest updated')				 => 'latestupdated'
										),
										'order'			 => 7,
										'desc'			 => gettext('Choose between latest by id for the latest uploaded and latest updated')),
						gettext('RSS enclosure:')					 => array('key'		 => 'RSS_enclosure', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 8,
										'desc'	 => gettext('Check if you want to enable the RSS enclosure feature which provides a direct download for full images, movies etc. from within certain RSS reader clients (only Images RSS).')),
						gettext('Media RSS:')							 => array('key'		 => 'RSS_mediarss', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 9,
										'desc'	 => gettext('Check if media RSS support is to be enabled. This support is used by some services and programs (only Images RSS).')),
						gettext('Cache')									 => array('key'		 => 'RSS_cache', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 10,
										'desc'	 => gettext('Check if you want to enable static RSS feed caching. The cached file will be placed within the cache_html folder.')),
						gettext('Cache expiration')				 => array('key'		 => 'RSS_cache_expire', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 11,
										'desc'	 => gettext('Cache expire default is 86400 seconds (1 day = 24 hrs * 60 min * 60 sec).')),
						gettext('Hitcounter')							 => array('key'		 => 'RSS_hitcounter', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 12,
										'desc'	 => gettext('Check if you want to store the hitcount on RSS feeds.')),
						gettext('Title')									 => array('key'			 => 'RSS_title', 'type'		 => OPTION_TYPE_RADIO,
										'order'		 => 13,
										'buttons'	 => array(gettext('Gallery title') => 'gallery', gettext('Website title') => 'website', gettext('Both') => 'both'),
										'desc'		 => gettext("Select what you want to use as the main RSS feed (channel) title. 'Both' means Website title followed by Gallery title")),
						gettext('Portable RSS link')			 => array('key'		 => 'RSS_portable_link', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 14,
										'desc'	 => gettext('If checked links generated for logged‑in users will include a token identifying the user. Use of that link when not logged‑in will give the same feed as if the user were logged‑in.'))
		);
		if (extensionEnabled('zenpage')) {
			$options[gettext('Feed text length')] = array('key'		 => 'RSS_truncate_length', 'type'	 => OPTION_TYPE_TEXTBOX,
							'order'	 => 5.5,
							'desc'	 => gettext("The text length of the Zenpage RSS feed items. No value for full length."));
			$options[gettext('Zenpage feed items')] = array('key'		 => 'RSS_zenpage_items', 'type'	 => OPTION_TYPE_TEXTBOX,
							'order'	 => 5,
							'desc'	 => gettext("The number of news articles you want to appear in your site's News RSS feed."));
		}
		return $options;
	}

	function handleOption($option, $currentValue) {

	}

}

/**
 * Prints a RSS link for if (class_exists('RSS')) printRSSLink() and if (class_exists('RSS')) printRSSHeaderLink()
 *
 * @param string $option type of RSS: "Gallery" feed for latest images of the whole gallery
 * 																		"Album" for latest images only of the album it is called from
 * 																		"Collection" for latest images of the album it is called from and all of its subalbums
 * 																		"Comments" for all comments of all albums and images
 * 																		"Comments-image" for latest comments of only the image it is called from
 * 																		"Comments-album" for latest comments of only the album it is called from
 * 																		"AlbumsRSS" for latest albums
 * 																		"AlbumsRSScollection" only for latest subalbums with the album it is called from
 * 															or
 * 																		"News" feed for all news articles
 * 																		"Category" for only the news articles of the category that is currently selected
 * 																		"NewsWithImages" for all news articles and latest images
 * 																		"Comments" for all news articles and pages
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
		$lang = zpFunctions::getLanguageText(getOption('locale'));
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
				$link = array('rss' => 'gallery', 'albumname' => $album->getFolder());
				break;
			}
		case 'collection':
			if (getOption('RSS_album_image')) {
				if (is_object($addl)) {
					$album = $addl;
				} else {
					$album = $_zp_current_album;
				}
				$link = array('rss' => 'gallery', 'folder' => $album->getFolder());
			}
			break;
		case 'comments':
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
				$link = array('rss' => 'gallery', 'folder' => $_zp_current_album->getFolder(), 'albumsmode' => '');
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
					$addl = $_zp_current_category->getTitlelink();
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
		case 'comments':
			if (getOption('RSS_article_comments')) {
				$link = array('comments' => 1, 'type' => 'zenpage');
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
			if (getOption('RSS_article_comments')) {
				$link = array('rss' => 'comments', 'type' => 'allcomments');
			}
			break;
	}
	if (is_array($link)) {
		$link['lang'] = $lang;
		if (zp_loggedin() && getOption('RSS_portable_link')) {
			$link['user'] = (string) $_zp_current_admin_obj->getID();
			$link['token'] = Zenphoto_Authority::passwordHash(serialize($link), '');
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
		$lang = zpFunctions::getLanguageText(getOption("locale"));
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
	$host = html_encode($_SERVER["HTTP_HOST"]);
	$protocol = SERVER_PROTOCOL . '://';
	if ($protocol == 'https_admin') {
		$protocol = 'https://';
	}
	echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode(strip_tags($linktext)) . "\" href=\"" .
	$protocol . $host . html_encode(getRSSLink($option, $lang, $addl)) . "\" />\n";
}

require_once(SERVERPATH . '/' . ZENFOLDER . '/class-feed.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/lib-MimeTypes.php');

class RSS extends feed {

	protected $feed = 'RSS';

	/**
	 * Creates a feed object from the URL parameters fetched only
	 *
	 */
	function __construct($options = NULL) {
		global $_zp_gallery, $_zp_current_admin_obj, $_zp_loggedin;
		if (empty($options))
			return;

		$this->feedtype = $options['rss'];
		parent::__construct($options);

		if (isset($_GET['token'])) {
//	The link camed from a logged in user, see if it is valid
			$link = $_GET;
			unset($link['token']);
			$token = Zenphoto_Authority::passwordHash(serialize($link), '');
			if ($token == $_GET['token']) {
				$adminobj = Zenphoto_Authority::getAnAdmin(array('`id`=' => (int) $link['user']));
				if ($adminobj) {
					$_zp_current_admin_obj = $adminobj;
					$_zp_loggedin = $_zp_current_admin_obj->getRights();
				}
			}
		}
// general feed setup
		$channeltitlemode = getOption('RSS_title');
		$this->host = html_encode($_SERVER["HTTP_HOST"]);

//channeltitle general
		switch ($channeltitlemode) {
			case 'gallery':
				$this->channel_title = $_zp_gallery->getBareTitle($this->locale);
				break;
			case 'website':
				$this->channel_title = strip_tags($_zp_gallery->getWebsiteTitle($this->locale));
				break;
			case 'both':
				$website_title = $_zp_gallery->getWebsiteTitle($this->locale);
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
					$alb = newAlbum($this->albumfolder, true, true);
					if ($alb->exists) {
						$albumtitle = $alb->getTitle();
						if ($this->mode = 'albums' || $this->collection) {
							$albumname = ' - ' . html_encode($albumtitle) . $this->getChannelTitleExtra();
						}
					} else {
						self::feed404();
					}
				} else {
					$albumtitle = '';
				}
				$albumname = $this->getChannelTitleExtra();

				$this->channel_title = html_encode($this->channel_title . ' ' . strip_tags($albumname));
				$this->imagesize = $this->getImageSize();
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
				$this->channel_title = html_encode($this->channel_title . $this->cattitle . $titleappendix);
				$this->imagesize = $this->getImageSize();
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
		}
		$this->feeditems = $this->getitems();
	}

	/**
	 * Updates the hitcoutner for RSS in the plugin_storage db table.
	 *
	 */
	protected function hitcounter() {
		if (!zp_loggedin() && getOption('RSS_hitcounter')) {
			$rssuri = $this->getCacheFilename();
			$type = 'hitcounter';
			$checkitem = query_single_row("SELECT `data` FROM " . prefix('plugin_storage') . " WHERE `aux` = " . db_quote($rssuri) . " AND `type` = '" . $type . "'", true);
			if ($checkitem) {
				$hitcount = $checkitem['data'] + 1;
				query("UPDATE " . prefix('plugin_storage') . " SET `data` = " . $hitcount . " WHERE `aux` = " . db_quote($rssuri) . " AND `type` = '" . $type . "'", true);
			} else {
				query("INSERT INTO " . prefix('plugin_storage') . " (`type`,`aux`,`data`) VALUES ('" . $type . "'," . db_quote($rssuri) . ",1)", true);
			}
		}
	}

	/**
	 * Gets the feed item data in a gallery feed
	 *
	 * @param object $item Object of an image or album
	 * @return array
	 */
	protected function getItemGallery($item) {
		if ($this->mode == "albums") {
			$albumobj = newAlbum($item['folder']);
			$totalimages = $albumobj->getNumImages();
			$itemlink = $this->host . pathurlencode($albumobj->getAlbumLink());
			$thumb = $albumobj->getAlbumThumbImage();
			$thumburl = '<img border="0" src="' . PROTOCOL . '://' . $this->host . pathurlencode($thumb->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE)) . '" alt="' . html_encode($albumobj->getTitle($this->locale)) . '" />';
			$title = $albumobj->getTitle($this->locale);
			if (true || $this->sortorder == "latestupdated") {
				$filechangedate = filectime(ALBUM_FOLDER_SERVERPATH . internalToFilesystem($albumobj->name));
				$latestimage = query_single_row("SELECT mtime FROM " . prefix('images') . " WHERE albumid = " . $albumobj->getID() . " AND `show` = 1 ORDER BY id DESC");
				if ($latestimage && $this->sortorder == 'latestupdated') {
					$count = db_count('images', "WHERE albumid = " . $albumobj->getID() . " AND mtime = " . $latestimage['mtime']);
				} else {
					$count = $totalimages;
				}
				if ($count != 0) {
					$imagenumber = sprintf(ngettext('%s (%u image)', '%s (%u images)', $count), $title, $count);
				} else {
					$imagenumber = $title;
				}
				$feeditem['desc'] = '<a title="' . $title . '" href="' . PROTOCOL . '://' . $itemlink . '">' . $thumburl . '</a>' .
								'<p>' . html_encode($imagenumber) . '</p>' . $albumobj->getDesc($this->locale) . '<br />' . sprintf(gettext("Last update: %s"), zpFormattedDate(DATE_FORMAT, $filechangedate));
			} else {
				if ($totalimages != 0) {
					$imagenumber = sprintf(ngettext('%s (%u image)', '%s (%u images)', $totalimages), $title, $totalimages);
				}
				$feeditem['desc'] = '<a title="' . html_encode($title) . '" href="' . PROTOCOL . '://' . $itemlink . '">' . $thumburl . '</a>' . $item->getDesc($this->locale) . '<br />' . sprintf(gettext("Date: %s"), zpFormattedDate(DATE_FORMAT, $item->get('mtime')));
			}
			$ext = getSuffix($thumb->localpath);
		} else {
			$ext = getSuffix($item->localpath);
			$albumobj = $item->getAlbum();
			$itemlink = $this->host . $item->getImagelink();
			$fullimagelink = $this->host . html_encode(pathurlencode($item->getFullImageURL()));
			$thumburl = '<img border="0" src="' . PROTOCOL . '://' . $this->host . html_encode(pathurlencode($item->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . '" alt="' . $item->getTitle($this->locale) . '" /><br />';
			$title = $item->getTitle($this->locale);
			$albumtitle = $albumobj->getTitle($this->locale);
			$datecontent = '<br />Date: ' . zpFormattedDate(DATE_FORMAT, $item->get('mtime'));
			if ((($ext == "flv") || ($ext == "mp3") || ($ext == "mp4") || ($ext == "3gp") || ($ext == "mov")) AND $this->mode != "album") {
				$feeditem['desc'] = '<a title="' . html_encode($title) . ' in ' . html_encode($albumobj->getTitle($this->locale)) . '" href="' . PROTOCOL . '://' . $itemlink . '">' . $thumburl . '</a>' . $item->getDesc($this->locale) . $datecontent;
			} else {
				$feeditem['desc'] = '<a title="' . html_encode($title) . ' in ' . html_encode($albumobj->getTitle($this->locale)) . '" href="' . PROTOCOL . '://' . $itemlink . '"><img src="' . PROTOCOL . '://' . $this->host . html_encode(pathurlencode($item->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . '" alt="' . html_encode($title) . '" /></a>' . $item->getDesc($this->locale) . $datecontent;
			}
		}
// title
		if ($this->mode != "albums") {
			$feeditem['title'] = sprintf('%1$s (%2$s)', $item->getTitle($this->locale), $albumobj->getTitle($this->locale));
		} else {
			$feeditem['title'] = $imagenumber;
		}
//link
		$feeditem['link'] = PROTOCOL . '://' . $itemlink;

// enclosure
		$feeditem['enclosure'] = '';
		if (getOption("RSS_enclosure") AND $this->mode != "albums") {
			$feeditem['enclosure'] = '<enclosure url="' . PROTOCOL . '://' . $fullimagelink . '" type="' . getMimeString($ext) . '" length="' . filesize($item->localpath) . '" />';
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
			$feeditem['media_content'] = '<media:content url="' . PROTOCOL . '://' . $fullimagelink . '" type="image/jpeg" />';
			$feeditem['media_thumbnail'] = '<media:thumbnail url="' . PROTOCOL . '://' . $fullimagelink . '" width="' . $this->imagesize . '"	height="' . $this->imagesize . '" />';
		}
//date
		if ($this->mode != "albums") {
			$feeditem['pubdate'] = date("r", strtotime($item->getDateTime()));
		} else {
			$feeditem['pubdate'] = date("r", strtotime($albumobj->getDateTime()));
		}
		return $feeditem;
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
		$itemtype = strtolower($item['type']); // needed because getZenpageStatistic returns "News" instead of "news" for unknown reasons...
//get the type of the news item
		switch ($itemtype) {
			case 'news':
				$obj = new ZenpageNews($item['titlelink']);
				$title = $feeditem['title'] = get_language_string($obj->getTitle('all'), $this->locale);
				$link = getNewsURL($obj->getTitlelink());
				$count2 = 0;
				$plaincategories = $obj->getCategories();
				$categories = '';
				foreach ($plaincategories as $cat) {
					$catobj = new ZenpageCategory($cat['titlelink']);
					$categories .= get_language_string($catobj->getTitle('all'), $this->locale) . ', ';
				}
				$categories = rtrim($categories, ', ');
				$feeditem['desc'] = shortenContent($obj->getContent($this->locale), getOption('RSS_truncate_length'), '...');
				break;
			case 'images':
				$albumobj = newAlbum($item['albumname']);
				$obj = newImage($albumobj, $item['titlelink']);
				$categories = get_language_string($albumobj->getTitle('all'), $this->locale);
				$feeditem['title'] = strip_tags(get_language_string($obj->getTitle('all'), $this->locale));
				$title = get_language_string($obj->getTitle('all'), $this->locale);
				$link = $obj->getImageLink();
				$filename = $obj->getFilename();
				$ext = getSuffix($filename);
				$album = $albumobj->getFolder();
				$fullimagelink = $this->host . html_encode(pathurlencode($obj->getFullImageURL()));
				$content = shortenContent($obj->getDesc($this->locale), getOption('RSS_truncate_length'), '...');
				if (isImagePhoto($obj)) {
					$thumburl = '<img border="0" src="' . PROTOCOL . '://' . $this->host . html_encode(pathurlencode($obj->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . '" alt="' . $obj->getTitle($this->locale) . '" /><br />';
				} else {
					$thumburl = '<img border="0" src="' . PROTOCOL . '://' . $this->host . html_encode(pathurlencode($obj->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . '" alt="' . $obj->getTitle($this->locale) . '" /><br />';
				}
				$feeditem['desc'] = '<a title="' . html_encode($feeditem['title']) . ' in ' . html_encode($categories) . '" href="' . PROTOCOL . '://' . $this->host . $link . '">' . $thumburl . '</a><br />' . $content;
				if (getOption("RSS_enclosure")) {
					$feeditem['enclosure'] = '<enclosure url="' . PROTOCOL . '://' . $fullimagelink . '" type="' . getMimeString($ext) . '" length="' . filesize($obj->localpath) . '" />';
				}
				break;
			case 'albums':
				$obj = newAlbum($item['albumname']);
				$categories = get_language_string($obj->getTitle('all'), $this->locale);
				$feeditem['title'] = strip_tags(get_language_string($obj->getTitle('all'), $this->locale));
				$title = get_language_string($obj->getTitle('all'), $this->locale);
				$link = $obj->getAlbumLink();
				$album = $obj->getFolder();
				$albumthumb = $obj->getAlbumThumbImage();
				$content = shortenContent($obj->getDesc($this->locale), getOption('RSS_truncate_length'), '...');
				if (isImagePhoto($obj)) {
					$thumburl = '<img border="0" src="' . PROTOCOL . '://' . $this->host . html_encode(pathurlencode($albumthumb->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . '" alt="' . $obj->getTitle($this->locale) . '" /><br />';
				} else {
					$thumburl = '<img border="0" src="' . PROTOCOL . '://' . $this->host . html_encode(pathurlencode($albumthumb->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . '" alt="' . $obj->getTitle($this->locale) . '" /><br />';
				}
				$feeditem['desc'] = '<a title="' . html_encode($feeditem['title']) . '" href="' . PROTOCOL . '://' . $this->host . $link . '">' . $thumburl . '</a><br />' . $content;
				break;
		}
		if (!empty($categories)) {
			$feeditem['category'] = html_encode($categories);
			$feeditem['title'] = $title . ' (' . $categories . ')';
		}
		$feeditem['link'] = PROTOCOL . '://' . $this->host . $link;
		$feeditem['media_content'] = '';
		$feeditem['media_thumbnail'] = '';
		$feeditem['pubdate'] = date("r", strtotime($item['date']));

		return $feeditem;
	}

	/**
	 * Prints the RSS feed xml
	 *
	 */
	public function printFeed() {
		global $_zp_gallery;
		$feeditems = $this->getitems();
		if (is_array($feeditems)) {
			$this->hitcounter();
			$this->startCache();
			header('Content-Type: application/xml');
			echo '<?xml-stylesheet type="text/css" href="' . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/rss/rss.css" ?>' . "\n";
			?>
			<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
				<channel>
					<title><?php echo $this->channel_title; ?></title>
					<link><?php echo PROTOCOL . '://' . $this->host . WEBPATH; ?></link>
					<atom:link href="<?php echo PROTOCOL; ?>://<?php echo $this->host; ?><?php echo html_encode(getRequestURI()); ?>" rel="self"	type="application/rss+xml" />
					<description><?php echo strip_tags($_zp_gallery->getDesc($this->locale)); ?></description>
					<language><?php echo $this->locale_xml; ?></language>
					<pubDate><?php echo date("r", time()); ?></pubDate>
					<lastBuildDate><?php echo date("r", time()); ?></lastBuildDate>
					<docs>http://blogs.law.harvard.edu/tech/rss</docs>
					<generator>Zenphoto RSS Generator</generator>
					<?php
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
							<pubDate><?php echo $item['pubdate']; ?></pubDate>
						</item>
						<?php
					} // foreach
					?>
				</channel>
			</rss>
			<?php
			$this->endCache();
		}
	}

}

// RSS feed calls before anything else
if (!OFFSET_PATH && isset($_GET['rss'])) {
	if (!$_GET['rss']) {
		$_GET['rss'] = 'gallery';
	}
//	load the theme plugins just incase
	$_zp_gallery_page = 'rss.php';
	$rss = new RSS(sanitize($_GET));
	$rss->printFeed();
	exitZP();
}
?>