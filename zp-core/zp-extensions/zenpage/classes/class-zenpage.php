<?php

/**
 * Some global variable setup
 */
//TODO: on the 1.4.7 release these combinews defines can be removed.
define('ZENPAGE_COMBINEWS', false);
define('ZP_COMBINEWS', false);

define('ZP_SHORTENINDICATOR', $shortenindicator = getOption('zenpage_textshorten_indicator'));
define('ZP_SHORTEN_LENGTH', getOption('zenpage_text_length'));
define('ZP_READ_MORE', getOption("zenpage_read_more"));
define('ZP_ARTICLES_PER_PAGE', (getOption("zenpage_articles_per_page") >= 1 ? getOption("zenpage_articles_per_page") : 1));

/**
 * Zenpage root classes
 * @author Stephen Billard (sbillard), Malte Müller (acrylian)
 * @package zpcore\plugins\zenpage\classes
 */
class Zenpage {

	public $categoryStructure = null;
	// article defaults (mirrors category vars)
	protected $sorttype = 'date';
	protected $sortdirection = true; // descending
	protected $sortSticky = true;
	
	// category defaults
	protected $category_sorttype = 'sort_order';
	protected $category_sortdirection = false; // ascending

	// page defaults
	protected $page_sorttype = 'sort_order';
	protected $page_sortdirection = false; //ascending
	/**
	 * Class instantiator
	 */
	function __construct() {
		
	}

	static function expiry() {
		global $_zp_db;
		/**
		 * Un-publishes pages/news whose expiration date has been reached
		 *
		 */
		$sql = ' WHERE `date`<="' . date('Y-m-d H:i:s') . '" AND `show`="1"' .
						' AND `expiredate`<="' . date('Y-m-d H:i:s') . '"' .
						' AND `expiredate`!="0000-00-00 00:00:00"' .
						' AND `expiredate` IS NOT NULL';
		foreach (array('news' => 'ZenpageNews', 'pages' => 'ZenpagePage') as $table => $class) {
			$result = $_zp_db->queryFullArray('SELECT * FROM ' . $_zp_db->prefix($table) . $sql);
			if ($result) {
				foreach ($result as $item) {
					$obj = new $class($item['titlelink']);
					$obj->setPublished(0);
					$obj->save();
				}
			}
		}
	}

	/**
	 * Provides the complete category structure regardless of permissions.
	 * This is needed for quick checking of status of a category and is used only internally to the Zenpage core.
	 * 
	 * @return array
	 */
	private function getCategoryStructure() {
		global $_zp_db;
		if (is_null($this->categoryStructure)) {
			$allcategories = $_zp_db->queryFullArray("SELECT * FROM " . $_zp_db->prefix('news_categories') . " ORDER by sort_order");
			if ($allcategories) {
				$this->categoryStructure = array();
				foreach ($allcategories as $cat) {
					$this->categoryStructure[$cat['id']] = $cat;
				}
				return $this->categoryStructure;
			}
			return $this->categoryStructure = array();
		} else {
			return $this->categoryStructure;
		}	
	}

