<?php

/**
 * zenpage page class
 *
 * @author Malte Müller (acrylian)
 * @package zpcore\plugins\zenpage\classes
 */
class ZenpagePage extends ZenpageItems {

	public $manage_rights = MANAGE_ALL_PAGES_RIGHTS;
	public $manage_some_rights = ZENPAGE_PAGES_RIGHTS;
	public $view_rights = ALL_PAGES_RIGHTS;
	public $parent = null;
	public $parents = null;
	public $urparent = null;

	function __construct($titlelink, $allowCreate = NULL) {
		if (is_array($titlelink)) {
			$titlelink = $titlelink['titlelink'];
		}
		$new = $this->instantiate('pages', array('titlelink' => $titlelink), 'titlelink', true, empty($titlelink), $allowCreate);
		$this->exists = $this->loaded;
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
	
	/**
	 * Sets a default sortorder for a page.
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
	 * Gets a default sortorder for a page.
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
		return $_zp_zenpage->getItemDefaultSortorder('page', $this->getParentID());
	}

	/**
	 * Returns the guest user
	 *
	 * @return string
	 */
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

	/**
	 * Returns the password
	 *
	 * @return string
	 */
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
		if($this->isProtected() && !$this->getPassword()) {
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

	/**
	 * Returns the password hint
	 *
	 * @return string
	 */
	function getPasswordHint($locale = NULL) {
		$text = ($this->get('password_hint'));
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
		$this->set('password_hint', tagURLs($hint));
	}

	/**
	 * duplicates an article
	 * @param string $newtitle the title for the new article
	 */
	function copy($newtitle) {
		$newID = $newtitle;
		$id = parent::copy(array('titlelink' => $newID));
		if (!$id) {
			$newID = $newtitle . ':' . seoFriendly(date('Y-m-d_H-i-s'));
			$id = parent::copy(array('titlelink' => $newID));
		}
		if ($id) {
			$newobj = new ZenpagePage($newID);
			$newobj->setTitle($newtitle);
			$newobj->setSortOrder(NULL);
			$newobj->setTags($this->getTags());
			$newobj->setDateTime(date('Y-m-d H:i:s'));
			$newobj->setPublished(0);
			$newobj->save();
			return $newobj;
		}
		return false;
	}

	/**
	 * Deletes a page (and also if existing its subpages) from the database
	 *
	 */
	function remove() {
		global $_zp_db;
		if ($success = parent::remove()) {
			$sortorder = $this->getSortOrder();
			if ($this->id) {
				$success = $success && $_zp_db->query("DELETE FROM " . $_zp_db->prefix('obj_to_tag') . "WHERE `type`='pages' AND `objectid`=" . $this->id);
				$success = $success && $_zp_db->query("DELETE FROM " . $_zp_db->prefix('comments') . " WHERE ownerid = " . $this->getID() . ' AND type="pages"'); // delete any comments
				//	remove subpages
				$mychild = strlen($sortorder) + 4;
				$result = $_zp_db->queryFullArray('SELECT * FROM ' . $_zp_db->prefix('pages') . " WHERE `sort_order` like '" . $sortorder . "-%'");
				if (is_array($result)) {
					foreach ($result as $row) {
						if (strlen($row['sort_order']) == $mychild) {
							$subpage = new ZenpagePage($row['titlelink']);
							$success = $success && $subpage->remove();
						}
					}
				}
			}
		}
		return $success;
	}
	
	/**
	 * Gets the parent page object based on the parentid set
	 * 
	 * @since 1.5.5
	 * 
	 * @return obj|null
	 */
	function getParent() {
		if ($this->getParentID()) {
			if (is_null($this->parent)) {
				$obj = getItembyID('pages', $this->getParentID());
				if ($obj) {
					return $obj;
				}
			} else {
				return $this->parent;
			}
		}
		return null;
	}

	/**
	 * Gets the parent pages' name recursivly to the page
	 *
	 * @return array
	 */
	function getParents() {
		if (func_num_args() != 0) {
			deprecationNotice(gettext('class ZenpagePage getParents(): The parameters $parentid and $initparents have been removed in Zenphoto 1.5.5.'), true);
		}
		if ($this->getParentID()) {
			if (is_null($this->parents)) {
				$parents = array();
				$page = $this;
				while (!is_null($page = $page->getParent())) {
					array_unshift($parents, $page->getName());
				}
				return $this->parents = $parents;
			} else {
				return $this->parents;
			}
		}
		return $this->parents = array();
	}

	/**
	 * Gets the sub pages of a page
	 * @param bool $published TRUE for published or FALSE for all pages including un-published
	 * @param bool $directchilds Default true to get only the direct sub level pages, set to false to get all levels
	 * @param int $number number of pages to get (NULL by default for all)
	 * @param string $sorttype NULL for the standard order as sorted on the backend, "title", "date", "popular", "mostrated", "toprated", "random"
	 * @param string $sortdirection false for ascending, true for descending
	 * @param string $author Optional author name to get the pages of
	 * @return array
	 */
	function getPages($published = NULL, $directchilds = true, $number = NULL, $sorttype = NULL, $sortdirection = NULL, $author = null) {
		global $_zp_zenpage;
		return $_zp_zenpage->getPages($published, $directchilds, $number, $sorttype, $sortdirection, $author, $this);
	}

	/**
	 * Checks if user is allowed to access the page
	 * @param $hint
	 * @param $show
	 */
	function checkforGuest(&$hint = NULL, &$show = NULL) {
		global $_zp_db;
		if (!parent::checkForGuest()) {
			return false;
		}
		$pageobj = $this;
		$hash = $pageobj->getPassword();
		while (empty($hash) && !is_null($pageobj)) {
			$parentID = $pageobj->getParentID();
			if (empty($parentID)) {
				$pageobj = NULL;
			} else {
				$sql = 'SELECT `titlelink` FROM ' . $_zp_db->prefix('pages') . ' WHERE `id`=' . $parentID;
				$result = $_zp_db->querySingleRow($sql);
				$pageobj = new ZenpagePage($result['titlelink']);
				$hash = $pageobj->getPassword();
			}
		}
		if (empty($hash)) { // no password required
			return 'zp_public_access';
		} else {
			$authType = "zpcms_auth_page_" . $pageobj->getID();
			$saved_auth = zp_getCookie($authType);
			if ($saved_auth == $hash) {
				return $authType;
			} else {
				$user = $pageobj->getUser();
				$show = (!empty($user));
				$hint = $pageobj->getPasswordHint();
				return false;
			}
		}
	}
	
	/**
	 * Returns true if this page is published and also all of its parents.
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
	 * Checks if user is author of page
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
				return LIST_RIGHTS;
			}
			if ($_zp_current_admin_obj->getUser() == $this->getAuthor()) {
				return true;
			}
			$mypages = $_zp_current_admin_obj->getObjects('pages');
			if (!empty($mypages)) {
				if (array_search($this->getName(), $mypages) !== false) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Returns full path to a specific page
	 *
	 * @param string $path Default null, optionally pass a path constant like WEBPATH or FULLWEBPATH
	 * @return string
	 */
	function getLink($path = null) {
		return zp_apply_filter('getLink', rewrite_path(_PAGES_ . '/' . $this->getName() . '/', '/index.php?p=pages&title=' . $this->getName(), $path), $this, NULL);
	}

}