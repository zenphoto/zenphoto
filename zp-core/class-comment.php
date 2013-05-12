<?php
/**
 *Comment Class
 * @package classes
 */

// force UTF-8 Ø

class Comment extends PersistentObject {

	var $comment_error_text = NULL;

	/**
	 * This is a simple class so that we have a convienient "handle" for manipulating comments.
	 *
	 * @return Comment
	 */

	/**
	 * Constructor for a comment
	 *
	 * @param int $id set to the ID of the comment if not a new one.
	 * @return Comment
	 */
	function __construct($id=NULL) {
		$new = parent::PersistentObject('comments', array('id'=>$id), 'id', true, is_null($id));
	}

	/**
	 * Sets up default items on new comment objects
	 *
	 */
	function setDefaults() {
		$this->set('date', date('Y-m-d H:i:s'));
	}

	// convienence get & set functions

	/**
	 * returns the comment date/time
	 *
	 * @return string
	 */
	function getDateTime() { return $this->get('date'); }
	/**
	 * Sets a comment date/time value
	 *
	 * @param string $datetime
	 */
	function setDateTime($datetime) {
		if ($datetime == "") {
			$this->set('date', '0000-00-00 00:00:00');
		} else {
			$newtime = dateTimeConvert($datetime);
			if ($newtime === false) return;
			$this->set('date', $newtime);
		}
	}

	/**
	 * Returns the id of the comment owner
	 *
	 * @return int
	 */
	function getOwnerID() { return $this->get('ownerid'); }
	/**
	 * Sets the id of the owner of the comment
	 *
	 * @param int $value
	 */
	function setOwnerID($value) { $this->set('ownerid', $value); }

	/**
	 * Returns the commentor's name
	 *
	 * @return string
	 */
	function getName() { return $this->get('name'); }
	/**
	 * Sets the commentor's name
	 *
	 * @param string $value
	 */
	function setName($value) { $this->set('name', $value); }

	/**
	 * returns the email address of the commentor
	 *
	 * @return string
	 */
	function getEmail() { return $this->get('email'); }
	/**
	 * Sets the email address of the commentor
	 *
	 * @param string $value
	 */
	function setEmail($value) { $this->set('email', $value); }

	/**
	 * returns the Website of the commentor
	 *
	 * @return string
	 */
	function getWebsite() { return $this->get('website'); }
	/**
	 * Stores the website of the commentor
	 *
	 * @param string $value
	 */
	function setWebsite($value) { $this->set('website', $value); }

	/**
	 * Returns the comment text
	 *
	 * @return string
	 */
	function getComment() { return $this->get('comment'); }
	/**
	 * Stores the comment text
	 *
	 * @param string $value
	 */
	function setComment($value) { $this->set('comment', $value); }

	/**
	 * Returns true if the comment is marked for moderation
	 *
	 * @return int
	 */
	function getInModeration() { return $this->get('inmoderation'); }
	/**
	 * Sets the moderation flag of the comment
	 *
	 * @param int $value
	 */
	function setInModeration($value) { $this->set('inmoderation', $value); }

	/**
	 * Returns the 'type' of the comment. i.e. the class of the owner object
	 *
	 * @return string
	 */
	function getType() {
		return $this->get('type');
	}
	/**
	 * Sets the 'type' field of the comment
	 *
	 * @param string $type
	 */
	function setType($type) {
		$this->set('type', $type);
	}

	/**
	 * Returns the IP address of the comment poster
	 *
	 * @return string
	 */
	function getIP() { return $this->get('ip'); }
	/**
	 * Sets the IP address field of the comment
	 *
	 * @param string $value
	 */
	function setIP($value) { $this->set('ip', $value); }

	/**
	 * Returns true if the comment is flagged private
	 *
	 * @return bool
	 */
	function getPrivate() { return $this->get('private'); }
	/**
	 * Sets the private flag of the comment
	 *
	 * @param bool $value
	 */
	function setPrivate($value) { $this->set('private', $value); }

	/**
	 * Returns true if the comment is flagged anonymous
	 *
	 * @return bool
	 */
	function getAnon() { return $this->get('anon'); }
	/**
	 * Sets the anonymous flag of the comment
	 *
	 * @param bool $value
	 */
	function setAnon($value) { $this->set('anon', $value); }

	/**
	 * Returns the custom data field of the comment
	 *
	 * @return string
	 */
	function getCustomData() { return $this->get('custom_data'); }
	/**
	 * Stores the custom data field of the comment
	 *
	 * @param string $value
	 */
	function setCustomData($value) { $this->set('custom_data', $value); }
}
?>
