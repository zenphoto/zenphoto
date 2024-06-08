<?php

/**
 * zenpage news category class
 *
 * @author Malte Müller (acrylian)
 * @package zpcore\plugins\zenpage\classes
 */
class ZenpageCategory extends ZenpageRoot {

	public $manage_rights = MANAGE_ALL_NEWS_RIGHTS;
	public $manage_some_rights = ZENPAGE_NEWS_RIGHTS;
	public $view_rights = ALL_NEWS_RIGHTS;
	public $parent = null;
	public $parents = null;
	public $urparent = null;
	protected $sorttype = 'date';
	protected $sortdirection = true;
	protected $sortSticky = true;
	

	function __construct($catlink, $create = NULL) {
		if (is_array($catlink)) {
			$catlink = $catlink['titlelink'];
		}
		$new = $this->instantiate('news_categories', array('titlelink' => $catlink), 'titlelink', true, empty($catlink), $create);
		$this->exists = $this->loaded;
	}

	/**
	 * Returns the description
	 *
	 * @return string
	 */
	function getDesc($locale = NULL) {
		$text = $this->get('desc');
		if ($locale == 'all') {
			return unTagURLs($text);
		} else {
			return applyMacros(unTagURLs(get_language_string($text, $locale)));
		}
	}

	/**
	 * Stores the description
	 *
	 * @param string $desc description text
	 */
	function setDesc($desc) {
		$desc = tagURLs($desc);
		$this->set('desc', $desc);
	}

	/**
	 * Returns the sort order
	 *
	 * @return string
	 */
	function getSortOrder() {
		return $this->get('sort_order');
	}
	
	/**
	 * Sets a default sortorder for the category.
	 * 
	 * Use this before save()
	 * 
	 * a) If you created an new item after you set a parentid and no new specific sortorder
	 * b) You updated the parentid without setting specific new sortorder
	 * 
	 * The sortorder takes care of existing categories on the level and adds the item after existing ones.
	 * 
	 * @since 1.5.8
	 */
	function setDefaultSortorder() {
		$default = $this->getDefaultSortorder();
		$this->setSortorder($default);
	}
	
	/**
	 * Gets the default sortorder if a category
	 * 
	 * Use this before save()
	 * 
	 * a) If you created an new item after you set a parentid and no new specific sortorder
	 * b) You updated the parentid without setting specific new sortorder
	 * 
	 * The sortorder takes care of existing categories on the level and adds the item after existing ones.
	 * 
	 * @since 1.5.8
	 * 
	 * @global obj $_zp_zenpage  
	 * @return string
	 */
	function getDefaultSortorder() {
		global $_zp_zenpage;
		return $_zp_zenpage->getItemDefaultSortorder('category', $this->getParentID());
	}

	/**
	 * Stores the sort order
	 *
	 * @param string $sortorder image sort order
	 */
	function setSortOrder($sortorder) {
		$this->set('sort_order', $sortorder);
	}

	function getSortDirection() {
		return $this->sortdirection;
	}

	function setSortDirection($value) {
		$this->sortdirection = (int) ($value && true);
	}

	function getSortType() {
		return $this->sorttype;
	}

	function setSortType($value) {
		$this->sorttype = $value;
	}

	function getSortSticky() {
		return $this->sortSticky;
	}

	function setSortSticky($value) {
		$this->sortSticky = (bool) $value;
	}

	function getUser() {
		return $this->get('user');
	}

	/**
	 * Sets the guest user
	 *
	 * @param string $user
	 */
	function setUser($user) {
		$this->set('user', $user);
	}

	function getPassword() {
		if (GALLERY_SECURITY != 'public') {
			return NULL;
		} else {
			return $this->get('password');
		}
	}
	
		/**
	 * Returns true if not protected but protection is inherited by a parent
	 * 
	 * @since 1.6.1
	 * 
	 * @return bool
	 */
	function isProtectedByParent() {
		if ($this->isProtected() && !$this->getPassword()) {
			return true;
		}
		return false;
	}

