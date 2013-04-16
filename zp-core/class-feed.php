<?php

/** TODO: THis doc needs generalisation regarding "?rss"
 *
 * Base feed class from which all others descend.
 * Current status is that this is a place holder while we re-organize the RSS handling into
 * RSS specific items and move the common elements to this class.
 *
 *
 * The feed is dependent on GET parameters available.
 *
 * The gallery and additionally Zenpage CMS plugin provide rss link functions to generate context dependent rss links.
 * You can however create your own links manually with even some more options than via the backend option.
 *
 *
 * I. GALLERY RSS FEEDS:
 * These accept the following parameters:
 *
 * - "Gallery" feed for latest images of the whole gallery
 * index.php?rss&lang=<locale></li>
 *
 * - "Album" for latest images only of the album it is called from
 * index.php?rss&albumname=<album name>&lang=<locale>
 *
 * - "Collection" for latest images of the album it is called from and all of its subalbums
 * index.php?rss&folder=<album name>&lang=<locale>
 *
 * - "AlbumsRSS" for latest albums
 * index.php?rss&lang=<locale>&albumsmode';
 *
 * - "AlbumsRSScollection" only for latest subalbums with the album it is called from
 * index.php?rss&folder=<album name>&lang=<locale>&albumsmode';

 * - "Comments" for all comments of all albums and images
 * index.php?rss=comments&type=gallery&lang=<locale>
 *
 * - "Comments-image" for latest comments of only the image it is called from
 * index.php?rss=comments&id=<id of image>&type=image&lang=<locale>
 *
 * - "Comments-album" for latest comments of only the album it is called from
 * index.php?rss=comments&id=<album id>&type=album&lang=<locale>
 *
 * It is recommended to use urlencode() around the album names.
 *
 * Optional gallery feed parameters:
 * "sortorder" for "Gallery", "Album", "Collection" only with the following values (the same as the image_album_statistics plugin):
 * - "latest" for the latest uploaded by id (discovery order) (optional, used if sortorder is not set)
 * - "latest-date" for the latest fetched by date
 * - "latest-mtime" for the latest fetched by mtime
 * - "latest-publishdate" for the latest fetched by publishdate
 * - "popular" for the most popular albums
 * - "toprated" for the best voted
 * - "mostrated" for the most voted
 * - "random" for random order
 * "sortdir"
 * - "desc" (default) for descending order
 * - "asc" for ascending order
 *
 * Overrides the admin option value if set.
 *
 * "sortorder" for latest "AlbumsRSS" and "AlbumsRSScollection" only with the following values (the same as the image_album_statistics plugin):
 * - "latest" for the latest uploaded by ID (discovery order) (optional, used if sortorder is not set)
 * - "latest-date" for the latest fetched by date
 * - "latest-mtime" for the latest fetched by mtime
 * - "latest-publishdate" for the latest fetched by publishdate
 * - "popular" for the most popular albums,
 * - "toprated" for the best voted
 * - "mostrated" for the most voted
 * - "latestupdated" for the latest updated
 * - "random" for random order
 * "sortdir"
 * - "desc" (default) for descending order
 * - "asc" for ascending order
 *
 * Overrides the option value if set.
 *
 * Optional gallery feed parameters for all except comments:
 * "size" the pixel size for the image (uncropped and longest side)
 *
 *
 * II. RSS FEEDS WITH THE ZENPAGE CMS PLUGIN
 * Requires the plugin to be enabled naturally.
 *
 * a. NEWS ARTICLE FEEDS
 * - "News" feed for latest news articles
 * index.php?rss=news&lang=<locale>
 *
 * - "Category" for only the latest news articles of the category
 * index.php?rss=news&lang=<locale>&category=<titlelink of category>
 *
 * - "Comments" for all news articles and pages
 * index.php?rss=comments&type=zenpage&lang=<locale>
 *
 * - "Comments-news" for comments of only the news article it is called from
 * index.php?rss=comments&id=<news article id>&type=news&lang=<locale>
 *
 * - "Comments-page" for comments of only the page it is called from
 * index.php?rss=comments&id=<page id>&type=page&lang=<locale>
 *
 * - "Comments-all" for comments from all albums, images, news articels and pages
 * index.php?rss=comments&type=allcomments&lang=<locale>
 *
 * Optional parameters for "News" and "Category":
 * "sortorder  with these values:
 * - "latest" for latest articles. (If "sortorder" is not set at all "latest" order is used)
 * - "popular" for most viewed articles
 * - "mostrated" for most voted articles
 * - "toprated" for top voted articles
 * - "random" for random articles
 * - "id" by internal ID order
 *
 * b. COMBINEWS MODE RSS FEEDS (ARTICLES WITH IMAGES OR ALBUMS COMBINED)
 * NOTE: These override the sortorder parameter. You can also only set one of these parameters at the time. For other custom feed needs use the mergedRSS plugin.
 *
 * - "withimages" for all latest news articles and latest images by date combined
 * index.php?rss=news&withimages&lang=<locale>
 *
 * - "withimages_mtime" for all latest news articles and latest images by mtime combined
 * index.php?rss=news&withimages_mtime&lang=<locale>
 *
 * - "withimages_publishdate" for all latest news articles and latest images by publishdate combined
 * index.php?rss=news&withimages_publishdate&lang=<locale>
 *
 * - "withalbums" for all latest news articles and latest albums by date combined
 * index.php?rss=news&withimages_withalbums&lang=<locale>
 *
 * - "withalbums_mtime" for all latest news articles and latest images by mtime combined
 * index.php?rss=news&withalbums_mtime&lang=<locale>
 *
 * - "withalbums_publishdate" for all latest news articles and latest images by publishdate combined
 * index.php?rss=news&withalbums_publishdate&lang=<locale>
 *
 * - "withalbums_latestupdated" for all latest news articles and latest updated albums combined
 * index.php?rss=news&withalbums_latestupdated&lang=<locale>
 *
 * Optional CombiNews parameter:
 * "size" the pixel size for the image (uncropped and longest side)
 *
 * c. PAGES ARTICLE FEEDS
 * - "pages" feed for latest news articles
 * index.php?rss=pages&lang=<locale>
 *
 * Optional parameters:
 * "sortorder  with these values:
 * - "latest" for latest articles. (If "sortorder" is not set at all "latest" order is used)
 * - "popular" for most viewed articles
 * - "mostrated" for most voted articles
 * - "toprated" for top voted articles
 * - "random" for random articles
 * - "id" by internal ID
 * "sortdir"
 * - "desc" (default) for descending order
 * - "asc" for ascending order
 *
 * III. OPTIONAL PARAMETERS TO I. AND II.:
 * "itemnumber" for the number of items to get. If set overrides the admin option value.
 * "lang" for the language locale. Actually optional as well and if not set the currently set locale option is used.
 *
 * IV. INTENDED USAGE:
 * $rss = new RSS(); // gets parameters from the urls above
 * $rss->printFeed(); // prints xml feed
 *
 * @package classes
 */
