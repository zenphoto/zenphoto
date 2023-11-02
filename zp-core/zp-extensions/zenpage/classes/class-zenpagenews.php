<?php

if (!defined('NEWS_POSITION_NORMAL')) { // No idea why this is needed, but clones get already defined errors.
	define('NEWS_POSITION_NORMAL', 0);
	define('NEWS_POSITION_STICKY', 1);
	define('NEWS_POSITION_STICK_TO_TOP', 9);
}

/**
 * zenpage news class
 *
 * @author Malte Müller (acrylian)
 * @package zpcore\plugins\zenpage\classes
 */
class ZenpageNews extends ZenpageItems {

	public $manage_rights = MANAGE_ALL_NEWS_RIGHTS;
	public $manage_some_rights = ZENPAGE_NEWS_RIGHTS;
	public $view_rights = ALL_NEWS_RIGHTS;
	public $categories = NULL;
	public $index = NULL;

	function __construct($titlelink, $allowCreate = NULL) {
		if (is_array($titlelink)) {
			$titlelink = $titlelink['titlelink'];
		}
		$new = $this->instantiate('news', array('titlelink' => $titlelink), 'titlelink', true, empty($titlelink), $allowCreate);
		$this->exists = $this->loaded;
	}

	/**
	 * Gets the categories assigned to an news article
	 *
	 * @param int $article_id ID od the article
	 * @return array
	 */
	function getCategories() {
		global $_zp_db;
		if (is_null($this->categories)) {
			$this->categories = $_zp_db->queryFullArray("SELECT * FROM " . $_zp_db->prefix('news_categories') . " as cat," . $_zp_db->prefix('news2cat') . " as newscat WHERE newscat.cat_id = cat.id AND newscat.news_id = " . $this->getID() . " ORDER BY cat.titlelink", false, 'title');
			if (!$this->categories) {
				$this->categories = array();
			}
		}
		return $this->categories;
	}

	function setCategories($categories) {
		global $_zp_db;
		$result = $_zp_db->query('DELETE FROM ' . $_zp_db->prefix('news2cat') . ' WHERE `news_id`=' . $this->getID());
		$result = $_zp_db->queryFullArray("SELECT * FROM " . $_zp_db->prefix('news_categories') . " ORDER BY titlelink");
		foreach ($result as $cat) {
			if (in_array($cat['titlelink'], $categories)) {
				$_zp_db->query("INSERT INTO " . $_zp_db->prefix('news2cat') . " (cat_id, news_id) VALUES ('" . $cat['id'] . "', '" . $this->getID() . "')");
			}
		}
	}

	/**
	 * Returns true if the article is sticky
	 *
	 * @return bool
	 */
	function getSticky() {
		return $this->get('sticky');
	}

	function setSticky($v) {
		$this->set('sticky', $v);
	}

	function getTruncation() {
		return $this->get('truncation');
	}

	function setTruncation($v) {
		$this->set('truncation', $v);
	}

	/**
	 * duplicates an article
	 * @param string $newtitle the title for the new article
	 */
	function copy($newtitle) {
		global $_zp_db;
		$newID = $newtitle;
		$id = parent::copy(array('titlelink' => $newID));
		if (!$id) {
			$newID = $newtitle . ':' . seoFriendly(date('Y-m-d_H-i-s'));
			$id = parent::copy(array('titlelink' => $newID));
		}
		if ($id) {
			$newobj = new ZenpageNews($newID);
			$newobj->setTitle($newtitle);
			$newobj->setTags($this->getTags());
			$newobj->setPublished(0);
			$newobj->setDateTime(date('Y-m-d H:i:s'));
			$newobj->save();
			$categories = array();
			foreach ($this->getCategories() as $cat) {
				$categories[] = $cat['cat_id'];
			}
			$result = $_zp_db->queryFullArray("SELECT * FROM " . $_zp_db->prefix('news_categories') . " ORDER BY titlelink");
			foreach ($result as $cat) {
				if (in_array($cat['id'], $categories)) {
					$_zp_db->query("INSERT INTO " . $_zp_db->prefix('news2cat') . " (cat_id, news_id) VALUES ('" . $cat['id'] . "', '" . $id . "')");
				}
			}
			return $newobj;
		}
		return false;
	}

