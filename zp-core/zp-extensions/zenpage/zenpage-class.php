<?php
/**
 * Zenpage root classes
 * @author Stephen Billard (sbillard), Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */

/**
 * Some global variable setup
 *
 */

define('ZP_SHORTENINDICATOR',$shortenindicator = getOption('zenpage_textshorten_indicator'));
define('ZP_SINGULAR',get_language_string(getOption('combinews-customtitle-singular')));
define('ZP_PLURAL',get_language_string(getOption('combinews-customtitle-plural')));
define('ZP_COMBINEWS_IMAGETITLES',getOption('combinews-customtitle-imagetitles'));
define('ZP_SHORTEN_LENGTH',getOption('zenpage_text_length'));
define('ZP_COMBINEWS_SORTORDER',getOption("zenpage_combinews_sortorder"));
define('ZP_READ_MORE',getOption("zenpage_read_more"));
define('ZP_COMBINEWS',getOption('zenpage_combinews'));
define('ZP_ARTICLES_PER_PAGE',getOption("zenpage_articles_per_page"));
define('ZP_CN_IMAGESIZE',getOption('zenpage_combinews_imagesize'));
define('ZP_CN_THUMBWIDTH',getOption('combinews-thumbnail-width'));
define('ZP_CN_THUMBHEIGHT',getOption('combinews-thumbnail-height'));
define('ZP_CN_CROPWIDTH',getOption('combinews-thumbnail-cropwidth'));
define('ZP_CN_CROPHEIGHT',getOption('combinews-thumbnail-cropheight'));
define('ZP_CN_CROPX',getOption('combinews-thumbnail-cropx'));
define('ZP_CN_CROPY',getOption('combinews-thumbnail-cropy'));
define('ZP_CN_MODE',getOption('zenpage_combinews_mode'));
if (!defined('MENU_TRUNCATE_STRING')) define('MENU_TRUNCATE_STRING',getOption('menu_truncate_string'));
if (!defined('MENU_TRUNCATE_INDICATOR')) define('MENU_TRUNCATE_INDICATOR',getOption('menu_truncate_indicator'));

class Zenpage {

	var $news_on_index = NULL;
	protected $categoryStructure = NULL;

	/**
	 * Class instantiator
	 */
	function __construct() {
	}

	function getCategoryStructure() {
		if (is_null($this->categoryStructure)) {
			$allcategories = query_full_array("SELECT * FROM ".prefix('news_categories')." ORDER by sort_order");
			$structure = array();
			foreach ($allcategories as $cat) {
				$catobj = new ZenpageCategory($cat['titlelink']);
				if ($catobj->isMyItem(ALL_NEWS_RIGHTS)) {
					$cat['show'] = 1;
				} else {
					if ($cat['show'] && $cat['parentid']) {
						$cat['show'] = $structure[$cat['parentid']]['show'];
					}
				}
				$structure[$cat['id']] = $cat;
			}
			$structure = sortMultiArray($structure, 'sort_order', false, false, false, true);
			$this->categoryStructure = $structure;
		}
		return $this->categoryStructure;
	}

