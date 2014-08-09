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
define('ZP_SHORTEN_LENGTH', getOption('zenpage_text_length'));
define('ZP_READ_MORE', getOption("zenpage_read_more"));
define('ZP_ARTICLES_PER_PAGE', getOption("zenpage_articles_per_page"));
if (!defined('MENU_TRUNCATE_STRING'))
	define('MENU_TRUNCATE_STRING', getOption('menu_truncate_string'));
if (!defined('MENU_TRUNCATE_INDICATOR'))
	define('MENU_TRUNCATE_INDICATOR', getOption('menu_truncate_indicator'));

class CMS {

	public $categoryStructure = array();
	// article defaults (mirrors category vars)
	protected $sortorder = 'date';
	protected $sortdirection = true;
	protected $sortSticky = true;
	// page defaults
	protected $page_sortorder;
	protected $page_sortdirection;
	var $news_enabled = true;
	var $pages_enabled = true;

	/**
	 * Class instantiator
	 */
	function __construct() {
		$allcategories = query_full_array("SELECT * FROM " . prefix('news_categories') . " ORDER by sort_order");
		$this->categoryStructure = array();
		foreach ($allcategories as $cat) {
			$this->categoryStructure[$cat['id']] = $cat;
		}
		$this->news_enabled = getOption('zenpage_enabled_items') & 1;
		$this->pages_enabled = getOption('zenpage_enabled_items') & 2;
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
		foreach (array('news' => 'News', 'pages' => 'Page') as $table => $class) {
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
	 * @return array
	 */
	function getPages($published = NULL, $toplevel = false, $number = NULL, $sorttype = NULL, $sortdirection = NULL) {
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
		if ($sortdirection) {
			$sortdir = ' DESC';
		} else {
			$sortdir = ' ASC';
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
				$sortdir = '';
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
				if ($all || ($row['show'] && GALLERY_SECURITY == 'public')) {
					$all_pages[] = $row;
				} else if ($_zp_loggedin) {
					$page = newPage($row['titlelink']);
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
	 * @param bool $sortdirection TRUE for ascending, FALSE for descending. Note: This parameter is not used for date archives
	 * @param bool $sticky set to true to place "sticky" articles at the front of the list.
	 * @return array
	 */
	function getArticles($articles_per_page = 0, $published = NULL, $ignorepagination = false, $sortorder = NULL, $sortdirection = NULL, $sticky = NULL, $category = NULL) {
		global $_zp_current_category, $_zp_post_date, $_zp_newsCache;
		if (empty($published)) {
			if (zp_loggedin() || $category && $category->isMyItem(ZENPAGE_NEWS_RIGHTS)) {
				$published = "all";
			} else {
				$published = "published";
			}
		}
		if ($category) {
			$sortObj = $category;
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
		$newsCacheIndex = "$sortorder-$sortdirection-$published-" . (int) $sticky;
		if ($category) {
			$newsCacheIndex .= '-' . $category->getTitlelink();
		}
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
						$subcatobj = newCategory($subcat);
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
					$getUnpublished = true;
					$show = false;
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
					$article = newArticle($item['titlelink']);
					if ($getUnpublished || $article->isMyItem(ZENPAGE_NEWS_RIGHTS) || $currentcategory && ($article->inNewsCategory($currentcategory)) || $article->categoryIsVisible()) {
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
	 * Returns an article from the album based on the index passed.
	 *
	 * @param int $index
	 * @return int
	 */
	function getArticle($index, $published = NULL, $sortorder = NULL, $sortdirection = NULL, $sticky = true) {
		$articles = $this->getArticles(0, NULL, true, $sortorder, $sortdirection, $sticky);
		if ($index >= 0 && $index < count($articles)) {
			$article = $articles[$index];
			$obj = newArticle($articles[$index]['titlelink']);
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
		if (empty($_zp_current_category)) {
			if (isset($_GET['category'])) {
				$cat = sanitize($_GET['category']);
				$catobj = newCategory($cat);
			} else {
				return count($this->getArticles(0));
			}
		} else {
			$catobj = $_zp_current_category;
		}
		return count($catobj->getArticles());
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
					$article = newArticle($item['titlelink']);
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
				$catobj = newCategory($cat['titlelink']);
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
				$structure = sortMultiArray($structure, $sortorder, $sortdirection, true, false, false);
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
class CMSRoot extends ThemeObject {

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
class CMSItems extends CMSRoot {

	protected $subrights = NULL; //	cache for subrights

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
		if ($locale == 'all') {
			return zpFunctions::unTagURLs($text);
		} else {
			return applyMacros(zpFunctions::unTagURLs(get_language_string($text, $locale)));
		}
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

	function subRights() {
		global $_zp_current_admin_obj;
		if (!is_null($this->subrights)) {
			return $this->subrights;
		}
		if (zp_loggedin($this->manage_rights)) {
			$this->subrights = MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_VIEW;
			return $this->subrights;
		}

		$objects = $_zp_current_admin_obj->getObjects();
		$me = $this->getTitlelink();
		foreach ($objects as $object) {
			if ($object['type'] == $this->table) {
				if ($object['data'] == $me) {
					$this->subrights = $object['edit'] | MANAGED_OBJECT_MEMBER;
					return $this->subrights;
				}
			}
			$this->subrights = 0;
			return 0;
		}
	}

}

?>