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

//TODO: the real RSS class goes here!
?>