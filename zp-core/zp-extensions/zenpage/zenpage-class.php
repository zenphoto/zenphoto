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
define('ZP_SHORTENINDICATOR', $shortenindicator = getOption('zenpage_textshorten_indicator'));
define('ZP_COMBINEWS_CUSTOMTITLE', get_language_string(getOption('combinews-customtitle')));
define('ZP_COMBINEWS_IMAGETITLES', getOption('combinews-customtitle-imagetitles'));
define('ZP_SHORTEN_LENGTH', getOption('zenpage_text_length'));
define('ZP_COMBINEWS_SORTORDER', getOption("zenpage_combinews_sortorder"));
define('ZP_READ_MORE', getOption("zenpage_read_more"));
define('ZP_COMBINEWS', getOption('zenpage_combinews'));
define('ZP_ARTICLES_PER_PAGE', getOption("zenpage_articles_per_page"));
define('ZP_CN_IMAGESIZE', getOption('zenpage_combinews_imagesize'));
define('ZP_CN_THUMBWIDTH', getOption('combinews-thumbnail-width'));
define('ZP_CN_THUMBHEIGHT', getOption('combinews-thumbnail-height'));
define('ZP_CN_CROPWIDTH', getOption('combinews-thumbnail-cropwidth'));
define('ZP_CN_CROPHEIGHT', getOption('combinews-thumbnail-cropheight'));
define('ZP_CN_CROPX', getOption('combinews-thumbnail-cropx'));
define('ZP_CN_CROPY', getOption('combinews-thumbnail-cropy'));
define('ZP_CN_MODE', getOption('zenpage_combinews_mode'));
if (!defined('MENU_TRUNCATE_STRING'))
	define('MENU_TRUNCATE_STRING', getOption('menu_truncate_string'));
if (!defined('MENU_TRUNCATE_INDICATOR'))
	define('MENU_TRUNCATE_INDICATOR', getOption('menu_truncate_indicator'));

class Zenpage {

	var $news_on_index = NULL;
	var $categoryStructure = array();
	var $sortorder;
	var $sortdirection;
	var $sortSticky = true;

	/**
	 * Class instantiator
	 */
	function __construct() {
		if (OFFSET_PATH !== 2) {
			/**
			 * Un-publishes pages/news whose expiration date has been reached
			 *
			 */
			$sql = ' WHERE `date`<="' . date('Y-m-d H:i:s') . '" AND `show`="1"' .
							' AND `expiredate`<="' . date('Y-m-d H:i:s') . '"' .
							' AND `expiredate`!="0000-00-00 00:00:00"' .
							' AND `expiredate` IS NOT NULL';
			foreach (array('news', 'pages') as $table) {
				$result = query_full_array('SELECT * FROM ' . prefix($table) . $sql);
				if ($result) {
					foreach ($result as $item) {
						$class = 'Zenpage' . $table;
						$obj = new $class($item['titlelink']);
						$obj->setShow(0);
						$obj->save();
					}
				}
			}

			$allcategories = query_full_array("SELECT * FROM " . prefix('news_categories') . " ORDER by sort_order");
			$this->categoryStructure = array();
			foreach ($allcategories as $cat) {
				$this->categoryStructure[$cat['id']] = $cat;
			}
		}
	}

	/**
	 * Provides the complete category structure regardless of permissions.
	 * This is needed for quick checking of status of a category and is used only internally to the Zenpage core.
	 * @return array
	 */
	private function getCategoryStructure() {
		return $this->categoryStructure;
	}

	/*	 * ********************************* */
	/* general page functions   */
	/*	 * ********************************* */

	function visibleCategory($cat) {
		return $this->categoryStructure[$cat['cat_id']]['show'];
	}

