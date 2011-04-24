<?php
/**
 * zenpage news class
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */

class ZenpageNews extends ZenpageItems {

	var $manage_rights = MANAGE_ALL_NEWS_RIGHTS;
	var $manage_some_rights = ZENPAGE_NEWS_RIGHTS;
	var $view_rights = VIEW_NEWS_RIGHTS;

	function ZenpageNews($titlelink) {
		$new = parent::PersistentObject('news', array('titlelink'=>$titlelink), NULL, true, empty($titlelink));
	}

	/**
	 * Gets the categories assigned to an news article
	 *
	 * @param int $article_id ID od the article
	 * @return array
	 */
	function getCategories() {
		$categories = query_full_array("SELECT * FROM ".prefix('news_categories')." as cat,".prefix('news2cat')." as newscat WHERE newscat.cat_id = cat.id AND newscat.news_id = ".$this->getID()." ORDER BY cat.titlelink",false,'title');
		return $categories;
	}
	function setCategories($categories) {
		$result = query_full_array("SELECT * FROM ".prefix('news_categories')." ORDER BY titlelink");
		foreach ($result as $cat) {
			if (in_array($cat['titlelink'],$categories)) {
				query("INSERT INTO ".prefix('news2cat')." (cat_id, news_id) VALUES ('".$cat['id']."', '".$this->getID()."')");
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
		$this->set('sticky',$v);
	}

	/**
	 * duplicates an article
	 * @param string $newtitle the title for the new article
	 */
	function copy($newtitle) {
		$newID = $newtitle;
		$id = parent::copy(array('titlelink'=>$newID));
		if (!$id) {
			$newID = $newtitle.':'.seoFriendly(date('Y-m-d_H-i-s'));
			$id = parent::copy(array('titlelink'=>$newID));
		}
		if ($id) {
			$newobj = new ZenpageNews($newID);
			$newobj->setTitle($newtitle);
			$newobj->setTags($this->getTags());
			$newobj->save();
			$categories = array();
			foreach ($this->getCategories() as $cat) {
				$categories[] = $cat['cat_id'];
			}
			$result = query_full_array("SELECT * FROM ".prefix('news_categories')." ORDER BY titlelink");
			foreach ($result as $cat) {
				if (in_array($cat['id'],$categories)) {
					query("INSERT INTO ".prefix('news2cat')." (cat_id, news_id) VALUES ('".$cat['id']."', '".$id."')");
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
		if ($success = parent::remove()) {
			if ($this->id) {
				$success = query("DELETE FROM " . prefix('obj_to_tag') . "WHERE `type`='news' AND `objectid`=" . $this->getID());
				$success = $success && query("DELETE FROM ".prefix('news2cat')." WHERE news_id = ".$this->getID()); // delete the category association
				$success = $success && query("DELETE FROM ".prefix('comments')." WHERE ownerid = ".$this->getID().' AND type="news"'); // delete any comments
			}
		}
		return $success;
	}

/**
 * Checks if an article (not CombiNews gallery items!) is in a password protected category and returns TRUE or FALSE
 * NOTE: This function does not check if the password has been entered! Use checkAccess() for that.
 *
 * @return array
 */
	function inProtectedCategory() {
		$categories = $this->getCategories();
		if(count($categories) > 0) {
			foreach($categories as $cat) {
				$cat = new ZenpageCategory($cat['titlelink']);
				$password = $cat->getPassword();
				if(!empty($password)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Each object should have this method, but for news, if you get past the category it is public
	 * @param $hint
	 * @param $show
	 */
	function checkforGuest($hint,$show) {
		return 'zp_public_access';	//	news articles are not password protected, only their categories
	}

	/**
	 * Checks if user is news author
	 * @param bit $action what the caller wants to do
	 *
	 * returns true of access is allowed
	 */
	function isMyItem($action) {
		global $_zp_current_admin_obj;
		if (parent::isMyItem($action)) {
			return true;
		}
		if (zp_apply_filter('check_credentials', false, $this, $action)) return true;
		if (zp_loggedin($action)) {
			$mycategories = $_zp_current_admin_obj->getObjects('news');
			if (!empty($mycategories)) {
				foreach ($this->getCategories() as $category) {
					if (array_search($category['titlelink'],$mycategories)!==false) return true;
				}
			}
			return $_zp_current_admin_obj->getUser() == $this->getAuthor();
		}
		return false;
	}

	/**
	 * Checks if user is allowed access to the news article
	 * @param $hint
	 * @param $show
	 */
	function checkAccess(&$hint=NULL, &$show=NULL) {
		if ($this->isMyItem(LIST_RIGHTS)) return true;
		if (GALLERY_SECURITY == 'private') {	// only registered users allowed
			return false;
		}
		$allcategories = $this->getCategories();
		if (count($allcategories) == 0) return true;
		foreach ($allcategories as $category) {
			$catobj = new ZenpageCategory($category['titlelink']);
			if ($authtype = $catobj->checkforGuest()) {
				return $authtype;
			}
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
		if(!empty($catlink)) {
			$categories = $this->getCategories();
			$count = 0;
			foreach($categories as $cat) {
				if($catlink == $cat['titlelink']) {
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
		if(!empty($catlink)) {
			$categories = $this->getCategories();
			$count = 0;
			foreach($categories as $cat) {
				$catobj = new ZenpageCategory($cat['titlelink']);
				$parentid = $catobj->getParentID();
				$parentcats = $catobj->getParents();
				foreach($parentcats as $parentcat) {
					if($catlink == $parentcat) {
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

} // zenpage news class end


?>