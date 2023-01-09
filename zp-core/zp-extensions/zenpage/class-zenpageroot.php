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
	 * @since ZenphotoCMS 1.6
	 * 
	 * @return string
	 */
	function getName() {
		return $this->get("titlelink");
	}

	/**
	 * sets the name (title link)
	 * 
	 * @since ZenphotoCMS 1.6
	 * 
	 * @param $v
	 */
	function setName($v) {
		$this->set("titlelink", $v);
	}

	/**
	 * Returns the titlelink
	 * 
	 * @deprecated ZenphotoCMS 2.0 - use getName() instead
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
	 * @deprecated ZenphotoCMS 2.0 - use setName() instead
	 * @param $v
	 */
	function setTitlelink($v) {
		deprecationNotice(gettext('Use setName() instead.'));
		$this->set("titlelink", $v);
	}
	
}