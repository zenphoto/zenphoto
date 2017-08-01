<?php

function newCategory($catllink, $create = NULL) {
	return new Category($catllink, $create);
}

/**
 * zenpage news category class
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */
class Category extends CMSRoot {

	var $manage_rights = MANAGE_ALL_NEWS_RIGHTS;
	var $manage_some_rights = ZENPAGE_NEWS_RIGHTS;
	var $access_rights = ALL_NEWS_RIGHTS;
	protected $sortorder = 'date';
	protected $sortdirection = true;
	protected $sortSticky = true;
	protected $subrights = NULL; //	cache for subrights
	protected $index;

	function __construct($catlink, $create = NULL) {
		if (is_array($catlink)) {
			$catlink = $catlink['titlelink'];
		}
		$new = $this->instantiate('news_categories', array('titlelink' => $catlink), 'titlelink', true, empty($catlink), $create);
		$this->checkForPublish();
		if ($new || empty($catlink)) {
			$this->setShow(1);
		}
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
			return zpFunctions::unTagURLs($text);
		} else {
			return applyMacros(zpFunctions::unTagURLs(get_language_string($text, $locale)));
		}
	}

	/**
	 * Stores the description
	 *
	 * @param string $desc description text
	 */
	function setDesc($desc) {
		$desc = zpFunctions::tagURLs($desc);
		$this->set('desc', $desc);
	}

	/**
	 * Returns the content
	 *
	 * @return string
	 */
	function getContent($locale = NULL) {
		$content = $this->get("content");
		if ($locale == 'all') {
			return zpFunctions::unTagURLs($content);
		} else {
			return applyMacros(zpFunctions::unTagURLs(get_language_string($content, $locale)));
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
	 * Returns the sort order
	 *
	 * @return string
	 */
	function getSortOrder() {
		return $this->get('sort_order');
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
		$type = $this->sortorder;
		return $type;
	}

	function setSortType($value) {
		$this->sortorder = $value;
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
	 * Sets the encrypted password
	 *
	 * @param string $pwd the cleartext password
	 */
	function setPassword($pwd) {
		$this->set('password', $pwd);
	}

	function getPasswordHint() {
		return $this->get('password_hint');
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
	 * duplicates a category
	 * @param string $newtitle the title for the new category
	 */
	function copy($newtitle) {
		$newID = $newtitle;
		$id = parent::copy(array('titlelink' => $newID));
		if (!$id) {
			$newID = $newtitle . ':' . seoFriendly(date('Y-m-d_H-i-s'));
			$id = parent::copy(array('titlelink' => $newID));
		}
		if ($id) {
			$newobj = newCategory($newID);
			$newobj->setTitle($newtitle);
			$newobj->setSortOrder(NULL);
			$newobj->setDateTime(date('Y-m-d H:i:s'));
			$newobj->setShow(1);
			$newobj->save();
			return $newobj;
		}
		return false;
	}

	/**
	 * Deletes a category (and also if existing its subpages) from the database
	 *
	 */
	function remove() {
		if ($success = parent::remove()) {
			$sortorder = $this->getSortOrder();
			$success = query("DELETE FROM " . prefix('news2cat') . " WHERE cat_id = " . $this->getID()); // the cat itself
			// get Subcategories
			$mychild = strlen($sortorder) + 4;
			$result = query_full_array('SELECT * FROM ' . prefix('news_categories') . " WHERE `sort_order` like '" . $sortorder . "-%'");
			if (is_array($result)) {
				foreach ($result as $row) {
					if (strlen($row['sort_order']) == $mychild) {
						$subcat = newCategory($row['titlelink']);
						$success = $success && $subcat->remove();
					}
				}
			}
		}
		return $success;
	}

	/**
	 * Gets the sub categories recursivly by titlelink
	 * @param bool $visible TRUE for published and unprotected
	 * @param string $sorttype NULL for the standard order as sorted on the backend, "title", "date", "popular"
	 * @param string $sortdirection "asc" or "desc" for ascending or descending order
	 * @return array
	 */
	function getSubCategories($visible = true, $sorttype = NULL, $sortdirection = NULL) {
		global $_zp_CMS;
		$subcategories = self::subCategoryRecurse($this, $_zp_CMS->getAllCategories($visible, $sorttype, $sortdirection));
		if (!empty($subcategories)) {
			return $subcategories;
		}
		return FALSE;
	}

	/**
	 * Recursively gets sub categories of a category
	 * @param object $category the parent category
	 * @param array $all possible sub category list
	 * @return array
	 */
	protected function subCategoryRecurse($category, $all) {
		$subcategories = array();
		if ($category->exists) {
			$sortorder = $this->getSortOrder();
			foreach ($all as $cat) {
				$catobj = newCategory($cat['titlelink']);
				if ($catobj->getParentID() == $category->getID() && $catobj->getSortOrder() != $sortorder) { // exclude the category itself!
					$subcategories = array_merge($subcategories, self::subCategoryRecurse($catobj, $all));
					array_push($subcategories, $catobj->getTitlelink());
				}
			}
		}
		return $subcategories;
	}

	/**
	 * Checks if the current news category is a sub category of $catlink
	 *
	 * @return bool
	 */
	function isSubNewsCategoryOf($catlink) {
		if (!empty($catlink)) {
			$parentid = $this->getParentID();
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
	 * Gets the parent categories recursivly to the category whose parentid is passed or the current object
	 *
	 * @param int $parentid The parentid of the category to get the parents of
	 * @param bool $initparents
	 * @return array
	 */
	function getParents(&$parentid = '', $initparents = true) {
		global $parentcats, $_zp_CMS;
		$allitems = $_zp_CMS->getAllCategories(false);
		if ($initparents) {
			$parentcats = array();
		}
		if (empty($parentid)) {
			$currentparentid = $this->getParentID();
		} else {
			$currentparentid = $parentid;
		}
		foreach ($allitems as $item) {
			$obj = newCategory($item['titlelink']);
			$itemtitlelink = $obj->getTitlelink();
			$itemid = $obj->getID();
			$itemparentid = $obj->getParentID();
			if ($itemid == $currentparentid) {
				array_unshift($parentcats, $itemtitlelink);
				$obj->getParents($itemparentid, false);
			}
		}
		return $parentcats;
	}

	/**
	 * Checks if user is allowed to access news category
	 * @param $hint
	 * @param $show
	 */
	function checkforGuest(&$hint = NULL, &$show = NULL) {
		if (!parent::checkForGuest()) {
			return false;
		}
		$id = $this->getID();
		$obj = $this;
		$hash = $this->getPassword();
		while (empty($hash) && !is_null($obj)) {
			$parentID = $obj->getParentID();
			if (empty($parentID)) {
				$obj = NULL;
			} else {
				$obj = getItemByID('news_categories', $parentID);
				$hash = $obj->getPassword();
				$id = $obj->getID();
			}
		}

		if (empty($hash)) { // no password required
			return 'zp_public_access';
		} else {
			$authType = "zp_category_auth_" . $id;
			$saved_auth = zp_getCookie($authType);
			if ($saved_auth == $hash) {
				return $authType;
			} else {
				$user = $this->getUser();
				if (!empty($user))
					$show = true;
				$hint = $this->getPasswordHint();
				return false;
			}
		}
	}

	/**
	 * Checks if a category is protected and returns TRUE or FALSE
	 * NOTE: This function does only check if a password is set not if it has been entered! Use $this->checkforGuest() for that.
	 *
	 * @return bool
	 */
	function isProtected() {
		return GALLERY_SECURITY != 'public' || $this->checkforGuest() != 'zp_public_access';
	}

	function subRights() {
		global $_zp_current_admin_obj;
		if (!is_null($this->subrights)) {
			return $this->subrights;
		}
		if (zp_loggedin()) {
			if (zp_loggedin($this->manage_rights)) {
				$this->subrights = MANAGED_OBJECT_RIGHTS_EDIT | MANAGED_OBJECT_RIGHTS_VIEW;
				return $this->subrights;
			}

			$objects = $_zp_current_admin_obj->getObjects();
			$me = $this->getTitlelink();
			foreach ($objects as $object) {
				if ($object['type'] == 'news') {
					if ($object['data'] == $me) {
						$this->subrights = $object['edit'] | MANAGED_OBJECT_MEMBER;
						return $this->subrights;
					}
				}
			}
		}
		$this->subrights = 0;
		return 0;
	}

	function isMyItem($action) {
		global $_zp_current_admin_obj;
		if (parent::isMyItem($action)) {
			return true;
		}
		if (zp_loggedin($action)) {
			if ($this->getShow() && $action == LIST_RIGHTS) {
				return true;
			}

			$subRights = $this->subRights();
			if ($subRights) {
				$rights = LIST_RIGHTS;
				if ($subRights & MANAGED_OBJECT_RIGHTS_EDIT) {
					$rights = $rights | ZENPAGE_NEWS_RIGHTS;
				}
				if ($action & $rights) {
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
	 * @return array
	 */
	function getArticles($articles_per_page = 0, $published = NULL, $ignorepagination = false, $sortorder = NULL, $sortdirection = NULL, $sticky = NULL) {
		global $_zp_CMS;
		return $_zp_CMS->getArticles($articles_per_page, $published, $ignorepagination, $sortorder, $sortdirection, $sticky, $this);
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
		global $_zp_CMS;
		if ($this->index == NULL) {
			$articles = $_zp_CMS->getArticles(0, NULL, true, $sortorder, $sortdirection, $sticky);
			for ($i = 0; $i < count($articles); $i++) {
				$article = $articles[$i];
				if ($this->getTitlelink() == $article['titlelink']) {
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
		$index = $this->getIndex($sortorder, $sortdirection, $sticky);
		$article = $this->getArticle($index + 1);
		return $article;
	}

	/**
	 * Returns the full path to a news category
	 *
	 * @param string $page The category page number
	 *
	 * @return string
	 */
	function getLink($page = NULL) {
		global $_zp_CMS;
		if ($page > 1) {
			$pager = $page;
			$page = '&page=' . $page;
		} else {
			$pager = $page = '';
		}
		return zp_apply_filter('getLink', rewrite_path(_CATEGORY_ . '/' . $this->getTitlelink() . '/' . $pager, "/index.php?p=news&category=" . $this->getTitlelink() . $page), $this, NULL);
	}

}

// zenpage news category class end
?>