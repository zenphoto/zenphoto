<?php
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions.php');

/**
 *
 * Base feed class from which all others descend.
 *
 * Plugins will set the <var>feedtype</var> property to the feed desired
 * <ul>
 * 	<li>gallery</li>
 * 	<li>news</li>
 * 	<li>pages</li>
 * 	<li>comments</li>
 * </ul>
 *
 * Feed details are determined by the <var>option</var> property.
 * Elements of this array and their meaning follow:
 * <ul>
 * 	<li>lang
 * 		<ul>
 * 			<li><i>locale</i></li>
 * 		</ul>
 * 	</li>
 * 	<li>sortdir
 * 		<ul>
 * 			<li>desc (default) for descending order</li>
 * 			<li>asc for ascending order</li>
 * 		</ul>
 * 	</li>
 * 	<li>sortorder</li>
 * 		<ul>
 * 			<li><i>latest</i> (default) for the latest uploaded by id (discovery order)</li>
 * 			<li><i>latest-date</i> for the latest fetched by date</li>
 * 			<li><i>latest-mtime</i> for the latest fetched by mtime</li>
 * 			<li><i>latest-publishdate</i> for the latest fetched by publishdate</li>
 * 			<li><i>popular</i> for the most popular albums</li>
 * 			<li><i>topratedv for the best voted</li>
 * 			<li><i>mostrated</i> for the most voted</li>
 * 			<li><i>random</i> for random order</li>
 * 			<li><i>id</i> internal <var>id</var> order</li>
 * 		</ul>
 * 	</li>
 * 	<li>albumname</li>
 * 	<li>albumsmode</li>
 * 	<li>folder</li>
 * 	<li>size</li>
 * 	<li>category</li>
 * 	<il>id</li>
 * 	<li>itemnumber</li>
 * 	<li>type (for comments feed)
 * 		<ul>
 * 			<li>albums</li>
 * 			<li>images</li>
 * 			<li>pages</li>
 * 			<li>news</li>
 * 		</ul>
 * 	</li>
 * </ul>
 *
 *
 * @package zpcore\classes\objects
 */
class feed {

	protected $feed = 'feed'; //	feed type
	protected $mode; //	feed mode
	protected $options; // This array will store the options for the feed.
	//general feed type gallery, news or comments
	protected $feedtype = NULL;
	protected $itemnumber = NULL;
	protected $locale = NULL; // standard locale for lang parameter
	protected $locale_xml = NULL; // xml locale within feed
	protected $sortorder = NULL;
	protected $sortdirection = NULL;
	//gallery feed specific vars
	protected $albumfolder = NULL;
	protected $collection = NULL;
	protected $albumpath = NULL;
	protected $imagepath = NULL;
	protected $imagesize = NULL;
	protected $modrewritesuffix = NULL;
	// Zenpage news feed specific
	protected $catlink = NULL;
	protected $cattitle = NULL;
	protected $newsoption = NULL;
	protected $titleappendix = NULL;
	//comment feed specific
	protected $id = NULL;
	protected $commentfeedtype = NULL;
	protected $itemobj = NULL; // if comments for an item its object
	//channel vars
	protected $channel_title = NULL;
	protected $feeditem = array();

	/**
	 * Creates a file name from the options array
	 *
	 * @return string
	 */
	protected function getCacheFilename() {
		$filename = array();
		foreach ($this->options as $key => $value) {
			if (empty($value) || $key == 'albumsmode') { // supposed to be empty always
				$filename[] = $key;
			} else {
				$filename[] = $value;
			}
		}
		$filename = seoFriendly(implode('_', $filename));
		return $filename . ".xml";
	}

