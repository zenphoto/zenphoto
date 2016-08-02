<?php
/**
 * This plugin handles <i>External</i> feeds:
 *
 * url: <i>site</i>/index.php?external=<i>type</i>&accesskey=<i>key</i>+feed selections
 *
 * The accesskey is obtained from the plugin options. Each request changes the key
 * for the next. That next key is provided in the feed. If the wrong key is provided
 * the result is a page not found (404)
 *
 * Feed types:
 *
 * Supports all RSS feed options plus individual Image, News, and Page requests:
 * <ul>
 * 	<li>?external=gallery
 * 		<ul>
 * 			<li>&album=<i>album</i> for an album</li>
 * 			<li>&album[]=<i>album</i>&album[]=>i>album</i>... for a list of albums</li>
 * 			<li>&album=<i>album</i>&image=<i>image</i> for an image</li>
 * 			<li>&album=<i>album</i>&image[]=<i>image</i>&image[]=<i>image</i>... for a list of images</li>
 *
 * 				add &size=<i>size</i> to the image request to select a particular image size. (This
 * 				cannot be larger than the plugin's image size option.)
 * 		</ul>
 * 	</li>
 * 	<li>?external=news
 * 		<ul>
 * 			<li>&titlelink=<i>article</i> for an article</li>
 * 			<li>&titlelink[]=<i>article</i>&titlelink[]=<i>article</i>... for a list of articles</li>
 * 		</ul>
 * 	</li>
 * 	<li>?external=news
 * 		<ul>
 * 			<li>&titlelink=<i>page</i> for a page</li>
 * 			<li>&titlelink[]=<i>page</i>&titlelink[]=<i>page</i>... for a list of pages</li>
 * 	 </ul>
 * 	</li>
 * </ul>
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage feed
 */
// force UTF-8 Ø

$plugin_is_filter = 900 | FEATURE_PLUGIN | ADMIN_PLUGIN;
$plugin_description = gettext('The Zenphoto <em>externalFeed</em> handler.');

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
			setOptionDefault('externalFeed_sortorder_albums', 'latest');
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
										'desc'			 => gettext('Check each feeds you wish to activate.')),
						gettext('Image feed items:')					 => array('key'		 => 'externalFeed_items', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1,
										'desc'	 => gettext("The number of new images and comments you want to appear in your site’s feed")),
						gettext('Image size')									 => array('key'		 => 'externalFeed_imagesize', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 3,
										'desc'	 => gettext('Size of image feed images:')),
						gettext('Image feed sort order:')			 => array('key'				 => 'externalFeed_sortorder', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 7,
										'selections' => array(gettext('latest by id')					 => 'latest',
														gettext('latest by date')				 => 'latest-date',
														gettext('latest by mtime')			 => 'latest-mtime',
														gettext('latest by publishdate') => 'latest-publishdate'
										),
										'desc'			 => gettext("Choose between latest by id for the latest uploaded, latest by date for the latest uploaded fetched by date, or latest by mtime for the latest uploaded fetched by the file’s last change timestamp.")),
						gettext('Album feed sort order:')			 => array('key'				 => 'externalFeed_sortorder_albums', 'type'			 => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('latest by id')					 => 'latest',
														gettext('latest by date')				 => 'latest-date',
														gettext('latest by mtime')			 => 'latest-mtime',
														gettext('latest by publishdate') => 'latest-publishdate',
														gettext('latest updated')				 => 'latestupdated'
										),
										'order'			 => 8,
										'desc'			 => gettext('In addition to the above you may select latest updated.')),
						gettext('New requestor:')							 => array('key'		 => 'externalFeed_site', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 9,
										'desc'	 => gettext("Supply a site name to add a new using site.")),
						gettext('Registered sites:')					 => array('key'		 => 'externalFeed_sitelist', 'type'	 => OPTION_TYPE_CUSTOM,
										'order'	 => 9,
										'desc'	 => gettext("Check the box to remove a site."))
		);
		if (extensionEnabled('zenpage')) {
			$options[gettext('Feed text length')] = array('key'		 => 'externalFeed_truncate_length', 'type'	 => OPTION_TYPE_TEXTBOX,
							'order'	 => 6,
							'desc'	 => gettext("The text length of the Zenpage feed items. No value for full length."));
			$options[gettext('News feed items')] = array('key'		 => 'externalFeed_zenpage_items', 'type'	 => OPTION_TYPE_TEXTBOX,
							'order'	 => 5,
							'desc'	 => gettext("The number of news articles you want to appear in your site’s News feed."));
		}

		return $options;
	}

	function handleOption($option, $currentValue) {
		$count = 0;
		$result = query('SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="externalFeed" ORDER BY `aux`');
		if ($result) {
			$list = array();
			while ($row = db_fetch_assoc($result)) {
				$count++;
				$key = $row['data'];
				$site = $row['aux'];
				?>
				<div>
					<label><?php printf(gettext('<em><strong>%1$s</strong></em> key=%2$s'), $site, $key); ?> <input type="checkbox" name="externalFeed_delete_<?php echo $site; ?>" /></label>
				</div>
				<?php
			}
		}
		if (!$count)
			echo gettext('No sites registered');
	}

	function handleOptionSave($themename, $themealbum) {
		if ($site = getOption('externalFeed_site')) {
			purgeOption('externalFeed_site');
			$key = md5($site . serialize($_SERVER));
			query('INSERT INTO ' . prefix('plugin_storage') . ' (`type`, `aux`,`data`) VALUES ("externalFeed",' . db_quote($site) . ',' . db_quote($key) . ') ON DUPLICATE KEY UPDATE `data`=' . db_quote($key));
		}
		foreach ($_POST as $option => $value) {
			if (strpos($option, 'externalFeed_delete_') !== false) {
				$site = str_replace('externalFeed_delete_', '', $option);
				query('DELETE FROM ' . prefix('plugin_storage') . ' WHERE `type`="externalFeed" AND `aux`=' . db_quote($site));
			}
		}
		return false;
	}

}