	/**
	 * Gets all pages or published ones.
	 *
	 * NOTE: Since this function only returns titlelinks for use with the object model it does not exclude pages that are password protected
	 *
	 * @param bool $published TRUE for published or FALSE for all pages including un-published
	 * @param bool $toplevel TRUE for only the toplevel pages
	 * @param int $number number of pages to get (NULL by default for all)
	 * @param string $sorttype NULL for the standard order as sorted on the backend, "title", "date", "id", "popular", "mostrated", "toprated", "random"
	 * @param string $sortdirection "asc" or "desc" for ascending or descending order
	 * @param object $page set to the page object if this is a subpage request
	 * @return array
	 */
	function getPages($published = NULL, $toplevel = false, $number = NULL, $sorttype = NULL, $sortdirection = NULL, $parent = NULL) {
		global $_zp_loggedin;
		if ($parent) {
			$sortObj = $parent;
		} else {
			$sortObj = $this;
		}

		if (is_null($published)) {
			$published = !zp_loggedin();
			$all = zp_loggedin(MANAGE_ALL_PAGES_RIGHTS);
		} else {
			$all = !$published;
		}
		$gettop = '';
		if ($published) {
			if ($toplevel)
				$gettop = " AND parentid IS NULL";
			$show = " WHERE `show` = 1 AND date <= '" . date('Y-m-d H:i:s') . "'" . $gettop;
		} else {
			if ($toplevel)
				$gettop = " WHERE parentid IS NULL";
			$show = $gettop;
		}
		if (is_null($sortdirection)) {
			$sortdirection = $sortObj->sortdirection;
		}
		switch ($sortdirection) {
			default:
			case 'asc':
				$sortdir = ' ASC';
				break;
			default:
			case 'desc':
				$sortdir = ' DESC';
				break;
		}
		if (is_null($sorttype)) {
			$sorttype = $sortObj->sortorder;
		}
		switch ($sorttype) {
			default:
			case 'date':
				$sortorder = 'date';
				break;
			case 'lastchange':
				$sortorder = 'lastchange';
				break;
			case 'title':
				$sortorder = 'title';
				break;
			case 'id':
				$sortorder = 'id';
				break;
			case 'popular':
				$sortorder = 'hitcounter';
				break;
			case 'mostrated':
				$sortorder = 'total_votes';
				break;
			case 'toprated':
				if (empty($sortdir))
					$sortdir = ' DESC';
				$sortorder = '(total_value/total_votes) ' . $sortdir . ', total_value';
				break;
			case 'random':
				$sortorder = 'RAND()';
				break;
			default:
				$sortorder = 'sort_order';
				$sortdir = '';
				break;
		}
		$all_pages = array(); // Disabled cache var for now because it does not return un-publishded and published if logged on index.php somehow if logged in.
		$result = query('SELECT * FROM ' . prefix('pages') . $show . ' ORDER by `' . $sortorder . '`' . $sortdir);
		if ($result) {
			while ($row = db_fetch_assoc($result)) {
				if ($all || $row['show']) {
					$all_pages[] = $row;
				} else if ($_zp_loggedin) {
					$page = new ZenpagePage($row['titlelink']);
					if ($page->isMyItem(LIST_RIGHTS)) {
						$all_pages[] = $row;
						if ($number && count($result) >= $number) {
							break;
						}
					}
				}
			}
			db_free_result($result);
		}
		return $all_pages;
	}

	/**
	 * Returns path to the pages.php page without the title(link)
	 *
	 * @return string
	 */
	function getPagesLinkPath($title) {
		return rewrite_path(_PAGES_ . '/' . $title, "/index.php?p=pages&title=$title");
	}

	/*	 * ********************************* */
	/* general news article functions   */
	/*	 * ********************************* */

