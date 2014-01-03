<?php
/**
 * This plugin handles <i>External</i> feeds:
 *
 * Supports all RSS feed options plus individual image, news, and Page requests:
 * <var>?external&album=album</var> for an album
 * <var>?external&album=album&image=image</var> for an image
 * <var>?external=news&titlelink=article</var> for an article
 * <var>?external=pages&titlelink=article</var> for an page
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage feed
 */
// force UTF-8 Ø

$plugin_is_filter = 9 | FEATURE_PLUGIN | ADMIN_PLUGIN;
$plugin_description = gettext('The Zenphoto <em>externalFeed</em> handler.');
$plugin_notice = gettext('This plugin must be enabled to supply <em>externalFeed</em> feeds.');

$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'externalFeed_options';

class externalFeed_options {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('externalFeed_truncate_length', '100');
			setOptionDefault('externalFeed_zenpage_items', '10');
			setOptionDefault('externalFeed_items', 10); // options for standard images rss
			setOptionDefault('externalFeed_imagesize', 240);
			setOptionDefault('externalFeed_sortorder', 'latest');
			setOptionDefault('externalFeed_items_albums', 10); // options for albums rss
			setOptionDefault('externalFeed_imagesize_albums', 240);
			setOptionDefault('externalFeed_sortorder_albums', 'latest');
			setOptionDefault('externalFeed_hitcounter', 1);
		}
	}

	function getOptionsSupported() {
		$options = array(gettext('externalFeed feeds enabled:') => array('key'				 => 'externalFeed_feed_list', 'type'			 => OPTION_TYPE_CHECKBOX_ARRAY,
										'order'			 => 0,
										'checkboxes' => array(gettext('Gallery')						 => 'externalFeed_album_image',
														gettext('Gallery Comments')		 => 'externalFeed_comments',
														gettext('News')								 => 'externalFeed_articles',
														gettext('Pages')							 => 'externalFeed_pages',
														gettext('News/Page Comments')	 => 'externalFeed_article_comments'
										),
										'desc'			 => gettext('Check each externalFeed feed you wish to activate.')),
						gettext('Image feed items:')					 => array('key'		 => 'externalFeed_items', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1,
										'desc'	 => gettext("The number of new images and comments you want to appear in your site's externalFeed feed")),
						gettext('Album feed items:')					 => array('key'		 => 'externalFeed_items_albums', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 2,
										'desc'	 => gettext("The number of new images and comments you want to appear in your site's externalFeed feed")),
						gettext('Image size')									 => array('key'		 => 'externalFeed_imagesize', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 3,
										'desc'	 => gettext('Size of externalFeed image feed images:')),
						gettext('Album image size')						 => array('key'		 => 'externalFeed_imagesize_albums', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 4,
										'desc'	 => gettext('Size of externalFeed album feed images :')),
						gettext('Image feed sort order:')			 => array('key'				 => 'externalFeed_sortorder', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 6,
										'selections' => array(gettext('latest by id')					 => 'latest',
														gettext('latest by date')				 => 'latest-date',
														gettext('latest by mtime')			 => 'latest-mtime',
														gettext('latest by publishdate') => 'latest-publishdate'
										),
										'desc'			 => gettext("Choose between latest by id for the latest uploaded, latest by date for the latest uploaded fetched by date, or latest by mtime for the latest uploaded fetched by the file's last change timestamp.")),
						gettext('Album feed sort order:')			 => array('key'				 => 'externalFeed_sortorder_albums', 'type'			 => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('latest by id')					 => 'latest',
														gettext('latest by date')				 => 'latest-date',
														gettext('latest by mtime')			 => 'latest-mtime',
														gettext('latest by publishdate') => 'latest-publishdate',
														gettext('latest updated')				 => 'latestupdated'
										),
										'order'			 => 7,
										'desc'			 => gettext('Choose between latest by id for the latest uploaded and latest updated')),
						gettext('Hitcounter')									 => array('key'		 => 'externalFeed_hitcounter', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 12,
										'desc'	 => gettext('Check if you want to store the hitcount on externalFeed feeds.'))
		);
		if (extensionEnabled('zenpage')) {
			$options[gettext('Feed text length')] = array('key'		 => 'externalFeed_truncate_length', 'type'	 => OPTION_TYPE_TEXTBOX,
							'order'	 => 5.5,
							'desc'	 => gettext("The text length of the Zenpage externalFeed feed items. No value for full length."));
			$options[gettext('Zenpage feed items')] = array('key'		 => 'externalFeed_zenpage_items', 'type'	 => OPTION_TYPE_TEXTBOX,
							'order'	 => 5,
							'desc'	 => gettext("The number of news articles you want to appear in your site's News externalFeed feed."));
		}
		return $options;
	}

	function handleOption($option, $currentValue) {

	}

}

require_once(SERVERPATH . '/' . ZENFOLDER . '/class-feed.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/lib-MimeTypes.php');

class ExternalFeed extends feed {

	protected $feed = 'externalFeed';

