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
	var $view_rights = VIEW_NEWS_RIGHTS;

	function ZenpageCategory($catlink) {
		$new = parent::PersistentObject('news_categories', array('titlelink'=>$catlink), NULL, true, empty($catlink));
	}

	/**
	 * Returns the description
	 *
	 * @return string
	 */
	function getDesc() {
		return get_language_string($this->get('desc'));
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
		return $this->get('password');
	}

	/**
	 * Sets the encrypted password
	 *
	 * @param string $pwd the cleartext password
	 */
	function setPassword($pwd) {
		global $_zp_authority;
		if (empty($pwd)) {
			$this->set('password', "");
		} else {
			$this->set('password', $_zp_authority->passwordHash($this->get('user'), $pwd));
		}
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
			if($itemparentid && $itemid == $currentparentid) {
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
			$mycategories = $_zp_current_admin_obj->getObjects('news');
			if (!empty($mycategories)) {
				if (in_array($this->getTitlelink(), $mycategories)) {
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
	 * @param string $published "published" for an published articles,
	 * 													"unpublished" for an unpublished articles,
	 * 													"sticky" for sticky articles,
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
	function getArticles($articles_per_page='', $published=NULL,$ignorepagination=false,$sortorder="date", $sortdirection="desc",$sticky=true) {
		global $_zp_current_category, $_zp_post_date, $_zp_zenpage;
		$_zp_zenpage->processExpired('news');
		if (is_null($published)) {
			if(zp_loggedin(ZENPAGE_NEWS_RIGHTS)) {
				$published = "all";
			} else {
				$published = "published";
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
		if (!$articles_per_page || $ignorepagination) {
			$limit = '';
		} else {
			$limit = " LIMIT ".$zenpage->getOffset($articles_per_page).",".$articles_per_page;
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
				break;
			case "unpublished":
				$show = " AND `show` = 0 AND date <= '".date('Y-m-d H:i:s')."'";
				break;
			case 'sticky':
				$show = ' AND `sticky` <> 0';
				break;
			case "all":
				$show = "";
				break;
		}
		$order = " ORDER BY ".$sticky."news.$sort1 $dir";
		$sql = "SELECT DISTINCT news.titlelink FROM ".prefix('news')." as news, ".prefix('news2cat')." as cat WHERE".$cat.$show.$order.$limit;
		$result = query_full_array($sql);

		return $result;
	}


} // zenpage news category class end



?>