	/**
	 * Gets all news articles titlelink.
	 *
	 * NOTE: Since this function only returns titlelinks for use with the object model it does not exclude articles that are password protected via a category
	 *
	 *
	 * @param int $articles_per_page The number of articles to get
	 * @param string $published "published" for an published articles,
	 * 													"unpublished" for an unpublised articles,
	 * 													"published-unpublished" for published articles only from an unpublished category,
	 * 													"sticky" for sticky articles (published or not!) for admin page use only,
	 * 													"all" for all articles
	 * @param boolean $ignorepagination Since also used for the news loop this function automatically paginates the results if the "page" GET variable is set. To avoid this behaviour if using it directly to get articles set this TRUE (default FALSE)
	 * @param string $sortorder "date" (default), "title", "id, "popular", "mostrated", "toprated", "random"
	 * 													This parameter is not used for date archives
	 * @param string $sortdirection "asc" or "desc" for ascending or descending order
	 * 											        This parameter is not used for date archives
	 * @param bool $sticky set to true to place "sticky" articles at the front of the list.
	 * @return array
	 */
	function getArticles($articles_per_page = 0, $published = NULL, $ignorepagination = false, $sortorder = NULL, $sortdirection = NULL, $sticky = NULL, $category = NULL) {
		global $_zp_current_category, $_zp_post_date, $_zp_newsCache;
		if ($category) {
			$sortObj = $category;
		} else {
			$sortObj = $this;
		}

		if (empty($published)) {
			if (zp_loggedin() || $category && $category->isMyItem(ZENPAGE_NEWS_RIGHTS)) {
				$published = "all";
			} else {
				$published = "published";
			}
		}

		$newsCacheIndex = "$sortorder-$sortdirection-$published-" . (bool) $sticky;
		if ($category)
			$newsCacheIndex .= '-' . $category->getTitlelink();

		if (isset($_zp_newsCache[$newsCacheIndex])) {
			$result = $_zp_newsCache[$newsCacheIndex];
		} else {
			$show = $currentcategory = false;
			if ($category) {
				if (is_object($_zp_current_category)) {
					$currentcategory = $_zp_current_category->getTitlelink();
				}
				$showConjunction = ' AND ';
				// new code to get nested cats
				$catid = $category->getID();
				$subcats = $category->getSubCategories();
				if ($subcats) {
					$cat = " (cat.cat_id = '" . $catid . "'";
					foreach ($subcats as $subcat) {
						$subcatobj = new ZenpageCategory($subcat);
						$cat .= "OR cat.cat_id = '" . $subcatobj->getID() . "' ";
					}
					$cat .= ") AND cat.news_id = news.id ";
				} else {
					$cat = " cat.cat_id = '" . $catid . "' AND cat.news_id = news.id ";
				}
			} else {
				$showConjunction = ' WHERE ';
			}

			if (is_null($sticky)) {
				$sticky = $sortObj->sortSticky;
			}
			if ($sticky) {
				$sticky = 'sticky DESC,';
			}
			switch ($sortdirection) {
				case NULL:
					$sortdirection = $sortObj->sortdirection;
				case "desc":
				default:
					$dir = "DESC";
					break;
				case "asc":
					$dir = "ASC";
					$sticky = false; //makes no sense
					break;
			}
			// sortorder and sortdirection (only used for all news articles and categories naturally)
			switch ($sortorder) {
				case NULL:
					$sortorder = $sortObj->sortorder;
				case "date":
				default:
					$sort1 = "date";
					break;
				case 'lastchange':
					$sort1 = 'lastchange';
					break;
				case "id":
					$sort1 = "id";
					break;
				case "title":
					$sort1 = "title";
					break;
				case "popular":
					$sort1 = 'hitcounter';
					break;
				case "mostrated":
					$sort1 = 'total_votes';
					break;
				case "toprated":
					$sort1 = '(total_value/total_votes) DESC, total_value';
					$dir = '';
					break;
				case "random":
					$sort1 = 'RAND()';
					break;
			}

			/** get all articles * */
			switch ($published) {
				case "published":
					$show = "$showConjunction `show` = 1 AND date <= '" . date('Y-m-d H:i:s') . "'";
					$getUnpublished = false;
					break;
				case "published-unpublished":
					$show = "$showConjunction `show` = 1 AND date <= '" . date('Y-m-d H:i:s') . "'";
					$getUnpublished = true;
					break;
				case "unpublished":
					$show = "$showConjunction `show` = 0 AND date <= '" . date('Y-m-d H:i:s') . "'";
					$getUnpublished = true;
					break;
				case 'sticky':
					$show = ' `sticky` <> 0';
					$getUnpublished = true;
					break;
				case "all":
					$getUnpublished = true;
					$show = "";
					break;
			}
			$order = " ORDER BY $sticky";

			if (in_context(ZP_ZENPAGE_NEWS_DATE)) {
				$datesearch = '';
				switch ($published) {
					case "published":
						$datesearch = "date LIKE '$_zp_post_date%' ";
						break;
					case "unpublished":
						$datesearch = "date LIKE '$_zp_post_date%' ";
						break;
					case "all":
						$datesearch = "date LIKE '$_zp_post_date%' ";
						break;
				}
				if ($datesearch) {
					if ($show) {
						$datesearch = ' AND ' . $datesearch;
					} else {
						$datesearch = ' WHERE ' . $datesearch;
					}
				}
				$order .= " date DESC";
			} else {
				$datesearch = "";
				if ($category)
					$order .= ' news.';
				$order .= " $sort1 $dir";
			}
			if ($category) {
				$sql = "SELECT DISTINCT news.date, news.title, news.titlelink FROM " . prefix('news') . " as news, " . prefix('news2cat') . " as cat WHERE" . $cat . $show . $order;
			} else {
				$sql = "SELECT date, title, titlelink FROM " . prefix('news') . $show . $datesearch . " " . $order;
			}
			$resource = query($sql);
			$result = array();
			if ($resource) {
				while ($item = db_fetch_assoc($resource)) {
					$article = new ZenpageNews($item['titlelink']);
					if ($getUnpublished || $article->isMyItem(ZENPAGE_NEWS_RIGHTS) || $currentcategory && ($article->inNewsCategory($currentcategory)) || $article->categoryIsVisible()) {
						$result[] = $item;
					}
				}
				db_free_result($resource);
				if ($sort1 == 'title') { // multi-lingual field!
					$result = sortByMultilingual($result, 'title', $dir == 'DESC');
					if ($sticky) {
						$result = sortMultiArray($result, array('sticky'), true);
					}
				}
			}
			$_zp_newsCache[$newsCacheIndex] = $result;
		}

		if ($articles_per_page) {
			if ($ignorepagination) {
				$offset = 0;
			} else {
				$offset = self::getOffset($articles_per_page);
			}
			$result = array_slice($result, $offset, $articles_per_page);
		}
		return $result;
	}