	/**
	 * Sets the encrypted password
	 *
	 * @param string $pwd the cleartext password
	 */
	function setPassword($pwd) {
		$this->set('password', $pwd);
	}

	function getPasswordHint($locale = NULL) {
		$text = $this->get('password_hint');
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = unTagURLs($text);
		return $text;
	}

	/**
	 * Sets the password hint
	 *
	 * @param string $hint the hint text
	 */
	function setPasswordHint($hint) {
		$this->set('password_hint', $hint);
	}

	/**
	 * Deletes a category (and also if existing its subpages) from the database
	 *
	 */
	function remove() {
		global $_zp_db;
		if ($success = parent::remove()) {
			$sortorder = $this->getSortOrder();
			$success = $_zp_db->query("DELETE FROM " . $_zp_db->prefix('news2cat') . " WHERE cat_id = " . $this->getID()); // the cat itself
			// get Subcategories
			$mychild = strlen($sortorder) + 4;
			$result = $_zp_db->queryFullArray('SELECT * FROM ' . $_zp_db->prefix('news_categories') . " WHERE `sort_order` like '" . $sortorder . "-%'");
			if (is_array($result)) {
				foreach ($result as $row) {
					if (strlen($row['sort_order']) == $mychild) {
						$subcat = new ZenpageCategory($row['titlelink']);
						$success = $success && $subcat->remove();
					}
				}
			}
		}
		return $success;
	}
	
	/**
	 * Gets the sub categories recursivly by titlelink
	 * 
	 * @since 1.5.8 - deprecates getSubCategories()
	 * 
	 * @param bool $visible TRUE for published and unprotected
	 * @param string $sorttype NULL for the standard order as sorted on the backend, "title", "date", "popular"
	 * @param bool $sortdirection True for descending (default), false for ascending direction
	 * @param bool $directchilds Default true to get only the direct sub level pages, set to false to get all levels
	 * @return array
	 */
	function getCategories($visible = true, $sorttype = NULL, $sortdirection = NULL, $directchilds = true) {
		global $_zp_zenpage;
		$categories = array();
		foreach ($_zp_zenpage->getAllCategories($visible, $sorttype, $sortdirection, false) as $cat) {
			if ($cat['sort_order'] != $this->getSortOrder() && (!$directchilds && stripos(strval($cat['sort_order']), strval($this->getSortOrder())) === 0) || $cat['parentid'] == $this->getID()) {
				array_push($categories, $cat);
			}
		}
		return $categories;
	}

	/**
	 * @see getCategories()
	 * @deprecated 2.0 - Use getCategories() instead
	 */
	function getSubCategories($visible = true, $sorttype = NULL, $sortdirection = NULL, $directchilds = false) {
		return $this->getCategories($visible, $sorttype, $sortdirection, $directchilds);
	}
	
	/**
	 * Checks if the current news category is a sub category of $catlink
	 *
	 * @since 1.5.8 - deprecates isSubNewsCategoryOf()
	 * 
	 * @return bool
	 */
	function isSubCategoryOf($catlink) {
		if (!empty($catlink)) {
			$categories = $this->getParents();
			$count = 0;
			foreach ($categories as $cat) {
				if ($catlink == $cat) {
					$count = 1;
					break;
				}
			}
			return $count == 1;
		} else {
			return false;
		}
	}

	/**
	 * @see isSubCategoryOf()
	 * @deprecated 2.0 - Use getCategories() instead
	 */
	function isSubNewsCategoryOf($catlink) {
		return $this->isSubCategoryOf($catlink);
	}

	/**
	 * Gets the parent category object based on the parentid set
	 * 
	 * @since 1.5.5
	 * 
	 * @return obj|null
	 */
	function getParent() {
		if ($this->getParentID()) {
			if (is_null($this->parent)) {
				$obj = getItembyID('news_categories', $this->getParentID());
				if ($obj) {
					return $this->parent = $obj;
				}
			} else {
				return $this->parent;
			}
		}
		return null;
	}