	/**
	 * Deletes an news article from the database
	 *
	 */
	function remove() {
		global $_zp_db;
		if ($success = parent::remove()) {
			if ($this->id) {
				$success = $_zp_db->query("DELETE FROM " . $_zp_db->prefix('obj_to_tag') . "WHERE `type`='news' AND `objectid`=" . $this->getID());
				$success = $success && $_zp_db->query("DELETE FROM " . $_zp_db->prefix('news2cat') . " WHERE news_id = " . $this->getID()); // delete the category association
				$success = $success && $_zp_db->query("DELETE FROM " . $_zp_db->prefix('comments') . " WHERE ownerid = " . $this->getID() . ' AND type="news"'); // delete any comments
			}
		}
		return $success;
	}

	/**
	 * Checks if an article is in a password protected category and returns TRUE or FALSE
	 * 	
	 * @param bool $only set to true to know if the news article belongs only to protected categories
	 *
	 * @return boolean
	 */
	function inProtectedCategory($only = false) {
		$categories = $this->getCategories(false, null, null, false);
		$protected_cats = '';
		if (!empty($categories)) {
			$catcount = count($categories);
			foreach ($categories as $cat) {
				$catobj = new ZenpageCategory($cat['titlelink']);
				if ($catobj->isProtected()) {
					if ($only) {
						$protected_cats++;
					} else {
						return true;
					}
				}
			}
			return ($catcount == $protected_cats);
		}
		return false;
	}

	/**
	 * Returns true if the article is protected by a category
	 * @since 1.6.1 Returns true if the article is in any protected category instead if protected categories only
	 * @return bool
	 */
	function isProtected() {
		if (is_null($this->is_protected)) {
			return $this->is_protected = $this->inProtectedCategory();
		}
		return $this->is_protected;
	}

	/**
	 * Returns true if not protected but protection is inherited by a parent 
	 * Note: Here the same as isProtected() to align with other objects
	 * 
	 * @since 1.6.1 
	 * 
	 * @return bool
	 */
	function isProtectedByParent() {
		return $this->isProtected();
	}
	
	/**
	 * Returns true if this article is published and in any published category
	 * 
	 * @since 1.5.5
	 * 
	 * @return bool
	 */
	function isPublic() {
		if (is_null($this->is_public)) {
			if(!$this->isPublished()) {
				return $this->is_public = false;
			}
			return $this->is_public = true;
		} else {
			return $this->is_public;
		}
	}

