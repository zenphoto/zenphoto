<?php

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
 * 	<li>withimages</li>
 * 	<li>withimages_mtime</li>
 * 	<li>withimages_publishdate</li>
 * 	<li>withalbums</li>
 * 	<li>withalbums_mtim</li>
 * 	<li>withalbums_publishdate</li>
 * 	<li>withalbums_publishdate</li>
 * </ul>
 *
 *
 * @package classes
 */
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions.php');

class feed {

	protected $feed = 'feed'; //	feed type
	protected $mode; //	feed mode
	protected $options; // This array will store the options for the feed.
	//general feed type gallery, news or comments
	protected $feedtype = NULL;
	protected $itemnumber = NULL;
	protected $locale = NULL; // standard locale for lang parameter
	protected $locale_xml = NULL; // xml locale within feed
	protected $host = NULL;
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
	protected $combinews_images = NULL;
	protected $combinews_albums = NULL;
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
			if (empty($value)) {
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
			$cachefilepath = SERVERPATH . '/cache_html/' . strtolower($this->feed) . '/' . internalToFilesystem($this->getCacheFilename());
			if (file_exists($cachefilepath) AND time() - filemtime($cachefilepath) < getOption($this->feed . "_cache_expire")) {
				echo file_get_contents($cachefilepath);
				exitZP();
			} else {
				if (file_exists($cachefilepath)) {
					@chmod($cachefilepath, 0666);
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
				$cachefilepath = SERVERPATH . '/cache_html/' . strtolower($this->feed) . '/' . $cachefilepath;
				mkdir_recursive(SERVERPATH . '/cache_html/' . strtolower($this->feed) . '/', FOLDER_MOD);
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
		zpFunctions::removeDir(SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/' . strtolower($this->feed) . '/' . $cachefolder, true);
	}

	function __construct($options) {
		$this->options = $options;
		if (isset($this->options['lang'])) {
			$this->locale = $this->options['lang'];
		} else {
			$this->locale = getOption('locale');
		}
		$this->locale_xml = strtr($this->locale, '_', '-');
		if (isset($this->options['sortdir'])) {
			$this->sortdirection = $this->options['sortdir'];
			if ($this->sortdirection = !'desc' || $sortdir != 'asc') {
				$this->sortdirection = 'desc';
			}
		} else {
			$this->sortdirection = 'desc';
		}
		if (isset($this->options['sortorder'])) {
			$this->sortorder = $this->options['sortorder'];
		} else {
			$this->sortorder = NULL;
		}
		switch ($this->feedtype) {
			case 'comments':
				if (isset($this->options['type'])) {
					$this->commentfeedtype = $this->options['type'];
				} else {
					$this->commentfeedtype = 'all';
				}
				if (isset($this->options['id'])) {
					$this->id = (int) $this->options['id'];
				}
				break;
			case 'gallery':
				if (isset($this->options['albumsmode'])) {
					$this->mode = 'albums';
				}
				if (isset($this->options['folder'])) {
					$this->albumfolder = $this->options['folder'];
					$this->collection = true;
				} else if (isset($this->options['albumname'])) {
					$this->albumfolder = $this->options['albumname'];
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
				break;
			case 'news':
				if ($this->sortorder == 'latest') {
					$this->sortorder = NULL;
				}

				if (isset($this->options['category'])) {
					$this->catlink = $this->options['category'];
					$catobj = new ZenpageCategory($this->catlink);
					$this->cattitle = $catobj->getTitle();
					$this->newsoption = 'category';
				} else {
					$this->catlink = '';
					$this->cattitle = '';
					$this->newsoption = 'news';
				}

				if (isset($this->options['withimages'])) {
					$this->sortorder = NULL;
					return $this->newsoption = 'withimages';
				} else if (isset($this->options['withimages_mtime'])) {
					return $this->newsoption = 'withimages_mtime';
				} else if (isset($this->options['withimages_publishdate'])) {
					return $this->newsoption = 'withimages_publishdate';
				}

				if (isset($this->options['withalbums'])) {
					$this->sortorder = NULL;
					return $this->newsoption = 'withalbums';
				} else if (isset($this->options['withalbums_mtime'])) {
					return $this->newsoption = 'withalbums_mtime';
				} else if (isset($this->options['withalbums_publishdate'])) {
					return $this->newsoption = 'withalbums_publishdate';
				} else if (isset($this->options['withalbums_latestupdated'])) {
					return $this->newsoption = 'withalbums_latestupdated';
				}
				break;
			case 'pages':
				break;
		}
		if (isset($this->options['itemnumber'])) {
			$this->itemnumber = (int) $this->options['itemnumber'];
		} else {
			$this->itemnumber = getOption($this->feed . '_items');
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
		if (is_numeric($imagesize) && !is_null($imagesize) && $imagesize < getOption($this->feed . '_imagesize')) {
			$imagesize = $imagesize;
		} else {
			if ($this->mode == 'albums') {
				$imagesize = getOption($this->feed . '_imagesize_albums'); // un-cropped image size
			} else {
				$imagesize = getOption($this->feed . '_imagesize'); // un-cropped image size
			}
		}
		return $imagesize;
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
							$items = getLatestNews($this->itemnumber, "none", $this->catlink, false, $this->sortdirection);
						}
						break;
					case "news":
						if ($this->sortorder) {
							$items = getZenpageStatistic($this->itemnumber, 'news', $this->sortorder, $this->sortdirection);
						} else {
							// Needed baceause type variable "news" is used by the feed item method and not set by the class method getArticles!
							$items = getLatestNews($this->itemnumber, 'none', '', false, $this->sortdirection);
						}
						break;
					case "withimages":
						//$items = getLatestNews($this->itemnumber,"with_latest_images_date",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber, 'latestimages-thumbnail', NULL, 'date', false, $this->sortdirection);
						break;
					case "withimages_id":
						//$items = getLatestNews($this->itemnumber,"with_latest_images_date",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber, 'latestimages-thumbnail', NULL, 'id', false, $this->sortdirection);
						break;
					case 'withimages_mtime':
						//$items = getLatestNews($this->itemnumber,"with_latest_images_mtime",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber, 'latestimages-thumbnail', NULL, 'mtime', false, $this->sortdirection);
						break;
					case 'withimages_publishdate':
						//$items = getLatestNews($this->itemnumber,"with_latest_images_publishdate",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber, 'latestimages-thumbnail', NULL, 'publishdate', false, $this->sortdirection);
						break;
					case 'withalbums':
						//$items = getLatestNews($this->itemnumber,"with_latest_albums_date",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber, 'latestalbums-thumbnail', NULL, 'date', false, $this->sortdirection);
						break;
					case 'withalbums_mtime':
						//$items = getLatestNews($this->itemnumber,"with_latest_albums_mtime",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber, 'latestalbums-thumbnail', NULL, 'mtime', false, $this->sortdirection);
						break;
					case 'withalbums_publishdate':
						//$items = getLatestNews($this->itemnumber,"with_latest_albums_publishdate",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber, 'latestalbums-thumbnail', NULL, 'publishdate', false, $this->sortdirection);
						break;
					case 'withalbums_latestupdated':
						//$items = getLatestNews($this->itemnumber,"with_latestupdated_albums",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber, 'latestupdatedalbums-thumbnail', NULL, '', false, $this->sortdirection);
						break;
				}
				break;
			case "pages":
				if ($this->sortorder) {
					$items = getZenpageStatistic($this->itemnumber, 'pages', $this->sortorder, $this->sortdirection);
				} else {
					$items = $_zp_zenpage->getPages(NULL, false, $this->itemnumber, $this->sortorder, $this->sortdirection);
				}
				break;
			case 'comments':
				switch ($type = $this->commentfeedtype) {
					case 'gallery':
						$items = getLatestComments($this->itemnumber, 'all');
						break;
					case 'album':
						$items = getLatestComments($this->itemnumber, 'album', $this->id);
						break;
					case 'image':
						$items = getLatestComments($this->itemnumber, 'image', $this->id);
						break;
					case 'zenpage':
						$type = 'all';
					case 'news':
					case 'page':
						if (function_exists('getLatestZenpageComments')) {
							$items = getLatestZenpageComments($this->itemnumber, $type, $this->id);
						}
						break;
					case 'allcomments':
						$items = getLatestComments($this->itemnumber, 'all');
						$items_zenpage = array();
						if (function_exists('getLatestZenpageComments')) {
							$items_zenpage = getLatestZenpageComments($this->itemnumber, 'all', $this->id);
							$items = array_merge($items, $items_zenpage);
							$items = sortMultiArray($items, 'date', true);
							$items = array_slice($items, 0, $this->itemnumber);
						}
						break;
				}
				break;
		}
		if (isset($items)) {
			return $items;
		}
		if (TEST_RELEASE) {
			trigger_error(gettext('Bad ' . $this->feed . ' feed:' . $this->feedtype), E_USER_WARNING);
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
		$feeditem['link'] = getPageLinkURL($obj->getTitlelink());
		$feeditem['desc'] = shortenContent($obj->getContent($this->locale), $len, '...');
		$feeditem['enclosure'] = '';
		$feeditem['category'] = '';
		$feeditem['media_content'] = '';
		$feeditem['media_thumbnail'] = '';
		$feeditem['pubdate'] = date("r", strtotime($obj->getDatetime()));
		return $feeditem;
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
		switch ($item['type']) {
			case 'images':
				$title = get_language_string($item['title']);
				$obj = newImage(NULL, array('folder'	 => $item['folder'], 'filename' => $item['filename']));
				$link = $obj->getImagelink();
				$feeditem['pubdate'] = date("r", strtotime($item['date']));
				$category = $item['albumtitle'];
				$website = $item['website'];
				if ($item['type'] == 'albums') {
					$title = $category;
				} else {
					$title = $category . ": " . $title;
				}
				$commentpath = PROTOCOL . '://' . $this->host . html_encode($link) . "#" . $item['id'];
				break;
			case 'albums':
				$obj = newAlbum($item['folder']);
				$link = rtrim($obj->getAlbumlink(), '/');
				$feeditem['pubdate'] = date("r", strtotime($item['date']));
				$category = $item['albumtitle'];
				$website = $item['website'];
				if ($item['type'] == 'albums') {
					$title = $category;
				} else {
					$title = $category . ": " . $title;
				}
				$commentpath = PROTOCOL . '://' . $this->host . html_encode($link) . "#" . $item['id'];
				break;
			case 'news':
			case 'pages':
				$album = '';
				$feeditem['pubdate'] = date("r", strtotime($item['date']));
				$category = '';
				$title = get_language_string($item['title']);
				$titlelink = $item['titlelink'];
				$website = $item['website'];
				if (function_exists('getNewsURL')) {
					if ($item['type'] == 'news') {
						$commentpath = PROTOCOL . '://' . $this->host . html_encode(getNewsURL($titlelink)) . "#" . $item['id'];
					} else {
						$commentpath = PROTOCOL . '://' . $this->host . html_encode(getPageLinkURL($titlelink)) . "#" . $item['id'];
					}
				} else {
					$commentpath = '';
				}
				break;
		}
		$feeditem['title'] = strip_tags($title . $author);
		$feeditem['link'] = $commentpath;
		$feeditem['desc'] = $item['comment'];
		return $feeditem;
	}

	static protected function feed404() {
		header("HTTP/1.0 404 Not Found");
		header("Status: 404 Not Found");
		include(ZENFOLDER . '/404.php');
		exitZP();
	}

}

?>