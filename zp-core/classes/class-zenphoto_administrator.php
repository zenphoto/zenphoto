<?php
/**
 * 
 * @package zpcore\classes\deprecated
 * @deprecated 2.0 - Use the class Administrator instead
 */
class Zenphoto_Administrator extends Administrator {
	
	function __construct($user, $valid) {
		parent::__construct($user, $valid);
		deprecationNotice(gettext('Use the Administrator class instead'));
	}
	
}