<?php
/**
 * 
 * @package zpcore\classes\deprecated
 * @deprecated 2.0 - Use the class Authority instead
 */
class Zenphoto_Authority extends Authority {
	
	function __construct() {
		parent::__construct();
		deprecationNotice(gettext('Use the Authority class instead'));
	}
}