	/**
	 * Starts static caching
	 *
	 */
	protected function startCache() {
		$caching = getOption($this->feed . "_cache") && !zp_loggedin();
		if ($caching) {
			$cachefilepath = SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/' . strtolower($this->feed) . '/' . internalToFilesystem($this->getCacheFilename());
			if (file_exists($cachefilepath) AND time() - filemtime($cachefilepath) < getOption($this->feed . "_cache_expire")) {
				echo file_get_contents($cachefilepath);
				exitZP();
			} else {
				if (file_exists($cachefilepath)) {
					@chmod($cachefilepath, 0777);
					@unlink($cachefilepath);
				}
				ob_start();
			}
		}
	}

	/**
	 * Ends the static caching.
	 *
	 */
	protected function endCache() {
		$caching = getOption($this->feed . "_cache") && !zp_loggedin();
		if ($caching) {
			$cachefilepath = internalToFilesystem($this->getCacheFilename());
			if (!empty($cachefilepath)) {
				$cachefilepath = SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/' . strtolower($this->feed) . '/' . $cachefilepath;
				mkdir_recursive(SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/' . strtolower($this->feed) . '/', FOLDER_MOD);
				$pagecontent = ob_get_contents();
				ob_end_clean();
				if ($fh = @fopen($cachefilepath, "w")) {
					fputs($fh, $pagecontent);
					fclose($fh);
					clearstatcache();
				}
				echo $pagecontent;
			}
		}
	}

	/**
	 * Cleans out the cache folder
	 *
	 * @param string $cachefolder the sub-folder to clean
	 */
	function clearCache($cachefolder = NULL) {
		removeDir(SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/' . strtolower($this->feed) . '/' . $cachefolder, true);
	}

	function __construct($options) {
		$this->options = $options;
		$invalid_options = array();
		$this->locale = $this->getLang();
		$this->locale_xml = strtr($this->locale, '_', '-');
		$this->sortdirection = $this->getSortdir();
		$this->sortorder = $this->getSortorder();
		switch ($this->feedtype) {
			case 'comments':
				$this->commentfeedtype = $this->getCommentFeedType();
				$this->id = $this->getId();
				$invalid_options = array('albumsmode', 'folder', 'albumname', 'category', 'size');
				break;
			case 'gallery':
				if (isset($this->options['albumsmode'])) {
					$this->mode = 'albums';
				}
				if (isset($this->options['folder'])) {
					$this->albumfolder = $this->getAlbum('folder');
					$this->collection = true;
				} else if (isset($this->options['albumname'])) {
					$this->albumfolder = $this->getAlbum('albumname');
					$this->collection = false;
				} else {
					$this->collection = false;
				}
				if (is_null($this->sortorder)) {
					if ($this->mode == "albums") {
						$this->sortorder = getOption($this->feed . "_sortorder_albums");
					} else {
						$this->sortorder = getOption($this->feed . "_sortorder");
					}
				}
				$this->imagesize = $this->getImageSize();
				$invalid_options = array('id', 'type', 'category');
				break;
			case 'news':
				if ($this->sortorder == 'latest') {
					$this->sortorder = NULL;
				}
				$this->catlink = $this->getCategory();
				if (!empty($this->catlink)) {
					$catobj = new ZenpageCategory($this->catlink);
					$this->cattitle = $catobj->getTitle();
					$this->newsoption = 'category';
				} else {
					$this->catlink = '';
					$this->cattitle = '';
					$this->newsoption = 'news';
				}
				$invalid_options = array('folder', 'albumname', 'albumsmode', 'type', 'id', 'size');
				break;
			case 'pages':
				$invalid_options = array('folder', 'albumname', 'albumsmode', 'type', 'id', 'category', 'size');
				break;
			case 'null': //we just want the class instantiated
				return;
		}
		$this->unsetOptions($invalid_options); // unset invalid options that this feed type does not support
		if (isset($this->options['itemnumber'])) {
			$this->itemnumber = (int) $this->options['itemnumber'];
		} else {
			$this->itemnumber = getOption($this->feed . '_items');
		}
	}

	/**
	 * Validates and gets the "lang" parameter option value 
	 * 
	 * @global array $_zp_active_languages
	 * @return string
	 */
	protected function getLang() {
		if (isset($this->options['lang'])) {
			$langs = generateLanguageList();
			$valid = array_values($langs);
			if (in_array($this->options['lang'], $valid)) {
				return $this->options['lang'];
			}
		}
		return getOption('locale');
	}

	/**
	 * Validates and gets the "sortdir" parameter option value 
	 * 
	 * @return bool
	 */
	protected function getSortdir() {
		$valid = array('desc', 'asc');
		if (isset($this->options['sortdir']) && in_array($this->options['sortdir'], $valid)) {
			return strtolower($this->options['sortdir']) != 'asc';
		}
		$this->options['sortdir'] = 'desc'; // make sure this is a valid default name
		return true;
	}

	/**
	 * Validates and gets the "sortorder" parameter option value 
	 * 
	 * @return string
	 */
	protected function getSortorder() {
		if (isset($this->options['sortorder'])) {
			$valid = array('latest', 'latest-date', 'latest-mtime', 'latest-publishdate', 'popular', 'toprated', 'mostrated', 'random', 'id');
			if (in_array($this->options['sortorder'], $valid)) {
				$this->options['sortdir'] = $this->options['sortorder']; // make sure this is a valid default name
				return $this->options['sortorder'];
			} else {
				$this->unsetOptions(array('sortorder'));
			}
		}
		return null;
	}

	/**
	 * Validates and gets the "type" parameter option value for comment feeds
	 * 
	 * @return string
	 */
	protected function getCommentFeedType() {
		$valid = false;
		if (isset($this->options['type'])) {
			$valid = array('all', 'album', 'image', 'news', 'page', 'zenpage', 'gallery');
			if (in_array($this->options['type'], $valid)) {
				return $this->options['type'];
			}
		}
		return 'all';
	}

	/**
	 * Validates and gets the "id" parameter option value for comments feeds of a specific item
	 * 
	 * @return int
	 */
	protected function getID() {
		global $_zp_db;
		if (isset($this->options['id'])) {
			$type = $this->getCommentFeedType();
			$table = '';
			if ($type != 'all') {
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
				}
				if ($table) {
					$id = (int) $this->options['id'];
					$result = $_zp_db->querySingleRow('SELECT `id` FROM ' . $_zp_db->prefix($table) . ' WHERE id =' . $id);
					if ($result) {
						return $id;
					}
				}
			}
		}
		$this->unsetOptions(array('id'));
		return '';
	}

	/**
	 * Validates and gets the "folder" or 'albumname" parameter option value
	 * @param string $option "folder" or "albumname"
	 * @return int
	 */
	protected function getAlbum($option) {
		if (in_array($option, array('folder', 'albumname')) && isset($this->options[$option])) {
			$albumobj = AlbumBase::newAlbum($this->options[$option], true, true);
			if ($albumobj->exists) {
				return $this->options[$option];
			}
		}
		$this->unsetOptions(array($option));
		return '';
	}

	/**
	 * Validates and gets the "category" parameter option value
	 * 
	 * @return int
	 */
	protected function getCategory() {
		if (isset($this->options['category']) && class_exists('ZenpageCategory')) {
			$catobj = new ZenpageCategory($this->options['category']);
			if ($catobj->exists) {
				return $this->options['category'];
			}
		}
		$this->unsetOptions(array('category'));
		return '';
	}

	/**
	 * Helper function that gets the images size of the "size" get parameter
	 *
	 * @return string
	 */
	protected function getImageSize() {
		if (isset($this->options['size'])) {
			$imagesize = (int) $this->options['size'];
		} else {
			$imagesize = NULL;
		}
		if ($this->mode == 'albums') {
			if (is_null($imagesize) || $imagesize > getOption($this->feed . '_imagesize_albums')) {
				$imagesize = getOption($this->feed . '_imagesize_albums'); // un-cropped image size
			}
		} else {
			if (is_null($imagesize) || $imagesize > getOption($this->feed . '_imagesize')) {
				$imagesize = getOption($this->feed . '_imagesize'); // un-cropped image size
			}
		}
		return $imagesize;
	}

	/**
	 * Unsets certain option name indices from the $options property.
	 * @param array $options Array of option (parameter) names to be unset
	 */
	protected function unsetOptions($options = null) {
		if (!empty($options)) {
			foreach ($options as $option) {
				unset($this->options[$option]);
			}
		}
	}

	protected function getChannelTitleExtra() {
		switch ($this->sortorder) {
			default:
			case 'latest':
			case 'latest-date':
			case 'latest-mtime':
			case 'latest-publishdate':
				if ($this->mode == 'albums') {
					$albumextra = ' (' . gettext('Latest albums') . ')'; //easier to understand for translators as if I would treat "images"/"albums" in one place separately
				} else {
					$albumextra = ' (' . gettext('Latest images') . ')';
				}
				break;
			case 'latestupdated':
				$albumextra = ' (' . gettext('latest updated albums') . ')';
				break;
			case 'popular':
				if ($this->mode == 'albums') {
					$albumextra = ' (' . gettext('Most popular albums') . ')';
				} else {
					$albumextra = ' (' . gettext('Most popular images') . ')';
				}
				break;
			case 'toprated':
				if ($this->mode == 'albums') {
					$albumextra = ' (' . gettext('Top rated albums') . ')';
				} else {
					$albumextra = ' (' . gettext('Top rated images') . ')';
				}
				break;
			case 'random':
				if ($this->mode == 'albums') {
					$albumextra = ' (' . gettext('Random albums') . ')';
				} else {
					$albumextra = ' (' . gettext('Random images') . ')';
				}
				break;
		}
		return $albumextra;
	}

	/**
	 * Gets the feed items
	 *
	 * @return array
	 */
	public function getitems() {
		global $_zp_zenpage;
		switch ($this->feedtype) {
			case 'gallery':
				if ($this->mode == "albums") {
					$items = getAlbumStatistic($this->itemnumber, $this->sortorder, $this->albumfolder, $this->sortdirection);
				} else {
					$items = getImageStatistic($this->itemnumber, $this->sortorder, $this->albumfolder, $this->collection, 0, $this->sortdirection);
				}
				break;
			case 'news':
				switch ($this->newsoption) {
					case "category":
						if ($this->sortorder) {
							$items = getZenpageStatistic($this->itemnumber, 'categories', $this->sortorder, $this->sortdirection);
						} else {
							$items = getLatestNews($this->itemnumber, $this->catlink, false, $this->sortdirection);
						}
						break;
					default:
					case "news":
						if ($this->sortorder) {
							$items = getZenpageStatistic($this->itemnumber, 'news', $this->sortorder, $this->sortdirection);
						} else {
							// Needed baceause type variable "news" is used by the feed item method and not set by the class method getArticles!
							$items = getLatestNews($this->itemnumber, '', false, $this->sortdirection);
						}
						break;
				}
				break;
			case "pages":
				if ($this->sortorder) {
					$items = getZenpageStatistic($this->itemnumber, 'pages', $this->sortorder, $this->sortdirection);
				} else {
					$items = $_zp_zenpage->getPages(NULL, false, $this->itemnumber);
				}
				break;
			case 'comments':
				switch ($this->commentfeedtype) {
					case 'album':
						$items = getLatestComments($this->itemnumber, 'album', $this->id);
						break;
					case 'image':
						$items = getLatestComments($this->itemnumber, 'image', $this->id);
						break;
					case 'news':
					case 'page':
						if (function_exists('getLatestZenpageComments')) {
							$items = getLatestZenpageComments($this->itemnumber, $this->commentfeedtype, $this->id);
						}
						break;
					case 'all':
						$items = getLatestComments($this->itemnumber);
						break;
					case 'gallery':
						$items = getLatestComments($this->itemnumber, ['albums', 'images']);
						break;
					case 'zenpage':
						$items = getLatestZenpageComments($this->itemnumber);
						break;
				}
				break;
		}
		if (isset($items)) {
			return $items;
		}
		if (TEST_RELEASE) {
			trigger_error(gettext('Bad ' . $this->feed . ' feed respectively no items available:' . $this->feedtype), E_USER_WARNING);
		}
		return NULL;
	}

	/**
	 * Gets the feed item data in a Zenpage news feed
	 *
	 * @param array $item Titlelink a Zenpage article or filename of an image if a combined feed
	 * @return array
	 */
	protected function getitemPages($item, $len) {
		$obj = new ZenpagePage($item['titlelink']);
		$feeditem['title'] = $feeditem['title'] = get_language_string($obj->getTitle('all'), $this->locale);
		$feeditem['link'] = $obj->getLink(FULLWEBPATH);
		$desc = $obj->getContent($this->locale);
		$desc = str_replace('//<![CDATA[', '', $desc);
		$desc = str_replace('//]]>', '', $desc);
		$feeditem['desc'] = shortenContent($desc, $len, '...');
		$feeditem['enclosure'] = '';
		$feeditem['category'] = '';
		$feeditem['media_content'] = '';
		$feeditem['media_thumbnail'] = '';
		$feeditem['pubdate'] = date("r", strtotime($obj->getDatetime()));
		return zp_apply_filter('feed_page', $feeditem, $obj);
	}

	/**
	 * Gets the feed item data in a comments feed
	 *
	 * @param array $item Array of a comment
	 * @return array
	 */
	protected function getitemComments($item) {
		if ($item['anon']) {
			$author = "";
		} else {
			$author = " " . gettext("by") . " " . $item['name'];
		}
		$commentpath = $imagetag = $title = '';
		$obj = null;
		switch ($item['type']) {
			case 'images':
				$title = get_language_string($item['title']);
				$obj = Image::newImage(NULL, array('folder' => $item['folder'], 'filename' => $item['filename']));
				$link = $obj->getLink(FULLWEBPATH);
				$feeditem['pubdate'] = date("r", strtotime($item['date']));
				$category = get_language_string($item['albumtitle']);
				$title = $category . ": " . $title;
				$commentpath = $link . "#zp_comment_id_" . $item['id'];
				break;
			case 'albums':
				$obj = AlbumBase::newAlbum($item['folder']);
				$link = rtrim($obj->getLink(1, FULLWEBPATH), '/');
				$feeditem['pubdate'] = date("r", strtotime($item['date']));
				$title = get_language_string($item['albumtitle']);
				$commentpath = $link . "#zp_comment_id_" . $item['id'];
				break;
			case 'news':
			case 'pages':
				if (extensionEnabled('zenpage')) {
					$feeditem['pubdate'] = date("r", strtotime($item['date']));
					$category = '';
					$title = get_language_string($item['title']);
					$titlelink = $item['titlelink'];
					if ($item['type'] == 'news') {
						$obj = new ZenpageNews($titlelink);
					} else {
						$obj = new ZenpagePage($titlelink);
					}
					$commentpath = $obj->getLink(FULLWEBPATH) . "#zp_comment_id_" . $item['id'];
				} else {
					$commentpath = '';
				}

				break;
		}
		$feeditem['title'] = getBare($title . $author);
		$feeditem['link'] = $commentpath;
		$feeditem['desc'] = $item['comment'];
		return zp_apply_filter('feed_comment', $feeditem, $item, $obj);
	}

	static protected function feed404() {
		header("HTTP/1.0 404 Not Found");
		header("Status: 404 Not Found");
		include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
		exitZP();
	}

}