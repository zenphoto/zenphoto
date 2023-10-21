<?php
/**
 * The basic ThemeObject class. Extends PersistentObject, is extended by various Theme related objects.
 * Provides some basic methods that all use.
 * 
 * @package zpcore\classes\objects
 */
class ThemeObject extends PersistentObject {

	private $commentcount; //Contains the number of comments
	public $comments = NULL; //Contains an array of the comments of the object
	public $manage_rights = ADMIN_RIGHTS;
	public $manage_some_rights = ADMIN_RIGHTS;
	public $view_rights = VIEW_ALL_RIGHTS;
	protected $is_public = null;
	protected $is_protected = null;

	/**
	 * Class instantiator
	 */
	function __construct() {
		// no action required
	}

	/**
	 * Returns the title
	 *
	 * @return string
	 */
	function getTitle($locale = NULL) {
		$text = $this->get('title');
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = unTagURLs($text);
		return $text;
	}

	/**
	 * Stores the title
	 *
	 * @param string $title the title
	 */
	function setTitle($title) {
		$this->set('title', tagURLs($title));
	}

	/**
	 * Returns the partent id
	 *
	 * @return string
	 */
	function getParentID() {
		return $this->get('parentid');
	}

	/**
	 * Sets the ParentID field
	 * @param $v id of the parent
	 */
	function setParentID($v) {
		$this->set('parentid', $v);
	}

	/**
	 * Returns the hitcount
	 *
	 * @return int
	 */
	function getHitcounter() {
		return $this->get('hitcounter');
	}

	/**
	 * counts visits to the object
	 */
	function countHit() {
		$this->set('hitcounter', $this->get('hitcounter') + 1);
		$this->save();
	}
	
	/**
	 * Returns true if the item itself is published
	 * 
	 * @since 1.5.8
	 * 
	 * For a check including inheritance use isPublic()
	 *
	 * @param bool $use_dbvalue Set to true to use the actual db value stored 
	 * and not the possibly temporary modified value (e.g. if in scheduled publishing or expiration)
	 * @return bool
	 */
	function isPublished($use_dbvalue = false) {
		if ($use_dbvalue) {
			return $this->get('show', false);
		}
		return $this->get('show');
	}

	/**
	 * Stores the published value
	 * 
	 * @since 1.5.8
	 *
	 * @param bool $published True if the item is published
	 */
	function setPublished($published) {
		$old = $this->get('show');
		$new = (int) ($published && true);
		$this->set('show', $new);
		if ($old != $new && $this->get('id')) {
			zp_apply_filter('show_change', $this); // TODO rename to "published_change"
		}
	}
	
	/**
	 * Returns true if itself published but unpublished status is inheretited by a parent
	 * @since 1.6.1
	 * 
	 * @return bool
	 */
	function isUnpublishedByParent() {
		if ($this->isPublished() && !$this->isPublic()) {
			return true;
		}
		return false;
	}
	
	/**
	 * Returns true if this object is published and does not inherit any unpublish status by a parent
	 * 
	 * Child classes with any hierachic structure need to override and extend this method to cover inheritance.
	 * 
	 * @since 1.5.5
	 * @since 1.6.1 Moved from mediaObject to themeObject class
	 * 
	 * @return bool
	 */
	function isPublic() {
		if (is_null($this->is_public)) {
			if (!$this->isPublished()) {
				return $this->is_public = false;
			}
		} else {
			return $this->is_public;
		}
	}
	
	/**
	 * Checks if the current item is visible (= listed) to the current visitor via rights, publish status or protection status
	 *
	 * Convenience wrapper for various methods.
	 * 
	 * @since 1.6.1
	 * 
	 * @see isPublic()
	 * @see isProtectedByParent()
	 * @see isMyItem()
	 * 
	 * @param bit $action User rights level, default LIST_RIGHTS
	 * @return bool
	 */
	function isVisible($action = LIST_RIGHTS) {
		if ($this->isMyItem($action) || ($this->isPublic() && !$this->isProtectedByParent())) {
			return true;
		}
		return false;
	}