	/**
	 * Returns an article from the album based on the index passed.
	 *
	 * @param int $index
	 * @return int
	 */
	function getArticle($index, $published = NULL, $sortorder = 'date', $sortdirection = 'desc', $sticky = true) {
		$articles = $this->getArticles(0, NULL, true, $sortorder, $sortdirection, $sticky);
		if ($index >= 0 && $index < count($articles)) {
			$article = $articles[$index];
			$obj = new ZenpageNews($articles[$index]['titlelink']);
			return $obj;
		}
		return false;
	}

	/**
	 * Gets the LIMIT and OFFSET for the query that gets the news articles
	 *
	 * @param int $articles_per_page The number of articles to get
	 * @param bool $ignorepagination If pagination should be ingored so always with the first is started (false is default)
	 * @return string
	 */
	static function getOffset($articles_per_page, $ignorepagination = false) {
		global $_zp_page, $subpage;
		if (OFFSET_PATH) {
			$page = $subpage + 1;
		} else {
			$page = $_zp_page;
		}
		if ($ignorepagination || is_null($page)) { //	maybe from a feed since this means that $_zp_page is not set
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
		if (ZP_COMBINEWS AND !isset($_GET['title']) AND !isset($_GET['category']) AND !isset($_GET['date']) AND OFFSET_PATH != 4) {
			return $this->countCombiNews();
		} else {
			if (empty($_zp_current_category)) {
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
	function getAllArticleDates($yearsonly = false, $order = 'desc') {
		$alldates = array();
		$cleandates = array();
		$sql = "SELECT date FROM " . prefix('news');
		if (!zp_loggedin(MANAGE_ALL_NEWS_RIGHTS)) {
			$sql .= " WHERE `show` = 1";
		}
		$result = query_full_array($sql);
		foreach ($result as $row) {
			$alldates[] = $row['date'];
		}
		foreach ($alldates as $adate) {
			if (!empty($adate)) {
				if ($yearsonly) {
					$cleandates[] = substr($adate, 0, 4);
				} else {
					$cleandates[] = substr($adate, 0, 7) . "-01";
				}
			}
		}
		$datecount = array_count_values($cleandates);
		switch ($order) {
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
			db_free_result($resource);
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
	 * 											"latestimages-thumbnail-customcrop"
	 * 											"latestimages-sizedimage"
	 * 											"latestalbums-thumbnail"
	 * 		 									"latestalbums-thumbnail-customcrop"
	 * 		 									"latestalbums-sizedimage"
	 * 		 									"latestimagesbyalbum-thumbnail"
	 * 		 									"latestimagesbyalbum-thumbnail-customcrop"
	 * 		 									"latestimagesbyalbum-sizedimage"
	 * 		 									"latestupdatedalbums-thumbnail" (for RSS and getLatestNews() used only)
	 * 		 									"latestupdatedalbums-thumbnail-customcrop" (for RSS and getLatestNews() used only)
	 * 		 									"latestupdatedalbums-sizedimage" (for RSS and getLatestNews() used only)
	 * 	NOTE: The "latestupdatedalbums" variants do NOT support pagination as required on the news loop!
	 *
	 * @param string $published "published" for published articles,
	 * 													"unpublished" for un-published articles,
	 * 													"all" for all articles
	 * @param string $sortorder 	id, date or mtime, only for latestimages-... modes
	 * @param bool $sticky set to true to place "sticky" articles at the front of the list.
	 * @param string $direction 	"desc" or "asc"
	 * @return array
	 */
	function getCombiNews($articles_per_page = '', $mode = '', $published = NULL, $sortorder = NULL, $sticky = true, $sortdirection = 'desc') {
		global $_zp_combiNews_cache, $_zp_gallery;
		if (is_null($published)) {
			if (zp_loggedin(ZENPAGE_NEWS_RIGHTS | ALL_NEWS_RIGHTS)) {
				$published = "all";
			} else {
				$published = "published";
			}
		}

		if (empty($mode)) {
			$mode = ZP_CN_MODE;
		}

		if (isset($_zp_combiNews_cache[$published . $mode . $sticky . $sortorder . $sortdirection])) {
			return $_zp_combiNews_cache[$published . $mode . $sticky . $sortorder . $sortdirection];
		}

		if ($published == "published") {
			$show = " WHERE `show` = 1 AND date <= '" . date('Y-m-d H:i:s') . "'";
			$imagesshow = " AND images.show = 1 ";
		} else {
			$show = "";
			$imagesshow = "";
		}
		getAllAccessibleAlbums($_zp_gallery, $albumlist, false);
		if (empty($albumlist)) {
			$albumWhere = 'albums.`id` is NULL';
		} else {
			$albumWhere = 'albums.`id` in (' . implode(',', $albumlist) . ')';
		}
		if ($articles_per_page) {
			$offset = self::getOffset($articles_per_page);
		} else {
			$offset = 0;
		}
		if (empty($sortorder)) {
			$combinews_sortorder = getOption("zenpage_combinews_sortorder");
		} else {
			$combinews_sortorder = $sortorder;
		}
		$stickyorder = '';
		if ($sticky) {
			$stickyorder = 'sticky DESC,';
		}
		switch ($sortdirection) {
			case 'desc':
			default:
				$sortdir = 'DESC';
				break;
			case 'asc':
				$sortdir = 'ASC';
				break;
		}
		$type3 = query("SET @type3:='0'");
		switch ($mode) {
			case "latestimages-thumbnail":
			case "latestimages-thumbnail-customcrop":
			case "latestimages-sizedimage":
			case "latestimages-sizedimage-maxspace":
			case "latestimages-fullimage":
				$albumWhere = ' AND ' . $albumWhere;
				$sortorder = $combinews_sortorder;
				$type1 = query("SET @type1:='news'");
				$type2 = query("SET @type2:='images'");
				switch ($combinews_sortorder) {
					case 'id':
					case 'date':
						$imagequery = "(SELECT albums.folder, images.filename, images.date, @type2, @type3 as sticky FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums
							WHERE albums.id = images.albumid " . $imagesshow . $albumWhere . ")";
						break;
					case 'publishdate':
						$imagequery = "(SELECT albums.folder, images.filename, IFNULL(images.publishdate,images.date), @type2, @type3 as sticky FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums
													WHERE albums.id = images.albumid " . $imagesshow . $albumWhere . ")";
					case 'mtime':
						$imagequery = "(SELECT albums.folder, images.filename, FROM_UNIXTIME(images.mtime), @type2, @type3 as sticky FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums
							WHERE albums.id = images.albumid " . $imagesshow . $albumWhere . ")";
						break;
				}
				$result = $this->siftResults("(SELECT title as albumname, titlelink, date, @type1 as type, sticky FROM " . prefix('news') . " " . $show . ")
																		UNION
																		" . $imagequery . "
																		ORDER BY $stickyorder date " . $sortdir, $offset, $articles_per_page);
				break;
			case "latestalbums-thumbnail":
			case "latestalbums-thumbnail-customcrop":
			case "latestalbums-sizedimage":
			case "latestalbums-sizedimage-maxspace":
			case "latestalbums-fullimage":
				if (empty($show)) {
					$albumWhere = ' WHERE ' . $albumWhere;
				} else {
					$albumWhere = ' AND ' . $albumWhere;
				}
				$sortorder = $combinews_sortorder;
				$type1 = query("SET @type1:='news'");
				$type2 = query("SET @type2:='albums'");
				switch ($combinews_sortorder) {
					case 'id':
					case 'date':
						$albumquery = "(SELECT albums.folder, albums.title, albums.date, @type2, @type3 as sticky FROM " . prefix('albums') . " AS albums
							" . $show . $albumWhere . ")";
						break;
					case 'publishdate':
						$albumquery = "(SELECT albums.folder, albums.title, IFNULL(albums.publishdate,albums.date), @type2, @type3 as sticky FROM " . prefix('albums') . " AS albums
													" . $show . $albumWhere . ")";
						break;
					case 'mtime':
						$albumquery = "(SELECT albums.folder, albums.title, FROM_UNIXTIME(albums.mtime), @type2, @type3 as sticky FROM " . prefix('albums') . " AS albums
							" . $show . $albumWhere . ")";
						break;
				}
				$result = $this->siftResults("(SELECT title as albumname, titlelink, date, @type1 as type, sticky FROM " . prefix('news') . " " . $show . ")
																		UNION
																		" . $albumquery . "
																		ORDER BY $stickyorder date " . $sortdir, $offset, $articles_per_page);
				break;
			case "latestimagesbyalbum-thumbnail":
			case "latestimagesbyalbum-thumbnail-customcrop":
			case "latestimagesbyalbum-sizedimage":
			case "latestimagesbyalbum-sizedimage-maxspace":
			case "latestimagesbyalbum-fullimage":
				$albumWhere = ' AND ' . $albumWhere;
				$type1 = query("SET @type1:='news'");
				$type2 = query("SET @type2:='albums'");
				if (empty($combinews_sortorder) || $combinews_sortorder != "date" || $combinews_sortorder != "mtime" || $combinews_sortorder != "publishdate") {
					$combinews_sortorder = "date";
				}
				$sortorder = "images." . $combinews_sortorder;
				switch ($combinews_sortorder) {
					case "date":
						$imagequery = "(SELECT DISTINCT DATE_FORMAT(" . $sortorder . ",'%Y-%m-%d'), albums.folder, DATE_FORMAT(images.date,'%Y-%m-%d'), @type2, @type3 as sticky FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums
														WHERE albums.id = images.albumid " . $imagesshow . $albumWhere . ")";
						break;
					case "mtime":
						$imagequery = "(SELECT DISTINCT FROM_UNIXTIME(" . $sortorder . ",'%Y-%m-%d'), albums.folder, DATE_FORMAT(images.mtime,'%Y-%m-%d'), @type2, @type3 as sticky FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums
														WHERE albums.id = images.albumid " . $imagesshow . $albumWhere . ")";
					case "publishdate":
						$imagequery = "(SELECT DISTINCT FROM_UNIXTIME(" . $sortorder . ",'%Y-%m-%d'), albums.folder, DATE_FORMAT(images.publishdate,'%Y-%m-%d'), @type2, @type3 as sticky FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums
																				WHERE albums.id = images.albumid " . $imagesshow . $albumWhere . ")";
						break;
				}
				$result = $this->siftResults("(SELECT title as albumname, titlelink, date, @type1 as type, sticky FROM " . prefix('news') . " " . $show . ")
																		UNION
																		" . $imagequery . "
																		ORDER By $stickyorder date " . $sortdir, $offset, $articles_per_page);
				break;
			case "latestupdatedalbums-thumbnail":
			case "latestupdatedalbums-thumbnail-customcrop":
			case "latestupdatedalbums-sizedimage":
			case "latestupdatedalbums-sizedimage-maxspace":
			case "latestupdatedalbums-fullimage":
				$latest = $this->getArticles($articles_per_page, NULL, true, 'date', $sortdirection);
				$counter = '';
				foreach ($latest as $news) {
					$article = new ZenpageNews($news['titlelink']);
					if ($article->checkAccess()) {
						$counter++;
						$latestnews[$counter] = array(
										"albumname"	 => $article->getTitle(),
										"titlelink"	 => $article->getTitlelink(),
										"date"			 => $article->getDateTime(),
										"type"			 => "news",
						);
					}
				}
				$albums = getAlbumStatistic($articles_per_page, "latestupdated", '', $sortdirection);
				$latestalbums = array();
				$counter = "";
				foreach ($albums as $album) {
					$counter++;
					$tempalbum = newAlbum($album['folder']);
					$tempalbumthumb = $tempalbum->getAlbumThumbImage();
					$timestamp = $tempalbum->get('mtime');
					if ($timestamp == 0) {
						$albumdate = $tempalbum->getDateTime();
					} else {
						$albumdate = strftime('%Y-%m-%d %H:%M:%S', $timestamp);
					}
					$latestalbums[$counter] = array(
									"albumname"	 => $tempalbum->getFolder(),
									"titlelink"	 => $tempalbum->getTitle(),
									"date"			 => $albumdate,
									"type"			 => 'albums',
					);
				}
				//$latestalbums = array_merge($latestalbums, $item);
				$latest = array_merge($latestnews, $latestalbums);
				$result = sortMultiArray($latest, "date", $sortdirection != 'asc');
				if (count($result) > $articles_per_page) {
					$result = array_slice($result, 0, $articles_per_page);
				}
				break;
		}
		$_zp_combiNews_cache[$published . $mode . $sticky . $sortorder . $sortdirection] = $result;
		return $result;
	}

