<?php

/**
 * 
 * @package zpcore\classes\deprecated
 * @deprecated 2.0 - Use the class Administrator instead
 */
class Zenphoto_Administrator extends Administrator {

	/**
	 * @deprecated 2.0 - Use the class Authority instead
	 */
	function __construct($user, $valid) {
		parent::__construct($user, $valid);
		deprecationNotice(gettext('Use the Administrator class instead'));
	}

	/**
	 * @deprecated 2.0 - Use the class Authority method instead
	 */
	static function getNameByUser($user) {
		deprecationNotice(gettext('Use the Administrator class method instead'));
		parent::getNameByUser($user);
	}
}