	/**
	 * Gets the parent categories' titlelinks recursivly to the category
	 * 
	 * @return array|null
	 */
	function getParents() {
		if (func_num_args() != 0) {
			deprecationNotice(gettext('class ZenpageCategory getParents(): The parameters $parentid and $initparents have been removed in Zenphoto 1.5.5.'), true);
		}
		if ($this->getParentID()) {
			if (is_null($this->parents)) {
				$parents = array();
				$cat = $this;
				while (!is_null($cat = $cat->getParent())) {
					array_unshift($parents, $cat->getName());
				}
				return $this->parents = $parents;
			} else {
				return $this->parents;
			}
		}
		return $this->parents = array();
	}

	/**
	 * Checks if user is allowed to access news category
	 * @param $hint
	 * @param $show
	 */
	function checkforGuest(&$hint = NULL, &$show = NULL) {
		global $_zp_db;
		if (!parent::checkForGuest()) {
			return false;
		}
		$obj = $this;
		$hash = $obj->getPassword();
		while (empty($hash) && !is_null($obj)) {
			$parentID = $obj->getParentID();
			if (empty($parentID)) {
				$obj = NULL;
			} else {
				$sql = 'SELECT `titlelink` FROM ' . $_zp_db->prefix('news_categories') . ' WHERE `id`=' . $parentID;
				$result = $_zp_db->querySingleRow($sql);
				$obj = new ZenpageCategory($result['titlelink']);
				$hash = $obj->getPassword();
			}
		}
		if (empty($hash)) { // no password required
			return 'zp_public_access';
		} else {
			$authType = "zpcms_auth_category_" . $this->getID();
			$saved_auth = zp_getCookie($authType);
			if ($saved_auth == $hash) {
				return $authType;
			} else {
				$user = $this->getUser();
				$show = (!empty($user));
				$hint = $this->getPasswordHint();
				return false;
			}
		}
	}
	
	/**
	 * Returns true if this category is published and also all of its parents.
	 * 
	 * @since 1.5.5
	 * 
	 * @return bool
	 */
	function isPublic() {
		if (is_null($this->is_public)) {
			if (!$this->isPublished()) {
				return $this->is_public = false;
			}
			$parent = $this->getParent();
			if($parent && !$parent->isPublic()) {
				return $this->is_public = false;
			}
			return $this->is_public = true;
		} else {
			return $this->is_public;
		}
	}

	
	/**
	 * Checks if user is news author
	 * @param bit $action User rights level, default LIST_RIGHTS
	 *
	 * returns true of access is allowed
	 */
	function isMyItem($action = LIST_RIGHTS) {
		global $_zp_current_admin_obj;
		if (parent::isMyItem($action)) {
			return true;
		}
		if (zp_loggedin($action)) {
			if (GALLERY_SECURITY != 'public' && $this->isPublic() && $action == LIST_RIGHTS) {
				return true;
			}
			$mycategories = $_zp_current_admin_obj->getObjects('news');
			if (!empty($mycategories)) {
				$allowed = $this->getParents();
				array_unshift($allowed, $this->getName());
				$overlap = array_intersect($mycategories, $allowed);
				if (!empty($overlap)) {
					return true;
				}
			}
		}
		return false;
	}