	/**
	 * CombiNews Feature: Counts all news articles and all images
	 *
	 * @return int
	 */
	function countCombiNews($published = NULL) {
		return count($this->getCombiNews(NULL, NULL, $published));
	}

	/**
	 * Returns the full path of the news index page (news page 1) or if the "news on zp index" option is set a link to the gallery index.
	 *
	 * @return string
	 */
	function getNewsIndexURL() {
		if ($this->news_on_index) {
			return getGalleryIndexURL(false);
		} else {
			return rewrite_path(_NEWS_, "/index.php?p=news");
		}
	}

	/**
	 * Returns partial path of news category
	 *
	 * @return string
	 */
	function getNewsCategoryPath($category, $page = NULL) {
		if ($page) {
			return rewrite_path('/' . _CATEGORY_ . '/' . $category . '/' . $page, "/index.php?p=news&category=$category&page=$page");
		} else {
			return rewrite_path('/' . _CATEGORY_ . '/' . $category, "/index.php?p=news&category=$category");
		}
	}

	/**
	 * Returns partial path of news date archive
	 *
	 * @return string
	 */
	function getNewsArchivePath($date, $page = NULL) {
		if ($page > 1) {
			return rewrite_path('/' . _NEWS_ARCHIVE_ . '/' . $date . '/' . $page, "/index.php?p=news&date=$date&page=$page");
		} else {
			return rewrite_path('/' . _NEWS_ARCHIVE_ . '/' . $date, "/index.php?p=news&date=$date");
		}
	}