	/*	 * ********************************* */
	/* general page functions   */
	/*	 * ********************************* */
	/**
	 * Checks if a category itself is published.
	 * 
	 * @deprecated 2.0 Use the method isPublic() of the ZenpageCategory class instead.
	 * @param type $cat
	 * @return type
	 */
	function visibleCategory($cat) {
		Zenpage_internal_deprecations::visibleCategory();
		$categorystructure = $this->getCategoryStructure();
		return $categorystructure[$cat['cat_id']]['show'];
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
	 * @param obj $pageobj Optional pageobj to get its subpages
	 * @return array
	 */
	function getPages($published = NULL, $toplevel = false, $number = NULL, $sorttype = NULL, $sortdirection = NULL, $author = null, $pageobj = null) {
		global $_zp_loggedin, $_zp_db;
		if(!is_null($pageobj) && get_class($pageobj) != 'ZenpagePage') {
			$pageobj = null;
		}
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
		if ($toplevel) {
			if ($pageobj) {
				$gettop = " parentid = " . $pageobj->getID();
			} else {
				$gettop = " parentid IS NULL";
			}
		} else {
			if ($pageobj) {
				$gettop = " sort_order like '" . $pageobj->getSortorder() . "-%'";
			} 
		}
		if ($published) {
			if ($gettop) {
				$gettop = ' AND' . $gettop;
			}
			$show = " WHERE `show` = 1 AND date <= '" . date('Y-m-d H:i:s') . "'" . $gettop;
		} else {
			if ($gettop) {
				$gettop = ' WHERE' . $gettop;
			}
			$show = $gettop;
		}
		if ($author) {
			$show .= ' AND author = ' . $_zp_db->quote($author);
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
		$result = $_zp_db->query('SELECT * FROM ' . $_zp_db->prefix('pages') . $show . ' ORDER by `' . $sortorder . '`' . $sortdir);
		if ($result) {
			while ($row = $_zp_db->fetchAssoc($result)) {
				$page = new ZenpagePage($row['titlelink']);
				if ($all || $page->isVisible()) {
					$all_pages[] = $row;
				} 
				if ($number && count($all_pages) >= $number) {
					break;
				}
			}
			$_zp_db->freeResult($result);
		}
		if ($sorttype == 'title') {
			$all_pages = sortMultiArray($all_pages, 'title', $sortdirection, true, false, false);
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
					if (!$obj->isVisible()) {
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
	 * @param obj $category Object of category to get articles of only
	 * @param string $author Optional author name to get the article of
	 * @param string|null|false $date Date YYYY-mm format for a date archive, default null uses global theme date archive context, set to false to disable date archive context
	 * @return array
	 */
	function getArticles($articles_per_page = 0, $published = NULL, $ignorepagination = false, $sortorder = NULL, $sortdirection = NULL, $sticky = NULL, $category = NULL, $author = null, $date = null) {
		global $_zp_current_category, $_zp_post_date, $_zp_news_cache, $_zp_db;
		$getunpublished_myitems = false;
		$cat = '';
		$postdate = null;
		if ($date !== false) {
			if (is_null($date) && in_context(ZP_ZENPAGE_NEWS_DATE)) {
				$postdate = $_zp_post_date;
			} else {
				$postdate = $date;
			}
		}
		if (empty($published)) {
			if (zp_loggedin(ALL_NEWS_RIGHTS) || ($category && $category->isMyItem(ALL_NEWS_RIGHTS))) { // lower rights, additionally checked below
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
		$newsCacheIndex = "$sortorder-$sortdirection-$published-" . (bool) $sticky;
		if ($category) {
			$newsCacheIndex .= '-' . $category->getName();
		}
		if ($author) {
			$newsCacheIndex .= '-' . $author;
		}
		if (isset($_zp_news_cache[$newsCacheIndex])) {
			$result = $_zp_news_cache[$newsCacheIndex];
		} else {
			$show = $currentcategory = false;
			if ($category) {
				$currentcategory = $category->getName();
				$showConjunction = ' AND ';
				// new code to get nested cats
				$catid = $category->getID();
				$subcats = $category->getCategories(false, null, null, false);
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
				$sticky = 'news.sticky DESC,';
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
					$sort1 = "news.date" . $dir;
					break;
				case 'lastchange':
					$sort1 = 'news.lastchange' . $dir;
					break;
				case "id":
					$sort1 = "news.id" . $dir;
					break;
				case "title":
					$sort1 = "news.title" . $dir;
					break;
				case "popular":
					$sort1 = 'news.hitcounter' . $dir;
					break;
				case "mostrated":
					$sort1 = 'news.total_votes' . $dir;
					break;
				case "toprated":
					$sort1 = '(news.total_value/news.total_votes) DESC, news.total_value';
					break;
				case "random":
					$sort1 = 'RAND()';
					break;
			}
			/** get all articles * */
			$getall = false;
			switch ($published) {
				case "published":
					$show = "$showConjunction news.show = 1 AND news.date <= '" . date('Y-m-d H:i:s') . "'";
					$getUnpublished = false;
					break;
				case "published-unpublished":
					$show = "$showConjunction news.show = 1 AND news.date <= '" . date('Y-m-d H:i:s') . "'";
					$getUnpublished = true;
					break;
				case "unpublished":
					$show = "$showConjunction news.show = 0 AND news.date <= '" . date('Y-m-d H:i:s') . "'";
					$getUnpublished = true;
					break;
				case 'sticky':
					$show = "$showConjunction news.sticky <> 0";
					$getUnpublished = true;
					$getall = true;
					break;
				case "all":
					$show = false;
					$getUnpublished = true;
					if ($getunpublished_myitems) {
						$getUnpublished = false;
					}
					$getall = true;
					break;
			}
			if ($author) {
				if ($cat || $show) {
					$author_conjuction = ' AND ';
				} else {
					$author_conjuction = ' WHERE ';
				}
				$show .= $author_conjuction . ' news.author = ' . $_zp_db->quote($author);
			}
			$order = " ORDER BY $sticky";	
			if (!empty($postdate)) {
				$datesearch = '';
				switch ($published) {
					case "published":
					case "unpublished":
					case "all":
						$datesearch = "news.date LIKE '$postdate%' ";
						break;
				}
				if ($datesearch) {
					if ($show || $cat) {
						$datesearch = ' AND ' . $datesearch;
					} else {
						$datesearch = ' WHERE ' . $datesearch;
					}
				}
				if ($sortdirection || is_null($sortdirection)) {
					$order .= ' news.date DESC';
				} else {
					$order .= ' news.date ASC';
				}
			} else {
				$datesearch = "";
				$order .= $sort1; 
			}
			if ($category) {
				$sql = "SELECT DISTINCT news.date, news.title, news.titlelink, news.sticky FROM " . $_zp_db->prefix('news') . " as news, " . $_zp_db->prefix('news2cat') . " as cat WHERE" . $cat . $show . $datesearch . ' ' . $order;
			} else {
				$sql = "SELECT news.date, news.title, news.titlelink, news.sticky FROM " . $_zp_db->prefix('news') . ' as news ' . $show . $datesearch . " " . $order;
			}
			$resource = $_zp_db->query($sql);
			$result = array();
			if ($resource) {
				while ($item = $_zp_db->fetchAssoc($resource)) {
					$article = new ZenpageNews($item['titlelink']);
					if (($currentcategory && $article->inNewsCategory($currentcategory)) && ($getall || ($getUnpublished && !$article->isVisible()) || $article->isVisible())) {
						$result[] = $item;
					} else if ($getall || ($getUnpublished && !$article->isVisible()) || $article->isVisible()) {
						$result[] = $item;
					}
				}
				$_zp_db->freeResult($resource);
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
			$_zp_news_cache[$newsCacheIndex] = $result;
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
			$items = $this->getArticles(0, 'all', true, NULL, NULL, NULL, NULL);
			if (!is_null($items)) {
				$_zp_not_viewable_news_list = array();
				foreach ($items as $item) {
					$obj = new ZenpageNews($item['titlelink']);
					if (!$obj->isVisible()) {
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
		global $_zp_page, $pagenumber;
		if (OFFSET_PATH) {
			$page = $pagenumber + 1;
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
	 * @since 1.6 - This gets the news articles of the news index - For categories use the same named method of the Category object
	 */
	function getTotalArticles() {
		return count($this->getArticles(0));
	}

	/**
	 * Gets the total news pages
	 * 
	 * @since 1.6 - This gets the news page number of the news index - For categories use the same named method of the Category object
	 */
	function getTotalNewsPages() {
		return ceil($this->getTotalArticles() / ZP_ARTICLES_PER_PAGE);
	}

	/**
	 * Retrieves a list of all unique years & months. Note if in localized date mode a "-03" day is 
	 * appended because otherwise localized dates are not properly generated
	 * @param bool $yearsonly If set to true only the years' count is returned (Default false)
	 * @param string $order 'desc' (default) or 'asc' for descending or ascending
	 * @return array
	 */
	function getAllArticleDates($yearsonly = false, $order = 'desc') {
		global $_zp_db;
		$alldates = array();
		$cleandates = array();
		$sql = "SELECT date FROM " . $_zp_db->prefix('news');
		$hidenews = $this->getNotViewableNews();
		if (!empty($hidenews)) {
			$sql .= ' WHERE `id` NOT IN('. implode(',', $hidenews) . ')';
		}
		$result = $_zp_db->queryFullArray($sql);
		foreach ($result as $row) {
			$alldates[] = $row['date'];
		}
		foreach ($alldates as $adate) {
			if (!empty($adate)) {
				if ($yearsonly) {
					$cleandates[] = substr($adate, 0, 4);
				} else {
					$cleandates[] = substr($adate, 0, 7);
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
	 * 
	 * @deprecated 2.0 Unused and obsolete method - articles are already filtered via getArticles()
	 * 
	 * @param $sql query to return all candidates of interest
	 * @param $offset skip this many legitimate items (used for pagination)
	 * @param $limit return only this many items
	 */
	private function siftResults($sql, $offset, $limit) {
		global $_zp_db;
		deprecationNotice(gettext('Unused and obsolete method - articles are already filtered via getArticles()'));
		$resource = $result = $_zp_db->query($sql);
		if ($resource) {
			$result = array();
			while ($item = $_zp_db->fetchAssoc($resource)) {
				if ($item['type'] == 'news') {
					$article = new ZenpageNews($item['titlelink']);
					if (!$article->isPublic() && !zp_loggedin(ALL_NEWS_RIGHTS)) { 
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
			$_zp_db->freeResult($resource);
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
	 * 
	 * @param bool $visible TRUE for published and unprotected
	 * @param string $sorttype NULL for the standard order as sorted on the backend, "title", "id", "popular", "random"
	 * @param bool $sortdirection TRUE for descending (default) or FALSE for ascending order
	 * @param bool $toplevel True for only toplevel categories
	 * @return array
	 */
	function getAllCategories($visible = true, $sorttype = NULL, $sortdirection = NULL, $toplevel = false) {
		$structure = $this->getCategoryStructure();
		if (is_null($sortdirection)) {
			$sortdirection = $this->getSortDirection('categories');
		} else {
			// fallback of old documentation
			switch(strtolower($sortdirection)) {
				case 'asc':
					$sortdirection = false;
					trigger_error(gettext('Zenpage::getAllCategories() - The value "asc" for the $sortdirection is deprecated since ZenphotoCMS 1.5.8. Use false instead.'), E_USER_NOTICE);
					break;
				case 'desc':
					trigger_error(gettext('Zenpage::getAllCategories() - The value "desc" for the $sortdirection is deprecated since ZenphotoCMS 1.5.8. Use true instead.'), E_USER_NOTICE);
					$sortdirection = true;
					break;
			}
		}
		if (is_null($sorttype)) {
			$sorttype = $this->getSortType('categories');
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
		if ($toplevel) {
			foreach ($structure as $key => $cat) {
				if (!is_null($cat['parentid'])) {
					unset($structure[$key]);
				}
			}
		}
		if ($visible) {
			foreach ($structure as $key => $cat) {
				$catobj = new ZenpageCategory($cat['titlelink']);
				if ($catobj->isVisible()) {
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
				$structure = sortMultiArray($structure, $sortorder, $sortdirection, true, false, false);
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
		global $_zp_db;
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
		$sql = 'SELECT DISTINCT author FROM ' . $_zp_db->prefix($table) . ' ORDER BY author ASC';
		$resource = $_zp_db->query($sql);
		if ($resource) {
			while ($item = $_zp_db->fetchAssoc($resource)) {
				$authors[] = $item['author'];
			}
		}
		$_zp_db->freeResult($resource);
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
	
	/**
	 * Gets the internal default sortdirection
	 * @param sting $what "new", "pages", "categories"
	 * @return booliean
	 */
	function getSortDirection($what = 'news') {
		switch ($what) {
			case 'pages':
				return $this->page_sortdirection;
			case 'news':
				return $this->sortdirection;
			case 'categories':
				return $this->category_sortdirection;
		}
	}

	/**
	 * Sets the sortdirection
	 * @param boolean $value The  true for decending false for ascending
	 * @param string $what "new", "pages", "categories"
	 */
	function setSortDirection($value, $what = 'news') {
		switch ($what) {
			case 'pages':
				$this->page_sortdirection = (int) ($value && true);
				break;
			case 'news':
				$this->sortdirection = (int) ($value && true);
				break;
			case 'categories':
				$this->category_sortdirection = (int) ($value && true);
				break;
		}
	}

	/**
	 * Gets the sorttype 
	 * @param string $what "new", "pages", "categories"
	 * @return string
	 */
	function getSortType($what = 'news') {
		switch ($what) {
			case 'pages':
				return $this->page_sorttype;
			case 'news':
				return $this->sorttype;
			case 'categories':
				return $this->category_sorttype;
		}
	}

	/**
	 * Sets the sortdtype
	 * @param boolean $value The field/sorttype to sort by
	 * @param string $what "new", "pages", "categories"
	 * @return string
	 */
	function setSortType($value, $what = 'news') {
		switch ($what) {
			case 'pages':
				$this->page_sorttype = $value;
				break;
			case 'news':
				$this->sorttype = $value;
				break;
			case 'categories':
				$this->category_sorttype = $value;
				break;
		}
	}

	function getSortSticky() {
		return $this->sortSticky;
	}

	function setSortSticky($value) {
		$this->sortSticky = (bool) $value;
	}
	
	/**
	 * Gets the default sortorder for a Zenpage caategory or page that does not yet have one, e.g. because newly created
	 * The sortorder takae care of existing ones and add the item after existing items.
	 *  
	 * @since 1.5.8
	 * 
	 * @param string $type "category" or "page"
	 * @return string
	 */
	function getItemDefaultSortorder($type = 'category') {
		if (!in_array($type, array('category', 'page'))) {
			return '000';
		}
		switch ($type) {
			case 'category':
				$items = $this->getAllCategories(false, null, null, true);
				break;
			case 'page':
				$items = $this->getPages(false, true);
				break;
		}
		if (empty($items)) {
			$sortorder = '000';
		} else {
			$count = count($items);
			$sortorder = str_pad($count, 3, "0", STR_PAD_LEFT);
		}
		return $sortorder;
	}

}