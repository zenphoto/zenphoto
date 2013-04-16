<?php
/**
 * This plugin handles <i>RSS</i> feeds:
 *
 * @author Stephen Billard (sbillard)
 * @package classes
 * @subpackage feed
 */

// force UTF-8 Ø

$plugin_is_filter = 9|CLASS_PLUGIN;
$plugin_description = gettext('The Zenphoto <em>RSS</em> handler.');
$plugin_notice = gettext('This plugin must be enabled to supply <em>RSS</em> feeds.').'<br />'.gettext('<strong>Note:</strong> Theme support is required to display RSS links.');

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
			setOptionDefault('RSS_title',getOption('feed_title'));

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
		}
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
		setOptionDefault('RSS_title','both');
	}

	function getOptionsSupported() {
		$options = array(gettext('RSS feeds enabled:')=>array('key'=>'RSS_feed_list', 'type' => OPTION_TYPE_CHECKBOX_ARRAY,
																													'order' => 0,
																													'checkboxes' => array(gettext('Gallery')=>'RSS_album_image',
																																								gettext('Comments')=>'RSS_comments',
																																								gettext('All News')=>'RSS_articles',
																																								gettext('All Pages')=>'RSS_pages',
																																								gettext('News/Page Comments')=>'RSS_article_comments'
																																							),
																													'desc' => gettext('Check each RSS feed you wish to activate.')),
											gettext('Number of RSS image feed items:')=>array('key'=>'RSS_items', 'type'=>OPTION_TYPE_TEXTBOX,
																																				'order'=>1,
																																				'desc'=>gettext("The number of new images and comments you want to appear in your site's RSS feed")),
											gettext('Number of RSS album feed items:')=>array('key'=>'RSS_items_albums', 'type'=>OPTION_TYPE_TEXTBOX,
																																				'order'=>2,
																																				'desc'=>gettext("The number of new images and comments you want to appear in your site's RSS feed")),
											gettext('')=>array('key'=>'RSS_imagesize', 'type'=>OPTION_TYPE_TEXTBOX,
																					'order'=>3,
																					'desc'=>gettext('Size of RSS image feed images:')),
											gettext('')=>array('key'=>'RSS_imagesize_albums', 'type'=>OPTION_TYPE_TEXTBOX,
																					'order'=>4,
																					'desc'=>gettext('Size of RSS album feed images :')),
											gettext('RSS feed image sort order:')=>array('key'=>'RSS_sortorder', 'type'=>OPTION_TYPE_SELECTOR,
																																		'order'=>5,
																																		'selections'=>array(gettext('latest by id')=>'latest',
																																											gettext('latest by date')=>'latest-date',
																																											gettext('latest by mtime')=>'latest-mtime',
																																											gettext('latest by publishdate')=>'latest-publishdate'
																																											),
																																		'desc'=>gettext("Choose between latest by id for the latest uploaded, latest by date for the latest uploaded fetched by date, or latest by mtime for the latest uploaded fetched by the file's last change timestamp.")),
											gettext('RSS feed album sort order:')=>array('key'=>'RSS_sortorder_albums', 'type'=>OPTION_TYPE_SELECTOR,
																																		'selections'=>array(gettext('latest by id')=>'latest',
																																											gettext('latest by date')=>'latest-date',
																																											gettext('latest by mtime')=>'latest-mtime',
																																											gettext('latest by publishdate')=>'latest-publishdate',
																																											gettext('latest updated')=>'latestupdated'
																																										),
																																		'order'=>6,
																																		'desc'=>gettext('Choose between latest by id for the latest uploaded and latest updated')),
											gettext('RSS enclosure:')=>array('key'=>'RSS_enclosure', 'type'=>OPTION_TYPE_CHECKBOX,
																												'order'=>7,
																												'desc'=>gettext('Check if you want to enable the RSS enclosure feature which provides a direct download for full images, movies etc. from within certain RSS reader clients (only Images RSS).')),
											gettext('Media RSS:')=>array('key'=>'RSS_mediarss', 'type'=>OPTION_TYPE_CHECKBOX,
																										'order'=>8,
																										'desc'=>gettext('Check if media RSS support is to be enabled. This support is used by some services and programs (only Images RSS).')),
											gettext('RSS cache')=>array('key'=>'RSS_cache', 'type'=>OPTION_TYPE_CHECKBOX,
																									'order'=>9,
																									'desc'=>gettext('Check if you want to enable static RSS feed caching. The cached file will be placed within the cache_html folder.')),
											gettext('RSS cache expiration')=>array('key'=>'RSS_cache_expire', 'type'=>OPTION_TYPE_TEXTBOX,
																															'order'=>10,
																															'desc'=>gettext('Cache expire default is 86400 seconds (1 day = 24 hrs * 60 min * 60 sec).')),
											gettext('RSS hitcounter')=>array('key'=>'RSS_hitcounter', 'type'=>OPTION_TYPE_CHECKBOX,
																												'order'=>11,
																												'desc'=>gettext('Check if you want to store the hitcount on RSS feeds.')),
											gettext('RSS title')=>array('key'=>'RSS_title', 'type'=>OPTION_TYPE_RADIO,
																									'order'=>12,
																									'buttons' => array(gettext('Gallery title')=>'gallery', gettext('Website title')=>'website',gettext('Both')=>'both'),
																									'desc'=>gettext("Select what you want to use as the main RSS feed (channel) title. 'Both' means Website title followed by Gallery title")),
											gettext('Portable RSS link')=>array('key'=>'RSS_portable_link', 'type'=>OPTION_TYPE_CHECKBOX,
																													'order'=>13,
																													'desc'=>gettext('If checked links generated for logged‑in users will include a token identifying the user. Use of that link when not logged‑in will give the same feed as if the user were logged‑in.'))
										);
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
 *																		"Pages" feed for all pages
 * @param string $lang optional to display a feed link for a specific language. Enter the locale like "de_DE" (the locale must be installed on your Zenphoto to work of course). If empty the locale set in the admin option or the language selector (getOption('locale') is used.
 * @param string $addl provided additional data for feeds (e.g. album object for album feeds, $categorylink for zenpage categories
 */
function getRSSLink($option,$lang=NULL,$addl=NULL) {
	global $_zp_current_album, $_zp_current_image, $_zp_current_admin_obj;
	if(empty($lang)) {
		$lang = zpFunctions::getLanguageText(getOption('locale'));
	}
	$link = NULL;
	switch($option) {
		case 'Gallery':
			if (getOption('RSS_album_image')) {
				$link =  array('rss'=>'gallery');
			}
			break;
		case 'Album':
			if (getOption('RSS_album_image')) {
				if (is_object($addl)) {
					$album = $addl;
				} else {
					$album = $_zp_current_album;
				}
				$link = array('rss'=>'gallery','albumname'=>urlencode($album->getFolder()));
				break;
			}
		case 'Collection':
			if (getOption('RSS_album_image')) {
				if (is_object($addl)) {
					$album = $addl;
				} else {
					$album = $_zp_current_album;
				}
				$link = array('rss'=>'gallery','folder'=>urlencode($album->getFolder()));
			}
			break;
		case 'Comments':
			if (getOption('RSS_comments')) {
				$link = array('rss'=>'comments','type'=>'gallery');
			}
			break;
		case 'Comments-image':
			if (getOption('RSS_comments')) {
				$link = array('rss'=>'comments', 'id'=>(string) $_zp_current_image->getID(),'type'=>'image');
			}
			break;
		case 'Comments-album':
			if (getOption('RSS_comments')) {
				$link = array('rss'=>'comments','id'=>(string) $_zp_current_album->getID(),'type'=>'album');
			}
			break;
		case 'AlbumsRSS':
			if (getOption('RSS_album_image')) {
				$link = array('rss'=>'gallery','albumsmode'=>'');
			}
			break;
		case 'AlbumsRSScollection':
			if (getOption('RSS_album_image')) {
				$link = array('rss'=>'gallery','folder'=>urlencode($_zp_current_album->getFolder()),'albumsmode'=>'');
			}
			break;
		case 'Pages':
			if (getOption('RSS_pages')) {
				$link = array('rss'=>'page');
			}
			break;
		case 'News':
			if (getOption('RSS_articles')) {
				$link = array('rss'=>'news');
			}
			break;
		case 'Category':
			if (getOption('RSS_articles')) {
				$link = array('rss'=>'news',$categorylink=>'');
			}
			break;
		case 'NewsWithImages':
			if (getOption('RSS_articles')) {
				$link = array('rss'=>'news','withimages'=>'');
			}
			break;
		case 'Comments':
			if (getOption('RSS_article_comments')) {
				$link = array('comments'=>1,'type'=>'zenpage');
			}
			break;
		case 'Comments-news':
			if (getOption('RSS_article_comments')) {
				$link = array('rss'=>'comments','id'=>(string) getNewsID(),'type'=>news);;
			}
			break;
		case 'Comments-page':
			if (getOption('RSS_article_comments')) {
				$link = array('rss'=>'comments','id'=>(string) getPageID(),'type'=>'page');
			}
			break;
		case 'Comments-all':
			if (getOption('RSS_article_comments')) {
				$link = array('rss'=>'comments','type'=>'allcomments');;
			}
			break;
	}
	if (is_array($link)) {
		$link['lang'] = $lang;
		if (zp_loggedin() && getOption('RSS_portable_link')) {
			$link['user'] = (string) $_zp_current_admin_obj->getID();
			$link['token'] = Zenphoto_Authority::passwordHash(serialize($link), '');
		}
		$uri = WEBPATH.'/index.php?'.str_replace('=&', '&', http_build_query($link));
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
function printRSSLink($option, $prev, $linktext, $next, $printIcon=true, $class=null, $lang='', $addl=NULL) {
	if ($printIcon) {
		$icon = ' <img src="' . FULLWEBPATH . '/' . ZENFOLDER . '/images/rss.png" alt="RSS Feed" />';
	} else {
		$icon = '';
	}
	if (!is_null($class)) {
		$class = 'class="' . $class . '"';
	}
	if(empty($lang)) {
		$lang = zpFunctions::getLanguageText(getOption("locale"));
	}
	echo $prev."<a $class href=\"".html_encode(getRSSLink($option,$lang,$addl))."\" title=\"".gettext("Latest images RSS")."\" rel=\"nofollow\">".$linktext."$icon</a>".$next;
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
function printRSSHeaderLink($option, $linktext, $lang='', $addl=NULL) {
	$host = html_encode($_SERVER["HTTP_HOST"]);
	$protocol = SERVER_PROTOCOL.'://';
	if ($protocol == 'https_admin') {
		$protocol = 'https://';
	}
	echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$protocol.$host.html_encode(getRSSLink($option,$lang,$addl))."\" />\n";
}


require_once(SERVERPATH.'/'.ZENFOLDER.'/class-feed.php');
require_once(SERVERPATH.'/'.ZENFOLDER.'/lib-MimeTypes.php');

class RSS extends feed {
	protected $feed = 'RSS';

	/**
	 * Creates a feed object from the URL parameters fetched only
	 *
	 */
	function __construct() {
		global $_zp_gallery, $_zp_current_admin_obj, $_zp_loggedin;
		if(isset($_GET['rss'])) {
			if (isset($_GET['token'])) {
				//	The link camed from a logged in user, see if it is valid
				$link = $_GET;
				unset($link['token']);
				$token = Zenphoto_Authority::passwordHash(serialize($link), '');
				if ($token == $_GET['token']) {
					$adminobj = Zenphoto_Authority::getAnAdmin(array('`id`='=>(int) $link['user']));
					if ($adminobj) {
						$_zp_current_admin_obj = $adminobj;
						$_zp_loggedin = $_zp_current_admin_obj->getRights();
					}
				}
			}
			// general feed setup
			$channeltitlemode = getOption('RSS_title');
			$this->host = html_encode($_SERVER["HTTP_HOST"]);
			// url and xml locale
			if(isset($_GET['lang'])) {
				$this->locale = sanitize($_GET['lang']);
			} else {
				$this->locale = getOption('locale');
			}
			$this->locale_xml = strtr($this->locale,'_','-');

			//channeltitle general
			switch($channeltitlemode) {
				case 'gallery':
					$this->channel_title = $_zp_gallery->getBareTitle($this->locale);
					break;
				case 'website':
					$this->channel_title = strip_tags($_zp_gallery->getWebsiteTitle($this->locale));
					break;
				case 'both':
					$website_title = $_zp_gallery->getWebsiteTitle($this->locale);
					$this->channel_title = $_zp_gallery->getBareTitle($this->locale);
					if(!empty($website_title)) {
						$this->channel_title = $website_title.' - '.$this->channel_title;
					}
					break;
			}
			$this->feedtype = sanitize($_GET['rss']);;
			$this->sortorder = $this->getSortorder();
			$this->sortdirection = $this->getSortdirection();
			if(isset($_GET['itemnumber'])) {
				$this->itemnumber = sanitize_numeric($_GET['itemnumber']);
			} else {
				$this->itemnumber = getOption('RSS_items');
			}
			// individual feedtype setup
			switch($this->feedtype) {

				default:
					$this->feedtype = 'gallery';
				case 'gallery':
					if (!getOption('RSS_album_image')) {
						header("HTTP/1.0 404 Not Found");
						header("Status: 404 Not Found");
						include(ZENFOLDER. '/404.php');
						exitZP();
					}
					$this->feedtype = 'gallery';
					if(isset($_GET['albumsmode'])) {
						$this->mode = 'albums';
					}
					if(isset($_GET['folder'])) {
						$this->albumfolder = sanitize(urldecode($_GET['folder']));
						$this->collection = TRUE;
						$alb = newAlbum($this->albumfolder);
						$albumtitle = $alb->getTitle();
					} else if(isset($_GET['albumname'])){
						$this->albumfolder = sanitize(urldecode($_GET['albumname']));
						$this->collection = false;
						$alb = newAlbum($this->albumfolder);
						$albumtitle = $alb->getTitle();
					} else {
						$albumtitle = '';
						$this->collection = FALSE;
					}
					$albumname = ''; // to be sure
					if($this->mode == 'albums' || isset($_GET['albumname'])) {
						$albumname = ' - '.html_encode($albumtitle).$this->getChannelTitleExtra();
					} elseif ($this->mode == 'albums' && !isset($_GET['folder'])) {
						$albumname = $this->getChannelTitleExtra();
					} elseif ($this->mode == 'albums' && isset($_GET['folder'])) {
						$albumname = ' - '.html_encode($albumtitle).$this->getChannelTitleExtra();
					} else {
						$albumname = $this->getChannelTitleExtra();
					}
					$this->channel_title = html_encode($this->channel_title.' '.strip_tags($albumname));
					$this->albumpath = $this->getImageAndAlbumPaths('albumpath');
					$this->imagepath = $this->getImageAndAlbumPaths('imagepath');
					$this->imagesize = $this->getImageSize();
					require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER . '/image_album_statistics.php');
					break;

				case 'news':	//Zenpage News RSS
					if (!getOption('RSS_articles')) {
						header("HTTP/1.0 404 Not Found");
						header("Status: 404 Not Found");
						include(ZENFOLDER. '/404.php');
						exitZP();
					}
					$this->catlink = $this->getNewsCatOptions('catlink');
					$cattitle = $this->getNewsCatOptions('cattitle');
					if(!empty($cattitle)) {
						$cattitle = ' - '.html_encode($this->cattitle) ;
					}
					$this->newsoption = $this->getNewsCatOptions("option");
					$titleappendix = gettext(' (Latest news)');
					if($this->getCombinewsImages() || $this->getCombinewsAlbums()) {
						if($this->getCombinewsImages()) {
							$this->newsoption = $this->getCombinewsImages();
							$titleappendix = gettext(' (Latest news and images)');
						} else if($this->getCombinewsAlbums()) {
							$this->newsoption = $this->getCombinewsAlbums();
							$titleappendix = gettext(' (Latest news and albums)');
						}
					} else {
						switch($this->sortorder) {
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
					}
					$this->channel_title = html_encode($this->channel_title.$cattitle.$titleappendix);
					$this->imagesize = $this->getImageSize();
					$this->itemnumber = getOption("zenpage_rss_items"); // # of Items displayed on the feed
					require_once(SERVERPATH.'/'.ZENFOLDER . '/'.PLUGIN_FOLDER . '/image_album_statistics.php');
					require_once(SERVERPATH.'/'.ZENFOLDER . '/'.PLUGIN_FOLDER . '/zenpage/zenpage-template-functions.php');

					break;
				case 'pages':	//Zenpage News RSS
					if (!getOption('RSS_pages')) {
						header("HTTP/1.0 404 Not Found");
						header("Status: 404 Not Found");
						include(ZENFOLDER. '/404.php');
						exitZP();
					}
					switch($this->sortorder) {
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
					$this->channel_title = html_encode($this->channel_title.$titleappendix);
					require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER . '/zenpage/zenpage-template-functions.php');
					break;

				case 'comments':	//Comments RSS
					if (!getOption('RSS_comments')) {
						header("HTTP/1.0 404 Not Found");
						header("Status: 404 Not Found");
						include(ZENFOLDER. '/404.php');
						exitZP();
					}
					if(isset($_GET['type'])) {
						$this->commentrsstype = sanitize($_GET['type']);
					} else {
						$this->commentrsstype = 'all';
					}
					if(isset($_GET['id'])) {
						$this->id = sanitize_numeric($_GET['id']);
						$table = NULL;
						switch($this->commentrsstype) {
							case 'album': //sadly needed but the parameter cannot be changed for backward compatibility of feeds.
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
						}
						$this->itemobj = getItemByID($table,$this->id);
						$title = ' - '.$this->itemobj->getTitle();
					} else {
						$this->id = NULL;
						$this->itemobj = NULL;
						$title = NULL;
					}
					$this->channel_title = html_encode($this->channel_title.$title.gettext(' (latest comments)'));
					if(getOption('zp_plugin_zenpage')) {
						require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/zenpage/zenpage-template-functions.php');
					}
					break;
			}
			$this->feeditems = $this->getitems();
		}
	}

	/**
	 * Gets the RSS file name from the feed url and clears out query items and special chars
	 *
	 * @return string
	 */
	protected function getCacheFilename() {
		$uri = explode('?',getRequestURI());
		$filename = array();
		foreach (explode('&',$uri[1]) as $param) {
			$p = explode('=', $param);
			if (isset($p[1]) && !empty($p[1])) {
				$filename[] = $p[1];
			} else {
				$filename[] = $p[0];
			}
		}
		$filename = seoFriendly(implode('_',$filename));
		return $filename.".xml";


		//old way
		$replace = array(
				WEBPATH.'/' => '',
				"albumname="=>"_",
				"albumsmode="=>"_",
				"title=" => "_",
				"folder=" => "_",
				"type=" => "-",
				"albumtitle=" => "_",
				"category=" => "_",
				"id=" => "_",
				"lang=" => "_",
				"&amp;" => "_",
				"&" => "_",
				"index.php" => "",
				"/"=>"-",
				"?"=> ""
		);
		$filename = strtr(getRequestURI(),$replace);
		$filename = preg_replace("/__/","_",$filename);
		$filename = seoFriendly($filename);
		return $filename.".xml";
	}

	/**
	 * Updates the hitcoutner for RSS in the plugin_storage db table.
	 *
	 */
	protected function hitcounter() {
		if(!zp_loggedin() && getOption('RSS_hitcounter')) {
			$rssuri = $this->getCacheFilename();
			$type = 'hitcounter';
			$checkitem = query_single_row("SELECT `data` FROM ".prefix('plugin_storage')." WHERE `aux` = ".db_quote($rssuri)." AND `type` = '".$type."'",true);
			if($checkitem) {
				$hitcount = $checkitem['data']+1;
				query("UPDATE ".prefix('plugin_storage')." SET `data` = ".$hitcount." WHERE `aux` = ".db_quote($rssuri)." AND `type` = '".$type."'",true);
			} else {
				query("INSERT INTO ".prefix('plugin_storage')." (`type`,`aux`,`data`) VALUES ('".$type."',".db_quote($rssuri).",1)",true);
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
		if($this->mode == "albums") {
			$albumobj = newAlbum($item['folder']);
			$totalimages = $albumobj->getNumImages();
			$itemlink = $this->host.pathurlencode($albumobj->getAlbumLink());
			$thumb = $albumobj->getAlbumThumbImage();
			$thumburl = '<img border="0" src="'.PROTOCOL.'://'.$this->host.pathurlencode($thumb->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE)).'" alt="'.html_encode($albumobj->getTitle($this->locale)) .'" />';
			$title =  $albumobj->getTitle($this->locale);
			if(true || $this->sortorder == "latestupdated") {
				$filechangedate = filectime(ALBUM_FOLDER_SERVERPATH.internalToFilesystem($albumobj->name));
				$latestimage = query_single_row("SELECT mtime FROM " . prefix('images'). " WHERE albumid = ".$albumobj->getID() . " AND `show` = 1 ORDER BY id DESC");
				if($latestimage && $this->sortorder == 'latestupdated') {
					$count = db_count('images',"WHERE albumid = ".$albumobj->getID() . " AND mtime = ". $latestimage['mtime']);
				} else {
					$count = $totalimages;
				}
				if($count != 0) {
					$imagenumber = sprintf(ngettext('%s (%u image)','%s (%u images)',$count),$title, $count);
				} else {
					$imagenumber = $title;
				}
				$feeditem['desc'] = '<a title="'.$title.'" href="'.PROTOCOL.'://'.$itemlink.'">'.$thumburl.'</a>'.
						'<p>'.html_encode($imagenumber).'</p>'.$albumobj->getDesc($this->locale).'<br />'.sprintf(gettext("Last update: %s"),zpFormattedDate(DATE_FORMAT,$filechangedate));
			} else {
				if($totalimages != 0) {
					$imagenumber = sprintf(ngettext('%s (%u image)','%s (%u images)',$totalimages),$title, $totalimages);
				}
				$feeditem['desc'] = '<a title="'.html_encode($title).'" href="'.PROTOCOL.'://'.$itemlink.'">'.$thumburl.'</a>'.$item->getDesc($this->locale).'<br />'.sprintf(gettext("Date: %s"),zpFormattedDate(DATE_FORMAT,$item->get('mtime')));
			}
			$ext = getSuffix($thumb->filename);
		} else {
			$ext = getSuffix($item->filename);
			$albumobj = $item->getAlbum();
			$itemlink = $this->host.$item->getImagelink();
			$fullimagelink = $this->host.pathurlencode($item->getFullImageURL());
			$thumburl = '<img border="0" src="'.PROTOCOL.'://'.$this->host.pathurlencode($item->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE)).'" alt="'.$item->getTitle($this->locale) .'" /><br />';
			$title = $item->getTitle($this->locale);
			$albumtitle = $albumobj->getTitle($this->locale);
			$datecontent = '<br />Date: '.zpFormattedDate(DATE_FORMAT,$item->get('mtime'));
			if ((($ext == "flv") || ($ext == "mp3") || ($ext == "mp4") ||  ($ext == "3gp") ||  ($ext == "mov")) AND $this->mode != "album") {
				$feeditem['desc'] = '<a title="'.html_encode($title).' in '.html_encode($albumobj->getTitle($this->locale)).'" href="'.PROTOCOL.'://'.$itemlink.'">'.$thumburl.'</a>' . $item->getDesc($this->locale).$datecontent;
			} else {
				$feeditem['desc'] = '<a title="'.html_encode($title).' in '.html_encode($albumobj->getTitle($this->locale)).'" href="'.PROTOCOL.'://'.$itemlink.'"><img src="'.PROTOCOL.'://'.$this->host.pathurlencode($item->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE)).'" alt="'.html_encode($title).'" /></a>' . $item->getDesc($this->locale).$datecontent;
			}
		}
		// title
		if($this->mode != "albums") {
			$feeditem['title'] = sprintf('%1$s (%2$s)', $item->getTitle($this->locale), $albumobj->getTitle($this->locale));
		} else {
			$feeditem['title'] = $imagenumber;
		}
		//link
		$feeditem['link'] = PROTOCOL.'://'.$itemlink;

		// enclosure
		$feeditem['enclosure'] = '';
		if(getOption("RSS_enclosure") AND $this->mode != "albums") {
			$feeditem['enclosure'] = '<enclosure url="'.PROTOCOL.'://'.$fullimagelink.'" type="'.getMimeString($ext).'" length="'.filesize($item->localpath).'" />';
		}
		//category
		if($this->mode != "albums") {
			$feeditem['category'] = html_encode($albumobj->getTitle($this->locale));
		} else {
			$feeditem['category'] = html_encode($albumobj->getTitle($this->locale));
		}
		//media content
		$feeditem['media_content'] = '';
		$feeditem['media_thumbnail'] = '';
		if(getOption("RSS_mediarss") AND $this->mode != "albums") {
			$feeditem['media_content'] = '<media:content url="'.PROTOCOL.'://'.$fullimagelink.'" type="image/jpeg" />';
			$feeditem['media_thumbnail'] = '<media:thumbnail url="'.PROTOCOL.'://'.$fullimagelink.'" width="'.$this->imagesize.'"	height="'.$this->imagesize.'" />';
		}
		//date
		if($this->mode != "albums") {
			$feeditem['pubdate'] = date("r",strtotime($item->getDateTime()));
		} else {
			$feeditem['pubdate'] = date("r",strtotime($albumobj->getDateTime()));
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
		switch($itemtype) {
			case 'news':
				$obj = new ZenpageNews($item['titlelink']);
				$title = $feeditem['title'] = get_language_string($obj->getTitle('all'),$this->locale);
				$link = getNewsURL($obj->getTitlelink());
				$count2 = 0;
				$plaincategories = $obj->getCategories();
				$categories = '';
				foreach($plaincategories as $cat){
					$catobj = new ZenpageCategory($cat['titlelink']);
					$categories .= get_language_string($catobj->getTitle('all'), $this->locale).', ';
				}
				$categories = rtrim($categories, ', ');
				$feeditem['desc'] = shortenContent($obj->getContent($this->locale),getOption('zenpage_rss_length'), '...');
				break;
			case 'images':
				$albumobj = newAlbum($item['albumname']);
				$obj = newImage($albumobj,$item['titlelink']);
				$categories = get_language_string($albumobj->getTitle('all'),$this->locale);
				$feeditem['title'] = strip_tags(get_language_string($obj->getTitle('all'),$this->locale));
				$title = get_language_string($obj->getTitle('all'),$this->locale);
				$link = $obj->getImageLink();
				$filename = $obj->getFilename();
				$ext = getSuffix($filename);
				$album = $albumobj->getFolder();
				$fullimagelink = $this->host.pathurlencode($obj->getFullImageURL());
				$content = shortenContent($obj->getDesc($this->locale),getOption('zenpage_rss_length'), '...');
				if(isImagePhoto($obj)) {
					$feeditem['desc'] = '<a title="'.html_encode($feeditem['title']).' in '.html_encode($categories).'" href="'.PROTOCOL.'://'.$this->host.$link.'"><img border="0" src="'.PROTOCOL.'://'.$this->host.pathurlencode($obj->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE)).'" alt="'. html_encode($feeditem['title']).'"></a><br />'.$content;
				} else {
					$feeditem['desc'] = '<a title="'.html_encode($feeditem['title']).' in '.html_encode($categories).'" href="'.PROTOCOL.'://'.$this->host.$link.'"><img src="'.pathurlencode($obj->getThumb()).'" alt="'.html_encode($feeditem['title']).'" /></a><br />'.$content;
				}
				if(getOption("RSS_enclosure")) {
					$feeditem['enclosure'] = '<enclosure url="'.PROTOCOL.'://'.$fullimagelink.'" type="'.getMimeString($ext).'" length="'.filesize($obj->localpath).'" />';
				}
				break;
			case 'albums':
				$obj = newAlbum($item['albumname']);
				$categories = get_language_string($obj->getTitle('all'),$this->locale);
				$feeditem['title'] = strip_tags(get_language_string($obj->getTitle('all'),$this->locale));
				$title = get_language_string($obj->getTitle('all'),$this->locale);
				$link = pathurlencode($obj->getAlbumLink());
				$album = $obj->getFolder();
				$albumthumb = $obj->getAlbumThumbImage();
				$content = shortenContent($obj->getDesc($this->locale),getOption('zenpage_rss_length'), '...');

				if(isImagePhoto($obj)) {
					$feeditem['desc'] = '<a title="'.html_encode($feeditem['title']).'" href="'.PROTOCOL.'://'.$this->host.$link.'"><img border="0" src="'.PROTOCOL.'://'.$this->host.pathurlencode($albumthumb->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE)).'" alt="'. html_encode($feeditem['title']).'"></a><br />'.$content;
				} else {
					$feeditem['desc'] = '<a title="'.html_encode($feeditem['title']).'" href="'.PROTOCOL.'://'.$this->host.$link.'"><img src="'.pathurlencode($obj->getAlbumThumb()).'" alt="'.html_encode($feeditem['title']).'" /></a><br />'.$content;
				}
				break;
		}
		if(!empty($categories)) {
			$feeditem['category'] = html_encode($categories);
			$feeditem['title'] = $title.' ('.$categories.')';
		}
		$feeditem['link'] = PROTOCOL.'://'.$this->host.$link;
		$feeditem['media_content'] = '';
		$feeditem['media_thumbnail'] = '';
		$feeditem['pubdate'] = date("r",strtotime($item['date']));

		return $feeditem;
	}

	/**
	 * Prints the RSS feed xml
	 *
	 */
	public function printFeed() {
		global $_zp_gallery;
		$feeditems = $this->getitems();
		if(is_array($feeditems)) {
			$this->hitcounter();
			$this->startCache();
			header('Content-Type: application/xml');
			echo '<?xml-stylesheet type="text/css" href="'.WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/rss/rss.css" ?>'."\n";
			?>
			<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
				<channel>
					<title><?php echo $this->channel_title; ?></title>
					<link><?php echo PROTOCOL.'://'.$this->host.WEBPATH; ?></link>
					<atom:link href="<?php echo PROTOCOL; ?>://<?php echo $this->host; ?><?php echo html_encode(getRequestURI()); ?>" rel="self"	type="application/rss+xml" />
					<description><?php echo strip_tags($_zp_gallery->getDesc($this->locale)); ?></description>
					<language><?php echo $this->locale_xml; ?></language>
					<pubDate><?php echo date("r", time()); ?></pubDate>
					<lastBuildDate><?php echo date("r", time()); ?></lastBuildDate>
					<docs>http://blogs.law.harvard.edu/tech/rss</docs>
					<generator>Zenphoto RSS Generator</generator>
					<?php
					foreach($feeditems as $feeditem) {
						switch($this->feedtype) {
							case 'gallery':
								$item = $this->getItemGallery($feeditem);
								break;
							case 'news':
								$item = $this->getItemNews($feeditem);
								break;
							case 'pages':
								$item = $this->getitemPages($feeditem);
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
							if(!empty($item['enclosure'])) {
								echo $item['enclosure']; //prints xml as well
							}
							if(!empty($item['category'])) {
								?>
								<category><![CDATA[<?php echo $item['category']; ?>]]></category>
								<?php
							}
							if(!empty($item['media_content'])) {
								echo $item['media_content']; //prints xml as well
							}
							if(!empty($item['media_thumbnail'])) {
								echo $item['media_thumbnail']; //prints xml as well
							}
							?>
							<guid><?php echo html_encode($item['link']); ?></guid>
							<pubDate><?php echo $item['pubdate'];  ?></pubDate>
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

?>