	/**
	 * Creates a feed object from the URL parameters fetched only
	 *
	 */
	function __construct($options = NULL) {
		global $_zp_gallery, $_zp_current_admin_obj, $_zp_loggedin;
		if (empty($options))
			self::feed404();

		$this->feedtype = $options['external'];
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
		$channeltitlemode = getOption('externalFeed_title');
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
				if (!getOption('externalFeed_album_image')) {
					self::feed404();
				}

				$albumname = $this->getChannelTitleExtra();
				if ($this->albumfolder) {
					$alb = newAlbum($this->albumfolder, true, true);
					if ($alb->exists) {
						$albumtitle = $alb->getTitle();
						if ($this->mode == 'albums' || $this->collection) {
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

			case 'news': //Zenpage News
				if (!getOption('externalFeed_articles')) {
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
				$this->itemnumber = getOption("externalFeed_zenpage_items"); // # of Items displayed on the feed
				require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/image_album_statistics.php');
				require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/zenpage-template-functions.php');

				break;
			case 'pages': //Zenpage News
				if (!getOption('externalFeed_pages')) {
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

			case 'comments': //Comments
				if (!getOption('externalFeed_comments')) {
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
	 * Updates the hitcoutner for  in the plugin_storage db table.
	 *
	 */
	protected function hitcounter() {
		if (!zp_loggedin() && getOption('externalFeed_hitcounter')) {
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
			$itemlink = $this->host . pathurlencode($albumobj->getLink());
			$thumb = $albumobj->getAlbumThumbImage();
			$title = $albumobj->getTitle($this->locale);

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
			$feeditem['desc'] = $albumobj->getDesc($this->locale);

			$feeditem['title'] = $imagenumber;
			$feeditem['pubdate'] = date("r", strtotime($albumobj->getDateTime()));
		} else {
			if (get_class($item) == 'Album') {
				$albumobj = $item;
				$thumb = $albumobj->getAlbumThumbImage();
			} else {
				$albumobj = $item->getAlbum();
				$thumb = $item;
			}
			$itemlink = $this->host . $item->getLink();
			$title = $item->getTitle($this->locale);

			$feeditem['desc'] = $item->getDesc($this->locale);
			$feeditem['title'] = sprintf('%1$s (%2$s)', $item->getTitle($this->locale), $albumobj->getTitle($this->locale));
			$feeditem['pubdate'] = date("r", strtotime($item->getDateTime()));
		}

//link
		$feeditem['link'] = $itemlink;

//category
		$feeditem['category'] = html_encode($albumobj->getTitle($this->locale));

//media content

		$feeditem['media_content'] = '<image url="' . PROTOCOL . '://' . html_encode($thumb->getCustomImage($this->imagesize, NULL, NULL, NULL, NULL, NULL, NULL, TRUE)) . '" />';
		$feeditem['media_thumbnail'] = '<thumbnail url="' . PROTOCOL . '://' . html_encode($thumb->getThumb()) . '" />';

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
		$desc = $obj->getContent($this->locale);
		$desc = str_replace('//<![CDATA[', '', $desc);
		$desc = str_replace('//]]>', '', $desc);
		$feeditem['desc'] = shortenContent($desc, getOption('externalFeed_truncate_length'), '...');
		if (!empty($categories)) {
			$feeditem['category'] = html_encode($categories);
			$feeditem['title'] = $title . ' (' . $categories . ')';
		}
		$feeditem['link'] = $link;
		$feeditem['media_content'] = '';
		$feeditem['media_thumbnail'] = '';
		$feeditem['pubdate'] = date("r", strtotime($obj->getDateTime()));

		return $feeditem;
	}

	public function getitems() {

		if (($album = @$this->options['album'])) {
			if ($image = @$this->options['image']) {
				$image = newImage(NULL, array('folder' => $album, 'filename' => $image), true);
				if ($image->exists) {
					return array($image);
				} else {
					return array();
				}
			} else {
				$album = newAlbum($album);
				if ($album->exists) {
					return array($album);
				} else {
					return array();
				}
			}
		}

		if ($this->feedtype == 'news' && $news = @$this->options['titlelink']) {
			$obj = new ZenpageNews($news, false);
			if ($obj->loaded) {
				return array(array('titlelink' => $news));
			} else {
				return array();
			}
		}
		if ($this->feedtype == 'pages' && $page = @$this->options['titlelink']) {
			$obj = new ZenpagePage($page, false);
			if ($obj->loaded) {
				return array(array('titlelink' => $page));
			} else {
				return array();
			}
		}
		return parent::getitems();
	}

	/**
	 * Prints the feed xml
	 *
	 */
	public function printFeed() {
		global $_zp_gallery;
		$feeditems = $this->getitems();
		if (is_array($feeditems)) {
			$this->hitcounter();
			header('Content-Type: application/xml');
			?>
			<external version="1.0" >
				<channel>
					<link href="<?php echo PROTOCOL; ?>://<?php echo $this->host; ?><?php echo html_encode(getRequestURI()); ?>" />
					<language><?php echo $this->locale_xml; ?></language>
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
								$item = $this->getitemPages($feeditem, getOption('externalFeed_truncate_length'));
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
							<link><?php echo PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . WEBPATH . '/' . html_encode(ltrim($item['link'], '/')); ?></link>
							<description><![CDATA[<?php echo $item['desc']; ?>]]></description>
							<?php
							if (!empty($item['enclosure'])) {
								echo $item['enclosure'] . "\n"; //prints xml as well
							}
							if (!empty($item['category'])) {
								?>
								<category><![CDATA[<?php echo $item['category']; ?>]]></category>
								<?php
							}
							if (!empty($item['media_content'])) {
								echo $item['media_content'] . "\n"; //prints xml as well
							}
							if (!empty($item['media_thumbnail'])) {
								echo $item['media_thumbnail'] . "\n"; //prints xml as well
							}
							?>
							<pubDate><?php echo $item['pubdate']; ?></pubDate>
						</item>
						<?php
					} // foreach
					?>
				</channel>
			</external>
			<?php
		}
	}

}

// feed calls before anything else
if (!OFFSET_PATH && isset($_GET['external'])) {
	if (!$_GET['external']) {
		$_GET['external'] = 'gallery';
	}
//	load the theme plugins just incase
	$_zp_gallery_page = 'rss.php';
	$e = new ExternalFeed(sanitize($_GET));
	$e->printFeed();
	exitZP();
}
?>