	/**
	 * Returns true published
	 * 
	 * @deprecated 2.0 – Use isPublished() instead
	 * @return bool
	 */
	function getShow() {
		deprecationNotice(gettext('Use isPublished() instead'));
		return $this->isPublished();
	}

	/**
	 * Stores the published value
	 * 
	 * @deprecated 2.0 – Use setPublished() instead
	 * @param bool $show True if the album is published
	 */
	function setShow($show) {
		deprecationNotice(gettext('Use setPublished() instead'));
		$this->setPublished($show);
	}

	/**
	 * Returns the tag data
	 *
	 * @return string
	 */
	function getTags() {
		return readTags($this->getID(), $this->table);
	}

	/**
	 * Stores tag information
	 *
	 * @param string $tags the tag list
	 */
	function setTags($tags) {
		if (!$this->getID()) { //	requires a valid id to link tags to the object
			$this->save();
		}
		storeTags(array_unique($tags), $this->getID(), $this->table);
	}

	/**
	 * Checks if an object has a tag assigned.
	 *
	 * @param string $checktag tag to check for
	 *
	 * @return bool
	 */
	function hasTag($checktag) {
		$tags = $this->getTags();
		return in_array($checktag, $tags);
	}

	/**
	 * Returns the unformatted date
	 *
	 * @return int
	 */
	function getDateTime() {
		return strval($this->get('date'));
	}

	/**
	 * Stores the date
	 *
	 * @param string $datetime formatted date
	 */
	function setDateTime($datetime) {
		if ($datetime) {
			$newtime = dateTimeConvert($datetime);
			if ($newtime !== false) {
				$this->set('date', $newtime);
			}
		} else {
			$this->set('date', NULL);
		}
	}

	/**
	 * Returns the codeblocks as an serialized array
	 *
	 * @return array
	 */
	function getCodeblock() {
		return unTagURLs($this->get("codeblock"));
	}

	/**
	 * set the codeblocks as an serialized array
	 *
	 */
	function setCodeblock($cb) {
		$this->set('codeblock', tagURLs($cb));
	}

	/**
	 * returns the custom data field
	 *
	 * @return string
	 */
	function getCustomData($locale = NULL) {
		$text = $this->get('custom_data');
		if ($locale !== 'all') {
			$text = get_language_string($text, $locale);
		}
		$text = unTagURLs($text);
		return $text;
	}

	/**
	 * Sets the custom data field
	 *
	 * @param string $val the value to be put in custom_data
	 */
	function setCustomData($val) {
		$this->set('custom_data', tagURLs($val));
	}

	/**
	 * Retuns true if comments are allowed
	 *
	 * @return bool
	 */
	function getCommentsAllowed() {
		return $this->get('commentson');
	}

	/**
	 * Sets the comments allowed flag
	 *
	 * @param bool $commentson true if they are allowed
	 */
	function setCommentsAllowed($commentson) {
		$this->set('commentson', (int) ($commentson && true));
	}

	/**
	 * Returns an array of comments for this album
	 *
	 * @param bool $moderated if false, ignores comments marked for moderation
	 * @param bool $private if false ignores private comments
	 * @param bool $desc set to true for descending order
	 * @return array
	 */
	function getComments($moderated = false, $private = false, $desc = false) {
		global $_zp_db;
		$sql = "SELECT *, (date + 0) AS date FROM " . $_zp_db->prefix("comments") .
						" WHERE `type`='" . $this->table . "' AND `ownerid`='" . $this->getID() . "'";
		if (!$moderated) {
			$sql .= " AND `inmoderation`=0";
		}
		if (!$private) {
			$sql .= " AND `private`=0";
		}
		$sql .= " ORDER BY id";
		if ($desc) {
			$sql .= ' DESC';
		}
		$comments = $_zp_db->queryFullArray($sql);
		$this->comments = $comments;
		return $this->comments;
	}