class feed {
	protected $feed = 'feed';	//	feed type
	protected $mode;	//	feed mode

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
	protected $commentrsstype = NULL;
	protected $itemobj = NULL; // if comments for an item its object

	//channel vars
	protected $channel_title = NULL;
	protected $feeditem = array();

	/**
	 * each feed must override this function
	 */
	protected function getCacheFilename() {
		return NULL;
	}

	/**
	 * Starts static caching
	 *
	 */
	protected function startCache() {
		$caching = getOption($this->feed."_cache") && !zp_loggedin();
		if($caching) {
			$cachefilepath = SERVERPATH.'/cache_html/'.strtolower($this->feed).'/'.internalToFilesystem($this->getCacheFilename());
			if(file_exists($cachefilepath) AND time()-filemtime($cachefilepath) < getOption($this->feed."_cache_expire")) {
				echo file_get_contents($cachefilepath);
				exitZP();
			} else {
				if(file_exists($cachefilepath)) {
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
		$caching = getOption($this->feed."_cache") && !zp_loggedin();
		if($caching) {
			$cachefilepath = internalToFilesystem($this->getCacheFilename());
			if(!empty($cachefilepath)) {
				$cachefilepath = SERVERPATH.'/cache_html/'.strtolower($this->feed).'/'.$cachefilepath;
				mkdir_recursive(SERVERPATH.'/cache_html/'.strtolower($this->feed).'/',FOLDER_MOD);
				$pagecontent = ob_get_contents();
				ob_end_clean();
				if ($fh = @fopen($cachefilepath,"w")) {
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
	function clearCache($cachefolder=NULL) {
		zpFunctions::removeDir(SERVERPATH.'/'.STATIC_CACHE_FOLDER.'/'.strtolower($this->feed).'/'.$cachefolder,true);
	}

 /**
	* Helper function that gets the sortdirection (not used by all feeds)
	*
	* @return string
	*/
	protected function getSortdirection() {
		if(isset($_GET['sortdir'])) {
			$sortdir = sanitize($_GET['sortdir']);
			if($sortdir =! 'desc' || $sortdir != 'asc') {
				$sortdir = 'desc';
			}
			return $sortdir;
		}
	}

 /**
	* Helper function that gets the sortorder for gallery and plain news/category feeds
	*
	* @return string
	*/
	protected function getSortorder() {
		if(isset($_GET['sortorder'])) {
			$sortorder = sanitize($_GET['sortorder']);
		} else {
			$sortorder = NULL;
		}
		switch($this->feedtype) {
			default:
			case 'gallery':
				if(is_null($sortorder)) {
					if($this->mode == "albums") {
						$sortorder = getOption($this->feed."_sortorder_albums");
					} else {
						$sortorder = getOption($this->feed."_sortorder");
					}
				}
				break;
			case 'news':
				if($this->newsoption == 'withimages' || $sortorder == 'latest') {
					$sortorder = NULL;
				}
				break;
		}
		return $sortorder;
	}

	protected function getChannelTitleExtra() {
		switch($this->sortorder) {
			default:
			case 'latest':
			case 'latest-date':
			case 'latest-mtime':
			case 'latest-publishdate':
				if($this->mode == 'albums') {
					$albumextra = ' ('.gettext('Latest albums').')'; //easier to understand for translators as if I would treat "images"/"albums" in one place separately
				} else {
					$albumextra = ' ('.gettext('Latest images').')';
				}
				break;
			case 'latestupdated':
				$albumextra = ' ('.gettext('latest updated albums').')';
				break;
			case 'popular':
				if($this->mode == 'albums') {
					$albumextra = ' ('.gettext('Most popular albums').')';
				} else {
					$albumextra = ' ('.gettext('Most popular images').')';
				}
				break;
			case 'toprated':
				if($this->mode == 'albums') {
					$albumextra = ' ('.gettext('Top rated albums').')';
				} else {
					$albumextra = ' ('.gettext('Top rated images').')';
				}
				break;
			case 'random':
				if($this->mode == 'albums') {
					$albumextra = ' ('.gettext('Random albums').')';
				} else {
					$albumextra = ' ('.gettext('Random images').')';
				}
				break;
		}
		return $albumextra;
	}

	/**
	 * Helper function that returns the image path, album path and modrewrite suffix for Gallery feeds
	 *
	 * @param string $arrayfield "albumpath", "imagepath" or "modrewritesuffix"
	 * @return string
	 */
	protected function getImageAndAlbumPaths($arrayfield) {
		$arrayfield = sanitize($arrayfield);
		$array = array();
		if(MOD_REWRITE) {
			$array['albumpath'] = '/';
			$array['imagepath'] = '/';
			$array['modrewritesuffix'] = IM_SUFFIX;
		} else  {
			$array['albumpath'] = '/index.php?album=';
			$array['imagepath'] = '&amp;image=';
			$array['modrewritesuffix'] = '';
		}
		return $array[$arrayfield];
	}

	/**
	 * Helper function that returns the albumname and TRUE or FALSE for the collection mode (album + subalbums)
	 *
	 * @param string $arrayfield "albumfolder" or "collection"
	 * @return mixed
	 */
	protected function getAlbumnameAndCollection($arrayfield) {
		$arrayfield = sanitize($arrayfield);
		$array = array();
		if(!empty($arrayfield)) {
			if(isset($_GET['albumname'])) {
				$albumfolder = sanitize_path($_GET['albumname']);
				if(!file_exists(ALBUM_FOLDER_SERVERPATH.'/'.internalToFilesystem($albumfolder))) {
					$array['albumfolder'] = NULL;
				}
				$array['collection'] = FALSE;
			} else if(isset($_GET['folder'])) {
				$albumfolder = sanitize_path($_GET['folder']);
				if(!file_exists(ALBUM_FOLDER_SERVERPATH.'/'.internalToFilesystem($albumfolder))) {
					$array['albumfolder'] = NULL;
					$array['collection'] = FALSE;
				} else {
					$array['collection'] = TRUE;
				}
			} else {
				$array['albumfolder'] = NULL;
				$array['collection'] = FALSE;
			}
			return $array[$arrayfield];
		}
	}

	/**
	* Helper function that gets the images size of the "size" get parameter
	*
	* @return string
	*/
	protected function getImageSize() {
		if(isset($_GET['size'])) {
			$imagesize = sanitize_numeric($_GET['size']);
		} else {
			$imagesize = NULL;
		}
		if(is_numeric($imagesize) && !is_null($imagesize) && $imagesize < getOption('RSS_imagesize')) {
			$imagesize = $imagesize;
		} else {
			if($this->mode == 'albums') {
				$imagesize = getOption('RSS_imagesize_albums'); // un-cropped image size
			} else {
				$imagesize = getOption('RSS_imagesize'); // un-cropped image size
			}
		}
		return $imagesize;
	}

	/**
	 * Helper function that returns the News category title or catlink (name) or the mode (all news or category only) for the Zenpage news feed.
	 *
	 * @param string $arrayfield "catlink", "catttitle" or "option"
	 * @return string
	 */
	protected function getNewsCatOptions($arrayfield) {
		$arrayfield = sanitize($arrayfield);
		$array = array();
		if(!empty($arrayfield)) {
			if(isset($_GET['category'])) {
				$array['catlink'] = sanitize($_GET['category']);
				$catobj = new ZenpageCategory($array['catlink']);
				$array['cattitle'] = html_encode($catobj->getTitle());
				$array['option'] = 'category';
			} else {
				$array['catlink'] = '';
				$array['cattitle'] = '';
				$array['option'] = 'news';
			}
			return $array[$arrayfield];
		}
	}

	/**
	 * Helper function that returns if and what Zenpage Combinews mode with images is set
	 *
	 * @return string
	 */
		protected function getCombinewsImages() {
			if(isset($_GET['withimages'])) {
				return 'withimages';
			} else if(isset($_GET['withimages_mtime'])) {
				return 'withimages_mtime';
			}	else	if(isset($_GET['withimages_publishdate'])) {
				return 'withimages_publishdate';
			}
		}

		/**
		 * Helper function that returns if and what Zenpage Combinews mode with albums is set
		 *
		 * @return string
		 */
		protected function getCombinewsAlbums() {
			if(isset($_GET['withalbums'])) {
				return 'withalbums';
			}	else if(isset($_GET['withalbums_mtime'])) {
				return 'withalbums_mtime';
			}	else if(isset($_GET['withalbums_publishdate'])) {
				return 'withalbums_publishdate';
			}	else if(isset($_GET['withalbums_latestupdated'])) {
				return 'withalbums_latestupdated';
			}
		}


	/**
	 * Gets the feed items
	 *
	 * @return array
	 */
	public function getitems() {
		global $_zp_zenpage;
		switch($this->feedtype) {
			case 'gallery':
				if ($this->mode == "albums") {
					$items = getAlbumStatistic($this->itemnumber,$this->sortorder,$this->albumfolder,$this->sortdirection);
				} else {
					$items = getImageStatistic($this->itemnumber,$this->sortorder,$this->albumfolder,$this->collection,0,$this->sortdirection);
				}
				break;
			case 'news':
				switch ($this->newsoption) {
					case "category":
						if($this->sortorder) {
							$items = getZenpageStatistic($this->itemnumber,'categories',$this->sortorder,$this->sortdirection);
						} else {
							$items = getLatestNews($this->itemnumber,"none",$this->catlink,false,$this->sortdirection);
						}
						break;
					case "news":
						if($this->sortorder) {
							$items = getZenpageStatistic($this->itemnumber,'news',$this->sortorder,$this->sortdirection);
						} else {
							// Needed baceause type variable "news" is used by the feed item method and not set by the class method getArticles!
							$items = getLatestNews($this->itemnumber,'none','',false,$this->sortdirection);
						}
						break;
					case "withimages":
						//$items = getLatestNews($this->itemnumber,"with_latest_images_date",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber,'latestimages-thumbnail',NULL,'date',false,$this->sortdirection);
						break;
					case "withimages_id":
						//$items = getLatestNews($this->itemnumber,"with_latest_images_date",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber,'latestimages-thumbnail',NULL,'id',false,$this->sortdirection);
						break;
					case 'withimages_mtime':
						//$items = getLatestNews($this->itemnumber,"with_latest_images_mtime",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber,'latestimages-thumbnail',NULL,'mtime',false,$this->sortdirection);
						break;
					case 'withimages_publishdate':
						//$items = getLatestNews($this->itemnumber,"with_latest_images_publishdate",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber,'latestimages-thumbnail',NULL,'publishdate',false,$this->sortdirection);
						break;
					case 'withalbums':
						//$items = getLatestNews($this->itemnumber,"with_latest_albums_date",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber,'latestalbums-thumbnail',NULL,'date',false,$this->sortdirection);
						break;
					case 'withalbums_mtime':
						//$items = getLatestNews($this->itemnumber,"with_latest_albums_mtime",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber,'latestalbums-thumbnail',NULL,'mtime',false,$this->sortdirection);
						break;
					case 'withalbums_publishdate':
						//$items = getLatestNews($this->itemnumber,"with_latest_albums_publishdate",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber,'latestalbums-thumbnail',NULL,'publishdate',false,$this->sortdirection);
						break;
					case 'withalbums_latestupdated':
						//$items = getLatestNews($this->itemnumber,"with_latestupdated_albums",'',false,$this->sortdirection);
						$items = $_zp_zenpage->getCombiNews($this->itemnumber,'latestupdatedalbums-thumbnail',NULL,'',false,$this->sortdirection);
						break;
				}
				break;
			case "pages":
				if($this->sortorder) {
					$items = getZenpageStatistic($this->itemnumber,'pages',$this->sortorder,$this->sortdirection);
				} else {
					$items = $_zp_zenpage->getPages(NULL,false,$this->itemnumber,$this->sortorder,$this->sortdirection);
				}
				break;
			case 'comments':
				switch($type = $this->commentrsstype) {
					case 'gallery':
						$items = getLatestComments($this->itemnumber,'all');
						break;
					case 'album':
						$items = getLatestComments($this->itemnumber,'album',$this->id);
						break;
					case 'image':
						$items = getLatestComments($this->itemnumber,'image',$this->id);
						break;
					case 'zenpage':
						$type = 'all';
					case 'news':
					case 'page':
						if(function_exists('getLatestZenpageComments')) {
							$items = getLatestZenpageComments($this->itemnumber,$type,$this->id);
						}
						break;
					case 'allcomments':
						$items = getLatestComments($this->itemnumber,'all');
						$items_zenpage = array();
						if(function_exists('getLatestZenpageComments')) {
							$items_zenpage = getLatestZenpageComments($this->itemnumber,$type,$this->id);
							$items = array_merge($items,$items_zenpage);
							$items = sortMultiArray($items,'date',true);
							$items = array_slice($items,0,$this->itemnumber);
						}
						break;
				}
				break;
		}
		if (isset($items)) {
			return $items;
		}
		if (TEST_RELEASE) {
			trigger_error(gettext('Bad RSS feed:'.$this->feedtype),E_USER_WARNING);
		}
		return NULL;
	}

	/**
	* Gets the feed item data in a Zenpage news feed
	*
	* @param array $item Titlelink a Zenpage article or filename of an image if a combined feed
	* @return array
	*/
	protected function getitemPages($item) {
		$obj = new ZenpagePage($item['titlelink']);
		$feeditem['title'] = $feeditem['title'] = get_language_string($obj->getTitle('all'),$this->locale);
		$feeditem['link'] = getPageLinkURL($obj->getTitlelink());
		$feeditem['desc'] = shortenContent($obj->getContent($this->locale),getOption('zenpage_rss_length'), '...');
		$feeditem['enclosure'] = '';
		$feeditem['category'] = '';
		$feeditem['media_content'] = '';
		$feeditem['media_thumbnail'] = '';
		$feeditem['pubdate'] = date("r",strtotime($obj->getDatetime()));
		return $feeditem;
	}

	/**
	 * Gets the feed item data in a comments feed
	 *
	 * @param array $item Array of a comment
	 * @return array
	 */
	protected function getitemComments($item) {
		if($item['anon']) {
			$author = "";
		} else {
			$author = " ".gettext("by")." ".$item['name'];
		}
		$commentpath = $imagetag = $title = '';
		switch($item['type']) {
			case 'images':
				$title = get_language_string($item['title']);
				$obj = newImage(NULL, array('folder'=>$item['folder'],'filename'=>$item['filename']));
				$link = $obj->getImagelink();
				$feeditem['pubdate'] = date("r",strtotime($item['date']));
				$category = $item['albumtitle'];
				$website =$item['website'];
				if($item['type'] == 'albums') {
					$title = $category;
				} else {
					$title = $category.": ".$title;
				}
				$commentpath = PROTOCOL.'://'.$this->host.html_encode($link)."#".$item['id'];
				break;
			case 'albums':
				$obj = newAlbum($item['folder']);
				$link = rtrim($obj->getAlbumlink(),'/');
				$feeditem['pubdate'] = date("r",strtotime($item['date']));
				$category = $item['albumtitle'];
				$website =$item['website'];
				if($item['type'] == 'albums') {
					$title = $category;
				} else {
					$title = $category.": ".$title;
				}
				$commentpath = PROTOCOL.'://'.$this->host.html_encode($link)."#".$item['id'];
				break;
			case 'news':
			case 'pages':
				$album = '';
				$feeditem['pubdate'] = date("r",strtotime($item['date']));
				$category = '';
				$title = get_language_string($item['title']);
				$titlelink = $item['titlelink'];
				$website = $item['website'];
				if(function_exists('getNewsURL')) {
					if ($item['type']=='news') {
						$commentpath = PROTOCOL.'://'.$this->host.html_encode(getNewsURL($titlelink))."#".$item['id'];
					} else {
						$commentpath = PROTOCOL.'://'.$this->host.html_encode(getPageLinkURL($titlelink))."#".$item['id'];
					}
				} else {
					$commentpath = '';
				}
				break;
		}
		$feeditem['title'] = strip_tags($title.$author);
		$feeditem['link'] = $commentpath;
		$feeditem['desc'] = $item['comment'];
		return $feeditem;
	}


}
?>