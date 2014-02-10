<?php

/*
 * RSS deprecated functions
 */

class RSS_internal_deprecations {

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getRSSLink($option) {
		deprecated_functions::notify(sprintf(gettext('The %1$s feed is deprecated.'), $option));
	}

}

?>