	/**
	 * Adds comments to the album
	 * assumes data is coming straight from GET or POST
	 *
	 * Returns a comment object
	 *
	 * @param string $name Comment author name
	 * @param string $email Comment author email
	 * @param string $website Comment author website
	 * @param string $comment body of the comment
	 * @param string $code CAPTCHA code entered
	 * @param string $code_ok CAPTCHA hash expected
	 * @param string $ip the IP address of the comment poster
	 * @param bool $private set to true if the comment is for the admin only
	 * @param bool $anon set to true if the poster wishes to remain anonymous
	 * @param string $customdata
	 * @param bool $dataconfirmation true or false if data privacy confirmation was required
	 * @return object
	 */
	function addComment($name, $email, $website, $comment, $code, $code_ok, $ip, $private, $anon, $customdata, $dataconfirmation) {
		$goodMessage = zp_apply_filter('object_addComment', $name, $email, $website, $comment, $code, $code_ok, $this, $ip, $private, $anon, $customdata, false, $dataconfirmation);
		return $goodMessage;
	}

	/**
	 * Returns the count of comments in the album. Ignores comments in moderation
	 *
	 * @return int
	 */
	function getCommentCount() {
		global $_zp_db;
		if (is_null($this->commentcount)) {
			if ($this->comments == null) {
				$count = $_zp_db->count("comments", "WHERE `type`='" . $this->table . "' AND `inmoderation`=0 AND `private`=0 AND `ownerid`=" . $this->getID());
				$this->commentcount = $count;
			} else {
				$this->commentcount = count($this->comments);
			}
		}
		return $this->commentcount;
	}

	/**
	 * Checks basic access rights of an object
	 * @param bit $action User rights level, default LIST_RIGHTS
	 */
	function isMyItem($action = LIST_RIGHTS) {
		$this->checkPublishDates();
		if (zp_loggedin($this->manage_rights)) {
			return true;
		}
		if (zp_loggedin($this->view_rights) && ($action == LIST_RIGHTS)) { // sees all
			return true;
		}
		if (zp_apply_filter('check_credentials', false, $this, $action)) {
			return true;
		}
		return NULL;
	}

	/**
	 * Checks if the item is password protected and if the password has been entered
	 * 
	 * @param $hint
	 * @param $show
	 */
	function checkForGuest(&$hint = NULL, &$show = NULL) {
		return !(GALLERY_SECURITY != 'public');
	}
	
	/**
	 * Checks if the item is password protected. Checks if the password has been entered
	 * 	 * 
	 * @since 1.6.1 Moved to themeObject class as central definition to avoid a lot same override methods
	 *
	 * @return bool
	 */
	function isProtected() {
		if (is_null($this->is_protected)) {
			return $this->is_protected = !in_array($this->checkforGuest(), array('zp_public_access', true));
		}
		return $this->is_protected;
	}

	/**
	 * Check if the item is not protected itself but protection is inherited by a parent. Checks if the password has been entered.
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
	 * Gets the password if set
	 * 
	 * Placeholder for all child classes. Needs to be properly overriden there if password functionality is available
	 * @since 1.6.1 Added for general class compatibility
	 * @return null
	 */
	function getPassword() {
		return null;
	}

	/**
	 * Checks if viewing of object is allowed by rights or protection status
	 * 
	 * @param string $hint
	 * @param string $show
	 */
	function checkAccess(&$hint = NULL, &$show = NULL) {
		if ($this->isMyItem(LIST_RIGHTS)) {
			return true;
		}
		return $this->checkforGuest($hint, $show);
	}

	/**
	 * Checks if the item is either expired or needs to be scheduled published
	 * 
	 * Unpublishes the item if expired (saves to db) or scheduled (temporary) and 
	 * returns false if expired or scheduled.
	 * @return boolean
	 */
	function checkPublishDates() {
		$row = array();
		if (AlbumBase::isAlbumClass($this) || Image::isImageClass($this)) {
			$row = array(
					'show' => $this->isPublished(),
					'expiredate' => $this->getExpireDate(),
					'publishdate' => $this->getPublishDate()
			);
		} else if ($this->table == 'news' || $this->table == 'pages') {
			$row = array(
					'show' => $this->isPublished(),
					'expiredate' => $this->getExpireDate(),
					'publishdate' => $this->getDateTime()
			);
		}
		$check = self::checkScheduledPublishing($row);
		switch ($check) {
			case 1:
				$this->setPublished(0);
				$this->save();
				return false;
			case 2:
				$this->setPublished(0);
				return false;
			default;
				return true;
		}
	}