	/**
	 * Un-publishes pages/news whose expiration date has been reached
	 *
	 */
	static function processExpired($table) {
		$expire = date('Y-m-d H:i:s');
		$sql = 'SELECT * FROM '.prefix($table).' WHERE `date`<="'.$expire.'"'.
						' AND `show`="1"'.
						' AND `expiredate`<="'.$expire.'"'.
						' AND `expiredate`!="0000-00-00 00:00:00"'.
						' AND `expiredate` IS NOT NULL';
		$result = query_full_array($sql);
		if ($result) {
			foreach ($result as $item) {
				$class = 'Zenpage'.$table;
				$obj = new $class($item['titlelink']);
				$obj->setShow(0);
				$obj->save();
			}
		}
	}

/************************************/
/* general page functions   */
/************************************/

/**
	 * Gets the titlelink and sort order for all pages or published ones.
	 *
	 * NOTE: Since this function only returns titlelinks for use with the object model it does not exclude pages that are password protected
	 *
	 * @param bool $published TRUE for published or FALSE for all pages including un-published
	 * @return array
	 */
	function getPages($published=NULL) {
		global $_zp_loggedin;
		$this->processExpired('pages');
		if (is_null($published)) {
			$published = !zp_loggedin();
			$all = zp_loggedin(MANAGE_ALL_PAGES_RIGHTS);
		} else {
			$all = !$published;;
		}
		if($published) {
			$show = " WHERE `show` = 1 AND date <= '".date('Y-m-d H:i:s')."'";
		} else {
			$show = '';
		}
		$all_pages = array(); // Disabled cache var for now because it does not return un-publishded and published if logged on index.php somehow if logged in.
		$result  = query("SELECT * FROM ".prefix('pages').$show." ORDER by `sort_order`");
		if ($result) {
			while ($row = db_fetch_assoc($result)) {
				if ($all || $row['show']) {
					$all_pages[] = $row;
				} else if ($_zp_loggedin) {
					$page = new ZenpagePage($row['titlelink']);
					if ($page->isMyItem(LIST_RIGHTS)) {
						$all_pages[] = $row;
					}
				}
			}
		}
		return $all_pages;
	}


	/**
	* Returns path to the pages.php page without the title(link)
	*
	* @return string
	*/
	function getPagesLinkPath() {
		return rewrite_path("pages/", "/index.php?p=pages&title=");
	}

	/************************************/
	/* general news article functions   */
	/************************************/

