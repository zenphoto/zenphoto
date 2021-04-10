<?php

/**
 * zenpage page class
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */
class ZenpagePage extends ZenpageItems {

	public $manage_rights = MANAGE_ALL_PAGES_RIGHTS;
	public $manage_some_rights = ZENPAGE_PAGES_RIGHTS;
	public $view_rights = ALL_PAGES_RIGHTS;
	public $parent = null;
	public $parents = null;
	protected $is_public = null;

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
			$newobj->setShow(0);
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
		if ($success = parent::remove()) {
			$sortorder = $this->getSortOrder();
			if ($this->id) {
				$success = $success && query("DELETE FROM " . prefix('obj_to_tag') . "WHERE `type`='pages' AND `objectid`=" . $this->id);
				$success = $success && query("DELETE FROM " . prefix('comments') . " WHERE ownerid = " . $this->getID() . ' AND type="pages"'); // delete any comments
				//	remove subpages
				$mychild = strlen($sortorder) + 4;
				$result = query_full_array('SELECT * FROM ' . prefix('pages') . " WHERE `sort_order` like '" . $sortorder . "-%'");
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
	 * @since Zenphoto 1.5.5
	 * 
	 * @return obj|null
	 */
	function getParent() {
		if (is_null($this->parent)) {
			$parentid = $this->getParentID();
			$obj = getItembyID('pages', $parentid);
			if ($obj) {
				return $obj;
			}
		} else {
			return $this->parent;
		}
		return null;
	}

	/**
	 * Gets the parent pages' titlelinks recursivly to the page
	 *
	 * @return array
	 */
	function getParents() {
		if (func_num_args() != 0) {
			debuglog(gettext('class ZenpagePage getParents(): The parameters $parentid and $initparents have been removed in Zenphoto 1.5.5.'));
		}
		if (is_null($this->parents)) {
			$parents = array();
			$page = $this;
			while (!is_null($page = $page->getParent())) {
				array_unshift($parents, $page->getTitlelink());
			}
			return $this->parents = $parents;
		} else {
			return $this->parents;
		}
	}

	/**
	 * Gets the sub pages of a page
	 * @param bool $published TRUE for published or FALSE for all pages including un-published
	 * @param bool $toplevel ignored, left for parameter compatibility
	 * @param int $number number of pages to get (NULL by default for all)
	 * @param string $sorttype NULL for the standard order as sorted on the backend, "title", "date", "popular", "mostrated", "toprated", "random"
	 * @param string $sortdirection false for ascending, true for descending
	 * @param string $author Optional author name to get the pages of
	 * @return array
	 */
	function getPages($published = NULL, $toplevel = false, $number = NULL, $sorttype = NULL, $sortdirection = NULL, $author = null) {
		global $_zp_zenpage;
		$subpages = array();
		$sortorder = $this->getSortOrder();
		$pages = $_zp_zenpage->getPages($published, false, $number, $sorttype, $sortdirection, $author);
		foreach ($pages as $page) {
			if ($page['parentid'] == $this->getID() && $page['sort_order'] != $sortorder) { // exclude the page itself!
				array_push($subpages, $page);
			}
		}
		return $subpages;
	}

	/**
	 * Checks if user is allowed to access the page
	 * @param $hint
	 * @param $show
	 */
	function checkforGuest(&$hint = NULL, &$show = NULL) {
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
				$sql = 'SELECT `titlelink` FROM ' . prefix('pages') . ' WHERE `id`=' . $parentID;
				$result = query_single_row($sql);
				$pageobj = new ZenpagePage($result['titlelink']);
				$hash = $pageobj->getPassword();
			}
		}
		if (empty($hash)) { // no password required
			return 'zp_public_access';
		} else {
			$authType = "zp_page_auth_" . $pageobj->getID();
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
	 * Checks if a page is protected and returns TRUE or FALSE
	 * NOTE: This function does only check if a password is set not if it has been entered! Use $this->checkforGuest() for that.
	 *
	 * @return bool
	 */
	function isProtected() {
		return $this->checkforGuest() != 'zp_public_access';
	}
	
	/**
	 * Returns true if this page is published and also all of its parents.
	 * 
	 * @since Zenphoto 1.5.5
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
	 * @param bit $action what the caller wants to do
	 *
	 * returns true of access is allowed
	 */
	function isMyItem($action) {
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
				if (array_search($this->getTitlelink(), $mypages) !== false) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Returns full path to a specific page
	 *
	 * @return string
	 */
	function getLink() {
		return zp_apply_filter('getLink', rewrite_path(_PAGES_ . '/' . $this->getTitlelink() . '/', '/index.php?p=pages&title=' . $this->getTitlelink()), $this, NULL);
	}

}

?>