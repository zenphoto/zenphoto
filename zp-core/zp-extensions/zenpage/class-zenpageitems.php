<?php
/**
 * Base class from which Zenpage news articles and pages derive
 * @author Stephen Billard (sbillard), Malte Müller (acrylian)
 * @package plugins
 * @subpackage zenpage
 */
class ZenpageItems extends ZenpageRoot {

	/**
	 * Class instantiator
	 */
	function __construct() {
		// no action required
	}

	/**
	 * Returns the author
	 *
	 * @return string
	 */
	function getAuthor() {
		return $this->get("author");
	}

	/**
	 *
	 * sets the author attribute

	 */
	function setAuthor($a) {
		$this->set("author", $a);
	}

	/**
	 * Returns the content
	 *
	 * @return string
	 */
	function getContent($locale = NULL) {
		$text = $this->get("content");
		if ($locale == 'all') {
			return unTagURLs($text);
		} else {
			return applyMacros(unTagURLs(get_language_string($text, $locale)));
		}
	}
	
	/**
	 *
	 * Set the content datum
	 * @param $c full language string
	 */
	function setContent($c) {
		$c = tagURLs($c);
		$this->set("content", $c);
	}
	
	/**
	 * Returns the last change author
	 * 
	 * @deprecated 1.6 - Use getLastChangeUser() instead
	 *
	 * @return string
	 */
	function getLastchangeAuthor() {
		return $this->getLastChangeUser();
	}

	/**
	 *
	 * stores the last change author
	 * 
	 * @deprecated 1.6 - Use setLastChangeUser() instead
	 */
	function setLastchangeAuthor($a) {
		$this->setLastchangeUser($a);
	}

	/**
	 * Returns the locked status , "1" if locked (only used on the admin)
	 *
	 * @return string
	 */
	function getLocked() {
		return $this->get("locked");
	}

	/**
	 * sets the locked status , "1" if locked (only used on the admin)
	 *
	 */
	function setLocked($l) {
		$this->set("locked", $l);
	}

	/**
	 * Returns the extra content
	 *
	 * @return string
	 */
	function getExtraContent($locale = NULL) {
		$text = $this->get("extracontent");
		if ($locale == 'all') {
			return unTagURLs($text);
		} else {
			return applyMacros(unTagURLs(get_language_string($text, $locale)));
		}
	}

	/**
	 * sets the extra content
	 *
	 */
	function setExtraContent($ec) {
		$this->set("extracontent", tagURLs($ec));
	}

	/**
	 * Returns the expire date
	 *
	 * @return string
	 */
	function getExpireDate() {
		$dt = $this->get("expiredate");
		if ($dt == '0000-00-00 00:00:00') {
			return NULL;
		} else {
			return $dt;
		}
	}

	/**
	 * sets the expire date
	 *
	 */
	function setExpireDate($ed) {
		if ($ed) {
			$newtime = dateTimeConvert($ed);
			if ($newtime === false)
				return;
			$this->set('expiredate', $newtime);
		} else {
			$this->set('expiredate', NULL);
		}
	}

}