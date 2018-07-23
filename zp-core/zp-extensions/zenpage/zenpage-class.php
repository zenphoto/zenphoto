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
//TODO: on the 1.4.7 release these combinews defines can be removed.
define('ZENPAGE_COMBINEWS', false);
define('ZP_COMBINEWS', false);

define('ZP_SHORTENINDICATOR', $shortenindicator = getOption('zenpage_textshorten_indicator'));
define('ZP_SHORTEN_LENGTH', getOption('zenpage_text_length'));
define('ZP_READ_MORE', getOption("zenpage_read_more"));
define('ZP_ARTICLES_PER_PAGE', getOption("zenpage_articles_per_page"));

class Zenpage {

	public $categoryStructure = array();
	// article defaults (mirrors category vars)
	protected $sortorder = 'date';
	protected $sortdirection = true;
	protected $sortSticky = true;
	// page defaults
	protected $page_sortorder;
	protected $page_sortdirection;
	/**
	 * Class instantiator
	 */
	function __construct() {
		$allcategories = query_full_array("SELECT * FROM " . prefix('news_categories') . " ORDER by sort_order");
		$this->categoryStructure = array();
		foreach ($allcategories as $cat) {
			$this->categoryStructure[$cat['id']] = $cat;
		}
	}

	static function expiry() {
		/**
		 * Un-publishes pages/news whose expiration date has been reached
		 *
		 */
		$sql = ' WHERE `date`<="' . date('Y-m-d H:i:s') . '" AND `show`="1"' .
						' AND `expiredate`<="' . date('Y-m-d H:i:s') . '"' .
						' AND `expiredate`!="0000-00-00 00:00:00"' .
						' AND `expiredate` IS NOT NULL';
		foreach (array('news' => 'ZenpageNews', 'pages' => 'ZenpagePage') as $table => $class) {
			$result = query_full_array('SELECT * FROM ' . prefix($table) . $sql);
			if ($result) {
				foreach ($result as $item) {
					$obj = new $class($item['titlelink']);
					$obj->setShow(0);
					$obj->save();
				}
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
	 * @param string $sortdirection false for ascenting, true for descending
	 * @param string $author Optional author name to get the pages of
	 * @return array
	 */
	function getPages($published = NULL, $toplevel = false, $number = NULL, $sorttype = NULL, $sortdirection = NULL, $author = null) {
		global $_zp_loggedin;
		if (is_null($sortdirection)) {
			$sortdirection = $this->getSortDirection('pages');
		}
		if (is_null($sorttype)) {
			$sorttype = $this->getSortType('pages');
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
		if ($author) {
			$show .= ' AND author = ' . db_quote($author);
		}
		if ($sortdirection) {
			$sortdir = ' DESC';
		} else {
			$sortdir = ' ASC';
		}
		switch ($sorttype) {
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
				$sortdir = '';
				break;
			default:
				$sortorder = 'sort_order';
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
	 * Returns a list of Zenpage page IDs that the current viewer is not allowed to see
	 * Helper function to be used with getAllTagsUnique() and getAllTagsCount()
	 * Note if the Zenpage plugin is not enabled but items exists this returns no IDs so you need an extra check afterwards!
	 *
	 * @return array
	 */
	function getNotViewablePages() {
		global $_zp_not_viewable_pages_list;
		if (zp_loggedin(ADMIN_RIGHTS | ALL_PAGES_RIGHTS)) {
			return array(); //admins can see all
		}
		if (is_null($_zp_not_viewable_pages_list)) {
			$items = $this->getPages(true, false, NULL, NULL, NULL);
			if (!is_null($items)) {
				$_zp_not_viewable_pages_list = array();
				foreach ($items as $item) {
					$obj = new ZenpageNews($item['titlelink']);
					if (!$obj->isProtected()) {
						$_zp_not_viewable_pages_list[] = $obj->getID();
					}
				}
			}
		}
		return $_zp_not_viewable_pages_list;
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
	 * @param bool $sortdirection TRUE for descending, FALSE for ascending. Note: This parameter is not used for date archives
	 * @param bool $sticky set to true to place "sticky" articles at the front of the list.
	 * @param obj $category Optional category to get the article from
	 * @param string $author Optional author name to get the articles of
	 * @return array
	 */
	function getArticles($articles_per_page = 0, $published = NULL, $ignorepagination = false, $sortorder = NULL, $sortdirection = NULL, $sticky = NULL, $category = NULL, $author = null) {
		global $_zp_current_category, $_zp_post_date, $_zp_newsCache;
		$getunpublished_myitems = false;
		$cat = '';
		if (empty($published)) {
			if (zp_loggedin(ZENPAGE_NEWS_RIGHTS) || ($category && $category->isMyItem(ZENPAGE_NEWS_RIGHTS))) { // lower rights, additionally checked below
				$published = "all"; 
				// without explicitly $published == 'all' we only want all the logged in is allowed to get
				$getunpublished_myitems = true; 
			} else {
				$published = "published";
			}
		}
		if ($category) {
			$sortObj = $category;
		} else if (is_object($_zp_current_category)) {
			$sortObj = $_zp_current_category;
		} else {
			$sortObj = $this;
		}
		if (is_null($sticky)) {
			$sticky = $sortObj->getSortSticky();
		}

		if (is_null($sortdirection)) {
			$sortdirection = $sortObj->getSortDirection('news');
		}
		if (is_null($sortorder)) {
			$sortorder = $sortObj->getSortType('news');
		}
		$newsCacheIndex = "$sortorder-$sortdirection-$published" . (bool) $sticky;
		if ($category) {
			$newsCacheIndex .= '-' . $category->getTitlelink();
		}
		if($author) {
			$newsCacheIndex .= '-' . $author;
		}  
		if (isset($_zp_newsCache[$newsCacheIndex])) {
			$result = $_zp_newsCache[$newsCacheIndex];
		} else {
			$show = $currentcategory = false;
			if ($category) {
				$currentcategory = $category->getTitlelink();
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

			if ($sticky) {
				$sticky = 'sticky DESC,';
			}
			if ($sortdirection) {
				$dir = " DESC";
			} else {
				$dir = " ASC";
			}
			// sortorder and sortdirection (only used for all news articles and categories naturally)
			switch ($sortorder) {
				case "date":
				default:
					$sort1 = "date" . $dir;
					break;
				case 'lastchange':
					$sort1 = 'lastchange' . $dir;
					break;
				case "id":
					$sort1 = "id" . $dir;
					break;
				case "title":
					$sort1 = "title" . $dir;
					break;
				case "popular":
					$sort1 = 'hitcounter' . $dir;
					break;
				case "mostrated":
					$sort1 = 'total_votes' . $dir;
					break;
				case "toprated":
					$sort1 = '(total_value/total_votes) DESC, total_value';
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
					$show = "$showConjunction `sticky` <> 0";
					$getUnpublished = true;
					break;
				case "all":
					$show = false;
					$getUnpublished = true;
					if($getunpublished_myitems) {
						$getUnpublished = false;
					}
					break;
			}
			if ($author) {
				if($cat || $show) {
					$author_conjuction = ' AND ';
				} else {
					$author_conjuction = ' WHERE ';
				}
				$show .= $author_conjuction . ' author = ' .db_quote($author);
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
				if ($sortdirection || is_null($sortdirection)) {
					$order .= ' date DESC';
				} else {
					$order .= ' date ASC';
				}
			} else {
				$datesearch = "";
				if ($category) {
					$order .= ' news.';
				} else {
					$order .= ' ';
				}
				$order .= $sort1;
			}
			if ($category) {
				$sql = "SELECT DISTINCT news.date, news.title, news.titlelink, news.sticky FROM " . prefix('news') . " as news, " . prefix('news2cat') . " as cat WHERE" . $cat . $show . $order;
			} else {
				$sql = "SELECT date, title, titlelink, sticky FROM " . prefix('news') . $show . $datesearch . " " . $order;
			}
			$resource = query($sql);
			$result = array();
			if ($resource) {
				while ($item = db_fetch_assoc($resource)) {
					$article = new ZenpageNews($item['titlelink']); 
					if ($getUnpublished && $article->isMyItem(LIST_RIGHTS) || ($currentcategory && $article->inNewsCategory($currentcategory)) || $article->categoryIsVisible()) {
						$result[] = $item;
					}
				}
				db_free_result($resource);
				if ($sortorder == 'title') { // multi-lingual field!
					$result = sortByMultilingual($result, 'title', $sortdirection);
					if ($sticky) {
						$stickyItems = array();
						foreach ($result as $key => $element) {
							if ($element['sticky']) {
								array_unshift($stickyItems, $element);
								unset($result[$key]);
							}
						}
						$stickyItems = sortMultiArray($stickyItems, 'sticky', true);
						$result = array_merge($stickyItems, $result);
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
	 * Returns a list of Zenpage news article IDs that the current viewer is not allowed to see
	 * Helper function to be used with getAllTagsUnique() and getAllTagsCount() or db queries only
	 * Note if the Zenpage plugin is not enabled but items exists this returns no IDs so you need an extra check afterwards!
	 *
	 * @return array
	 */
	function getNotViewableNews() {
		global $_zp_not_viewable_news_list;
		if (zp_loggedin(ADMIN_RIGHTS | ALL_NEWS_RIGHTS)) {
			return array(); //admins can see all
		}
		if (is_null($_zp_not_viewable_news_list)) {
			$items = $this->getArticles(0, 'published', true, NULL, NULL, NULL, NULL);
			if (!is_null($items)) {
				$_zp_not_viewable_news_list = array();
				foreach ($items as $item) {
					$obj = new ZenpageNews($item['titlelink']);
					if ($obj->isProtected()) {
						$_zp_not_viewable_news_list[] = $obj->getID();
					}
				}
			}
		}
		return $_zp_not_viewable_news_list;
	}

	/**
	 * Returns an article from the album based on the index passed.
	 *
	 * @param int $index
	 * @return int
	 */
	function getArticle($index, $published = NULL, $sortorder = NULL, $sortdirection = NULL, $sticky = true) {
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
	 */
	function getTotalArticles() {
		global $_zp_current_category;
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
	
	/**
	 * Gets the total news pages
	 */
	function getTotalNewsPages() {
		return ceil($this->getTotalArticles() / ZP_ARTICLES_PER_PAGE);
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

	/*	 * ********************************* */
	/* general news category functions  */
	/*	 * ********************************* */


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
	 * @param bool $sortdirection TRUE for ascending or FALSE for descending order
	 * @return array
	 */
	function getAllCategories($visible = true, $sorttype = NULL, $sortdirection = NULL) {
		$structure = $this->getCategoryStructure();
		if (is_null($sortdirection))
			$sortdirection = $this->sortdirection;

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
				//sortMultiArray descending = true
				if($sortdirection) {
					$sortdir = false;
				} else {
					$sortdir = true;
				}
				$structure = sortMultiArray($structure, $sortorder, $sortdir, true, false, false);
			}
		}
		return $structure;
	}
	
	/**
	 * Gets all authors assigned to news articles or pages
	 * 
	 * @param string $type "news" or "pages"
	 * @return array
	 */
	static public function getAllAuthors($type = 'news') {
		$authors = array();
		switch($type) {
			default:
			case 'news':
				$table = 'news';
				break;
			case 'pages':
				$table = 'pages';
				break;
		}
		$sql = 'SELECT DISTINCT author FROM ' . prefix($table) . ' ORDER BY author ASC';
		$resource = query($sql);
		if ($resource) {
			while ($item = db_fetch_assoc($resource)) {
				$authors[] = $item['author'];
			}
		}
		db_free_result($resource);
		return $authors;
	}

	/**
	 *
	 * "Magic" function to return a string identifying the object when it is treated as a string
	 * @return string
	 */
	public function __toString() {
		return 'Zenpage';
	}

	function getSortDirection($what = 'news') {
		if ($what == 'pages') {
			return $this->page_sortdirection;
		} else {
			return $this->sortdirection;
		}
	}

	function setSortDirection($value, $what = 'news') {
		if ($what == 'pages') {
			$this->page_sortdirection = (int) ($value && true);
		} else {
			$this->sortdirection = (int) ($value && true);
		}
	}

	function getSortType($what = 'news') {
		if ($what == 'pages') {
			return $this->page_sortorder;
		} else {
			return $this->sortorder;
		}
	}

	function setSortType($value, $what = 'news') {
		if ($what == 'pages') {
			$this->page_sortorder = $value;
		} else {
			$this->sortorder = $value;
		}
	}

	function getSortSticky() {
		return $this->sortSticky;
	}

	function setSortSticky($value) {
		$this->sortSticky = (bool) $value;
	}

}

// ZenpageCMS

/**
 *
 * Base class from which all Zenpage classes derive
 *
 */
class ZenpageRoot extends ThemeObject {

	protected $sortorder;
	protected $sortdirection;
	protected $sortSticky = true;

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
			return unTagURLs($text);
		} else {
			return applyMacros(unTagURLs(get_language_string($text, $locale)));
		}
	}

	/**
	 *
	 * Set the content datum
	 * @param $c full language string
	 */
	function setContent($c) {
		$c = tagURLs($c);
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
		if ($locale == 'all') {
			return unTagURLs($text);
		} else {
			return applyMacros(unTagURLs(get_language_string($text, $locale)));
		}
	}

	/**
	 * sets the extra content
	 *
	 */
	function setExtraContent($ec) {
		$this->set("extracontent", tagURLs($ec));
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

}

?>