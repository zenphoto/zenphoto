<?php
/**
 * Base class from which all Zenpage classes derive
 * @author Stephen Billard (sbillard), Malte Müller (acrylian)
 * @package zpcore\plugins\zenpage\classes
 */
class ZenpageRoot extends ThemeObject {

	protected $sorttype;
	protected $sortdirection;
	protected $sortSticky = true;

	/**
	 * Returns the perma link status (only used on admin)
	 *
	 * @return string
	 */
	function getPermalink() {
		return $this->get("permalink");
	}

	/*	 * '
	 * sets the permalink
	 */

	function setPermalink($v) {
		$this->set('permalink', $v);
	}

	/**
	 * Returns the name (titlelink)
	 *
	 * @since 1.6
	 * 
	 * @return string
	 */
	function getName() {
		return $this->get("titlelink");
	}

	/**
	 * sets the name (title link)
	 * 
	 * @since 1.6
	 * 
	 * @param $v
	 */
	function setName($v) {
		$this->set("titlelink", $v);
	}

	/**
	 * Returns the titlelink
	 * 
	 * @deprecated 2.0 - use getName() instead
	 * 
	 * @return string
	 */
	function getTitlelink() {
		deprecationNotice(gettext('Use getName() instead.'));
		return $this->getName();
	}

	/**
	 * sets the title link
	 * 
	 * @deprecated 2.0 - use setName() instead
	 * @param $v
	 */
	function setTitlelink($v) {
		deprecationNotice(gettext('Use setName() instead.'));
		$this->set("titlelink", $v);
	}
	
	/**
	 * Gets the object of the oldest ancester of the page or category. Returns the object of the page/category itself if there is no urparent
	 * 
	 * @since 1.6.1
	 * return object
	 */
	function getUrParent() {
		global $_zp_db;
		if (method_exists($this, 'getParentID')) {
			if (is_null($this->urparent)) {
				$classname = get_class($this);
				if (!$this->getParentID()) {
					return $this->urparent = $this;
				}
				if (is_null($this->parents)) {
					$sortorders = explode('-', $this->getSortorder());
					if (count($sortorders) == 1) {
						$urparent = $this->getParent();
						$this->parents = array($urparent);
						return $this->urparent = $urparent;
					}
					$result = $_zp_db->querySingleRow('SELECT `titlelink` FROM ' . $_zp_db->prefix($this->table) . ' WHERE sort_order ="' . $sortorders[0] . '"');
					if ($result) {
						$urparent = new $classname($result['titlelink'], false);
						return $this->urparent = $urparent;
					} else {
						return $this->urparent = $this;
					}
				} else {
					$urparent = new $classname($this->parents[0], false);
					return $this->urparent = $urparent;
				}
			} else {
				return $this->urparent;
			}
		}
	}

}