		/**
	 * Gets news articles titlelinks this category is attached to
	 *
	 * NOTE: Since this function only returns titlelinks for use with the object model it does not exclude articles that are password protected via a category
	 *
	 *
	 * @param int $articles_per_page The number of articles to get
	 * @param string $published "published" for published articles,
	 * 													"published-unpublished" for published articles only from an unpublished category,
	 * 													"unpublished" for unpublished articles,
	 * 													"sticky" for sticky articles (published or not!) for Admin page use only,
	 * 													"all" for all articles
	 * @param boolean $ignorepagination Since also used for the news loop this function automatically paginates the results if the "page" GET variable is set. To avoid this behaviour if using it directly to get articles set this TRUE (default FALSE)
	 * @param string $sortorder "date" (default), "title", "popular", "mostrated", "toprated", "random"
	 * 													This parameter is not used for date archives
	 * @param string $sortdirection "asc" or "desc" for ascending or descending order
	 * 											        This parameter is not used for date archives
	 * @param bool $sticky set to true to place "sticky" articles at the front of the list.
	 * @param string $author Optional author name to get the article of
	* @param string|null|false $date Date YYYY-mm format for a date archive, null uses global theme date archive context, false (default) to disable date archive context
	 * @return array
	 */
	function getArticles($articles_per_page = 0, $published = NULL, $ignorepagination = false, $sortorder = NULL, $sortdirection = NULL, $sticky = NULL, $author = null, $date = false) {
		global $_zp_zenpage;
		return $_zp_zenpage->getArticles($articles_per_page, $published, $ignorepagination, $sortorder, $sortdirection, $sticky, $this, $author, $date);
	}
	
	/**
	 * Returns the articles count
	 * 
	 * @since 1.6
	 */
	function getTotalArticles() {
		return count($this->getArticles(0));
	}
	
	/**
	 * Gets the total news pages
	 * 
	 * @since 1.6
	 */
	function getTotalNewsPages() {
		return ceil($this->getTotalArticles() / ZP_ARTICLES_PER_PAGE);
	}

	/**
	 * Returns an article from the album based on the index passed.
	 *
	 * @param int $index
	 * @return int
	 */
	function getArticle($index, $published = NULL, $sortorder = 'date', $sortdirection = 'desc', $sticky = true) {
		$articles = $this->getArticles(0, $published, true, $sortorder, $sortdirection, $sticky); // pagination not needed
		if ($index >= 0 && $index < count($articles)) {
			return $articles[$index];
		}
		return false;
	}

	/**
	 * Get the index of this article
	 *
	 * @return int
	 */
	function getIndex($sortorder, $sortdirection, $sticky) {
		if ($this->index == NULL) {
			$articles = $this->getArticles(0, NULL, true, $sortorder, $sortdirection, $sticky);
			for ($i = 0; $i < count($articles); $i++) {
				$article = $articles[$i];
				if ($this->getName() == $article['titlelink']) {
					$this->index = $i;
					break;
				}
			}
		}
		return $this->index;
	}

	/**
	 * Return the previous article
	 *
	 * @return object
	 */
	function getPrevArticle($sortorder = 'date', $sortdirection = 'desc', $sticky = true) {
		global $_zp_zenpage, $_zp_current_zenpage_news;
		$index = $this->getIndex($sortorder, $sortdirection, $sticky);
		$article = $this->getArticle($index - 1);
		return $article;
	}

	/**
	 * Returns the next article.
	 *
	 * @return object
	 */
	function getNextArticle($sortorder = 'date', $sortdirection = 'desc', $sticky = true) {
		global $_zp_zenpage, $_zp_current_zenpage_news;
		$index = $this->getIndex($sortorder, $sortdirection, $sticky);
		$article = $this->getArticle($index + 1);
		return $article;
	}

	/**
	 * Returns the full path to a news category
	 * 
	 * @param string $page The category page number
	 * @param string $path Default null, optionally pass a path constant like WEBPATH or FULLWEBPATH
	 * @return string
	 */
	function getLink($page = NULL, $path = null) {
		if ($page > 1) {
			$pager = $page . '/';
			$page = '&page=' . $page;
		} else {
			$pager = $page = '';
		}
		return zp_apply_filter('getLink', rewrite_path(_CATEGORY_ . '/' . $this->getName() . '/' . $pager, '/index.php?p=news&category=' . $this->getName() . $page, $path), $this, NULL);
	}

}