	/**
	 *
	 * returns true if the article exists in any published category (or in no categories)
	 * 
	 * @deprecated 2.0 Use if($obj->isPublic() || zp_loggedin(ALL_NEWS_RIGHTS)) { … } for a equivalent check instead.
	 */
	function categoryIsVisible() {
		if (zp_loggedin(ALL_NEWS_RIGHTS))
			return true;
		return $this->isPublic();
	}
	
	
	/**
	 * See if a guest is logged on to the news category.
	 * Note: If any belonging category is plublic or he is logged on, then success.
	 * @param $hint
	 * @param $show
	 */
	function checkforGuest(&$hint = NULL, &$show = NULL) {
		if (!parent::checkForGuest()) {
			return false;
		}
		$categories = $this->getCategories();
		if (empty($categories)) { //	cannot be protected!
			return 'zp_public_access';
		} else {
			$access = array();
			foreach ($categories as $cat) {
				$catobj = new ZenpageCategory($cat['titlelink']);
				$guestaccess = $catobj->checkforGuest($hint, $show);
				if($guestaccess) {
					$access[] = 1;
				} else {
					$access[] = 0;
				}
			}
			if(in_array(0, $access)) { // if there is only one protected category, no public access
				return false;
			} else {
				return 'zp_public_access';
			}
		}
		return false;
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
			if (GALLERY_SECURITY != 'public' && $this->isPublic() && $action == LIST_RIGHTS) { //$this->getShow() && $action == LIST_RIGHTS) {
				return LIST_RIGHTS;
			}
			if ($_zp_current_admin_obj->getUser() == $this->getAuthor()) {
				return true; //	he is the author
			}
			if ($action == LIST_RIGHTS && !$this->isProtected() && $this->isPublic() || zp_loggedin(ALL_NEWS_RIGHTS)) {   //$this->categoryIsVisible()
				return true;
			}
			// A user actually cannot have rights for an article without categories assigned
			if(!$this->getCategories()) {
				return false;
			}
			$mycategories = $_zp_current_admin_obj->getObjects('news');
			if (!empty($mycategories)) {
				foreach ($this->getCategories() as $category) {
					$cat = new ZenpageCategory($category['titlelink']);
					// only override item visibility if we "own" the category and this article is in an owned category
					if ($cat->isMyItem(ZENPAGE_NEWS_RIGHTS)) { 
						return true;
					} 
				}
			}
			//echo "no category so for sure not my item!";			
		}
		return false;
	}

	/**
	 * Checks if an article is in a category and returns TRUE or FALSE
	 *
	 * @param string $catlink The titlelink of a category
	 * @return bool
	 */
	function inNewsCategory($catlink) {
		if (!empty($catlink)) {
			$categories = $this->getCategories();
			$count = 0;
			foreach ($categories as $cat) {
				if ($catlink == $cat['titlelink']) {
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
	 * Checks if an article is in a sub category of $catlink
	 *
	 * @param string $catlink The titlelink of a category
	 * @return bool
	 */
	function inSubNewsCategoryOf($catlink) {
		if (!empty($catlink)) {
			$categories = $this->getCategories();
			$count = 0;
			foreach ($categories as $cat) {
				$catobj = new ZenpageCategory($cat['titlelink']);
				$parentid = $catobj->getParentID();
				$parentcats = $catobj->getParents();
				foreach ($parentcats as $parentcat) {
					if ($catlink == $parentcat) {
						$count = 1;
						break;
					}
				}
			}
			return $count == 1;
		} else {
			return false;
		}
	}

	/**
	 * Returns the url to a news article
	 *
	 * @param string $path Default null, optionally pass a path constant like WEBPATH or FULLWEBPATH
	 * @return string
	 */
	function getLink($path = null) {
		return zp_apply_filter('getLink', rewrite_path(_NEWS_ . '/' . $this->getName() . '/', '/index.php?p=news&title=' . $this->getName(), $path), $this, NULL);
	}

	/**
	 * Get the index of this article
	 *
	 * @return int
	 */
	function getIndex() {
		global $_zp_zenpage, $_zp_current_zenpage_news;
		if ($this->index == NULL) {
			$articles = $_zp_zenpage->getArticles(0, NULL, true);
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
	function getPrevArticle() {
		global $_zp_zenpage, $_zp_current_zenpage_news;
		$index = $this->getIndex();
		$article = $_zp_zenpage->getArticle($index - 1);
		return $article;
	}

	/**
	 * Returns the next article.
	 *
	 * @return object
	 */
	function getNextArticle() {
		global $_zp_zenpage, $_zp_current_zenpage_news;
		$index = $this->getIndex();
		$article = $_zp_zenpage->getArticle($index + 1);
		return $article;
	}

	/**
	 * Returns the page number in the news loop
	 *
	 * @return int
	 */
	function getNewsLoopPage() {
		$index = $this->getIndex();
		return floor(($index / ZP_ARTICLES_PER_PAGE) + 1);
	}

}