	/**
	 * Returns partial path of news article title
	 *
	 * @return string
	 */
	function getNewsTitlePath($title) {
		return rewrite_path('/' . _NEWS_ . "/$title", "/index.php?p=news&title=$title");
	}

	/*	 * ********************************* */
	/* general news category functions  */
	/*	 * ********************************* */

	/**
	 * Gets the category link of a category
	 *
	 * @param string $catname the title of the category
	 * @return string
	 */
	function getCategoryLink($catname) {
		foreach ($this->getAllCategories(false) as $cat) {
			if ($cat['titlelink'] == $catname) {
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
		foreach ($this->getAllCategories(false) as $cat) {
			if ($cat['id'] == $id) {
				return $cat;
			}
		}
		return '';
	}

	/**
	 * Gets all categories
	 * @param bool $visible TRUE for published and unprotected
	 * @param string $sorttype NULL for the standard order as sorted on the backend, "title", "id", "popular", "random"
	 * @param string $sortdirection "asc" or "desc" for ascending or descending order
	 * @return array
	 */
	function getAllCategories($visible = true, $sorttype = NULL, $sortdirection = NULL) {
		$structure = $this->getCategoryStructure();
		switch ($sortdirection) {
			case 'asc':
			default:
				$sortdir = FALSE;
				break;
			case 'desc':
				$sortdir = TRUE;
				break;
		}
		switch ($sorttype) {
			case "id":
				$sortorder = "id";
				break;
			case "title":
				$sortorder = "title";
				break;
			case "popular":
				$sortorder = 'hitcounter';
				break;
			case "random":
				$sortorder = 'random';
				break;
			default:
				$sortorder = "sort_order";
				break;
		}
		if ($visible) {
			foreach ($structure as $key => $cat) {
				$catobj = new ZenpageCategory($cat['titlelink']);
				if ($catobj->getShow() || $catobj->isMyItem(LIST_RIGHTS)) {
					$structure[$key]['show'] = 1;
				} else {
					unset($structure[$key]);
				}
			}
		}

		if (!is_null($sorttype) || !is_null($sortdirection)) {
			if ($sorttype == 'random') {
				shuffle($structure);
			} else {
				$structure = sortMultiArray($structure, $sortorder, $sortdir, true, false, false);
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

}

// ZenpageCMS

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

	/*	 * '
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
		$this->set("titlelink", $v);
	}

}

// Zenpage main class end

/**
 *
 * Base class from which Zenpage news articles and pages derive
 *
 */
class ZenpageItems extends ZenpageRoot {

	var $sortorder;
	var $sortdirection;
	var $sortSticky = true;

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
		$this->set("author", $a);
	}

	/**
	 * Returns the content
	 *
	 * @return string
	 */
	function getContent($locale = NULL) {
		$text = $this->get("content");
		if ($locale == 'all') {
			return zpFunctions::unTagURLs($text);
		} else {
			return applyMacros(zpFunctions::unTagURLs(get_language_string($text, $locale)));
		}
	}

	/**
	 *
	 * Set the content datum
	 * @param $c full language string
	 */
	function setContent($c) {
		$c = zpFunctions::tagURLs($c);
		$this->set("content", $c);
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
			if ($newtime === false)
				return;
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
		$this->set("lastchangeauthor", $a);
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
		$this->set("locked", $l);
	}

	/**
	 * Returns the extra content
	 *
	 * @return string
	 */
	function getExtraContent($locale = NULL) {
		$text = $this->get("extracontent");
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = zpFunctions::unTagURLs($text);
		return $text;
	}

	/**
	 * sets the extra content
	 *
	 */
	function setExtraContent($ec) {
		$this->set("extracontent", zpFunctions::tagURLs($ec));
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
			if ($newtime === false)
				return;
			$this->set('expiredate', $newtime);
		} else {
			$this->set('expiredate', NULL);
		}
	}

	/**
	 *
	 * @param bool $value
	 */
	function setSortDirection($value) {
		$this->sort_direction = (int) ($value && true);
	}

	function setSortSticky($value) {
		$this->sortSticky = (bool) $value;
	}

	/**
	 *
	 * @param string $value
	 */
	function setSortType($value) {
		$this->sorttype = $value;
	}

}

?>