	/**
	 * Checks if the item has expired or is in scheduled publishing
	 * 
	 * Returns 1 if expired, 2 if in scheduled future publishing
	 * 
	 * @since 1.5.7 - Code moved from the deprecated checKPublishDates() function
	 * @param array $row database row of the object
	 * @return int
	 */
	static function checkScheduledPublishing($row) {
		if (@$row['show']) {
			if (isset($row['expiredate']) && $row['expiredate'] && $row['expiredate'] != '0000-00-00 00:00:00') {
				if ($row['expiredate'] < date('Y-m-d H:i:s')) {
					return 1; 
				}
			}
			if (isset($row['publishdate']) && $row['publishdate'] && $row['publishdate'] != '0000-00-00 00:00:00') {
				if ($row['publishdate'] > date('Y-m-d H:i:s')) {
					return 2; 
				}
			}
			return null;
		}
	}
	
	/**
	 * Returns the expired date if available,
	 * 
	 * @since 1.6.1 Added as placeholder for general class compatibility. Child classes need to override properly
	 * @return string
	 */
	function getExpireDate() {
		return '';
	}
	
	/**
	 * Returns true if the item has a proper expire date set no matter if it has expired already or will expire in the future
	 * 
	 * @since 1.5.7
	 * @return boolean
	 */
	function hasExpireDate() {
		if ($this->getExpireDate() && $this->getExpireDate() != '0000-00-00 00:00:00') {
			return true;
		}
	}

	/**
	 * Returns true if the item will be automatically unpublished by a not yet reached future expire date
	 * 
	 * @since 1.5.7
	 * @return boolean
	 */
	function hasExpiration() {
		if ($this->hasExpireDate() && $this->get('show', false) && $this->getExpireDate() > date('Y-m-d H:i:s')) {
			return true;
		}
		return false;
	}

	
	/**
	 * Returns true if a future expiredate is set but the item is unpublished
	 * 
	 * @since 1.5.7 
	 * @return boolean
	 */
	function hasInactiveExpiration() {
		if($this->hasExpiredate() && !$this->get('show', false)) {
			return true;
		}
		return false;
	}

	/**
	 * Returns true if the items has been unpublished after reaching the set expire date.
	 * 
	 * @since 1.5.7
	 * @return boolean
	 */
	function hasExpired() {
		if ($this->hasExpireDate() && $this->getExpireDate() <= date('Y-m-d H:i:s')) {
			return true;
		}
		return false;
	}
	
	/**
	 * Returns the future date (publishdate for gallery, date for Zenpage items) if set to the future only
	 * 
	 * @since 1.5.7
	 * @return string | null
	 */
	function hasFutureDate() {
		$date = null;
		if ($this->table == 'images' || $this->table == 'albums') {
			$date = $this->getPublishDate();
		} else if ($this->table == 'news' || $this->table == 'pages') {
			$date = $this->getDateTime();
		}
		if($date && $date != '0000-00-00 00:00:00' && $date > date('Y-m-d H:i:s') ) {
			return $date;
		}
		return null;
	}

	/**
	 * Returns true if the item will be automatically published by a future date set
	 * 
	 * @since 1.5.7
	 * @return boolean
	 */
	function hasPublishSchedule() {
		if ($this->hasFutureDate() && $this->get('show', false)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Returns true if the item has a future date but is not published
	 * 
	 * @since 1.5.7 
	 * @return boolean
	 */
	function hasInactivePublishSchedule() {
		if($this->hasFutureDate() && !$this->get('show', false)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Gets the level based on the sort order if the theme object is hierachical. Otherwise returns 1 (top level)
	 * 
	 * @since 1.6.1
	 * 
	 * @return int
	 */
	function getLevel() {
		return substr_count($this->get('sort_order'), '-') + 1;
	}

}
