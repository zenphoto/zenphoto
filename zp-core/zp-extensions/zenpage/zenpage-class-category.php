<?php
/**
 * zenpage news category class
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */

class ZenpageCategory extends ZenpageRoot {

	var $manage_rights = MANAGE_ALL_NEWS_RIGHTS;
	var $manage_some_rights = ZENPAGE_NEWS_RIGHTS;
	var $view_rights = ALL_NEWS_RIGHTS;

	function __construct($catlink, $create=NULL) {
		$new = parent::PersistentObject('news_categories', array('titlelink'=>$catlink), 'titlelink', true, empty($catlink), $create);
	}

	/**
	 * Returns the description
	 *
	 * @return string
	 */
	function getDesc($locale=NULL) {
		return get_language_string($this->get('desc'),$locale);
	}

	/**
	 * Stores the description
	 *
	 * @param string $desc description text
	 */
	function setDesc($desc) { $this->set('desc', $desc); }

	/**
	 * Returns the sort order
	 *
	 * @return string
	 */
	function getSortOrder() { return $this->get('sort_order'); }

	/**
	 * Stores the sort order
	 *
	 * @param string $sortorder image sort order
	 */
	function setSortOrder($sortorder) { $this->set('sort_order', $sortorder); }


	function getUser() {
		return $this->get('user');
	}
	/**
	 * Sets the guest user
	 *
	 * @param string $user
	 */
	function setUser($user) { $this->set('user', $user);	}

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
	function setPasswordHint($hint) { $this->set('password_hint', $hint); }

