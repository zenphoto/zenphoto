<?php
/**
 * Global defintions of constants
 * 
 * @deprecated 2.0 Use definitions-global.php instead
 * 
 * @package core
 */
require_once(dirname(__FILE__) . '/definitions-global.php'); // Include the version info.
trigger_error(gettext('Zenphoto deprecation: zp-core/global-definitions.php is deprecated. Use zp-core/definitions-global.php instead'), E_USER_DEPRECATED);