	/**
	 * Gets all news articles titlelink.
	 *
	 * NOTE: Since this function only returns titlelinks for use with the object model it does not exclude articles that are password protected via a category
	 *
	 *
	 * @param int $articles_per_page The number of articles to get
	 * @param string $published "published" for an published articles,
	 * 													"unpublished" for an unpublised articles,
	 * 													"sticky" for sticky articles (published or not!) for admin page use only,
	 * 													"all" for all articles
	 * @param boolean $ignorepagination Since also used for the news loop this function automatically paginates the results if the "page" GET variable is set. To avoid this behaviour if using it directly to get articles set this TRUE (default FALSE)
	 * @param string $sortorder "date" for sorting by date (default)
	 * 													"title" for sorting by title
	 * 													This parameter is not used for date archives
	 * @param string $sortdirection "desc" (default) for descending sort order
	 * 													    "asc" for ascending sort order
	 * 											        This parameter is not used for date archives
	 * @param bool $sticky set to true to place "sticky" articles at the front of the list.
	 * @return array
	 */
	function getArticles($articles_per_page=0, $published=NULL,$ignorepagination=false,$sortorder='date', $sortdirection='desc',$sticky=true) {
		global $_zp_current_category, $_zp_post_date;
		$this->processExpired('news');
		$getUnpublished = NULL;
		if (empty($published)) {
			if(zp_loggedin( ALL_NEWS_RIGHTS)) {
				$published = "all";
				$getUnpublished = true;
			} else {
				if (zp_loggedin()) {
					$published = 'all';
				} else {
					$published = "published";
				}
			}
		}
		$show = '';
		if(in_context(ZP_ZENPAGE_NEWS_DATE)) {
			$postdate = $_zp_post_date;
		} else {
			$postdate = NULL;
		}
		if ($sticky) {
			$sticky = 'sticky DESC,';
		}
		// sortorder and sortdirection (only used for all news articles and categories naturally)
		switch($sortorder) {
			case "date":
			default:
				$sort1 = "date";
				break;
			case "title":
				$sort1 = "title";
				break;
		}
		switch($sortdirection) {
			case "desc":
			default:
				$dir = "DESC";
				break;
			case "asc":
				$dir = "ASC";
				$sticky = false;	//makes no sense
				break;
		}
		/***get all articles ***/
		switch($published) {
			case "published":
				$show = " WHERE `show` = 1 AND date <= '".date('Y-m-d H:i:s')."'";
				break;
			case "unpublished":
				$show = " WHERE `show` = 0 AND date <= '".date('Y-m-d H:i:s')."'";
				$getUnpublished = true;
				break;
			case 'sticky':
				$show = ' WHERE `sticky` <> 0';
				break;
			case "all":
				$show = "";
				break;
		}
		if(in_context(ZP_ZENPAGE_NEWS_DATE)) {
			$datesearch = '';
			switch($published) {
				case "published":
					$datesearch = "date LIKE '$postdate%' ";
					break;
				case "unpublished":
					$datesearch = "date LIKE '$postdate%' ";
					break;
				case "all":
					$datesearch = "date LIKE '$postdate%' ";
					break;
			}
			if ($datesearch) {
				if ($show) {
					$datesearch = ' AND '.$datesearch;
				} else {
					$datesearch = ' WHERE '.$datesearch;
				}
			}
			$order = " ORDER BY $sticky date DESC";
		} else {
			$datesearch = "";
			$order = " ORDER BY ".$sticky.$sort1." ".$dir;
		}
		$sql = "SELECT titlelink FROM ".prefix('news').$show.$datesearch." ".$order;
		$resource = query($sql);
		$result = array();
		if ($resource) {
			if ($ignorepagination) {
				$offset = 0;
			} else {
				$offset = self::getOffset($articles_per_page);
			}
			while ($item = db_fetch_assoc($resource)) {
				$article = new ZenpageNews($item['titlelink']);
				if ($getUnpublished || $article->categoryIsVisible()) {
					$offset--;
					if ($offset < 0) {
						$result[] = $item;
						if ($articles_per_page && count($result) >= $articles_per_page) {
							break;
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Gets the LIMIT and OFFSET for the query that gets the news articles
	 *
	 * @param int $articles_per_page The number of articles to get
	 * @param bool $ignorepagination If pagination should be ingored so always with the first is started (false is default)
	 * @return string
	 */
	static function getOffset($articles_per_page,$ignorepagination=false) {
		global $_zp_zenpage_currentadminnewspage, $_zp_page;
		if(strstr(dirname($_SERVER['REQUEST_URI']), '/'.PLUGIN_FOLDER.'/zenpage')) {
			$page = $_zp_zenpage_currentadminnewspage;
		} else {
			$page = $_zp_page;
		}
		if($ignorepagination) {
			$offset = 0;
		} else {
			$offset = ($page - 1) * $articles_per_page;
		}
		return $offset;
	}

	/**
	 * Returns the articles count
	 *
	 */
	function getTotalArticles() {
		global $_zp_current_category;
		if(ZP_COMBINEWS AND !isset($_GET['title']) AND !isset($_GET['category']) AND !isset($_GET['date']) AND OFFSET_PATH != 4) {
			return $this->countCombiNews();
		} else {
			if(empty($_zp_current_category)) {
				if (isset($_GET['category'])) {
					$cat = sanitize($_GET['category']);
					$catobj = new ZenpageCategory($cat);
				} else {
					return count($this->getArticles(0));
				}
			} else {
				$catobj = $_zp_current_category;
			}
			return count($catobj->getArticles());
		}
	}

	/**
	 * Retrieves a list of all unique years & months
	 * @param bool $yearsonly If set to true only the years' count is returned (Default false)
	 * @param string $order 'desc' (default) or 'asc' for descending or ascending
	 * @return array
	 */
	function getAllArticleDates($yearsonly=false,$order='desc') {
		$alldates = array();
		$cleandates = array();
		$sql = "SELECT date FROM ". prefix('news');
		if (!zp_loggedin(MANAGE_ALL_NEWS_RIGHTS)) { $sql .= " WHERE `show` = 1"; }
		$result = query_full_array($sql);
		foreach($result as $row){
			$alldates[] = $row['date'];
		}
		foreach ($alldates as $adate) {
			if (!empty($adate)) {
				if($yearsonly) {
					$cleandates[] = substr($adate, 0, 4);
				} else {
					$cleandates[] = substr($adate, 0, 7) . "-01";
				}
			}
		}
		$datecount = array_count_values($cleandates);
		switch($order) {
			case 'desc':
			default:
				krsort($datecount);
				break;
			case 'asc':
				ksort($datecount);
			break;
		}
		return $datecount;
	}

	/**
	 *
	 * filters query results for only news that should be shown. (that is fit to print?)
	 * @param $sql query to return all candidates of interest
	 * @param $offset skip this many legitimate items (used for pagination)
	 * @param $limit return only this many items
	 */
	private function siftResults($sql, $offset, $limit) {
		$resource = $result = query($sql);
		if ($resource) {
			$result = array();
			while ($item = db_fetch_assoc($resource)) {
				if ($item['type'] == 'news') {
					$article = new ZenpageNews($item['titlelink']);
					if (!$article->categoryIsVisible()) {
						continue;
					}
				}
				$offset--;
				if ($offset < 0) {
					$result[] = $item;
					if ($limit && count($result) >= $limit) {
						break;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Gets news articles and images of a gallery to show them together on the news section
	 *
	 * NOTE: This function does not exclude articles that are password protected via a category
	 *
	 * @param int $articles_per_page The number of articles to get
	 * @param string $mode 	"latestimages-thumbnail"
	 *											"latestimages-thumbnail-customcrop"
	 *											"latestimages-sizedimage"
	 *											"latestalbums-thumbnail"
	 *		 									"latestalbums-thumbnail-customcrop"
	 *		 									"latestalbums-sizedimage"
	 *		 									"latestimagesbyalbum-thumbnail"
	 *		 									"latestimagesbyalbum-thumbnail-customcrop"
	 *		 									"latestimagesbyalbum-sizedimage"
	 *		 									"latestupdatedalbums-thumbnail" (for RSS and getLatestNews() used only)
	 *		 									"latestupdatedalbums-thumbnail-customcrop" (for RSS and getLatestNews() used only)
	 *		 									"latestupdatedalbums-sizedimage" (for RSS and getLatestNews() used only)
	 *	NOTE: The "latestupdatedalbums" variants do NOT support pagination as required on the news loop!
	 *
	 * @param string $published "published" for published articles,
	 * 													"unpublished" for un-published articles,
	 * 													"all" for all articles
	 * @param string $sortorder 	id, date or mtime, only for latestimages-... modes
	 * @param bool $sticky set to true to place "sticky" articles at the front of the list.
	 * @return array
	 */
	function getCombiNews($articles_per_page='', $mode='',$published=NULL,$sortorder='',$sticky=true) {
		global $_zp_gallery, $_zp_flash_player;
		$this->processExpired('news');
		if (is_null($published)) {
			if(zp_loggedin(ZENPAGE_NEWS_RIGHTS | ALL_NEWS_RIGHTS)) {
				$published = "all";
			} else {
				$published = "published";
			}
		}
		if(empty($mode)) {
			$mode = ZP_CN_MODE;
		}
		if($published == "published") {
			$show = " WHERE `show` = 1 AND date <= '".date('Y-m-d H:i:s')."'";
			$imagesshow = " AND images.show = 1 ";
		} else {
			$show = "";
			$imagesshow = "";
		}
		$passwordcheck = "";
		if (zp_loggedin(ZENPAGE_NEWS_RIGHTS | ALL_NEWS_RIGHTS)) {
			$albumWhere = "";
			$passwordcheck = "";
		} else {
			$albumscheck = query_full_array("SELECT * FROM " . prefix('albums'). " ORDER BY title");
			foreach($albumscheck as $albumcheck) {
				if(!checkAlbumPassword($albumcheck['folder'])) {
					$albumpasswordcheck= " AND albums.id != ".$albumcheck['id'];
					$passwordcheck = $passwordcheck.$albumpasswordcheck;
				}
			}
			$albumWhere = "AND albums.show=1".$passwordcheck;
		}
		if ($articles_per_page) {
			$offset = self::getOffset($articles_per_page);
		} else {
			$offset = 0;
		}
		if(empty($sortorder)) {
			$combinews_sortorder = getOption("zenpage_combinews_sortorder");
		} else {
			$combinews_sortorder = $sortorder;
		}
		$stickyorder = '';
		if($sticky) {
			$stickyorder = 'sticky DESC,';
		}
		$type3 = query("SET @type3:='0'");
		switch($mode) {
			case "latestimages-thumbnail":
			case "latestimages-thumbnail-customcrop":
			case "latestimages-sizedimage":
			case "latestimages-sizedimage-maxspace":
			case "latestimages-fullimage":
				$sortorder = $combinews_sortorder;
				$type1 = query("SET @type1:='news'");
				$type2 = query("SET @type2:='images'");
				switch($combinews_sortorder) {
					case 'id':
					case 'date':
						$imagequery = "(SELECT albums.folder, images.filename, images.date, @type2, @type3 as sticky FROM ".prefix('images')." AS images, ".prefix('albums')." AS albums
							WHERE albums.id = images.albumid ".$imagesshow.$albumWhere.")";
						break;
					case 'publishdate':
						$imagequery = "(SELECT albums.folder, images.filename, IFNULL(images.publishdate,images.date), @type2, @type3 as sticky FROM ".prefix('images')." AS images, ".prefix('albums')." AS albums
													WHERE albums.id = images.albumid ".$imagesshow.$albumWhere.")";
					case 'mtime':
						$imagequery = "(SELECT albums.folder, images.filename, FROM_UNIXTIME(images.mtime), @type2, @type3 as sticky FROM ".prefix('images')." AS images, ".prefix('albums')." AS albums
							WHERE albums.id = images.albumid ".$imagesshow.$albumWhere.")";
						break;
				}
				$result = $this->siftResults("(SELECT title as albumname, titlelink, date, @type1 as type, sticky FROM ".prefix('news')." ".$show.")
																		UNION
																		".$imagequery."
																		ORDER BY $stickyorder date DESC
																		", $offset, $articles_per_page);
				break;
			case "latestalbums-thumbnail":
			case "latestalbums-thumbnail-customcrop":
			case "latestalbums-sizedimage":
			case "latestalbums-sizedimage-maxspace":
			case "latestalbums-fullimage":
				$sortorder = $combinews_sortorder;
				$type1 = query("SET @type1:='news'");
				$type2 = query("SET @type2:='albums'");
				switch($combinews_sortorder) {
					case 'id':
					case 'date':
						$albumquery = "(SELECT albums.folder, albums.title, albums.date, @type2, @type3 as sticky FROM ".prefix('albums')." AS albums
							".$show.$albumWhere.")";
						break;
					case 'publishdate':
							$albumquery = "(SELECT albums.folder, albums.title, IFNULL(albums.publishdate,albums.date), @type2, @type3 as sticky FROM ".prefix('albums')." AS albums
													".$show.$albumWhere.")";
							break;
					case 'mtime':
						$albumquery = "(SELECT albums.folder, albums.title, FROM_UNIXTIME(albums.mtime), @type2, @type3 as sticky FROM ".prefix('albums')." AS albums
							".$show.$albumWhere.")";
						break;
				}
				$result = $this->siftResults("(SELECT title as albumname, titlelink, date, @type1 as type, sticky FROM ".prefix('news')." ".$show.")
																		UNION
																		".$albumquery."
																		ORDER BY $stickyorder date DESC
																		", $offset, $articles_per_page);
				break;
			case "latestimagesbyalbum-thumbnail":
			case "latestimagesbyalbum-thumbnail-customcrop":
			case "latestimagesbyalbum-sizedimage":
			case "latestimagesbyalbum-sizedimage-maxspace":
			case "latestimagesbyalbum-fullimage":
				$type1 = query("SET @type1:='news'");
				$type2 = query("SET @type2:='albums'");
				if(empty($combinews_sortorder) || $combinews_sortorder != "date" || $combinews_sortorder != "mtime" || $combinews_sortorder != "publishdate") {
					$combinews_sortorder = "date";
				}
				$sortorder = "images.".$combinews_sortorder;
				switch(		$combinews_sortorder) {
					case "date":
						$imagequery = "(SELECT DISTINCT DATE_FORMAT(".$sortorder.",'%Y-%m-%d'), albums.folder, DATE_FORMAT(images.date,'%Y-%m-%d'), @type2, @type3 as sticky FROM ".prefix('images')." AS images, ".prefix('albums')." AS albums
														WHERE albums.id = images.albumid ".$imagesshow.$albumWhere.")";
						break;
					case "mtime":
						$imagequery = "(SELECT DISTINCT FROM_UNIXTIME(".$sortorder.",'%Y-%m-%d'), albums.folder, DATE_FORMAT(images.mtime,'%Y-%m-%d'), @type2, @type3 as sticky FROM ".prefix('images')." AS images, ".prefix('albums')." AS albums
														WHERE albums.id = images.albumid ".$imagesshow.$albumWhere.")";
					case "publishdate":
							$imagequery = "(SELECT DISTINCT FROM_UNIXTIME(".$sortorder.",'%Y-%m-%d'), albums.folder, DATE_FORMAT(images.publishdate,'%Y-%m-%d'), @type2, @type3 as sticky FROM ".prefix('images')." AS images, ".prefix('albums')." AS albums
																				WHERE albums.id = images.albumid ".$imagesshow.$albumWhere.")";
						break;
				}
				$result = $this->siftResults("(SELECT title as albumname, titlelink, date, @type1 as type, sticky FROM ".prefix('news')." ".$show.")
																		UNION
																		".$imagequery."
																		ORDER By $stickyorder date DESC
																		", $offset, $articles_per_page);
				break;
			case "latestupdatedalbums-thumbnail":
			case "latestupdatedalbums-thumbnail-customcrop":
			case "latestupdatedalbums-sizedimage":
			case "latestupdatedalbums-sizedimage-maxspace":
			case "latestupdatedalbums-fullimage":
				$latest = $this->getArticles($articles_per_page,NULL,true);
				$counter = '';
				foreach($latest as $news) {
					$article = new ZenpageNews($news['titlelink']);
					if ($article->checkAccess($hint, $show)) {
						$counter++;
						$latestnews[$counter] = array(
																					"albumname" => $article->getTitle(),
																					"titlelink" => $article->getTitlelink(),
																					"date" => $article->getDateTime(),
																					"type" => "news",
																					);
					}
				}
				$albums = getAlbumStatistic($articles_per_page, "latestupdated");
				$latestalbums = array();
				$counter = "";
				foreach($albums as $album) {
					$counter++;
					$tempalbum = new Album(NULL, $album['folder']);
					$tempalbumthumb = $tempalbum->getAlbumThumbImage();
					$timestamp = $tempalbum->get('mtime');
					if($timestamp == 0) {
						$albumdate = $tempalbum->getDateTime();
					} else {
						$albumdate = strftime('%Y-%m-%d %H:%M:%S',$timestamp);
					}
					$latestalbums[$counter] = array(
																					"albumname" => $tempalbum->getFolder(),
																					"titlelink" => $tempalbum->getTitle(),
																					"date" => $albumdate,
																					"type" => 'albums',
																					);
				}
				//$latestalbums = array_merge($latestalbums, $item);
				$latest = array_merge($latestnews, $latestalbums);
				$result = sortMultiArray($latest,"date",true);
				if(count($result) > $articles_per_page) {
					$result = array_slice($result,0,$articles_per_page);
				}
				break;
		}
		//$result = "";
		return $result;
	}


	/**
	 * CombiNews Feature: Counts all news articles and all images
	 *
	 * @return int
	 */
	function countCombiNews($published=NULL) {
		global $_zp_gallery;
		$countGalleryitems = 0;
		$countArticles = 0;
		if(ZP_COMBINEWS) {
			$countArticles = count($this->getArticles(0));
			if(is_null($published)) {
				if(zp_loggedin(ZENPAGE_NEWS_RIGHTS | ALL_NEWS_RIGHTS)) {
					$published = FALSE;
				} else {
					$published = TRUE;
				}
			}
			$mode = getOption("zenpage_combinews_mode");
			if(is_object($_zp_gallery)) { // workaround if called on the admin pages....
				switch($mode) {
					case "latestimages-sizedimage":
					case "latestimages-sizedimage-maxspace":
					case "latestimages-sizedimage-fullimage":
					case "latestimages-thumbnail":
					case "latestimages-thumbnail-customcrop":
						$countGalleryitems = $_zp_gallery->getNumImages($published);
						break;
					case "latestalbums-sizedimage":
					case "latestalbums-sizedimage-maxspace":
					case "latestalbums-fullimage":
					case "latestalbums-thumbnail":
					case "latestalbums-thumbnail-customcrop":
						$countGalleryitems =  $_zp_gallery->getNumAlbums(true,$published);
						break;
					case "latestimagesbyalbum-thumbnail":
					case "latestimagesbyalbum-thumbnail-customcrop":
					case "latestimagesbyalbum-sizedimage":
					case "latestimagesbyalbum-sizedimage-maxspace":
					case "latestimagesbyalbum-fullimage":
						($published) ? $show = "WHERE `show`= 1" : $show = "";
						$countGalleryitems = db_count('images',$show,'DISTINCT Date(date),albumid');
						break;
				}
			} else {
				$countGalleryitems = 0;
			}

			$totalcount = $countArticles+$countGalleryitems;
			return $totalcount;
		}
	}

	/**
 	* Returns the full path of the news index page (news page 1) or if the "news on zp index" option is set a link to the gallery index.
	*
 	* @return string
 	*/
	function getNewsIndexURL() {
		if($this->news_on_index) {
			return getGalleryIndexURL(false);
		} else {
			return rewrite_path('news', "/index.php?p=news");
		}
	}


	/**
 	* Returns the base /news or index.php?p=news url
 	*
 	* @return string
 	*/
	function getNewsBaseURL() {
		return rewrite_path('news', "/index.php?p=news");
	}


	/**
 	* Returns partial path of news category
 	*
 	* @return string
 	*/
	function getNewsCategoryPath() {
		return rewrite_path("/category/","&category=",false);
	}

	/**
 	* Returns partial path of news date archive
 	*
 	* @return string
 	*/
	function getNewsArchivePath() {
		return rewrite_path("/archive/","&date=",false);
	}


	/**
 	* Returns partial path of news article title
 	*
 	* @return string
 	*/
	function getNewsTitlePath() {
		return rewrite_path("/","&title=",false);
	}


	/**
 	* Returns partial path of a news page number path
 	*
 	* @return string
 	*/
	function getNewsPagePath() {
		return rewrite_path("/","&page=",false);
	}


	/************************************/
	/* general news category functions  */
	/************************************/

	/**
	 * Gets the category link of a category
	 *
	 * @param string $catname the title of the category
	 * @return string
	 */
	function getCategoryLink($catname) {
		foreach($this->getAllCategories(false) as $cat) {
			if($cat['titlelink'] == $catname) {
				return $cat['title'];
			}
		}
	}


	/**
	 * Gets a category titlelink by id
	 *
	 * @param int $id id of the category
	 * @return array
	 */
	function getCategory($id) {
		foreach($this->getAllCategories(false) as $cat) {
			if($cat['id'] == $id) {
				return $cat;
			}
		}
		return '';
	}


	/**
	 * Gets all categories
	 *
	 * @return array
	 */
	function getAllCategories($visible=true) {
		$structure = $this->getCategoryStructure();
		if ($visible) {
			foreach ($structure as $key=>$cat) {
				if (!$cat['show']) {
					unset($structure[$key]);
				}
			}
		}
		return $structure;
	}

	/**
	*
	* "Magic" function to return a string identifying the object when it is treated as a string
	* @return string
	*/
	public function __toString() {
	return 'Zenpage';
	}

}	// ZenpageCMS

/**
 *
 * Base class from which all Zenpage classes derive
 *
 */
class ZenpageRoot extends ThemeObject {

	/**
	 * Class instantiator
	 */
	function ZenpageRoot() {
		// no action required
	}

	/**
	 * Returns the perma link status (only used on admin)
	 *
	 * @return string
	 */
	function getPermalink() {
		return $this->get("permalink");
	}

	/**'
	 * sets the permalink
	 */
	function setPermalink($v) {
		$this->set('permalink', $v);
	}

	/**
	 * Returns the titlelink
	 *
	 * @return string
	 */
	function getTitlelink() {
		return $this->get("titlelink");
	}

	/**
	 * sets the title link
	 * @param $v
	 */
	function setTitlelink($v) {
		$this->set("titlelink",$v);
	}

} // Zenpage main class end

/**
 *
 * Base class from which Zenpage news articles and pages derive
 *
 */
class ZenpageItems extends ZenpageRoot {

	/**
	 * Class instantiator
	 */
	function __construct() {
		// no action required
	}


	/**
	 * Returns the author
	 *
	 * @return string
	 */
	function getAuthor() {
		return $this->get("author");
	}

	/**
	 *
	 * sets the author attribute

	 */
	function setAuthor($a) {
		$this->set("author",$a);
	}

	/**
	 * Returns the content
	 *
	 * @return string
	 */
	function getContent($locale=NULL) {
		return get_language_string($this->get("content"),$locale);
	}

	/**
	 *
	 * Set the content datum
	 * @param $c full language string
	 */
	function setContent($c) {
		$this->set("content",$c);
	}

	/**
	 * Returns the last change date
	 *
	 * @return string
	 */
	function getLastchange() {
		return $this->get("lastchange");
	}

	/**
	 *
	 * sets the last change date
	 */
	function setLastchange($d) {
		if ($d) {
			$newtime = dateTimeConvert($d);
			if ($newtime === false) return;
			$this->set('lastchange', $newtime);
		} else {
			$this->set('lastchange', NULL);
		}
	}

	/**
	 * Returns the last change author
	 *
	 * @return string
	 */
	function getLastchangeAuthor() {
		return $this->get("lastchangeauthor");
	}

	/**
	 *
	 * stores the last change author
	 */
	function setLastchangeAuthor($a) {
		$this->set("lastchangeauthor",$a);
	}

	/**
	 * Returns the locked status , "1" if locked (only used on the admin)
	 *
	 * @return string
	 */
	function getLocked() {
		return $this->get("locked");
	}

	/**
	 * sets the locked status , "1" if locked (only used on the admin)
	 *
	 */
	function setLocked($l) {
		$this->set("locked",$l);
	}

	/**
	 * Returns the extra content
	 *
	 * @return string
	 */
	function getExtraContent($locale=NULL) {
		return get_language_string($this->get("extracontent"),$locale);
	}

	/**
	 * sets the extra content
	 *
	 */
	function setExtraContent($ec) {
		$this->set("extracontent",$ec);
	}

	/**
	 * Returns the expire date
	 *
	 * @return string
	 */
	function getExpireDate() {
		$dt = $this->get("expiredate");
		if ($dt == '0000-00-00 00:00:00') {
			return NULL;
		} else {
			return $dt;
		}
	}

	/**
	 * sets the expire date
	 *
	 */
	function setExpireDate($ed) {
		if ($ed) {
			$newtime = dateTimeConvert($ed);
			if ($newtime === false) return;
			$this->set('expiredate', $newtime);
		} else {
			$this->set('expiredate', NULL);
		}
	}

}
?>