	/**
	 * Deletes a category (and also if existing its subpages) from the database
	 *
	 */
	function remove() {
		if ($success = parent::remove()) {
			$sortorder = $this->getSortOrder();
			$success = query("DELETE FROM ".prefix('news2cat')." WHERE cat_id = ".$this->getID()); // the cat itself
			// get Subcategories
			$mychild = strlen($sortorder)+4;
			$result = query_full_array('SELECT * FROM '.prefix('news_categories')." WHERE `sort_order` like '".$sortorder."-%'");
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
	 * @return array
	 */
	function getSubCategories() {
		global $_zp_zenpage;
		$subcategories = array();
		$sortorder = $this->getSortOrder();
		foreach($_zp_zenpage->getAllCategories(false) as $cat) {
			$catobj = new ZenpageCategory($cat['titlelink']);
			if($catobj->getParentID() == $this->getID() && $catobj->getSortOrder() != $sortorder) { // exclude the category itself!
				array_push($subcategories,$catobj->getTitlelink());
			}
		}
		if(count($subcategories) != 0) {
			return $subcategories;
		} else {
			return FALSE;
		}
	}

	/**
	 * Checks if the current news category is a sub category of $catlink
	 *
	 * @return bool
	 */
	function isSubNewsCategoryOf($catlink) {
		if(!empty($catlink)) {
			$parentid = $this->getParentID();
			$categories = $this->getParents();
			$count = 0;
			foreach($categories as $cat) {
				if($catlink == $cat) {
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
	function getParents(&$parentid='',$initparents=true) {
		global $parentcats,$_zp_zenpage;
		$allitems = $_zp_zenpage->getAllCategories(false);
		if($initparents) {
			$parentcats = array();
		}
		if(empty($parentid)) {
			$currentparentid = $this->getParentID();
		} else {
			$currentparentid = $parentid;
		}
		foreach($allitems as $item) {
			$obj = new ZenpageCategory($item['titlelink']);
			$itemtitlelink = $obj->getTitlelink();
			$itemid = $obj->getID();
			$itemparentid = $obj->getParentID();
			if($itemid == $currentparentid) {
				array_unshift($parentcats,$itemtitlelink);
				$obj->getParents($itemparentid,false);
			}
		}
		return $parentcats;
	}

	/**
	 * Checks if user is allowed to access news category
	 * @param $hint
	 * @param $show
	 */
	function checkforGuest(&$hint=NULL, &$show=NULL) {
		if (!parent::checkForGuest()) {
			return false;
		}
		$obj = $this;
		$hash = $this->getPassword();
		while(empty($hash) && !is_null($obj)) {
			$parentID = $obj->getParentID();
			if (empty($parentID)) {
				$obj = NULL;
			} else {
				$sql = 'SELECT `titlelink` FROM '.prefix('news_categories').' WHERE `id`='.$parentID;
				$result = query_single_row($sql);
				$obj = new ZenpageCategory($result['titlelink']);
				$hash = $obj->getPassword();
			}
		}
		if (empty($hash)) { // no password required
			return 'zp_public_access';
		} else {
			$authType = "zp_category_auth_" . $this->getID();
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
	 * Checks if a category is protected and returns TRUE or FALSE
	 * NOTE: This function does only check if a password is set not if it has been entered! Use $this->checkforGuest() for that.
	 *
	 * @return bool
	 */
	function isProtected() {
		return $this->checkforGuest() != 'zp_public_access';
	}

	function isMyItem($action) {
		global $_zp_current_admin_obj;
		if (parent::isMyItem($action)) {
			return true;
		}
		if (zp_loggedin($action)) {
			if ($action == LIST_RIGHTS && $this->getShow()) {
				return true;
			}
			$mycategories = $_zp_current_admin_obj->getObjects('news');
			if (!empty($mycategories)) {
				$allowed = $this->getParents();
				array_unshift($allowed, $this->getTitlelink());
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
	 *													"published-unpublished" for published articles only from an unpublished category,
	 * 													"unpublished" for unpublished articles,
	 * 													"sticky" for sticky articles (published or not!) for Admin page use only,
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
	function getArticles($articles_per_page=0, $published=NULL,$ignorepagination=false,$sortorder="date", $sortdirection="desc",$sticky=true) {
		global $_zp_current_category, $_zp_post_date;
		Zenpage::processExpired('news');
		if (empty($published)) {
			$published = "published";
			if(zp_loggedin(MANAGE_ALL_NEWS_RIGHTS)) {
				$published = "all";
			}
		}
		$show = "";
		// new code to get nested cats
		$catid = $this->getID();
		$subcats = $this->getSubCategories();
		if($subcats) {
			$cat = " (cat.cat_id = '".$catid."'";
			foreach($subcats as $subcat) {
				$subcatobj = new ZenpageCategory($subcat);
				$cat .= "OR cat.cat_id = '".$subcatobj->getID()."' ";
			}
			$cat .= ") AND cat.news_id = news.id ";
		} else {
			$cat = " cat.cat_id = '".$catid."' AND cat.news_id = news.id ";
		}
		if(in_context(ZP_ZENPAGE_NEWS_DATE)) {
			$postdate = $_zp_post_date;
		} else {
			$postdate = NULL;
		}

		if ($sticky) {
			$sticky = 'sticky DESC,';
		}
		// sortorder and sortdirection
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
		/*** get articles by category ***/
		switch($published) {
			case "published":
				$show = " AND `show` = 1 AND date <= '".date('Y-m-d H:i:s')."'";
				$getUnpublished = false;
				break;
			case "published-unpublished":
				$show = " AND `show` = 1 AND date <= '".date('Y-m-d H:i:s')."'";
				$getUnpublished = true;
				break;
			case "unpublished":
				$show = " AND `show` = 0 AND date <= '".date('Y-m-d H:i:s')."'";
				$getUnpublished = true;
				break;
			case 'sticky':
				$show = ' AND `sticky` <> 0';
				$getUnpublished = true;
				break;
			case "all":
				$getUnpublished = true;
				$show = "";
				break;
		}
		$order = " ORDER BY ".$sticky."news.$sort1 $dir";
		$sql = "SELECT DISTINCT news.titlelink FROM ".prefix('news')." as news, ".prefix('news2cat')." as cat WHERE".$cat.$show.$order;
		$resource = $result = query($sql);
		if ($resource) {
			if ($ignorepagination) {
				$offset = 0;
			} else {
				$offset = Zenpage::getOffset($articles_per_page);
			}
			if (is_object($_zp_current_category)) {
				$currentcategory = $_zp_current_category->getTitlelink();
			} else {
				$currentcategory = false;
			}
			$result = array();
			while ($item = db_fetch_assoc($resource)) {
				$article = new ZenpageNews($item['titlelink']);
				if ($getUnpublished || $currentcategory && ($article->inNewsCategory($currentcategory)) || $article->categoryIsVisible()) {
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
 * Returns the full path to a news category
 *
 * @param string $catlink The category link of a category
 *
 * @return string
 */
function getCategoryLink() {
	global $_zp_zenpage;
	return $_zp_zenpage->getNewsBaseURL().$_zp_zenpage->getNewsCategoryPath().urlencode($this->getTitlelink());
}


} // zenpage news category class end



?>