<?php

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function printFavoritesLink($text = NULL) {
	deprecated_functions::notify(gettext('use printFavoritesURL()'));
	printFavoritesURL($text);
}

?>