require_once(SERVERPATH . '/' . ZENFOLDER . '/class-feed.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/lib-MimeTypes.php');

class ExternalFeed extends feed {

	protected $feed = 'externalFeed';
	protected $key;

	/**
	 * Creates a feed object from the URL parameters fetched only
	 *
	 */
	function __construct($options = NULL) {
		global $_zp_gallery, $_zp_current_admin_obj, $_zp_loggedin;
		if (empty($options))
			self::feed404();

		$this->feedtype = $options['external'];
		$this->key = @$options['accesskey'];
		parent::__construct($options);


		if ($this->key) {
			$result = query_single_row('SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="externalFeed" AND `data`=' . db_quote($this->key));
			if (!$result) {
				$this->key = NULL;
			}
		}
		if (!$this->key && $this->feedtype != 'site_closed')
			self::feed404();
// general feed setup
		$channeltitlemode = getOption('externalFeed_title');
		$this->host = html_encode($_SERVER["HTTP_HOST"]);

//channeltitle general
		switch ($channeltitlemode) {
			case 'gallery':
				$this->channel_title = $_zp_gallery->getBareTitle($this->locale);
				break;
			case 'website':
				$this->channel_title = getBare($_zp_gallery->getWebsiteTitle($this->locale));
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

				$this->channel_title = html_encode($this->channel_title . ' ' . getBare($albumname));
				$this->imagesize = $this->getImageSize();
				require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/image_album_statistics.php');
				break;

			case 'news': //Zenpage News
				if (!getOption('externalFeed_articles')) {
					self::feed404();
				}
				$titleappendix = gettext(' (Latest news)');

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
			case 'null': //we just want the class instantiated
				return;
		}
		$this->feeditems = $this->getitems();
	}

	/**
	 * Gets the feed item data in a gallery feed
	 *
	 * @param object $item Object of an image or album
	 * @return array
	 */
	protected function getItemGallery($item) {
		if ($this->mode == "albums") {
			$albumobj = $item;
			$totalimages = $albumobj->getNumImages();
			$itemlink = $this->host . $albumobj->getLink();
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
			if (isAlbumClass($item)) {
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
		$link = $obj->getLink();
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
		$items = array();
		if (($album = @$this->options['album'])) {
			if ($image = @$this->options['image']) {
				if (!is_array($image)) {
					$image = array($image);
				}
				foreach ($image as $filename) {
					$obj = newImage(NULL, array('folder' => $album, 'filename' => $filename), true);
					if ($obj->exists) {
						$items[] = $obj;
					}
				}
			} else {
				if (!is_array($album)) {
					$album = array($album);
				}
				foreach ($album as $folder) {
					$obj = newAlbum($folder, true);
					if ($obj->exists) {
						$items[] = $obj;
					}
				}
			}
			return $items;
		}

		if ($this->feedtype == 'news' && $news = @$this->options['titlelink']) {
			if (!is_array($news)) {
				$news = array($news);
			}
			foreach ($news as $article) {
				$obj = new ZenpageNews($article, false);
				if ($obj->loaded) {
					$items[] = array('titlelink' => $article);
				}
			}
			return $items;
		}
		if ($this->feedtype == 'pages' && $pages = @$this->options['titlelink']) {
			if (!is_array($pages)) {
				$pages = array($pages);
			}
			foreach ($pages as $page) {
				$obj = new ZenpagePage($page, false);
				if ($obj->loaded) {
					$items[] = array('titlelink' => $page);
				}
			}
			return $items;
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
			header('Content-Type: application/xml');
			?>
			<external version="1.0" >
				<?php
				if ($this->key) {
					$key = md5($this->key . serialize($_SERVER));
					query('UPDATE ' . prefix('plugin_storage') . ' SET `data`=' . db_quote($key) . ' WHERE `type`="externalFeed" AND `data`=' . db_quote($this->key));
					?>
					<accesskey><?php echo $key; ?></accesskey>
					<?php
				}
				?>

				<channel>
					<link href="<?php echo PROTOCOL; ?>://<?php echo $this->host; ?><?php echo html_encode(getRequestURI()); ?>" />
					<language><?php echo $this->locale_xml; ?></language>
					<?php
					foreach ($feeditems as $feeditem) {

						switch
						($this->feedtype) {
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