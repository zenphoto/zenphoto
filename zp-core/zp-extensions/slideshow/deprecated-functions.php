<?php

/*
 * Slideshow deprecated functions
 * @package plugins
 * @subpackage slideshow
 */

/**
 * @deprecated
 * @since 1.4.5
 */
function printSlideShowJS() {
	deprecated_functions::notify(gettext('This feature is now done by a "theme_head" filter. You can remove the function call.'));
}

?>