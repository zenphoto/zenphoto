<?php
/**
 * zenpage news category class
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */

class ZenpageCategory extends Zenpage {

	var $manage_rights = MANAGE_ALL_NEWS_RIGHTS;
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
		$subcategories = array();
		$sortorder = $this->getSortOrder();
		//echo 'category to get :'.$catlink. '/sortorder:'.$sortorder.'<br />-----<br />';
		foreach(getAllCategories() as $cat) {
			$catobj = new ZenpageCategory($cat['titlelink']);
			$hasSortorder = strstr($catobj->getSortOrder(),$sortorder);
			//echo '/cat:'.$cat['cat_link'].'/catsortorder:'.$cat['sort_order'].'/sortorder searchred:'.$sortorder.'<br />';
			if($hasSortorder && $catobj->getSortOrder()  != $sortorder) { // exclude the category itself!
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
		$catlink = sanitize($catlink);
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
		global $parentcats;
		$allitems = getAllCategories();
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
			return 'zp_unprotected';
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
		return $this->checkforGuest() != 'zp_unprotected';
	}

	function isMyItem($action) {
		return parent::isMyItem($action);
	}

} // zenpage news category class end


?>